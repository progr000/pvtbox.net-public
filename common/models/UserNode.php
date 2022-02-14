<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
use common\helpers\Functions;

/**
 * This is the model class for table "{{%user_node}}".
 *
 * @property string $node_id
 * @property string $node_hash
 * @property string $node_name
 * @property string $node_created
 * @property string $node_updated
 * @property integer $node_last_ip
 * @property string $node_countrycode
 * @property string $node_country
 * @property string $node_city
 * @property string $node_useragent
 * @property string $node_osname
 * @property string $node_ostype
 * @property string $node_devicetype
 * @property integer $node_online
 * @property integer $node_status
 * @property integer $node_upload_speed
 * @property integer $node_download_speed
 * @property integer $node_disk_usage
 * @property integer $node_logout_status
 * @property integer $node_wipe_status
 * @property integer $is_server
 * @property integer $node_prev_status
 * @property string $user_id
 * @property string $_relative_path
 * @property string $_full_path
 * @property integer $_current_node_status
 */
class UserNode extends ActiveRecord
{
    private static $CACHE_TTL = 3600;

    const WebFMOnlineTimeout = 600;

    const STATUS_DEACTIVATED = 0;
    const STATUS_ACTIVE      = 1;
    const STATUS_DELETED     = 2;
    const STATUS_SYNCING     = 3;
    const STATUS_SYNCED      = 4;
    const STATUS_LOGGEDOUT   = 5;
    const STATUS_WIPED       = 6;
    const STATUS_POWEROFF    = 7;
    const STATUS_PAUSED      = 8;
    const STATUS_INDEXING    = 9;

    const ONLINE_ON  = 1;
    const ONLINE_OFF = 0;

    const IS_SERVER  = 1;
    const NOT_SERVER = 0;

    const DEVICE_DESKTOP = "desktop";
    const DEVICE_PHONE   = "phone";
    const DEVICE_TABLET  = "tablet";
    const DEVICE_BROWSER = "browser";

    const OSTYPE_WEBFM   = "WebFM";
    const OSTYPE_WINDOWS = "Windows";
    const OSTYPE_DARWIN  = "Darwin";
    const OSTYPE_LINUX   = "Linux";
    const OSTYPE_IOS     = "iOS";
    const OSTYPE_ANDROID = "Android";

    const LOGOUT_STATUS_READY_TO    = 0; //"Log out";
    const LOGOUT_STATUS_IN_PROGRESS = 1; //"Execute LogOut";
    const LOGOUT_STATUS_SUCCESS     = 2; //"Logout Success";

    const WIPE_STATUS_READY_TO    = 0; //"Log out & Remote wipe*";
    const WIPE_STATUS_IN_PROGRESS = 1; //"Execute Wipe";
    const WIPE_STATUS_SUCCESS     = 2; //"Wipe Sucess";

    public $_relative_path = '';
    public $_full_path = '';
    public $_current_node_status;

    /**
     * @return array
     */
    public static function logoutStatuses()
    {
        return [
            self::LOGOUT_STATUS_READY_TO    => Yii::t('models/user-node', 'Log_out'),
            self::LOGOUT_STATUS_IN_PROGRESS => Yii::t('models/user-node', 'Execute_LogOut'),
            self::LOGOUT_STATUS_SUCCESS     => Yii::t('models/user-node', 'Logout_Success'),
        ];
    }

    /**
     * @param integer $node_logout_status
     * @return string|null
     */
    public static function logoutStatus($node_logout_status)
    {
        $labels = self::logoutStatuses();
        return isset($labels[$node_logout_status]) ? $labels[$node_logout_status] : null;
    }

    /**
     * @return array
     */
    public static function wipeStatuses()
    {
        return [
            self::WIPE_STATUS_READY_TO    => Yii::t('models/user-node', 'Log_out_and_Remote_wipe'),
            self::WIPE_STATUS_IN_PROGRESS => Yii::t('models/user-node', 'Execute_Wipe'),
            self::WIPE_STATUS_SUCCESS     => Yii::t('models/user-node', 'Wipe_Success'),
        ];
    }

