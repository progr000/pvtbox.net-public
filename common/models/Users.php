<?php
namespace common\models;

use Yii;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\web\IdentityInterface;
use yii\caching\TagDependency;
use common\helpers\FileSys;
use common\helpers\Functions;
use frontend\models\NodeApi;
use frontend\models\CollaborationApi;
use frontend\models\ConferenceApi;
use frontend\models\forms\ShareElementForm;
use backend\models\Admins;

/**
 * User model
 *
 * @property integer $user_id
 * @property string $user_name
 * @property string $user_company_name
 * @property string $user_email
 * @property string $user_hash
 * @property string $user_remote_hash
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property integer $user_status
 * @property number $user_balance
 * @property integer $user_last_ip
 * @property string $user_created
 * @property string $user_updated
 * @property integer $user_ref_id
 * @property integer $user_closed_confirm
 * @property string $license_type
 * @property integer $license_bytes_allowed
 * @property integer $license_bytes_sent
 * @property integer $license_count_available
 * @property integer $license_count_used
 * @property integer $license_business_from
 * @property integer $shares_count_in24
 * @property integer $previous_license_business_from
 * @property string $previous_license_business_finish
 * @property string $license_expire
 * @property string $password write-only password
 * @property string $first_event_uuid_after_cron
 * @property integer $license_period
 * @property string $admin_full_name
 * @property string $pay_type
 * @property integer $payment_already_initialized
 * @property string $payment_init_date
 * @property integer $static_timezone
 * @property integer $dynamic_timezone
 * @property integer $expired_notif_sent
 * @property integer $user_dop_status
 * @property string $user_dop_log
 * @property integer $enable_admin_panel
 * @property integer $upl_limit_nodes
 * @property integer $upl_shares_count_in24
 * @property integer $upl_max_shares_size
 * @property integer $upl_max_count_children_on_copy
 * @property integer $upl_block_server_nodes_above_bought
 * @property integer $has_personal_seller
 *
 * @property string $_relative_path
 * @property string $_full_path
 * @property string $_color
 * @property string $_sname
 * @property integer $_count_events
 * @property string $_old_license_type
 * @property integer $_count_optimized_events
 * @property bool $_need_clear_collaborations
 * @property bool $_need_complex_processing_collaborations
 * @property bool $_is_colleague_self_leave
 *
 * @property integer $_ucl_limit_nodes
 * @property integer $_ucl_shares_count_in24
 * @property integer $_ucl_max_shares_size
 * @property integer $_ucl_max_count_children_on_copy
 * @property integer $_ucl_block_server_nodes_above_bought
 *
 * @property string $user_promo_code
 * @property string $license_key_for_sh
 * @property string $user_oo_address
 *
 * @property PaypalPays[] $paypalPays
 * @property Sessions[] $sessions
 * @property Transfers[] $transfers
 * @property UserPayments[] $userpayments
 * @property UserFiles[] $userFiles
 * @property UserNode[] $userNodes
 * @property UserUploads[] $userUploads
 * @property Licenses $licenseType
 */
class Users extends ActiveRecord implements IdentityInterface
{
    private static $CACHE_TTL = 3600;

    const STATUS_BLOCKED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_CONFIRMED = 2;

    const CONFIRM_CLOSED   = 1;
    const CONFIRM_UNCLOSED = 0;

    const PAY_NOTSET = 'not_set';
    const PAY_CARD   = 'card';
    const PAY_CRYPTO = 'crypto';

    const PAYMENT_PROCESSED = 2;
    const PAYMENT_INITIALIZED = 1;
    const PAYMENT_NOT_INITIALIZED = 0;

    const EXPIRED_NOTIF_SENT = 1;
    const EXPIRED_NOTIF_NOT_SENT = 0;

    const DOP_IN_PROGRESS = 1;
    const DOP_IS_COMPLETE = 0;

    const ADMIN_PANEL_ENABLE  = 1;
    const ADMIN_PANEL_DISABLE = 0;

    const YES = 1;
    const NO  = 0;

    private $sha512_password = "";

    public $_relative_path = '';
    public $_full_path = '';
    public $_color = 'M';
    public $_sname = '';

    public $_count_events;
    public $_count_optimized_events;
    public $_old_license_type;

    public $_need_clear_collaborations = false;
    public $_need_complex_processing_collaborations = false;
    public $_is_colleague_self_leave = false;

    public $_ucl_limit_nodes;
    public $_ucl_shares_count_in24;
    public $_ucl_max_shares_size;
    public $_ucl_max_count_children_on_copy;
    public $_ucl_block_server_nodes_above_bought;

    const PASSWORD_PATTERN = '/^[a-zA-Z0-9!@#$%^&*()_\-+=[\]{};:"\'\\\|\?\/\.\,]+$/';

    /**
     * returns list of statuses in array
     *
     * @return array
     */
    public static function statusParams()
    {
        return [
            self::STATUS_BLOCKED =>   ['name' => 'Blocked (Deleted)',    'color'=>'#BB0202'],
            self::STATUS_ACTIVE =>    ['name' => 'Active (not Confirmed)', 'color'=>'#25BB02'],
            self::STATUS_CONFIRMED => ['name' => 'Confirmed',  'color'=>'#CACC01'],
        ];
    }

    /**
     * returns list of statuses in array
     *
     * @return array
     */
    public static function statusLabels()
    {
        $labels = [];
        $params = self::statusParams();
        foreach ($params as $k=>$v)
            $labels[$k] = $v['name'];

        return $labels;
    }

    /**
     * return status name by user_status value
     * @param integer $user_status
     *
     * @return string | null
     */
    public static function statusLabel($user_status)
    {
        $params = self::statusParams();
        return isset($params[$user_status]) ? $params[$user_status]['name'] : $user_status;
    }

    /**
     * return status-color by user_status value
     * @param integer $user_status
     *
     * @return string | null
     */
    public static function statusColor($user_status)
    {
        $params = self::statusParams();
        return isset($params[$user_status]) ? $params[$user_status]['color'] : null;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%users}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'user_created',
                'updatedAtAttribute' => 'user_updated',
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
            [['user_email', 'user_name', 'license_type', ], 'required'],
            [['user_created', 'user_updated', 'previous_license_business_finish', 'license_expire', 'payment_init_date'], 'validateDateField', 'skipOnEmpty' => true],
            [['user_created', 'user_updated', 'previous_license_business_finish', 'license_expire', 'payment_init_date'], 'safe'],
            [['first_event_uuid_after_cron'], 'string', 'length' => 32],
            [['user_name', 'user_company_name', 'admin_full_name', 'user_hash'], 'string', 'max' => 50],
            [['user_remote_hash'], 'string', 'length' => 128],
            [['password_reset_token'], 'string', 'max' => 255],
            [['user_email', 'user_name', 'user_company_name', 'admin_full_name'], 'trim'],
            [['user_email'], 'email'],

            [[
                'user_ref_id',
                'license_bytes_allowed',
                'license_bytes_sent',
                'license_count_available',
                'license_count_used',
                'shares_count_in24',
                'license_business_from',
                'previous_license_business_from',
            ], 'integer'],


            [['expired_notif_sent'], 'integer'],
            [['expired_notif_sent'], 'in', 'range' => [self::EXPIRED_NOTIF_SENT, self::EXPIRED_NOTIF_NOT_SENT]],
            [['expired_notif_sent'], 'default', 'value' => self::EXPIRED_NOTIF_NOT_SENT],

            [['user_closed_confirm'], 'integer'],
            [['user_closed_confirm'], 'in', 'range' => [self::CONFIRM_CLOSED, self::CONFIRM_UNCLOSED]],
            [['user_closed_confirm'], 'default', 'value' => self::CONFIRM_UNCLOSED],

            [['payment_already_initialized'], 'integer'],
            [['payment_already_initialized'], 'in', 'range' => [self::PAYMENT_NOT_INITIALIZED, self::PAYMENT_INITIALIZED, self::PAYMENT_PROCESSED]],
            [['payment_already_initialized'], 'default', 'value' => self::PAYMENT_NOT_INITIALIZED],

