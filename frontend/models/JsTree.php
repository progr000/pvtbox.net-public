<?php
namespace frontend\models;

use common\models\Users;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use common\helpers\FileSys;

/**
 * JsTree
 */
class JsTree extends Model
{

    protected $base = null;

    public $operation;
    public $id;
    public $text;
    public $type;
    public $parent;
    public $node_id;
    public $user_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['node_id', 'operation', 'user_id'], 'required'],
            [['node_id', 'user_id'], 'integer'],
            ['operation', 'in', 'range' => ['get_node', 'get_content', 'create_node', 'rename_node', 'delete_node', 'move_node', 'copy_node']],
            [['id', 'text', 'type', 'parent'], 'string'],
        ];
    }

    /**
     * Проверяет реальность существования пути $path
     *
     * @param string $path
     * @return string
     * @throws Exception
     */
    protected function real($path)
    {
        if ($path === false) { return $path; }
        $temp = realpath($path);
        if(!$temp) { throw new Exception('Path does not exist: ' . $path); }
        if($this->base && strlen($this->base)) {
            if(strpos($temp, $this->base) !== 0) { throw new Exception('Path is not inside base ('.$this->base.'): ' . $temp); }
        }
        return $temp;
    }

    /**
     * Форматирует реальный путь для элемента $id
     *
     * @param string $id
     * @return mixed|string
     * @throws Exception
     */
    protected function path($id)
    {
        $id = str_replace('/', DIRECTORY_SEPARATOR, $id);
        $id = trim($id, DIRECTORY_SEPARATOR);
        $id = $this->real($this->base . DIRECTORY_SEPARATOR . $id);
        return $id;
    }

    /**
     * Формирует путь для элемента $id
     *
     * @param string $path
     * @return string
     * @throws Exception
     */
    protected function id($path)
    {
        $path = $this->real($path);
        $path = substr($path, strlen($this->base));
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        $path = trim($path, '/');
        return strlen($path) ? $path : '/';
    }

    /**
     * Основная работа с деревом каталогов. Консолидация всех методов.
     *
     * @return array|null
     * @throws Exception
     */
    public function processTree()
    {
        $rslt = null;
        if ($this->validate()) {
            $User = Users::getPathNodeFS(Yii::$app->user->identity->getId());
            if (!$User)  { return "You are hacker!"; }
            $base = $User->_full_path;
            //var_dump($base); exit;
            if ($base === '') { return "Path error!"; }

            $this->base = $this->real($base);
            if(!$this->base) { /*exit;*/ throw new Exception('Base directory does not exist'); }

            $node = (($this->id !== null) && ($this->id !== '#')) ? $this->id : '/';
            switch ($this->operation) {
                case 'get_node':
                    $with_root = ($this->id === '#');
                    $rslt = $this->lst($node, $with_root);
                    break;
                case "get_content":
                    $rslt = $this->data($node);
                    break;
                case 'create_node':
                    $name = ($this->text !== null) ? $this->text : '';
                    $mkdir = ($this->type !== 'file');
                    $rslt = $this->create($node, $name, $mkdir);
                    break;
                case 'rename_node':
                    $name = ($this->text !== null) ? $this->text : '';
                    $rslt = $this->rename($node, $name);
                    break;
                case 'delete_node':
                    $rslt = $this->remove($node);
                    break;
                case 'move_node':
                    $parn = (($this->parent !== null) && ($this->parent !== '#')) ? $this->parent : '/';
                    $rslt = $this->move($node, $parn);
                    break;
                case 'copy_node':
                    $parn = (($this->parent !== null) && ($this->parent !== '#')) ? $this->parent : '/';
                    $rslt = $this->copy($node, $parn);
                    break;
                default:
                    $rslt = null;
                    break;
            }
        }
        return $rslt;
    }

    /**
     * Вернет cписок элементов в каталоге $id
     *
     * @param string $id
     * @param bool $with_root
     * @return array
     * @throws Exception
     */
    protected function lst($id, $with_root = false)
    {
        $dir = $this->path($id);
        $lst = @scandir($dir);
        if(!$lst) { throw new Exception('Could not list path: ' . $dir); }
        $res = array();
        foreach($lst as $item) {
            if($item == '.' || $item == '..' || $item === null) { continue; }
            $tmp = preg_match('([^ a-zа-я-_0-9.]+)ui', $item);
            if($tmp === false || $tmp === 1) { continue; }
            if(is_dir($dir . DIRECTORY_SEPARATOR . $item)) {
                $res[] = array('text' => $item, 'children' => true,  'id' => $this->id($dir . DIRECTORY_SEPARATOR . $item), 'icon' => 'folder');
            }
            else {
                $res[] = array('text' => $item, 'children' => false, 'id' => $this->id($dir . DIRECTORY_SEPARATOR . $item), 'type' => 'file', 'icon' => 'file file-'.substr($item, strrpos($item,'.') + 1));
            }
        }
        if($with_root && $this->id($dir) === '/') {
            $res = array(array('text' => FileSys::basename($this->base), 'children' => $res, 'id' => '/', 'icon'=>'folder', 'state' => array('opened' => true, 'disabled' => true)));
        }
        return $res;
    }

    /**
     * Вернет тип элемента $id
     *
     * @param string $id
     * @return array
     * @throws Exception
     */
    protected function data($id)
    {
        if(strpos($id, ":")) {
            $id = array_map(array($this, 'id'), explode(':', $id));
            return array('type'=>'multiple', 'content'=> 'Multiple selected: ' . implode(' ', $id));
        }
        $dir = $this->path($id);
        if(is_dir($dir)) {
            return array('type'=>'folder', 'content'=> $id);
        }
        if(is_file($dir)) {
            $ext = strpos($dir, '.') !== FALSE ? substr($dir, strrpos($dir, '.') + 1) : '';
            $dat = array('type' => $ext, 'content' => '');
            switch($ext) {
                case 'txt':
                case 'text':
                case 'md':
                case 'js':
                case 'json':
                case 'css':
                case 'html':
                case 'htm':
                case 'xml':
                case 'c':
                case 'cpp':
                case 'h':
                case 'sql':
                case 'log':
                case 'py':
                case 'rb':
                case 'htaccess':
                case 'php':
                    $dat['content'] = file_get_contents($dir);
                    break;
                case 'jpg':
                case 'jpeg':
                case 'gif':
                case 'png':
                case 'bmp':
                    $dat['content'] = 'data:'.finfo_file(finfo_open(FILEINFO_MIME_TYPE), $dir).';base64,'.base64_encode(file_get_contents($dir));
                    break;
                default:
                    $dat['content'] = 'File not recognized: '.$this->id($dir);
                    break;
            }
            return $dat;
        }
        throw new Exception('Not a valid selection: ' . $dir);
    }

    /**
     * Создает новый элемент в каталоге $id
     *
     * @param string $id
     * @param string $name
     * @param bool $mkdir
     * @return array
     * @throws Exception
     */
    protected function create($id, $name, $mkdir = false)
    {
        $dir = $this->path($id);
        if(preg_match('([^ a-zа-я-_0-9.]+)ui', $name) || !strlen($name)) {
            throw new Exception('Invalid name: ' . $name);
        }
        if($mkdir) {
            mkdir($dir . DIRECTORY_SEPARATOR . $name);
            // + ACTION CREATE DIR IN BD
        }
        else {
            file_put_contents($dir . DIRECTORY_SEPARATOR . $name, '');
            // + ACTION CREATE FILE IN BD
        }
        return array('id' => $this->id($dir . DIRECTORY_SEPARATOR . $name));
    }

    /**
     * Переименовывает элемент $id
     *
     * @param string $id
     * @param string $name
     * @return array
     * @throws Exception
     */
    protected function rename($id, $name)
    {
        $dir = $this->path($id);
        if($dir === $this->base) {
            throw new Exception('Cannot rename root');
        }
        if(preg_match('([^ a-zа-я-_0-9.]+)ui', $name) || !strlen($name)) {
            throw new Exception('Invalid name: ' . $name);
        }
        $new = explode(DIRECTORY_SEPARATOR, $dir);
        array_pop($new);
        array_push($new, $name);
        $new = implode(DIRECTORY_SEPARATOR, $new);
        if($dir !== $new) {
            if(is_file($new) || is_dir($new)) { throw new Exception('Path already exists: ' . $new); }
            rename($dir, $new);
            // + ACTION RENAME OBJ IN BD
        }
        return array('id' => $this->id($new));
    }

    /**
     * Удаляет элемент $id
     *
     * @param string $id
     * @return array
     * @throws Exception
     */
    protected function remove($id)
    {
        $dir = $this->path($id);
        if($dir === $this->base) {
            throw new Exception('Cannot remove root');
        }
        if(is_dir($dir)) {
            foreach(array_diff(scandir($dir), array(".", "..")) as $f) {
                $this->remove($this->id($dir . DIRECTORY_SEPARATOR . $f));
            }
            rmdir($dir);
            // + ACTION DELETE DIR IN BD
        }
        if(is_file($dir)) {
            unlink($dir);
            // + ACTION DELETE FILE IN BD
        }
        return array('status' => 'OK');
    }

    /**
     * Перемещает элемент $id в $par
     *
     * @param string $id
     * @param string $par
     * @return array
     */
    protected function move($id, $par)
    {
        $dir = $this->path($id);
        $par = $this->path($par);
        $new = explode(DIRECTORY_SEPARATOR, $dir);
        $new = array_pop($new);
        $new = $par . DIRECTORY_SEPARATOR . $new;
        rename($dir, $new);
        // + ACTION MOVE OBJ IN BD
        return array('id' => $this->id($new));
    }

    /**
     * Копирует элемент $id в $par
     *
     * @param string $id
     * @param string $par
     * @return array
     * @throws Exception
     */
    protected function copy($id, $par)
    {
        $dir = $this->path($id);
        $par = $this->path($par);
        $new = explode(DIRECTORY_SEPARATOR, $dir);
        $new = array_pop($new);
        $new = $par . DIRECTORY_SEPARATOR . $new;
        if(is_file($new) || is_dir($new)) { throw new Exception('Path already exists: ' . $new); }

        if(is_dir($dir)) {
            mkdir($new);
            foreach(array_diff(scandir($dir), array(".", "..")) as $f) {
                $this->copy($this->id($dir . DIRECTORY_SEPARATOR . $f), $this->id($new));
            }
        }
        if(is_file($dir)) {
            copy($dir, $new);
        }
        // + ACTION COPY OBJ IN BD
        return array('id' => $this->id($new));
    }
}
