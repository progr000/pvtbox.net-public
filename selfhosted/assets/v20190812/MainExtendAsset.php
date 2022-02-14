<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace selfhosted\assets\v20190812;

use Yii;
use yii\web\AssetBundle;
use common\helpers\FileSys;
use common\helpers\JSMin;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MainExtendAsset extends AssetBundle
{
    const TYPE_CSS = 'css';
    const TYPE_JS  = 'js';

    //public $sourcePath = '@frontend/web/themes/v20190812/';
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $fullPathToSource       = '@frontend/web/';
    public $fullPathToMinimizedCss = '@selfhosted/web/assets/v20190812-min/css';
    public $webPathToMinimizedCss  = 'assets/v20190812-min/css/';
    public $fullPathToMinimizedJs  = '@selfhosted/web/assets/v20190812-min/js';
    public $webPathToMinimizedJs   = 'assets/v20190812-min/js/';
    public $pathToLinkMediaSources = '@selfhosted/web/assets/v20190812-min';
    public $mediaSourcesForLink = [
        '@frontend/web/themes/v20190812/files',
        '@frontend/web/themes/v20190812/fonts',
        '@frontend/web/themes/v20190812/icons',
        '@frontend/web/themes/v20190812/images',
        '@frontend/web/themes/v20190812/sounds',
        '@frontend/web/themes/v20190812/icons',
    ];

    protected $useMinimized;


    /**
     * @param string $file_web
     * @param string $type
     * @param string|null $sub_folder
     * @param string|null $fullPathToSource
     * @return string
     */
    protected function compressFile($file_web, $type=self::TYPE_CSS, $sub_folder=null, $fullPathToSource=null)
    {
        /* если в параметрах системы указано что не используем минификцированнные, то вернем как есть */
        if (!$this->useMinimized) {
            return $file_web;
        }

        /* если не передан параметр $fullPathToSource то определим его как $this->fullPathToSource */
        if (!$fullPathToSource) {
            $fullPathToSource = $this->fullPathToSource;
        }

        /* подготовка путей в зависимости от типа файла (css|js) */
        if ($type == self::TYPE_CSS) {
            $webPathToMinimized  = $this->webPathToMinimizedCss;
            $fullPathToMinimized = $this->fullPathToMinimizedCss;
        } else {
            $webPathToMinimized  = $this->webPathToMinimizedJs;
            $fullPathToMinimized = $this->fullPathToMinimizedJs;
        }

        /* доаботка путей, если передан параметр $sub_folder */
        if (isset($sub_folder)) {
            $webPathToMinimized  .= "{$sub_folder}/";
            $fullPathToMinimized .= "/{$sub_folder}";
        }

        /* подготовка путей */
        $file_path = str_replace([
            '@frontend',
            '@selfhosted'
        ], [
            Yii::getAlias('@frontend'),
            Yii::getAlias('@selfhosted'),
        ], $fullPathToSource . $file_web);
        $tmp = FileSys::pathinfo($file_web);
        $file_web_min = $webPathToMinimized . $tmp['filename'] . '.minimized.' . $tmp['extension'];
        $file_path_min = str_replace([
            '@frontend',
            '@selfhosted'
        ], [
            Yii::getAlias('@frontend'),
            Yii::getAlias('@selfhosted'),
        ], $fullPathToMinimized . DIRECTORY_SEPARATOR . $tmp['filename'] . '.minimized.' . $tmp['extension']);

        /* для отладки файл будет пересоздаваться каждый раз если откоментировать*/
        //@unlink($file_path_min);

        /* если файл уже минифицирован ранее и не было изменений после этого в оригинальном файле, то отдаем минифицированый ранее */
        if (file_exists($file_path_min)) {
            $orig_mtime = filemtime($file_path);
            $min_mtime  = filemtime($file_path_min);
            $min_ctime  = filectime($file_path_min);
            if ($min_mtime > $orig_mtime) {
                return $file_web_min;
            }
        }

        /* Создаем дирректорию для минифицированых файлов */
        $directory_to_write = dirname($file_path_min);
        if (!file_exists($directory_to_write)) {
            FileSys::mkdir($directory_to_write, 0777, true);
        }

        /* если передается файл который уже предположительно минифицирован сторонними сервисами, то ну его нахер, отдаем как есть */
        if (strrpos($file_web, '.min.') !== false) {
            if ($this->onlyMoveCompressed($file_path, $file_path_min)) {
                return $file_web_min;
            }
        }

        /* Сжимаем СSS */
        if ($type == self::TYPE_CSS) {
            if ($this->compressCss($file_path, $file_path_min)) {
                return $file_web_min;
            }
        }

        /* Сжимаем JS */
        if ($type == self::TYPE_JS) {
            if ($this->compressJs($file_path, $file_path_min)) {
                return $file_web_min;
            }
        }

        /* Если сжатие НЕ удалось, возвращаем неминифицированный */
        return $file_web;
    }

    /**
     * @param string $file_path
     * @param string $file_path_min
     * @return bool
     */
    protected function onlyMoveCompressed($file_path, $file_path_min)
    {
        return @copy($file_path, $file_path_min);
    }

    /**
     * @param string $file_path
     * @param string $file_path_min
     * @return int
     */
    protected function compressCss($file_path, $file_path_min)
    {
        /* тут производим минификацию и возвращаем путь к нему */
        /* удалить комментарии */
        /* удалить табуляции, пробелы, символы новой строки и т.д. */
        return @file_put_contents(
            $file_path_min,
            str_replace(
                [' {', '} ', '{ ', ' }', ': ', ' :', '; ', ' ;', ', ', ' ,', ' > '],
                ['{' , '}' , '{' , '}' , ':' , ':' , ';' , ';' , ',' , ',' ,  '>' ],
                str_replace(
                    ["\r\n", "\r", "\n", "\t", '  ', '    ', '    '],
                    '',
                    preg_replace(
                        '!/\*[^*]*\*+([^/][^*]*\*+)*/!',
                        '',
                        file_get_contents($file_path)
                    )
                )
            )
        );
    }

    /**
     * @param string $file_path
     * @param string $file_path_min
     * @return int
     */
    protected function compressJs($file_path, $file_path_min)
    {
        //return @file_put_contents($file_path_min, file_get_contents($file_path));
        /* тут производим минификацию и возвращаем путь к нему */
        return @file_put_contents(
            $file_path_min,
            str_replace(
                //[";\r\n", ";\r", ";\n", "}\r\n", "}\r", "}\n", "\r\n{", "\r{", "\n{"],
                //[';'    , ';'  , ';'  , '}'    , '}'  , '}'  , '{'    , '{'  , '{'  ],
                [";\r\n", ";\r", ";\n"],
                [';'    , ';'  , ';'  ],
                trim(JSMin::minify(file_get_contents($file_path)))
            )
        );

        /*
        return @file_put_contents(
            $file_path_min,
            file_get_contents($file_path)
        );
        */
    }

    /**
     *
     */
    protected function makeMediaLinks()
    {
        $pathToLinkMediaSources = str_replace([
            '@frontend',
            '@selfhosted'
        ], [
            Yii::getAlias('@frontend'),
            Yii::getAlias('@selfhosted'),
        ], $this->pathToLinkMediaSources);
        if (!file_exists($pathToLinkMediaSources)) {
            FileSys::mkdir($pathToLinkMediaSources, 0777, true);
        }

        foreach ($this->mediaSourcesForLink as $v) {
            $baseName = basename($v);
            if (!file_exists($pathToLinkMediaSources . DIRECTORY_SEPARATOR . $baseName)) {
                $target = str_replace([
                    '@frontend',
                    '@selfhosted'
                ], [
                    Yii::getAlias('@frontend'),
                    Yii::getAlias('@selfhosted'),
                ], $v);
                symlink($target, $pathToLinkMediaSources . DIRECTORY_SEPARATOR . $baseName);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->makeMediaLinks();
        $this->useMinimized = (isset(Yii::$app->params['use_minimized_css']) && Yii::$app->params['use_minimized_css']);
    }
}
