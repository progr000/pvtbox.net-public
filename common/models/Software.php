<?php

namespace common\models;

use Yii;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;
use common\helpers\FileSys;

/**
 * This is the model class for table "{{%software}}".
 *
 * @property string $software_id
 * @property string $software_type
 * @property string $software_description
 * @property string $software_file_name
 * @property string $software_url
 * @property string $software_program_type
 * @property string $software_version
 * @property string $software_created
 * @property string $software_updated
 * @property integer $software_status
 * @property integer $software_sort
 * @property \yii\web\UploadedFile $software_file
 * @property string $_old_software_version
 * @property string $_old_software_type
 */
class Software extends ActiveRecord
{
    private static $CACHE_TTL = 3600;

    const TYPE_WINDOWS  = 'windows';
    const TYPE_LINUX    = 'linux';
    const TYPE_LINUX_32 = 'linux32';
    const TYPE_LINUX_64 = 'linux64';
    const TYPE_DEBIAN   = 'debian';
    const TYPE_UBUNTU   = 'ubuntu';
    const TYPE_CENTOS   = 'centos';
    const TYPE_SUSE     = 'suse';
    const TYPE_MAC      = 'mac';
    const TYPE_IOS      = 'ios';
    const TYPE_ANDROID  = 'android';
    const TYPE_PORTABLE = 'portable';

    const STATUS_ACTIVE   = 1;
    const STATUS_DEACTIVE = 0;

    const PROGRAM_TYPE_FILE = 'file';
    const PROGRAM_TYPE_URL = 'url';

    public $software_file;