    /**
     * @param integer $node_wipe_status
     * @return string|null
     */
    public static function wipeStatus($node_wipe_status)
    {
        $labels = self::wipeStatuses();
        return isset($labels[$node_wipe_status]) ? $labels[$node_wipe_status] : null;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_node}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'node_created',
                'updatedAtAttribute' => 'node_updated',
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
            [['node_hash', 'user_id'], 'required'],

            [['node_hash'], 'string', 'length' => 128], // +++changed 2019-03-05 16:00

            [['node_name'], 'string', 'max' => 30],

            [['node_created', 'node_updated'], 'validateDateField', 'skipOnEmpty' => true],
            [['node_created', 'node_updated'], 'safe'],

            //[['node_last_ip'], 'integer'],

            [['node_countrycode'], 'string', 'max' => 2],
            [['node_country', 'node_city'], 'string', 'max' => 40],

            [['node_useragent'], 'string', 'max' => 255],

            [['node_osname'], 'string', 'max' => 255],

            [['node_ostype'], 'in', 'range' => [
                self::OSTYPE_WEBFM,
                self::OSTYPE_ANDROID,
                self::OSTYPE_DARWIN,
                self::OSTYPE_IOS,
                self::OSTYPE_LINUX,
                self::OSTYPE_WINDOWS,
            ]],
            [['node_ostype'], 'checkIsSingleWebFM'],

            [['node_devicetype'], 'in', 'range' => [
                self::DEVICE_BROWSER,
                self::DEVICE_DESKTOP,
                self::DEVICE_PHONE,
                self::DEVICE_TABLET,
            ]],

            [['node_online'], 'integer'],
            [['node_online'], 'in', 'range' => [self::ONLINE_OFF, self::ONLINE_ON]],
            [['node_online'], 'default', 'value' => self::ONLINE_ON],

