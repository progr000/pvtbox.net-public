<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;
use common\helpers\Functions;
use common\helpers\FileSys;
use frontend\models\NodeApi;

/**
 * This is the model class for table "{{%user_files}}".
 *
 * @property integer $file_id
 * @property integer $file_parent_id
 * @property string $file_uuid
 * @property string $file_name
 * @property integer $file_size
 * @property string $file_md5
 * @property string $file_created
 * @property string $file_updated
 * @property integer $file_lastatime
 * @property integer $file_lastmtime
 * @property integer $is_folder
 * @property integer $is_deleted
 * @property integer $is_updated
 * @property integer $is_outdated
 * @property integer $last_event_type
 * @property string $last_event_uuid
 * @property integer $first_event_id
 * @property integer $last_event_id
 * @property string $diff_file_uuid
 * @property integer $user_id
 * @property integer $node_id
 * @property integer $collaboration_id
 * @property integer $is_collaborated
 * @property integer $is_owner
 * @property integer $is_shared
 * @property string $share_hash
 * @property string $share_group_hash
 * @property string $share_created
 * @property string $share_lifetime
 * @property integer $share_ttl_info
 * @property string $share_password
 * @property string $folder_children_count
 * @property integer $share_is_locked
 *
 * @property string $file_parent_uuid
 * @property UserFileEvents[] $userFileEvents
 * @property Users $user
 */
class UserFiles extends ActiveRecord
{
    const FILE_NAME_MAX_LENGTH = 255;
    const FILE_PATH_MAX_LENGTH = 3096;

    const CHMOD_FILE = 0664;
    const CHMOD_DIR  = 0775;

    const ROOT_PARENT_ID = 0;

    const TYPE_FOLDER = 1;
    const TYPE_FILE = 0;
    const TYPE_TOP_FOLDER = 2;
    const TYPE_UP_FOLDER = 3;

    const FILE_DELETED = 1;
    const FILE_UNDELETED = 0;

    const FILE_UPDATED = 1;
    const FILE_UNUPDATED = 0;

    const FILE_SHARED = 1;
    const FILE_UNSHARED = 0;

    const SHARE_LOCKED = 1;
    const SHARE_UNLOCKED = 0;

    const FILE_COLLABORATED = 1;
    const FILE_UNCOLLABORATED = 0;

    const FILE_OUTDATED = 1;
    const FILE_UNOUTDATED = 0;

    const IS_OWNER = 1;
    const IS_COLLEAGUE = 0;

    const DIR_INFO_FILE = ".dirInfoFile";
    const DIR_COPYING_IN_PROGRESS = ".copying_in_progress";

    //const TTL_IMMEDIATELY_DOWNLOADED = -2;
    const TTL_IMMEDIATELY            = -1;
    const TTL_WITHOUTEXPIRY          = 0;
    //const TTL_3HOURS                 = 10800;
    //const TTL_12HOURS                = 43200;
    const TTL_1DAY                   = 86400;
    const TTL_3DAYS                  = 259200;
    //const TTL_5DAYS                  = 432000;
    //const TTL_10DAYS                 = 864000;
    //const TTL_1MONTH                 = 2592000;