    public $_old_software_type;
    public $_old_software_version;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%software}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'software_created',
                'updatedAtAttribute' => 'software_updated',
                'value' => function() { return date(SQL_DATE_FORMAT); }
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['software_description', 'software_type', /*'software_version'*/], 'required'],
            [
                ['software_type'],
                'in', 'range' => [
                    self::TYPE_WINDOWS,
                    self::TYPE_LINUX,
                    self::TYPE_LINUX_32,
                    self::TYPE_LINUX_64,
                    self::TYPE_MAC,
                    self::TYPE_IOS,
                    self::TYPE_ANDROID,
                    self::TYPE_PORTABLE,

                    self::TYPE_DEBIAN,
                    self::TYPE_UBUNTU,
                    self::TYPE_CENTOS,
                    self::TYPE_SUSE,
                ]
            ],
            [['software_type'], 'default', 'value' => self::TYPE_WINDOWS],
            [['software_status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DEACTIVE]],
            [['software_status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['software_program_type'], 'in', 'range' => [self::PROGRAM_TYPE_FILE, self::PROGRAM_TYPE_URL]],
            [['software_program_type'], 'default', 'value' => self::PROGRAM_TYPE_FILE],
            //[['software_type'], 'string'],
            //[['software_status'], 'integer'],
            [['software_description', 'software_file_name', 'software_url'], 'string', 'max' => 255],
            [['software_url'], 'url'],
            [['software_sort'], 'integer', 'min' => 0],
            [['software_version'], 'string', 'max' => 50],
            ['software_version', 'validateVersion', 'skipOnEmpty' => false, 'skipOnError' => false],
            /*
            [
                ['software_url', 'software_file_name'],
                'unique',
                'targetClass' => '\common\models\Software',
                'targetAttribute' => ['software_url', 'software_file_name'],
                'message' => 'Такая версия программы уже загружена.'
            ],
            */
            [
                ['software_file'],
                'file',
                'skipOnEmpty' => true,
                'extensions' => ['exe', 'zip', 'rar', 'apk'],
                'checkExtensionByMimeType' => false,
            ],
            ['software_url', 'checkNotEmptyUrl', 'skipOnEmpty' => false, 'skipOnError' => false],
            ['software_file', 'checkNotEmptyFile', 'skipOnEmpty' => false, 'skipOnError' => false],
        ];
    }

    /**
     * @param string $attribute
     * @param array $params
     */
    public function checkNotEmptyUrl($attribute, $params)
    {
        if (!$this->software_url && ($this->software_program_type == 'url')) {
            $this->addError($attribute, 'You must fill in the URL or upload the file.');
        }
    }

    /**
     * @param string $attribute
     * @param array $params
     */
    public function checkNotEmptyFile($attribute, $params)
    {
        if ($this->isNewRecord) {
            if (!UploadedFile::getInstance($this, 'software_file') && ($this->software_program_type == 'file')) {
                $this->addError($attribute, 'You must download the file or fill in the URL.');
            }
        }
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateVersion($attribute, $params)
    {
        if (mb_strrpos($this->$attribute, "..") !== false) {
            $this->addError($attribute, 'Incorrect program version format.');
        }
        if (!preg_match('/^[0-9a-z\(\)\.\s]{1,50}$/i', $this->$attribute)) {
            $this->addError($attribute, 'Incorrect program version format (allowed [a-z0-9().\s]');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'software_id'           => 'Id',
            'software_type'         => 'OS Type',
            'software_description'  => 'Description',
            'software_file_name'    => 'Path',
            'software_url'          => 'URL',
            'software_program_type' => 'Url or File',
            'software_version'      => 'Program version',
            'software_created'      => 'Created',
            'software_updated'      => 'Updated',
            'software_status'       => 'Status',
            'software_file'         => 'Program file',
            'software_sort'         => 'Sorting index',
        ];
    }

    /**
     * returns list of types in array
     *
     * @return array
     */
    public static function linkTypes()
    {
        return [
            self::TYPE_WINDOWS  => 'Windows OS',
            self::TYPE_LINUX    => 'Linux OS',
            self::TYPE_LINUX_32 => 'Linux OS [x32]',
            self::TYPE_LINUX_64 => 'Linux OS [x64]',
            self::TYPE_DEBIAN   => 'Linux OS [Debian]',
            self::TYPE_UBUNTU   => 'Linux OS [Ubuntu]',
            self::TYPE_CENTOS   => 'Linux OS [CentOS]',
            self::TYPE_SUSE     => 'Linux OS [OpenSUSE]',
            self::TYPE_MAC      => 'Mac OS',
            self::TYPE_IOS      => 'iPhone/iPad iOS',
            self::TYPE_ANDROID  => 'Android OS',
            self::TYPE_PORTABLE => 'Portable Version',
        ];
    }

    /**
     * return type
     * @param string $link_type
     * @param bool $for_frontend
     * @return string | null
     */
    public static function getType($link_type, $for_frontend=false)
    {
        $labels = self::linkTypes();

        if (isset($labels[$link_type])) {
            if (!$for_frontend) {
                return $labels[$link_type];
            }

            $tmp = explode('[', $labels[$link_type]);
            return trim($tmp[0]);
        }

        return null;
    }

    /**
     * returns list of actives in array
     *
     * @return array
     */
    public static function linkStatuses()
    {
        return [
            self::STATUS_ACTIVE   => 'Active',
            self::STATUS_DEACTIVE => 'Deactivated',
        ];
    }

    /**
     * return status
     * @param integer $link_status
     *
     * @return string | null
     */
    public static function getStatus($link_status)
    {
        $labels = self::linkStatuses();
        return isset($labels[$link_status]) ? $labels[$link_status] : null;
    }

    /**
     * returns list of actives in array
     *
     * @return array
     */
    public static function listProgramTypes()
    {
        return [
            self::PROGRAM_TYPE_FILE => 'Application',
            self::PROGRAM_TYPE_URL  => 'Link to Application',
        ];
    }

    /**
     * return software_program_type
     * @param string $software_program_type
     *
     * @return string | null
     */
    public static function getProgramType($software_program_type)
    {
        $labels = self::listProgramTypes();
        return isset($labels[$software_program_type]) ? $labels[$software_program_type] : null;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            // Папка с версией (переименование)
            if (!$this->isNewRecord && $this->software_file_name) {
                if (($this->_old_software_version !== $this->software_version) ||
                    ($this->_old_software_type !== $this->software_type)
                ) {
                    $old_path = dirname(realpath(Yii::$app->params['uploadSoftwareDir'] . $this->software_file_name));
                    if (file_exists($old_path)) {
                        FileSys::move(
                            $old_path,
                            Yii::$app->params['uploadSoftwareDir'] . $this->software_type . "/" . $this->software_version
                        );
                        $this->software_file_name = $this->software_type . "/" . $this->software_version . "/" . basename($this->software_file_name);
                    }
                }
            }

            // Файл
            if ($this->software_file) {
                if ($this->software_file_name !== "") {
                    FileSys::remove(realpath(Yii::$app->params['uploadSoftwareDir'] . $this->software_file_name));
                }
                $this->software_file_name = $this->software_type . "/" . $this->software_version . "/" . $this->software_file->baseName . '.' . $this->software_file->extension;
                FileHelper::createDirectory(dirname(Yii::$app->params['uploadSoftwareDir'] . $this->software_file_name), 0777, true);
                if (!$this->software_file->saveAs(Yii::$app->params['uploadSoftwareDir'] . $this->software_file_name, false)) {
                    Yii::$app->session->setFlash('error', 'Failed move uploaded file.');
                    return false;
                }
            }

            // Статус
            if ($this->software_status == self::STATUS_ACTIVE) {
                /*
                if ($this->isNewRecord) {
                    self::updateAll(
                        ['software_status' => self::STATUS_DEACTIVE],
                        ['software_type' => $this->software_type]
                    );
                } else {
                    self::updateAll(
                        ['software_status' => self::STATUS_DEACTIVE],
                        '(software_type = :software_type) AND (software_id != :software_id)',
                        ['software_type' => $this->software_type, 'software_id' => $this->software_id]
                    );
                }
                */
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

        /* @var \common\models\Software $software */
        if ($this->software_file_name !== "") {
            FileSys::remove(dirname(realpath(Yii::$app->params['uploadSoftwareDir'] . $this->software_file_name)), true);
        }
        if ($this->software_status == self::STATUS_ACTIVE) {
            $software = self::find()
                ->where(['software_type' => $this->software_type])
                ->orderBy(['software_version' => SORT_DESC])
                ->limit(1)->all();
            if ($software) {
                $software[0]->software_status = self::STATUS_ACTIVE;
                $software[0]->save();
            }
        }

        self::invalidateCache();
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->_old_software_type    = $this->software_type;
        $this->_old_software_version = $this->software_version;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        self::invalidateCache();
    }

    /**
     * Invalidate cache
     */
    public static function invalidateCache()
    {
        TagDependency::invalidate(Yii::$app->cache, [
            'Software.findOtherVersionSoftware',
        ]);
    }

    /**
     * @return array
     */
    public static function findOtherVersionSoftware()
    {
        /*
        SELECT t1.*
        FROM {{%download_links}} as t1
        LEFT JOIN {{%download_links}} as t2
           ON (t1.link_type = t2.link_type)
              AND (t1.link_version < t2.link_version)
              AND (t2.link_status>0)
        WHERE (t2.link_version IS NULL)
        AND (t1.link_status>0)
        */

        /*
        Software::find()
        ->asArray()
        ->alias('t1')
        ->select('t1.*')
        //->leftJoin('{{%software}} as t2', '(t1.software_type = t2.software_type) AND (t1.software_version < t2.software_version) AND (t2.software_status>0)')
        //->where('(t2.software_version IS NULL) AND (t1.software_status>0)')
        ->where('(t1.software_status>0)')
        ->orderBy(['software_sort' => SORT_ASC])
        ->all();

        return $software;
        */

        return self::getDb()->cache(
            function($db) {
                return static::find()
                    ->asArray()
                    ->alias('t1')
                    ->select('t1.*')
                    ->where('(t1.software_status>0)')
                    ->orderBy(['software_sort' => SORT_ASC])
                    ->all();
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'Software.findOtherVersionSoftware'])
        );

    }
}