            [['node_status', 'node_prev_status'], 'integer'],
            [['node_status', 'node_prev_status'], 'in', 'range' => [
                self::STATUS_DEACTIVATED,
                self::STATUS_ACTIVE,
                self::STATUS_DELETED,
                self::STATUS_SYNCING,
                self::STATUS_SYNCED,
                self::STATUS_LOGGEDOUT,
                self::STATUS_WIPED,
                self::STATUS_POWEROFF,
                self::STATUS_PAUSED,
                self::STATUS_INDEXING,
            ]],
            [['node_status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['node_prev_status'], 'default', 'value' => null],

            [['node_upload_speed', 'node_download_speed', 'node_disk_usage'], 'integer', 'min' => 0],

            [['node_logout_status', 'node_wipe_status'], 'integer', 'min' => 0, 'max' => 2],

            [['node_logout_status'], 'integer'],
            [['node_logout_status'], 'in', 'range' => [
                self::LOGOUT_STATUS_READY_TO,
                self::LOGOUT_STATUS_IN_PROGRESS,
                self::LOGOUT_STATUS_SUCCESS
            ]],
            [['node_logout_status'], 'default', 'value' => self::LOGOUT_STATUS_READY_TO],

            [['node_wipe_status'], 'integer'],
            [['node_wipe_status'], 'in', 'range' => [
                self::WIPE_STATUS_READY_TO,
                self::WIPE_STATUS_IN_PROGRESS,
                self::WIPE_STATUS_SUCCESS
            ]],
            [['node_wipe_status'], 'default', 'value' => self::WIPE_STATUS_READY_TO],

            [['is_server'], 'integer'],
            [['is_server'], 'in', 'range' => [self::NOT_SERVER, self::IS_SERVER]],
            [['is_server'], 'default', 'value' => self::NOT_SERVER],

            [['user_id'], 'integer'],

            [['node_hash'], 'unique', 'targetClass' => 'common\models\UserNode'], // +++added 2019-03-05 16:00
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id']], // +++added 2019-03-05 16:00
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
     * Проверка того что у поьзователя не может быть более одной ноды с типом OSTYPE_WEBFM
     * @param $attribute
     * @param $params
     */
    public function checkIsSingleWebFM($attribute, $params)
    {
        if ($this->isNewRecord && ($this->$attribute == self::OSTYPE_WEBFM)) {
            if (self::findOne(['user_id' => $this->user_id, 'node_ostype' => self::OSTYPE_WEBFM])) {
                $this->addError($attribute, "User already has a node with node_ostype=" . self::OSTYPE_WEBFM);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'node_id' => 'Id',
            'node_hash' => 'Hash',
            'node_name' => 'Name',
            'node_created' => 'Created',
            'node_updated' => 'Last act.',
            'node_useragent' => 'UserAgent',
            'node_osname' => 'OS Name',
            'node_ostype' => 'Os Type',
            'node_devicetype' => 'Dev Type',
            'node_last_ip' => 'Last IP',
            'node_countrycode' => 'Country Code',
            'node_country' => 'Country',
            'node_city' => 'City',
            'node_online' => 'On/Off',
            'node_status' => 'Status',
            'node_upload_speed' => 'Up Speed',
            'node_download_speed' => 'Dwn Speed',
            'node_disk_usage' => 'Disk Usage',
            'node_logout_status' => '0 => ready for logout; 1 => logout action sent and it in progress, 2 => logout success',
            'node_wipe_status'   => '0 => ready for wipe;   1 => wipe action sent and it in progress,   2 => wipe success',
            'user_id' => 'User Id',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
    }

    /**
     * @param int|string $id
     * @return UserNode|null
     */
    public static function findIdentity($id)
    {
        return self::getDb()->cache(
            function($db) use($id) {
                return static::findOne(['node_id' => $id]);
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'UserNode.node_id.' . $id])
        );
        //return static::findOne(['node_id' => $id]);
    }

    /**
     * Finds node by node_hash
     *
     * @param string $node_hash
     * @return UserNode|null
     */
    public static function findByHash($node_hash)
    {
        return self::getDb()->cache(
            function($db) use($node_hash) {
                return static::findOne(['node_hash' => $node_hash]);
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'UserNode.node_hash.' . $node_hash])
        );
        //return static::findOne(['node_hash' => $node_hash]);
    }

    /**
     * Finds user by node_hash
     *
     * @param integer $user_id
     * @return UserNode|null
     */
    public static function findNodeWebFM($user_id)
    {
        return self::getDb()->cache(
            function($db) use($user_id) {
                return static::findOne(['user_id' => $user_id, 'node_devicetype' => self::DEVICE_BROWSER]);
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'UserNode.user_id.OSTYPE_WEBFM' . $user_id])
        );
        //return static::findOne(['node_hash' => $node_hash]);
    }

    /**
     * returns list of os types in array
     *
     * @return array
     */
    public static function osLabels()
    {
        return [
            self::OSTYPE_WEBFM   => self::OSTYPE_WEBFM,
            self::OSTYPE_WINDOWS => self::OSTYPE_WINDOWS,
            self::OSTYPE_DARWIN  => self::OSTYPE_DARWIN,
            self::OSTYPE_LINUX   => self::OSTYPE_LINUX,
            self::OSTYPE_IOS     => self::OSTYPE_IOS,
            self::OSTYPE_ANDROID => self::OSTYPE_ANDROID,
        ];
    }

    /**
     * return os type name by node_ostype value
     * @param string $node_ostype
     *
     * @return string | null
     */
    public static function osLabel($node_ostype)
    {
        $labels = self::osLabels();
        return isset($labels[$node_ostype]) ? $labels[$node_ostype] : null;
    }

    /**
     * returns list of statuses in array
     *
     * @return array
     */
    public static function devicesLabels()
    {
        return [
            self::DEVICE_DESKTOP => Yii::t('models/user-node', 'desktop'),
            self::DEVICE_PHONE   => Yii::t('models/user-node', 'phone'),
            self::DEVICE_TABLET  => Yii::t('models/user-node', 'tablet'),
            self::DEVICE_BROWSER => Yii::t('models/user-node', 'browser'),
        ];
    }

    /**
     * return status name by transfer_status value
     * @param string $node_devicetype
     *
     * @return string | null
     */
    public static function deviceLabel($node_devicetype)
    {
        $labels = self::devicesLabels();
        return isset($labels[$node_devicetype]) ? $labels[$node_devicetype] : null;
    }

    /**
     * returns list of statuses in array
     *
     * @return array
     */
    public static function statusLabels()
    {
        return [
            self::STATUS_DEACTIVATED => Yii::t('models/user-node', 'DEACTIVATED'),
            self::STATUS_ACTIVE      => Yii::t('models/user-node', 'ACTIVE'),
            self::STATUS_DELETED     => Yii::t('models/user-node', 'DELETED'),
            self::STATUS_SYNCING     => Yii::t('models/user-node', 'SYNCING'),
            self::STATUS_SYNCED      => Yii::t('models/user-node', 'SYNCED'),
            self::STATUS_LOGGEDOUT   => Yii::t('models/user-node', 'LOGGEDOUT'),
            self::STATUS_WIPED       => Yii::t('models/user-node', 'WIPED'),
            self::STATUS_POWEROFF    => Yii::t('models/user-node', 'POWEROFF'),
            self::STATUS_PAUSED      => Yii::t('models/user-node', 'PAUSED'),
            self::STATUS_INDEXING    => Yii::t('models/user-node', 'INDEXING'),
        ];
    }

    /**
     * return status name by transfer_status value
     * @param integer $node_status
     *
     * @return string | null
     */
    public static function statusLabel($node_status)
    {
        $labels = self::statusLabels();
        return isset($labels[$node_status]) ? $labels[$node_status] : null;
    }

    /**
     * returns list of statuses in array
     *
     * @return array
     */
    public static function onlineLabels()
    {
        return [
            self::ONLINE_ON  => Yii::t('models/user-node', 'ONLINE'),
            self::ONLINE_OFF => Yii::t('models/user-node', 'OFFLINE'),
        ];
    }

    /**
     * return status name by transfer_status value
     * @param integer $node_online
     *
     * @return string | null
     */
    public static function onlineLabel($node_online)
    {
        $labels = self::onlineLabels();
        return isset($labels[$node_online]) ? $labels[$node_online] : $node_online;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if ($this->_current_node_status != $this->node_status) {
                $this->node_prev_status = $this->_current_node_status;
            }

            if (!$this->node_last_ip) {
                if (method_exists(Yii::$app->request, 'getUserIP')) {
                    $this->node_last_ip = Yii::$app->request->getUserIP();
                } else {
                    $this->node_last_ip = '127.0.0.1';
                }
            }

            if (is_string($this->node_last_ip)) {
                $this->node_last_ip = intval(ip2long($this->node_last_ip));
            }

            $Info = \Yii::createObject([
                'class' => '\rmrevin\yii\geoip\HostInfo',
                'host' => long2ip($this->node_last_ip),
            ]);

            if ($Info->isAvailable()) {
                $InfoData = $Info->getData();
                $this->node_city        = !empty($InfoData['city'])         ? mb_substr(mb_convert_encoding($InfoData['city'],         'UTF-8'), 0, 40) : '';
                $this->node_country     = !empty($InfoData['country_name']) ? mb_substr(mb_convert_encoding($InfoData['country_name'], 'UTF-8'), 0, 40) : '';
                $this->node_countrycode = !empty($InfoData['country_code']) ? mb_substr(mb_convert_encoding($InfoData['country_code'], 'UTF-8'), 0, 2)  : '';
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Invalidate Cache
     */
    protected function invalidateCache()
    {
        TagDependency::invalidate(Yii::$app->cache, [
            'UserNode.node_id.' . $this->node_id,
            'UserNode.node_hash.' . $this->node_hash,
            'UserNode.user_id.OSTYPE_WEBFM' . $this->user_id,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $this->invalidateCache();
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

        $this->invalidateCache();
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        $this->_current_node_status = $this->node_status;
        $this->node_last_ip = long2ip($this->node_last_ip);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSessions()
    {
        return $this->hasMany(Sessions::className(), ['node_id' => 'node_id']);
    }
}