    public $file_parent_uuid;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'file_created',
                'updatedAtAttribute' => 'file_updated',
                'value' => function() { return date(SQL_DATE_FORMAT); }
            ],
        ];
    }

    /**
     * returns list of TTL in array
     * @return array
     */
    public static function ttlLabels()
    {
        return [
            self::TTL_WITHOUTEXPIRY => Yii::t('models/user-files', 'TTL_WITHOUTEXPIRY'),
            self::TTL_IMMEDIATELY   => Yii::t('models/user-files', 'TTL_IMMEDIATELY'),
            //self::TTL_3HOURS        => Yii::t('models/user-files', 'TTL_3HOURS'),
            //self::TTL_12HOURS       => Yii::t('models/user-files', 'TTL_12HOURS'),
            self::TTL_1DAY          => Yii::t('models/user-files', 'TTL_1DAY'),
            self::TTL_3DAYS         => Yii::t('models/user-files', 'TTL_3DAYS'),
            //self::TTL_5DAYS         => Yii::t('models/user-files', 'TTL_5DAYS'),
            //self::TTL_10DAYS        => Yii::t('models/user-files', 'TTL_10DAYS'),
            //self::TTL_1MONTH        => Yii::t('models/user-files', 'TTL_1MONTH'),
        ];
    }

    /**
     * return ttl name by transfer_status value
     * @param integer $ttl
     *
     * @return string | null
     */
    public static function ttlLabel($ttl)
    {
        $labels = self::ttlLabels();
        return isset($labels[$ttl]) ? $labels[$ttl] : null;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_files}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['file_parent_id', 'file_uuid', 'file_name', 'is_deleted', 'user_id'], 'required'], // added+++ 2019-03-05 16:40

            [['file_uuid', 'file_md5', 'diff_file_uuid', 'share_hash', 'share_group_hash', 'last_event_uuid'], 'string', 'length' => 32],

            [['file_name'], 'string', 'encoding' => '8bit', 'min' => 1, 'max' => self::FILE_NAME_MAX_LENGTH, 'tooLong' => '{attribute} should contain at most {max} bytes.'],
            [['file_name'], 'filter', 'filter' => 'trim', 'skipOnArray' => true],
            [['file_name'], 'validateFilename'],

            [['file_created', 'file_updated', 'share_created', 'share_lifetime'], 'validateDateField', 'skipOnEmpty' => true],
            [['file_created', 'file_updated', 'share_created', 'share_lifetime'], 'safe'],

            [[
                'user_id',
                'node_id',
                'file_parent_id',
                'file_size',
                'file_lastatime',
                'file_lastmtime',
                'collaboration_id',
                'share_ttl_info',
                'folder_children_count',
                'first_event_id',
                'last_event_id'
            ], 'integer'],

            [['last_event_type'], 'integer'],
            [['last_event_type'], 'in', 'range' => [
                UserFileEvents::TYPE_CREATE,
                UserFileEvents::TYPE_UPDATE,
                UserFileEvents::TYPE_DELETE,
                UserFileEvents::TYPE_MOVE,
                UserFileEvents::TYPE_FORK,
                UserFileEvents::TYPE_RESTORE,
                UserFileEvents::TYPE_ROLLBACK,
            ]],
            [['last_event_type'], 'default', 'value' => UserFileEvents::TYPE_CREATE], // added+++ 2019-03-05 16:40

            [['is_folder'], 'integer'],
            [['is_folder'], 'in', 'range' => [self::TYPE_FILE, self::TYPE_FOLDER]], // added+++ 2019-03-05 16:40
            [['is_folder'], 'default', 'value' => self::FILE_UNDELETED],

            [['is_deleted'], 'integer'],
            [['is_deleted'], 'in', 'range' => [self::FILE_DELETED, self::FILE_UNDELETED]], // added+++ 2019-03-05 16:40
            //[['is_deleted'], 'default', 'value' => self::FILE_UNDELETED],

            [['is_updated'], 'integer'],
            [['is_updated'], 'in', 'range' => [self::FILE_UPDATED, self::FILE_UNUPDATED]], // added+++ 2019-03-05 16:40
            [['is_updated'], 'default', 'value' => self::FILE_UNUPDATED],

            [['is_outdated'], 'integer'],
            [['is_outdated'], 'in', 'range' => [self::FILE_OUTDATED, self::FILE_UNOUTDATED]], // added+++ 2019-03-05 16:40
            [['is_outdated'], 'default', 'value' => self::FILE_UNOUTDATED],

            [['is_collaborated'], 'integer'],
            [['is_collaborated'], 'in', 'range' => [self::FILE_COLLABORATED, self::FILE_UNCOLLABORATED]], // added+++ 2019-03-05 16:40
            [['is_collaborated'], 'default', 'value' => self::FILE_UNCOLLABORATED],

            [['is_owner'], 'integer'],
            [['is_owner'], 'in', 'range' => [self::IS_OWNER, self::IS_COLLEAGUE]], // added+++ 2019-03-05 16:40
            [['is_owner'], 'default', 'value' => self::IS_OWNER],

            [['is_shared'], 'integer'],
            [['is_shared'], 'in', 'range' => [self::FILE_SHARED, self::FILE_UNSHARED]], // added+++ 2019-03-05 16:40
            [['is_shared'], 'default', 'value' => self::FILE_UNSHARED],

            [['share_is_locked'], 'integer'],
            [['share_is_locked'], 'in', 'range' => [self::SHARE_UNLOCKED, self::SHARE_LOCKED]],
            [['share_is_locked'], 'default', 'value' => self::SHARE_UNLOCKED],

            [['share_password'], 'string', 'max' => 32],

            /* unique keys */
            [['file_uuid', 'user_id', 'is_deleted', 'collaboration_id'],
                'unique',
                'when' => function ($model) {
                    return !empty($model->collaboration_id);
                },
                'targetAttribute' => ['file_uuid', 'user_id', 'is_deleted', 'collaboration_id'],
                'message' => 'The combination of [file_uuid, user_id, is_deleted, collaboration_id] has already been taken.'
            ],  // +++changed 2019-03-05 16:40
            [['file_name', 'file_parent_id', 'user_id', 'is_deleted'], 'unique', 'targetAttribute' => ['file_name', 'file_parent_id', 'user_id', 'is_deleted'], 'message' => 'The combination of [file_name, file_parent_id, user_id, is_deleted] has already been taken.'], // +++changed 2019-03-05 16:40
            [['share_hash'], 'unique', 'skipOnEmpty' => true],

            /* foreign keys */
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id']],
            [['node_id'], 'exist', 'skipOnEmpty' => true, 'skipOnError' => true, 'targetClass' => UserNode::className(), 'targetAttribute' => ['node_id' => 'node_id']], // +++changed 2019-03-05 16:40
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateDateField($attribute, $params)
    {
        $check = Functions::checkDateIsValidForDB($this->$attribute);
        if (!$check) {
            $this->addError($attribute, 'Invalid date format');
        }
    }

    /**
     * Проверка на зарезервированные имена файлов и всякие кривые символы
     * @param $attribute
     * @param $params
     */
    public function validateFilename($attribute, $params)
    {
        $ret = self::checkSystemReservedFilename($this->$attribute);
        if (isset($ret['error'])) {
            $this->addError($attribute, $ret['error']);
        }
    }

    /**
     * @param string $file_name
     * @return array|bool
     */
    public static function checkSystemReservedFilename($file_name)
    {
        // Match Emoticons
        $regex['illegal'] = [
            'check'   => '/[<>\:\"\/\|\?\*\\\]/u',
            'error'   => 'Illegal characters in the file name',
            'replace' => '_'
        ];
        $regex['system_filename'] = [
            'check'   => '/^(CON|PRN|AUX|NUL|NULL|COM|LPT)[0-9]{0,1}$/iu',
            'error'   => 'Not allowed reserved filename',
            'replace' => '___'
        ];
        $regex['system_extension'] = [
            'check'   => '/\.(?:CON|PRN|AUX|NUL|NULL|COM|LPT)[0-9]{0,1}$/iu',
            'error'   => 'Not allowed reserved extension',
            'replace' => '.___'
        ];
        $regex['locked_filename'] = [
            'check'   => '/^(desktop\.ini|\.DS_Store|\.directory|\.pvtbox|\.dirInfoFile|\.quarantine|\.tmb)$/iu',
            'error'   => 'Not allowed reserved filename',
            'replace' => '___'
        ];
        $regex['dot_at_end'] = [
            'check'   => '/\.{1,}$/iu',
            'error'   => 'Not allowed dot at end of name',
            'replace' => '_'
        ];
        $error = false;
        $error_text = [];
        $matches = [];
        $rules = [];
        $var_file_name = $file_name;
        foreach ($regex as $val) {
            $match = [];
            if (preg_match_all($val['check'], $var_file_name, $match)) {
                $error = true;
                $error_text[] = $val['error'];
                $rules[] = $val['check'];
                $matches = array_merge($matches, $match);
                $var_file_name = preg_replace($val['check'], $val['replace'], $var_file_name);
            }
        }
        if ($error) {
            return [
                'error'   => $error_text,
                //'rules'   => $rules,
                //'matches' => $matches,
                'orig_file_name' => $file_name,
                'var_file_name'  => $var_file_name,
            ];
        }
        /*
        foreach ($regex as $val) {
            if (preg_match_all($val['check'], $file_name, $matches)) {
                return [
                    'error'=> $val['error'],
                    'rule' => $val['check'],
                    'matches' => $matches,
                    'var_file_name' => preg_replace($val['check'], $val['replace'], $file_name),
                ];
            }
        }
        */

        return true;
    }

    /**
     * @param string $file_name
     * @return array|bool
     */
    public static function checkEmoticonsInFilename($file_name)
    {
        // Match Emoticons
        $regex['Emoticons'] = '/[\x{1F600}-\x{1F64F}]/u';
        // Match Miscellaneous Symbols and Pictographs
        $regex['Symbols'] = '/[\x{1F300}-\x{1F5FF}]/u';
        // Match Transport And Map Symbols
        $regex['Transport'] = '/[\x{1F680}-\x{1F6FF}]/u';
        // Match Miscellaneous Symbols
        $regex['Misc'] = '/[\x{2600}-\x{26FF}]/u';
        // Match Dingbats
        $regex['Dingbats'] = '/[\x{2700}-\x{27BF}]/u';
        // Match Flags
        $regex['Flags'] = '/[\x{1F1E6}-\x{1F1FF}]/u';
        // Others
        $regex['Others'] = '/[\x{1F910}-\x{1F95E}]/u';
        $regex['Others2'] = '/[\x{1F980}-\x{1F991}]/u';
        $regex['Others3'] = '/[\x{1F9C0}]/u';
        $regex['Others4'] = '/[\x{1F9F9}]/u';
        foreach ($regex as $val) {
            if (preg_match($val, $file_name)) {
                return ['error' => 'Illegal characters in the file name'];
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'file_id' => 'id файла',
            'file_parent_id' => 'Родительский id. По сути - ссылка на папку в которой находится файл. можеть быть null для файлов лежащих в корневой папке',
            'file_uuid' => '32-х битный идентификатор (например 90d9a178f3b1e725e13735faac1e9315)',
            'file_name' => 'имя файла',
            'file_size' => 'Размер файла',
            'file_md5' => 'Контрольная сумма md5-файла',
            'file_lastatime' => 'Last access time',
            'file_lastmtime' => 'Last modified time',
            'is_folder' => 'флаг. true (1) если это папка и false (0) если это файл',
            'is_deleted' => 'признак удаления файла/папки',
            'is_updated' => 'признак того что у файла есть евент update',
            'is_outdated' => 'Признак того что файл удален и уже вне даты восстановления и он уже обработан кроном',
            'last_event_type' => 'Последнее событие, зарегистрированное по этому файлу',
            'last_event_uuid' => 'Uuid последнего события',
            'diff_file_uuid' => 'uuid файла с разницей данных - последняя версия этого uuid (из таблицы user_file_events. создано в этой таблице для уменьшения нагрузки при запросах)',
            'user_id' => 'ссылка на id пользователя users.user_id',
            'node_id' => 'ссылка на id ноды user_node.node_id',
            'collaboration_id' => 'Ссылка на user_collaborations.collaboration_id',
            'is_collaborated' => 'Признак что файл учавствует в коллаборации',
            'is_owner' => "Признак того что файл принадлежит пользователю user_id в случае коллабораци",
            'is_shared' => 'флаг. true (1) если элемент расшарен как собственный и false (0) если он не расшарен или принадлежит групповой шаре (расшаренной папке)',
            'share_hash' => 'уникальный идентификатор расшаренного файла или папки. Может быть NULL (нет шаринга)',
            'share_group_hash' => 'идентификатор группы расшаренных файлов и папок (при расшаривании папки). Может быть NULL (нет группового шаринга)',
            'share_created' => 'Дата создания шаринга',
            'share_lifetime' => 'Дата окончания шаринга',
            'share_ttl_info' => 'Информационное поле для получения ТТЛ шары и вывода его в селект поле',
            'share_password' => 'Пароль доступа к расшаренному элементу',
            'folder_children_count' => 'Количество файлов в дирректории (считается с вложенными)',
            'share_is_locked' => 'Признак что шара заблокирована админом',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserFileEvents()
    {
        return $this->hasMany(UserFileEvents::className(), ['file_id' => 'file_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNode()
    {
        return $this->hasOne(UserNode::className(), ['node_id' => 'node_id']);
    }

    /**
     * Метод для получения последнего last_event_id для заданного файла file_id
     *
     * @param integer $file_id
     * @return mixed
     */
    public static function last_event_id($file_id)
    {
        return UserFileEvents::find()
            ->andWhere(['file_id' => $file_id])
            ->max('event_id');
    }

    /**
     * @param integer $file_id
     * @return \common\models\UserFileEvents | null
     */
    public static function last_event_as_object($file_id)
    {
        return UserFileEvents::find()
            ->where(['file_id' => $file_id])
            ->orderBy(['event_id' => SORT_DESC])
            ->limit(1)
            ->one();
    }

    /**
     * Генерирует share_hash для объекта
     * @return null|string
     */
    public function generate_share_hash()
    {
        if ($this->file_uuid) {
            $this->share_hash = md5($this->file_uuid . $this->user_id . $this->is_deleted);
        } else {
            $this->share_hash = null;
        }
        return $this->share_hash;
    }

    /**
     * @param string $file_uuid
     * @return string
     */
    /*
    public static function generate__share_hash($file_uuid)
    {
        return md5($file_uuid);
    }
    */

    /**
     * Получает полный путь к файлу в реальной фс
     * @param \common\models\UserFiles $UserFile
     * @return string
     */
    public static function getFullPath($UserFile)
    {
        $res = Yii::$app->db->createCommand("SELECT get_full_path(:file_id, :separator) as full_path", [
            'file_id'   => $UserFile->file_id,
            'separator' => DIRECTORY_SEPARATOR,
        ])->queryOne();
        if (isset($res['full_path'])) {
            return $res['full_path'];
        } else {
            return false;
        }
    }

    /**
     * @param string $source_full_path
     * @param \common\models\UserNode $UserNodeDestination
     * @param string $folder_parent_uuid
     * @param integer $collaboration_id
     * @param array $event_store
     */
    public static function folderCopy($source_full_path, $UserNodeDestination, $folder_parent_uuid, $collaboration_id, &$event_store=array())
    {
        /* получение информации по текущей папке или файлу */
        //var_dump($source_full_path); exit;
        $source_file_info = self::getFileShareInfo($source_full_path);

        /* евент создания копии папки */
        if ($source_file_info['is_folder']) {
            $data['copy_folder_uuid'] = $source_file_info['file_uuid'];
            $data['parent_folder_uuid'] = $folder_parent_uuid;
            $data['folder_name'] = $source_file_info['file_name'];
            $data['collaboration_id'] = $collaboration_id;
            //var_dump($source_file_info); exit;
            $model = new NodeApi(['folder_name']);
            $model->load(['NodeApi' => $data]);
            $model->validate();
            $answer = $model->folder_event_create(
                $UserNodeDestination,
                true,
                false,
                false
            );

            /* собираем евенты */
            if (isset($answer['event_data'])) {
                foreach ($answer['event_data'] as $k => $v) {
                    $event_store[] = $v;
                }
            }
        }

        /* Открытие папки источника для получения ее вложений и создания их копии */
        if ($h = @opendir($source_full_path)) {
            while (false !== ($f = readdir($h))) {

                if (($f === '.') || ($f === '..') || ($f === self::DIR_INFO_FILE))
                    continue;

                $path = $source_full_path . DIRECTORY_SEPARATOR . $f;
                if (is_dir($path)) {
                    /* рекурсивный обход вложенных папок */
                    self::folderCopy($path, $UserNodeDestination, $source_file_info['file_uuid'], $collaboration_id, $event_store);
                } else {
                    //var_dump($path);
                    $source_file_info2 = self::getFileShareInfo($path);
                    //var_dump($source_file_info2);
                    /* евент создания копии файла */
                    $data2['copy_file_uuid']   = $source_file_info2['file_uuid'];
                    $data2['file_uuid']        = $source_file_info2['file_uuid'];
                    $data2['folder_uuid']      = $source_file_info['file_uuid'];
                    $data2['file_name']        = $source_file_info2['file_name'];
                    $data2['file_size']        = $source_file_info2['file_size'];
                    $data2['collaboration_id'] = $collaboration_id;

                    //var_dump($data2); exit;
                    $model = new NodeApi(['copy_file_uuid', 'file_uuid', 'file_name', 'file_size']);
                    $model->load(['NodeApi' => $data2]);
                    $model->validate();
                    $answer2 = $model->file_event_copy(
                        $UserNodeDestination,
                        true,
                        false,
                        false
                    );
                    //var_dump($answer2); exit;

                    /* собираем евенты */
                    if (isset($answer2['event_data'])) {
                        foreach ($answer2['event_data'] as $k => $v) {
                            $event_store[] = $v;
                        }
                    }
                }
            }
            closedir($h);
        }
    }

    /**
     * Проверяет можно ли перемещать папку в другую (не нарушает ли это структуру фс)
     * @param integer|null $folder_destination_id
     * @param string $folder_source_uuid
     * @return string
     */
    public static function allowFolderMove($folder_destination_id, $folder_source_uuid)
    {
        //var_dump($folder_destination_id);exit;
        /* если не указана перемещаемая папка-источник то нельзя перемещать */
        if (!$folder_source_uuid) {
            return false;
        }

        /* если перемещаем источник в корень ФС то можно перемещать */
        if ($folder_destination_id === 0) {
            return true;
        }

        /* тут проверяем что не перемещаем папку в самое себя */
        /* или не перемещаем ее на уровни ниже владельцем которых она и является */
        $UserFiles = self::findOne(['file_id' => $folder_destination_id]);
        //var_dump($UserFiles->file_uuid . ' ---' . $folder_source_uuid); exit;
        while ($UserFiles) {
            //echo $folder_source_uuid . '<br />' . $UserFiles->file_uuid . "<br />parent_id=" . $UserFiles->file_parent_id ."<hr />";
            if ($folder_source_uuid === $UserFiles->file_uuid) {
                return false;
            }
            $UserFiles = self::findOne(['file_id' => $UserFiles->file_parent_id]);
        }

        /* во всех остальных случаяъ позволяем переместить */
        return true;
    }

    /**
     * @param string $share_group_hash
     * @param integer $file_id
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getChildren($share_group_hash, $file_id)
    {
        $ret = self::find()
            ->select([
                'file_id',
                'share_hash',
                'file_name as name',
                'file_size',
                'diff_file_uuid',
                'is_folder',
                'is_shared',
                'share_password',
                'file_md5 as file_hash',
            ])
            ->where([
                'share_group_hash' => $share_group_hash,
                'file_parent_id'   => $file_id,
            ])
            ->andWhere("(share_lifetime > :share_lifetime) OR (share_lifetime IS NULL)", ['share_lifetime'  => date(SQL_DATE_FORMAT)])
            /*
            ->andWhere("(share_lifetime > :share_lifetime) OR ((share_lifetime IS NULL) AND (share_ttl_info != :TTL_IMMEDIATELY_DOWNLOADED))", [
                'share_lifetime'             => date(SQL_DATE_FORMAT),
                'TTL_IMMEDIATELY_DOWNLOADED' => self::TTL_IMMEDIATELY_DOWNLOADED,
            ])
            */
            ->andWhere("last_event_type <> :last_event_type", ['last_event_type' => UserFileEvents::TYPE_DELETE])
            ->asArray()
            ->all();
        if (is_array($ret)) {
            foreach ($ret as $k=>$v) {

                if ($ret[$k]['is_folder'] == self::TYPE_FOLDER) {

                    $file_id = ($ret[$k]['is_shared'] == self::FILE_UNSHARED) ? $ret[$k]['file_id'] : null;
                    $ret[$k]['share_link'] = self::getShareLink($share_group_hash, true, $file_id);
                    unset($ret[$k]['file_size'], $ret[$k]['diff_file_uuid']);
                    $ret[$k]['childs'] = self::getChildren($share_group_hash, $ret[$k]['file_id']);

                } else {

                    /** @var \common\models\UserFileEvents $eventWithUuid */
                    $eventWithUuid = UserFileEvents::find()
                        ->select([
                            'event_id',
                            'event_uuid',
                        ])
                        ->where(['file_id' => $ret[$k]['file_id']])
                        ->andWhere('event_type NOT IN (:event_delete)', ['event_delete' => UserFileEvents::TYPE_DELETE])
                        ->orderBy(['event_id' => SORT_DESC])
                        ->limit(1)
                        ->one();

                    if ($eventWithUuid) {
                        $ret[$k]['event_uuid'] = $eventWithUuid->event_uuid;
                        $ret[$k]['share_link'] = self::getShareLink($ret[$k]['share_hash'], false);
                    } else {
                        $ret[$k] = [];
                    }

                }

                if ($ret[$k]['share_password']) {
                    $ret[$k]['share_password'] = true;
                } else {
                    $ret[$k]['share_password'] = false;
                }

                unset($ret[$k]['is_folder'], $ret[$k]['file_id'], $ret[$k]['is_shared']);
            }
        } else {
            unset($ret);
            $ret = [];
        }
        return $ret;
    }

    /**
     * Вспомогательная функция для  функции markChildrenAsDeleted
     * @param $dir
     * @param array $files_ids
     * @param $has_no_any_files
     */
    protected static function markChildRecursively($dir, array &$files_ids, &$has_no_any_files)
    {
        if ($h = @opendir($dir)) {
            while (false !== ($f = readdir($h))) {

                if (($f === '.') || ($f === '..') || $f === self::DIR_INFO_FILE)
                    continue;

                $path = $dir. DIRECTORY_SEPARATOR .$f;
                if (!is_dir($path)) {
                    $is_folder = false;
                    $has_no_any_files = false;
                    $info_file = $path;
                } else {
                    $is_folder = true;
                    $info_file = $path . DIRECTORY_SEPARATOR . self::DIR_INFO_FILE;
                    self::markChildRecursively($path, $files_ids, $has_no_any_files);
                }
                //var_dump($info_file);
                try {
                    if (file_exists($info_file)) {
                        $UserFile = @unserialize(file_get_contents($info_file));
                        //var_dump($UserFile);
                        if (isset($UserFile['file_id'])) {
                            $files_ids[] = intval($UserFile['file_id']);
                            /*
                            $UserFile['is_owner']        = $UserFile['cbl_is_owner'];
                            $UserFile['is_updated']      = $UserFile['file_updated'] ? self::FILE_UPDATED : self::FILE_UNUPDATED;
                            $UserFile['is_shared']       = $UserFile['file_shared'] ? self::FILE_SHARED : self::FILE_UNSHARED;
                            */
                            if ($is_folder) {
                                $UserFile['file_size'] = 0;
                                $UserFile['folder_children_count'] = 0;
                            }
                            $UserFile['is_shared'] = self::FILE_UNSHARED;
                            $UserFile['share_hash'] = null;
                            $UserFile['share_lifetime'] = null;
                            $UserFile['share_ttl_info'] = null;
                            $UserFile['share_password'] = null;
                            $UserFile['is_deleted'] = self::FILE_DELETED;
                            $UserFile['is_collaborated'] = self::FILE_UNCOLLABORATED;
                            self::createFileInfoRaw($info_file, $UserFile);
                        }
                    }
                } catch (\Exception $e) {
                    if (!is_dir($path)) {
                        unlink($info_file);
                    }
                }
            }
            closedir($h);
        }
    }

    /**
     * Помечает всех чилдреннов удаленного объекта как удаленные
     * @param \common\models\UserFiles $UserFileParent
     * @param bool $erase_nested
     * @param bool $has_no_any_files
     */
    public static function markChildrenAsDeleted($UserFileParent, $erase_nested, &$has_no_any_files)
    {
        if ($erase_nested) {
            /* Если труе (значит удаляется коллега из коллаборации) то нужно тупо удалить всю папку и удалить из бд всех ее чилдренов */
            /* запрос для выборки всех чилдренов (папок и файлов) овнера колллаборации */
            $has_no_any_files = true;
        } else {
            /* Иначе пройти по всем чилдренам и выставить отметку как удаленный (есть возможность восстановления) */
            $User = Users::getPathNodeFS($UserFileParent->user_id);
            $relativePath = self::getFullPath($UserFileParent);
            $file_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;
            $files_ids = [];
            self::markChildRecursively($file_name, $files_ids, $has_no_any_files);
            //var_dump($files_ids);
            if (sizeof($files_ids)) {

                /* Удаление из аплоадов */
                //UserUploads::deleteAll("(file_parent_id IN (" . implode(", ", $files_ids) . "))");
                UserUploads::deleteRecords(['user_id' => $User->user_id, 'file_parent_id' => $files_ids]);

                /* Обновление записей о файлах */
                $query = "UPDATE {{%user_files}} SET
                    last_event_type = :last_event_type,
                    is_deleted = :is_deleted,
                    is_shared = :is_shared,
                    share_hash = :share_hash,
                    share_group_hash = :share_group_hash,
                    share_lifetime = :share_lifetime,
                    share_ttl_info = :share_ttl_info,
                    share_created = :share_created,
                    share_password = :share_password,
                    is_collaborated = :is_collaborated,
                    file_size = CASE WHEN (is_folder = :TYPE_FOLDER) THEN 0 ELSE file_size END,
                    folder_children_count = CASE WHEN (is_folder = :TYPE_FOLDER) THEN 0 ELSE folder_children_count END
                WHERE file_id IN (" . implode(",", $files_ids) . ")";
                $res = Yii::$app->db->createCommand($query, [
                    'last_event_type'  => UserFileEvents::TYPE_DELETE,
                    'is_deleted'       => self::FILE_DELETED,
                    'is_shared'        => self::FILE_UNSHARED,
                    'share_hash'       => null,
                    'share_group_hash' => null,
                    'share_lifetime'   => null,
                    'share_ttl_info'   => null,
                    'share_created'    => null,
                    'share_password'   => null,
                    'is_collaborated'  => self::FILE_UNCOLLABORATED,
                    'TYPE_FOLDER'      => self::TYPE_FOLDER,
                ])->execute();
                //var_dump($res); exit;
                /*
                self::updateAll([
                    'last_event_type'  => UserFileEvents::TYPE_DELETE,
                    'is_deleted'       => self::FILE_DELETED,
                    'is_shared'        => self::FILE_UNSHARED,
                    'share_hash'       => null,
                    'share_group_hash' => null,
                    'share_lifetime'   => null,
                    'share_ttl_info'   => null,
                    'share_created'    => null,
                    'share_password'   => null,
                    'is_collaborated'  => self::FILE_UNCOLLABORATED,
                ], "file_id IN (" . implode(", ", $files_ids) . ")");
                */
            }
        }
    }

    /**
     * Рекурсивно находим все удаленые папки для объекта
     * @param \common\models\UserFiles $Child
     * @param array $parent_array
     */
    public static function findDeletedParents($Child, array &$parent_array)
    {
        /** @var \common\models\UserFiles $Parent */
        $Parent = self::find()
            ->alias('t1')
            ->select([
                't1.*',
                't2.file_uuid as file_parent_uuid',
            ])
            ->leftJoin('{{%user_files}} as t2', 't1.file_parent_id=t2.file_id')
            ->where(['t1.file_id' => $Child->file_parent_id])
            ->one();
        //var_dump($Parent->file_parent_uuid); exit;

        if ($Parent && $Parent->is_folder && $Parent->is_deleted) {
            $parent_array[] = $Parent;
            self::findDeletedParents($Parent, $parent_array);
        }
    }

    /**
     * Восстанавливаем рекурсивно удаленные папки при восстановлении файла
     *
     * @param array $Parents
     * @param \common\models\UserNode $UserNode
     * @return array
     */
    public static function unMarkParentsAsDeleted($Parents, $UserNode)
    {
        $event_data = [];
        $last = sizeof($Parents) - 1;
        //var_dump($Parents);
        $folder_for_rename_after_restore = [];

        for ($i=$last; $i>=0; $i--) {
            //var_dump($i);
            /** @var \common\models\UserFiles $Parent */
            $Parent = $Parents[$i];

            /* Восстанавливаем папку в БД */
            $Parent->is_deleted = self::FILE_UNDELETED;
            $Parent->save();

            /* Восстанавливаем папку в ФМ */

            $User = Users::getPathNodeFS($Parent->user_id);

            //++ Max 21.09.2018  ** Подготовка массива папок у которых нужно переименовать префикс Deleted на Restored
            /*
            if (preg_match("/\s\(Deleted [\d]{2}\-[\d]{2}\-[\d]{4} [\d]{2}\.[\d]{2}\.[\d]{2}\)/", $Parent->file_name, $ma)) {
                $oldRelative = self::getFullPath($Parent);
                $_old_folder_name = $User->_full_path . DIRECTORY_SEPARATOR . $oldRelative;
                $append_folder_name = " (Restored " . date('d-m-Y H.i.s') .")";
                $Parent->file_name = preg_replace("/\s\(Deleted [\d]{2}\-[\d]{2}\-[\d]{4} [\d]{2}\.[\d]{2}\.[\d]{2}\)/", "", $Parent->file_name);
                $Parent->file_name .= $append_folder_name;
            }
            */

            if (preg_match("/\s\(Deleted [\d]{2}\-[\d]{2}\-[\d]{4} [\d]{2}\.[\d]{2}\.[\d]{2}\)/", $Parent->file_name, $ma)) {
                $rename_action = true;
                $append_folder_name = " (Restored " . date('d-m-Y H.i.s') .")";
                $new_folder_name = preg_replace("/\s\(Deleted [\d]{2}\-[\d]{2}\-[\d]{4} [\d]{2}\.[\d]{2}\.[\d]{2}\)/", $append_folder_name, $Parent->file_name);
                if ($Parent->file_parent_id) {
                    $tmpParentParent = UserFiles::findOne(['file_id' => $Parent->file_parent_id]);
                    if ($tmpParentParent) {
                        $new_parent_folder_uuid = $tmpParentParent->file_uuid;
                    } else {
                        // если что то пошло не так и нет перента в базе, а он должен быть, тогда не будем переименовываь папку
                        $new_parent_folder_uuid = "";
                        $rename_action = false;
                    }
                } else {
                    $new_parent_folder_uuid = "";
                }

                if ($rename_action) {
                    $folder_for_rename_after_restore[] = [
                        'file_id'         => $Parent->file_id,
                        'old_folder_name' => $Parent->file_name,
                        'folder_uuid'     => $Parent->file_uuid,
                        'new_folder_name' => $new_folder_name,
                        'new_parent_folder_uuid' => $new_parent_folder_uuid,
                    ];
                }
            }
            //--

            $relativePath = self::getFullPath($Parent);
            $folder_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;
            $info_file = $folder_name . DIRECTORY_SEPARATOR . self::DIR_INFO_FILE;
            //++ Max 21.09.2012
            /*
            if (isset($_old_folder_name) && file_exists($_old_folder_name)) {
                FileSys::move($_old_folder_name, $folder_name);
                //$_old_folder_name
            }
            */
            //--
            if (!file_exists($folder_name)) {
                FileSys::mkdir($folder_name, self::CHMOD_DIR, true);
            }
            if (!file_exists($info_file)) {
                FileSys::touch($info_file, self::CHMOD_DIR, self::CHMOD_FILE);
            }
            self::createFileInfo($info_file, $Parent);

            /* Вычисление last_event_id */
            $last_event_id = self::last_event_id($Parent->file_id);
            if (!$last_event_id || $last_event_id == 0) {
                return [
                    'status' => false,
                    'info'   => "last_event_id error for file_uuid={$Parent->file_uuid}",
                ];
            }

            /* Создаем евент */
            $folderEventRestore                         = new UserFileEvents();
            $folderEventRestore->event_uuid             = md5($User->user_id . time() . microtime());
            $folderEventRestore->event_type             = UserFileEvents::TYPE_RESTORE;
            $folderEventRestore->event_timestamp        = time();
            $folderEventRestore->event_invisible        = UserFileEvents::EVENT_INVISIBLE;
            $folderEventRestore->last_event_id          = $last_event_id;
            $folderEventRestore->file_id                = $Parent->file_id;
            $folderEventRestore->diff_file_uuid         = null;
            $folderEventRestore->diff_file_size         = 0;
            $folderEventRestore->rev_diff_file_uuid     = null;
            $folderEventRestore->rev_diff_file_size     = 0;
            $folderEventRestore->file_hash_before_event = null;
            $folderEventRestore->file_hash              = null;
            $folderEventRestore->node_id                = $UserNode->node_id;
            $folderEventRestore->user_id                = $Parent->user_id;
            $folderEventRestore->file_name_before_event = $Parent->file_name;
            $folderEventRestore->file_name_after_event  = $Parent->file_name;
            $folderEventRestore->file_size_before_event = $Parent->file_size;
            $folderEventRestore->file_size_after_event  = $Parent->file_size;
            if (!$folderEventRestore->save()) {
                return [
                    'status' => false,
                    'info'   => $folderEventRestore->getErrors(),
                ];
            }
            $Parent->last_event_type = $folderEventRestore->event_type;
            $Parent->save();

            $event_data[] = [
                'operation' => "file_event",
                'data' => [
                    'event_id'           => $folderEventRestore->event_id,
                    'event_uuid'         => $folderEventRestore->event_uuid,
                    'last_event_id'      => $folderEventRestore->last_event_id,
                    'event_type'         => UserFileEvents::getType($Parent->last_event_type),
                    'event_type_int'     => $folderEventRestore->event_type,
                    'timestamp'          => $folderEventRestore->event_timestamp,
                    'hash'               => $folderEventRestore->file_hash,
                    'file_hash_before_event' => $folderEventRestore->file_hash_before_event,
                    'file_hash_after_event'  => $folderEventRestore->file_hash,
                    'file_hash'              => $folderEventRestore->file_hash,
                    'diff_file_uuid'     => $folderEventRestore->diff_file_uuid,
                    'diff_file_size'     => $folderEventRestore->diff_file_size,
                    'rev_diff_file_uuid' => $folderEventRestore->rev_diff_file_uuid,
                    'rev_diff_file_size' => $folderEventRestore->rev_diff_file_size,
                    'file_size_after_event'  => $folderEventRestore->file_size_after_event,
                    'file_size_before_event' => $folderEventRestore->file_size_before_event,
                    'is_folder'          => true,
                    'uuid'               => $Parent->file_uuid,
                    'file_id'            => $Parent->file_id,
                    'file_name'          => $Parent->file_name,
                    'file_parent_id'     => $Parent->file_parent_id,
                    'file_size'          => 0,
                    'user_id'            => $Parent->user_id,
                    'node_id'            => $folderEventRestore->node_id,
                    'parent_folder_uuid' => $Parent->file_parent_uuid,
                ],
            ];
        }

        /* Возврат данных */
        return [
            'status'     => true,
            'event_data' => $event_data,
            'folder_for_rename_after_restore' => $folder_for_rename_after_restore,
        ];
    }

    /**
     * Мнеяет принадлежность шаринга для чилдренов в случае перемещения
     * @param \common\models\UserFiles $UserFileParent
     * @return int
     */
    public static function changeChildrenCollaborationID($UserFileParent)
    {
        $query = "with recursive obj_tree as (
            SELECT
                file_id,
                file_parent_id
            FROM {{%user_files}}
            WHERE file_id = :file_id
              UNION ALL
            SELECT
                t.file_id,
                t.file_parent_id
            FROM {{%user_files}} AS t
            JOIN obj_tree ff on ff.file_id = t.file_parent_id
        )
        UPDATE {{%user_files}} SET collaboration_id = :collaboration_id WHERE file_id IN (SELECT file_id FROM obj_tree WHERE file_id != :file_id);";

        $res = Yii::$app->db->createCommand($query, [
            'file_id' => $UserFileParent->file_id,
            'collaboration_id' => $UserFileParent->collaboration_id,
        ])->query()->getRowCount();
        return $res;
    }

    /**
     * Мнеяет принадлежность шаринга для чилдренов в случае перемещения
     * @param \common\models\UserFiles $UserFileParent
     * @param string $share_group_hash
     * @param integer $collaboration_id
     */
    public static function changeChildrenGroupHash($UserFileParent, $share_group_hash, $collaboration_id)
    {
        $query = "with recursive obj_tree as (
            SELECT
                file_id,
                file_parent_id,
                file_uuid,
                file_name,
                file_size,
                file_lastatime,
                file_lastmtime,
                folder_children_count,
                is_folder,
                is_updated,
                is_deleted,
                collaboration_id,
                is_collaborated,
                is_owner,
                is_shared,
                share_hash,
                share_lifetime,
                share_ttl_info,
                share_password,
                last_event_uuid,
                text(file_name) AS file_path
            FROM {{%user_files}}
            WHERE file_id = :file_id
              UNION ALL
            SELECT
                t.file_id,
                t.file_parent_id,
                t.file_uuid,
                t.file_name,
                t.file_size,
                t.file_lastatime,
                t.file_lastmtime,
                t.folder_children_count,
                t.is_folder,
                t.is_updated,
                t.is_deleted,
                t.collaboration_id,
                t.is_collaborated,
                t.is_owner,
                t.is_shared,
                t.share_hash,
                t.share_lifetime,
                t.share_ttl_info,
                t.share_password,
                t.last_event_uuid,
                concat_ws(:separator, ff.file_path, t.file_name)
            FROM {{%user_files}} AS t
            JOIN obj_tree ff on ff.file_id = t.file_parent_id
            WHERE (t.is_shared = :FILE_SHARED)
        )
        SELECT * FROM obj_tree WHERE file_id != :file_id;";

        $UserFilesOldShare = self::findBySql($query, [
            'FILE_SHARED' => self::FILE_SHARED,
            'file_id'     => $UserFileParent->file_id,
            'separator'   => DIRECTORY_SEPARATOR,
        ])->asArray()->all();

        //var_dump($UserFilesOldShare); exit;
        if (sizeof($UserFilesOldShare)) {
            $User = Users::getPathNodeFS($UserFileParent->user_id);
            $upd_ids = [];
            foreach ($UserFilesOldShare as $share) {
                $upd_ids[] = $share['file_id'];

                $share['is_shared'] = self::FILE_UNSHARED;
                $file_name = $User->_full_path . DIRECTORY_SEPARATOR . $share['file_path'];
                if ($share['is_folder']) {
                    $file_name .= DIRECTORY_SEPARATOR . self::DIR_INFO_FILE;
                }
                //var_dump($file_name); exit;
                @unlink($file_name);
                self::createFileInfoRaw($file_name, $share);
            }
            self::updateAll(['is_shared' => self::FILE_UNSHARED], ['file_id' => $upd_ids]);
        }

        $query = "with recursive obj_tree as (
            SELECT
                file_id,
                file_parent_id
            FROM {{%user_files}}
            WHERE file_id = :file_id
              UNION ALL
            SELECT
                t.file_id,
                t.file_parent_id
            FROM {{%user_files}} AS t
            JOIN obj_tree ff on ff.file_id = t.file_parent_id
        )
        UPDATE {{%user_files}} SET
            collaboration_id = :collaboration_id,
            share_group_hash = :share_group_hash,
            is_shared = :is_shared,
            share_hash = " . ($share_group_hash ? "md5(concat(file_uuid, user_id, is_deleted))" : "null") . ",
            share_created = :share_created,
            share_lifetime = :share_lifetime,
            share_ttl_info = :share_ttl_info,
            share_password = :share_password
        WHERE file_id IN (SELECT file_id FROM obj_tree WHERE file_id != :file_id);";

        $res = Yii::$app->db->createCommand($query, [
            'file_id' => $UserFileParent->file_id,
            'collaboration_id' => $collaboration_id,
            'share_group_hash' => $share_group_hash,
            'is_shared' => self::FILE_UNSHARED,
            //'share_hash' => $share_group_hash ? new Expression("md5(concat(file_uuid,user_id))") : null,
            'share_created' => $UserFileParent->share_created,
            'share_lifetime' => $UserFileParent->share_lifetime,
            'share_ttl_info' => $UserFileParent->share_ttl_info,
            'share_password' => $UserFileParent->share_password,
        ])->query()->getRowCount();
    }

    /**
     * Мнеяет принадлежность шаринга для чилдренов в случае перемещения
     * @param \common\models\UserFiles $UserFileOrig
     * @param string $_full_path
     * @param integer $collaboration_id
     * @param \common\models\UserNode $ColleagueUserNode
     * @param array $event_store
     * @param \yii\redis\Connection $redis
     * @return array|false|null
     */
    public static function changeCollaboration($UserFileOrig, $_full_path, $collaboration_id, $ColleagueUserNode=null, &$event_store=array(), &$redis)
    {
        /* если передан ИД коллеги - то создаем папки */
        if ($ColleagueUserNode) {

            /* запрос для выборки всех чилдренов (папок и файлов) овнера колллаборации */
            $query =
                "with recursive obj_tree as (
                    SELECT
                        file_id,
                        file_parent_id,
                        file_uuid,
                        file_name,
                        file_size,
                        file_lastatime,
                        file_lastmtime,
                        folder_children_count,
                        is_folder,
                        is_updated,
                        is_deleted,
                        collaboration_id,
                        is_collaborated,
                        is_owner,
                        is_shared,
                        share_hash,
                        share_lifetime,
                        share_ttl_info,
                        share_password,
                        last_event_uuid,
                        text(file_name) AS file_path
                    FROM {{%user_files}}
                    WHERE file_id = :file_id
                      UNION ALL
                    SELECT
                        t.file_id,
                        t.file_parent_id,
                        t.file_uuid,
                        t.file_name,
                        t.file_size,
                        t.file_lastatime,
                        t.file_lastmtime,
                        t.folder_children_count,
                        t.is_folder,
                        t.is_updated,
                        t.is_deleted,
                        t.collaboration_id,
                        t.is_collaborated,
                        t.is_owner,
                        t.is_shared,
                        t.share_hash,
                        t.share_lifetime,
                        t.share_ttl_info,
                        t.share_password,
                        t.last_event_uuid,
                        concat_ws(:separator, ff.file_path, t.file_name)
                    FROM {{%user_files}} AS t
                    JOIN obj_tree ff on ff.file_id = t.file_parent_id
                )
                SELECT * FROM obj_tree WHERE file_id != :file_id;";
            $res = Yii::$app->db->createCommand($query, [
                'file_id'   => $UserFileOrig->file_id,
                'separator' => DIRECTORY_SEPARATOR,
            ])->queryAll();

            /* по полученным данным записываем (обновляем) информацию о файлах */
            if (sizeof($res)) {
                $upd_ids = [];
                //var_dump($res);
                foreach ($res as $UserFileOrigChild) {
                    //var_dump($UserFileOrigChild); exit;
                    $UserFileOrigChild['collaboration_id'] = $collaboration_id;
                    $upd_ids[] = $UserFileOrigChild['file_id'];

                    $file_name = $_full_path . DIRECTORY_SEPARATOR . $UserFileOrigChild['file_path'];
                    if ($UserFileOrigChild['is_folder']) {
                        $file_name .= DIRECTORY_SEPARATOR . self::DIR_INFO_FILE;
                    }
                    //var_dump($file_name); exit;
                    $dir_name = dirname($file_name);
                    if (!file_exists($dir_name)) {
                        FileSys::mkdir($dir_name, self::CHMOD_DIR, true);
                    }
                    self::createFileInfoRaw($file_name, $UserFileOrigChild);
                }
                self::updateAll([
                    'collaboration_id' => $collaboration_id,
                    'is_owner'         => self::IS_OWNER,
                ], ['file_id' => $upd_ids]);
            }


            $answer = null;

            /* Проверим что у колеги не существует в корне папки с таким же именем. */
            /* Если существует, то придется поменять имя нашей создаваемой */
            $root_folder_name = $UserFileOrig->file_name;
            $i_append = 1;
            while (self::findOne([
                'file_name' => $root_folder_name,
                'file_parent_id' => self::ROOT_PARENT_ID,
                'user_id' => $ColleagueUserNode->user_id
            ])) {
                $root_folder_name .= "({$i_append})";
                $i_append++;
            }

            /* Создаем записи о папках и файлах у колеги через постгре-функцию */
            $query = "SELECT * FROM copy_collaboration_to_user(
                :collaboration_id,
                :user_id,
                :collaboration_name,
                :separator) LIMIT 1";
            $res2 = Yii::$app->db->createCommand($query, [
                'collaboration_id'   => $collaboration_id,
                'user_id'            => $ColleagueUserNode->user_id,
                'collaboration_name' => $root_folder_name,
                'separator'          => DIRECTORY_SEPARATOR,
            ])->queryOne();

            if ($res2 && is_array($res2)) {
                return [
                    'event_group_id'   => $res2['event_group_id'],
                    'root_folder_name' => $root_folder_name,
                ];
            } else {
                return null;
            }

            /* формируем евенты и создаем папки у коллеги на основании этих записей */
//            $event_store_local = [];
//            if (sizeof($res2)) {
//                $User = Users::getPathNodeFS($ColleagueUserNode->user_id);
//                foreach ($res2 as $UserFileCopied) {
//
//                    /* создаем файлы и папки */
//                    $file_name = $User->_full_path . DIRECTORY_SEPARATOR . $UserFileCopied['file_path'];
//                    if ($UserFileCopied['is_folder']) {
//                        $file_name .= DIRECTORY_SEPARATOR . self::DIR_INFO_FILE;
//                    }
//                    //var_dump($file_name); exit;
//                    $dir_name = dirname($file_name);
//                    if (!file_exists($dir_name)) {
//                        FileSys::mkdir($dir_name, self::CHMOD_DIR, true);
//                    }
//                    self::createFileInfoRaw($file_name, $UserFileCopied);
//
//                    /* Собираем набор евентов */
//                    $event_store_local[] = [
//                        'operation' => "file_event",
//                        'data' => [
//                            'event_id'               => $UserFileCopied['event_id'],
//                            'event_uuid'             => $UserFileCopied['event_uuid'],
//                            'erase_nested'           => ($UserFileCopied['erase_nested'] == UserFileEvents::ERASE_NESTED_TRUE),
//                            'last_event_id'          => $UserFileCopied['last_event_id'],
//                            'event_type'             => UserFileEvents::getType($UserFileCopied['event_type']),
//                            'event_type_int'         => $UserFileCopied['event_type'],
//                            'timestamp'              => $UserFileCopied['event_timestamp'],
//                            'hash'                   => $UserFileCopied['file_hash'],
//                            'file_hash_before_event' => $UserFileCopied['file_hash_before_event'],
//                            'file_hash_after_event'  => $UserFileCopied['file_hash'],
//                            'file_hash'              => $UserFileCopied['file_hash'],
//                            'diff_file_uuid'         => $UserFileCopied['diff_file_uuid'],
//                            'diff_file_size'         => $UserFileCopied['diff_file_size'],
//                            'rev_diff_file_uuid'     => $UserFileCopied['rev_diff_file_uuid'],
//                            'rev_diff_file_size'     => $UserFileCopied['rev_diff_file_size'],
//                            'is_folder'              => ($UserFileCopied['is_folder'] == UserFiles::TYPE_FOLDER),
//                            'uuid'                   => $UserFileCopied['file_uuid'],
//                            'file_id'                => $UserFileCopied['file_id'],
//                            'file_parent_id'         => $UserFileCopied['file_parent_id'],
//                            'file_name'              => $UserFileCopied['file_name'],
//                            'file_size'              => $UserFileCopied['file_size'],
//                            'user_id'                => $ColleagueUserNode->user_id,
//                            'node_id'                => $ColleagueUserNode->node_id,
//                            'parent_folder_uuid'     => $UserFileCopied['parent_folder_uuid'],
//                        ],
//                    ];
//
//                    /* Отправка пачки евентов на редис */
//                    if (sizeof($event_store_local) >= 100) {
//                        if (!in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT])) {
//                            try {
//                                $redis->publish("user:{$ColleagueUserNode->user_id}:fs_events", Json::encode($event_store_local));
//                                $redis->save();
//                                unset($event_store_local);
//                                $event_store_local = [];
//                            } catch (\Exception $e) {}
//                        }
//
//                    }
//
//                }
//
//                /* Отправка остатков евентов на редис */
//                if (!in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT])) {
//                    try {
//                        $redis->publish("user:{$ColleagueUserNode->user_id}:fs_events", Json::encode($event_store_local));
//                        $redis->save();
//                        unset($event_store_local);
//                    } catch (\Exception $e) {}
//                }
//
//            }

        }
        return null;
    }

    /**
     * Возвращает ссылку на шаринг в зависимости от того папка это или файл
     * @param string $share_hash
     * @param integer|bool $is_folder
     * @param integer|null $file_id
     * @return string
     */
    public static function getShareLink($share_hash, $is_folder, $file_id=null)
    {
        if ($is_folder) {
            if ($file_id) {
                return Yii::getAlias('@frontendWeb') . "/folder/{$share_hash}/{$file_id}";
            } else {
                return Yii::getAlias('@frontendWeb') . "/folder/{$share_hash}";
            }
        } else {
            return Yii::getAlias('@frontendWeb') . "/file/{$share_hash}";
        }
    }

    /**
     * Функция получает массив данных для миме типов
     * @return array
     */
    public static function ext_to_mime() {
        # Returns the system MIME type mapping of MIME types to extensions, as defined in /etc/mime.types (considering the first
        # extension listed to be canonical).
        $out = array();
        $file_mime = Yii::getAlias('@common') . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mime.types';
        //var_dump($file_mime); exit;
        if (file_exists($file_mime)) {
            if (($file = fopen($file_mime, 'r')) !== false) {
                while (($line = fgets($file)) !== false) {
                    $line = trim(preg_replace('/#.*/', '', $line));
                    if (!$line)
                        continue;
                    $parts = preg_split('/\s+/', $line);
                    if (count($parts) == 1)
                        continue;
                    $type = array_shift($parts);
                    foreach ($parts as $v) {
                        if (is_string($v) && !isset($out[$v])) {
                            $out[$v] = $type;
                        }
                    }
                }
                fclose($file);
            }
        }
        //var_dump($out); exit;
        return $out;
    }

    /**
     * @param string $path
     * @return string
     */
    public static function fileMime($path)
    {
        $ext_to_mime = Yii::$app->cache->get('ext_to_mime');
        if (!$ext_to_mime) {
            $ext_to_mime = self::ext_to_mime();
            Yii::$app->cache->set('ext_to_mime', $ext_to_mime);
        }

        setlocale(LC_ALL, 'en_US.UTF-8');
        $ext = mb_strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (isset($ext_to_mime[$ext])) {
            return $ext_to_mime[$ext];
        } else {
            return 'application/octet-stream';
        }
    }

    /**
     * @param \common\models\Users $User
     * @param \common\models\UserFiles $UserFile
     * @param integer $diff_size
     * @param integer $diff_count
     * @return bool
     */
    public static function update_parents_size_and_count(&$User, &$UserFile, $diff_size, $diff_count)
    {
        return true; //отключили подсчет при евентах будет по запросу
        if ($UserFile->file_parent_id) {
            $res = Yii::$app->db->createCommand("SELECT * FROM update_parents_size_and_count(:file_id, :diff_size, :diff_count, :separator);", [
                'file_id'    => $UserFile->file_id,
                'diff_size'  => $diff_size,
                'diff_count' => $diff_count,
                'separator'  => DIRECTORY_SEPARATOR,
            ])->queryAll();
            //return true;

            if (is_array($res)) {

                foreach ($res as $v) {
                    if (isset($v['full_path'], $v['file_size'], $v['folder_children_count'])) {
                        $folder_info_file = $User->_full_path . DIRECTORY_SEPARATOR . $v['full_path'] . DIRECTORY_SEPARATOR . UserFiles::DIR_INFO_FILE;
                        if (file_exists($folder_info_file)) {

                            try {
                                $ParentUserFile = @unserialize(file_get_contents($folder_info_file));
                                if (isset($ParentUserFile['file_id'])) {
                                    $ParentUserFile['file_size'] = intval($v['file_size']);
                                    $ParentUserFile['folder_children_count'] = intval($v['folder_children_count']);
                                    self::createFileInfoRaw($folder_info_file, $ParentUserFile);
                                }
                            } catch (\Exception $e) {}

                        }
                    }
                }

            }

        }
        return true;
    }

    /**
     * @param array $files_ids
     * @return array
     */
    public static function get_size_and_count_for(array $files_ids)
    {
        $files_ids = array_map(intval, $files_ids);
        $res = Yii::$app->db->createCommand("
            WITH RECURSIVE obj_tree AS (
                SELECT
                    file_id,
                    file_parent_id,
                    is_folder,
                    file_size
                FROM {{%user_files}}
                WHERE file_id IN (" . implode(', ', $files_ids) . ")
              UNION ALL
                SELECT
                    t.file_id,
                    t.file_parent_id,
                    t.is_folder,
                    t.file_size
                FROM {{%user_files}} as t
                JOIN obj_tree as ff ON t.file_parent_id = ff.file_id
            )
            SELECT coalesce(sum(file_size),0) as size, count(*) as count FROM obj_tree WHERE is_folder=0 LIMIT 1;
        ")->queryOne();

        if (isset($res['size'], $res['count'])) {
            return array_map('intval', $res);
        } else {
            return [
                'size'  => 0,
                'count' => 0,
            ];
        }

    }

    /**
     * @param string $file_name
     * @param \common\models\UserFiles $UserFile
     */
    public static function createFileInfo($file_name, $UserFile)
    {
        FileSys::fwrite($file_name, serialize([
            'file_id'           => $UserFile->file_id,
            'file_parent_id'    => $UserFile->file_parent_id,
            'file_uuid'         => $UserFile->file_uuid,
            'last_event_uuid'   => $UserFile->last_event_uuid,
            'file_name'         => $UserFile->file_name,
            'file_size'         => $UserFile->file_size,
            'folder_children_count' => $UserFile->folder_children_count,
            'file_updated'      => $UserFile->is_updated == self::FILE_UPDATED ? true : false,
            'file_deleted'      => $UserFile->is_deleted == self::FILE_DELETED ? true : false,
            'collaboration_id'  => $UserFile->collaboration_id,
            'file_collaborated' => $UserFile->is_collaborated == self::FILE_COLLABORATED ? true : false,
            'cbl_is_owner'      => $UserFile->is_owner,
            'file_shared'       => $UserFile->is_shared == self::FILE_SHARED ? true : false,
            'share_hash'        => $UserFile->share_hash,
            'share_lifetime'    => $UserFile->share_lifetime,
            'share_ttl_info'    => $UserFile->share_ttl_info,
            'share_password'    => $UserFile->share_password,
            'file_lastatime'    => $UserFile->file_lastatime,
            'file_lastmtime'    => $UserFile->file_lastmtime,
        ]), self::CHMOD_FILE);
    }

    /**
     * @param string $file_name
     * @param array $UserFile
     */
    public static function createFileInfoRaw($file_name, $UserFile)
    {
        if (!isset($UserFile['is_owner']) && isset($UserFile['cbl_is_owner']))   { $UserFile['is_owner']        = $UserFile['cbl_is_owner']; }
        if (!isset($UserFile['is_updated']) && isset($UserFile['file_updated'])) { $UserFile['is_updated']      = $UserFile['file_updated'] ? self::FILE_UPDATED : self::FILE_UNUPDATED; }
        if (!isset($UserFile['is_shared']) && isset($UserFile['file_shared']))   { $UserFile['is_shared']       = $UserFile['file_shared'] ? self::FILE_SHARED : self::FILE_UNSHARED; }
        if (!isset($UserFile['is_deleted']) && isset($UserFile['file_deleted'])) { $UserFile['is_deleted']      = $UserFile['file_deleted'] ? self::FILE_DELETED : self::FILE_UNDELETED; }
        if (!isset($UserFile['is_collaborated']) && isset($UserFile['file_collaborated'])) { $UserFile['is_collaborated'] = $UserFile['file_collaborated'] ? self::FILE_COLLABORATED : self::FILE_UNCOLLABORATED; }

        //var_dump($UserFile);
        FileSys::fwrite($file_name, serialize([
            'file_id'               => $UserFile['file_id'],
            'file_parent_id'        => $UserFile['file_parent_id'],
            'file_uuid'             => $UserFile['file_uuid'],
            'last_event_uuid'       => (isset($UserFile['last_event_uuid']))
                ? $UserFile['last_event_uuid']
                : (isset($UserFile['event_uuid'])
                    ? $UserFile['event_uuid']
                    : null),
            'file_name'             => $UserFile['file_name'],
            'file_size'             => $UserFile['file_size'],
            'folder_children_count' => $UserFile['folder_children_count'],
            'file_updated'          => $UserFile['is_updated'] == self::FILE_UPDATED ? true : false,
            'file_deleted'          => $UserFile['is_deleted'] == self::FILE_DELETED ? true : false,
            'collaboration_id'      => $UserFile['collaboration_id'],
            'file_collaborated'     => $UserFile['is_collaborated'] == self::FILE_COLLABORATED ? true : false,
            'cbl_is_owner'          => $UserFile['is_owner'],
            'file_shared'           => $UserFile['is_shared'] == self::FILE_SHARED ? true : false,
            'share_hash'            => $UserFile['share_hash'],
            'share_lifetime'        => $UserFile['share_lifetime'],
            'share_ttl_info'        => $UserFile['share_ttl_info'],
            'share_password'        => $UserFile['share_password'],
            'file_lastatime'        => isset($UserFile['file_lastatime']) ? $UserFile['file_lastatime'] : 0,
            'file_lastmtime'        => isset($UserFile['file_lastmtime']) ? $UserFile['file_lastmtime'] : 0,
        ]), self::CHMOD_FILE);
    }

    /**
     * Возвращает дополнительную информацию по файлу или папке,
     * необходимую для прорисовки объектов в elFinder
     * @param $path
     * @return mixed
     */
    public static function getFileShareInfo($path)
    {
        $ret['shared_element']    = "";
        //$ret['file_path']       = $path;
        $ret['file_uuid']         = null;
        $ret['last_event_uuid']   = null;
        $ret['file_id']           = null;
        $ret['file_parent_id']    = null;
        $ret['file_name']         = null;
        $ret['extension']         = null;
        $ret['file_size']         = 0;
        $ret['file_updated']      = false;
        $ret['file_deleted']      = false;
        $ret['file_shared']       = false;
        $ret['file_collaborated'] = false;
        $ret['collaboration_id']  = null;
        $ret['cbl_is_owner']      = self::IS_OWNER;
        $ret['share_hash']        = null;
        $ret['share_lifetime']    = null;
        $ret['share_ttl_info']    = null;
        $ret['share_password']    = null;
        $ret['share_link']        = "";
        $ret['file_lastatime']    = 0;
        $ret['file_lastmtime']    = 0;
        $ret['mime']              = "application/octet-stream";
        // +++ Нужен что бы в ФМ происходил релоад файлов, даже если сами файлы не поменялиь (для отображения и скрытия удаленных)
        // если так то пиздец - при обновлении страницы задубливаются папки в левой части ФМ
        //$ret['randomizer'] = md5(microtime());
        // а если так то норм (fm_randomizer устанавливается заново в
        // elFinder.class.php if (isset($_GET['reload'])) { Yii::$app->session->set('fm_randomizer', md5(microtime())); }
        $ret['randomizer'] = Yii::$app->session->get('fm_randomizer', null);
        // --- Ебанный рандомайзер, попил крови
        if (file_exists($path)) {
            //if ($ret['mime'] == "directory") {
            if (is_dir($path)) {
                $infoFile = $path . DIRECTORY_SEPARATOR . self::DIR_INFO_FILE;
                $ret['is_folder'] = true;
                $ret['mime'] = 'directory';
                $ret['children_count'] = 0;
            } else {
                $infoFile = $path;
                $ret['is_folder'] = false;
                //$ret['mime'] = self::fileMime($path);
                $ret['mime'] = FileSys::file_mime($path);
            }

            if (file_exists($infoFile)) {
                try {
                    $tmp = @unserialize(file_get_contents($infoFile));
                    //var_dump($tmp);//exit;
                    if (isset($tmp['file_uuid'])) $ret['file_uuid'] = $tmp['file_uuid'];
                    if (isset($tmp['last_event_uuid'])) $ret['last_event_uuid'] = $tmp['last_event_uuid'];
                    if (isset($tmp['file_id'])) $ret['file_id'] = $tmp['file_id'];
                    if (isset($tmp['file_parent_id'])) $ret['file_parent_id'] = $tmp['file_parent_id'];
                    $ret['file_name'] = isset($tmp['file_name']) ? $tmp['file_name'] : basename($path);
                    if (isset($tmp['file_size'])) $ret['file_size'] = $tmp['file_size'];
                    if (isset($tmp['uploaded']['upload_size'])) { $ret['file_size'] = $tmp['uploaded']['upload_size']; }
                    if (isset($tmp['folder_children_count']) && $ret['is_folder'])
                        $ret['children_count'] = $tmp['folder_children_count'];
                    //if ($ret['is_folder']) { $ret['file_size'] = '-'; }
                    if (isset($tmp['file_updated']) && !$ret['is_folder'])
                        $ret['file_updated'] = $tmp['file_updated'];
                    if (isset($tmp['file_deleted'])) $ret['file_deleted'] = $tmp['file_deleted'];
                    if (isset($tmp['file_shared'])) $ret['file_shared'] = $tmp['file_shared'];
                    if (isset($tmp['file_collaborated'])) $ret['file_collaborated'] = $tmp['file_collaborated'];
                    if (isset($tmp['cbl_is_owner'])) $ret['cbl_is_owner'] = $tmp['cbl_is_owner'];
                    if (isset($tmp['share_hash'])) $ret['share_hash'] = $tmp['share_hash'];
                    if (isset($tmp['share_lifetime'])) $ret['share_lifetime'] = $tmp['share_lifetime'];
                    if (isset($tmp['share_ttl_info'])) $ret['share_ttl_info'] = $tmp['share_ttl_info'];
                    if (isset($tmp['share_password'])) $ret['share_password'] = $tmp['share_password'];
                    if (isset($tmp['file_lastatime'])) $ret['file_lastatime'] = $tmp['file_lastatime'];
                    if (isset($tmp['file_lastmtime'])) $ret['file_lastmtime'] = $tmp['file_lastmtime'];
                    if (isset($tmp['collaboration_id'])) $ret['collaboration_id'] = $tmp['collaboration_id'];

                    //if ($ret['share_ttl_info'] === null) { $ret['share_ttl_info'] = self::TTL_WITHOUTEXPIRY; }

                    if ($ret['share_lifetime'] !== null) {
                        $share_lifetime_timestamp = strtotime($ret['share_lifetime']);
                        if ($share_lifetime_timestamp < time()) {
                            $ret['file_shared'] = false;
                            $ret['share_hash'] = null;
                        }
                    }

                    if ($ret['file_shared'] && $ret['share_hash']) {
                        $ret['share_link'] = self::getShareLink($ret['share_hash'], $ret['is_folder']);
                    }
                } catch (\Exception $e) {}
            }
            if (file_exists($path . DIRECTORY_SEPARATOR . UserFiles::DIR_COPYING_IN_PROGRESS)) {
                $ret['copying_in_progress'] = true;
            } else {
                $ret['copying_in_progress'] = false;
            }

            if ($ret['file_shared']) { $ret['shared_element'] = "shared_element"; }
            if ($ret['file_collaborated']) { $ret['shared_element'] = "collaborated_element"; }
            if ($ret['file_shared'] && $ret['file_collaborated']) { $ret['shared_element'] = "shared_element collaborated_element"; }

            /* */
            $pathInfo = FileSys::pathinfo($path);
            if (isset($pathInfo['extension'])) {
                $ret['extension'] = $pathInfo['extension'];
            }


        }
        //var_dump($ret);
        return $ret;
    }

    /**
     * @param integer $folder_id
     * @param bool $include_deleted
     * @return array
     */
    public static function countSizeByBD($folder_id, $include_deleted=false)
    {
        $ret = Yii::$app->db->createCommand("SELECT * FROM get_count_children_for(:folder_id, :include_deleted) LIMIT 1", [
            'folder_id' => $folder_id,
            'include_deleted' => $include_deleted,
        ])->queryOne();

        if (!isset($ret['size'])) { $ret['size'] = 0; }
        if (!isset($ret['count_files'])) { $ret['count_files'] = 0; }
        if (!isset($ret['count_folders'])) { $ret['count_folders'] = 0; }
        if (!isset($ret['count_children'])) { $ret['count_children'] = 0; }

        return $ret;
    }

    /**
     * @param string $path
     * @param integer $stop_count_after_children
     * @param array $ret
     * @return array
     */
    public static function countSizeByFS($path, $stop_count_after_children=0, &$ret=array()) {
        if (!isset($ret['size'])) { $ret['size'] = 0; }
        if (!isset($ret['count_files'])) { $ret['count_files'] = 0; }
        if (!isset($ret['count_folders'])) { $ret['count_folders'] = -1; }
        if (!isset($ret['count_children'])) { $ret['count_children'] = 0; }

        /* Остановим подсчет если установлен лимит чилдренов после которых не нужно уже считать */
        if ($stop_count_after_children > 0 && $ret['count_children'] >= $stop_count_after_children) {
            return $ret;
        }

        //var_dump(file_exists($path));
        if (file_exists($path)) {
            //var_dump($path);
            if (!is_dir($path)) {
                if (FileSys::basename($path) !== self::DIR_INFO_FILE) {
                    $info = self::getFileShareInfo($path);
                    //var_dump($info);
                    if (!$info['file_deleted']) { $ret['size'] += intval($info['file_size']);}
                    if (!$info['file_deleted']) { $ret['count_files']++; }
                }
            } else {
                if (file_exists($path . DIRECTORY_SEPARATOR . self::DIR_INFO_FILE)) {
                    $info = self::getFileShareInfo($path . DIRECTORY_SEPARATOR . self::DIR_INFO_FILE);
                    if (!$info['file_deleted']) { $ret['count_folders']++; }
                }
            }
        }

        $ret['count_children'] = $ret['count_files'] + $ret['count_folders'];

        if (is_dir($path)) {
            if ($h = @opendir($path)) {
                while (false !== ($f = readdir($h))) {

                    if (!file_exists($path . DIRECTORY_SEPARATOR . self::DIR_INFO_FILE))
                        continue;

                    $info2 = self::getFileShareInfo($path);
                    if ($info2['file_deleted'])
                        continue;

                    if (($f === '.') || ($f === '..') || ($f === self::DIR_INFO_FILE))
                        continue;

                    $newpath = $path . DIRECTORY_SEPARATOR . $f;
                    self::countSizeByFS($newpath, $stop_count_after_children, $ret);

                }
                closedir($h);
            }
        }

        return $ret;
    }

}