            [['license_period'], 'integer'],
            [['license_period'], 'in', 'range' => [
                Licenses::PERIOD_NOT_SET,
                Licenses::PERIOD_DAILY,
                Licenses::PERIOD_MONTHLY,
                Licenses::PERIOD_ANNUALLY,
                Licenses::PERIOD_ONETIME,
            ]],
            [['license_period'], 'default', 'value' => Licenses::PERIOD_NOT_SET],

            [['user_status'], 'integer'],
            [['user_status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_BLOCKED, self::STATUS_CONFIRMED]],
            [['user_status'], 'default', 'value' => self::STATUS_ACTIVE],

            [['pay_type'], 'in', 'range' => [self::PAY_NOTSET, self::PAY_CARD, self::PAY_CRYPTO]],
            [['pay_type'], 'default', 'value' => self::PAY_NOTSET],

            [['user_dop_status'], 'integer'],
            [['user_dop_status'], 'in', 'range' => [self::DOP_IS_COMPLETE, self::DOP_IN_PROGRESS]],
            [['user_dop_status'], 'default', 'value' => self::DOP_IS_COMPLETE],

            [['enable_admin_panel'], 'integer'],
            [['enable_admin_panel'], 'in', 'range' => [self::ADMIN_PANEL_ENABLE, self::ADMIN_PANEL_DISABLE]],
            [['enable_admin_panel'], 'default', 'value' => self::ADMIN_PANEL_ENABLE],

            [['upl_limit_nodes', 'upl_shares_count_in24'], 'integer', 'min' => 0, 'max' => 32767],
            [['upl_limit_nodes', 'upl_shares_count_in24'], 'default', 'value' => null],

            [['upl_max_shares_size', 'upl_max_count_children_on_copy'], 'integer'],
            [['upl_max_shares_size', 'upl_max_count_children_on_copy'], 'default', 'value' => null],

            [['upl_block_server_nodes_above_bought'], 'integer'],
            [['upl_block_server_nodes_above_bought'], 'in', 'range' => [null, self::NO, self::YES]],
            [['upl_block_server_nodes_above_bought'], 'default', 'value' => null],

            [['static_timezone', 'dynamic_timezone'], 'integer',  'min' => -43200, 'max' => 46800],
            [['static_timezone', 'dynamic_timezone'], 'default', 'value' => self::NO],

            [['user_dop_log'], 'safe'],
            [['user_balance'], 'number'],

            [['has_personal_seller'], 'integer'],
            [['has_personal_seller'], 'in', 'range' => [self::YES, self::NO]],
            [['has_personal_seller'], 'default', 'value' => self::NO],

            [['user_email'], 'unique', 'targetClass' => Users::className(), 'message' => 'User with this user_email already exists'],
            [['user_hash'], 'unique', 'targetClass' => Users::className(), 'message' => 'User with this user_hash already exists'],
            [['user_remote_hash'], 'unique', 'targetClass' => Users::className(), 'message' => 'User with this user_remote_hash already exists'],
            [['password_reset_token'], 'unique', 'targetClass' => Users::className(), 'message' => 'User with this password_reset_token already exists'],

            [['user_promo_code'], 'string', 'max' => 30],
            [['license_key_for_sh'], 'string', 'max' => 128],

            [['user_oo_address'], 'string', 'max' => 255],

            [['license_type'], 'exist', 'skipOnError' => true, 'targetClass' => Licenses::className(), 'targetAttribute' => ['license_type' => 'license_type']],

            [['user_ref_id'], 'exist', 'skipOnError' => true, 'skipOnEmpty' => true, 'targetClass' => Admins::className(), 'targetAttribute' => ['user_ref_id' => 'admin_id']],
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'Id',
            'user_email' => 'E-mail',
            'user_name' => 'User Name',
            'user_company_name' => 'Company Name',
            'user_balance' => 'Payments sum',
            'user_last_ip' => 'Last IP',
            'user_status' => 'Status',
            'user_created' => 'Registration date',
            'user_updated' => 'Last activity',
            'user_closed_confirm' => 'A sign that the user closed the popup with a proposal to confirm the email',
            'license_type' => 'Type of license',
            'license_bytes_allowed' => 'Count allowed bytes',
            'license_bytes_sent' => 'Count bytes sent',
            'shares_count_in24' => 'Count of shared files in 24 hours (starts from 00:00)',
            'license_expire' => 'date when license is expire',
            'admin_full_name' => 'Administrator full name',
            'user_dop_status' => 'Status for Delete Old Patches console script for this user',
            'user_dop_log' => 'Log for Delete Old Patches console script for this user',
            'enable_admin_panel' => 'Enable access or not to Admin-panel for Business',
            'upl_limit_nodes' => 'Personal limit for nodes instead limit by license (if not set then limit by license)',
            'user_promo_code' => 'Promo code',
        ];
    }

    /**
     *
     */
    public function generatePathForUser()
    {
        $this->_relative_path = self::generateRelativePathNodeFsFor($this->user_id);
        $this->_full_path = Yii::$app->params['nodeVirtualFS'] . DIRECTORY_SEPARATOR . $this->_relative_path;

        if (!Yii::$app->params['Stop_NodeApi_and_FM']) {
            if (!file_exists($this->_full_path)) {
                //@mkdir($User->_full_path, 0777, true);
                FileSys::mkdir($this->_full_path, 0777, true);
            }
            $folder_info_file = $this->_full_path . DIRECTORY_SEPARATOR . UserFiles::DIR_INFO_FILE;
            if (!file_exists($folder_info_file)) {
                FileSys::touch($folder_info_file, 0777, 0666);
                FileSys::fwrite($folder_info_file, serialize([
                    'file_id' => null,
                    'file_uuid' => "",
                ]), 0666);
            }
        }
    }

    /**
     * {@inheritdoc}
     * @return static ActiveRecord instance matching the condition, or `null` if nothing matches.
     */
    public static function findOne($condition)
    {
        /** @var Users $User */
        //$User = static::findByCondition($condition)->one();
        $User = parent::findOne($condition);
        if ($User) {
            $User->_relative_path = self::generateRelativePathNodeFsFor($User->user_id);
            $User->_full_path = Yii::$app->params['nodeVirtualFS'] . DIRECTORY_SEPARATOR . $User->_relative_path;

            if (!Yii::$app->params['Stop_NodeApi_and_FM']) {
                if (!file_exists($User->_full_path)) {
                    //@mkdir($User->_full_path, 0777, true);
                    FileSys::mkdir($User->_full_path, 0777, true);
                }
                $folder_info_file = $User->_full_path . DIRECTORY_SEPARATOR . UserFiles::DIR_INFO_FILE;
                if (!file_exists($folder_info_file)) {
                    FileSys::touch($folder_info_file, 0777, 0666);
                    FileSys::fwrite($folder_info_file, serialize([
                        'file_id' => null,
                        'file_uuid' => "",
                    ]), 0666);
                }
            }
        }
        return $User;
    }

    /**
     * @param int|string $id
     * @return Users|null
     */
    public static function findIdentity($id)
    {
        return self::getDb()->cache(
            function($db) use($id) {
                //return static::findOne(['user_id' => $id, 'user_status' => [self::STATUS_ACTIVE, self::STATUS_CONFIRMED]]);
                return static::findOne(['user_id' => $id]);
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'Users.user_id.' . $id])
        );
        //return static::findOne(['user_id' => $id, 'user_status' => [self::STATUS_ACTIVE, self::STATUS_CONFIRMED]]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by user_name
     *
     * @param string $user_name
     * @return Users|null
     */
    public static function findByUsername($user_name)
    {
        return self::getDb()->cache(
            function($db) use($user_name) {
                return static::findOne(['user_name' => $user_name, 'user_status' => [self::STATUS_ACTIVE, self::STATUS_CONFIRMED]]);
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'Users.user_name.' . $user_name])
        );
        //return static::findOne(['user_name' => $user_name, 'user_status' => [self::STATUS_ACTIVE, self::STATUS_CONFIRMED]]);
    }

    /**
     * Finds user by user_email
     *
     * @param string $user_email
     * @return Users|null
     */
    public static function findByEmail($user_email)
    {
        return self::getDb()->cache(
            function($db) use($user_email) {
                return static::findOne(['user_email' => $user_email, 'user_status' => [self::STATUS_ACTIVE, self::STATUS_CONFIRMED]]);
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'Users.user_email.' . $user_email])
        );
        //return static::findOne(['user_email' => $user_email, 'user_status' => [self::STATUS_ACTIVE, self::STATUS_CONFIRMED]]);
    }

    /**
     * Finds user by user_hash
     *
     * @param string $user_hash
     * @return Users|null
     */
    public static function findByUserHash($user_hash)
    {
        return self::getDb()->cache(
            function($db) use($user_hash) {
                return static::findOne(['user_hash' => $user_hash, 'user_status' => [self::STATUS_ACTIVE, self::STATUS_CONFIRMED]]);
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'Users.user_hash.' . $user_hash])
        );
        //return static::findOne(['user_hash' => $user_hash, 'user_status' => [self::STATUS_ACTIVE, self::STATUS_CONFIRMED]]);
    }

    /**
     * Finds user by user_remote_hash
     *
     * @param string $user_remote_hash
     * @return Users|null
     */
    public static function findByUserRemoteHash($user_remote_hash)
    {
        return self::getDb()->cache(
            function($db) use($user_remote_hash) {
                return static::findOne(['user_remote_hash' => $user_remote_hash, 'user_status' => [self::STATUS_ACTIVE, self::STATUS_CONFIRMED]]);
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'Users.user_remote_hash.' . $user_remote_hash])
        );
        //return static::findOne(['user_remote_hash' => $user_remote_hash, 'user_status' => [self::STATUS_ACTIVE, self::STATUS_CONFIRMED]]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @param bool $checkTokenValid
     * @return Users|null
     */
    public static function findByPasswordResetToken($token, $checkTokenValid=true)
    {
        if ($checkTokenValid && !self::isPasswordResetTokenValid($token)) {
            return null;
        }

        return self::getDb()->cache(
            function($db) use($token) {
                return static::findOne(['password_reset_token' => $token, 'user_status' => [self::STATUS_ACTIVE, self::STATUS_CONFIRMED]]);
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'Users.password_reset_token.' . $token])
        );
        //return static::findOne(['password_reset_token' => $token, 'user_status' => [self::STATUS_ACTIVE, self::STATUS_CONFIRMED]]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Preferences::getValueByKey('user.passwordResetTokenExpire');
        return $timestamp + $expire >= time();
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isLoginTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = 3600;
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @param bool $encryptSha512Before - если true то до создания пароля происходит шифрование sha512
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password, $encryptSha512Before=true)
    {
        if ($encryptSha512Before)
            $password = hash('sha512', $password);

        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     * @param bool $encryptSha512Before - если true то до создания пароля происходит шифрование sha512
     */
    public function setPassword($password, $encryptSha512Before=true)
    {
        if ($encryptSha512Before)
            $password = hash('sha512', $password);

        $this->sha512_password = $password;
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
        $this->user_remote_hash = self::generateUserRemoteHash($this->user_email, $this->sha512_password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        TagDependency::invalidate(Yii::$app->cache, [
            'Users.password_reset_token.' . $this->password_reset_token,
        ]);
        $this->password_reset_token = null;
    }

    /**
     * Generate user_remote_hash
     *
     * @param string $user_email
     * @param string $sha512_password
     * @return string sha512 hash
     */
    public static function generateUserRemoteHash($user_email, $sha512_password)
    {
        return  hash('sha512', $user_email . $sha512_password . Yii::$app->params['userHashSalt']);
        //return md5($user_email . $sha512_password . Yii::$app->params['userHashSalt']);
    }

    /**
     * Generate user_remote_hash
     *
     * @param string $user_email
     * @return string sha512 hash
     */
    public static function generateShuUserHash($user_email)
    {
        return  hash('sha512', $user_email);
    }

    /**
     * @param $event
     * @throws Exception
     */
    public static function afterLogin($event)
    {
        /** @var \common\models\Users $User */
        $User = $event->identity;
        $User->user_last_ip = Yii::$app->request->getUserIP();
        $User->save();
        Yii::$app->session->set('UserTimeZoneOffset', $User->static_timezone);

        $UserNode = NodeApi::registerNodeFM($User);
        if ($UserNode) {
            $UserNode->node_online = UserNode::ONLINE_ON;
            $UserNode->node_useragent = Yii::$app->request->getUserAgent();
            $ua_info = Functions::clientDetection($UserNode->node_useragent);
            $UserNode->node_osname = ($ua_info['os']['name'] == "Linux" && $ua_info['os']['version'] != "")
                ? $ua_info['os']['version']
                : $ua_info['os']['name'] . ' ' . $ua_info['os']['version'];
            $UserNode->node_ostype = $ua_info['os']['name'];
            $UserNode->node_name = $ua_info['browser']['name'] . ' ' . $ua_info['browser']['version'];
            $UserNode->save();
        }
        $session = new Sessions();
        $session->user_id = $User->user_id;
        $session->node_id = $UserNode->node_id;
        $session->sess_action = Sessions::ACTION_LOGIN;
        $session->save();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        //var_dump($this->user_created); exit;
        if (parent::beforeSave($insert)) {

            if ($this->user_promo_code === '') {
                $this->user_promo_code = null;
            }

            // +++ License limitations
            $License = Licenses::findByType($this->license_type);
            if ($License) {
                $bytes_rest = $License->license_limit_bytes - $this->license_bytes_sent;
                $this->license_bytes_allowed = ($bytes_rest > 0) ? $bytes_rest : 0;
            } else {
                $this->license_bytes_allowed = 0;
            }
            /* Если у лицензии имеется ограничение по времени */
            /* проверка что лицензия актуальна */
            //var_dump($License); exit;
            //if ($this->license_type == Licenses::TYPE_FREE_TRIAL) {
            if ($License->license_limit_days > 0) {
                /* Если пользователь подтвердил свой емейл и лицензия еще триал */
                if ($this->user_status == self::STATUS_CONFIRMED && $this->license_type == Licenses::TYPE_FREE_TRIAL) {
                    $BonusTrialForEmailConfirm = Preferences::getValueByKey('BonusTrialForEmailConfirm', 14, 'integer');
                    $append_period = $BonusTrialForEmailConfirm * 24 * 60 * 60;
                } else {
                    $append_period = 0;
                }
                /* перевод лицензии на free в случае если истек срок триал */
                if (time() - strtotime($this->user_created) > $License->license_limit_days * 24 * 60 * 60 + $append_period) {
                    $this->license_type = Licenses::TYPE_FREE_DEFAULT;
                    $this->license_expire = null;
                    $this->license_count_available = 0;
                    $this->license_count_used = 0;
                }
            }
            /* Проверим до когда оплачена лицензия если это не фри или фри-триал*/
            if (!in_array($this->license_type, [Licenses::TYPE_FREE_TRIAL, Licenses::TYPE_FREE_DEFAULT])) {
                if ($this->license_expire) {
                    $BonusPeriodLicense = Preferences::getValueByKey('BonusPeriodLicense', 72, 'integer') * 3600;
                    if (strtotime($this->license_expire) + $BonusPeriodLicense < time()) {
                        $this->license_type = Licenses::TYPE_FREE_DEFAULT;
                        $this->license_expire = null;
                        $this->license_count_available = 0;
                        $this->license_count_used = 0;
                    }
                }
            } else {
                $this->license_period = Licenses::PERIOD_NOT_SET;
                $this->pay_type = self::PAY_NOTSET;
            }

            /* Если по какой ли бо из причин лицензия изменилась на TYPE_FREE_DEFAULT то обнуляем лицухи по бизнесу*/
            if ($this->license_type == Licenses::TYPE_FREE_DEFAULT) {
                $this->license_period = Licenses::PERIOD_NOT_SET;
                $this->pay_type = self::PAY_NOTSET;
                $this->license_expire = null;
                $this->license_count_available = 0;
                $this->license_count_used = 0;
                $this->expired_notif_sent = self::EXPIRED_NOTIF_NOT_SENT;
            }

            if (in_array($this->license_type, [Licenses::TYPE_FREE_DEFAULT, Licenses::TYPE_FREE_TRIAL])) {
                //$this->payment_already_initialized = self::PAYMENT_NOT_INITIALIZED;
            }

            if ($this->_old_license_type && $this->_old_license_type != $this->license_type) {
                $this->expired_notif_sent = self::EXPIRED_NOTIF_NOT_SENT;
            }

            /** Если с бизнес лицензии перешел на любую другую, то скинуь коллабы придется */
            if (($this->_old_license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) && ($this->license_type != Licenses::TYPE_PAYED_BUSINESS_ADMIN)) {
                $this->_need_clear_collaborations = true;
            }

            /** Если с профессиональной лицензии перешел на любую из фришных, то скинуть коллабы */
            if (($this->_old_license_type == Licenses::TYPE_PAYED_PROFESSIONAL) && in_array($this->license_type, [Licenses::TYPE_FREE_DEFAULT, Licenses::TYPE_FREE_TRIAL])) {
                $this->_need_clear_collaborations = true;
            }

            /** Если с профессиональной или триальной лицензии перешел на бизнес лицензию, то нужна сложная схема переоформления лицензий, так что сейчас просто скинуть коллабы */
            if (in_array($this->_old_license_type, [Licenses::TYPE_FREE_TRIAL, Licenses::TYPE_PAYED_PROFESSIONAL]) && $this->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                /* TO-DO: тут (в методе afterSave) нужно доработать потом сложную систему лицензий (СДЕЛАНО) */
                //$this->_need_clear_collaborations = true;
                $this->_need_complex_processing_collaborations = true;
            }

            // +++ remote hash
            if ($this->isNewRecord) {
                $this->user_hash = md5($this->user_email);
                $this->user_remote_hash = self::generateUserRemoteHash($this->user_email, $this->sha512_password);
            }

            // +++ IP to long
            if (is_string($this->user_last_ip)) {
                $this->user_last_ip = intval(ip2long($this->user_last_ip));
            }

            // +++ если юзер с типом лицензии не равной бизнес-юзер, то и license_business_from нужно установить = NULL
            if ($this->license_type != Licenses::TYPE_PAYED_BUSINESS_USER) {
                $this->license_business_from = null;
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
            'Users.user_id.' . $this->user_id,
            'Users.user_name.' . $this->user_name,
            'Users.user_email.' . $this->user_email,
            'Users.user_hash.' . $this->user_hash,
            'Users.user_remote_hash.' . $this->user_remote_hash,
            'Users.password_reset_token.' . $this->password_reset_token,
        ]);
        TagDependency::invalidate(Yii::$app->cache, [
            'UserLicenses.user_id.license_unused.' . $this->user_id,
            'UserLicenses.user_id.license_total.' . $this->user_id,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        /* очистка мемкеша для этого юзера */
        $this->invalidateCache();

        /* если изменена лицензия каким либо образом в какую либо сторону, выполняется ряд проверок и действий */
        if (isset($changedAttributes['license_type'])) {

            /* отправка инфы о смене лицензии в редис */
            $this->afterSaveSendLicenseInfoToRedis();

            /* Если переход от триал или про к бизнес то сложная обработка */
            if ($this->_need_complex_processing_collaborations) {
                $this->afterSaveComplexProcessingCollaborations();
            }

            /* Если лицензия стала фришной, отменить все шары,
             * отменить все коллаборации которые создал юзер,
             * отменить все конференции для этого юзера,
             * оставить только 5 устройств. (поправочка оставляем все зареганные ноды как есть) */
            if (($this->license_type == Licenses::TYPE_FREE_DEFAULT) || $this->_need_clear_collaborations) {
                $this->afterSaveIfLicenseBecomeFree($changedAttributes);
            }
        }

        /* Если вдруг у бизнес админа не списана лицензия на него самого, то спишем ее */
        if ($this->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
            $this->afterSaveCheckBusinessAdminWriteOffLicense();
        }

        /* Если новая регистрация юзера или если сменили емейла юзера (в реальности сейчас запрещена смена емейла) */
        if (isset($changedAttributes['user_email']) || $insert) {

            /* если новая регистрация */
            if ($insert) {

                /* создание нотификаций по конференциям (+ обновление данных по ИД в таблице conference_participants) */
                $this->afterSaveCreateNotificationsAboutConferences();

                /* создание нотификаций по коллаборациям (+ обновление данных по ИД в таблице user_colleagues) */
                $this->afterSaveCreateNotificationsAboutCollaborations();

                /* установка лимитов для бизнес-юзера на основе лимитов которые установлены для бизнес-админа */
                $this->afterSaveSetBusinesUserLimitations();

                /* Обновим user_id в таблице self_host_users */
                $this->afterSaveUpdateSelfHostUsers();

            /* если сменили емейл юзера в системе */
            } else {

                $this->afterSaveIfChangedUserEmail();

            }
        }

        /* Если изменили екмпайр дату лицензии бизнес админа то нужно всем его лицухам устанвить такую же дату
         * а так же всем его коллегам установить експайр дату до такой же */
        if (isset($changedAttributes['license_expire']) && $this->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
            $this->afterSaveIfChangedLicenseExpireDateForBusinessAdmin();
        }
    }

    /**
     * отправка инфы о смене лицензии в редис
     */
    protected function afterSaveSendLicenseInfoToRedis()
    {
        /** @var \yii\redis\Connection $redis */
        try {
            $redis = Yii::$app->redis;
            $redis->publish("user:{$this->user_id}:license_type_changed", $this->license_type);
            $redis->save();
        } catch (Exception $e) {
            RedisSafe::createNewRecord(
                RedisSafe::TYPE_LICENSE_CHANGES,
                $this->user_id,
                null,
                Json::encode([
                    'action'           => 'license_type_changed',
                    'chanel'           => "user:{$this->user_id}:license_type_changed",
                    'user_id'          => $this->user_id,
                ])
            );
        }
    }

    /**
     * Если переход от триал или про к бизнес то сложная обработка
     */
    protected function afterSaveComplexProcessingCollaborations()
    {
        $query = "SELECT
                    _is_owner,
                    _colleague_status,
                    _license_type,
                    _colleague_email as colleague_email,
                    _user_id as user_id
                  FROM get_real_collaborated_colleagues(:user_id)
                  WHERE (_license_type NOT IN (:PAYED_PROFESSIONAL, :PAYED_BUSINESS_ADMIN, :PAYED_BUSINESS_USER))
                  AND (_is_owner != 1)
                  ORDER BY _is_owner DESC, _colleague_status ASC, _license_type DESC";
        $cpc_res = Yii::$app->db->createCommand($query, [
            'user_id'              => $this->user_id,
            'PAYED_PROFESSIONAL'   => Licenses::TYPE_PAYED_PROFESSIONAL,
            'PAYED_BUSINESS_ADMIN' => Licenses::TYPE_PAYED_BUSINESS_ADMIN,
            'PAYED_BUSINESS_USER'  => Licenses::TYPE_PAYED_BUSINESS_USER,
        ])->queryAll();
        foreach ($cpc_res as $colleague) {
            /*
             * признак того что коллаборацию нужно очистить
             * (зададим изначально в тру, если же удастся найти и назначить свободную лицензию, то установим в фалс)
             * Если же значение останется тру, то выполним сброс колабы для этого коллеги
             */
            $clearCollaboration = true;

            /* пробуем найти свободную лицензию */
            $freeUserLicense = UserLicenses::getFreeLicense($this->user_id);
            /* И пробуем найти этого юзера-коллегу */
            $User_for_Colleague = self::findIdentity($colleague['user_id']);
            if ($freeUserLicense && $User_for_Colleague) {

                $freeUserLicense->lic_colleague_user_id = $colleague['user_id'];
                $freeUserLicense->lic_colleague_email = $colleague['colleague_email'];

                $User_for_Colleague->license_expire = $freeUserLicense->lic_end;
                $User_for_Colleague->license_type = Licenses::TYPE_PAYED_BUSINESS_USER;
                $User_for_Colleague->license_business_from = $this->user_id;
                $User_for_Colleague->upl_limit_nodes = $this->upl_limit_nodes;
                $User_for_Colleague->upl_shares_count_in24 = $this->upl_shares_count_in24;
                $User_for_Colleague->upl_max_shares_size = $this->upl_max_shares_size;
                $User_for_Colleague->upl_max_count_children_on_copy = $this->upl_max_count_children_on_copy;
                $User_for_Colleague->upl_block_server_nodes_above_bought = $this->upl_block_server_nodes_above_bought;

                if ($freeUserLicense->save() && $User_for_Colleague->save()) {
                    /* если нашли и смогли ее захватить для коллеги, то коллаба с этим коллегой остается */
                    $clearCollaboration = false;
                }

            }

            /* удаление коллабы с коллегой для которого не удалось захватить лицензию */
            if ($clearCollaboration) {
                $model = new ShareElementForm(['colleague_email']);
                $model->colleague_email = $colleague['colleague_email'];
                $model->owner_user_id = $this->user_id;
                if ($model->validate()) {
                    $model->adminPanelColleagueDelete(false);
                }
            }
        }
    }

    /**
     * Если лицензия стала фришной, отменить все шары,
     * отменить все коллаборации которые создал юзер,
     * отменить все конференции для этого юзера,
     * оставить только 5 устройств. (поправочка оставляем все зареганные ноды как есть)
     * @param array $changedAttributes
     */
    protected function afterSaveIfLicenseBecomeFree($changedAttributes)
    {
        /* Отмена всех шар */
//                $shares = UserFiles::find()
//                    ->where([
//                        'user_id'   => $this->user_id,
//                        'is_shared' => UserFiles::FILE_SHARED
//                    ])
//                    ->all();
//                $UserNode = NodeApi::registerNodeFM($this);
//                if ($UserNode) {
//                /** @var \common\models\UserFiles $share */
//                    foreach ($shares as $share) {
//                        $model = new NodeApi(['uuid']);
//                        $data['uuid'] = $share->file_uuid;
//                        if ($model->load(['NodeApi' => $data]) && $model->validate()) {
//                            $model->sharing_disable($UserNode, false, false);
//                        }
//                    }
//                }

        //$transaction = Yii::$app->db->beginTransaction();

        /* отмена коллабораций владельцем которых есть юзер */
        $collaborations = UserCollaborations::find()
            ->where(['user_id' => $this->user_id])
            ->all();
        /** @var \common\models\UserCollaborations $collaboration */
        foreach ($collaborations as $collaboration) {
            $data['owner_user_id']  = $this->user_id;
            $data['uuid']           = $collaboration->file_uuid;
            $data['is_from_system'] = true;

            $model = new CollaborationApi(['owner_user_id', 'uuid']);
            if ($model->load(['CollaborationApi' => $data]) && $model->validate()) {
                $model->collaborationDelete();
            }
        }

        /* послать логаут для нод этого юзера в случае если это СХ и бизнес-юзер потерял лицензию */
        if (in_array($changedAttributes['license_type'], [Licenses::TYPE_PAYED_BUSINESS_USER]) && Yii::$app->params['self_hosted']) {
            $UserNodes = UserNode::find()
                ->where('user_id = :user_id) AND (node_devicetype != :node_devicetype)', [
                    'user_id' => $this->user_id,
                    'node_devicetype' => UserNode::DEVICE_BROWSER
                ]);
            if ($UserNodes) {
                $UserNodeFM = NodeApi::registerNodeFM($this);
                foreach ($UserNodes as $userNode) {
                    /** @var $userNode \common\models\UserNode */
                    $modelLogout = new NodeApi(['target_node_id', 'action_type']);
                    $tmp['target_node_id'] = $userNode->node_id;
                    $tmp['action_type']    = RemoteActions::TYPE_LOGOUT;
                    if ($modelLogout->load(['NodeApi' => $tmp]) && $modelLogout->validate()) {
                        $modelLogout->execute_remote_action($UserNodeFM, $this);
                    }
                }
            }
        }

        /* Создание нотификации о даунгрейде лицензии */
        //$changedAttributes['license_type']
        if (in_array($changedAttributes['license_type'], [
            Licenses::TYPE_PAYED_BUSINESS_ADMIN,
            Licenses::TYPE_PAYED_PROFESSIONAL,
            Licenses::TYPE_FREE_TRIAL,
        ])) {

            $notif_type = Notifications::TYPE_LICENSE_DOWNGRADED;
            if ($this->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                $notif_type = Notifications::TYPE_LICENSE_UPGRADED;
            }
            if ($this->license_type == Licenses::TYPE_PAYED_PROFESSIONAL && in_array($changedAttributes['license_type'], [
                    Licenses::TYPE_FREE_DEFAULT,
                    Licenses::TYPE_FREE_TRIAL])) {
                $notif_type = Notifications::TYPE_LICENSE_UPGRADED;
            }
            if ($this->license_type == Licenses::TYPE_PAYED_PROFESSIONAL && $changedAttributes['license_type'] == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                $notif_type = Notifications::TYPE_LICENSE_CHANGED;
            }

            $notif = new Notifications();
            $notif->user_id = $this->user_id;
            $notif->notif_isnew = Notifications::IS_NEW;
            $notif->notif_type = $notif_type;
            $notif->notif_data = serialize([
                'search' => [
                    '{OLD_LICENSE_TYPE}',
                    '{NEW_LICENSE_TYPE}',
                ],
                'replace' => [
                    Licenses::getType($changedAttributes['license_type']),
                    Licenses::getType($this->license_type),
                ],
            ]);
            $notif->save();
        }

        /* отмена коллабораций где юзер является коллегой */
        $query = "SELECT
                    t1.collaboration_id,
                    t1.file_uuid,
                    t1.user_id as owner_user_id,
                    t2.colleague_id
                  FROM {{%user_collaborations}} as t1
                  INNER JOIN {{%user_colleagues}} as t2 ON t1.collaboration_id=t2.collaboration_id
                  WHERE (t2.user_id = :user_id)
                  AND (t1.user_id != :user_id) -- added 30/08/2018 15:27
                  AND (t2.colleague_permission != :PERMISSION_OWNER)
                  AND (t2.colleague_status != :STATUS_QUEUED_DEL)";
        $res = Yii::$app->db
            ->createCommand($query, [
                'user_id'           => $this->user_id,
                'PERMISSION_OWNER'  => UserColleagues::PERMISSION_OWNER,
                'STATUS_QUEUED_DEL' => UserColleagues::STATUS_QUEUED_DEL,
            ])
            ->queryAll();
        //var_dump($res); exit;
        if (sizeof($res)) {
            foreach ($res as $v) {
                $data['is_from_recursion'] = true;
                $data['action']            = CollaborationApi::ACTION_DELETE;
                $data['access_type']       = UserColleagues::PERMISSION_DELETE;
                $data['uuid']              = $v['file_uuid'];
                $data['colleague_id']      = $v['colleague_id'];
                $data['owner_user_id']     = $v['owner_user_id'];
                $data['is_colleague_self_leave'] = $this->_is_colleague_self_leave;
                $model = new CollaborationApi(['uuid', 'owner_user_id', 'colleague_id', 'action', 'access_type']);
                if ($model->load(['CollaborationApi' => $data]) && $model->validate()) {
                    //$modesl->initOwner($res['owner_user_id']);
                    $model->colleagueDelete();
                }
            }
        }

        /* Удаление нул коллабораций, где присутстывует этот юзер */
        $res = Yii::$app->db->createCommand("
                  DELETE FROM {{%user_collaborations}} as t1
                  USING {{%user_colleagues}} as t2
                  WHERE (t1.collaboration_id = t2.collaboration_id)
                  AND ((t1.user_id=:user_id))
                  AND (t1.file_uuid IS NULL);
                ", [
            'user_id' => $this->user_id,
        ])->execute();

        /* Отзовем все выданные этим юзером лицензии */
        $only_revoke = ($this->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN);
        UserLicenses::revokeForUserId($this->user_id, $only_revoke);
        UserServerLicenses::deleteAll(['lic_srv_owner_user_id' => $this->user_id]);
        UserServerLicenses::updateAll([
            'lic_srv_colleague_user_id' => null,
            'lic_srv_node_id'           => null,
        ], [
            'lic_srv_colleague_user_id' => $this->user_id,
        ]);

        /* Отмена всех конференций */
        $modelConferences = new ConferenceApi();
        $listConferences = $modelConferences->getListConferences($this);
        if (isset($listConferences['data'])) {
            foreach ($listConferences['data'] as $listConference) {
                $modelConferences->conference_id = $listConference['conference_id'];
                $modelConferences->cancelConference($this);
            }
        }

        //$transaction->commit();

    }

    /**
     * Если вдруг у бизнес админа не списана
     * лицензия на него самого, то спишем ее
     */
    protected function afterSaveCheckBusinessAdminWriteOffLicense()
    {
        $query_test = "SELECT lic_colleague_user_id
                       FROM {{%user_licenses}}
                       WHERE (lic_owner_user_id = :this_user_id)
                       AND (lic_colleague_user_id = :this_user_id)
                       LIMIT 1";
        $test = Yii::$app->db->createCommand($query_test, [
            'this_user_id' => $this->user_id,
        ])->queryOne();
        //var_dump($test); exit;
        if (!isset($test['lic_colleague_user_id'])) {
            $query = "UPDATE {{%user_licenses}} SET
                        lic_colleague_user_id = :this_user_id,
                        lic_colleague_email = :this_user_email
                      WHERE ctid IN (
                        SELECT ctid FROM {{%user_licenses}}
                        WHERE lic_owner_user_id = :this_user_id
                        ORDER BY lic_id ASC
                        LIMIT 1
                      )";
            Yii::$app->db->createCommand($query, [
                'this_user_id'    => $this->user_id,
                'this_user_email' => $this->user_email,
            ])->execute();
        }
    }

    /**
     * Если изменили екмпайр дату лицензии бизнес админа
     * то нужно всем его лицухам устанвить такую же дату
     * а так же всем его коллегам установить експайр дату
     * до такой же
     */
    protected function afterSaveIfChangedLicenseExpireDateForBusinessAdmin()
    {
        UserLicenses::updateAll(['lic_end' => $this->license_expire], ['lic_owner_user_id' => $this->user_id]);
        UserServerLicenses::updateAll(['lic_srv_end' => $this->license_expire], ['lic_srv_owner_user_id' => $this->user_id]);
        $query = "UPDATE {{%users}} SET
                    license_expire = :license_expire
                  WHERE (user_id IN (
                      SELECT lic_colleague_user_id
                      FROM {{%user_licenses}}
                      WHERE (lic_owner_user_id = :this_user_id)
                      AND (lic_colleague_user_id IS NOT NULL)
                  )
                  AND (license_type = :TYPE_PAYED_BUSINESS_USER))
                  OR (license_business_from = :this_user_id)
                  RETURNING user_id, user_name, user_email, user_hash, user_remote_hash, password_reset_token";
        $resUpd = Yii::$app->db->createCommand($query, [
            'license_expire'           => $this->license_expire,
            'this_user_id'             => $this->user_id,
            'TYPE_PAYED_BUSINESS_USER' => Licenses::TYPE_PAYED_BUSINESS_USER,
        ])->queryAll();
        foreach ($resUpd as $v) {
            TagDependency::invalidate(Yii::$app->cache, [
                'Users.user_id.' . $v['user_id'],
                'Users.user_name.' . $v['user_name'],
                'Users.user_email.' . $v['user_email'],
                'Users.user_hash.' . $v['user_hash'],
                'Users.user_remote_hash.' . $v['user_remote_hash'],
                'Users.password_reset_token.' . $v['password_reset_token'],
            ]);
            TagDependency::invalidate(Yii::$app->cache, [
                'UserLicenses.user_id.license_unused.' . $v['user_id'],
                'UserLicenses.user_id.license_total.' . $v['user_id'],
            ]);
        }
        TagDependency::invalidate(Yii::$app->cache, [
            'Users.user_id.' . $this->user_id,
            'Users.user_name.' . $this->user_id,
            'Users.user_email.' . $this->user_id,
            'Users.user_hash.' . $this->user_id,
            'Users.user_remote_hash.' . $this->user_remote_hash,
            'Users.password_reset_token.' . $this->password_reset_token,
        ]);
        TagDependency::invalidate(Yii::$app->cache, [
            'UserLicenses.user_id.license_unused.' . $this->user_id,
            'UserLicenses.user_id.license_total.' . $this->user_id,
        ]);
    }

    /**
     * если сменили емейл юзера в системе
     */
    protected function afterSaveIfChangedUserEmail()
    {
        /* обновление в таблице коллег */
        Yii::$app->db->createCommand("
                    UPDATE {{%user_colleagues}} SET
                      colleague_email=:colleague_email
                    WHERE user_id=:user_id
                ", [
            'user_id'             => $this->user_id,
            'colleague_email'     => $this->user_email,
        ])->execute();

        /* нужно сделать обновление в других таблицах */
    }

    /**
     * Обновит user_id в таблице self_host_users
     */
    protected function afterSaveUpdateSelfHostUsers()
    {
        SelfHostUsers::updateAll(['user_id' => $this->user_id], [
            'shu_email' => $this->user_email,
        ]);
    }

    /**
     * установка лимитов для бизнес-юзера на
     * основе лимитов которые установлены для бизнес-админа
     */
    protected function afterSaveSetBusinesUserLimitations()
    {
        $res3 = Yii::$app->db->createCommand("
                    UPDATE {{%user_licenses}} SET
                      lic_colleague_user_id = :user_id
                    WHERE lic_colleague_email = :colleague_email
                    RETURNING
                      lic_owner_user_id,
                      lic_end
                ", [
            'user_id'         => $this->user_id,
            'colleague_email' => $this->user_email,
        ])->queryAll();
        //var_dump($res3);exit;
        if (is_array($res3) && isset($res3[0]['lic_owner_user_id'])) {
            $UserBusinessAdmin = self::findIdentity($res3[0]['lic_owner_user_id']);
            //if (strtotime($res3['lic_end']) > time()) {
            $this->license_type = Licenses::TYPE_PAYED_BUSINESS_USER;
            $this->license_business_from = $res3[0]['lic_owner_user_id'];
            if ($UserBusinessAdmin) {
                $this->upl_limit_nodes = $UserBusinessAdmin->upl_limit_nodes;
                $this->upl_shares_count_in24 = $UserBusinessAdmin->upl_shares_count_in24;
                $this->upl_max_shares_size = $UserBusinessAdmin->upl_max_shares_size;
                $this->upl_max_count_children_on_copy = $UserBusinessAdmin->upl_max_count_children_on_copy;
                $this->upl_block_server_nodes_above_bought = $UserBusinessAdmin->upl_block_server_nodes_above_bought;
            }
            $this->save();
            //}
        }
    }

    /**
     * Обновление юзер-ид в таблице user_colleagues для этого емейла
     * Создание нотификаций о новых коллаборациях у только что зарегистрированного юзера
     * (если его пригласили как несуществующего в системе)
     */
    protected function afterSaveCreateNotificationsAboutCollaborations()
    {
        /* обновление юзер-ид в таблице user_colleagues для этого емейла */
        $res = Yii::$app->db->createCommand("
                    UPDATE {{%user_colleagues}} SET
                      user_id = :user_id
                    WHERE colleague_email = :colleague_email
                    RETURNING colleague_id, colleague_permission, collaboration_id
                ", [
            'user_id'         => $this->user_id,
            'colleague_email' => $this->user_email,
        ])->queryAll(); //foreach ($res as $v) { $v['colleague_id'] вместо $res['colleague_id']
        //var_dump($res);

        /* создание нотификаций */
        if (is_array($res)) {
            foreach ($res as $vc) {
                $query = "SELECT
                                t1.user_id,
                                t2.file_name,
                                t2.file_uuid
                              FROM {{%user_collaborations}} as t1
                              INNER JOIN {{%user_files}} as t2 ON (t1.file_uuid = t2.file_uuid) AND (t1.user_id = t2.user_id)
                              WHERE (t1.collaboration_id = :collaboration_id)
                              LIMIT 1";
                $res2 = Yii::$app->db->createCommand($query, [
                    'collaboration_id' => $vc['collaboration_id'],
                ])->queryOne();
                //var_dump($res2);
                if (isset($res2['file_name'])) {
                    $UserOwner = self::findIdentity($res2['user_id']);
                    //var_dump($UserOwner->user_email);
                    //exit;
                    if ($UserOwner) {
                        $notif = new Notifications();
                        $notif->user_id = $this->user_id;
                        $notif->notif_isnew = Notifications::IS_NEW;
                        $notif->notif_type = Notifications::TYPE_COLLABORATION_INVITE;
                        $notif->notif_data = serialize([
                            'search' => [
                                '{folder_name}',
                                '{user_email}',
                                '{access_type}',
                                '{colleague_id}',
                                '{file_uuid}',
                                //'{accept_link}',
                            ],
                            'replace' => [
                                $res2['file_name'],
                                $UserOwner->user_email,
                                $vc['colleague_permission'],
                                $vc['colleague_id'],
                                $res2['file_uuid'],
                                //Yii::$app->urlManager->createAbsoluteUrl(['user/accept-collaboration', 'colleague_id' => $vc['colleague_id']]),
                            ],
                            'links_data' => [
                                'accept_link' => ['user/accept-collaboration', 'colleague_id' => $vc['colleague_id']],
                            ],
                        ]);
                        $notif->save();
                        //var_dump($notif->notif_id);
                    }
                }
            }
        }
    }

    /**
     * Обновление юзер-ид в таблице conference_participants для этого емейла
     * Создание нотификаций о новых конференциях у только что зарегистрированного юзера
     * (если его пригласили как несуществующего в системе)
     */
    protected function afterSaveCreateNotificationsAboutConferences()
    {
        /* Обновление юзер-ид в таблице conference_participants для этого емейла */
        Yii::$app->db->createCommand("
                    UPDATE {{%conference_participants}} SET
                      user_id = :user_id
                    WHERE participant_email = :participant_email
                ", [
            'user_id'           => $this->user_id,
            'participant_email' => $this->user_email,
        ])->execute();

        /* Создание нотификаций о новых конференциях у только что зарегистрированного юзера */
        $query = "SELECT
                    t1.participant_id,
                    t1.conference_id,
                    t2.conference_name,
                    t3.user_email as user_owner_email
                  FROM {{%conference_participants}} as t1
                  INNER JOIN {{%user_conferences}} as t2 ON t1.conference_id = t2.conference_id
                  INNER JOIN {{%users}} as t3 ON t2.user_id = t3.user_id
                  WHERE (t1.user_id = :user_id)
                  AND (t1.participant_status != :STATUS_OWNER)";
        $res = Yii::$app->db->createCommand($query, [
            'user_id'      => $this->user_id,
            'STATUS_OWNER' => ConferenceParticipants::STATUS_OWNER
        ])->queryAll();
        if (is_array($res)) {
            foreach ($res as $v) {
                $notif = new Notifications();
                $notif->user_id = $this->user_id;
                $notif->notif_isnew = Notifications::IS_NEW;
                $notif->notif_type = Notifications::TYPE_CONFERENCE_INVITE;
                $notif->notif_data = serialize([
                    'search' => [
                        '{conference_name}',
                        '{user_email}',
                    ],
                    'replace' => [
                        $v['conference_name'],
                        $v['user_owner_email'],
                    ],
                    'links_data' => [
                        'accept_link' => ['conferences/accept-invitation', 'participant_id' => $v['participant_id']],
                    ],
                ]);
                $notif->save();
            }
        }
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
     * initialize user limitations
     */
    public function initUserLimitations()
    {
        $License = Licenses::findByType($this->license_type);
        if ($License) {

            if ($this->upl_limit_nodes === null) {
                $this->_ucl_limit_nodes = $License->license_limit_nodes;
            } else {
                $this->_ucl_limit_nodes = $this->upl_limit_nodes;
            }

            if ($this->upl_shares_count_in24 === null) {
                $this->_ucl_shares_count_in24 = $License->license_shares_count_in24;
            } else {
                $this->_ucl_shares_count_in24 = $this->upl_shares_count_in24;
            }

            if ($this->upl_max_shares_size === null) {
                $this->_ucl_max_shares_size = $License->license_max_shares_size;
            } else {
                $this->_ucl_max_shares_size = $this->upl_max_shares_size;
            }

            if ($this->upl_max_count_children_on_copy === null) {
                $this->_ucl_max_count_children_on_copy = $License->license_max_count_children_on_copy;
            } else {
                $this->_ucl_max_count_children_on_copy = $this->upl_max_count_children_on_copy;
            }

            if ($this->upl_block_server_nodes_above_bought === null) {
                $this->_ucl_block_server_nodes_above_bought = (bool) $License->license_block_server_nodes_above_bought;
            } else {
                $this->_ucl_block_server_nodes_above_bought = (bool) $this->upl_block_server_nodes_above_bought;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        if ($this->license_type == Licenses::TYPE_PAYED_BUSINESS_USER) {
            $this->user_company_name = "";
            if ($this->license_business_from) {
                $UserAdmin = self::findIdentity($this->license_business_from);
                if ($UserAdmin) {
                    $this->user_company_name = $UserAdmin->user_company_name;
                }
            }
        }
        $this->_old_license_type = $this->license_type;
        $this->user_last_ip = long2ip($this->user_last_ip);
        $this->setUserColor();
        $this->initUserLimitations();
    }

    /**
     * @param integer $user_id
     * @return string
     */
    public static function generateRelativePathNodeFsFor($user_id)
    {
        $tmp = intval(floor($user_id / 100)) * 100;
        //return 'UserID-' . $user_id . DIRECTORY_SEPARATOR . 'All files';
        return $tmp . DIRECTORY_SEPARATOR . 'UserID-' . $user_id . DIRECTORY_SEPARATOR . 'All files';
    }

    /**
     * Получает путь к виртуальной файловой системе нод
     *
     * @param integer $user_id
     * @return Users|null
     */
    public static function getPathNodeFS($user_id)
    {
        $User = self::findIdentity($user_id);
        if ($User) {
            $User->_relative_path = self::generateRelativePathNodeFsFor($User->user_id);
            $User->_full_path = Yii::$app->params['nodeVirtualFS'] . DIRECTORY_SEPARATOR . $User->_relative_path ;

            if (!Yii::$app->params['Stop_NodeApi_and_FM']) {
                if (!file_exists($User->_full_path)) {
                    //@mkdir($User->_full_path, 0777, true);
                    FileSys::mkdir($User->_full_path, 0777, true);
                }
                $folder_info_file = $User->_full_path . DIRECTORY_SEPARATOR . UserFiles::DIR_INFO_FILE;
                if (!file_exists($folder_info_file)) {
                    FileSys::touch($folder_info_file, 0777, 0666);
                    FileSys::fwrite($folder_info_file, serialize([
                        'file_id' => null,
                        'file_uuid' => "",
                    ]), 0666);
                }
            }
            return $User;
        }
        return null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLicenseType()
    {
        return $this->hasOne(Licenses::className(), ['license_type' => 'license_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaypalPays()
    {
        return $this->hasMany(PaypalPays::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSessions()
    {
        return $this->hasMany(Sessions::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransfers()
    {
        return $this->hasMany(Transfers::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserPayments()
    {
        return $this->hasMany(UserPayments::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserFiles()
    {
        return $this->hasMany(UserFiles::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserNodes()
    {
        return $this->hasMany(UserNode::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserUploads()
    {
        return $this->hasMany(UserUploads::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRemoteActions()
    {
        return $this->hasMany(RemoteActions::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQueueEvents()
    {
        return $this->hasMany(QueuedEvents::className(), ['user_id' => 'user_id']);
    }

    /**
     * Return color of user icon
     *
     * @param string $user_email
     * @return string
     */
    public static function getUserIcon($user_email)
    {
        preg_match("/[a-z]/siU", $user_email, $matches);
        if (isset($matches[0])) {
            return [
                'color' => mb_strtoupper(mb_substr($matches[0], 0, 1)),
                'sname' => mb_strtoupper(mb_substr($user_email, 0, 2)),
            ];
        } else {
            return [
                'color' => 'M',
                'sname' => mb_strtoupper(mb_substr($user_email, 0, 2)),
            ];
        }
    }

    /**
     * Set color of user icon
     */
    public function setUserColor()
    {
        $icon = self::getUserIcon($this->user_email);
        $this->_color = $icon['color'];
        $this->_sname = $icon['sname'];
    }

    /**
     * Check is any payment was already initialized by user
     * @return bool
     */
    public function checkPaymentInitialized()
    {
        $ttl_payment = 12 * 60 * 60;
        if ($this->payment_already_initialized) {
            if ($this->pay_type == self::PAY_CRYPTO) {
                if (strtotime($this->payment_init_date) + $ttl_payment < time()) {
                    //$this->payment_already_initialized = self::PAYMENT_NOT_INITIALIZED;
                    //$this->save();
                    return false;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return array
     */
    public static function payTypes()
    {
        return [
            self::PAY_NOTSET => ['name' => Yii::t('models/licenses', self::PAY_NOTSET), 'auto' => true],
            self::PAY_CARD   => ['name' => Yii::t('models/licenses', self::PAY_CARD),   'auto' => true],
            self::PAY_CRYPTO => ['name' => Yii::t('models/licenses', self::PAY_CRYPTO), 'auto' => false],
        ];
    }

    /**
     * @return array
     */
    public static function getPayTypesFilter()
    {
        $tmp = self::payTypes();
        foreach ($tmp as $k => $v) {
            $tmp[$k] = $v['name'];
        }
        return $tmp;
    }

    /**
     * @param string $pay_type
     * @return array|null
     */
    public static function getPayTypeName($pay_type)
    {
        $labels = self::payTypes();
        return isset($labels[$pay_type]) ? $labels[$pay_type]['name'] : null;
    }

    /**
     * @param string $pay_type
     * @return bool
     */
    public static function isAutoPayType($pay_type)
    {
        $labels = self::payTypes();
        return isset($labels[$pay_type]['auto']) ? $labels[$pay_type]['auto'] : false;
    }

    /**
     * @return int|string
     */
    public function getCountEvents()
    {
        $this->_count_events = UserFileEvents::find()->where(['user_id' => $this->user_id])->count('*');
        return $this->_count_events;
    }

    /**
     * @return array|false|int|string
     */
    public function getCountOptimizedEvents()
    {
        $where_node_filter = "";
        $query = "SELECT
                       count(*) as cnt
                     FROM {{%user_file_events}} as t1
                     INNER JOIN {{%user_files}} as t2 ON t1.file_id=t2.file_id and t1.user_id = t2.user_id
                     WHERE t1.user_id = :user_id
                     --{$where_node_filter}
                     AND (
                       (/*TODO: probably the condidtion will be removed after updating all records*/
                         t2.first_event_id is null or t2.last_event_id is null
                       )
                       OR
                       (
                         NOT(t2.is_outdated = 1 AND (t1.event_type = 2 OR t2.is_deleted = 1))
                         AND t1.event_id = t2.last_event_id
                         AND (t2.is_deleted = 0 OR t2.is_folder = 1)
                       )
                     )";

        $res = Yii::$app->db->createCommand($query, [
            'user_id' => $this->user_id,
        ])->queryOne();
        if (isset($res['cnt'])) {
            $this->_count_optimized_events = $res['cnt'];
            return $this->_count_optimized_events;
        } else {
            return 'query fail';
        }
    }

    /**
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function markUserAsDeleted()
    {
        //$transaction = Yii::$app->db->beginTransaction();

        /* Отмена всех шар */
        $shares = UserFiles::find()
            ->where([
                'user_id'   => $this->user_id,
                'is_shared' => UserFiles::FILE_SHARED
            ])
            ->all();
        $UserNode = NodeApi::registerNodeFM($this);
        if ($UserNode) {
        /** @var \common\models\UserFiles $share */
            foreach ($shares as $share) {
                $model = new NodeApi(['uuid']);
                $data['uuid'] = $share->file_uuid;
                if ($model->load(['NodeApi' => $data]) && $model->validate()) {
                    $model->sharing_disable($UserNode, false, false);
                }
            }
        }


        /* отмена коллабораций владельцем которых есть юзер */
        $collaborations = UserCollaborations::find()
            ->where(['user_id' => $this->user_id])
            ->all();
        /** @var \common\models\UserCollaborations $collaboration */
        foreach ($collaborations as $collaboration) {
            $data['owner_user_id']  = $this->user_id;
            $data['uuid']           = $collaboration->file_uuid;
            $data['is_from_system'] = true;

            $model = new CollaborationApi(['owner_user_id', 'uuid']);
            if ($model->load(['CollaborationApi' => $data]) && $model->validate()) {
                $model->collaborationDelete();
            }
        }


        /* отмена коллабораций где юзер является коллегой */
        $query = "SELECT
                    t1.collaboration_id,
                    t1.file_uuid,
                    t1.user_id as owner_user_id,
                    t2.colleague_id
                  FROM {{%user_collaborations}} as t1
                  INNER JOIN {{%user_colleagues}} as t2 ON t1.collaboration_id=t2.collaboration_id
                  WHERE (t2.user_id = :user_id)
                  AND (t1.user_id != :user_id) -- added 30/08/2018 15:27
                  AND (t2.colleague_permission != :PERMISSION_OWNER)
                  AND (t2.colleague_status != :STATUS_QUEUED_DEL)";
        $res = Yii::$app->db
            ->createCommand($query, [
                'user_id'           => $this->user_id,
                'PERMISSION_OWNER'  => UserColleagues::PERMISSION_OWNER,
                'STATUS_QUEUED_DEL' => UserColleagues::STATUS_QUEUED_DEL,
            ])
            ->queryAll();
        //var_dump($res); exit;
        if (sizeof($res)) {
            foreach ($res as $v) {
                $data['is_from_recursion'] = true;
                $data['action']            = CollaborationApi::ACTION_DELETE;
                $data['access_type']       = UserColleagues::PERMISSION_DELETE;
                $data['uuid']              = $v['file_uuid'];
                $data['colleague_id']      = $v['colleague_id'];
                $data['owner_user_id']     = $v['owner_user_id'];
                $model = new CollaborationApi(['uuid', 'owner_user_id', 'colleague_id', 'action', 'access_type']);
                if ($model->load(['CollaborationApi' => $data]) && $model->validate()) {
                    //$modesl->initOwner($res['owner_user_id']);
                    $model->colleagueDelete();
                }
            }
        }


        /* Удаление нул коллабораций, шде присутстывует этот юзер */
        $res = Yii::$app->db->createCommand("
            DELETE FROM {{%user_collaborations}} as t1
            USING {{%user_colleagues}} as t2
            WHERE (t1.collaboration_id = t2.collaboration_id)
            AND ((t1.user_id=:user_id))
            AND (t1.file_uuid IS NULL);
        ", [
            'user_id' => $this->user_id,
        ])->execute();


        /* Отзовем все выданные этим юзером лицензии */
        UserLicenses::revokeForUserId($this->user_id, true);


        /* Отправить логаут для тех нод которые онлайн */
        $UserNodes = UserNode::find()
            ->where([
                'user_id' => $this->user_id,
            ])
            ->andWhere('(node_online = :ONLINE_ON) OR (node_status IN (:STATUS_SYNCING, :STATUS_INDEXING, :STATUS_PAUSED, :STATUS_SYNCED))', [
                'ONLINE_ON'       => UserNode::ONLINE_ON,
                'STATUS_SYNCING'  => UserNode::STATUS_SYNCING,
                'STATUS_INDEXING' => UserNode::STATUS_INDEXING,
                'STATUS_PAUSED'   => UserNode::STATUS_PAUSED,
                'STATUS_SYNCED'   => UserNode::STATUS_SYNCED,
            ])
            ->all();
        if ($UserNodes) {
            $UserNodeFM = NodeApi::registerNodeFM($this);
            $arr['action_type'] = RemoteActions::TYPE_LOGOUT;
            /** @var \common\models\UserNode $UserNode */
            foreach ($UserNodes as $UserNode) {
                $arr['target_node_id'] = $UserNode->node_id;

                $model = new NodeApi(['target_node_id', 'action_type']);
                if (!$model->load(['NodeApi' => $arr]) || !$model->validate()) {
                    return [
                        'status'  => false,
                        'errcode' => NodeApi::ERROR_WRONG_DATA,
                        'info'    => $model->getErrors()
                    ];
                }

                $model->execute_remote_action($UserNodeFM, $this);
            }
        }


        /* Установим статус юзера как блокед-делетед и переименуем емейл */
        $this->user_status = self::STATUS_BLOCKED;
        $this->user_email  = 'deleted_' . date('Y-m-d_H.i.s') . '___' . $this->user_email;
        $this->user_hash = md5($this->user_email);
        $this->user_remote_hash = self::generateUserRemoteHash($this->user_email, uniqid());
        //$this->save();

        //$transaction->commit();
        return [
            'status'  => true,
            'info'    => 'ok',
        ];
    }
}
