<?php
namespace frontend\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\Json;
use yii\web\Response;
use common\helpers\FileSys;
use common\helpers\Functions;
use common\models\Users;
use common\models\UserNode;
use common\models\Servers;
use common\models\Preferences;
use common\models\UserFiles;
use common\models\UserFileEvents;
use common\models\UserServerLicenses;
use common\models\UserUploads;
use common\models\Licenses;
use common\models\Sessions;
use common\models\UserCollaborations;
use common\models\UserColleagues;
use common\models\RemoteActions;
use common\models\ColleaguesReports;
use common\models\QueuedEvents;
use common\models\MailTemplatesStatic;
use common\models\RedisSafe;
use common\models\TrafficLog;
use common\models\BadLogins;
use common\models\Notifications;
use frontend\models\Jobs\CopyFolderJob;
use frontend\models\forms\SupportForm;
use frontend\models\forms\PasswordResetRequestForm;

/**
 * NodeApi
 *
 * @property string user_password
 * @property \yii\mutex\FileMutex $mutex
 * @property \yii\queue\file\Queue $queue
 * @property \yii\redis\Connection $redis
 */
class NodeApi extends Model
{
    const ERROR_STOPPED_FOR_MAINTENANCE = 'STOPPED_FOR_MAINTENANCE';
    const ERROR_WRONG_YII_CONF = 'WRONG_YII_CONF';
    const ERROR_INVALID_JSON = 'INVALID_JSON';
    const ERROR_TOO_MANY_TRIES = 'TOO_MANY_TRIES';
    const ERROR_USER_NOT_FOUND = 'USER_NOT_FOUND';
    const ERROR_NODE_NOT_FOUND = 'NODE_NOT_FOUND';
    const ERROR_NODE_SIGN_NOT_FOUND = 'NODE_SIGN_NOT_FOUND';
    const ERROR_SIGNATURE_INVALID = 'SIGNATURE_INVALID';
    const ERROR_USER_NODE_MISMATCH = 'USER_NODE_MISMATCH';
    const ERROR_USERHASH_NODEHASH = 'USERHASH_NODEHASH';
    const ERROR_WRONG_DATA = 'WRONG_DATA';
    const ERROR_NODEHASH_EXIST = 'NODEHASH_EXIST';
    const ERROR_EMAIL_EXIST = 'EMAIL_EXIST';
    const ERROR_WRONG_OLDPASSWD = 'WRONG_OLDPASSWD';
    const ERROR_ADD_NODE_FAILED = 'ADD_NODE_FAILED';
    const ERROR_NODE_EXIST = 'NODE_EXIST';
    const ERROR_CANT_SELF_DELETE = 'CANT_SELF_DELETE';
    const ERROR_CANT_SELF_HIDE = 'CANT_SELF_HIDE';
    const ERROR_DATABASE_FAILURE = 'DATABASE_FAILURE';
    const ERROR_UPLOADED_FILE_NOT_FOUND = 'UPLOADED_FILE_NOT_FOUND';
    const ERROR_FS_SYNC = 'FS_SYNC';
    const ERROR_FS_SYNC_NOT_FOUND = 'FS_SYNC_NOT_FOUND';
    const ERROR_FS_SYNC_PARENT_NOT_FOUND = 'FS_SYNC_PARENT_NOT_FOUND';
    const ERROR_FS_SYNC_COLLABORATION_MOVE = 'FS_SYNC_COLLABORATION_MOVE';
    const ERROR_FS_TRY_LATER = 'FS_TRY_LATER';
    const ERROR_FS_QUEUE_LIMIT_TASKS = 'FS_QUEUE_LIMIT_TASKS';
    const ERROR_SIGNAL_SERVER_CONNECT = 'SIGNAL_SERVER_CONNECT';
    const ERROR_SHARE_NOT_FOUND = 'SHARE_NOT_FOUND';
    const ERROR_TOKEN_INVALID = 'TOKEN_INVALID';
    const ERROR_LICENSE_ACCESS = 'LICENSE_ACCESS';
    const ERROR_LICENSE_LIMIT = 'LICENSE_LIMIT';
    const ERROR_COLLABORATION_ACCESS = 'COLLABORATION_ACCESS';
    const ERROR_COLLABORATION_DATA = 'COLLABORATION_DATA';
    const ERROR_NODE_WIPED = 'NODE_WIPED';
    const ERROR_NODE_LOGOUT_EXIST = 'NODE_LOGOUT_EXIST';
    const ERROR_EVENT_NOT_FOUND = 'EVENT_NOT_FOUND';
    const ERROR_NODE_IS_DELETED = 'NODE_IS_DELETED';
    const ERROR_BAD_NODE_STATUS = 'BAD_NODE_STATUS';
    const ERROR_FILE_NOT_CHANGED = 'FILE_NOT_CHANGED';
    const ERROR_SHARE_WRONG_PASSWORD = 'SHARE_WRONG_PASSWORD';
    const ERROR_MOVE_PROHIBITED = 'MOVE_PROHIBITED';
    const ERROR_NULL_EVENT_UUID = 'NULL_EVENT_UUID';
    const ERROR_FAILED_SEND_EMAIL = 'FAILED_SEND_EMAIL';
    const ERROR_FILE_PATH_MAX_LENGTH = 'FILE_PATH_MAX_LENGTH';
    const ERROR_OPERATION_DENIED = 'OPERATION_DENIED';
    const ERROR_LOCKED_CAUSE_TOO_MANY_BAD_LOGIN = 'LOCKED_CAUSE_TOO_MANY_BAD_LOGIN';
    const ERROR_DENIED_FOR_SELF_HOSTED = 'DENIED_FOR_SELF_HOSTED';
    const ERROR_DENIED_FOR_THIS_LICENSE_ON_SELF_HOSTED = 'DENIED_FOR_THIS_LICENSE_ON_SELF_HOSTED';
    const ERROR_UPLOAD_NO_FILES_GIVEN = 'UPLOAD_NO_FILES_GIVEN';

    const DEFAULT_SHARE_TTL = 30 * 24 * 60 * 60;

    const DIFF_TYPE_DIRECT = 'direct';
    const DIFF_TYPE_REVERSED = 'reversed';

    protected $redis;
    protected $mutex;
    protected $queue;

    public $error_data = null;
    public $dynamic_rules = null;

    public $get;
    public $user_email, $user_password, $user_hash, $user_id;
    public $old_password, $new_password;
    public $node_hash, $node_sign, $node_id, $node_name, $node_ip, $node_status;
    public $node_useragent, $node_osname, $node_ostype, $node_devicetype;
    public $file_name, $new_file_name, $file_md5, $file_size, $uuid, $file_uuid, $copy_file_uuid, $copy_folder_uuid, $hash;
    public $folder_name, $new_folder_name, $folder_uuid, $new_folder_uuid, $parent_folder_uuid, $new_parent_folder_uuid;
    public $target_folder_name, $target_parent_folder_uuid, $source_folder_uuid;
    public $share_hash, $share_group_hash, $share_lifetime, $share_ttl, $share_password, $share_keep_password, $only_change_share_settings;
    public $signal_passphrase;
    public $last_event_id, $event_id;
    public $site_token;
    public $upload_id;
    public $bytes;
    public $is_collaborated, $collaboration_id, $is_owner;
    public $is_from_colleagueDelete = false;
    public $target_node_id, $action_type, $action_uuid;
    public $diff_uuid, $diff_file_uuid, $rev_diff_file_uuid;
    public $diff_size, $diff_file_size, $rev_diff_file_size;
    public $diff_type;
    public $direct_patch_event_id, $reversed_patch_event_id;
    public $event_invisible;
    public $node_online, $node_upload_speed, $node_download_speed, $node_disk_usage;
    public $event_uuid;
    public $is_rename = false;
    public $is_restore_patch = false;
    public $event_creator_user_id, $event_creator_node_id;
    public $subject, $body;
    public $limit, $offset, $from;
    public $checked_event_id, $events_count_check;
    public $node_without_backup;
    public $rs_id, $rs_type;
    public $interval, $tx_wd, $rx_wd, $tx_wr, $rx_wr, $is_share;
    public $traffic_data;
    public $remote_ip;
    public $is_server;
    public $colleague_id;
    public $log_file_name;

    public $DOP_onlyForUserId, $DOP_restorePatchTTL;

    /**************************** +++ GLOBAL +++ ***************************/
    /**
     * NodeApi constructor.
     * @param array $required_fields Поля которые будут проверяться на наличие в джсоне
     */
    public function __construct(array $required_fields = [])
    {
        if (is_array($required_fields) && sizeof($required_fields)) {
            $this->dynamic_rules = [[$required_fields, 'required', 'message' => 'Fields ' . implode(', ', $required_fields) . ' are required.']];
        }
        $this->redis = Yii::$app->redis;
        $this->mutex = Yii::$app->mutex;
        $this->queue = (isset(Yii::$app->queue) && method_exists(Yii::$app->queue, 'push')) ? Yii::$app->queue : false;

        parent::__construct();
    }

    /**
     * Правила валидации данных
     * @return array
     */
    public function rules()
    {
        $rules = [
            [['get'], 'in', 'range' => ['candidate'], 'message' => 'Wrong json data. Section get is wrong.'],
            [['user_email'], 'email'],
            [['user_password', 'old_password', 'new_password'], 'string', 'length' => 128],
            [[
                'user_id',
                'node_id',
                'event_creator_user_id',
                'event_creator_node_id',
                'upload_id',
                'collaboration_id',
                'colleague_id',
                'is_collaborated',
                'is_owner',
                'target_node_id',
                'rs_id'], 'integer'],
            [['user_hash', 'node_hash', 'node_sign'], 'string', 'length' => 128],
            [['node_name'], 'string', 'max' => 30],
            [['node_useragent'], 'string', 'max' => 255],
            [['node_osname'], 'string', 'max' => 255],
            [['node_ostype'], 'in', 'range' => [
                UserNode::OSTYPE_WEBFM,
                UserNode::OSTYPE_ANDROID,
                UserNode::OSTYPE_DARWIN,
                UserNode::OSTYPE_IOS,
                UserNode::OSTYPE_LINUX,
                UserNode::OSTYPE_WINDOWS,
            ]],
            [['node_devicetype'], 'in', 'range' => [
                UserNode::DEVICE_BROWSER,
                UserNode::DEVICE_DESKTOP,
                UserNode::DEVICE_PHONE,
                UserNode::DEVICE_TABLET,
            ]],
            [['action_type'], 'in', 'range' => [
                RemoteActions::TYPE_LOGOUT,
                RemoteActions::TYPE_WIPE,
                RemoteActions::TYPE_CREDENTIALS,
            ]],
            [['node_status'], 'in', 'range' => [
                UserNode::STATUS_DEACTIVATED,
                UserNode::STATUS_ACTIVE,
                UserNode::STATUS_DELETED,
                UserNode::STATUS_SYNCED,
                UserNode::STATUS_SYNCING,
                UserNode::STATUS_LOGGEDOUT,
                UserNode::STATUS_WIPED,
                UserNode::STATUS_POWEROFF,
                UserNode::STATUS_PAUSED,
                UserNode::STATUS_INDEXING,
            ]],
            [['event_invisible'], 'in', 'range' => [UserFileEvents::EVENT_INVISIBLE, UserFileEvents::EVENT_VISIBLE]],
            [['node_ip', 'remote_ip'], 'ip', 'ipv6' => false],
            [['share_hash', 'share_group_hash'], 'string', 'length' => 32],
            [['share_password'], 'string', 'max' => 32],
            //[['share_lifetime'], 'safe', 'skipOnEmpty' => true],
            [['share_lifetime'], 'datetime', 'format' => 'php:Y-m-d H:m:s'],
            [['share_ttl'], 'integer', 'min' => -1],
            [[
                'file_md5',
                'file_uuid',
                'hash',
                'diff_uuid',
                'diff_file_uuid',
                'rev_diff_file_uuid',
                'action_uuid',
                'event_uuid'], 'string', 'length' => 32],
            [[
                'uuid',
                'folder_uuid',
                'new_folder_uuid',
                'parent_folder_uuid',
                'new_parent_folder_uuid',
                'target_parent_folder_uuid',
                'source_folder_uuid',
                'copy_file_uuid',
                'copy_folder_uuid',], 'string', 'length' => 32, 'skipOnEmpty' => true],
            [['file_name', 'new_file_name', 'folder_name', 'new_folder_name', 'target_folder_name'], 'string', 'encoding' => '8bit', 'min' => 1, 'max' => UserFiles::FILE_NAME_MAX_LENGTH, 'tooLong' => '{attribute} should contain at most {max} bytes.'],
            [['file_name', 'new_file_name', 'folder_name', 'new_folder_name', 'target_folder_name'], 'filter', 'filter' => 'trim', 'skipOnArray' => true],
            [['file_name', 'new_file_name', 'folder_name', 'new_folder_name', 'target_folder_name'], 'validateFilename'],
            [['file_size', 'diff_size', 'diff_file_size', 'rev_diff_file_size'], 'integer', 'min' => 0],
            [['signal_passphrase'], 'string', 'length' => 128],
            [['last_event_id', 'event_id'], 'integer'],
            //[['site_token'], 'string', 'max' => 32],
            //[['site_token'], 'string', 'length' => 32],
            [['site_token'], 'string', 'min' => 10, 'max' => 255],
            [['bytes'], 'integer', 'min' => 0],
            [['is_from_colleagueDelete', 'is_rename', 'share_keep_password', 'only_change_share_settings', 'is_restore_patch'], 'boolean'],
            [['diff_type'], 'in', 'range' => [self::DIFF_TYPE_DIRECT, self::DIFF_TYPE_REVERSED]],
            [['direct_patch_event_id', 'reversed_patch_event_id'], 'integer', 'min' => 0],
            [['node_online', 'is_server'], 'boolean'],
            [['is_server'], 'default', 'value' => false],
            [['node_upload_speed', 'node_download_speed'], 'double', 'min' => 0],
            [['node_disk_usage'], 'integer', 'min' => 0],
            [['subject', 'body'], 'safe'],
            [['subject'], 'in', 'range' => [
                SupportForm::SUBJECT_TECHNICAL,
                SupportForm::SUBJECT_OTHER,
                SupportForm::SUBJECT_LICENSES,
                SupportForm::SUBJECT_FEEDBACK
            ]],
            [['limit', 'offset', 'from', 'checked_event_id', 'events_count_check', 'node_without_backup'], 'integer', 'min' => 0],
            [['rs_type'], 'string', 'max' => 32],
            [['rs_type'], 'in', 'range' => [
                RedisSafe::TYPE_COLLABORATION_CHANGES,
                RedisSafe::TYPE_UPLOAD_EVENTS,
                RedisSafe::TYPE_FS_EVENTS,
                RedisSafe::TYPE_PATCHES_INFO,
                RedisSafe::TYPE_LICENSE_CHANGES,
                RedisSafe::TYPE_REMOTE_ACTIONS,
                RedisSafe::TYPE_SHARING_EVENTS,
                RedisSafe::TYPE_NODE_STATUS,
            ]],
            [['interval', 'tx_wd', 'rx_wd', 'tx_wr', 'rx_wr'], 'integer', 'min' => 0],
            [['traffic_data'], 'checkIsArray'],
            [['is_share'], 'in', 'range' => [TrafficLog::IS_SHARE, TrafficLog::NOT_SHARE]],
            [['DOP_onlyForUserId', 'DOP_restorePatchTTL'], 'integer', 'min' => 0],
            [['log_file_name'], 'string', 'min' => 32, 'max' => 36],
        ];
        if (is_array($this->dynamic_rules)) {
            return array_merge($this->dynamic_rules, $rules);
        } else {
            return $rules;
        }
    }

    /**
     * @param $attribute
     * @param $params
     * @return bool
     */
    public function checkIsArray($attribute, $params) {
        if (!is_array($this->$attribute)){
            $this->addError('traffic_data','traffic_data is not array!');
        }

        $arr = $this->$attribute;

        foreach ($arr as $v) {
            if (!isset($v['interval'], $v['event_uuid'], $v['tx_wd'], $v['rx_wd'], $v['tx_wr'], $v['rx_wr'], $v['is_share'])) {
                $this->addError('traffic_data', 'traffic_data is not a valid array!');
                return false;
            }
        }
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateFilename($attribute, $params)
    {
        $ret = UserFiles::checkSystemReservedFilename($this->$attribute);
        if (isset($ret['error'])) {
            //$this->addError('SUB_ERROR_CODE', $ret);
            $ret['error'] = implode(' && ',$ret['error']);
            $this->addError('error_file_name', $ret['error']);
            $this->error_data = $ret;
        }
    }

    /**
     * Валидация сигнатуры ноды
     * @return bool
     */
    public function validateSignature()
    {
        //return true;
        return ($this->node_sign === hash("sha512", $this->node_hash . ip2long($_SERVER['REMOTE_ADDR'])));
    }
    /**************************** --- GLOBAL --- ***************************/


    /************************** +++ USER NODE +++ **************************/
    /**
     * Метод для регистрации нового пользователя через джсон запрос от ноды
     * @return array
     */
    public function signup()
    {
        /* Начинаем транзакцию */
        $transaction = Yii::$app->db->beginTransaction();
        $User = new Users();
        $User->user_name = Functions::getNameFromEmail($this->user_email);;
        $User->user_email = $this->user_email;
        $User->license_type = Licenses::TYPE_FREE_TRIAL;
        $User->license_expire = date(SQL_DATE_FORMAT, time() + Licenses::getCountDaysTrialLicense() * 86400);
        $User->setPassword($this->user_password, false);
        $User->generateAuthKey();
        $User->user_last_ip  = Yii::$app->request->getUserIP();
        $User->generatePasswordResetToken();
        if ($User->save()) {

            /* Зарегистрируем сразу служебную ноду ФМ */
            self::registerNodeFM($User);

            /* Зарегистрируем текущую ноду */
            $UserNode                     = new UserNode();
            $UserNode->user_id            = $User->user_id;
            $UserNode->node_hash          = $this->node_hash;
            $UserNode->node_name          = $this->node_name;
            $UserNode->node_osname        = $this->node_osname;
            $UserNode->node_ostype        = $this->node_ostype;
            $UserNode->node_devicetype    = $this->node_devicetype;
            $UserNode->node_useragent     = $this->node_useragent ? $this->node_useragent : "{$this->node_osname} ({$this->node_ostype}), {$this->node_devicetype}";
            $UserNode->node_online        = UserNode::ONLINE_OFF;
            $UserNode->node_status        = UserNode::STATUS_ACTIVE;
            $UserNode->node_logout_status = UserNode::LOGOUT_STATUS_READY_TO;
            $UserNode->node_wipe_status = UserNode::WIPE_STATUS_READY_TO;
            $UserNode->is_server = $this->is_server
                ? UserNode::IS_SERVER
                : UserNode::NOT_SERVER;
            if ($UserNode->save()) {

                /* успешное завершение транзакции */
                $transaction->commit();

                $session = new Sessions();
                $session->user_id = $User->user_id;
                $session->node_id = $UserNode->node_id;
                $session->sess_action = Sessions::ACTION_REGISTER;
                $session->save();

                MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_newRegister, $User->user_email, ['UserObject' => $User]);

                /* Возвращаем в случае успешного внесения пользоватея и ноды в БД */
                return [
                    'result' => "success",
                    'user_hash' => $User->user_remote_hash,
                    'info' => "User registered successful."
                ];

            } else {
                $transaction->rollBack();
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_DATABASE_FAILURE,
                    'info' => "An internal server error occurred.",
                    'debug' => $UserNode->getErrors(),
                ];
            }
        } else {
            $transaction->rollBack();
            return [
                'result' => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $User->getErrors(),
            ];
        }
    }

    /**
     * Метод для смены пароля через джсон запрос от ноды
     * @param $User \common\models\Users
     * @return array
     */
    public function changepassword($User)
    {
        $User->setPassword($this->new_password, false);
        if ($User->save()) {
            return [
                'result' => "success",
                'user_hash' => $User->user_remote_hash,
                'info' => "User password changed successful.",
            ];
        } else {
            return [
                'result' => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $User->getErrors(),
            ];
        }
    }

    /**
     * Метод для запроса сброса пароля
     * @return array
     */
    public function resetpassword()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(['PasswordResetRequestForm' => ['user_email' => $this->user_email]])) {
            if ($model->sendEmail()) {
                return [
                    'result' => "success",
                    'info'   => Yii::t("forms/reset-password-form", "Instructions_recovery_sent"),
                ];
            } else {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_FAILED_SEND_EMAIL,
                    'info' => "Sorry, we are unable to reset password for email provided.",
                ];
            }
        }

        return [
            'result' => "error",
            'errcode' => self::ERROR_DATABASE_FAILURE,
            'info' => "Sorry, we are unable to reset password for email provided.",
            'debug' => $model->getErrors(),
        ];
    }

    /**
     * Метод для разлогинивания ноды
     * @param \common\models\UserNode $UserNode
     * @return array
     */
    public function logout($UserNode)
    {
        $UserNode->node_status = UserNode::STATUS_LOGGEDOUT;
        //$UserNode->node_logout_status = UserNode::LOGOUT_STATUS_SUCCESS;

        if ($UserNode->save()) {
            return [
                'result' => "success",
                'info' => "Logged Out successfully.",
            ];
        } else {
            return [
                'result' => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $UserNode->getErrors(),
            ];
        }
    }

    /**
     * @param integer $node_id
     * @return array
     */
    public static function getRemoteActions($node_id)
    {
        $ret = RemoteActions::find()
            ->asArray()
            ->addSelect([
                'action_type',
                'action_uuid',
                'action_data',
            ])
            ->andWhere(
                "(target_node_id=:node_id) AND (action_end_time IS NULL)",
                ['node_id' => $node_id]
            )
            ->all();
        foreach ($ret as $k=>$v) {
            $ret[$k]['action_data'] = unserialize($ret[$k]['action_data']);
        }
        return $ret;
    }

    /**
     * Метод для авторизации пользователя через джсон запрос от ноды
     * @param $User \common\models\Users
     * @param $UserNode \common\models\UserNode
     * @return array
     */
    public function login($User, $UserNode)
    {
        /* Проверка что не превышено число нод */
        //$license_limit_nodes = Licenses::getCountLicenseLimitNodes($User->license_type, $User);
        $license_limit_nodes = $User->_ucl_limit_nodes;
        if ($license_limit_nodes > 0) {
            $count = UserNode::find()
                ->where(['user_id' => $User->user_id])
                //->andWhere('node_status != :node_status', ['node_status' => UserNode::STATUS_DELETED])
                ->andWhere('node_status NOT IN (:STATUS_DELETED, :STATUS_DEACTIVATED, :STATUS_WIPED)', [
                    'STATUS_DELETED'     => UserNode::STATUS_DELETED,
                    'STATUS_DEACTIVATED' => UserNode::STATUS_DEACTIVATED,
                    'STATUS_WIPED'       => UserNode::STATUS_WIPED
                ])
                ->andWhere('node_id != :current_node_id', ['current_node_id' => $UserNode->node_id])
                ->andWhere('node_devicetype != :node_devicetype', ['node_devicetype' => UserNode::DEVICE_BROWSER])
                ->count();
            if ($count >= $license_limit_nodes) {
                return [
                    'result'         => "error",
                    'errcode' => self::ERROR_LICENSE_LIMIT,
                    'remote_actions' => self::getRemoteActions($UserNode->node_id),
                    'info' => Yii::t('app/node-api', "Login_limit_nodes", ['license_limit_nodes' => $license_limit_nodes]),
                ];
            }
        }

        /* +++ update node */
        //$UserNode->node_status = UserNode::STATUS_ACTIVE;
        $UserNode->node_name = $this->node_name;
        $UserNode->node_osname = $this->node_osname;
        $UserNode->node_ostype = $this->node_ostype;
        $UserNode->node_devicetype = $this->node_devicetype;
        $UserNode->node_useragent = $this->node_useragent
            ? $this->node_useragent
            : "{$this->node_osname} ({$this->node_ostype}), {$this->node_devicetype}";
        $UserNode->node_last_ip = null; //сбросим ИП и он вычислится заново при сохранении
        $UserNode->node_logout_status = UserNode::LOGOUT_STATUS_READY_TO;
        $UserNode->node_wipe_status = UserNode::WIPE_STATUS_READY_TO;
        $UserNode->is_server = $this->is_server
            ? UserNode::IS_SERVER
            : UserNode::NOT_SERVER;
        $UserNode->save();

        $User->user_last_ip  = Yii::$app->request->getUserIP();
        $User->save();

        /* +++ servers list */
        $servers = Servers::find()->asArray()->addSelect([
            'server_id',
            'server_type',
            'server_url',
            'server_login',
            'server_password',
            'server_title'
        ])
            ->where(['server_status' => Servers::SERVER_ACTIVE_YES])
            ->all();

        /* nodes list */
        $nodes = UserNode::find()
            ->asArray()
            ->addSelect([
                'node_id',
                'node_online',
                'node_name',
                'node_useragent',
                'node_osname',
                'node_ostype',
                'node_devicetype',
                'node_status'
            ])
            ->where(['user_id' => $User->user_id])
            ->andWhere('node_status != :node_status', ['node_status' => UserNode::STATUS_DELETED])
            ->andWhere("node_devicetype != :node_devicetype", ['node_devicetype' => UserNode::DEVICE_BROWSER])
            ->all();

        $nodes_ids = [];
        if ($nodes) {
            foreach ($nodes as $k => $v)
                $nodes_ids[] = $v['node_id'];
        }

        /* last_event_uuid (на самом деле это первое из событий после удаленных по крону) */
        /** @var \common\models\UserFileEvents $last_event_uuid */
        /*
        $last_event_uuid = UserFileEvents::find()
            ->where(['user_id' => $User->user_id])
            ->orderBy(['event_id' => SORT_ASC])
            ->limit(1)
            ->one();
        */

        /* Записываем лог в базу об акшене логина */
        $session = new Sessions();
        $session->user_id = $User->user_id;
        $session->node_id = $UserNode->node_id;
        $session->sess_action = Sessions::ACTION_LOGIN;
        $session->save();

        /* соберем набор ремот-акшенов, если он есть */
        $remote_actions = self::getRemoteActions($UserNode->node_id);

        /* если нет ремот-акшенов, то имеет смысл следующая проверка на серверные лицензии */
        if (!sizeof($remote_actions)) {
            /* Если нода серверная, то пытаемся получить для нее лицензию и если нет свободной - ошибка */
            if ($UserNode->is_server) {
                if ($User->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                    if (!UserServerLicenses::tryObtainLicenseByNode($User->user_id, $UserNode)) {
                        if ($User->_ucl_block_server_nodes_above_bought) {
                            return [
                                'result' => "error",
                                'errcode' => NodeApi::ERROR_LICENSE_ACCESS,
                                'info' => "Login method by server node failed cause no available free server license for this node.",
                            ];
                        }
                    }
                } elseif ($User->license_type == Licenses::TYPE_PAYED_BUSINESS_USER) {
                    if (!UserServerLicenses::tryObtainLicenseByNode($User->license_business_from, $UserNode)) {
                        if ($User->_ucl_block_server_nodes_above_bought) {
                            return [
                                'result' => "error",
                                'errcode' => NodeApi::ERROR_LICENSE_ACCESS,
                                'info' => "Login method by server node failed cause no available free server license for this node.",
                            ];
                        }
                    }
                } else {
                    if ($User->_ucl_block_server_nodes_above_bought) {
                        return [
                            'result' => "error",
                            'errcode' => NodeApi::ERROR_LICENSE_ACCESS,
                            'info' => "Login method by server node failed cause no available free server license for this node.",
                        ];
                    }
                }
            }
        }
        
        /* Возвращаем в случае если нашли в бд запись */
        return [
            'result' => "success",
            'info' => "User authorized successful.",
            'user_id' => $User->user_id,
            'user_hash' => $User->user_remote_hash,
            'license_type' => $User->license_type,
            'servers' => $servers,
            'nodes' => $nodes_ids,
            'max_path_length' => UserFiles::FILE_PATH_MAX_LENGTH,
            'max_file_name_length' => UserFiles::FILE_NAME_MAX_LENGTH,
            'remote_actions' => $remote_actions,
            //'last_event_uuid' => ($last_event_uuid) ? $last_event_uuid->event_uuid : null, // last patch
            'last_event_uuid' => $User->first_event_uuid_after_cron,
        ];
    }

    /**
     * Метод для добавления новой ноды через джсон запрос от ноды
     * @param $User \common\models\Users
     * @return \common\models\UserNode | bool
     * @throws Exception
     */
    public static function registerNodeFM($User)
    {
        //$UserNode = UserNode::findOne(['user_id' => $User->user_id, 'node_ostype' => UserNode::OSTYPE_WEBFM]);
        $UserNode = UserNode::findNodeWebFM($User->user_id);
        //var_dump($UserNode); exit;
        if (!$UserNode) {
            $UserNode = new UserNode();
            $UserNode->user_id = $User->user_id;
            $UserNode->node_hash = hash("sha512", $User->user_id . $User->user_created . microtime());
            $UserNode->node_name = Yii::t('models/user-node', 'WEBFM_NODE_NAME');
            $UserNode->node_osname = Yii::t('models/user-node', 'WEBFM_NODE_NAME');
            $UserNode->node_ostype = UserNode::OSTYPE_WEBFM;
            $UserNode->node_devicetype = UserNode::DEVICE_BROWSER;
            $UserNode->node_useragent = "{$UserNode->node_osname} ({$UserNode->node_ostype}), {$UserNode->node_devicetype}";
            $UserNode->node_online = UserNode::ONLINE_ON;
            $UserNode->node_status = UserNode::STATUS_ACTIVE;
            $UserNode->node_logout_status = UserNode::LOGOUT_STATUS_READY_TO;
            $UserNode->node_wipe_status = UserNode::WIPE_STATUS_READY_TO;
            $UserNode->is_server = UserNode::NOT_SERVER;

            if (!$UserNode->save()) {
                throw new Exception(Json::encode($UserNode->getErrors()));
            }
        }

        return $UserNode;
    }

    /**
     * Метод для добавления новой ноды через джсон запрос от ноды
     * @param $User \common\models\Users
     * @return array
     */
    public function addNode($User)
    {
        //$license_limit_nodes = Licenses::getCountLicenseLimitNodes($User->license_type, $User);
        $license_limit_nodes = $User->_ucl_limit_nodes;
        if ($license_limit_nodes > 0) {
            $count = UserNode::find()
                ->where(['user_id' => $User->user_id])
                //->andWhere('node_status != :node_status', ['node_status' => UserNode::STATUS_DELETED])
                ->andWhere('node_status NOT IN (:STATUS_DELETED, :STATUS_DEACTIVATED, :STATUS_WIPED)', [
                    'STATUS_DELETED'     => UserNode::STATUS_DELETED,
                    'STATUS_DEACTIVATED' => UserNode::STATUS_DEACTIVATED,
                    'STATUS_WIPED'       => UserNode::STATUS_WIPED
                ])
                ->andWhere('node_devicetype != :node_devicetype', ['node_devicetype' => UserNode::DEVICE_BROWSER])
                ->count();
            if ($count >= $license_limit_nodes) {
                return [
                    'status' => false,
                    'UserNode' => false,
                    'errcode' => self::ERROR_LICENSE_LIMIT,
                    'info' => Yii::t('app/node-api', "Login_limit_nodes", ['license_limit_nodes' => $license_limit_nodes]),
                    //'info' => "Your license limit nodes set to {$license_limit_nodes}.",
                ];
            }
        }

        $UserNode = new UserNode();
        $UserNode->user_id = $User->user_id;
        $UserNode->node_hash = $this->node_hash;
        $UserNode->node_name = $this->node_name;
        $UserNode->node_osname = $this->node_osname;
        $UserNode->node_ostype = $this->node_ostype;
        $UserNode->node_devicetype = $this->node_devicetype;
        $UserNode->node_useragent = $this->node_useragent ? $this->node_useragent : "{$this->node_osname} ({$this->node_ostype}), {$this->node_devicetype}";
        $UserNode->node_online = UserNode::ONLINE_OFF;
        $UserNode->node_status = UserNode::STATUS_ACTIVE;
        $UserNode->is_server = $this->is_server
            ? UserNode::IS_SERVER
            : UserNode::NOT_SERVER;
        if ($UserNode->save()) {

            $session = new Sessions();
            $session->user_id = $User->user_id;
            $session->node_id = $UserNode->node_id;
            $session->sess_action = Sessions::ACTION_ADDNODE;
            $session->save();

            /* Возвращаем в случае успешного внесения ноды в БД */
            return [
                'status' => true,
                'UserNode' => $UserNode,
                'errcode' => 'ok',
                'info' => 'ok',
            ];
            //return ['result' => "success", "user_hash" => $User->user_remote_hash, 'info' => "Added successful."];
        } else {
            return [
                'status' => false,
                'UserNode' => false,
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $UserNode->getErrors(),
            ];
            //return ['result' => "error", 'info' => $UserNode->getErrors()];
        }
    }

    /**
     * Метод для удаления ноды через джсон запрос от ноды
     * @param $User \common\models\Users
     * @return array
     */
    public function delNode($User)
    {
        $deleteUserNode = UserNode::findOne([
            'node_id' => $this->node_id,
            'user_id' => $User->user_id
        ]);
        if (!$deleteUserNode) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_NODE_NOT_FOUND,
                'info' => "Node is not deleted. Node with this node_id not found",
            ];
        }

        $deleteUserNode->node_status = UserNode::STATUS_DELETED;
        //if ($deleteUserNode->delete()) {
        if ($deleteUserNode->save()) {
            return [
                'result' => "success",
                'info' => "Deleted successfully.",
            ];
        } else {
            return [
                'result' => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $deleteUserNode->getErrors(),
            ];
        }
    }

    /**
     * Метод для удаления ноды через джсон запрос от ноды
     * @param $User \common\models\Users
     * @param int|null $ownerServerLicense_user_id
     * @return array
     */
    public function hideNode($User, $ownerServerLicense_user_id=null)
    {
        $transaction = Yii::$app->db->beginTransaction();

        $hideUserNode = UserNode::findOne([
            'user_id' => $User->user_id,
            'node_id' => $this->node_id,
        ]);
        if (!$hideUserNode) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_NODE_NOT_FOUND,
                'info' => "Node is not hid. Node with this node_id not found",
            ];
        }

        $hideUserNode->node_status = UserNode::STATUS_DEACTIVATED;
        if ($hideUserNode->save()) {

            if ($hideUserNode->is_server) {
                /* проверим не занимает ли нода серверную лицензию, и если да, то освободим */
                $cnt = UserServerLicenses::updateAll([
                    'lic_srv_colleague_user_id' => null,
                    'lic_srv_node_id' => null,
                ], [
                    'lic_srv_node_id' => $this->node_id,
                ]);

                /* А тут проверим есть ли ноды которые нуждаются в серверной лицензии и если есть, попробуем назначить */
                if ($ownerServerLicense_user_id === null) {
                    $ownerServerLicense_user_id = $User->user_id;
                }
                //if ($cnt) {
                    $nodesList = UserServerLicenses::getNodesThatNeedServerLicense($ownerServerLicense_user_id);
                    if ($nodesList && isset($nodesList[0]['node_id'])) {
                        foreach ($nodesList as $node) {
                            $candidateNode = UserNode::findIdentity($node['node_id']);
                            if ($candidateNode) {
                                UserServerLicenses::tryObtainLicenseByNode($ownerServerLicense_user_id, $candidateNode);
                            }
                        }
                    }
                //}
            }


            /* попытка отправки инфы на редис */
            try {
                $this->redis->publish(
                    "node:{$hideUserNode->node_id}:status",
                    Json::encode([
                        'node_status' => $hideUserNode->node_status,
                        'node_id'     => $hideUserNode->node_id,
                        'user_id'     => $hideUserNode->user_id,
                    ])
                );
                $this->redis->save();
            } catch (\Exception $e) {
                RedisSafe::createNewRecord(
                    RedisSafe::TYPE_NODE_STATUS,
                    $hideUserNode->user_id,
                    $hideUserNode->node_id,
                    Json::encode([
                        'action'           => 'node_status',
                        'chanel'           => "node:{$hideUserNode->node_id}:status",
                        'user_id'          => $hideUserNode->user_id,
                        'node_id'          => $hideUserNode->node_id,
                        'node_status'      => $hideUserNode->node_status,
                    ])
                );
            }

            /* успешное завершение транзакции */
            $transaction->commit();
            return [
                'result' => "success",
                'info' => "Hid successfully.",
                'node_id' => $hideUserNode->node_id,
            ];

        } else {
            $transaction->rollBack();
            return [
                'result' => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $hideUserNode->getErrors(),
            ];
        }

    }

    /**
     * @param \common\models\Users $User
     * @return bool
     */
    public function support($User)
    {
        /*
        $tk  = new Tikets();
        $tk->user_id     = $User->user_id;
        $tk->tiket_email = $User->user_email;
        $tk->tiket_name  = $User->user_name;
        $tk->tiket_theme = $this->subject;
        $tk->tiket_count_new_admin += 1;
        $tk->tiket_count_new_user   = 0;
        if ($tk->save()) {
            $tkm = new TiketsMessages();
            $tkm->tiket_id = $tk->tiket_id;
            $tkm->message_text = $this->body;
            $tkm->user_id = $tk->user_id;
            $tkm->save();
        }
        */
        $to = Preferences::getValueByKey("supportEmail_{$this->subject}", Preferences::getValueByKey('adminEmail'));
        //$User = Users::findIdentity(Yii::$app->user->getId());
        $from_name = "{$User->user_name} <{$User->user_email}>";
        $reply_to_email = $User->user_email;
        $reply_to_name  = $User->user_name;

        $subject = SupportForm::getSubjectLabel($this->subject) . " from {$from_name}";

        $ret = MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_standardMailTpl, $to, [
            'from_name'      => $from_name,
            //'from_email'     => 'support@pvtbox.net',
            'reply_to_email' => $reply_to_email,
            'reply_to_name'  => $reply_to_name,
            'subject'        => $subject,
            'body'           => $this->body,
            'to_name'        => 'Support',
            'UserObject'     => $User,
            'attachment'     => $this->log_file_name
                ? Yii::$app->params['logUploadsDir']. DIRECTORY_SEPARATOR . $this->log_file_name
                : null,
        ]);
        if ($ret) {
            return [
                'result' => "success",
                'info' => "Sent successfully.",
            ];
        } else {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FAILED_SEND_EMAIL,
            ];
        }

    }

    /**
     * Метод для получения инфы по лицензии ноды через джсон запрос
     * @param $User \common\models\Users
     * @return array
     */
    public function license($User)
    {
        return [
            'result' => "success",
            'info' => $User->license_type,
        ];
    }

    /**
     * Метод для синхронизации времени на ноде
     * @return array
     */
    public function gettime()
    {
        return [
            'result' => "ok",
            'info' => time(),
        ];
    }

    /**
     * Метод для получения количества оставшихся байт по беспл. лицензии
     * @param $User \common\models\Users
     * @return array
     */
    public function turn_get_bytes($User)
    {
        $License = Licenses::findByType($User->license_type);
        if (!$License) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_LICENSE_ACCESS,
                'info' => "License [{$User->license_type}] does not exist in system.",
            ];
        }

        if ($License->license_limit_bytes == 0) {
            return [
                'result' => "success",
                'info' => ['allowed' => $this->bytes],
            ];
        }

        if ($User->license_bytes_allowed < $this->bytes) {
            return [
                'result' => "error",
                'errcode' => ['allowed' => $User->license_bytes_allowed],
                'info' => ['allowed' => $User->license_bytes_allowed],
            ];
        }

        $User->license_bytes_allowed = $User->license_bytes_allowed - $this->bytes;
        $User->license_bytes_sent = $User->license_bytes_sent + $this->bytes;
        if (!$User->save()) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $User->getErrors(),
            ];
        }

        return [
            'result' => "success",
            'info' => ['allowed' => $User->license_bytes_allowed],
        ];
    }

    /**
     * Метод для возврата незадействованного количества байт в случае ошибки при скачивании
     * @param $User \common\models\Users
     * @return array
     */
    public function turn_set_bytes($User)
    {
        if ($User->license_bytes_sent < $this->bytes) {
            return [
                'result' => "error",
                'errcode' => "Reserved bytes ({$User->license_bytes_sent}) less than required ({$this->bytes}).",
                'info' => "Reserved bytes ({$User->license_bytes_sent}) less than required ({$this->bytes}).",
            ];
        }

        $User->license_bytes_allowed = $User->license_bytes_allowed + $this->bytes;
        $User->license_bytes_sent = $User->license_bytes_sent - $this->bytes;

        if (!$User->save()) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $User->getErrors(),
            ];
        }

        return [
            'result' => "success",
            'info' => ['allowed' => $User->license_bytes_allowed],
        ];
    }

    /**
     * Метод для выполнения logout || wipe
     * @param \common\models\UserNode $UserNode
     * @param $User \common\models\Users
     * @return array
     */
    public function execute_remote_action($UserNode, $User)
    {
        /* Проерим что таргет-нода принадлежить тому же пользователю которому принадлежит нода с которой делается вызов */
        $targetNode = UserNode::findOne([
            'user_id' => $UserNode->user_id,
            'node_id' => $this->target_node_id,
        ]);
        if (!$targetNode) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_NODE_NOT_FOUND,
                'info' => "Node with this target_node_id not found",
            ];
        }

        /* Проверка лицензии (на фри запрещено) */
        if ($User->license_type == Licenses::TYPE_FREE_DEFAULT) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_LICENSE_ACCESS,
                'info_fm' => Yii::t('app/node-api', "No_actions_possible_at_Free_license"),
                'info' => strip_tags(Yii::t('app/node-api', "No_actions_possible_at_Free_license")),
            ];
        }

        /* проверим что таргет-нода не была очищена ранее */
        $RemoteActionWipe = RemoteActions::findOne([
            //'user_id'        => $UserNode->user_id,
            'target_node_id' => $this->target_node_id,
            'action_type' => RemoteActions::TYPE_WIPE,
        ]);
        if ($RemoteActionWipe) {
            try {
                $this->redis->publish(
                    "node:{$this->target_node_id}:remote_action",
                    Json::encode([
                        'action_type' => $RemoteActionWipe->action_type,
                        'action_uuid' => $RemoteActionWipe->action_uuid,
                        'action_data' => unserialize($RemoteActionWipe->action_data),
                    ])
                );
                $this->redis->save();
            } catch (\Exception $e) {
                RedisSafe::createNewRecord(
                    RedisSafe::TYPE_REMOTE_ACTIONS,
                    $User->user_id,
                    $this->target_node_id,
                    Json::encode([
                        'action'           => 'remote_action',
                        'chanel'           => "node:{$this->target_node_id}:remote_action",
                        'user_id'          => $User->user_id,
                    ])
                );
            }

            return [
                'result' => "error",
                'errcode' => self::ERROR_NODE_WIPED,
                'info' => $RemoteActionWipe->action_end_time
                    ? "This node is already wiped. Can't create any action for it."
                    : "Wipe action for this node is already created and in progress. Cant create any action for it.",
                'data' => [
                    'target_node_id' => $RemoteActionWipe->target_node_id,
                    'action_type' => $RemoteActionWipe->action_type,
                    'action_uuid' => $RemoteActionWipe->action_uuid,
                    'action_data' => unserialize($RemoteActionWipe->action_data),
                    'node_logout_status' => $targetNode->node_logout_status,
                    'node_logout_status_text' => UserNode::logoutStatus($targetNode->node_logout_status),
                    'node_wipe_status' => $targetNode->node_wipe_status,
                    'node_wipe_status_text' => UserNode::wipeStatus($targetNode->node_wipe_status),
                ]
            ];
        }

        /* Если текущий акшен = logout или credentials, то проверим что акшен logout не был создан ранее и еще не выполнен. */
        if (in_array($this->action_type, [RemoteActions::TYPE_LOGOUT, RemoteActions::TYPE_CREDENTIALS])) {
            $RemoteActionLogout = RemoteActions::findOne([
                //'user_id'        => $UserNode->user_id,
                'target_node_id' => $this->target_node_id,
                'action_type' => RemoteActions::TYPE_LOGOUT,
                'action_end_time' => null,
            ]);
            if ($RemoteActionLogout) {
                try {
                    $this->redis->publish(
                        "node:{$this->target_node_id}:remote_action",
                        Json::encode([
                            'action_type' => $RemoteActionLogout->action_type,
                            'action_uuid' => $RemoteActionLogout->action_uuid,
                            'action_data' => unserialize($RemoteActionLogout->action_data),
                        ])
                    );
                    $this->redis->save();
                } catch (\Exception $e) {
                    RedisSafe::createNewRecord(
                        RedisSafe::TYPE_REMOTE_ACTIONS,
                        $User->user_id,
                        $this->target_node_id,
                        Json::encode([
                            'action'           => 'remote_action',
                            'chanel'           => "node:{$this->target_node_id}:remote_action",
                            'user_id'          => $User->user_id,
                        ])
                    );
                }

                return [
                    'result' => "error",
                    'errcode' => self::ERROR_NODE_LOGOUT_EXIST,
                    //'info' => "Logout action for node with this target_node_id is created and in progress. Can't execute new {$this->action_type} action for it before logout not finish.",
                    'info' => "Logout action for this node is already created and in progress. Can't create new {$this->action_type} action before previous executed.",
                    'data' => [
                        'target_node_id' => $RemoteActionLogout->target_node_id,
                        'action_type' => $RemoteActionLogout->action_type,
                        'action_uuid' => $RemoteActionLogout->action_uuid,
                        'action_data' => unserialize($RemoteActionLogout->action_data),
                        'node_logout_status' => $targetNode->node_logout_status,
                        'node_logout_status_text' => UserNode::logoutStatus($targetNode->node_logout_status),
                        'node_wipe_status' => $targetNode->node_wipe_status,
                        'node_wipe_status_text' => UserNode::wipeStatus($targetNode->node_wipe_status),
                    ]
                ];
            }
        }

        /* Начинаем транзакцию */
        $transaction = Yii::$app->db->beginTransaction();

        /* Удаляем все акшены = credentials для этой ноды которые были созданы ранее и еще не закончены */
        $query = "DELETE FROM {{%remote_actions}}
                  WHERE (target_node_id = :target_node_id)
                  AND (action_type = :TYPE_CREDENTIALS)
                  AND (action_end_time IS NULL)
                  RETURNING action_uuid";
        $res = Yii::$app->db->createCommand($query, [
            'target_node_id'   => $this->target_node_id,
            'TYPE_CREDENTIALS' => RemoteActions::TYPE_CREDENTIALS,
        ])->queryAll();
        //var_dump($res); exit;

        if (is_array($res) && sizeof($res)) {
            $action_uuid = [];
            foreach ($res as $v) {
                $action_uuid[] = $v['action_uuid'];
            }
            /* отправляем удаленные action_uuid в канал редис */
            try {
                $this->redis->publish(
                    "node:{$this->target_node_id}:remote_actions_cancel",
                    Json::encode($action_uuid)
                );
                $this->redis->save();
            } catch (\Exception $e) {
                RedisSafe::createNewRecord(
                    RedisSafe::TYPE_REMOTE_ACTIONS,
                    $User->user_id,
                    $this->target_node_id,
                    Json::encode([
                        'action'           => 'remote_actions_cancel',
                        'chanel'           => "node:{$this->target_node_id}:remote_actions_cancel",
                        'user_id'          => $User->user_id,
                    ])
                );
            }
        }

        /* создаем новый акшен */
        $RemoteAction = new RemoteActions();
        $RemoteAction->action_uuid = md5(time() . $this->action_type . $this->target_node_id . microtime());
        $RemoteAction->action_type = $this->action_type;
        $RemoteAction->action_data = ($RemoteAction->action_type == RemoteActions::TYPE_CREDENTIALS)
            ? serialize(['user_hash' => $User->user_remote_hash, 'user_email' => $User->user_email])
            : serialize(null);
        $RemoteAction->source_node_id = $UserNode->node_id;
        $RemoteAction->target_node_id = $this->target_node_id;
        $RemoteAction->user_id = $UserNode->user_id;
        $RemoteAction->action_end_time = null;
        if ($RemoteAction->save()) {

            if ($this->action_type == RemoteActions::TYPE_LOGOUT) {
                $targetNode->node_logout_status = UserNode::LOGOUT_STATUS_IN_PROGRESS;
            }
            if ($this->action_type == RemoteActions::TYPE_WIPE) {
                $targetNode->node_wipe_status = UserNode::WIPE_STATUS_IN_PROGRESS;
                $targetNode->node_logout_status = UserNode::LOGOUT_STATUS_IN_PROGRESS;
            }
            if ($targetNode->save()) {
                try {
                    $this->redis->publish(
                        "node:{$RemoteAction->target_node_id}:remote_action",
                        Json::encode([
                            'action_type' => $RemoteAction->action_type,
                            'action_uuid' => $RemoteAction->action_uuid,
                            'action_data' => unserialize($RemoteAction->action_data),
                        ])
                    );
                    $this->redis->save();
                } catch (\Exception $e) {
                    RedisSafe::createNewRecord(
                        RedisSafe::TYPE_REMOTE_ACTIONS,
                        $RemoteAction->user_id,
                        $RemoteAction->target_node_id,
                        Json::encode([
                            'action'           => 'remote_actions_cancel',
                            'chanel'           => "node:{$RemoteAction->target_node_id}:remote_action",
                            'user_id'          => $RemoteAction->user_id,
                        ])
                    );
                }

                /* успешное завершение транзакции */
                $transaction->commit();

                return [
                    'result' => "success",
                    'info' => "remote action has been initialized successfully",
                    'data' => [
                        'target_node_id' => $RemoteAction->target_node_id,
                        'action_type' => $RemoteAction->action_type,
                        'action_uuid' => $RemoteAction->action_uuid,
                        'action_data' => unserialize($RemoteAction->action_data),
                        'node_logout_status' => $targetNode->node_logout_status,
                        'node_logout_status_text' => UserNode::logoutStatus($targetNode->node_logout_status),
                        'node_wipe_status' => $targetNode->node_wipe_status,
                        'node_wipe_status_text' => UserNode::wipeStatus($targetNode->node_wipe_status),
                    ]
                ];
            } else {
                $transaction->rollBack();
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_DATABASE_FAILURE,
                    'info' => "An internal server error occurred.",
                    'debug' => $targetNode->getErrors(),
                ];
            }
        } else {
            $transaction->rollBack();
            return [
                'result' => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $RemoteAction->getErrors(),
            ];
        }
    }

    /**
     * Метод для фиксации успеха logout || wipe
     * @param \common\models\UserNode $UserNode
     * @return array
     */
    public function remote_action_done($UserNode)
    {
        /* Проверим что такой акшен был создан ранее */
        $RemoteAction = RemoteActions::findOne([
            'action_uuid' => $this->action_uuid,
        ]);
        if (!$RemoteAction) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_WRONG_DATA,
                'info' => "Remote action with action_uuid not found.",
            ];
        }

        /* Проерим что таргет-нода принадлежить тому же пользователю которому принадлежит нода с которой делается вызов */
        if ($RemoteAction->user_id != $UserNode->user_id) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_USER_NODE_MISMATCH,
                'info' => "user_node.user_id <> remote_actions.user_id",
            ];
        }

        /* Начинаем транзакцию */
        $transaction = Yii::$app->db->beginTransaction();

        $RemoteAction->action_end_time = date(SQL_DATE_FORMAT);
        if ($RemoteAction->save()) {

            if ($RemoteAction->action_type == RemoteActions::TYPE_LOGOUT) {
                $UserNode->node_logout_status = UserNode::LOGOUT_STATUS_SUCCESS;
                $UserNode->node_status = UserNode::STATUS_LOGGEDOUT;
            }
            if ($RemoteAction->action_type == RemoteActions::TYPE_WIPE) {
                $UserNode->node_wipe_status = UserNode::WIPE_STATUS_SUCCESS;
                $UserNode->node_logout_status = UserNode::LOGOUT_STATUS_SUCCESS;
                $UserNode->node_status = UserNode::STATUS_WIPED;
            }
            if ($UserNode->save()) {

                /* записываем в канал данные об успешном выполнении акшена */
                try {
                    $this->redis->publish(
                        "node:{$UserNode->node_id}:remote_action_done",
                        Json::encode([
                            'node_id'     => $UserNode->node_id,
                            'user_id'     => $UserNode->user_id,
                            'node_status' => $UserNode->node_status,
                            'action_type' => $RemoteAction->action_type,
                            'action_uuid' => $RemoteAction->action_uuid,
                        ])
                    );
                    $this->redis->save();
                } catch (\Exception $e) {
                    RedisSafe::createNewRecord(
                        RedisSafe::TYPE_REMOTE_ACTIONS,
                        $UserNode->user_id,
                        $UserNode->node_id,
                        Json::encode([
                            'action'           => 'remote_action_done',
                            'chanel'           => "node:{$UserNode->node_id}:remote_action_done",
                            'user_id'          => $UserNode->user_id,
                        ])
                    );
                }

                /* успешное завершение транзакции */
                $transaction->commit();
                return [
                    'result' => "success",
                ];
            } else {
                $transaction->rollBack();
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_DATABASE_FAILURE,
                    'info' => "An internal server error occurred.",
                    'debug' => $UserNode->getErrors(),
                ];
            }

        } else {
            $transaction->rollBack();
            return [
                'result' => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $RemoteAction->getErrors(),
            ];
        }
    }

    /**
     * Метод для отправки на ноды юзера новых данных по логину (credentials)
     * @param $User \common\models\Users
     * @return array
     */
    public static function sendCredentialsForUserNodes($User)
    {
        $NodeFM = self::registerNodeFM($User);

        $UserNodes = UserNode::find()
            ->where([
                'user_id'            => $User->user_id,
                'node_wipe_status'   => UserNode::WIPE_STATUS_READY_TO,
                'node_logout_status' => [UserNode::LOGOUT_STATUS_READY_TO, UserNode::LOGOUT_STATUS_SUCCESS],
            ])
            ->andWhere("node_ostype != :OSTYPE_WEBFM", [
                'OSTYPE_WEBFM' => UserNode::OSTYPE_WEBFM,
            ])
            ->all();

        /** @var \common\models\UserNode $UserNode */
        foreach ($UserNodes as $UserNode) {
            $model = new NodeApi(['target_node_id', 'action_type']);
            $model->target_node_id = $UserNode->node_id;
            $model->action_type    = RemoteActions::TYPE_CREDENTIALS;
            $model->execute_remote_action($NodeFM, $User);
        }
    }

    /**
     * Метод для фиксации успеха logout || wipe
     * @param \common\models\Users $User
     * @return array
     */
    public function get_token_login_link($User)
    {
        $User->generatePasswordResetToken();
        if ($User->save()) {
            return [
                'result' => "success",
                'info' => "login-link in data[login_link]",
                'data' => [
                    'login_link' => Yii::$app->urlManager->createAbsoluteUrl([
                        'user/login-by-token', 'token' => $User->password_reset_token
                    ]),
                ]
            ];
        } else {
            return [
                'result' => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $User->getErrors(),
            ];
        }
    }

    /**
     * Метод для возврата всех нотификаций
     * @param \common\models\Users $User
     * @return array
     */
    public function getNotifications($User)
    {
        $query = Notifications::find();
        $query->where([
            'user_id' => $User->user_id
        ]);
        if ($this->from) {
            $query->andWhere('notif_id < :notif_id', [
                'notif_id' => $this->from
            ]);
        }
        $query->orderBy(['notif_id' => SORT_DESC]);
        $query->limit($this->limit);
        $notifications = $query->all();
        $data = [];
        $ids = [];
        if ($notifications) {
            /** @var \common\models\Notifications $notification */
            foreach ($notifications as $notification) {
                $tmp = unserialize($notification->notif_data);
                $data[] = [
                    'notification_id' => $notification->notif_id,
                    'search'          => $tmp['search'],
                    'replace'         => $tmp['replace'],
                    'action'          => $notification->notif_type,
                    'text'            => strip_tags(Yii::t('mail/notifications', $notification->notif_type)),
                    'timestamp'       => strtotime($notification->notif_date),
                    'read'            => (boolean) !$notification->notif_isnew,
                ];
                $ids[] = $notification->notif_id;
            }
        }
        Notifications::seatAllAsRead($User->user_id, $ids);

        return [
            'result' => "success",
            'data'   => $data,
        ];
    }
    /************************** --- USER NODE --- **************************/


    /************************* +++ FILE EVENTS +++ *************************/
    /**
     * Метод для проверки доступа к синхронизации
     * @param \common\models\UserNode $UserNode
     * @param \common\models\UserFiles $UserFile
     * @return bool
     */
    protected static function checkLicenseAccess($UserNode, $UserFile = null)
    {
        $User = Users::findIdentity($UserNode->user_id);

        /* Если запрос на апи пришел от WebFM и лицензия фри то запрещаем действия */
        if ($UserNode->node_devicetype == UserNode::DEVICE_BROWSER && $User->license_type == Licenses::TYPE_FREE_DEFAULT) {
            return [
                'access' => false,
                'info' => Yii::t('app/node-api', "No_actions_possible_at_Free_license"),
            ];
        }

        /* Если пользователь не найден - то это вообще глобально ошибочно поэтому нет доступа
        (вероятность такой ошибки только в случае краха БД) */
        if (!$User) {
            return [
                'access' => false,
            ];
        }

        /* Если нет родителя (корень файловой системы) то разрешаем доступ */
        if (!$UserFile) {
            return [
                'access' => true,
            ];
        }

        /* Если лицензия платная тоже разрешаем полный доступ*/
        if ($User->license_type != Licenses::TYPE_FREE_DEFAULT) {
            return [
                'access' => true,
            ];
        }

        /* Если ид ноды создавшей объект или его родителя не равно ид ноды текущего события то запрет при фри-лицензии */
        if ($UserFile->node_id == $UserNode->node_id) {
            return [
                'access' => true,
            ];
        }

        return [
            'access' => false,
        ];
    }

    /**
     * @return string
     */
    public static function uniq_uuid()
    {
        return md5(uniqid(rand(), true));
    }

    /**
     * Check is event exist or not
     * @param integer $user_id
     * @return array|bool
     */
    private function check_is_event_exist($user_id)
    {
        /* если нода пришла на апи без event_uuid, то тут генерим его и выходим дальше в функцию выполнения евента */
        if (!$this->event_uuid) {
            $this->event_uuid = self::uniq_uuid();
            return false;
            /*
            return [
                'result'  => "error",
                'errcode' => self::ERROR_NULL_EVENT_UUID,
                'info'    => "event_uuid must be specified.",
            ];
            */
        }

        /* если нода пришла со своим event_uuid, то проверим существует ли он в базе или еще нет */
        $UserFileEvent = UserFileEvents::findOne(['event_uuid' => $this->event_uuid, 'user_id' => $user_id]);
        if (!$UserFileEvent) {
            /* если не существует то выходим в функцию выполенния евента по обычному сценарию */
            return false;
        }

        /* если же такой евент уже существует в базе то отвечаем ноде данными по этому евенту и файлу */
        $UserFile = UserFiles::findOne(['file_id' => $UserFileEvent->file_id]);
        if (!$UserFile) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info'    => "UserFileEvent exists but UserFile does not exist.",
            ];
        }

        if ($UserFile->is_folder) {
            return [
                'result' => "success",
                'info'   => UserFileEvents::getType($UserFileEvent->event_type) . "-event stored successfully",
                'data'   => [
                    'event_id'    => $UserFileEvent->event_id,
                    'event_uuid'  => $UserFileEvent->event_uuid,
                    'folder_uuid' => $UserFile->file_uuid,
                    'timestamp'   => $UserFileEvent->event_timestamp,
                    'event_uuid_already_exists' => true,
                ],
            ];
        } else {
            return [
                'result' => "success",
                'info'   => UserFileEvents::getType($UserFileEvent->event_type) . "-event stored successfully",
                'data'   => [
                    'event_id'               => $UserFileEvent->event_id,
                    'event_uuid'             => $UserFileEvent->event_uuid,
                    'timestamp'              => $UserFileEvent->event_timestamp,
                    'file_uuid'              => $UserFile->file_uuid,
                    'diff_file_uuid'         => $UserFileEvent->diff_file_uuid,
                    'rev_diff_file_uuid'     => $UserFileEvent->rev_diff_file_uuid,
                    'file_name_before_event' => $UserFileEvent->file_name_before_event,
                    'file_name_after_event'  => $UserFileEvent->file_name_after_event,
                    'file_size_before_event' => $UserFileEvent->file_size_before_event,
                    'file_size_after_event'  => $UserFileEvent->file_size_after_event,
                    'file_hash_before_event' => $UserFileEvent->file_hash_before_event,
                    'file_hash'              => $UserFileEvent->file_hash,
                    'event_uuid_already_exists' => true,
                ],
            ];
        }
    }

    /**
     * Check is event exist in queue or not
     * @param integer $user_id
     * @return array|bool
     */
    private function check_is_event_queued($user_id)
    {
        /* если нода пришла на апи без event_uuid, то тут генерим его и выходим дальше в функцию выполнения евента */
        if (!$this->event_uuid) {
            $this->event_uuid = self::uniq_uuid();
            return false;
        }

        $QueuedEvents = QueuedEvents::findOne(['event_uuid' => $this->event_uuid, 'user_id' => $user_id]);
        if (!$QueuedEvents) {
            /* если не существует то выходим в функцию выполенния евента по обычному сценарию */
            return false;
        }

        /* если же такой евент уже существует в очереди то отвечаем ноде данными по очереди */
        return [
            'result' => "queued",
            'info' => "folder-event-copy already in queue",
            'data' => [
                'job_id'     => $QueuedEvents->job_id,
                'event_uuid' => $QueuedEvents->event_uuid,
            ],
        ];

    }

    /**
     * Метод для регистрации события copy
     * @param \common\models\UserNode $UserNode
     * @param bool $internalNodeFM
     * @param bool $check_collaboration_access
     * @param bool $sendEventToSignal
     * @param bool $send_collaboration_event
     * @return array
     */
    public function file_event_copy($UserNode, $sendEventToSignal = false, $internalNodeFM = false, $check_collaboration_access = true, $send_collaboration_event = false)
    {
        /* Проверим нет ли лока на выполнение ФС действий вследствие друхих действий этого юзера */
        $mutex_name = 'user_id_' . $UserNode->user_id;
        if (!$this->mutex->acquire($mutex_name, MUTEX_WAIT_TIMEOUT)) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_TRY_LATER,
                'info' => "File or folder is locked now, try later please.",
            ];
        }

        /* Проверим, есть ли уже в бд евент с таким event_uuid если есть - это повтор и выполнять ничего не нужно. Просто отдать ответ */
        $check_event = $this->check_is_event_exist($UserNode->user_id);
        if ($check_event) {
            return $check_event;
        }

        /* подготовка параметра */
        if (!$this->folder_uuid) {
            $this->folder_uuid = "";
        }

        /* Поиск родительского элемента для файла */
        if (mb_strlen($this->folder_uuid)) {
            $parent = UserFiles::findOne([
                'file_uuid' => $this->folder_uuid,
                'is_folder' => UserFiles::TYPE_FOLDER,
                'user_id' => $UserNode->user_id,
                'is_deleted' => UserFiles::FILE_UNDELETED,
            ]);
            if (!$parent) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_FS_SYNC_PARENT_NOT_FOUND,
                    'info' => "Folder does not exist.",
                ];
            }
            $file_parent_id = $parent->file_id;
            $file_parent_uuid = $parent->file_uuid;
            if ($parent->collaboration_id) {
                $this->collaboration_id = $parent->collaboration_id;
            }
        } else {
            $parent = null;
            $file_parent_id = UserFiles::ROOT_PARENT_ID;
            $file_parent_uuid = null;
        }

        /* проверка прав коллаборации */
        if ($check_collaboration_access) {
            if ($this->collaboration_id) {
                $Colleague = UserColleagues::findOne([
                    'user_id' => $UserNode->user_id,
                    'collaboration_id' => $this->collaboration_id,
                ]);
                //if (!in_array($Colleague->colleague_permission, [UserColleagues::PERMISSION_EDIT, UserColleagues::PERMISSION_OWNER])) {
                if (!$Colleague ||
                    ($Colleague->colleague_status != UserColleagues::STATUS_JOINED) ||
                    !in_array($Colleague->colleague_permission, [UserColleagues::PERMISSION_EDIT, UserColleagues::PERMISSION_OWNER])) {
                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_COLLABORATION_ACCESS,
                        'info' => "Haven't access to change collaboration.",
                    ];
                }
            }
        }

        /* Проверка прав лицензии */
        if ($check_collaboration_access) {
            $ret_lic_acc = self::checkLicenseAccess($UserNode, $parent);
            if (!$ret_lic_acc['access']) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_LICENSE_ACCESS,
                    'info' => isset($ret_lic_acc['info'])
                        ? $ret_lic_acc['info']
                        : "Haven't access to create file in this folder. It has another owner NodeId.",
                ];
            }
        }

        /* Проверка что такой файл еще не существует */
        if (UserFiles::findOne([
            'file_name' => $this->file_name,
            'file_parent_id' => $file_parent_id,
            'user_id' => $UserNode->user_id
        ])
        ) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "Folder or file with this name already exist.",
            ];
        }

        $originFile = UserFiles::findOne([
            'file_uuid' => $this->file_uuid,
            //'user_id'    => $UserNode->user_id,
            'is_deleted' => UserFiles::FILE_UNDELETED,
        ]);
        if (!$originFile) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "Source file does not exist.",
            ];
        }

        /*
        $originFileEventCreate = UserFileEvents::findOne([
            'file_id' => $originFile->file_id,
            'event_type' => UserFileEvents::TYPE_CREATE,
        ]);
        */
        /** @var \common\models\UserFileEvents $originFileEventCreate */
        $originFileEventCreate = UserFileEvents::find()
            ->where([
                'file_id' => $originFile->file_id,
            ])
            /*
            ->andWhere('event_type IN (:update, :create)', [
                'update' => UserFileEvents::TYPE_UPDATE,
                'create' => UserFileEvents::TYPE_CREATE,
            ])
            */
            ->andWhere('event_type != :TYPE_DELETE', [
                'TYPE_DELETE' => UserFileEvents::TYPE_DELETE,
            ])
            ->orderBy(['event_id' => SORT_DESC])
            ->limit(1)
            ->one();
        if (!$originFileEventCreate) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "Source file does not exist.",
            ];
        }

        /* проверка что если это коллаба, то она не должна быть залочена */
        if ($check_collaboration_access && $this->collaboration_id) {
            $mutex_collaboration_name = 'collaboration_id_' . $this->collaboration_id;
            if (!$this->mutex->acquire($mutex_collaboration_name, MUTEX_WAIT_TIMEOUT)) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_FS_TRY_LATER,
                    'info' => "Collaboration is locked now, try later please..",
                ];
            }
        }

        try {
            /* Начинаем транзакцию */
            $transaction = Yii::$app->db->beginTransaction();
            $UserFile = New UserFiles();
            $UserFile->file_parent_id = $file_parent_id;
            $UserFile->file_uuid = ($this->copy_file_uuid) ? $this->copy_file_uuid : self::uniq_uuid();
            $UserFile->file_name = $this->file_name;
            $UserFile->is_deleted = UserFiles::FILE_UNDELETED;
            $UserFile->is_updated = UserFiles::FILE_UNUPDATED;
            $UserFile->is_outdated = UserFiles::FILE_UNOUTDATED;
            $UserFile->file_size = $originFile->file_size;
            $UserFile->file_md5 = $originFile->file_md5;
            $UserFile->file_lastatime = time();
            $UserFile->file_lastmtime = time();
            $UserFile->is_folder = UserFiles::TYPE_FILE;
            $UserFile->last_event_type = UserFileEvents::TYPE_CREATE;
            $UserFile->last_event_uuid = $this->event_uuid ? $this->event_uuid : self::uniq_uuid();
            $UserFile->diff_file_uuid = $originFile->diff_file_uuid;
            $UserFile->user_id = $UserNode->user_id;
            $UserFile->node_id = $UserNode->node_id;
            /** @var $parent \common\models\UserFiles */
            $UserFile->collaboration_id = $this->collaboration_id ? $this->collaboration_id : null;
            $UserFile->is_owner = isset($this->is_owner) ? $this->is_owner : UserFiles::IS_OWNER;
            $UserFile->is_collaborated = UserFiles::FILE_UNCOLLABORATED;
            $UserFile->is_shared = UserFiles::FILE_UNSHARED;
            $UserFile->share_group_hash = $parent ? $parent->share_group_hash : null;
            $UserFile->share_lifetime = $parent ? $parent->share_lifetime : null;
            $UserFile->share_ttl_info = $parent ? $parent->share_ttl_info : null;
            $UserFile->share_created = $parent ? $parent->share_created : null;
            $UserFile->share_password = $parent ? $parent->share_password : null;
            $UserFile->share_group_hash ? $UserFile->generate_share_hash() : $UserFile->share_hash = null;

            if ($UserFile->save()) {
                $UserFileEvent = new UserFileEvents();
                $UserFileEvent->event_uuid = $UserFile->last_event_uuid;
                $UserFileEvent->event_type = UserFileEvents::TYPE_CREATE;
                $UserFileEvent->event_timestamp = time();
                $UserFileEvent->last_event_id = 0;
                $UserFileEvent->file_id = $UserFile->file_id;
                $UserFileEvent->diff_file_uuid = $originFileEventCreate->diff_file_uuid;
                $UserFileEvent->diff_file_size = $originFileEventCreate->diff_file_size;
                $UserFileEvent->rev_diff_file_uuid = $originFileEventCreate->rev_diff_file_uuid;
                $UserFileEvent->rev_diff_file_size = $originFileEventCreate->rev_diff_file_size;
                $UserFileEvent->file_hash_before_event = "";
                $UserFileEvent->file_hash = $originFileEventCreate->file_hash;
                $UserFileEvent->node_id = $UserNode->node_id;
                $UserFileEvent->user_id = $UserNode->user_id;
                $UserFileEvent->file_name_before_event = "";
                $UserFileEvent->file_name_after_event = $this->file_name;
                $UserFileEvent->file_size_before_event = 0;
                $UserFileEvent->file_size_after_event = $originFileEventCreate->file_size_after_event;
                $UserFileEvent->parent_before_event = $UserFile->file_parent_id;
                $UserFileEvent->parent_after_event = $UserFile->file_parent_id;
                $UserFileEvent->event_creator_user_id = (empty($this->event_creator_user_id)) ? $UserNode->user_id : $this->event_creator_user_id;
                $UserFileEvent->event_creator_node_id = (empty($this->event_creator_node_id)) ? $UserNode->node_id : $this->event_creator_node_id;

                if ($UserFileEvent->save()) {

                    /* Подготовка инфы о юзере */
                    $User = Users::getPathNodeFS($UserFile->user_id);

                    /* Подготовка имени и полного пути файла а так же проверка на его максимальную длинну */
                    $relativePath = UserFiles::getFullPath($UserFile);
                    $file_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;
                    if (mb_strlen($file_name, '8bit') > UserFiles::FILE_PATH_MAX_LENGTH) {
                        $transaction->rollBack();
                        return [
                            'result' => "error",
                            'errcode' => self::ERROR_FILE_PATH_MAX_LENGTH,
                            'info' => "Path for folder or file is too long (more than " . UserFiles::FILE_PATH_MAX_LENGTH . " bytes).",
                        ];
                    }

                    /* обновление информации о размере родительской папки и количества файлов в ней */
                    UserFiles::update_parents_size_and_count(
                        $User,
                        $UserFile,
                        $UserFile->file_size,
                        1
                    );

                    /* евенты для отправки на сигнальный */
                    $event_data[] = [
                        'operation' => "file_event",
                        'data' => [
                            'event_id' => $UserFileEvent->event_id,
                            'event_uuid' => $UserFileEvent->event_uuid,
                            'last_event_id' => $UserFileEvent->last_event_id,
                            'event_type' => UserFileEvents::getType($UserFileEvent->event_type),
                            'event_type_int' => $UserFileEvent->event_type,
                            'timestamp' => $UserFileEvent->event_timestamp,
                            'hash' => $UserFileEvent->file_hash,
                            'file_hash_before_event' => $UserFileEvent->file_hash_before_event,
                            'file_hash_after_event' => $UserFileEvent->file_hash,
                            'file_hash' => $UserFileEvent->file_hash,
                            'diff_file_uuid' => $UserFileEvent->diff_file_uuid,
                            'diff_file_size' => $UserFileEvent->diff_file_size,
                            'rev_diff_file_uuid' => $UserFileEvent->rev_diff_file_uuid,
                            'rev_diff_file_size' => $UserFileEvent->rev_diff_file_size,
                            'is_folder' => false,
                            'uuid' => $UserFile->file_uuid,
                            'file_id' => $UserFile->file_id,
                            'file_parent_id' => $UserFile->file_parent_id,
                            'file_name' => $UserFile->file_name,
                            'file_size' => $UserFile->file_size,
                            'user_id' => $UserNode->user_id,
                            'node_id' => $UserNode->node_id,
                            'parent_folder_uuid' => $parent ? $parent->file_uuid : null,
                        ],
                    ];

                    /* Отключено создание копий евентов копируемого файла согласно тикета #181
                    //https://git.null.null/python-client/direct-link-python/issues/181

                    $events = UserFileEvents::find()
                        ->andWhere(['file_id' => $originFile->file_id, 'event_type' => UserFileEvents::TYPE_UPDATE])
                        ->orderBy(['event_id' => SORT_ASC])
                        ->all();
                    if ($events) {
                        /** @var UserFileEvents $event *-/
                        $this->file_uuid = $UserFile->file_uuid;
                        $this->file_size = $UserFile->file_size;
                        $this->last_event_id = $UserFileEvent->event_id;

                        foreach ($events as $event) {

                            $UserFileEventUp                         = new UserFileEvents();
                            $UserFileEventUp->event_uuid             = md5($event->event_id . time() . microtime());
                            $UserFileEventUp->event_type             = UserFileEvents::TYPE_UPDATE;
                            $UserFileEventUp->event_timestamp        = time();
                            $UserFileEventUp->last_event_id          = $this->last_event_id;
                            $UserFileEventUp->file_id                = $UserFile->file_id;
                            $UserFileEventUp->diff_file_uuid         = $event->diff_file_uuid;
                            $UserFileEventUp->diff_file_size         = $event->diff_file_size;
                            $UserFileEventUp->rev_diff_file_uuid     = $event->rev_diff_file_uuid;
                            $UserFileEventUp->rev_diff_file_size     = $event->rev_diff_file_size;
                            $UserFileEventUp->file_hash_before_event = $event->file_hash_before_event;
                            $UserFileEventUp->file_hash              = $event->file_hash;
                            $UserFileEventUp->node_id                = $UserNode->node_id;
                            $UserFileEventUp->user_id                = $UserNode->user_id;
                            $UserFileEventUp->file_name_before_event = $event->file_name_before_event;
                            $UserFileEventUp->file_name_after_event  = $event->file_name_after_event;
                            $UserFileEventUp->file_size_before_event = $event->file_size_before_event;
                            $UserFileEventUp->file_size_after_event  = $event->file_size_after_event;
                            if ($UserFileEventUp->save()) {
                                $this->last_event_id = $UserFileEventUp->event_id;
                                $event_data[] = [
                                    'operation' => "file_event",
                                    'data' => [
                                        'event_id'           => $UserFileEventUp->event_id,
                                        'event_uuid'         => $UserFileEventUp->event_uuid,
                                        'last_event_id'      => $UserFileEventUp->last_event_id,
                                        'event_type'         => UserFileEvents::getType($UserFileEventUp->event_type),
                                        'event_type_int'     => $UserFileEventUp->event_type,
                                        'timestamp'          => $UserFileEventUp->event_timestamp,
                                        'hash'               => $UserFileEventUp->file_hash,
                                        'diff_file_uuid'     => $UserFileEventUp->diff_file_uuid,
                                        'diff_file_size'     => $UserFileEventUp->diff_file_size,
                                        'rev_diff_file_uuid' => $UserFileEventUp->rev_diff_file_uuid,
                                        'rev_diff_file_size' => $UserFileEventUp->rev_diff_file_size,
                                        'is_folder'          => false,
                                        'uuid'               => $UserFile->file_uuid,
                                        'file_id'            => $UserFile->file_id,
                                        'file_parent_id'     => $UserFile->file_parent_id,
                                        'file_name'          => $UserFile->file_name,
                                        //'file_size'          => $UserFile->file_size,
                                        'user_id'            => $UserNode->user_id,
                                        //'parent_folder_uuid' => null,
                                    ],
                                ];
                            }
                        }
                    }
                    Отключено создание копий евентов копируемого файла согласно тикета #181 */

                    /* Вызов самое себя в случае если имеется коллаборация по родительской папке */
                    if ($check_collaboration_access) {
                        if ($this->collaboration_id && $file_parent_uuid) {
                            $UserColleagues = UserColleagues::find()
                                ->where([
                                    'collaboration_id' => $this->collaboration_id,
                                    'colleague_status' => UserColleagues::STATUS_JOINED,
                                ])
                                //->andWhere('(user_id!=:user_id) AND (user_id IS NOT NULL)', [':user_id' => $UserNode->user_id])
                                ->andWhere('(user_id IS NOT NULL)')
                                ->all();
                            /** @var \common\models\UserColleagues $colleague */
                            foreach ($UserColleagues as $colleague) {
                                if ($colleague->user_id == $UserNode->user_id) {

                                    /* создаем репорт для овнера о том что сделал сам овнер */
                                    if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                        $UserData = Users::findIdentity($colleague->user_id);
                                        if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                            $Report = ColleaguesReports::createNewReport(
                                                $event_data[0],
                                                $colleague,
                                                $UserNode
                                            );
                                        }
                                    }

                                } else {
                                    $data['copy_file_uuid'] = $UserFile->file_uuid;
                                    $data['file_uuid'] = $UserFile->file_uuid;
                                    $data['folder_uuid'] = $file_parent_uuid;
                                    $data['file_name'] = $UserFile->file_name;
                                    $data['file_size'] = $UserFile->file_size;
                                    $data['collaboration_id'] = $this->collaboration_id;
                                    $data['is_owner'] = UserFiles::IS_COLLEAGUE;
                                    $data['event_uuid'] = $UserFileEvent->event_uuid;
                                    $data['event_creator_user_id'] = $UserNode->user_id;
                                    $data['event_creator_node_id'] = $UserNode->node_id;


                                    $model = new NodeApi(['copy_file_uuid', 'file_uuid', 'file_name', 'file_size']);
                                    $model->load(['NodeApi' => $data]);
                                    $model->validate();

                                    $answer = $model->file_event_copy(
                                        self::registerNodeFM(Users::findIdentity($colleague->user_id)),
                                        true,
                                        false,
                                        false
                                    );

                                    /* собираем евенты */
                                    if (isset($answer['event_data'])) {
                                        foreach ($answer['event_data'] as $k => $v) {
                                            $event_data_redis[] = $v;

                                            /* создаем репорт для овнера */
                                            if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                                $UserData = Users::findIdentity($colleague->user_id);
                                                if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                                    $Report = ColleaguesReports::createNewReport(
                                                        $v,
                                                        $colleague,
                                                        $UserNode
                                                    );
                                                }
                                            }
                                        }
                                    }
                                }
                            }


                            /* Отправка данных на вебсокет */
                            /* Отправляем в редис */
                            if (isset($event_data_redis)) {
                                try {
                                    $this->redis->publish("collaboration:{$this->collaboration_id}:fsevent", Json::encode($event_data_redis));
                                    $this->redis->save();
                                } catch (\Exception $e) {
                                }
                            }

                        }
                    }

                    /* успешное завершение транзакции */
                    $transaction->commit();

                    /* Создание файла со служебной инфой для отображения в ФС вебФМ */
                    if (!$internalNodeFM) {
                        //$relativePath = UserFiles::getFullPath($UserFile);
                        $file_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;
                        if (file_exists($file_name)) {
                            @unlink($file_name);
                        }
                        if (!file_exists($file_name)) {
                            FileSys::mkdir(dirname($file_name), UserFiles::CHMOD_DIR, true);
                            FileSys::touch($file_name, UserFiles::CHMOD_DIR, UserFiles::CHMOD_FILE);
                            UserFiles::createFileInfo($file_name, $UserFile);
                        }
                    }

                    $ret = [
                        'result' => "success",
                        'info' => "create-event stored successfully",
                        'data' => [
                            'event_id' => $UserFileEvent->event_id,
                            'event_uuid' => $UserFileEvent->event_uuid,
                            'timestamp' => $UserFileEvent->event_timestamp,
                            'file_uuid' => $UserFile->file_uuid,
                            'diff_file_uuid' => $UserFileEvent->diff_file_uuid,
                            'rev_diff_file_uuid' => $UserFileEvent->rev_diff_file_uuid,
                            'file_name_before_event' => $UserFileEvent->file_name_before_event,
                            'file_name_after_event' => $UserFileEvent->file_name_after_event,
                            'file_size_before_event' => $UserFileEvent->file_size_before_event,
                            'file_size_after_event' => $UserFileEvent->file_size_after_event,
                            'file_hash_before_event' => $UserFileEvent->file_hash_before_event,
                            'file_hash' => $UserFileEvent->file_hash,
                        ],
                    ];

                    /* Отправка евента на редис если лицензия не ФРИИ */
                    if (!in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT])) {
                        try {
                            $this->redis->publish("user:{$UserNode->user_id}:fs_events", Json::encode($event_data));
                            $this->redis->save();
                        } catch (\Exception $e) {
                            RedisSafe::createNewRecord(
                                RedisSafe::TYPE_FS_EVENTS,
                                $UserNode->user_id,
                                null,
                                Json::encode([
                                    'action'           => 'fs_events',
                                    'chanel'           => "user:{$UserNode->user_id}:fs_events",
                                    'user_id'          => $UserNode->user_id,
                                ])
                            );
                        }
                    }

                    /* Подготовка данных об евенте в общий массив возврата, если нужно */
                    if ($sendEventToSignal) {
                        $ret['event_data'] = $event_data;
                    }

                    /* Освобождаем от блокировки по мутексу, нужно для ФМ,
                     * который за один проход скрипта может делать несколько евентов цикле,
                     * а иначе он не может например удалить сразу две папки*/
                    $this->mutex->release($mutex_name);
                    if (isset($mutex_collaboration_name)) { $this->mutex->release($mutex_collaboration_name); }

                    return $ret;
                } else {
                    $transaction->rollBack();

                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_DATABASE_FAILURE,
                        'info' => "An internal server error occurred.",
                        'debug' => $UserFileEvent->getErrors(),
                    ];
                }
            } else {
                $transaction->rollBack();

                return [
                    'result' => "error",
                    'errcode' => self::ERROR_DATABASE_FAILURE,
                    'info' => "An internal server error occurred.",
                    'debug' => $UserFile->getErrors(),
                ];
            }
        } catch (\Exception $e) {

            return [
                'result' => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $e,
            ];
        }
    }

    /**
     * Метод для регистрации события create
     * @param \common\models\UserNode $UserNode
     * @param bool $internalNodeFM
     * @param bool $check_collaboration_access
     * @param bool $sendEventToSignal
     * @param bool $send_collaboration_event
     * @return array
     */
    public function file_event_restore($UserNode, $sendEventToSignal = false, $internalNodeFM = false, $check_collaboration_access = true, $send_collaboration_event = false)
    {
        /* Проверим нет ли лока на выполнение ФС действий вследствие друхих действий этого юзера */
        $mutex_name = 'user_id_' . $UserNode->user_id;
        if (!$this->mutex->acquire($mutex_name, MUTEX_WAIT_TIMEOUT)) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_TRY_LATER,
                'info' => "File or folder is locked now, try later please.",
            ];
        }

        /* Проверим, есть ли уже в бд евент с таким event_uuid если есть - это повтор и выполнять ничего не нужно. Просто отдать ответ */
        $check_event = $this->check_is_event_exist($UserNode->user_id);
        if ($check_event) {
            return $check_event;
        }

        /* находим нужный файл по ууид */
        $UserFile = UserFiles::findOne([
            'file_uuid' => $this->file_uuid,
            'is_folder' => UserFiles::TYPE_FILE,
            'user_id' => $UserNode->user_id,
            'is_deleted' => UserFiles::FILE_DELETED,
        ]);

        /* проверка существования исходного файла */
        if (!$UserFile) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "File not found.",
            ];
        }

        /* проверка прав коллаборации */
        //var_dump($check_collaboration_access); exit;
        if ($check_collaboration_access) {
            if ($UserFile->collaboration_id) {
                $Colleague = UserColleagues::findOne([
                    'user_id' => $UserNode->user_id,
                    'collaboration_id' => $UserFile->collaboration_id,
                ]);
                //if (!in_array($Colleague->colleague_permission, [UserColleagues::PERMISSION_EDIT, UserColleagues::PERMISSION_OWNER])) {
                if (!$Colleague ||
                    ($Colleague->colleague_status != UserColleagues::STATUS_JOINED) ||
                    !in_array($Colleague->colleague_permission, [UserColleagues::PERMISSION_EDIT, UserColleagues::PERMISSION_OWNER])) {
                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_COLLABORATION_ACCESS,
                        'info' => "Haven't access to change collaboration.",
                    ];
                }
            }
        }

        /* Проверка прав лицензии */
        if ($check_collaboration_access) {
            $ret_lic_acc = self::checkLicenseAccess($UserNode, $UserFile);
            if (!$ret_lic_acc['access']) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_LICENSE_ACCESS,
                    'info' => isset($ret_lic_acc['info'])
                        ? $ret_lic_acc['info']
                        : "Haven't access to restore file. It has another owner NodeId.",
                ];
            }
        }

        /* проверка что файл действительно удаили до этого */
        if ($UserFile->last_event_type != UserFileEvents::TYPE_DELETE) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "File wasn't deleted. Can't do restore action with this file.",
            ];
        }

        /* проверка ошибки синхронизации по признаку last_event_id */
        /** @var \common\models\UserFileEvents $maxUserFileEvent */
        $maxUserFileEvent = UserFileEvents::find()
            ->where(['file_id' => $UserFile->file_id])
            ->orderBy(['event_id' => SORT_DESC])
            ->limit(1)
            ->one();
        //var_dump($maxUserFileEvent->event_id); exit;
        if (!$maxUserFileEvent) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "Synchronization conflict. last_event_id for file not found.",
                'debug' => "Synchronization conflict. last_event_id for file {$UserFile->file_uuid} not found.",
            ];
        }
        if (intval($maxUserFileEvent->event_id) != intval($this->last_event_id)) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "Synchronization conflict.",
                'debug' => "Synchronization conflict. (max_event_id={$maxUserFileEvent->event_id}; last_event_id={$this->last_event_id})",
            ];
        }

        /* Поиск родителя */
        /** @var \common\models\UserFiles $Parent */
        if ($UserFile->file_parent_id) {
            $Parent = UserFiles::findOne(['file_id' => $UserFile->file_parent_id]);
        } else {
            $Parent = null;
        }

        /* проверка что если это коллаба, то она не должна быть залочена */
        if ($check_collaboration_access && $UserFile->collaboration_id) {
            $mutex_collaboration_name = 'collaboration_id_' . $UserFile->collaboration_id;
            if (!$this->mutex->acquire($mutex_collaboration_name, MUTEX_WAIT_TIMEOUT)) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_FS_TRY_LATER,
                    'info' => "Collaboration is locked now, try later please..",
                ];
            }
        }

        try {
            /* Начинаем транзакцию */
            $transaction = Yii::$app->db->beginTransaction();

            /* Восстановление удаленных каталогов при восстановлении файла */
            $deleted_parents = [];
            UserFiles::findDeletedParents($UserFile, $deleted_parents);
            if (sizeof($deleted_parents) > 0) {
                $res_restore_parents = UserFiles::unMarkParentsAsDeleted($deleted_parents, $UserNode);
                if (!$res_restore_parents['status']) {
                    $transaction->rollBack();

                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_FS_SYNC,
                        'info' => "An internal server error occurred.",
                        'debug' => $res_restore_parents['info'],
                    ];
                }
                $event_data = $res_restore_parents['event_data'];
            }

            $UserFileEvent = new UserFileEvents();
            $UserFileEvent->event_uuid = $this->event_uuid ? $this->event_uuid : self::uniq_uuid();
            $UserFileEvent->event_type = UserFileEvents::TYPE_RESTORE;
            $UserFileEvent->event_timestamp = time();
            $UserFileEvent->event_invisible = UserFileEvents::EVENT_INVISIBLE;
            $UserFileEvent->last_event_id = $this->last_event_id;
            $UserFileEvent->file_id = $UserFile->file_id;
            $UserFileEvent->diff_file_uuid = $maxUserFileEvent->diff_file_uuid;
            $UserFileEvent->diff_file_size = $maxUserFileEvent->diff_file_size;
            $UserFileEvent->rev_diff_file_uuid = $maxUserFileEvent->rev_diff_file_uuid;
            $UserFileEvent->rev_diff_file_size = $maxUserFileEvent->rev_diff_file_size;
            $UserFileEvent->file_hash_before_event = null;
            $UserFileEvent->file_hash = $UserFile->file_md5;
            $UserFileEvent->node_id = $UserNode->node_id;
            $UserFileEvent->user_id = $UserNode->user_id;
            $UserFileEvent->file_name_before_event = $UserFile->file_name;
            $UserFileEvent->file_name_after_event = $UserFile->file_name;
            $UserFileEvent->file_size_before_event = $UserFile->file_size;
            $UserFileEvent->file_size_after_event = $UserFile->file_size;
            $UserFileEvent->parent_before_event = $UserFile->file_parent_id;
            $UserFileEvent->parent_after_event = $UserFile->file_parent_id;
            $UserFileEvent->event_creator_user_id = (empty($this->event_creator_user_id)) ? $UserNode->user_id : $this->event_creator_user_id;
            $UserFileEvent->event_creator_node_id = (empty($this->event_creator_node_id)) ? $UserNode->node_id : $this->event_creator_node_id;

            if ($UserFileEvent->save()) {
                //$UserFile->file_size        = $this->file_size;
                //$UserFile->file_md5         = $this->hash;
                //$UserFile->is_updated       = UserFiles::FILE_UPDATED;
                $UserFile->is_deleted = UserFiles::FILE_UNDELETED;
                $UserFile->is_outdated = UserFiles::FILE_UNOUTDATED;
                $UserFile->file_lastatime = time();
                $UserFile->file_lastmtime = time();
                $UserFile->last_event_type = UserFileEvents::TYPE_RESTORE;
                $UserFile->last_event_uuid = $UserFileEvent->event_uuid;
                //$UserFile->last_event_id = $UserFileEvent->event_id;
                $UserFile->diff_file_uuid = $UserFileEvent->diff_file_uuid;

                //++ Max 21.09.2018 ** Замена префикса Deleted DATE на префикс Restored DATE
                $old_relativePath = UserFiles::getFullPath($UserFile);
                $UserFile->file_name = preg_replace("/\s\(Deleted [\d]{2}\-[\d]{2}\-[\d]{4} [\d]{2}\.[\d]{2}\.[\d]{2}\)/", "", $UserFile->file_name);
                $append_file_name = " (Restored " . date('d-m-Y H.i.s') .")";
                $tmp = FileSys::pathinfo($UserFile->file_name);
                //var_dump($tmp); exit;
                if (isset($tmp['extension'], $tmp['filename'])) {
                    $new_UserFile_file_name = $tmp['filename'] . $append_file_name . "." . $tmp['extension'];
                } else {
                    $new_UserFile_file_name = $UserFile->file_name . $append_file_name;
                }

                /* Проверка длинны имени файла после восстановления, если больше 255 то нужно перед удалением укоротить */
                $test_length = mb_strlen($new_UserFile_file_name, '8bit');
                if ($test_length > UserFiles::FILE_NAME_MAX_LENGTH) {
                    $new_UserFile_file_name = Functions::cutUtf8StrToLengthBites($new_UserFile_file_name, UserFiles::FILE_NAME_MAX_LENGTH);
                }

                $UserFile->file_name = $new_UserFile_file_name;
                $UserFileEvent->file_name_after_event = $new_UserFile_file_name;
                //--

                if ($UserFile->save() && $UserFileEvent->save()) {

                    $User = Users::getPathNodeFS($UserFile->user_id);

                    /* обновление информации о размере родительской папки и количества файлов в ней */
                    UserFiles::update_parents_size_and_count(
                        $User,
                        $UserFile,
                        $UserFile->file_size,
                        1
                    );

                    /* евенты для отправки на сигнальный */
                    $event_data[] = [
                        'operation' => "file_event",
                        'data' => [
                            'event_id' => $UserFileEvent->event_id,
                            'event_uuid' => $UserFileEvent->event_uuid,
                            'last_event_id' => $UserFileEvent->last_event_id,
                            'event_type' => UserFileEvents::getType($UserFileEvent->event_type),
                            'event_type_int' => $UserFileEvent->event_type,
                            'timestamp' => $UserFileEvent->event_timestamp,
                            'hash' => $UserFileEvent->file_hash,
                            'file_hash_before_event' => $UserFileEvent->file_hash_before_event,
                            'file_hash_after_event' => $UserFileEvent->file_hash,
                            'file_hash' => $UserFileEvent->file_hash,
                            'diff_file_uuid' => $UserFileEvent->diff_file_uuid,
                            'diff_file_size' => $UserFileEvent->diff_file_size,
                            'rev_diff_file_uuid' => $UserFileEvent->rev_diff_file_uuid,
                            'rev_diff_file_size' => $UserFileEvent->rev_diff_file_size,
                            'file_size_after_event' => $UserFileEvent->file_size_after_event,
                            'file_size_before_event' => $UserFileEvent->file_size_before_event,
                            'is_folder' => false,
                            'uuid' => $UserFile->file_uuid,
                            'file_id' => $UserFile->file_id,
                            'file_name' => $UserFile->file_name,
                            'file_parent_id' => $UserFile->file_parent_id,
                            'file_size' => $UserFile->file_size,
                            'user_id' => $UserNode->user_id,
                            'node_id' => $UserNode->node_id,
                            'parent_folder_uuid' => $Parent ? $Parent->file_uuid : null,
                        ],
                    ];

                    /* Вызов самое себя в случае если имеется коллаборация по родительской папке */
                    if ($check_collaboration_access) {
                        if ($UserFile->collaboration_id) {
                            $UserColleagues = UserColleagues::find()
                                ->where([
                                    'collaboration_id' => $UserFile->collaboration_id,
                                    'colleague_status' => UserColleagues::STATUS_JOINED,
                                ])
                                //->andWhere('(user_id!=:user_id) AND (user_id IS NOT NULL)', [':user_id' => $UserNode->user_id])
                                ->andWhere('(user_id IS NOT NULL)')
                                ->all();
                            /** @var \common\models\UserColleagues $colleague */
                            foreach ($UserColleagues as $colleague) {

                                if ($colleague->user_id == $UserNode->user_id) {

                                    /* создаем репорт для овнера о том что сделал сам овнер */
                                    if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                        $UserData = Users::findIdentity($colleague->user_id);
                                        if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                            $Report = ColleaguesReports::createNewReport(
                                                $event_data[0],
                                                $colleague,
                                                $UserNode
                                            );
                                        }
                                    }

                                } else {
                                    $UserFile_FindID = UserFiles::findOne([
                                        'file_uuid' => $this->file_uuid,
                                        'is_folder' => UserFiles::TYPE_FILE,
                                        'user_id' => $colleague->user_id,
                                        'is_deleted' => UserFiles::FILE_DELETED,
                                    ]);
                                    if ($UserFile_FindID) {
                                        $last_event_id = UserFileEvents::find()
                                            ->andWhere(['file_id' => $UserFile_FindID->file_id])
                                            ->max('event_id');

                                        $data['file_uuid'] = $this->file_uuid;
                                        $data['last_event_id'] = $last_event_id;
                                        $data['event_uuid'] = $UserFileEvent->event_uuid;
                                        $data['event_creator_user_id'] = $UserNode->user_id;
                                        $data['event_creator_node_id'] = $UserNode->node_id;

                                        $model = new NodeApi(['file_uuid', 'last_event_id']);
                                        $model->load(['NodeApi' => $data]);
                                        $model->validate();
                                        $answer = $model->file_event_restore(
                                            self::registerNodeFM(Users::findIdentity($colleague->user_id)),
                                            true,
                                            false,
                                            false
                                        );

                                        /* собираем евенты */
                                        if (isset($answer['event_data'])) {
                                            foreach ($answer['event_data'] as $k => $v) {
                                                $event_data_redis[] = $v;

                                                /* создаем репорт для овнера */
                                                if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                                    $UserData = Users::findIdentity($colleague->user_id);
                                                    if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                                        $Report = ColleaguesReports::createNewReport(
                                                            $v,
                                                            $colleague,
                                                            $UserNode
                                                        );
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            /* Отправка данных на вебсокет */
                            /* Отправляем в редис */
                            if (isset($event_data_redis)) {
                                try {
                                    $this->redis->publish("collaboration:{$UserFile->collaboration_id}:fsevent", Json::encode($event_data_redis));
                                    $this->redis->save();
                                } catch (\Exception $e) {
                                }
                            }

                        }
                    }

                    /* успешное завершение транзакции */
                    $transaction->commit();

                    /* Создание файла со служебной инфой для отображения в ФС вебФМ */
                    if (!$internalNodeFM) {

                        //++ Max 21.09.2018 ** удаление реального файла из фс со старым именем и префииксом Deleted DATE
                        $_old_file_name = $User->_full_path . DIRECTORY_SEPARATOR . $old_relativePath;
                        @unlink($_old_file_name);
                        //--

                        $relativePath = UserFiles::getFullPath($UserFile);
                        $file_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;

                        if (!file_exists($file_name)) {
                            FileSys::touch($file_name, UserFiles::CHMOD_DIR, UserFiles::CHMOD_FILE);
                        }
                        UserFiles::createFileInfo($file_name, $UserFile);
                    }

                    $ret = [
                        'result' => "success",
                        'info' => "restore-event stored successfully",
                        'data' => [
                            'event_id' => $UserFileEvent->event_id,
                            'event_uuid' => $UserFileEvent->event_uuid,
                            'timestamp' => $UserFileEvent->event_timestamp,
                            'diff_file_uuid' => $UserFileEvent->diff_file_uuid,
                            'rev_diff_file_uuid' => $UserFileEvent->rev_diff_file_uuid,
                            'file_name_before_event' => $UserFileEvent->file_name_before_event,
                            'file_name_after_event' => $UserFileEvent->file_name_after_event,
                            'file_size_before_event' => $UserFileEvent->file_size_before_event,
                            'file_size_after_event' => $UserFileEvent->file_size_after_event,
                            'file_hash_before_event' => $UserFileEvent->file_hash_before_event,
                            'file_hash' => $UserFileEvent->file_hash,
                        ],
                    ];

                    /* Отправка евента на редис */
                    if (!in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT])) {
                        try {
                            $this->redis->publish("user:{$UserNode->user_id}:fs_events", Json::encode($event_data));
                            $this->redis->save();
                        } catch (\Exception $e) {
                            RedisSafe::createNewRecord(
                                RedisSafe::TYPE_FS_EVENTS,
                                $UserNode->user_id,
                                null,
                                Json::encode([
                                    'action'           => 'fs_events',
                                    'chanel'           => "user:{$UserNode->user_id}:fs_events",
                                    'user_id'          => $UserNode->user_id,
                                ])
                            );
                        }
                    }

                    /* Подготовка данных об евенте в общий массив возврата, если нужно */
                    if ($sendEventToSignal) {
                        $ret['event_data'] = $event_data;
                    }

                    //++ Max 21.09.2018 ** Переименовывание папок с префиксом Deleted DATE на Restored DATE при восстановлении файла
                    //var_dump($res_restore_parents); exit;
                    if (isset($res_restore_parents['folder_for_rename_after_restore']) &&
                        is_array($res_restore_parents['folder_for_rename_after_restore']) &&
                        sizeof($res_restore_parents['folder_for_rename_after_restore'])) {

                        foreach ($res_restore_parents['folder_for_rename_after_restore'] as $FoldersForRename) {
                            unset($data_rename);
                            //var_dump($FoldersForRename);
                            $data_rename['last_event_id'] = UserFiles::last_event_id($FoldersForRename['file_id']);
                            //var_dump($data_rename);
                            if ($data_rename['last_event_id']) {
                                $data_rename['folder_uuid']            = $FoldersForRename['folder_uuid'];
                                $data_rename['new_folder_name']        = FileSys::basename($FoldersForRename['new_folder_name']);
                                $data_rename['new_parent_folder_uuid'] = $FoldersForRename['new_parent_folder_uuid'];
                                $data_rename['is_rename']              = true;
                                $model_rename = new NodeApi(['folder_uuid', 'new_folder_name', 'last_event_id']);
                                //var_dump($data_rename);
                                if ($model_rename->load(['NodeApi' => $data_rename]) && $model_rename->validate()) {
                                    //var_dump($res_restore_parents['folder_for_rename_after_restore']);
                                    $rename[] = $model_rename->folder_event_move($UserNode, false, false);
                                    //var_dump($rename);
                                } else {
                                    //var_dump($model_rename->getErrors());
                                }
                            }
                        }
                    }
                    //exit;
                    //--

                    /* Освобождаем от блокировки по мутексу, нужно для ФМ,
                     * который за один проход скрипта может делать несколько евентов цикле,
                     * а иначе он не может например удалить сразу две папки*/
                    $this->mutex->release($mutex_name);
                    if (isset($mutex_collaboration_name)) { $this->mutex->release($mutex_collaboration_name); }

                    return $ret;
                } else {
                    $transaction->rollBack();

                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_DATABASE_FAILURE,
                        'info' => "An internal server error occurred.",
                        'debug' => $UserFile->getErrors(),
                    ];
                }
            } else {
                $transaction->rollBack();

                return [
                    'result' => "error",
                    'errcode' => self::ERROR_DATABASE_FAILURE,
                    'info' => "An internal server error occurred.",
                    'debug' => $UserFileEvent->getErrors(),
                ];
            }
        } catch (\Exception $e) {

            return [
                'result' => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $e,
            ];
        }
    }

    /**
     * Метод для регистрации события create
     * @param \common\models\UserNode $UserNode
     * @param bool $internalNodeFM
     * @param bool $check_collaboration_access
     * @param bool $sendEventToSignal
     * @param bool $send_collaboration_event
     * @return array
     */
    public function file_event_create($UserNode, $sendEventToSignal = false, $internalNodeFM = false, $check_collaboration_access = true, $send_collaboration_event = false)
    {
        /* Проверим нет ли лока на выполнение ФС действий вследствие друхих действий этого юзера */
        $mutex_name = 'user_id_' . $UserNode->user_id;
        if (!$this->mutex->acquire($mutex_name, MUTEX_WAIT_TIMEOUT)) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_TRY_LATER,
                'info' => "File or folder is locked now, try later please.",
            ];
        }

        /* Проверим, есть ли уже в бд евент с таким event_uuid если есть - это повтор и выполнять ничего не нужно. Просто отдать ответ */
        $check_event = $this->check_is_event_exist($UserNode->user_id);
        if ($check_event) {
            return $check_event;
        }

        /* подготовка параметра */
        if (!$this->folder_uuid) {
            $this->folder_uuid = "";
        }

        /* Поиск родительского элемента для файла */
        if (mb_strlen($this->folder_uuid)) {
            $parent = UserFiles::findOne([
                'file_uuid' => $this->folder_uuid,
                'is_folder' => UserFiles::TYPE_FOLDER,
                'user_id' => $UserNode->user_id,
                'is_deleted' => UserFiles::FILE_UNDELETED,
            ]);
            if (!$parent) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_FS_SYNC_PARENT_NOT_FOUND,
                    'info' => "Folder does not exist.",
                    'debug' => "Folder with folder_uuid='{$this->folder_uuid}' does not exist.",
                ];
            }
            $file_parent_id = $parent->file_id;
            $file_parent_uuid = $parent->file_uuid;
            if ($parent->collaboration_id) {
                $this->collaboration_id = $parent->collaboration_id;
            }
        } else {
            $parent = null;
            $file_parent_id = UserFiles::ROOT_PARENT_ID;
            $file_parent_uuid = null;
        }

        /* проверка прав коллаборации */
        if ($check_collaboration_access) {
            if ($this->collaboration_id) {
                $Colleague = UserColleagues::findOne([
                    'user_id' => $UserNode->user_id,
                    'collaboration_id' => $this->collaboration_id,
                ]);
                //if (!in_array($Colleague->colleague_permission, [UserColleagues::PERMISSION_EDIT, UserColleagues::PERMISSION_OWNER])) {
                if (!$Colleague ||
                    ($Colleague->colleague_status != UserColleagues::STATUS_JOINED) ||
                    !in_array($Colleague->colleague_permission, [UserColleagues::PERMISSION_EDIT, UserColleagues::PERMISSION_OWNER])) {
                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_COLLABORATION_ACCESS,
                        'info' => "Haven't access to change collaboration.",
                    ];
                }
            }
        }

        /* Проверка прав лицензии */
        /*
        if ($check_collaboration_access) {
            $ret_lic_acc = self::checkLicenseAccess($UserNode, $parent);
            if (!$ret_lic_acc['access']) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_LICENSE_ACCESS,
                    'info' => isset($ret_lic_acc['info'])
                        ? $ret_lic_acc['info']
                        : "Haven't access to create file in this folder. It has another owner NodeId.",
                ];
            }
        }
        */

        /* Проверка что такой файл еще не существует */
        $checkExistsUserFile = UserFiles::findOne([
            'file_name' => $this->file_name,
            'file_parent_id' => $file_parent_id,
            'user_id' => $UserNode->user_id
        ]);
        if ($checkExistsUserFile) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => 'Folder or file with this name already exist.',
                'error_data' => [
                    'file_hash' => $checkExistsUserFile->file_md5,
                    'file_md5'  => $checkExistsUserFile->file_md5,
                    'file_size' => $checkExistsUserFile->file_size,
                    'file_name' => $checkExistsUserFile->file_name,
                ],
            ];
        }

        /* Проверка что такой файл еще не существует */
//        $checkExistsUserFile = UserFiles::findOne([
//            'file_name' => $this->file_name,
//            'file_parent_id' => $file_parent_id,
//            'user_id' => $UserNode->user_id
//        ]);
//        if ($checkExistsUserFile) {
//            /* если совпадает хеш и размер создаваемого файла с существующим в базе - значит нода пытается создать такой же - пусть создает (не смогла скачать с других нод например) */
//            if ($checkExistsUserFile->file_md5 === $this->hash && $checkExistsUserFile->file_size === $this->file_size) {
//                /** @var \common\models\UserFileEvents $checkExistsUserFileEvent */
//                $checkExistsUserFileEvent = UserFileEvents::find()
//                    ->where([
//                        'file_id' => $checkExistsUserFile->file_id,
//                        'event_type' => UserFileEvents::TYPE_CREATE,
//                    ])
//                    ->orderBy(['event_id' => SORT_DESC])
//                    ->limit(1)
//                    ->one();
//                if ($checkExistsUserFileEvent) {
//                    return [
//                        'result' => "success",
//                        'info' => UserFileEvents::getType($checkExistsUserFileEvent->event_type) . "-event stored successfully",
//                        'data' => [
//                            'event_id' => $checkExistsUserFileEvent->event_id,
//                            'event_uuid' => $checkExistsUserFileEvent->event_uuid,
//                            'timestamp' => $checkExistsUserFileEvent->event_timestamp,
//                            'file_uuid' => $checkExistsUserFile->file_uuid,
//                            'diff_file_uuid' => $checkExistsUserFileEvent->diff_file_uuid,
//                            'rev_diff_file_uuid' => $checkExistsUserFileEvent->rev_diff_file_uuid,
//                            'file_name_before_event' => $checkExistsUserFileEvent->file_name_before_event,
//                            'file_name_after_event' => $checkExistsUserFileEvent->file_name_after_event,
//                            'file_size_before_event' => $checkExistsUserFileEvent->file_size_before_event,
//                            'file_size_after_event' => $checkExistsUserFileEvent->file_size_after_event,
//                            'file_hash_before_event' => $checkExistsUserFileEvent->file_hash_before_event,
//                            'file_hash' => $checkExistsUserFileEvent->file_hash,
//                        ],
//                    ];
//                } else {
//                    return [
//                        'result' => "error",
//                        'errcode' => self::ERROR_FS_SYNC,
//                        'info' => 'Folder or file with this name already exist, but no events exist for file'
//                    ];
//                }
//            } else {
//                return [
//                    'result' => "error",
//                    'errcode' => self::ERROR_FS_SYNC,
//                    'info' => 'Folder or file with this name already exist.'
//                ];
//            }
//        }

        /* проверка что если это коллаба, то она не должна быть залочена */
        if ($check_collaboration_access && $this->collaboration_id) {
            $mutex_collaboration_name = 'collaboration_id_' . $this->collaboration_id;
            if (!$this->mutex->acquire($mutex_collaboration_name, MUTEX_WAIT_TIMEOUT)) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_FS_TRY_LATER,
                    'info' => "Collaboration is locked now, try later please..",
                ];
            }
        }

        try {
            /* Начинаем транзакцию */
            $transaction = Yii::$app->db->beginTransaction();

            /* Подготовка инфы о юзере */
            $User = Users::getPathNodeFS($UserNode->user_id);

            /* Создание новой записи о файле */
            $UserFile = New UserFiles();
            $UserFile->file_parent_id = $file_parent_id;
            $UserFile->file_uuid = self::uniq_uuid();;
            $UserFile->file_name = $this->file_name;
            $UserFile->is_deleted = UserFiles::FILE_UNDELETED;
            $UserFile->is_updated = UserFiles::FILE_UNUPDATED;
            $UserFile->is_outdated = UserFiles::FILE_UNOUTDATED;
            $UserFile->file_size = $this->file_size;
            $UserFile->file_md5 = $this->hash;
            $UserFile->file_lastatime = time();
            $UserFile->file_lastmtime = time();
            $UserFile->is_folder = UserFiles::TYPE_FILE;
            $UserFile->last_event_type = UserFileEvents::TYPE_CREATE;
            $UserFile->last_event_uuid = $this->event_uuid ? $this->event_uuid : self::uniq_uuid();
            $UserFile->diff_file_uuid = md5('diff_file_uuid' . $this->hash);
            $UserFile->user_id = $UserNode->user_id;
            $UserFile->node_id = $UserNode->node_id;
            /** @var $parent \common\models\UserFiles */
            $UserFile->collaboration_id = $this->collaboration_id ? $this->collaboration_id : null;
            $UserFile->is_owner = isset($this->is_owner) ? $this->is_owner : UserFiles::IS_OWNER;
            $UserFile->is_collaborated = UserFiles::FILE_UNCOLLABORATED;
            $UserFile->is_shared = UserFiles::FILE_UNSHARED;
            $UserFile->share_group_hash = $parent ? $parent->share_group_hash : null;
            $UserFile->share_lifetime = $parent ? $parent->share_lifetime : null;
            $UserFile->share_ttl_info = $parent ? $parent->share_ttl_info : null;
            $UserFile->share_created = $parent ? $parent->share_created : null;
            $UserFile->share_password = $parent ? $parent->share_password : null;
            $UserFile->share_group_hash ? $UserFile->generate_share_hash() : $UserFile->share_hash = null;
            if ($UserFile->save()) {
                /* создание новой записи о евенте */
                $UserFileEvent = new UserFileEvents();
                $UserFileEvent->event_uuid = $UserFile->last_event_uuid;
                $UserFileEvent->event_type = UserFileEvents::TYPE_CREATE;
                $UserFileEvent->event_timestamp = time();
                $UserFileEvent->last_event_id = 0;
                $UserFileEvent->file_id = $UserFile->file_id;
                $UserFileEvent->diff_file_uuid = $UserFile->diff_file_uuid;
                $UserFileEvent->diff_file_size = $this->diff_file_size;
                $UserFileEvent->rev_diff_file_uuid = null;
                $UserFileEvent->rev_diff_file_size = 0;
                $UserFileEvent->file_hash_before_event = null;
                $UserFileEvent->file_hash = $this->hash;
                $UserFileEvent->node_id = $UserNode->node_id;
                $UserFileEvent->user_id = $UserNode->user_id;
                $UserFileEvent->file_name_before_event = '';
                $UserFileEvent->file_name_after_event = $UserFile->file_name;
                $UserFileEvent->file_size_before_event = 0;
                $UserFileEvent->file_size_after_event = $this->file_size;
                $UserFileEvent->parent_before_event = $UserFile->file_parent_id;
                $UserFileEvent->parent_after_event = $UserFile->file_parent_id;
                $UserFileEvent->event_creator_user_id = (empty($this->event_creator_user_id)) ? $UserNode->user_id : $this->event_creator_user_id;
                $UserFileEvent->event_creator_node_id = (empty($this->event_creator_node_id)) ? $UserNode->node_id : $this->event_creator_node_id;

                if ($UserFileEvent->save()) {

                    /* Подготовка имени и полного пути файла а так же проверка на его максимальную длинну */
                    $relativePath = UserFiles::getFullPath($UserFile);
                    $file_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;
                    if (mb_strlen($file_name, '8bit') > UserFiles::FILE_PATH_MAX_LENGTH) {
                        $transaction->rollBack();

                        return [
                            'result'  => "error",
                            'errcode' => self::ERROR_FILE_PATH_MAX_LENGTH,
                            'info'    => "Path for folder or file is too long (more than " . UserFiles::FILE_PATH_MAX_LENGTH . " bytes).",
                        ];
                    }

                    /* обновление информации о размере родительской папки и количества файлов в ней */
                    UserFiles::update_parents_size_and_count(
                        $User,
                        $UserFile,
                        $UserFile->file_size,
                        1
                    );

                    /* евенты для отправки на сигнальный */
                    $event_data[] = [
                        'operation' => "file_event",
                        'data' => [
                            'event_id' => $UserFileEvent->event_id,
                            'event_uuid' => $UserFileEvent->event_uuid,
                            'last_event_id' => $UserFileEvent->last_event_id,
                            'event_type' => UserFileEvents::getType($UserFileEvent->event_type),
                            'event_type_int' => $UserFileEvent->event_type,
                            'timestamp' => $UserFileEvent->event_timestamp,
                            'hash' => $UserFileEvent->file_hash,
                            'file_hash_before_event' => $UserFileEvent->file_hash_before_event,
                            'file_hash_after_event' => $UserFileEvent->file_hash,
                            'file_hash' => $UserFileEvent->file_hash,
                            'diff_file_uuid' => $UserFileEvent->diff_file_uuid,
                            'diff_file_size' => $UserFileEvent->diff_file_size,
                            'rev_diff_file_uuid' => $UserFileEvent->rev_diff_file_uuid,
                            'rev_diff_file_size' => $UserFileEvent->rev_diff_file_size,
                            'is_folder' => false,
                            'uuid' => $UserFile->file_uuid,
                            'file_id' => $UserFile->file_id,
                            'file_parent_id' => $UserFile->file_parent_id,
                            'file_name' => $UserFile->file_name,
                            'file_size' => $UserFile->file_size,
                            'user_id' => $UserNode->user_id,
                            'node_id' => $UserNode->node_id,
                            'parent_folder_uuid' => $parent ? $parent->file_uuid : null,
                        ],
                    ];

                    /* Вызов самое себя в случае если имеется коллаборация по родительской папке */
                    if ($check_collaboration_access) {
                        if ($this->collaboration_id && $file_parent_uuid) {
                            $UserColleagues = UserColleagues::find()
                                ->where([
                                    'collaboration_id' => $this->collaboration_id,
                                    'colleague_status' => UserColleagues::STATUS_JOINED,
                                ])
                                //->andWhere('(user_id!=:user_id) AND (user_id IS NOT NULL)', [':user_id' => $UserNode->user_id])
                                ->andWhere('(user_id IS NOT NULL)')
                                ->all();
                            /** @var \common\models\UserColleagues $colleague */
                            foreach ($UserColleagues as $colleague) {

                                if ($colleague->user_id == $UserNode->user_id) {

                                    /* создаем репорт для овнера о том что сделал сам овнер */
                                    if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                        $UserData = Users::findIdentity($colleague->user_id);
                                        if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                            $Report = ColleaguesReports::createNewReport(
                                                $event_data[0],
                                                $colleague,
                                                $UserNode
                                            );
                                        }
                                    }

                                } else {
                                    $data['copy_file_uuid'] = $UserFile->file_uuid;
                                    $data['file_uuid'] = $UserFile->file_uuid;
                                    $data['folder_uuid'] = $file_parent_uuid;
                                    $data['file_name'] = $UserFile->file_name;
                                    $data['file_size'] = $UserFile->file_size;
                                    $data['collaboration_id'] = $this->collaboration_id;
                                    $data['is_owner'] = UserFiles::IS_COLLEAGUE;
                                    $data['event_uuid'] = $UserFileEvent->event_uuid;
                                    $data['event_creator_user_id'] = $UserNode->user_id;
                                    $data['event_creator_node_id'] = $UserNode->node_id;

                                    $model = new NodeApi(['copy_file_uuid', 'file_uuid', 'file_name', 'file_size']);
                                    $model->load(['NodeApi' => $data]);
                                    $model->validate();
                                    $answer = $model->file_event_copy(
                                        self::registerNodeFM(Users::findIdentity($colleague->user_id)),
                                        true,
                                        false,
                                        false
                                    );

                                    /* собираем евенты */
                                    if (isset($answer['event_data'])) {
                                        foreach ($answer['event_data'] as $k => $v) {
                                            $event_data_redis[] = $v;

                                            /* создаем репорт для овнера */
                                            if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                                $UserData = Users::findIdentity($colleague->user_id);
                                                if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                                    $Report = ColleaguesReports::createNewReport(
                                                        $v,
                                                        $colleague,
                                                        $UserNode
                                                    );
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            /* Отправка данных на вебсокет */
                            /* Отправляем в редис */
                            if (isset($event_data_redis)) {
                                try {
                                    $this->redis->publish("collaboration:{$this->collaboration_id}:fsevent", Json::encode($event_data_redis));
                                    $this->redis->save();
                                } catch (\Exception $e) {
                                }
                            }

                        }
                    }

                    /* успешное завершение транзакции */
                    $transaction->commit();

                    /* Создание файла со служебной инфой для отображения в ФС вебФМ */
                    if (!$internalNodeFM) {
                        if (file_exists($file_name)) {
                            @unlink($file_name);
                        }
                        if (!file_exists($file_name)) {
                            FileSys::mkdir(dirname($file_name), UserFiles::CHMOD_DIR, true);
                            FileSys::touch($file_name, UserFiles::CHMOD_DIR, UserFiles::CHMOD_FILE);
                            UserFiles::createFileInfo($file_name, $UserFile);
                        }

                        /* Удаление из аплоадов */
                        //UserUploads::deleteAll(['user_id' => $UserNode->user_id, 'upload_path' => $relativePath]);
                        UserUploads::deleteRecords(['user_id' => $UserNode->user_id, 'upload_path' => $relativePath]);
                    }

                    $ret = [
                        'result' => "success",
                        'info' => "create-event stored successfully",
                        'data' => [
                            'event_id' => $UserFileEvent->event_id,
                            'event_uuid' => $UserFileEvent->event_uuid,
                            'file_uuid' => $UserFile->file_uuid,
                            'timestamp' => $UserFileEvent->event_timestamp,
                            'diff_file_uuid' => $UserFileEvent->diff_file_uuid,
                            'rev_diff_file_uuid' => $UserFileEvent->rev_diff_file_uuid,
                            'file_name_before_event' => $UserFileEvent->file_name_before_event,
                            'file_name_after_event' => $UserFileEvent->file_name_after_event,
                            'file_size_before_event' => $UserFileEvent->file_size_before_event,
                            'file_size_after_event' => $UserFileEvent->file_size_after_event,
                            'file_hash_before_event' => $UserFileEvent->file_hash_before_event,
                            'file_hash' => $UserFileEvent->file_hash,
                        ],
                    ];

                    /* Отправка евента на редис */
                    if (!in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT])) {
                        try {
                            $this->redis->publish("user:{$UserNode->user_id}:fs_events", Json::encode($event_data));
                            $this->redis->save();
                        } catch (\Exception $e) {
                            RedisSafe::createNewRecord(
                                RedisSafe::TYPE_FS_EVENTS,
                                $UserNode->user_id,
                                null,
                                Json::encode([
                                    'action'           => 'fs_events',
                                    'chanel'           => "user:{$UserNode->user_id}:fs_events",
                                    'user_id'          => $UserNode->user_id,
                                ])
                            );
                        }
                    }

                    /* Подготовка данных об евенте в общий массив возврата, если нужно */
                    if ($sendEventToSignal) {
                        $ret['event_data'] = $event_data;
                    }

                    /* Освобождаем от блокировки по мутексу, нужно для ФМ,
                     * который за один проход скрипта может делать несколько евентов цикле,
                     * а иначе он не может например удалить сразу две папки*/
                    $this->mutex->release($mutex_name);
                    if (isset($mutex_collaboration_name)) { $this->mutex->release($mutex_collaboration_name); }

                    return $ret;
                } else {
                    $transaction->rollBack();

                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_DATABASE_FAILURE,
                        'info' => "An internal server error occurred.",
                        'debug' => $UserFileEvent->getErrors(),
                    ];
                }
            } else {
                $transaction->rollBack();

                return [
                    'result' => "error",
                    'errcode' => self::ERROR_DATABASE_FAILURE,
                    'info' => "An internal server error occurred.",
                    'debug' => $UserFile->getErrors(),
                ];
            }
        } catch (\Exception $e) {

            return [
                'result' => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $e,
            ];
        }
    }

    /**
     * Метод для регистрации события update
     * @param \common\models\UserNode $UserNode
     * @param bool $internalNodeFM .
     * @param bool $check_collaboration_access
     * @param bool $sendEventToSignal
     * @param bool $send_collaboration_event
     * @return array
     */
    public function file_event_update($UserNode, $sendEventToSignal = false, $internalNodeFM = false, $check_collaboration_access = true, $send_collaboration_event = false)
    {
        /* Проверим нет ли лока на выполнение ФС действий вследствие друхих действий этого юзера */
        $mutex_name = 'user_id_' . $UserNode->user_id;
        if (!$this->is_restore_patch) {
            if (!$this->mutex->acquire($mutex_name, MUTEX_WAIT_TIMEOUT)) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_FS_TRY_LATER,
                    'info' => "File or folder is locked now, try later please.",
                ];
            }
        }

        /* Проверим, есть ли уже в бд евент с таким event_uuid если есть - это повтор и выполнять ничего не нужно. Просто отдать ответ */
        $check_event = $this->check_is_event_exist($UserNode->user_id);
        if ($check_event) {
            return $check_event;
        }

        /* находим нужный файл по ууид */
        $UserFile = UserFiles::findOne([
            'file_uuid' => $this->file_uuid,
            'is_folder' => UserFiles::TYPE_FILE,
            'user_id' => $UserNode->user_id,
            'is_deleted' => UserFiles::FILE_UNDELETED,
        ]);

        /* проверка существования исходного файла */
        if (!$UserFile) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "File not found.",
            ];
        }

        /* Проверка что файл хеш  предыдущего состояния отличается от нового (файл реально изменился) */
        if ($UserFile->file_md5 == $this->hash) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FILE_NOT_CHANGED,
                'info' => "File not changed (previous hash equal current hash).",
            ];
        }

        /* проверка прав коллаборации */
        if ($check_collaboration_access) {
            if ($UserFile->collaboration_id) {
                $Colleague = UserColleagues::findOne([
                    'user_id' => $UserNode->user_id,
                    'collaboration_id' => $UserFile->collaboration_id,
                ]);
                //if (!in_array($Colleague->colleague_permission, [UserColleagues::PERMISSION_EDIT, UserColleagues::PERMISSION_OWNER])) {
                if (!$Colleague ||
                    ($Colleague->colleague_status != UserColleagues::STATUS_JOINED) ||
                    !in_array($Colleague->colleague_permission, [UserColleagues::PERMISSION_EDIT, UserColleagues::PERMISSION_OWNER])) {
                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_COLLABORATION_ACCESS,
                        'info' => "Haven't access to change collaboration.",
                    ];
                }
            }
        }

        /* Проверка прав лицензии */
        if ($check_collaboration_access) {
            $ret_lic_acc = self::checkLicenseAccess($UserNode, $UserFile);
            if (!$ret_lic_acc['access']) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_LICENSE_ACCESS,
                    'info' => isset($ret_lic_acc['info'])
                        ? $ret_lic_acc['info']
                        : "Haven't access to edit file. It has another owner NodeId.",
                ];
            }
        }

        /* проверка что файл не удаили до этого */
        if ($UserFile->last_event_type == UserFileEvents::TYPE_DELETE) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "File was deleted. You can't do any actions with this file.",
            ];
        }

        /* проверка ошибки синхронизации по признаку last_event_id */
        /*
        $max_event_id = UserFileEvents::find()
            ->andWhere(['file_id' => $UserFile->file_id])
            ->max('event_id');
        if (intval($max_event_id) !== intval($this->last_event_id)) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info'    => "Synchronization conflict. (max_event_id={$max_event_id}; last_event_id={$this->last_event_id})"
            ];
        }
        */
        /** @var \common\models\UserFileEvents $maxUserFileEvent */
        $maxUserFileEvent = UserFileEvents::find()
            ->where(['file_id' => $UserFile->file_id])
            ->orderBy(['event_id' => SORT_DESC])
            ->limit(1)
            ->one();
        //var_dump($maxUserFileEvent->event_id); exit;
        if (!$maxUserFileEvent) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "Synchronization conflict.",
                'debug' => "Synchronization conflict. last_event_id for file {$UserFile->file_uuid} not found.",
            ];
        }
        if (intval($maxUserFileEvent->event_id) != intval($this->last_event_id)) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "Synchronization conflict.",
                'debug' => "Synchronization conflict. (max_event_id={$maxUserFileEvent->event_id}; last_event_id={$this->last_event_id})",
            ];
        }

        /* Поиск родителя */
        /** @var \common\models\UserFiles $Parent */
        if ($UserFile->file_parent_id) {
            $Parent = UserFiles::findOne(['file_id' => $UserFile->file_parent_id]);
        } else {
            $Parent = null;
        }

        /* проверка что если это коллаба, то она не должна быть залочена */
        if (!$this->is_restore_patch) {
            if ($check_collaboration_access && $UserFile->collaboration_id) {
                $mutex_collaboration_name = 'collaboration_id_' . $UserFile->collaboration_id;
                if (!$this->mutex->acquire($mutex_collaboration_name, MUTEX_WAIT_TIMEOUT)) {
                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_FS_TRY_LATER,
                        'info' => "Collaboration is locked now, try later please..",
                    ];
                }
            }
        }

        try {
            /* Начинаем транзакцию */
            $transaction = Yii::$app->db->beginTransaction();
            $UserFileEvent = new UserFileEvents();
            $UserFileEvent->event_uuid = $this->event_uuid ? $this->event_uuid : self::uniq_uuid();
            $UserFileEvent->event_type = UserFileEvents::TYPE_UPDATE;
            $UserFileEvent->event_timestamp = time();
            $UserFileEvent->event_invisible = isset($this->event_invisible) ? $this->event_invisible : UserFileEvents::EVENT_VISIBLE;
            $UserFileEvent->last_event_id = $this->last_event_id;
            $UserFileEvent->file_id = $UserFile->file_id;
            $UserFileEvent->diff_file_uuid = ($this->diff_file_uuid)
                ? $this->diff_file_uuid
                : md5('diff_file_uuid' . $this->hash . $maxUserFileEvent->file_hash);
            $UserFileEvent->diff_file_size = $this->diff_file_size;
            $UserFileEvent->rev_diff_file_uuid = ($this->rev_diff_file_uuid)
                ? $this->rev_diff_file_uuid
                : md5('diff_file_uuid' . $maxUserFileEvent->file_hash . $this->hash);
            $UserFileEvent->rev_diff_file_size = $this->rev_diff_file_size;
            $UserFileEvent->file_hash_before_event = $UserFile->file_md5;
            $UserFileEvent->file_hash = $this->hash;
            $UserFileEvent->node_id = $UserNode->node_id;
            $UserFileEvent->user_id = $UserNode->user_id;
            $UserFileEvent->file_name_before_event = $UserFile->file_name;
            $UserFileEvent->file_name_after_event = $UserFile->file_name;
            $UserFileEvent->file_size_before_event = $UserFile->file_size;
            $UserFileEvent->file_size_after_event = $this->file_size;
            $UserFileEvent->parent_before_event = $UserFile->file_parent_id;
            $UserFileEvent->parent_after_event = $UserFile->file_parent_id;
            $UserFileEvent->is_rollback = ($this->is_restore_patch) ? UserFileEvents::IS_ROLLBACK : UserFileEvents::NOT_ROLLBACK;
            $UserFileEvent->event_creator_user_id = (empty($this->event_creator_user_id)) ? $UserNode->user_id : $this->event_creator_user_id;
            $UserFileEvent->event_creator_node_id = (empty($this->event_creator_node_id)) ? $UserNode->node_id : $this->event_creator_node_id;

            if ($UserFileEvent->save()) {
                $old_file_size = $UserFile->file_size;
                $UserFile->file_size = $this->file_size;
                $UserFile->file_md5 = $this->hash;
                $UserFile->is_updated = UserFiles::FILE_UPDATED;
                $UserFile->is_outdated = UserFiles::FILE_UNOUTDATED;
                $UserFile->file_lastatime = time();
                $UserFile->file_lastmtime = time();
                $UserFile->last_event_type = UserFileEvents::TYPE_UPDATE;
                $UserFile->last_event_uuid = $UserFileEvent->event_uuid;
                //$UserFile->last_event_id = $UserFileEvent->event_id;
                $UserFile->diff_file_uuid = $UserFileEvent->diff_file_uuid;
                if ($UserFile->save()) {

                    $User = Users::getPathNodeFS($UserFile->user_id);

                    /* обновление информации о размере родительской папки и количества файлов в ней */
                    UserFiles::update_parents_size_and_count(
                        $User,
                        $UserFile,
                        $UserFile->file_size - $old_file_size,
                        0
                    );

                    /* евенты для отправки на сигнальный */
                    $event_data[] = [
                        'operation' => "file_event",
                        'data' => [
                            'event_id' => $UserFileEvent->event_id,
                            'event_uuid' => $UserFileEvent->event_uuid,
                            'last_event_id' => $UserFileEvent->last_event_id,
                            'event_type' => UserFileEvents::getType($UserFileEvent->event_type),
                            'event_type_int' => $UserFileEvent->event_type,
                            'timestamp' => $UserFileEvent->event_timestamp,
                            'hash' => $UserFileEvent->file_hash,
                            'file_hash_before_event' => $UserFileEvent->file_hash_before_event,
                            'file_hash_after_event' => $UserFileEvent->file_hash,
                            'file_hash' => $UserFileEvent->file_hash,
                            'diff_file_uuid' => $UserFileEvent->diff_file_uuid,
                            'diff_file_size' => $UserFileEvent->diff_file_size,
                            'rev_diff_file_uuid' => $UserFileEvent->rev_diff_file_uuid,
                            'rev_diff_file_size' => $UserFileEvent->rev_diff_file_size,
                            'file_size_after_event' => $UserFileEvent->file_size_after_event,
                            'file_size_before_event' => $UserFileEvent->file_size_before_event,
                            'is_restore_patch' => $this->is_restore_patch,
                            'is_folder' => false,
                            'uuid' => $UserFile->file_uuid,
                            'file_id' => $UserFile->file_id,
                            'file_parent_id' => $UserFile->file_parent_id,
                            'file_name' => $UserFile->file_name,
                            'file_size' => $UserFile->file_size,
                            'user_id' => $UserNode->user_id,
                            'node_id' => $UserNode->node_id,
                            'parent_folder_uuid' => $Parent ? $Parent->file_uuid : null,
                        ],
                    ];

                    /* Вызов самое себя в случае если имеется коллаборация по родительской папке */
                    if ($check_collaboration_access) {
                        if ($UserFile->collaboration_id) {
                            $UserColleagues = UserColleagues::find()
                                ->where([
                                    'collaboration_id' => $UserFile->collaboration_id,
                                    'colleague_status' => UserColleagues::STATUS_JOINED,
                                ])
                                //->andWhere('(user_id!=:user_id) AND (user_id IS NOT NULL)', [':user_id' => $UserNode->user_id])
                                ->andWhere('(user_id IS NOT NULL)')
                                ->all();
                            /** @var \common\models\UserColleagues $colleague */
                            foreach ($UserColleagues as $colleague) {

                                if ($colleague->user_id == $UserNode->user_id) {

                                    /* создаем репорт для овнера о том что сделал сам овнер */
                                    if ($UserFileEvent->event_invisible == UserFileEvents::EVENT_VISIBLE) {
                                        if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                            $UserData = Users::findIdentity($colleague->user_id);
                                            if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                                $Report = ColleaguesReports::createNewReport(
                                                    $event_data[0],
                                                    $colleague,
                                                    $UserNode
                                                );
                                            }
                                        }
                                    }

                                } else {
                                    $UserFile_FindID = UserFiles::findOne([
                                        'file_uuid' => $this->file_uuid,
                                        'is_folder' => UserFiles::TYPE_FILE,
                                        'user_id' => $colleague->user_id,
                                        'is_deleted' => UserFiles::FILE_UNDELETED,
                                    ]);
                                    if ($UserFile_FindID) {
                                        $last_event_id = UserFileEvents::find()
                                            ->andWhere(['file_id' => $UserFile_FindID->file_id])
                                            ->max('event_id');

                                        $data['is_restore_patch'] = $this->is_restore_patch;        //++ 2018-11-09 16:52
                                        $data['event_invisible'] = $UserFileEvent->event_invisible; //++ 2018-11-09 16:52
                                        $data['file_uuid'] = $this->file_uuid;
                                        $data['file_size'] = $this->file_size;
                                        $data['last_event_id'] = $last_event_id;
                                        $data['diff_file_size'] = $this->diff_file_size;
                                        $data['rev_diff_file_size'] = $this->rev_diff_file_size;
                                        $data['hash'] = $this->hash;
                                        $data['diff_file_uuid'] = $UserFileEvent->diff_file_uuid;
                                        $data['rev_diff_file_uuid'] = $UserFileEvent->rev_diff_file_uuid;
                                        $data['event_uuid'] = $UserFileEvent->event_uuid;
                                        $data['event_creator_user_id'] = $UserNode->user_id;
                                        $data['event_creator_node_id'] = $UserNode->node_id;

                                        $model = new NodeApi(['file_uuid', 'file_size', 'last_event_id', 'diff_file_size', 'rev_diff_file_size', 'hash']);
                                        $model->load(['NodeApi' => $data]);
                                        $model->validate();
                                        $answer = $model->file_event_update(
                                            self::registerNodeFM(Users::findIdentity($colleague->user_id)),
                                            true,
                                            false,
                                            false
                                        );

                                        /* собираем евенты */
                                        if (isset($answer['event_data'])) {
                                            foreach ($answer['event_data'] as $k => $v) {
                                                $event_data_redis[] = $v;

                                                /* создаем репорт для овнера */
                                                if ($UserFileEvent->event_invisible == UserFileEvents::EVENT_VISIBLE) {
                                                    if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                                        $UserData = Users::findIdentity($colleague->user_id);
                                                        if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                                            $Report = ColleaguesReports::createNewReport(
                                                                $v,
                                                                $colleague,
                                                                $UserNode
                                                            );
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            /* Отправка данных на вебсокет */
                            /* Отправляем в редис */
                            if (isset($event_data_redis)) {
                                try {
                                    $this->redis->publish("collaboration:{$UserFile->collaboration_id}:fsevent", Json::encode($event_data_redis));
                                    $this->redis->save();
                                } catch (\Exception $e) {
                                }
                            }

                        }
                    }

                    /* успешное завершение транзакции */
                    $transaction->commit();

                    /* Создание файла со служебной инфой для отображения в ФС вебФМ */
                    if (!$internalNodeFM) {
                        $relativePath = UserFiles::getFullPath($UserFile);
                        $file_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;

                        if (!file_exists($file_name)) {
                            FileSys::touch($file_name, UserFiles::CHMOD_DIR, UserFiles::CHMOD_FILE);
                        }
                        UserFiles::createFileInfo($file_name, $UserFile);
                    }

                    $ret = [
                        'result' => "success",
                        'info' => "update-event stored successfully",
                        'data' => [
                            'event_id' => $UserFileEvent->event_id,
                            'event_uuid' => $UserFileEvent->event_uuid,
                            'timestamp' => $UserFileEvent->event_timestamp,
                            'diff_file_uuid' => $UserFileEvent->diff_file_uuid,
                            'rev_diff_file_uuid' => $UserFileEvent->rev_diff_file_uuid,
                            'file_name_before_event' => $UserFileEvent->file_name_before_event,
                            'file_name_after_event' => $UserFileEvent->file_name_after_event,
                            'file_size_before_event' => $UserFileEvent->file_size_before_event,
                            'file_size_after_event' => $UserFileEvent->file_size_after_event,
                            'file_hash_before_event' => $UserFileEvent->file_hash_before_event,
                            'file_hash' => $UserFileEvent->file_hash,
                        ],
                    ];

                    /* Отправка евента на редис */
                    if (!$this->is_restore_patch) {
                        if (!in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT])) {
                            try {
                                $this->redis->publish("user:{$UserNode->user_id}:fs_events", Json::encode($event_data));
                                $this->redis->save();
                            } catch (\Exception $e) {
                                RedisSafe::createNewRecord(
                                    RedisSafe::TYPE_FS_EVENTS,
                                    $UserNode->user_id,
                                    null,
                                    Json::encode([
                                        'action'           => 'fs_events',
                                        'chanel'           => "user:{$UserNode->user_id}:fs_events",
                                        'user_id'          => $UserNode->user_id,
                                    ])
                                );
                            }
                        }
                    }

                    /* Подготовка данных об евенте в общий массив возврата, если нужно */
                    if ($sendEventToSignal || $this->is_restore_patch) {
                        $ret['event_data'] = $event_data;
                    }

                    /* Освобождаем от блокировки по мутексу, нужно для ФМ,
                     * который за один проход скрипта может делать несколько евентов цикле,
                     * а иначе он не может например удалить сразу две папки*/
                    $this->mutex->release($mutex_name);
                    if (isset($mutex_collaboration_name)) { $this->mutex->release($mutex_collaboration_name); }

                    return $ret;
                } else {
                    $transaction->rollBack();

                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_DATABASE_FAILURE,
                        'info' => "An internal server error occurred.",
                        'debug' => $UserFile->getErrors(),
                    ];
                }
            } else {
                $transaction->rollBack();

                return [
                    'result' => "error",
                    'errcode' => self::ERROR_DATABASE_FAILURE,
                    'info' => "An internal server error occurred.",
                    'debug' => $UserFileEvent->getErrors(),
                ];
            }
        } catch (\Exception $e) {

            return [
                'result' => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $e,
            ];
        }
    }

    /**
     * Метод для регистрации события delete
     * @param \common\models\UserNode $UserNode
     * @param bool $internalNodeFM
     * @param bool $check_collaboration_access
     * @param bool $sendEventToSignal
     * @param bool $send_collaboration_event
     * @return array
     */
    public function file_event_delete($UserNode, $sendEventToSignal = false, $internalNodeFM = false, $check_collaboration_access = true, $send_collaboration_event = false)
    {
        /* Проверим нет ли лока на выполнение ФС действий вследствие друхих действий этого юзера */
        $mutex_name = 'user_id_' . $UserNode->user_id;
        if (!$this->mutex->acquire($mutex_name, MUTEX_WAIT_TIMEOUT)) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_TRY_LATER,
                'info' => "File or folder is locked now, try later please.",
            ];
        }

        /* Проверим, есть ли уже в бд евент с таким event_uuid если есть - это повтор и выполнять ничего не нужно. Просто отдать ответ */
        $check_event = $this->check_is_event_exist($UserNode->user_id);
        if ($check_event) {
            return $check_event;
        }

        /* находим нужный файл по ууид */
        $UserFile = UserFiles::findOne([
            'file_uuid' => $this->file_uuid,
            'is_folder' => UserFiles::TYPE_FILE,
            'user_id' => $UserNode->user_id,
            'is_deleted' => UserFiles::FILE_UNDELETED,
        ]);

        /* проверка существования исходного файла */
        if (!$UserFile) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC_NOT_FOUND,
                'info' => "File not found.",
            ];
        }

        /* проверка прав коллаборации */
        if ($check_collaboration_access) {
            if ($UserFile->collaboration_id) {
                $Colleague = UserColleagues::findOne([
                    'user_id' => $UserNode->user_id,
                    'collaboration_id' => $UserFile->collaboration_id,
                ]);
                //if (!in_array($Colleague->colleague_permission, [UserColleagues::PERMISSION_EDIT, UserColleagues::PERMISSION_OWNER])) {
                if (!$Colleague ||
                    ($Colleague->colleague_status != UserColleagues::STATUS_JOINED) ||
                    !in_array($Colleague->colleague_permission, [UserColleagues::PERMISSION_EDIT, UserColleagues::PERMISSION_OWNER])) {
                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_COLLABORATION_ACCESS,
                        'info' => "Haven't access to change collaboration.",
                    ];
                }
            }
        }

        /* Проверка прав лицензии */
        if ($check_collaboration_access) {
            $ret_lic_acc = self::checkLicenseAccess($UserNode, $UserFile);
            if (!$ret_lic_acc['access']) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_LICENSE_ACCESS,
                    'info' => isset($ret_lic_acc['info'])
                        ? $ret_lic_acc['info']
                        : "Haven't access to delete file. It has another owner NodeId.",
                ];
            }
        }

        /* проверка что файл не удаили до этого */
        if ($UserFile->last_event_type == UserFileEvents::TYPE_DELETE) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "File already was deleted. You can't do any actions with this file.",
            ];
        }

        /* проверка ошибки синхронизации по признаку last_event_id */
        /*
        $max_event_id = UserFileEvents::find()
            ->andWhere(['file_id' => $UserFile->file_id])
            ->max('event_id');
        if (intval($max_event_id) !== intval($this->last_event_id)) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info'    => "Synchronization conflict. (max_event_id={$max_event_id}; last_event_id={$this->last_event_id})"
            ];
        }
        */
        /** @var \common\models\UserFileEvents $maxUserFileEvent */
        $maxUserFileEvent = UserFileEvents::find()
            ->where(['file_id' => $UserFile->file_id])
            ->orderBy(['event_id' => SORT_DESC])
            ->limit(1)
            ->one();
        //var_dump($maxUserFileEvent->event_id); exit;
        if (!$maxUserFileEvent) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "Synchronization conflict.",
                'debug' => "Synchronization conflict. last_event_id for file {$UserFile->file_uuid} not found.",
            ];
        }
        if (intval($maxUserFileEvent->event_id) != intval($this->last_event_id)) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "Synchronization conflict.",
                'debug' => "Synchronization conflict. (max_event_id={$maxUserFileEvent->event_id}; last_event_id={$this->last_event_id})",
            ];
        }

        /* Поиск родителя */
        /** @var \common\models\UserFiles $Parent */
        if ($UserFile->file_parent_id) {
            $Parent = UserFiles::findOne(['file_id' => $UserFile->file_parent_id]);
        } else {
            $Parent = null;
        }

        /* проверка что если это коллаба, то она не должна быть залочена */
        if ($check_collaboration_access && $UserFile->collaboration_id) {
            $mutex_collaboration_name = 'collaboration_id_' . $UserFile->collaboration_id;
            if (!$this->mutex->acquire($mutex_collaboration_name, MUTEX_WAIT_TIMEOUT)) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_FS_TRY_LATER,
                    'info' => "Collaboration is locked now, try later please..",
                ];
            }
        }

        try {
            //$append_file_name = "_" . $UserFile->file_uuid . "_" . time();
            $append_file_name = " (Deleted " . date('d-m-Y H.i.s') .")";
            $tmp = FileSys::pathinfo($UserFile->file_name);
            //var_dump($tmp); exit;
            if (isset($tmp['extension'], $tmp['filename'])) {
                $new_UserFile_file_name = $tmp['filename'] . $append_file_name . "." . $tmp['extension'];
            } else {
                $new_UserFile_file_name = $UserFile->file_name . $append_file_name;
            }
            unset($tmp);

            /* Проверка длинны имени файла после удаления, если больше 255 то нужно перед удалением укоротить */
            $test_length = mb_strlen($new_UserFile_file_name, '8bit');
            if ($test_length > UserFiles::FILE_NAME_MAX_LENGTH) {
                $new_UserFile_file_name = Functions::cutUtf8StrToLengthBites($new_UserFile_file_name, UserFiles::FILE_NAME_MAX_LENGTH);
            }

            /* Начинаем транзакцию */
            $transaction = Yii::$app->db->beginTransaction();
            $UserFileEvent = new UserFileEvents();
            $UserFileEvent->event_uuid = $this->event_uuid ? $this->event_uuid : self::uniq_uuid();
            $UserFileEvent->event_type = UserFileEvents::TYPE_DELETE;
            $UserFileEvent->event_timestamp = time();
            $UserFileEvent->last_event_id = $this->last_event_id;
            $UserFileEvent->file_id = $UserFile->file_id;
            $UserFileEvent->diff_file_uuid = $maxUserFileEvent->diff_file_uuid;
            $UserFileEvent->diff_file_size = $maxUserFileEvent->diff_file_size;
            $UserFileEvent->rev_diff_file_uuid = $maxUserFileEvent->rev_diff_file_uuid;
            $UserFileEvent->rev_diff_file_size = $maxUserFileEvent->rev_diff_file_size;
            $UserFileEvent->file_hash_before_event = $maxUserFileEvent->file_hash;
            $UserFileEvent->file_hash = null;
            $UserFileEvent->node_id = $UserNode->node_id;
            $UserFileEvent->user_id = $UserNode->user_id;
            $UserFileEvent->file_name_before_event = $UserFile->file_name;
            $UserFileEvent->file_name_after_event = $new_UserFile_file_name;
            $UserFileEvent->file_size_before_event = $UserFile->file_size;
            $UserFileEvent->file_size_after_event = 0;
            $UserFileEvent->parent_before_event = $UserFile->file_parent_id;
            $UserFileEvent->parent_after_event = $UserFile->file_parent_id;
            $UserFileEvent->event_creator_user_id = (empty($this->event_creator_user_id)) ? $UserNode->user_id : $this->event_creator_user_id;
            $UserFileEvent->event_creator_node_id = (empty($this->event_creator_node_id)) ? $UserNode->node_id : $this->event_creator_node_id;

            if ($UserFileEvent->save()) {
                $relativePath = UserFiles::getFullPath($UserFile);
                $UserFile->file_name = $new_UserFile_file_name;
                $UserFile->file_lastatime = time();
                $UserFile->is_deleted = UserFiles::FILE_DELETED;
                $UserFile->is_outdated = UserFiles::FILE_UNOUTDATED;
                $UserFile->last_event_type = UserFileEvents::TYPE_DELETE;
                $UserFile->last_event_uuid = $UserFileEvent->event_uuid;
                //$UserFile->last_event_id = $UserFileEvent->event_id;
                //$UserFile->collaboration_id = null;
                $UserFile->is_shared = UserFiles::FILE_UNSHARED;
                $UserFile->share_hash = null;
                $UserFile->share_group_hash = null;
                $UserFile->share_lifetime = null;
                $UserFile->share_ttl_info = null;
                $UserFile->share_created = null;
                $UserFile->share_password = null;
                if ($UserFile->save()) {

                    /* Подготовка инфы о юзере */
                    $User = Users::getPathNodeFS($UserFile->user_id);

                    /* Подготовка имени и полного пути файла а так же проверка на его максимальную длинну */
                    $relativePathAfter = UserFiles::getFullPath($UserFile);
                    $file_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePathAfter;
                    if (mb_strlen($file_name, '8bit') > UserFiles::FILE_PATH_MAX_LENGTH) {
                        $transaction->rollBack();

                        return [
                            'result'  => "error",
                            'errcode' => self::ERROR_FILE_PATH_MAX_LENGTH,
                            'info'    => "Path for folder or file is too long (more than " . UserFiles::FILE_PATH_MAX_LENGTH . " bytes).",
                        ];
                    }

                    /* обновление информации о размере родительской папки и количества файлов в ней */
                    UserFiles::update_parents_size_and_count(
                        $User,
                        $UserFile,
                        -1 * $UserFile->file_size,
                        -1
                    );

                    /* евенты для отправки на сигнальный */
                    $event_data[] = [
                        'operation' => "file_event",
                        'data' => [
                            'event_id' => $UserFileEvent->event_id,
                            'event_uuid' => $UserFileEvent->event_uuid,
                            'last_event_id' => $UserFileEvent->last_event_id,
                            'event_type' => UserFileEvents::getType($UserFileEvent->event_type),
                            'event_type_int' => $UserFileEvent->event_type,
                            'timestamp' => $UserFileEvent->event_timestamp,
                            'hash' => $UserFileEvent->file_hash,
                            'file_hash_before_event' => $UserFileEvent->file_hash_before_event,
                            'file_hash_after_event' => $UserFileEvent->file_hash,
                            'file_hash' => $UserFileEvent->file_hash,
                            'diff_file_uuid' => $UserFileEvent->diff_file_uuid,
                            'diff_file_size' => $UserFileEvent->diff_file_size,
                            'rev_diff_file_uuid' => $UserFileEvent->rev_diff_file_uuid,
                            'rev_diff_file_size' => $UserFileEvent->rev_diff_file_size,
                            'is_folder' => false,
                            'uuid' => $UserFile->file_uuid,
                            'file_id' => $UserFile->file_id,
                            'file_parent_id' => $UserFile->file_parent_id,
                            'file_name' => $UserFileEvent->file_name_after_event, //null
                            'file_name_before_event' => $UserFileEvent->file_name_before_event,
                            'file_size' => $UserFile->file_size, //0
                            'user_id' => $UserNode->user_id,
                            'node_id' => $UserNode->node_id,
                            'parent_folder_uuid' => $Parent ? $Parent->file_uuid : null,
                        ],
                    ];

                    /* Вызов самое себя в случае если имеется коллаборация по родительской папке */
                    if ($check_collaboration_access) {
                        if ($UserFile->collaboration_id) {
                            $UserColleagues = UserColleagues::find()
                                ->where([
                                    'collaboration_id' => $UserFile->collaboration_id,
                                    'colleague_status' => UserColleagues::STATUS_JOINED,
                                ])
                                //->andWhere('(user_id!=:user_id) AND (user_id IS NOT NULL)', [':user_id' => $UserNode->user_id])
                                ->andWhere('(user_id IS NOT NULL)')
                                ->all();
                            /** @var \common\models\UserColleagues $colleague */
                            foreach ($UserColleagues as $colleague) {

                                if ($colleague->user_id == $UserNode->user_id) {

                                    /* создаем репорт для овнера о том что сделал сам овнер */
                                    if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                        $UserData = Users::findIdentity($colleague->user_id);
                                        if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                            $Report = ColleaguesReports::createNewReport(
                                                $event_data[0],
                                                $colleague,
                                                $UserNode
                                            );
                                        }
                                    }

                                } else {
                                    $UserFile_FindID = UserFiles::findOne([
                                        'file_uuid' => $this->file_uuid,
                                        'is_folder' => UserFiles::TYPE_FILE,
                                        'user_id' => $colleague->user_id,
                                        'is_deleted' => UserFiles::FILE_UNDELETED,
                                    ]);
                                    if ($UserFile_FindID) {
                                        $last_event_id = UserFileEvents::find()
                                            ->andWhere(['file_id' => $UserFile_FindID->file_id])
                                            ->max('event_id');

                                        $data['file_uuid'] = $this->file_uuid;
                                        $data['last_event_id'] = $last_event_id;
                                        $data['event_uuid'] = $UserFileEvent->event_uuid;
                                        $data['event_creator_user_id'] = $UserNode->user_id;
                                        $data['event_creator_node_id'] = $UserNode->node_id;

                                        $model = new NodeApi(['file_uuid', 'last_event_id']);
                                        $model->load(['NodeApi' => $data]);
                                        $model->validate();
                                        $answer = $model->file_event_delete(
                                            self::registerNodeFM(Users::findIdentity($colleague->user_id)),
                                            true,
                                            false,
                                            false
                                        );

                                        /* собираем евенты */
                                        if (isset($answer['event_data'])) {
                                            foreach ($answer['event_data'] as $k => $v) {
                                                $event_data_redis[] = $v;

                                                /* создаем репорт для овнера */
                                                if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                                    $UserData = Users::findIdentity($colleague->user_id);
                                                    if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                                        $Report = ColleaguesReports::createNewReport(
                                                            $v,
                                                            $colleague,
                                                            $UserNode
                                                        );
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            /* Отправка данных на вебсокет */
                            /* Отправляем в редис */
                            if (isset($event_data_redis)) {
                                try {
                                    $this->redis->publish("collaboration:{$UserFile->collaboration_id}:fsevent", Json::encode($event_data_redis));
                                    $this->redis->save();
                                } catch (\Exception $e) {
                                }
                            }

                        }
                    }

                    /* успешное завершение транзакции */
                    $transaction->commit();

                    /* Создание файла со служебной инфой для отображения в ФС вебФМ */
                    if (!$internalNodeFM) {
                        /*
                        $User = Users::getPathNodeFS($UserFile->user_id);
                        $file_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;
                        if (file_exists($file_name) && !is_dir($file_name)) {
                            @unlink($file_name);
                        }
                        */
                        //$relativePath = UserFiles::getFullPath($UserFile);
                        $file_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;
                        @unlink($file_name);

                        $_new_relativePath = UserFiles::getFullPath($UserFile);
                        $new_file_name = $User->_full_path . DIRECTORY_SEPARATOR . $_new_relativePath;
                        if (!file_exists($new_file_name)) {
                            FileSys::touch($new_file_name, UserFiles::CHMOD_DIR, UserFiles::CHMOD_FILE);
                        }
                        UserFiles::createFileInfo($new_file_name, $UserFile);
                    }

                    $ret = [
                        'result' => "success",
                        'info' => "delete-event stored successfully",
                        'data' => [
                            'event_id' => $UserFileEvent->event_id,
                            'event_uuid' => $UserFileEvent->event_uuid,
                            'timestamp' => $UserFileEvent->event_timestamp,
                            'diff_file_uuid' => $UserFileEvent->diff_file_uuid,
                            'rev_diff_file_uuid' => $UserFileEvent->rev_diff_file_uuid,
                            'file_name_before_event' => $UserFileEvent->file_name_before_event,
                            'file_name_after_event' => $UserFileEvent->file_name_after_event,
                            'file_size_before_event' => $UserFileEvent->file_size_before_event,
                            'file_size_after_event' => $UserFileEvent->file_size_after_event,
                            'file_hash_before_event' => $UserFileEvent->file_hash_before_event,
                            'file_hash' => $UserFileEvent->file_hash,
                        ],
                    ];

                    /* Отправка евента на редис */
                    if (!in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT])) {
                        try {
                            $this->redis->publish("user:{$UserNode->user_id}:fs_events", Json::encode($event_data));
                            $this->redis->save();
                        } catch (\Exception $e) {
                            RedisSafe::createNewRecord(
                                RedisSafe::TYPE_FS_EVENTS,
                                $UserNode->user_id,
                                null,
                                Json::encode([
                                    'action'           => 'fs_events',
                                    'chanel'           => "user:{$UserNode->user_id}:fs_events",
                                    'user_id'          => $UserNode->user_id,
                                ])
                            );
                        }
                    }

                    /* Подготовка данных об евенте в общий массив возврата, если нужно */
                    if ($sendEventToSignal) {
                        $ret['event_data'] = $event_data;
                    }

                    //$UserFile->collaboration_id = null;
                    $UserFile->save();

                    /* Освобождаем от блокировки по мутексу, нужно для ФМ,
                     * который за один проход скрипта может делать несколько евентов цикле,
                     * а иначе он не может например удалить сразу две папки*/
                    $this->mutex->release($mutex_name);
                    if (isset($mutex_collaboration_name)) { $this->mutex->release($mutex_collaboration_name); }

                    return $ret;
                } else {
                    $transaction->rollBack();

                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_DATABASE_FAILURE,
                        'info' => "An internal server error occurred.",
                        'debug' => $UserFile->getErrors(),
                    ];
                }
            } else {
                $transaction->rollBack();

                return [
                    'result' => "error",
                    'errcode' => self::ERROR_DATABASE_FAILURE,
                    'info' => "An internal server error occurred.",
                    'debug' => $UserFileEvent->getErrors(),
                ];
            }
        } catch (\Exception $e) {

            return [
                'result' => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $e,
            ];
        }
    }

    /**
     * Метод для регистрации события move
     * @param \common\models\UserNode $UserNode
     * @param bool $internalNodeFM
     * @param bool $sendEventToSignal
     * @param bool $send_collaboration_event
     * @return array
     */
    public function file_event_move($UserNode, $sendEventToSignal = false, $internalNodeFM = false, $check_collaboration_access = true, $send_collaboration_event = false)
    {
        /* Проверим нет ли лока на выполнение ФС действий вследствие друхих действий этого юзера */
        $mutex_name = 'user_id_' . $UserNode->user_id;
        if (!$this->mutex->acquire($mutex_name, MUTEX_WAIT_TIMEOUT)) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_TRY_LATER,
                'info' => "File or folder is locked now, try later please.",
            ];
        }

        /* Проверим, есть ли уже в бд евент с таким event_uuid если есть - это повтор и выполнять ничего не нужно. Просто отдать ответ */
        $check_event = $this->check_is_event_exist($UserNode->user_id);
        if ($check_event) {
            return $check_event;
        }

        /* находим нужный файл по ууид */
        $UserFile = UserFiles::findOne([
            'file_uuid' => $this->file_uuid,
            'is_folder' => UserFiles::TYPE_FILE,
            'user_id' => $UserNode->user_id,
            'is_deleted' => UserFiles::FILE_UNDELETED,
        ]);

        /* проверка существования исходного файла */
        if (!$UserFile) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "Source file not found.",
                'debug' => "Source file with file_uuid='{$this->file_uuid}' not found.",
            ];
        }

        /* Проверка прав лицензии */
        if ($check_collaboration_access) {
            $ret_lic_acc = self::checkLicenseAccess($UserNode, $UserFile);
            if (!$ret_lic_acc['access']) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_LICENSE_ACCESS,
                    'info' => isset($ret_lic_acc['info'])
                        ? $ret_lic_acc['info']
                        : "Haven't access to move file. It has another owner NodeId.",
                ];
            }
        }

        /* проверка что файл не удаили до этого */
        if ($UserFile->last_event_type == UserFileEvents::TYPE_DELETE) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "File was deleted. You can't do any actions with this file.",
            ];
        }

        /* проверка существования папки назначения */
        if (!$this->new_folder_uuid) {
            $this->new_folder_uuid = "";
        }
        if (mb_strlen($this->new_folder_uuid)) {
            $parent = UserFiles::findOne([
                'file_uuid' => $this->new_folder_uuid,
                'is_folder' => UserFiles::TYPE_FOLDER,
                'user_id' => $UserNode->user_id,
                'is_deleted' => UserFiles::FILE_UNDELETED,
            ]);
            if (!$parent) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_FS_SYNC_PARENT_NOT_FOUND,
                    'info' => "Destination folder does not exist.",
                    'debug' => "Destination folder with folder_uuid='{$this->new_folder_uuid}' does not exist.",
                ];
            }
            $file_parent_id = $parent->file_id;
            $file_parent_uuid = $parent->file_uuid;
            $file_parent_collaboration_id = $parent->collaboration_id;
        } else {
            $parent = null;
            $file_parent_id = UserFiles::ROOT_PARENT_ID;
            $file_parent_uuid = null;
            $file_parent_collaboration_id = null;
        }
        /** @var \common\models\UserFiles $parent */
        $file_parent_id_before_event = $UserFile->file_parent_id;
        $file_renamed = ($UserFile->file_name != $this->new_file_name);
        $file_moved = ($UserFile->file_parent_id != $file_parent_id);

        /* проверка прав коллаборации */
        if ($check_collaboration_access) {
            /* проверка самого перемещаемого файла */
            if ($UserFile->collaboration_id) {
                $Colleague = UserColleagues::findOne([
                    'user_id' => $UserNode->user_id,
                    'collaboration_id' => $UserFile->collaboration_id,
                ]);
                //if (!in_array($Colleague->colleague_permission, [UserColleagues::PERMISSION_EDIT, UserColleagues::PERMISSION_OWNER])) {
                if (!$Colleague ||
                    ($Colleague->colleague_status != UserColleagues::STATUS_JOINED) ||
                    !in_array($Colleague->colleague_permission, [UserColleagues::PERMISSION_EDIT, UserColleagues::PERMISSION_OWNER])) {
                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_COLLABORATION_ACCESS,
                        'info' => "Haven't access to change collaboration.",
                    ];
                }
            }
            /* проверка папки куда перемещается файл */
            if ($parent && $parent->collaboration_id) {
                $Colleague = UserColleagues::findOne([
                    'user_id' => $UserNode->user_id,
                    'collaboration_id' => $parent->collaboration_id,
                ]);
                if (!$Colleague ||
                    ($Colleague->colleague_status != UserColleagues::STATUS_JOINED) ||
                    !in_array($Colleague->colleague_permission, [UserColleagues::PERMISSION_EDIT, UserColleagues::PERMISSION_OWNER])) {
                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_COLLABORATION_ACCESS,
                        'info' => "Haven't access to change collaboration.",
                    ];
                }
            }
        }

        /* Проверка прав лицензии (для файла перемещаемого в другую папку) */
        /*
        $ret_lic_acc = self::checkLicenseAccess($UserNode, $parent);
        if (!$ret_lic_acc['access']) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_LICENSE_ACCESS,
                'info' => isset($ret_lic_acc['info'])
                    ? $ret_lic_acc['info']
                    : "Haven't access to move file in folder. Destination folder has another owner NodeId.",
            ];
        }
        */

        /* проверка что в папке назначения не существует файла с таким же именем */
        $checkExistsUserFile = UserFiles::findOne([
            'file_name' => $this->new_file_name,
            'file_parent_id' => $file_parent_id,
            'user_id' => $UserNode->user_id
        ]);
        if ($checkExistsUserFile) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "Folder or file with this name already exist in destination folder.",
                'error_data' => [
                    'file_hash' => $checkExistsUserFile->file_md5,
                    'file_md5'  => $checkExistsUserFile->file_md5,
                    'file_size' => $checkExistsUserFile->file_size,
                    'file_name' => $checkExistsUserFile->file_name,
                ],
            ];
        }

        /* проверка ошибки синхронизации по признаку last_event_id */
        /*
        $max_event_id = UserFileEvents::find()
            ->andWhere(['file_id' => $UserFile->file_id])
            ->max('event_id');
        if (intval($max_event_id) !== intval($this->last_event_id)) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info'    => "Synchronization conflict. (max_event_id={$max_event_id}; last_event_id={$this->last_event_id})"
            ];
        }
        */
        /** @var \common\models\UserFileEvents $maxUserFileEvent */
        $maxUserFileEvent = UserFileEvents::find()
            ->where(['file_id' => $UserFile->file_id])
            ->orderBy(['event_id' => SORT_DESC])
            ->limit(1)
            ->one();
        //var_dump($maxUserFileEvent->event_id); exit;
        if (!$maxUserFileEvent) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "Synchronization conflict.",
                'debug' => "Synchronization conflict. last_event_id for file {$UserFile->file_uuid} not found.",
            ];
        }
        if (intval($maxUserFileEvent->event_id) != intval($this->last_event_id)) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "Synchronization conflict.",
                'debug' => "Synchronization conflict. (max_event_id={$maxUserFileEvent->event_id}; last_event_id={$this->last_event_id})",
            ];
        }

        /* Данные по файлу до выполнения евента */
        $UserFile_OLD_collaboration_id = $UserFile->collaboration_id;

        /* Проверка того что можно выполнить евент согласно правил коллаборации
        Если перемещается из коллаборации или между ними, то это не перемещение а копи+делет */
        if (
            (!$UserFile_OLD_collaboration_id && $file_parent_collaboration_id) || // ++ Перемещение файла из папки без коллаборации в коллаборацию
            (!$file_parent_collaboration_id && $UserFile_OLD_collaboration_id) || // ++ Перемещение файла из коллаборации в папку без коллаборации
            ($UserFile_OLD_collaboration_id && $file_parent_collaboration_id && ($file_parent_collaboration_id != $UserFile_OLD_collaboration_id)) // ++ Перемещение файла между коллаборациями
        ) {
            /**
             * Возвращать в этом случае все ту же ошибку о том что нужно копи+дел вместо мув
             * но в то же время выполнять эту операцию (евент копи и затем евент мув)
             * при этом возможны три варианта развития:
             *
             * 1, Евент копи успешен, евент делет успешен (в идеале так должно быть всегда)
             * 2, евент копи еррор, значит евент делет не нужно выполнять уже
             * 3, евент копи успешен, евент делет ошибка
             */
            $transaction1 = Yii::$app->db->beginTransaction();

            /* нужно подменить ид ноды на фм при замене мув на копи+дел*/
            $UserNodeFM = self::registerNodeFM(Users::findIdentity($UserNode->user_id));

            $data['file_uuid'] = $UserFile->file_uuid;
            $data['folder_uuid'] = $file_parent_uuid;
            $data['file_name'] = $this->new_file_name;
            $data['file_size'] = $UserFile->file_size;

            $model = new NodeApi(['file_uuid', 'file_name', 'file_size']);
            if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
                $transaction1->rollBack();
                return [
                    'result' => "error",
                    'info' => "Load data error for model API",
                ];
            }
            $answer_copy = $model->file_event_copy(
                $UserNodeFM, //$UserNode, /* нужно подменить ид ноды на фм при замене мув на копи+дел*/
                $sendEventToSignal,
                $internalNodeFM,
                $check_collaboration_access,
                $send_collaboration_event
            );

            if ($answer_copy['result'] == "success") {
                $data2['last_event_id'] = UserFiles::last_event_id($UserFile->file_id);
                if (!$data2['last_event_id'] || $data2['last_event_id'] == 0) {
                    $transaction1->rollBack();
                    return [
                        'result' => "error",
                        'info' => "last_event_id error.",
                    ];
                }
                $data2['file_uuid']       = $UserFile->file_uuid;

                $model = new NodeApi(['file_uuid', 'last_event_id']);
                if (!$model->load(['NodeApi' => $data2]) || !$model->validate()) {
                    //var_dump($model->getErrors()); exit;
                    $transaction1->rollBack();
                    return [
                        'result' => "error",
                        'info' => "Load data error for model API",
                    ];
                }

                $answer_delete = $model->file_event_delete(
                    $UserNodeFM, //$UserNode, /* нужно подменить ид ноды на фм при замене мув на копи+дел*/
                    $sendEventToSignal,
                    $internalNodeFM,
                    $check_collaboration_access,
                    $send_collaboration_event
                );
                if ($answer_delete['result'] == "success") {
                    $transaction1->commit();
                    return [
                        'result'  => "success",
                        //'errcode' => self::ERROR_MOVE_PROHIBITED,
                        'info' => "move-event stored successfully",
                        'data' => [
                            'is_copy_del_instead_move' => true,
                            'event_id' => $answer_copy['data']['event_id'],
                            'event_uuid' => $answer_copy['data']['event_uuid'],
                            'timestamp' => $answer_copy['data']['timestamp'],
                            'file_uuid' => $answer_copy['data']['file_uuid'],
                            'diff_file_uuid' => $answer_copy['data']['diff_file_uuid'],
                            'rev_diff_file_uuid' => $answer_copy['data']['rev_diff_file_uuid'],
                            'file_name_before_event' => $answer_copy['data']['file_name_before_event'],
                            'file_name_after_event' => $answer_copy['data']['file_name_after_event'],
                            'file_size_before_event' => $answer_copy['data']['file_size_before_event'],
                            'file_size_after_event' => $answer_copy['data']['file_size_after_event'],
                            'file_hash_before_event' => $answer_copy['data']['file_hash_before_event'],
                            'file_hash' => $answer_copy['data']['file_hash'],
                        ],
                    ];
                } else {
                    $transaction1->rollBack();
                    return [
                        'result'  => "error",
                        'errcode' => self::ERROR_MOVE_PROHIBITED,
                        'info'    => "File move to outside of collaboration is prohibited. Use copy and delete instead",
                        'debug'   => $answer_delete,
                    ];
                }
            } else {
                $transaction1->rollBack();
                return [
                    'result'  => "error",
                    'errcode' => self::ERROR_MOVE_PROHIBITED,
                    'info'    => "File move to outside of collaboration is prohibited. Use copy and delete instead",
                    'debug'   => $answer_copy,
                ];
            }
        }

        /* проверка что если это коллаба, то она не должна быть залочена */
        if ($check_collaboration_access && $UserFile->collaboration_id) {
            $mutex_collaboration_name = 'collaboration_id_' . $UserFile->collaboration_id;
            if (!$this->mutex->acquire($mutex_collaboration_name, MUTEX_WAIT_TIMEOUT)) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_FS_TRY_LATER,
                    'info' => "Collaboration is locked now, try later please..",
                ];
            }
        }

        /* */
        try {
            /* Начинаем транзакцию */
            $transaction = Yii::$app->db->beginTransaction();
            $UserFileEvent = new UserFileEvents();
            $UserFileEvent->event_uuid = $this->event_uuid ? $this->event_uuid : self::uniq_uuid();
            $UserFileEvent->event_type = UserFileEvents::TYPE_MOVE;
            $UserFileEvent->event_timestamp = time();
            $UserFileEvent->last_event_id = $this->last_event_id;
            $UserFileEvent->file_id = $UserFile->file_id;
            $UserFileEvent->diff_file_uuid = $maxUserFileEvent->diff_file_uuid;
            $UserFileEvent->diff_file_size = $maxUserFileEvent->diff_file_size;
            $UserFileEvent->rev_diff_file_uuid = $maxUserFileEvent->rev_diff_file_uuid;
            $UserFileEvent->rev_diff_file_size = $maxUserFileEvent->rev_diff_file_size;
            $UserFileEvent->file_hash_before_event = $maxUserFileEvent->file_hash;
            $UserFileEvent->file_hash = $maxUserFileEvent->file_hash;
            $UserFileEvent->node_id = $UserNode->node_id;
            $UserFileEvent->user_id = $UserNode->user_id;
            $UserFileEvent->file_name_before_event = $UserFile->file_name;
            $UserFileEvent->file_name_after_event = $this->new_file_name;
            $UserFileEvent->file_size_before_event = $UserFile->file_size;
            $UserFileEvent->file_size_after_event = $UserFile->file_size;
            $UserFileEvent->parent_before_event = $UserFile->file_parent_id;
            $UserFileEvent->parent_after_event = $file_parent_id;
            $UserFileEvent->event_creator_user_id = (empty($this->event_creator_user_id)) ? $UserNode->user_id : $this->event_creator_user_id;
            $UserFileEvent->event_creator_node_id = (empty($this->event_creator_node_id)) ? $UserNode->node_id : $this->event_creator_node_id;

            if ($UserFileEvent->save()) {

                /* Подготовка инфы о юзере */
                $User = Users::getPathNodeFS($UserFile->user_id);

                /* обновление информации о размере родительской папки и количества файлов в ней (где был файл до перемещения) */
                if ($file_moved) {
                    UserFiles::update_parents_size_and_count(
                        $User,
                        $UserFile,
                        -1 * $UserFile->file_size,
                        -1
                    );
                }

                $_old_relativePath = UserFiles::getFullPath($UserFile);
                $UserFile->is_outdated = UserFiles::FILE_UNOUTDATED;
                $UserFile->file_name = $this->new_file_name;
                $UserFile->file_lastatime = time();
                //$UserFile->file_lastmtime = time();
                $UserFile->file_parent_id = $file_parent_id;
                $UserFile->last_event_type = UserFileEvents::TYPE_MOVE;
                $UserFile->last_event_uuid = $UserFileEvent->event_uuid;
                //$UserFile->last_event_id = $UserFileEvent->event_id;
                //$UserFile_OLD_collaboration_id = $UserFile->collaboration_id;
                $UserFile->collaboration_id = $file_parent_collaboration_id;
                /** @var $parent \common\models\UserFiles */
                if (!$UserFile->is_shared) {
                    $UserFile->share_group_hash = $parent ? $parent->share_group_hash : null;
                    $UserFile->share_lifetime = $parent ? $parent->share_lifetime : null;
                    $UserFile->share_ttl_info = $parent ? $parent->share_ttl_info : null;
                    $UserFile->share_created = $parent ? $parent->share_created : null;
                    $UserFile->share_password = $parent ? $parent->share_password : null;
                    $UserFile->share_group_hash ? $UserFile->generate_share_hash() : $UserFile->share_hash = null;
                }

                if ($UserFile->save()) {

                    /* Подготовка имени и полного пути файла а так же проверка на его максимальную длинну */
                    $_new_relativePath = UserFiles::getFullPath($UserFile);
                    $new_file_name = $User->_full_path . DIRECTORY_SEPARATOR . $_new_relativePath;
                    if (mb_strlen($new_file_name, '8bit') > UserFiles::FILE_PATH_MAX_LENGTH) {
                        $transaction->rollBack();

                        return [
                            'result'  => "error",
                            'errcode' => self::ERROR_FILE_PATH_MAX_LENGTH,
                            'info'    => "Path for folder or file is too long (more than " . UserFiles::FILE_PATH_MAX_LENGTH . " bytes).",
                        ];
                    }

                    /* обновление информации о размере родительской папки и количества файлов в ней (куда попал файл после перемещения) */
                    if ($file_moved) {
                        UserFiles::update_parents_size_and_count(
                            $User,
                            $UserFile,
                            $UserFile->file_size,
                            1
                        );
                    }

                    /* евенты для отправки на сигнальный */
                    $event_data[] = [
                        'operation' => "file_event",
                        'data' => [
                            'event_id' => $UserFileEvent->event_id,
                            'event_uuid' => $UserFileEvent->event_uuid,
                            'last_event_id' => $UserFileEvent->last_event_id,
                            'event_type' => UserFileEvents::getType($UserFileEvent->event_type),
                            'event_type_int' => $UserFileEvent->event_type,
                            'timestamp' => $UserFileEvent->event_timestamp,
                            'hash' => $UserFileEvent->file_hash,
                            'file_hash_before_event' => $UserFileEvent->file_hash_before_event,
                            'file_hash_after_event' => $UserFileEvent->file_hash,
                            'file_hash' => $UserFileEvent->file_hash,
                            'diff_file_uuid' => $UserFileEvent->diff_file_uuid,
                            'diff_file_size' => $UserFileEvent->diff_file_size,
                            'rev_diff_file_uuid' => $UserFileEvent->rev_diff_file_uuid,
                            'rev_diff_file_size' => $UserFileEvent->rev_diff_file_size,
                            'is_folder' => false,
                            'uuid' => $UserFile->file_uuid,
                            'file_id' => $UserFile->file_id,
                            'file_parent_id' => $UserFile->file_parent_id,
                            'file_parent_id_before_event' => $file_parent_id_before_event,
                            'file_name' => $UserFileEvent->file_name_after_event,
                            'file_name_before_event' => $UserFileEvent->file_name_before_event,
                            'file_size' => $UserFile->file_size,
                            'file_size_before_event' => $UserFileEvent->file_size_before_event,
                            'file_size_after_event' => $UserFileEvent->file_size_after_event,
                            'user_id' => $UserNode->user_id,
                            'node_id' => $UserNode->node_id,
                            'parent_folder_uuid' => $parent ? $parent->file_uuid : null,
                            'parent_folder_name' => $parent ? $parent->file_name : 'root',
                            'file_renamed' => $file_renamed,
                            'file_moved' => $file_moved,
                        ],
                    ];

                    /* Вызов самое себя в случае если имеется коллаборация по родительской папке */
                    if ($check_collaboration_access) {

                        /* Перемещение файла внутри коллаборации */
                        /* тут все предельно просто вызываем мтод перемещения как есть - самое себя */
                        if ($UserFile_OLD_collaboration_id == $file_parent_collaboration_id) {

                            $event_data_redis = [];
                            $UserColleagues = UserColleagues::find()
                                ->where([
                                    'collaboration_id' => $UserFile_OLD_collaboration_id,
                                    'colleague_status' => UserColleagues::STATUS_JOINED,
                                ])
                                //->andWhere('(user_id!=:user_id) AND (user_id IS NOT NULL)', [':user_id' => $UserNode->user_id])
                                ->andWhere('(user_id IS NOT NULL)')
                                ->all();
                            /** @var \common\models\UserColleagues $colleague */
                            foreach ($UserColleagues as $colleague) {

                                if ($colleague->user_id == $UserNode->user_id) {

                                    /* создаем репорт для овнера о том что сделал сам овнер */
                                    if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                        $UserData = Users::findIdentity($colleague->user_id);
                                        if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                            $Report = ColleaguesReports::createNewReport(
                                                $event_data[0],
                                                $colleague,
                                                $UserNode
                                            );
                                        }
                                    }

                                } else {
                                    $UserFile_FindID = UserFiles::findOne([
                                        'file_uuid' => $this->file_uuid,
                                        'is_folder' => UserFiles::TYPE_FILE,
                                        'user_id' => $colleague->user_id,
                                        'is_deleted' => UserFiles::FILE_UNDELETED,
                                    ]);
                                    //var_dump($UserFile_OLD_collaboration_id); exit;
                                    if ($UserFile_FindID) {
                                        $last_event_id = UserFileEvents::find()
                                            ->andWhere(['file_id' => $UserFile_FindID->file_id])
                                            ->max('event_id');

                                        $data['new_folder_uuid'] = $file_parent_uuid;
                                        $data['file_uuid'] = $this->file_uuid;
                                        $data['last_event_id'] = $last_event_id;
                                        $data['new_file_name'] = $this->new_file_name;
                                        $data['event_uuid'] = $UserFileEvent->event_uuid;
                                        $data['event_creator_user_id'] = $UserNode->user_id;
                                        $data['event_creator_node_id'] = $UserNode->node_id;

                                        $model = new NodeApi(['new_folder_uuid', 'file_uuid', 'last_event_id', 'new_file_name']);
                                        $model->load(['NodeApi' => $data]);
                                        $model->validate();
                                        $answer = $model->file_event_move(
                                            self::registerNodeFM(Users::findIdentity($colleague->user_id)),
                                            true,
                                            false,
                                            false
                                        );

                                        /* собираем евенты */
                                        if (isset($answer['event_data'])) {
                                            foreach ($answer['event_data'] as $k => $v) {
                                                $event_data_redis[] = $v;

                                                /* создаем репорт для овнера */
                                                if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                                    $UserData = Users::findIdentity($colleague->user_id);
                                                    if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                                        $Report = ColleaguesReports::createNewReport(
                                                            $v,
                                                            $colleague,
                                                            $UserNode
                                                        );
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            /* Отправка данных на вебсокет */
                            /* Отправляем в редис */
                            if (isset($event_data_redis) && sizeof($event_data_redis)) {
                                try {
                                    $this->redis->publish("collaboration:{$UserFile_OLD_collaboration_id}:fsevent", Json::encode($event_data_redis));
                                    $this->redis->save();
                                } catch (\Exception $e) {
                                }
                            }

                        }

                        /* Перемещение файла из папки коллаборации в папку без коллаборации */
                        /* Для участников коллаборации это должно выглядеть как обычное удаление этого файла */
                        if (!$file_parent_collaboration_id && $UserFile_OLD_collaboration_id) {

                            $event_data_redis = [];
                            $UserColleagues = UserColleagues::find()
                                ->where([
                                    'collaboration_id' => $UserFile_OLD_collaboration_id,
                                    'colleague_status' => UserColleagues::STATUS_JOINED,
                                ])
                                //->andWhere('(user_id!=:user_id) AND (user_id IS NOT NULL)', [':user_id' => $UserNode->user_id])
                                ->andWhere('(user_id IS NOT NULL)')
                                ->all();
                            /** @var \common\models\UserColleagues $colleague */
                            foreach ($UserColleagues as $colleague) {

                                if ($colleague->user_id == $UserNode->user_id) {

                                    /* создаем репорт для овнера о том что сделал сам овнер */
                                    if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                        $UserData = Users::findIdentity($colleague->user_id);
                                        if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                            $Report = ColleaguesReports::createNewReport(
                                                $event_data[0],
                                                $colleague,
                                                $UserNode
                                            );
                                        }
                                    }

                                } else {
                                    $UserFile_FindID = UserFiles::findOne([
                                        'file_uuid' => $this->file_uuid,
                                        'is_folder' => UserFiles::TYPE_FILE,
                                        'user_id' => $colleague->user_id,
                                        'is_deleted' => UserFiles::FILE_UNDELETED,
                                    ]);
                                    //var_dump($UserFile_OLD_collaboration_id); exit;
                                    if ($UserFile_FindID) {
                                        $last_event_id = UserFileEvents::find()
                                            ->andWhere(['file_id' => $UserFile_FindID->file_id])
                                            ->max('event_id');


                                        $data['file_uuid'] = $this->file_uuid;
                                        $data['last_event_id'] = $last_event_id;
                                        $data['event_creator_user_id'] = $UserNode->user_id;
                                        $data['event_creator_node_id'] = $UserNode->node_id;

                                        $model = new NodeApi(['file_uuid', 'last_event_id']);
                                        $model->load(['NodeApi' => $data]);
                                        $model->validate();
                                        $answer = $model->file_event_delete(
                                            self::registerNodeFM(Users::findIdentity($colleague->user_id)),
                                            true,
                                            false,
                                            false
                                        );

                                        /* собираем евенты */
                                        if (isset($answer['event_data'])) {
                                            foreach ($answer['event_data'] as $k => $v) {
                                                $event_data_redis[] = $v;

                                                /* создаем репорт для овнера */
                                                if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                                    $UserData = Users::findIdentity($colleague->user_id);
                                                    if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                                        $Report = ColleaguesReports::createNewReport(
                                                            $v,
                                                            $colleague,
                                                            $UserNode
                                                        );
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            /* Отправка данных на вебсокет */
                            /* Отправляем в редис */
                            if (isset($event_data_redis) && sizeof($event_data_redis)) {
                                try {
                                    $this->redis->publish("collaboration:{$UserFile_OLD_collaboration_id}:fsevent", Json::encode($event_data_redis));
                                    $this->redis->save();
                                } catch (\Exception $e) {
                                }
                            }

                        }

                        /* Перемещение файла из вне в папку коллаборации */
                        /* Для участников коллаборации это должно выглядеть как создание копии этого файла */
                        if (!$UserFile_OLD_collaboration_id && $file_parent_collaboration_id) {

                            $event_data_redis = [];
                            $UserColleagues = UserColleagues::find()
                                ->where([
                                    'collaboration_id' => $file_parent_collaboration_id,
                                    'colleague_status' => UserColleagues::STATUS_JOINED,
                                ])
                                //->andWhere('(user_id!=:user_id) AND (user_id IS NOT NULL)', [':user_id' => $UserNode->user_id])
                                ->andWhere('(user_id IS NOT NULL)')
                                ->all();
                            /** @var \common\models\UserColleagues $colleague */
                            foreach ($UserColleagues as $colleague) {

                                if ($colleague->user_id == $UserNode->user_id) {

                                    /* создаем репорт для овнера о том что сделал сам овнер */
                                    if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                        $UserData = Users::findIdentity($colleague->user_id);
                                        if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                            $Report = ColleaguesReports::createNewReport(
                                                $event_data[0],
                                                $colleague,
                                                $UserNode
                                            );
                                        }
                                    }

                                } else {
                                    $data['copy_file_uuid'] = $this->file_uuid;
                                    $data['file_uuid'] = $this->file_uuid;
                                    $data['folder_uuid'] = $file_parent_uuid;
                                    $data['file_name'] = $UserFile->file_name;
                                    $data['file_size'] = $UserFile->file_size;
                                    $data['event_uuid'] = $UserFileEvent->event_uuid;
                                    $data['collaboration_id'] = $file_parent_collaboration_id;
                                    $data['event_creator_user_id'] = $UserNode->user_id;
                                    $data['event_creator_node_id'] = $UserNode->node_id;

                                    $model = new NodeApi(['copy_file_uuid', 'file_uuid', 'file_name', 'file_size']);
                                    $model->load(['NodeApi' => $data]);
                                    $model->validate();
                                    $answer = $model->file_event_copy(
                                        self::registerNodeFM(Users::findIdentity($colleague->user_id)),
                                        true,
                                        false,
                                        false
                                    );
                                    //var_dump($answer); exit;

                                    /* собираем евенты */
                                    if (isset($answer['event_data'])) {
                                        foreach ($answer['event_data'] as $k => $v) {
                                            $event_data_redis[] = $v;

                                            /* создаем репорт для овнера */
                                            if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                                $UserData = Users::findIdentity($colleague->user_id);
                                                if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                                    $Report = ColleaguesReports::createNewReport(
                                                        $v,
                                                        $colleague,
                                                        $UserNode
                                                    );
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            /* Отправка данных на вебсокет */
                            /* Отправляем в редис */
                            if (isset($event_data_redis) && sizeof($event_data_redis)) {
                                try {
                                    $this->redis->publish("collaboration:{$file_parent_collaboration_id}:fsevent", Json::encode($event_data_redis));
                                    $this->redis->save();
                                } catch (\Exception $e) {
                                }
                            }
                        }

                        /* Перемещение файла между коллаборациями */
                        if ($UserFile_OLD_collaboration_id && $file_parent_collaboration_id && ($file_parent_collaboration_id != $UserFile_OLD_collaboration_id)) {
                            /* все сложно ))) */

                            /* внутри бывшей коллаборации файл должен быть удален */
                            $event_data_redis_1 = [];
                            $UserColleagues = UserColleagues::find()
                                ->where([
                                    'collaboration_id' => $UserFile_OLD_collaboration_id,
                                    'colleague_status' => UserColleagues::STATUS_JOINED,
                                ])
                                //->andWhere('(user_id!=:user_id) AND (user_id IS NOT NULL)', [':user_id' => $UserNode->user_id])
                                ->andWhere('(user_id IS NOT NULL)')
                                ->all();
                            /** @var \common\models\UserColleagues $colleague */
                            foreach ($UserColleagues as $colleague) {

                                if ($colleague->user_id == $UserNode->user_id) {

                                    /* создаем репорт для овнера о том что сделал сам овнер */
                                    if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                        $UserData = Users::findIdentity($colleague->user_id);
                                        if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                            $Report = ColleaguesReports::createNewReport(
                                                $event_data[0],
                                                $colleague,
                                                $UserNode
                                            );
                                        }
                                    }

                                } else {
                                    $UserFile_FindID = UserFiles::findOne([
                                        'file_uuid' => $this->file_uuid,
                                        'is_folder' => UserFiles::TYPE_FILE,
                                        'user_id' => $colleague->user_id,
                                        'is_deleted' => UserFiles::FILE_UNDELETED,
                                    ]);
                                    if ($UserFile_FindID) {
                                        $last_event_id = UserFileEvents::find()
                                            ->andWhere(['file_id' => $UserFile_FindID->file_id])
                                            ->max('event_id');

                                        $data['file_uuid'] = $this->file_uuid;
                                        $data['last_event_id'] = $last_event_id;
                                        $data['event_creator_user_id'] = $UserNode->user_id;
                                        $data['event_creator_node_id'] = $UserNode->node_id;

                                        $model = new NodeApi(['file_uuid', 'last_event_id']);
                                        $model->load(['NodeApi' => $data]);
                                        $model->validate();
                                        $answer = $model->file_event_delete(
                                            self::registerNodeFM(Users::findIdentity($colleague->user_id)),
                                            true,
                                            false,
                                            false
                                        );

                                        /* собираем евенты */
                                        if (isset($answer['event_data'])) {
                                            foreach ($answer['event_data'] as $k => $v) {
                                                $event_data_redis_1[] = $v;

                                                /* создаем репорт для овнера */
                                                if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                                    $UserData = Users::findIdentity($colleague->user_id);
                                                    if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                                        $Report = ColleaguesReports::createNewReport(
                                                            $v,
                                                            $colleague,
                                                            $UserNode
                                                        );
                                                    }
                                                }
                                            }
                                        }

                                    }
                                }
                            }

                            /* Отправка данных на вебсокет */
                            /* Отправляем в редис */
                            if (isset($event_data_redis_1) && sizeof($event_data_redis_1)) {
                                try {
                                    $this->redis->publish("collaboration:{$UserFile_OLD_collaboration_id}:fsevent", Json::encode($event_data_redis_1));
                                    $this->redis->save();
                                } catch (\Exception $e) {
                                }
                            }


                            /* внутри новой коллаборации файл должен быть создан */
                            $event_data_redis_2 = [];
                            $UserColleagues = UserColleagues::find()
                                ->where([
                                    'collaboration_id' => $file_parent_collaboration_id,
                                    'colleague_status' => UserColleagues::STATUS_JOINED,
                                ])
                                //->andWhere('(user_id!=:user_id) AND (user_id IS NOT NULL)', [':user_id' => $UserNode->user_id])
                                ->andWhere('(user_id IS NOT NULL)')
                                ->all();
                            /** @var \common\models\UserColleagues $colleague */
                            foreach ($UserColleagues as $colleague) {

                                if ($colleague->user_id == $UserNode->user_id) {

                                    /* создаем репорт для овнера о том что сделал сам овнер */
                                    if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                        $UserData = Users::findIdentity($colleague->user_id);
                                        if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                            $Report = ColleaguesReports::createNewReport(
                                                $event_data[0],
                                                $colleague,
                                                $UserNode
                                            );
                                        }
                                    }

                                } else {
                                    $data['copy_file_uuid'] = $this->file_uuid;
                                    $data['file_uuid'] = $this->file_uuid;
                                    $data['folder_uuid'] = $file_parent_uuid;
                                    $data['file_name'] = $UserFile->file_name;
                                    $data['file_size'] = $UserFile->file_size;
                                    $data['event_uuid'] = $UserFileEvent->event_uuid;
                                    $data['collaboration_id'] = $file_parent_collaboration_id;
                                    $data['event_creator_user_id'] = $UserNode->user_id;
                                    $data['event_creator_node_id'] = $UserNode->node_id;

                                    $model = new NodeApi(['copy_file_uuid', 'file_uuid', 'file_name', 'file_size']);
                                    $model->load(['NodeApi' => $data]);
                                    $model->validate();
                                    $answer2 = $model->file_event_copy(
                                        self::registerNodeFM(Users::findIdentity($colleague->user_id)),
                                        true,
                                        false,
                                        false
                                    );
                                    //var_dump($answer); exit;

                                    /* собираем евенты */
                                    if (isset($answer2['event_data'])) {
                                        foreach ($answer2['event_data'] as $k => $v) {
                                            $event_data_redis_2[] = $v;

                                            /* создаем репорт для овнера */
                                            if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                                $UserData = Users::findIdentity($colleague->user_id);
                                                if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                                    $Report = ColleaguesReports::createNewReport(
                                                        $v,
                                                        $colleague,
                                                        $UserNode
                                                    );
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            /* Отправка данных на вебсокет */
                            /* Отправляем в редис */
                            if (isset($event_data_redis_2) && sizeof($event_data_redis_2)) {
                                try {
                                    $this->redis->publish("collaboration:{$file_parent_collaboration_id}:fsevent", Json::encode($event_data_redis_2));
                                    $this->redis->save();
                                } catch (\Exception $e) {
                                }
                            }

                        }

                    }

                    /* успешное завершение транзакции */
                    $transaction->commit();

                    /* Создание файла со служебной инфой для отображения в ФС вебФМ */
                    //$_new_relativePath = UserFiles::getFullPath($UserFile);
                    if (!$internalNodeFM) {
                        $old_file_name = $User->_full_path . DIRECTORY_SEPARATOR . $_old_relativePath;
                        $new_file_name = $User->_full_path . DIRECTORY_SEPARATOR . $_new_relativePath;
                        if (file_exists($old_file_name) && !file_exists($new_file_name)) {
                            FileSys::mkdir(dirname($new_file_name), UserFiles::CHMOD_DIR, true);
                            FileSys::move($old_file_name, $new_file_name);
                        }
                        UserFiles::createFileInfo($new_file_name, $UserFile);
                    }

                    $ret = [
                        'result' => "success",
                        'info' => "move-event stored successfully",
                        'data' => [
                            'event_id' => $UserFileEvent->event_id,
                            'event_uuid' => $UserFileEvent->event_uuid,
                            'timestamp' => $UserFileEvent->event_timestamp,
                            'diff_file_uuid' => $UserFileEvent->diff_file_uuid,
                            'rev_diff_file_uuid' => $UserFileEvent->rev_diff_file_uuid,
                            'file_name_before_event' => $UserFileEvent->file_name_before_event,
                            'file_name_after_event' => $UserFileEvent->file_name_after_event,
                            'file_size_before_event' => $UserFileEvent->file_size_before_event,
                            'file_size_after_event' => $UserFileEvent->file_size_after_event,
                            'file_hash_before_event' => $UserFileEvent->file_hash_before_event,
                            'file_hash' => $UserFileEvent->file_hash,
                        ],
                    ];

                    /* Отправка евента на редис */
                    if (!in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT])) {
                        try {
                            $this->redis->publish("user:{$UserNode->user_id}:fs_events", Json::encode($event_data));
                            $this->redis->save();
                        } catch (\Exception $e) {
                            RedisSafe::createNewRecord(
                                RedisSafe::TYPE_FS_EVENTS,
                                $UserNode->user_id,
                                null,
                                Json::encode([
                                    'action'           => 'fs_events',
                                    'chanel'           => "user:{$UserNode->user_id}:fs_events",
                                    'user_id'          => $UserNode->user_id,
                                ])
                            );
                        }
                    }

                    /* Подготовка данных об евенте в общий массив возврата, если нужно */
                    if ($sendEventToSignal) {
                        $ret['event_data'] = $event_data;
                    }

                    /* Освобождаем от блокировки по мутексу, нужно для ФМ,
                     * который за один проход скрипта может делать несколько евентов цикле,
                     * а иначе он не может например удалить сразу две папки*/
                    $this->mutex->release($mutex_name);
                    if (isset($mutex_collaboration_name)) { $this->mutex->release($mutex_collaboration_name); }

                    return $ret;
                } else {
                    $transaction->rollBack();

                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_DATABASE_FAILURE,
                        'info' => "An internal server error occurred.",
                        'debug' => $UserFile->getErrors()
                    ];
                }
            } else {
                $transaction->rollBack();

                return [
                    'result' => "error",
                    'errcode' => self::ERROR_DATABASE_FAILURE,
                    'info' => "An internal server error occurred.",
                    'debug' => $UserFileEvent->getErrors()
                ];
            }
        } catch (\Exception $e) {

            return [
                'result' => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $e,
            ];
        }
    }

    /**
     * Метод для регистрации события folder create
     * @param \common\models\UserNode $UserNode
     * @param bool $internalNodeFM
     * @param bool $sendEventToSignal
     * @param bool $check_collaboration_access
     * @param bool $send_collaboration_event
     * @return array
     */
    public function folder_event_create($UserNode, $sendEventToSignal = false, $internalNodeFM = false, $check_collaboration_access = true, $send_collaboration_event = false)
    {
        /* Проверим нет ли лока на выполнение ФС действий вследствие друхих действий этого юзера */
        $mutex_name = 'user_id_' . $UserNode->user_id;
        if (!$this->mutex->acquire($mutex_name, MUTEX_WAIT_TIMEOUT)) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_TRY_LATER,
                'info' => "File or folder is locked now, try later please.",
            ];
        }

        /* Проверим, есть ли уже в бд евент с таким event_uuid если есть - это повтор и выполнять ничего не нужно. Просто отдать ответ */
        $check_event = $this->check_is_event_exist($UserNode->user_id);
        if ($check_event) {
            return $check_event;
        }

        /* подготовка параметра */
        if (!$this->parent_folder_uuid) {
            $this->parent_folder_uuid = "";
        }

        /* Поиск родительского элемента для новой папки */
        if (mb_strlen($this->parent_folder_uuid)) {
            $parent = UserFiles::findOne([
                'file_uuid' => $this->parent_folder_uuid,
                'is_folder' => UserFiles::TYPE_FOLDER,
                'user_id' => $UserNode->user_id,
                'is_deleted' => UserFiles::FILE_UNDELETED,
            ]);
            if (!$parent) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_FS_SYNC_PARENT_NOT_FOUND,
                    'info' => "Parent folder with folder_uuid='{$this->parent_folder_uuid}' does not exist.",
                    'debug' => "Parent folder with folder_uuid='{$this->parent_folder_uuid}' does not exist.",
                ];
            }
            $file_parent_id = $parent->file_id;
            $file_parent_uuid = $parent->file_uuid;
            if ($parent->collaboration_id) {
                $this->collaboration_id = $parent->collaboration_id;
            }
        } else {
            $parent = null;
            $file_parent_id = UserFiles::ROOT_PARENT_ID;
            $file_parent_uuid = null;
        }

        /* проверка прав коллаборации */
        if ($check_collaboration_access) {
            if ($this->collaboration_id) {
                $Colleague = UserColleagues::findOne([
                    'user_id' => $UserNode->user_id,
                    'collaboration_id' => $this->collaboration_id,
                ]);
                //if (!in_array($Colleague->colleague_permission, [UserColleagues::PERMISSION_EDIT, UserColleagues::PERMISSION_OWNER])) {
                if (!$Colleague ||
                    ($Colleague->colleague_status != UserColleagues::STATUS_JOINED) ||
                    !in_array($Colleague->colleague_permission, [UserColleagues::PERMISSION_EDIT, UserColleagues::PERMISSION_OWNER])) {
                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_COLLABORATION_ACCESS,
                        'info' => "Haven't access to change collaboration.",
                    ];
                }
            }
        }

        /* Проверка прав лицензии */
        if ($check_collaboration_access) {
            $ret_lic_acc = self::checkLicenseAccess($UserNode, $parent);
            if (!$ret_lic_acc['access']) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_LICENSE_ACCESS,
                    'info' => isset($ret_lic_acc['info'])
                        ? $ret_lic_acc['info']
                        : "Haven't access to create folder in this folder. It has another owner NodeId.",
                ];
            }
        }

        /* Проверка что такой папки еще не существует тут */
        $checkExistsUserFile = UserFiles::findOne([
            'file_name' => $this->folder_name,
            'file_parent_id' => $file_parent_id,
            'user_id' => $UserNode->user_id
        ]);
        if ($checkExistsUserFile) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => 'Folder or file with this name already exist.',
                'error_data' => [
                    'file_hash' => $checkExistsUserFile->file_md5,
                    'file_md5'  => $checkExistsUserFile->file_md5,
                    'file_size' => $checkExistsUserFile->file_size,
                    'file_name' => $checkExistsUserFile->file_name,
                ],
            ];
        }

        /* проверка что если это коллаба, то она не должна быть залочена */
        if ($check_collaboration_access && $this->collaboration_id) {
            $mutex_collaboration_name = 'collaboration_id_' . $this->collaboration_id;
            if (!$this->mutex->acquire($mutex_collaboration_name, MUTEX_WAIT_TIMEOUT)) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_FS_TRY_LATER,
                    'info' => "Collaboration is locked now, try later please..",
                ];
            }
        }

        try {
            /* Начинаем транзакцию */
            $transaction = Yii::$app->db->beginTransaction();

            /* Подготовка инфы о юзере */
            $User = Users::getPathNodeFS($UserNode->user_id);

            /* Создание новой записи о файле */
            $UserFile = New UserFiles();
            $UserFile->file_parent_id = $file_parent_id;
            $UserFile->file_uuid = ($this->copy_folder_uuid) ? $this->copy_folder_uuid : self::uniq_uuid();
            $UserFile->file_name = $this->folder_name;
            $UserFile->file_lastatime = time();
            $UserFile->file_lastmtime = time();
            $UserFile->is_deleted = UserFiles::FILE_UNDELETED;
            $UserFile->is_outdated = UserFiles::FILE_UNOUTDATED;
            $UserFile->is_folder = UserFiles::TYPE_FOLDER;
            $UserFile->last_event_type = UserFileEvents::TYPE_CREATE;
            $UserFile->last_event_uuid = $this->event_uuid ? $this->event_uuid : self::uniq_uuid();
            $UserFile->diff_file_uuid = null;
            $UserFile->user_id = $UserNode->user_id;
            $UserFile->node_id = $UserNode->node_id;
            /** @var $parent \common\models\UserFiles */
            $UserFile->collaboration_id = $this->collaboration_id ? $this->collaboration_id : null;
            $UserFile->is_collaborated = $this->is_collaborated ? $this->is_collaborated : UserFiles::FILE_UNCOLLABORATED;
            $UserFile->is_owner = isset($this->is_owner) ? $this->is_owner : UserFiles::IS_OWNER;
            $UserFile->is_shared = UserFiles::FILE_UNSHARED;
            $UserFile->share_group_hash = $parent ? $parent->share_group_hash : null;
            $UserFile->share_lifetime = $parent ? $parent->share_lifetime : null;
            $UserFile->share_ttl_info = $parent ? $parent->share_ttl_info : null;
            $UserFile->share_created = $parent ? $parent->share_created : null;
            $UserFile->share_password = $parent ? $parent->share_password : null;
            $UserFile->share_group_hash ? $UserFile->generate_share_hash() : $UserFile->share_hash = null;

            if ($UserFile->save()) {
                /* создание новой записи о евенте */
                $UserFileEvent = new UserFileEvents();
                $UserFileEvent->event_uuid = $UserFile->last_event_uuid;
                $UserFileEvent->event_type = UserFileEvents::TYPE_CREATE;
                $UserFileEvent->event_timestamp = time();
                $UserFileEvent->last_event_id = 0;
                $UserFileEvent->file_id = $UserFile->file_id;
                $UserFileEvent->diff_file_uuid = null;
                $UserFileEvent->diff_file_size = 0;
                $UserFileEvent->rev_diff_file_uuid = null;
                $UserFileEvent->rev_diff_file_size = 0;
                $UserFileEvent->file_hash_before_event = null;
                $UserFileEvent->file_hash = null;
                $UserFileEvent->node_id = $UserNode->node_id;
                $UserFileEvent->user_id = $UserNode->user_id;
                $UserFileEvent->file_name_before_event = '';
                $UserFileEvent->file_name_after_event = $UserFile->file_name;
                $UserFileEvent->file_size_before_event = 0;
                $UserFileEvent->file_size_after_event = 0;
                $UserFileEvent->parent_before_event = $UserFile->file_parent_id;
                $UserFileEvent->parent_after_event = $UserFile->file_parent_id;
                $UserFileEvent->event_creator_user_id = (empty($this->event_creator_user_id)) ? $UserNode->user_id : $this->event_creator_user_id;
                $UserFileEvent->event_creator_node_id = (empty($this->event_creator_node_id)) ? $UserNode->node_id : $this->event_creator_node_id;

                if ($UserFileEvent->save()) {

                    /* Подготовка имени и полного пути файла а так же проверка на его максимальную длинну */
                    $relativePath = UserFiles::getFullPath($UserFile);
                    $folder_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;
                    if (mb_strlen($folder_name . DIRECTORY_SEPARATOR . UserFiles::DIR_INFO_FILE, '8bit') > UserFiles::FILE_PATH_MAX_LENGTH) {
                        $transaction->rollBack();

                        return [
                            'result'  => "error",
                            'errcode' => self::ERROR_FILE_PATH_MAX_LENGTH,
                            'info'    => "Path for folder or file is too long (more than " . UserFiles::FILE_PATH_MAX_LENGTH . " bytes).",
                        ];
                    }

                    $event_data[] = [
                        'operation' => "file_event",
                        'data' => [
                            'event_id' => $UserFileEvent->event_id,
                            'event_uuid' => $UserFileEvent->event_uuid,
                            'last_event_id' => $UserFileEvent->last_event_id,
                            'event_type' => UserFileEvents::getType($UserFileEvent->event_type),
                            'event_type_int' => $UserFileEvent->event_type,
                            'timestamp' => $UserFileEvent->event_timestamp,
                            'hash' => $UserFileEvent->file_hash,
                            'file_hash_before_event' => $UserFileEvent->file_hash_before_event,
                            'file_hash_after_event' => $UserFileEvent->file_hash,
                            'file_hash' => $UserFileEvent->file_hash,
                            'diff_file_uuid' => $UserFileEvent->diff_file_uuid,
                            'diff_file_size' => $UserFileEvent->diff_file_size,
                            'rev_diff_file_uuid' => $UserFileEvent->rev_diff_file_uuid,
                            'rev_diff_file_size' => $UserFileEvent->rev_diff_file_size,
                            'is_folder' => true,
                            'uuid' => $UserFile->file_uuid,
                            'file_id' => $UserFile->file_id,
                            'file_parent_id' => $UserFile->file_parent_id,
                            'file_name' => $UserFile->file_name,
                            'file_size' => 0,
                            'user_id' => $UserNode->user_id,
                            'node_id' => $UserNode->node_id,
                            'parent_folder_uuid' => $parent ? $parent->file_uuid : null,
                        ],
                    ];

                    /* Вызов самое себя в случае если имеется коллаборация по родительской папке */
                    if ($check_collaboration_access) {
                        if ($this->collaboration_id && $file_parent_uuid) {
                            $UserColleagues = UserColleagues::find()
                                ->where([
                                    'collaboration_id' => $this->collaboration_id,
                                    'colleague_status' => UserColleagues::STATUS_JOINED,
                                ])
                                //->andWhere('(user_id!=:user_id) AND (user_id IS NOT NULL)', [':user_id' => $UserNode->user_id])
                                ->andWhere('(user_id IS NOT NULL)')
                                ->all();
                            /** @var \common\models\UserColleagues $colleague */
                            foreach ($UserColleagues as $colleague) {

                                if ($colleague->user_id == $UserNode->user_id) {

                                    /* создаем репорт для овнера о том что сделал сам овнер */
                                    if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                        $UserData = Users::findIdentity($colleague->user_id);
                                        if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                            $Report = ColleaguesReports::createNewReport(
                                                $event_data[0],
                                                $colleague,
                                                $UserNode
                                            );
                                        }
                                    }

                                } else {
                                    $data['copy_folder_uuid'] = $UserFile->file_uuid;
                                    $data['parent_folder_uuid'] = $file_parent_uuid;
                                    $data['folder_name'] = $UserFile->file_name;
                                    $data['collaboration_id'] = $this->collaboration_id;
                                    $data['is_owner'] = UserFiles::IS_COLLEAGUE;
                                    $data['event_creator_user_id'] = $UserNode->user_id;
                                    $data['event_creator_node_id'] = $UserNode->node_id;

                                    $model = new NodeApi(['folder_name']);
                                    $model->load(['NodeApi' => $data]);
                                    $model->validate();
                                    $answer = $model->folder_event_create(
                                        self::registerNodeFM(Users::findIdentity($colleague->user_id)),
                                        true,
                                        false,
                                        false
                                    );

                                    /* собираем евенты */
                                    if (isset($answer['event_data'])) {
                                        foreach ($answer['event_data'] as $k => $v) {
                                            $event_data_redis[] = $v;

                                            /* создаем репорт для овнера */
                                            if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                                $UserData = Users::findIdentity($colleague->user_id);
                                                if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                                    $Report = ColleaguesReports::createNewReport(
                                                        $v,
                                                        $colleague,
                                                        $UserNode
                                                    );
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            /* Отправка данных на вебсокет */
                            /* Отправляем в редис */
                            if (isset($event_data_redis)) {
                                try {
                                    $this->redis->publish("collaboration:{$this->collaboration_id}:fsevent", Json::encode($event_data_redis));
                                    $this->redis->save();
                                } catch (\Exception $e) {
                                }
                            }

                        }
                    }

                    /* успешное завершение транзакции */
                    $transaction->commit();

                    /* Создание файла со служебной инфой для отображения в ФС вебФМ */
                    if (!$internalNodeFM) {
                        //$relativePath = UserFiles::getFullPath($UserFile);
                        $folder_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;
                        if (!file_exists($folder_name)) {
                            FileSys::mkdir($folder_name, UserFiles::CHMOD_DIR, true);
                        }
                        $folder_info_file = $folder_name . DIRECTORY_SEPARATOR . UserFiles::DIR_INFO_FILE;
                        if (!file_exists($folder_info_file)) {
                            FileSys::touch($folder_info_file, UserFiles::CHMOD_DIR, UserFiles::CHMOD_FILE);
                            UserFiles::createFileInfo($folder_info_file, $UserFile);
                        }
                    }

                    $ret = [
                        'result' => "success",
                        'info' => "create-event stored successfully",
                        'data' => [
                            'event_id' => $UserFileEvent->event_id,
                            'event_uuid' => $UserFileEvent->event_uuid,
                            'folder_uuid' => $UserFile->file_uuid,
                            'timestamp' => $UserFileEvent->event_timestamp,
                        ],
                    ];

                    /* Отправка евента на редис */
                    if (!in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT])) {
                        try {
                            $this->redis->publish("user:{$UserNode->user_id}:fs_events", Json::encode($event_data));
                            $this->redis->save();
                        } catch (\Exception $e) {
                            RedisSafe::createNewRecord(
                                RedisSafe::TYPE_FS_EVENTS,
                                $UserNode->user_id,
                                null,
                                Json::encode([
                                    'action'           => 'fs_events',
                                    'chanel'           => "user:{$UserNode->user_id}:fs_events",
                                    'user_id'          => $UserNode->user_id,
                                ])
                            );
                        }
                    }

                    /* Подготовка данных об евенте в общий массив возврата, если нужно */
                    if ($sendEventToSignal) {
                        $ret['event_data'] = $event_data;
                    }

                    /* Освобождаем от блокировки по мутексу, нужно для ФМ,
                     * который за один проход скрипта может делать несколько евентов цикле,
                     * а иначе он не может например удалить сразу две папки*/
                    $this->mutex->release($mutex_name);
                    if (isset($mutex_collaboration_name)) { $this->mutex->release($mutex_collaboration_name); }

                    return $ret;
                } else {
                    $transaction->rollBack();

                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_DATABASE_FAILURE,
                        'info' => "An internal server error occurred.",
                        'debug' => $UserFileEvent->getErrors()
                    ];
                }
            } else {
                $transaction->rollBack();

                return [
                    'result' => "error",
                    'errcode' => self::ERROR_DATABASE_FAILURE,
                    'info' => "An internal server error occurred.",
                    'debug' => $UserFile->getErrors(),
                ];
            }
        } catch (\Exception $e) {

            return [
                'result' => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $e,
            ];
        }
    }

    /**
     * Метод для регистрации события folder delete
     * @param \common\models\UserNode $UserNode
     * @param bool $internalNodeFM
     * @param bool $check_collaboration_access
     * @param bool $sendEventToSignal
     * @param bool $send_collaboration_event
     * @return array
     */
    public function folder_event_delete($UserNode, $sendEventToSignal = false, $internalNodeFM = false, $check_collaboration_access = true, $send_collaboration_event = false)
    {
        /* Проверим нет ли лока на выполнение ФС действий вследствие друхих действий этого юзера */
        $mutex_name = 'user_id_' . $UserNode->user_id;
        if (!$this->mutex->acquire($mutex_name, MUTEX_WAIT_TIMEOUT)) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_TRY_LATER,
                'info' => "File or folder is locked now, try later please.",
            ];
        }

        /* Проверим, есть ли уже в бд евент с таким event_uuid если есть - это повтор и выполнять ничего не нужно. Просто отдать ответ */
        $check_event = $this->check_is_event_exist($UserNode->user_id);
        if ($check_event) {
            return $check_event;
        }

        /* находим нужный файл по ууид */
        $UserFile = UserFiles::findOne([
            'file_uuid' => $this->folder_uuid,
            'is_folder' => UserFiles::TYPE_FOLDER,
            'user_id' => $UserNode->user_id,
            'is_deleted' => UserFiles::FILE_UNDELETED,
        ]);

        /* проверка существования исходной папки */
        if (!$UserFile) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC_NOT_FOUND,
                'info' => "Folder not found.",
            ];
        }

        /* проверка прав коллаборации */
        if ($check_collaboration_access) {
            if ($UserFile->collaboration_id && !$UserFile->is_collaborated) {
                $Colleague = UserColleagues::findOne([
                    'user_id' => $UserNode->user_id,
                    'collaboration_id' => $UserFile->collaboration_id,
                ]);
                //if (!in_array($Colleague->colleague_permission, [UserColleagues::PERMISSION_EDIT, UserColleagues::PERMISSION_OWNER])) {
                if (!$Colleague ||
                    ($Colleague->colleague_status != UserColleagues::STATUS_JOINED) ||
                    !in_array($Colleague->colleague_permission, [UserColleagues::PERMISSION_EDIT, UserColleagues::PERMISSION_OWNER])) {
                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_COLLABORATION_ACCESS,
                        'info' => "Haven't access to change collaboration.",
                    ];
                }
            }
        }

        /* Проверка прав лицензии */
        if ($check_collaboration_access) {
            $ret_lic_acc = self::checkLicenseAccess($UserNode, $UserFile);
            if (!$ret_lic_acc['access']) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_LICENSE_ACCESS,
                    'info' => isset($ret_lic_acc['info'])
                        ? $ret_lic_acc['info']
                        : "Haven't access to delete folder. It has another owner NodeId.",
                ];
            }
        }

        /* проверка что папку не удаили до этого */
        /* учитывая предыдущие проверки этот код не будет исполнен, и его удаляем */
//        if ($UserFile->last_event_type == UserFileEvents::TYPE_DELETE) {
//            return [
//                'result' => "error",
//                'errcode' => self::ERROR_FS_SYNC,
//                'info' => "Folder already was deleted. You can't do any actions with this folder.",
//            ];
//        }

        /* проверка ошибки синхронизации по признаку last_event_id */
        $max_event_id = UserFileEvents::find()
            ->andWhere(['file_id' => $UserFile->file_id])
            ->max('event_id');
        if (intval($max_event_id) !== intval($this->last_event_id)) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "Synchronization conflict.",
                'debug' => "Synchronization conflict. (max_event_id={$max_event_id}; last_event_id={$this->last_event_id})",
            ];
        }

        /* Поиск родителя */
        /** @var \common\models\UserFiles $Parent */
        if ($UserFile->file_parent_id) {
            $Parent = UserFiles::findOne(['file_id' => $UserFile->file_parent_id]);
        } else {
            $Parent = null;
        }

        /* если это корневая папка коллаборации */
        if (!$this->is_from_colleagueDelete) {
            if ($UserFile->is_collaborated) {
                $UserCollaboration = UserCollaborations::findOne(['collaboration_id' => $UserFile->collaboration_id]);
                if ($UserCollaboration) {

                    /* если это владелец коллаборации */
                    if ($UserFile->user_id == $UserCollaboration->user_id) {
                        /* тут модуль удаления каждого коллеги и самого владельца в случае если владелец удаляет свлю папку коллаборации */
                        $UserColleagues = UserColleagues::find()
                            ->where(['collaboration_id' => $UserCollaboration->collaboration_id])
                            ->andWhere('(user_id!=:user_id) AND (user_id IS NOT NULL)', [':user_id' => $UserCollaboration->user_id])
                            //->andWhere('(user_id IS NOT NULL)')
                            ->all();
                        /** @var \common\models\UserColleagues $colleague */
                        $collect_event_data = [];
                        $event_data_redis = [];
                        foreach ($UserColleagues as $colleague) {
                            unset($data);
                            $data['action'] = CollaborationApi::ACTION_DELETE;
                            $data['access_type'] = UserColleagues::PERMISSION_DELETE;
                            $data['colleague_id'] = $colleague->colleague_id;
                            $data['owner_user_id'] = $UserCollaboration->user_id;
                            $data['uuid'] = $this->folder_uuid;

                            /*
                            $CollaboratedFolder = UserFiles::findOne([
                                'file_uuid'   => $this->folder_uuid,
                                'is_folder'   => UserFiles::TYPE_FOLDER,
                                'user_id'     => $UserCollaboration->user_id,
                                'is_deleted'  => UserFiles::FILE_UNDELETED,
                            ]);
                            */
                            //var_dump($colleague->user_id);
                            $model = new CollaborationApi([
                                'action',
                                'access_type',
                                'owner_user_id',
                                'colleague_id',
                                'uuid',
                            ]/*, $CollaboratedFolder*/);
                            if (!$model->load(['CollaborationApi' => $data]) || !$model->validate()) {
                                return [
                                    'result' => "error",
                                    'errcode' => NodeApi::ERROR_WRONG_DATA,
                                    'info' => "An internal server error occurred.",
                                    'debug' => $model->getErrors(),
                                ];
                            }
                            /* ВОТ ТУТ ЗАГВОЗДОЧКА. НУЖНО НАСОБИРАТЬ КОЛЛЕКЦИЮ ОТВЕТОВ И ВЫБРАТЬ ОДИН КАК ГЛАВНЫЙ */

                            $tmp_answer = $model->colleagueDelete();
                            if ($colleague->user_id == $UserCollaboration->user_id) {
                                $answer = $tmp_answer;
                            }

                            /* собираем евенты */
                            if (isset($tmp_answer['event_data'])) {
                                foreach ($tmp_answer['event_data'] as $k => $v) {
                                    $collect_event_data[] = $tmp_answer['event_data'][$k];
                                    $event_data_redis[] = $tmp_answer['event_data'][$k];
                                }
                            }
                        }

                        /* Отправка данных на вебсокет */
                        /* Отправляем в редис */
                        if (isset($event_data_redis) && sizeof($event_data_redis)) {
                            try {
                                $this->redis->publish("collaboration:{$UserCollaboration->collaboration_id}:fsevent", Json::encode($event_data_redis));
                                $this->redis->save();
                            } catch (\Exception $e) {
                            }
                        }

                        //return false;
                        //exit;
                        /*
                        if (isset($answer['event_delete_answer'])) {
                            $answer['event_data'] = $collect_event_data;
                            $answer['event_delete_answer']['event_data'] = $collect_event_data;
                            return $answer['event_delete_answer'];
                        } else {
                            //return $collect_event_data;
                            return [
                                'result' => "error",
                                'errcode' => NodeApi::ERROR_WRONG_DATA,
                                'info' => $model->getErrors(),
                            ];
                        }
                        */

                        /* если это участник коллаборации */
                    } else {
                        /* тут модуль удаления одного коллеги если этот коллега сам удаляет папку коллаборации */
                        $UserColleague = UserColleagues::findOne([
                            'user_id' => $UserFile->user_id,
                            'collaboration_id' => $UserFile->collaboration_id,
                        ]);

                        unset($data);
                        $data['action'] = CollaborationApi::ACTION_DELETE;
                        $data['access_type'] = UserColleagues::PERMISSION_DELETE;
                        $data['colleague_id'] = $UserColleague->colleague_id;
                        $data['owner_user_id'] = $UserCollaboration->user_id;
                        $data['uuid'] = $this->folder_uuid;
                        $data['is_colleague_self_leave'] = true;

                        /*
                        $CollaboratedFolder = UserFiles::findOne([
                            'file_uuid'   => $this->folder_uuid,
                            'is_folder'   => UserFiles::TYPE_FOLDER,
                            'user_id'     => $UserCollaboration->user_id,
                            'is_deleted'  => UserFiles::FILE_UNDELETED,
                        ]);
                        */

                        $model = new CollaborationApi([
                            'action',
                            'access_type',
                            'owner_user_id',
                            'colleague_id',
                            'uuid',
                        ]/*, $CollaboratedFolder*/);
                        if (!$model->load(['CollaborationApi' => $data]) || !$model->validate()) {
                            return [
                                'result' => "error",
                                'errcode' => NodeApi::ERROR_WRONG_DATA,
                                'info' => "An internal server error occurred.",
                                'debug' => $model->getErrors(),
                            ];
                        }
                        $answer = $model->colleagueDelete();
                        if (isset($answer['event_delete_answer'])) {
                            return [
                                'result' => "success",
                                'info' => "exit-from-collaboration successfully",
                            ];
                            //return $answer['event_delete_answer'];
                        } else {
                            return [
                                'result' => "error",
                                'errcode' => NodeApi::ERROR_WRONG_DATA,
                                'info' => isset($answer['info']) ? $answer['info'] : $answer,
                                'debug' => isset($answer['debug']) ? $answer['debug'] : '',
                            ];
                        }
                        //return $model->colleagueDelete();
                    }
                }
            }
        }

        /* проверка что если это коллаба, то она не должна быть залочена */
        if ($check_collaboration_access && $UserFile->collaboration_id) {
            $mutex_collaboration_name = 'collaboration_id_' . $UserFile->collaboration_id;
            if (!$this->mutex->acquire($mutex_collaboration_name, MUTEX_WAIT_TIMEOUT)) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_FS_TRY_LATER,
                    'info' => "Collaboration is locked now, try later please..",
                ];
            }
        }

        /* ОСНОВНОЕ ТЕЛО ЕВЕНТА УДАЛЕНИЯ ПАПКИ */
        try {
            //$append_file_name = "_" . $UserFile->file_uuid . "_" . time();
            $append_file_name = " (Deleted " . date('d-m-Y H.i.s') .")";
            $test_UserFile_file_name = $UserFile->file_name . $append_file_name;
            //var_dump($test_UserFile_file_name);
            /* Проверка длинны имени файла после удаления, если больше 255 то нужно перед удалением укоротить */
            $test_length = mb_strlen($test_UserFile_file_name, '8bit');
            if ($test_length > UserFiles::FILE_NAME_MAX_LENGTH) {
                $test_UserFile_file_name = Functions::cutUtf8StrToLengthBites($test_UserFile_file_name, UserFiles::FILE_NAME_MAX_LENGTH);
            }
            //var_dump($test_UserFile_file_name); exit;

            /* Начинаем транзакцию */
            //$transaction = Yii::$app->db->getTransaction();
            //if (!$transaction) {
            $transaction = Yii::$app->db->beginTransaction();
            //}

            $UserFileEvent = new UserFileEvents();
            $UserFileEvent->event_uuid = $this->event_uuid ? $this->event_uuid : self::uniq_uuid();
            $UserFileEvent->event_type = UserFileEvents::TYPE_DELETE;
            $UserFileEvent->event_timestamp = time();
            $UserFileEvent->last_event_id = $this->last_event_id;
            $UserFileEvent->file_id = $UserFile->file_id;
            $UserFileEvent->diff_file_uuid = null;
            $UserFileEvent->diff_file_size = 0;
            $UserFileEvent->rev_diff_file_uuid = null;
            $UserFileEvent->rev_diff_file_size = 0;
            $UserFileEvent->file_hash_before_event = null;
            $UserFileEvent->file_hash = null;
            $UserFileEvent->node_id = $UserNode->node_id;
            $UserFileEvent->user_id = $UserNode->user_id;
            $UserFileEvent->file_name_before_event = $UserFile->file_name;
            //$UserFileEvent->file_name_after_event = $UserFile->file_name . $append_file_name;
            $UserFileEvent->file_name_after_event = $test_UserFile_file_name;
            $UserFileEvent->file_size_before_event = 0;
            $UserFileEvent->file_size_after_event = 0;
            $UserFileEvent->erase_nested =
                ($UserFile->is_collaborated == UserFiles::FILE_COLLABORATED && $UserFile->is_owner == UserFiles::IS_COLLEAGUE)
                    ? UserFileEvents::ERASE_NESTED_TRUE
                    : UserFileEvents::ERASE_NESTED_FALSE;
            $UserFileEvent->parent_before_event = $UserFile->file_parent_id;
            $UserFileEvent->parent_after_event = $UserFile->file_parent_id;
            $UserFileEvent->event_creator_user_id = (empty($this->event_creator_user_id)) ? $UserNode->user_id : $this->event_creator_user_id;
            $UserFileEvent->event_creator_node_id = (empty($this->event_creator_node_id)) ? $UserNode->node_id : $this->event_creator_node_id;

            if ($UserFileEvent->save()) {
                $relativePath = UserFiles::getFullPath($UserFile);
                //$UserFile->file_name       .= $append_file_name;
                $diff_size = $UserFile->file_size;
                $diff_count = $UserFile->folder_children_count;
                $UserFile->file_size = 0;
                $UserFile->folder_children_count = 0;
                $UserFile->file_lastatime = time();
                $UserFile->is_deleted = UserFiles::FILE_DELETED;
                $UserFile->is_outdated = UserFiles::FILE_UNOUTDATED;
                $UserFile->last_event_type = UserFileEvents::TYPE_DELETE;
                $UserFile->last_event_uuid = $UserFileEvent->event_uuid;
                //$UserFile->last_event_id = $UserFileEvent->event_id;
                //$UserFile->is_collaborated  = UserFiles::FILE_UNCOLLABORATED;
                //$UserFile->collaboration_id = null;
                $UserFile->is_shared = UserFiles::FILE_UNSHARED;
                $UserFile->share_hash = null;
                $UserFile->share_group_hash = null;
                $UserFile->share_lifetime = null;
                $UserFile->share_ttl_info = null;
                $UserFile->share_created = null;
                $UserFile->share_password = null;
                $erase_nested = ($UserFileEvent->erase_nested == UserFileEvents::ERASE_NESTED_TRUE);
                if ($UserFile->save()) {

                    /* Подготовка инфы о юзере */
                    $User = Users::getPathNodeFS($UserFile->user_id);

                    /* обновление информации о размере родительской папки и количества файлов в ней */
                    UserFiles::update_parents_size_and_count(
                        $User,
                        $UserFile,
                        -1 * $diff_size,
                        -1 * $diff_count
                    );

                    /* Помечаем всех чилдренов как удаленные */
                    //$erase_nested = false;
                    $has_no_any_files = true;
                    if (!$this->is_from_colleagueDelete) {
                        UserFiles::markChildrenAsDeleted($UserFile, $erase_nested, $has_no_any_files);
                    }
                    //var_dump($has_no_any_files);exit;

                    /* Удаление из аплоадов */
                    //UserUploads::deleteAll(['user_id' => $UserFile->user_id, 'file_parent_id' => $UserFile->file_id]);
                    UserUploads::deleteRecords(['user_id' => $UserFile->user_id, 'file_parent_id' => $UserFile->file_id]);

                    /* подготовка евентов */
                    $event_data[] = [
                        'operation' => "file_event",
                        'data' => [
                            'event_id' => $UserFileEvent->event_id,
                            'event_uuid' => $UserFileEvent->event_uuid,
                            'erase_nested' => $erase_nested,
                            'last_event_id' => $UserFileEvent->last_event_id,
                            'event_type' => UserFileEvents::getType($UserFileEvent->event_type),
                            'event_type_int' => $UserFileEvent->event_type,
                            'timestamp' => $UserFileEvent->event_timestamp,
                            'hash' => $UserFileEvent->file_hash,
                            'file_hash_before_event' => $UserFileEvent->file_hash_before_event,
                            'file_hash_after_event' => $UserFileEvent->file_hash,
                            'file_hash' => $UserFileEvent->file_hash,
                            'diff_file_uuid' => $UserFileEvent->diff_file_uuid,
                            'diff_file_size' => $UserFileEvent->diff_file_size,
                            'rev_diff_file_uuid' => $UserFileEvent->rev_diff_file_uuid,
                            'rev_diff_file_size' => $UserFileEvent->rev_diff_file_size,
                            'is_folder' => true,
                            'uuid' => $UserFile->file_uuid,
                            'file_id' => $UserFile->file_id,
                            'file_parent_id' => $UserFile->file_parent_id,
                            'file_name' => $UserFileEvent->file_name_after_event,
                            'file_name_before_event' => $UserFileEvent->file_name_before_event,
                            'file_size' => 0,
                            'user_id' => $UserNode->user_id,
                            'node_id' => $UserNode->node_id,
                            'parent_folder_uuid' => $Parent ? $Parent->file_uuid : null,
                        ],
                    ];

                    /* Вызов самое себя в случае если имеется коллаборация по родительской папке */
                    if ($check_collaboration_access) {
                        if ($UserFile->collaboration_id) {

                            $event_data_redis = [];
                            $UserColleagues = UserColleagues::find()
                                ->where([
                                    'collaboration_id' => $UserFile->collaboration_id,
                                    'colleague_status' => UserColleagues::STATUS_JOINED,
                                ])
                                //->andWhere('(user_id!=:user_id) AND (user_id IS NOT NULL)', [':user_id' => $UserNode->user_id])
                                ->andWhere('(user_id IS NOT NULL)')
                                ->all();
                            //var_dump($UserColleagues);exit;
                            /** @var \common\models\UserColleagues $colleague */
                            foreach ($UserColleagues as $colleague) {

                                if ($colleague->user_id == $UserNode->user_id) {

                                    /* создаем репорт для овнера о том что сделал сам овнер */
                                    if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                        $UserData = Users::findIdentity($colleague->user_id);
                                        if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                            $Report = ColleaguesReports::createNewReport(
                                                $event_data[0],
                                                $colleague,
                                                $UserNode
                                            );
                                        }
                                    }

                                } else {
                                    $UserFile_FindID = UserFiles::findOne([
                                        'file_uuid' => $UserFile->file_uuid,
                                        'is_folder' => UserFiles::TYPE_FOLDER,
                                        'user_id' => $colleague->user_id,
                                        'is_deleted' => UserFiles::FILE_UNDELETED,
                                    ]);
                                    //var_dump($UserFile_FindID);exit;
                                    if ($UserFile_FindID) {
                                        $last_event_id = UserFileEvents::find()
                                            ->andWhere(['file_id' => $UserFile_FindID->file_id])
                                            ->max('event_id');

                                        $data['folder_uuid'] = $UserFile->file_uuid;
                                        $data['last_event_id'] = $last_event_id;
                                        $data['event_creator_user_id'] = $UserNode->user_id;
                                        $data['event_creator_node_id'] = $UserNode->node_id;

                                        $model = new NodeApi(['folder_uuid', 'last_event_id']);
                                        $model->load(['NodeApi' => $data]);
                                        $model->validate();
                                        $answer = $model->folder_event_delete(
                                            self::registerNodeFM(Users::findIdentity($colleague->user_id)),
                                            true,
                                            false,
                                            false
                                        );

                                        /* собираем евенты */
                                        if (isset($answer['event_data'])) {
                                            foreach ($answer['event_data'] as $k => $v) {
                                                $event_data_redis[] = $v;

                                                /* создаем репорт для овнера */
                                                if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                                    $UserData = Users::findIdentity($colleague->user_id);
                                                    if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                                        $Report = ColleaguesReports::createNewReport(
                                                            $v,
                                                            $colleague,
                                                            $UserNode
                                                        );
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            /* Отправка данных на вебсокет */
                            /* Отправляем в редис */
                            if (isset($event_data_redis) && sizeof($event_data_redis)) {
                                try {
                                    $this->redis->publish("collaboration:{$UserFile->collaboration_id}:fsevent", Json::encode($event_data_redis));
                                    $this->redis->save();
                                } catch (\Exception $e) {
                                }
                            }

                        }
                    }

                    /* успешное завершение транзакции */

                    //$UserFile->file_name .= $append_file_name;
                    $UserFile->file_name = $test_UserFile_file_name;
                    $UserFile->is_collaborated = UserFiles::FILE_UNCOLLABORATED;
                    $UserFile->collaboration_id = null;
                    $UserFile->save();
                    //var_dump($UserFile->getErrors()); exit;

                    /* Подготовка имени и полного пути файла а так же проверка на его максимальную длинну */
                    $_new_relativePath = UserFiles::getFullPath($UserFile);
                    $new_file_name = $User->_full_path . DIRECTORY_SEPARATOR . $_new_relativePath;
                    if (mb_strlen($new_file_name, '8bit') > UserFiles::FILE_PATH_MAX_LENGTH) {
                        $transaction->rollBack();

                        return [
                            'result'  => "error",
                            'errcode' => self::ERROR_FILE_PATH_MAX_LENGTH,
                            'info'    => "Path for folder or file is too long (more than " . UserFiles::FILE_PATH_MAX_LENGTH . " bytes).",
                        ];
                    }

                    /* успешное завершение транзакции */
                    $transaction->commit();

                    /* Создание файла со служебной инфой для отображения в ФС вебФМ */
                    if (!$internalNodeFM) {

                        // Если нужно что бы при отмене коллаборации у коллеги папка исчезала безследно
                        // без возможности восстановления копии на момент отмены коллаборации
                        // тогда вот так:
                        if ($erase_nested) {
                            $has_no_any_files = true;
                        }
                        if ($this->is_from_colleagueDelete) {
                            $has_no_any_files = true;
                        }
                        // Иначе закоментировать эту строку которая выше

                        /* удаление папки или ее скрытие в зависимости от того пустая она или с файлами */
                        if ($has_no_any_files) {
                            $folder_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;
                            if (file_exists($folder_name) && is_dir($folder_name)) {
                                FileSys::rmdir($folder_name, true);
                            }
                        } else {
                            $old_file_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;
                            //$_new_relativePath = UserFiles::getFullPath($UserFile);
                            $new_file_name = $User->_full_path . DIRECTORY_SEPARATOR . $_new_relativePath;
                            //var_dump($old_file_name); echo "<br />";
                            //var_dump($new_file_name); echo "<br />";
                            if (file_exists($old_file_name) && !file_exists($new_file_name)) {
                                FileSys::mkdir(dirname($new_file_name), UserFiles::CHMOD_DIR, true);
                                FileSys::move($old_file_name, $new_file_name);
                            }
                            $folder_info_file = $new_file_name . DIRECTORY_SEPARATOR . UserFiles::DIR_INFO_FILE;
                            UserFiles::createFileInfo($folder_info_file, $UserFile);
                        }
                    }

                    $ret = [
                        'result' => "success",
                        'info' => "delete-event stored successfully",
                        'data' => [
                            'event_id' => $UserFileEvent->event_id,
                            'event_uuid' => $UserFileEvent->event_uuid,
                            'folder_uuid' => $UserFile->file_uuid,
                            'timestamp' => $UserFileEvent->event_timestamp,
                        ],
                    ];

                    /* Отправка евента на редис */
                    if (!in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT]) || $erase_nested) {
                        try {
                            $this->redis->publish("user:{$UserNode->user_id}:fs_events", Json::encode($event_data));
                            $this->redis->save();
                        } catch (\Exception $e) {
                            RedisSafe::createNewRecord(
                                RedisSafe::TYPE_FS_EVENTS,
                                $UserNode->user_id,
                                null,
                                Json::encode([
                                    'action'           => 'fs_events',
                                    'chanel'           => "user:{$UserNode->user_id}:fs_events",
                                    'user_id'          => $UserNode->user_id,
                                ])
                            );
                        }
                    }

                    /* Подготовка данных об евенте в общий массив возврата, если нужно */
                    if ($sendEventToSignal) {
                        $ret['event_data'] = $event_data;
                    }

                    /* Освобождаем от блокировки по мутексу, нужно для ФМ,
                     * который за один проход скрипта может делать несколько евентов цикле,
                     * а иначе он не может например удалить сразу две папки*/
                    $this->mutex->release($mutex_name);
                    if (isset($mutex_collaboration_name)) { $this->mutex->release($mutex_collaboration_name); }

                    return $ret;
                } else {
                    $transaction->rollBack();

                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_DATABASE_FAILURE,
                        'info' => "An internal server error occurred.",
                        'debug' => $UserFile->getErrors(),
                    ];
                }
            } else {
                $transaction->rollBack();

                return [
                    'result' => "error",
                    'errcode' => self::ERROR_DATABASE_FAILURE,
                    'info' => "An internal server error occurred.",
                    'debug' => $UserFileEvent->getErrors(),
                ];
            }
        } catch (\Exception $e) {

            return [
                'result' => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $e,
            ];
        }
    }

    /**
     * Функция для копирования папки со всем ее содержимым
     * @param \common\models\UserNode $UserNode
     * @param bool $check_collaboration_access
     * @return array
     */
    public function folder_event_copy($UserNode, $check_collaboration_access = true)
    {
        /* Проверим нет ли лока на выполнение ФС действий вследствие друхих действий этого юзера */
        $mutex_name = 'user_id_' . $UserNode->user_id;
        if (!$this->mutex->acquire($mutex_name, MUTEX_WAIT_TIMEOUT)) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_TRY_LATER,
                'info' => "File or folder is locked now, try later please.",
            ];
        }

        $event_uuid_from_node = $this->event_uuid;

        /* Проверим, есть ли уже в бд евент с таким event_uuid если есть - это повтор и выполнять ничего не нужно. Просто отдать ответ */
        $check_event = $this->check_is_event_exist($UserNode->user_id);
        if ($check_event) {
            return $check_event;
        }

        /* Проверим, есть ли в очереди евент с таким event_uuid если есть - это повтор и выполнять ничего не нужно. Просто отдать ответ */
        if ($event_uuid_from_node) {
            $check_event_queued = $this->check_is_event_queued($UserNode->user_id);
            if ($check_event_queued) {
                return $check_event_queued;
            }
        }

        /* Ограничение на копирование (когда от юзера уже есть в очереди N копирований, отказывать ему в следующем запросе)*/
        $countQueued = QueuedEvents::find()->where([
            'job_type'   => QueuedEvents::TYPE_COPY_FOLDER,
            'job_status' => [QueuedEvents::STATUS_WAITING, QueuedEvents::STATUS_DELAYED, QueuedEvents::STATUS_RESERVED],
            'user_id'    => $UserNode->user_id,
        ])->count();
        if ($countQueued >= Preferences::getValueByKey('QueueCopyFolderLimit', 10, 'integer')) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_QUEUE_LIMIT_TASKS,
                'info' => "Processing folders copy limit reached, try again later",
            ];
        }

        /* находим нужный файл по ууид */
        $UserFile = UserFiles::findOne([
            'file_uuid' => $this->source_folder_uuid,
            'is_folder' => UserFiles::TYPE_FOLDER,
            'user_id' => $UserNode->user_id,
            'is_deleted' => UserFiles::FILE_UNDELETED,
        ]);

        /* проверка существования исходной папки (source) */
        if (!$UserFile) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC_PARENT_NOT_FOUND,
                'info' => "Source folder not found.",
                'debug' => "Source folder with folder_uuid='{$this->source_folder_uuid}' not found.",
            ];
        }

        /* проверка что файл не удаили до этого */
        if ($UserFile->last_event_type == UserFileEvents::TYPE_DELETE) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "Folder was deleted. You can't do any actions with this folder.",
            ];
        }

        /* проверка что не копируем корневую главную папку */
        if (!$this->source_folder_uuid) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "Can't copy root folder.",
            ];
        }

        /* Проверка прав лицензии */
        $ret_lic_acc = self::checkLicenseAccess($UserNode, $UserFile);
        if (!$ret_lic_acc['access']) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_LICENSE_ACCESS,
                'info' => isset($ret_lic_acc['info'])
                    ? $ret_lic_acc['info']
                    : "Haven't access to copy folder. It has another owner NodeId.",
            ];
        }

        /* проверка существования папки назначения (destination) */
        if (!$this->target_parent_folder_uuid) {
            $this->target_parent_folder_uuid = "";
        }
        /** @var \common\models\UserFiles $parent */
        if (mb_strlen($this->target_parent_folder_uuid)) {
            $parent = UserFiles::findOne([
                'file_uuid' => $this->target_parent_folder_uuid,
                'is_folder' => UserFiles::TYPE_FOLDER,
                'user_id' => $UserNode->user_id,
                'is_deleted' => UserFiles::FILE_UNDELETED,
            ]);
            if (!$parent) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_FS_SYNC,
                    'info' => "Destination folder does not exist.",
                    'debug' => "Destination folder with folder_uuid='{$this->target_parent_folder_uuid}' does not exist.",
                ];
            }
            $folder_parent_id = $parent->file_id;
            $folder_parent_uuid = $parent->file_uuid;
            $folder_parent_collaboration_id = $parent->collaboration_id;
        } else {
            $parent = null;
            $folder_parent_id = 0;
            $folder_parent_uuid = null;
            $folder_parent_collaboration_id = null;
        }

        /*Проверка максимальной длинны пути*/
        $maxPath = Yii::$app->db
            ->createCommand("SELECT verify_path_length(:file_id, :file_parent_id, :new_name, :separator, :maxPathAllow, :includeDeleted, false) as test;", [
                'file_id'        => $UserFile->file_id,
                'file_parent_id' => $folder_parent_id,
                'new_name'       => $this->target_folder_name,
                'separator'      => DIRECTORY_SEPARATOR,
                'maxPathAllow'   => UserFiles::FILE_PATH_MAX_LENGTH,
                'includeDeleted' => false,
            ])
            ->queryOne();
        if (!isset($maxPath['test']) || !$maxPath['test']) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_FILE_PATH_MAX_LENGTH,
                'info'    => "Path for folder or file is too long (more than " . UserFiles::FILE_PATH_MAX_LENGTH . " bytes).",
            ];
        }

        /* проверка что если это коллаба, то она не должна быть залочена */
        if ($parent && $parent->collaboration_id) {
            $mutex_collaboration_name = 'collaboration_id_' . $parent->collaboration_id;
            if (!$this->mutex->acquire($mutex_collaboration_name, MUTEX_WAIT_TIMEOUT)) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_FS_TRY_LATER,
                    'info' => "Collaboration is locked now, try later please..",
                ];
            }
        }

        /* проверка прав коллаборации */
        //if ($check_collaboration_access) {
            /* проверка прав коллаборации - проверка папки куда копируется папка */
            if ($parent && $parent->collaboration_id) {
                $Colleague = UserColleagues::findOne([
                    'user_id' => $UserNode->user_id,
                    'collaboration_id' => $parent->collaboration_id,
                ]);
                if (!$Colleague ||
                    ($Colleague->colleague_status != UserColleagues::STATUS_JOINED) ||
                    !in_array($Colleague->colleague_permission, [UserColleagues::PERMISSION_EDIT, UserColleagues::PERMISSION_OWNER])
                ) {
                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_COLLABORATION_ACCESS,
                        'info' => "Haven't access to change collaboration.",
                    ];
                }
            }
        //}

        /* Проверка прав лицензии (для папки копируемой в другую папку) */
        $ret_lic_acc = self::checkLicenseAccess($UserNode, $parent);
        if (!$ret_lic_acc['access']) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_LICENSE_ACCESS,
                'info' => isset($ret_lic_acc['info'])
                    ? $ret_lic_acc['info']
                    : "Haven't access to copy folder in folder. Destination folder has another owner NodeId.",
            ];
        }

        /* проверка что не копируем папку в самое себя */
        if (!UserFiles::allowFolderMove($folder_parent_id, $UserFile->file_uuid)) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "Can't copy folder to it self.",
            ];
        }

        /* проверка что в папке назначения не существует файла или папки с таким же именем */
        if (UserFiles::findOne([
            'file_name' => $this->target_folder_name,
            'file_parent_id' => $folder_parent_id,
            'user_id' => $UserNode->user_id
        ])
        ) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "Folder or file with this name already exist in destination folder.",
            ];
        }

        /* проверка ошибки синхронизации по признаку last_event_id */
        $max_event_id = UserFileEvents::find()
            ->andWhere(['file_id' => $UserFile->file_id])
            ->max('event_id');
        if (intval($max_event_id) !== intval($this->last_event_id)) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "Synchronization conflict.",
                'debug' => "Synchronization conflict. (max_event_id={$max_event_id}; last_event_id={$this->last_event_id})",
            ];
        }

        /* Проверка что копируемая папка не превышает ограничение по количеству чилдренов */
        $User = Users::getPathNodeFS($UserFile->user_id);
        $source_folder_path = $User->_full_path . DIRECTORY_SEPARATOR . UserFiles::getFullPath($UserFile);
        //$License = Licenses::findByType($User->license_type);
        //$max_allowed_children = $License->license_max_count_children_on_copy;
        $max_allowed_children = $User->_ucl_max_count_children_on_copy;
        if ($max_allowed_children > 0) {

            //$ret = UserFiles::countSizeByFS($source_folder_path, $max_allowed_children + 1);
            $ret = UserFiles::countSizeByBD($UserFile->file_id);

            if (isset($ret['count_children']) && $ret['count_children'] > $max_allowed_children) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_OPERATION_DENIED,
                    'info'    => "Folder too big for copy",
                    'debug'   => "Count children ({$ret['count_children']}) in this folder is too big (max allowed children is {$max_allowed_children})",
                    //'info' => "Count children (more than {$max_allowed_children}) in this folder is too big (max allowed children is {$max_allowed_children})",
                ];
            }
        }

        /**/
        $max_timestamp = time();

        /* через очередь или напрямую */
        if ($this->queue) {
            /* выполняем остальную часть через очередь */
            $job_id = $this->queue->push(new CopyFolderJob([
                'file_id'              => $UserFile->file_id,
                'node_hash'            => $UserNode->node_hash,
                'folder_parent_id'     => $folder_parent_id,
                'target_folder_name'   => $this->target_folder_name,
                'max_timestamp'        => $max_timestamp,
                'event_uuid_from_node' => $this->event_uuid,
            ]));

            /* Эта хрень нужна что бы запустить выполенение задание в псевдоочереди, при Unit-тестировании */
            if (isset(Yii::$app->components['queue']['class'], Yii::$app->params['UnitTests']) &&
                Yii::$app->components['queue']['class'] == 'yii\queue\sync\Queue') {
                $this->queue->run(true);
            }

            $QueuedEvent = new QueuedEvents();
            $QueuedEvent->event_uuid = $this->event_uuid;
            $QueuedEvent->job_id     = (string) $job_id;
            $QueuedEvent->user_id    = $UserNode->user_id;
            $QueuedEvent->node_id    = $UserNode->node_id;
            $QueuedEvent->job_status = QueuedEvents::STATUS_WAITING;
            $QueuedEvent->job_type   = QueuedEvents::TYPE_COPY_FOLDER;
            $QueuedEvent->queue_id   = 'queue';
            $QueuedEvent->save();
            /*
            var_dump($QueuedEvent->save());
            var_dump($QueuedEvent->getErrors());
            exit;
            */

            /* Освобождаем от блокировки по мутексу, нужно для ФМ,
             * который за один проход скрипта может делать несколько евентов цикле,
             * а иначе он не может например удалить сразу две папки*/
            $this->mutex->release($mutex_name);
            if (isset($mutex_collaboration_name)) { $this->mutex->release($mutex_collaboration_name); }

            return [
                'result' => "queued",
                'info' => "folder-event-copy stored in queue successfully",
                'data' => [
                    'job_id'     => $QueuedEvent->job_id,
                    'event_uuid' => $this->event_uuid,
                ],
            ];
        } else {
            /* выполняем напрямую */
            return self::folder_event_copy_exec(
                $this->redis,
                $UserFile,
                $UserNode,
                $parent,
                $this->target_folder_name,
                $max_timestamp,
                $this->event_uuid
            );
        }
    }

    /**
     * Рабочая (тяжелая и долгая по времени выполнения) часть функции для копирования папки со всем ее содержимым
     * Вызывается в функции NodeApi::folder_event_copy() а так же из CopyFolderJob::execute()
     *
     * @param $redis
     * @param \common\models\UserFiles $UserFile  ($UserFile)
     * @param \common\models\UserNode $UserNode  ($UserNode)
     * @param \common\models\UserFiles $parent ($parent)
     * @param string $target_folder_name  ($this->target_folder_name)
     * @param integer $max_timestamp
     * @param string|null $event_uuid_from_node
     * @return array
     */
    public static function folder_event_copy_exec(&$redis, &$UserFile, &$UserNode, &$parent, $target_folder_name, $max_timestamp, $event_uuid_from_node=null)
    {
        try {

            /* если копируемая дирректория принадлежит коллаборации, то заблокируем коллаборацию */
            if ($parent && $parent->collaboration_id) {
                /** @var \yii\mutex\FileMutex $mutex */
                $mutex = Yii::$app->mutex;
                $mutex_collaboration_name = 'collaboration_id_' . $parent->collaboration_id;
                $mutex->acquire($mutex_collaboration_name, MUTEX_WAIT_TIMEOUT);
            }

            /* наинаем транзакцию */
            $transaction = Yii::$app->db->beginTransaction();

            /* Получаем данные о пользователе (нужен _full_path) */
            $User = Users::getPathNodeFS($UserFile->user_id);

            /* проверка что в папке назначения не существует файла или папки с таким же именем */
            $folder_parent_id = ($parent) ? $parent->file_id : 0;
            if (UserFiles::findOne([
                'file_name' => $target_folder_name,
                'file_parent_id' => $folder_parent_id,
                'user_id' => $UserNode->user_id
            ])
            ) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_FS_SYNC,
                    'info' => "Folder or file with this name already exist in destination folder.",
                ];
            }

            /* Удаляем фиктивную папку которая сообщает юзеру о том что копирование в очереди */
            if ($parent) {
                $_parent_path = DIRECTORY_SEPARATOR . UserFiles::getFullPath($parent) . DIRECTORY_SEPARATOR;
                $folder_parent_id = $parent->file_id;
            } else {
                $_parent_path = DIRECTORY_SEPARATOR;
                $folder_parent_id = 0;
            }
            $copied_root_file_name = $User->_full_path . $_parent_path . $target_folder_name;
            //var_dump($copied_root_file_name);
            //var_dump($copied_root_file_name. DIRECTORY_SEPARATOR . UserFiles::DIR_COPYING_IN_PROGRESS);
            @unlink($copied_root_file_name. DIRECTORY_SEPARATOR . UserFiles::DIR_COPYING_IN_PROGRESS);
            if (file_exists($copied_root_file_name)) {
                FileSys::rmdir($copied_root_file_name, true);
            }

            /* Если папка куда копируется новая папка была удалена */
            if ($parent && $parent->is_deleted) {
                $transaction->rollBack();
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_FS_SYNC,
                    'info' => "Destination parent folder is deleted. Can't copy into this folder.",
                    'debug' => "Destination parent folder with file_id='{$parent->file_id}' is deleted. Can't copy into this folder.",
                ];
            }

            /* Начинаем процедуру копирования данных */
            //var_dump($target_folder_name);exit;
            $query = "SELECT * FROM copy_files(:file_id, :target_parent_id, :new_folder_name, :separator, :max_timestamp, :event_uuid_from_node, false) LIMIT 1";
            $res = Yii::$app->db->createCommand($query, [
                'file_id'              => $UserFile->file_id,
                'target_parent_id'     => $folder_parent_id,
                'new_folder_name'      => $target_folder_name,
                'separator'            => DIRECTORY_SEPARATOR,
                'max_timestamp'        => $max_timestamp,
                'event_uuid_from_node' => $event_uuid_from_node,
            ])->queryOne();
            //var_dump($res); exit;

            /* проверим что перента не удалили пока делалось копиование в базе */
            if ($parent) {
                $test_parent = UserFiles::findOne(['file_id'    => $parent->file_id]);
                if ($test_parent->is_deleted) {
                    $transaction->rollBack();
                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_FS_SYNC,
                        'info' => "Destination parent folder is deleted. Can't copy into this folder.",
                        'debug' => "Destination parent folder with file_id='{$parent->file_id}' is deleted. Can't copy into this folder.",
                    ];
                }
            }

            /* В зависимости от лицензии будет разный канал для отправки на редис */
            if (!in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT])) {
                $redis_chanel = "user:{$UserNode->user_id}:fs_events";
                $redis_node = null;
            } else {
                $redis_chanel = "node:{$UserNode->node_id}:fs_events";
                $redis_node = $UserNode->node_id;
            }

            /* начинаем обработку копируемой папки и всех ее чилдренов */
            /*
             * Запоминаем групповой ИД этой выборки (единичной записи)
             * для того что бы после транзакции сделать выборку всех записей с этим ИД
             * и выполнить обработку записей (создание фс для ФМ и отправку данных на редис)
             */
            if ($res && is_array($res)) {
                $event_group_ids[$User->user_id] = [
                    'event_group_id' => $res['event_group_id'],
                    '_full_path'     => $User->_full_path,
                    'user_id'        => $User->user_id,
                    'is_owner'       => true,
                ];

                /* Запомним ИД новой скопированной корневой папки */
                if ($target_folder_name == $res['file_name'] && $res['file_parent_id'] == $folder_parent_id) {
                    $stored_file_id = $res['file_id'];
                }


                /* Если копируем в родителя (папку) который находится под коллаборацией */
                if ($parent && $parent->collaboration_id && isset($stored_file_id)) {
                    /*
                    сначала находим все папки c таким же file_uuid как у $parent в связке с коллегами коллаборации
                    но исключаем того коллегу который делает текущее копирование

                    и затем в цикле по найденным коллегам вызываем функцию
                    copy_collaborated(
                        id bigint, -- тут ид папки КОТОРУЮ только что создали как КОПИЮ
                        parent_id bigint, -- тут ид перентов которых только что нашли
                        separator character varying -- тут разделитель пути
                    )

                    функция вернет набор данных как и основная функци копирования copy_files
                    и создаем папки для каждого из коллег + добавляем евенты в набор для отправки на редис
                    не забыть что для каждого из коллег это уже другой user_id и node_id
                    */
                    $query = "SELECT
                        t1.file_id,
                        t1.file_uuid,
                        t1.user_id,
                        t2.colleague_id,
                        t2.user_id
                    FROM {{%user_files}} as t1
                    INNER JOIN {{%user_colleagues}} as t2 ON t1.user_id=t2.user_id AND t1.collaboration_id=t2.collaboration_id
                    WHERE t1.file_uuid = :file_uuid
                    AND t1.user_id != :user_id";
                    $res2 = Yii::$app->db->createCommand($query, [
                        'file_uuid' => $parent->file_uuid,
                        'user_id'   => $UserFile->user_id,
                    ])->queryAll();
                    if (is_array($res2)) {
                        foreach ($res2 as $colleagueParent) {

                            $query = "SELECT * FROM copy_collaborated(:file_id, :colleague_parent_id, :separator) LIMIT 1";
                            $res3 = Yii::$app->db->createCommand($query, [
                                'file_id'             => $stored_file_id,
                                'colleague_parent_id' => $colleagueParent['file_id'],
                                'separator'           => DIRECTORY_SEPARATOR,
                            ])->queryOne();

                            /*
                             * Аналогично copy_files  и тут запоминаем групповой ИД этой выборки (единичной записи)
                             * но уже для каждого из коллаборантов
                             * для того что бы после транзакции сделать выборку всех записей с этим ИД
                             * и выполнить обработку записей (создание фс для ФМ и отправку данных на редис)
                             */
                            if (is_array($res3)) {

                                $UserColleague = Users::getPathNodeFS($colleagueParent['user_id']);
                                //$ColleagueNodeFM = self::registerNodeFM($UserColleague);

                                $event_group_ids[$UserColleague->user_id] = [
                                    'event_group_id' => $res3['event_group_id'],
                                    '_full_path'     => $UserColleague->_full_path,
                                    'user_id'        => $UserColleague->user_id,
                                    'is_owner'       => false,
                                ];

                            }
                        }
                    }
                }

                /* успешное завершение транзакции */
                $transaction->commit();


                /*
                 * После завершения транзакции обраотаем всех кто в массиве $event_group_ids
                 * и создадим для них папки и файлы в фс ФМа а так же отправим данные на редис
                 * */
                if (isset($event_group_ids) && is_array($event_group_ids)) {
                    foreach ($event_group_ids as $k=>$v) {

                        $min_file_id = 0;
                        do {
                            $query = "SELECT DISTINCT ON (e1.file_id)
                                          e1.event_id,
                                          e1.event_uuid,
                                          e1.last_event_id,
                                          e1.event_type,
                                          e1.event_timestamp,
                                          (CASE WHEN (e1.file_hash IS NOT NULL) THEN e1.file_hash ELSE f1.file_md5 END) as file_hash,
                                          e1.file_hash_before_event,
                                          e1.diff_file_uuid,
                                          e1.diff_file_size,
                                          e1.rev_diff_file_uuid,
                                          e1.rev_diff_file_size,
                                          f1.is_folder,
                                          f1.file_uuid,

                                          f1.is_owner,
                                          f1.is_updated,
                                          f1.is_shared,
                                          f1.is_deleted,
                                          f1.is_collaborated,
                                          f1.folder_children_count,
                                          f1.collaboration_id,
                                          f1.share_hash,
                                          f1.share_lifetime,
                                          f1.share_ttl_info,
                                          f1.share_password,
                                          f1.file_lastatime,
                                          f1.file_lastmtime,

                                          e1.file_id,
                                          e1.parent_after_event as file_parent_id,
                                          e1.file_name_after_event as file_name,
                                          e1.file_size_after_event as file_size,
                                          coalesce(f2.file_uuid, null) as parent_folder_uuid,
                                          get_full_path(e1.file_id, :DIRECTORY_SEPARATOR) as file_path
                                      FROM dl_user_file_events as e1
                                      INNER JOIN dl_user_files as f1 ON f1.file_id = e1.file_id
                                      LEFT JOIN dl_user_files as f2 ON e1.parent_after_event = f2.file_id
                                      WHERE e1.event_group_id = :event_group_id
                                      AND e1.file_id > :min_file_id
                                      ORDER BY e1.file_id ASC
                                      LIMIT 100";
                            $res4 = Yii::$app->db->createCommand($query, [
                                'event_group_id'      => $v['event_group_id'],
                                'min_file_id'         => $min_file_id,
                                'DIRECTORY_SEPARATOR' => DIRECTORY_SEPARATOR,
                            ])->queryAll();

                            $event_data = [];
                            if (sizeof($res4)) {
                                foreach ($res4 as $data) {
                                    $min_file_id = $data['file_id'];


                                    /* создаем файлы и папки в файловой системе ФМ для владельца */
                                    $file_name = $v['_full_path'] . DIRECTORY_SEPARATOR . $data['file_path'];
                                    if ($data['is_folder'] == UserFiles::TYPE_FOLDER) {
                                        $file_name .= DIRECTORY_SEPARATOR . UserFiles::DIR_INFO_FILE;
                                    }
                                    //var_dump($file_name);exit;
                                    $dir_name = dirname($file_name);
                                    if (!file_exists($dir_name)) {
                                        FileSys::mkdir($dir_name, UserFiles::CHMOD_DIR, true);
                                    }
                                    UserFiles::createFileInfoRaw($file_name, $data);


                                    /* Собираем набор евентов */
                                    $event_data[] = [
                                        'operation' => "file_event",
                                        'data' => [
                                            'event_id'               => $data['event_id'],
                                            'event_uuid'             => $data['event_uuid'],
                                            'last_event_id'          => $data['last_event_id'],
                                            'event_type'             => UserFileEvents::getType($data['event_type']),
                                            'event_type_int'         => $data['event_type'],
                                            'timestamp'              => $data['event_timestamp'],
                                            'hash'                   => $data['file_hash'],
                                            'file_hash_before_event' => $data['file_hash_before_event'],
                                            'file_hash_after_event'  => $data['file_hash'],
                                            'file_hash'              => $data['file_hash'],
                                            'diff_file_uuid'         => $data['diff_file_uuid'],
                                            'diff_file_size'         => $data['diff_file_size'],
                                            'rev_diff_file_uuid'     => $data['rev_diff_file_uuid'],
                                            'rev_diff_file_size'     => $data['rev_diff_file_size'],
                                            'is_folder'              => ($data['is_folder'] == UserFiles::TYPE_FOLDER),
                                            'uuid'                   => $data['file_uuid'],
                                            'file_id'                => $data['file_id'],
                                            'file_parent_id'         => $data['file_parent_id'],
                                            'file_name'              => $data['file_name'],
                                            'file_size'              => $data['file_size'],
                                            'user_id'                => $v['user_id'],
                                            'node_id'                => 0, //$UserNode->node_id,
                                            'parent_folder_uuid'     => $data['parent_folder_uuid'],
                                        ],
                                    ];
                                }

                                /* Отправка пачки евентов на редис (того кто создал данное событие) */
                                if ($v['is_owner']) {
                                    try {
                                        $redis->publish($redis_chanel, Json::encode($event_data));
                                        $redis->save();
                                        unset($event_data);
                                        $event_data = [];
                                    } catch (\Exception $e) {
                                        RedisSafe::createNewRecord(
                                            RedisSafe::TYPE_FS_EVENTS,
                                            $v['user_id'],
                                            $redis_node,
                                            Json::encode([
                                                'action' => 'fs_events',
                                                'chanel' => $redis_chanel,
                                                'user_id' => $v['user_id'],
                                                'noe_id' => $redis_node,
                                            ])
                                        );
                                    }
                                } else {
                                    if (!in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT])) {
                                        try {
                                            $redis->publish("user:{$v['user_id']}:fs_events", Json::encode($event_data));
                                            $redis->save();
                                            unset($event_data);
                                            $event_data = [];
                                        } catch (\Exception $e) {
                                            RedisSafe::createNewRecord(
                                                RedisSafe::TYPE_FS_EVENTS,
                                                $v['user_id'],
                                                null,
                                                Json::encode([
                                                    'action' => 'fs_events',
                                                    'chanel' => "user:{$v['user_id']}:fs_events",
                                                    'user_id' => $v['user_id'],
                                                ])
                                            );
                                        }
                                    }
                                }

                            }


                        } while (sizeof($res4) > 0);
                    }
                }

                if (isset($mutex, $mutex_collaboration_name)) { $mutex->release($mutex_collaboration_name); }

                /* Ответ */
                return [
                    'result' => "success",
                    'info' => "folder-event-copy executed successfully",
                    'data' => [
                        /*
                        'event_id' => $UserFileEvent->event_id,
                        'event_uuid' => $UserFileEvent->event_uuid,
                        'timestamp' => $UserFileEvent->event_timestamp,
                        */
                    ],
                ];

            }

        } catch (\Exception $e) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $e,
            ];
        }

    }

    /**
     * Метод для регистрации события move
     * @param \common\models\UserNode $UserNode
     * @param bool $internalNodeFM
     * @param bool $sendEventToSignal
     * @return array
     */
    public function folder_event_move($UserNode, $sendEventToSignal=false, $internalNodeFM=false, $check_collaboration_access=true, $send_collaboration_event=false)
    {
        /* Проверим нет ли лока на выполнение ФС действий вследствие друхих действий этого юзера */
        $mutex_name = 'user_id_' . $UserNode->user_id;
        if (!$this->mutex->acquire($mutex_name, MUTEX_WAIT_TIMEOUT)) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_TRY_LATER,
                'info' => "File or folder is locked now, try later please.",
            ];
        }

        /* Проверим, есть ли уже в бд евент с таким event_uuid если есть - это повтор и выполнять ничего не нужно. Просто отдать ответ */
        $check_event = $this->check_is_event_exist($UserNode->user_id);
        if ($check_event) {
            return $check_event;
        }

        /* находим нужный файл по ууид */
        $UserFile = UserFiles::findOne([
            'file_uuid' => $this->folder_uuid,
            'is_folder' => UserFiles::TYPE_FOLDER,
            'user_id'   => $UserNode->user_id,
            'is_deleted' => UserFiles::FILE_UNDELETED,
        ]);

        /* проверка существования исходной папки (source) */
        if (!$UserFile){
            return [
                'result'  => "error",
                'errcode' => self::ERROR_FS_SYNC_PARENT_NOT_FOUND,
                'info'    => "Source folder not found.",
                'debug'    => "Source folder with folder_uuid='{$this->folder_uuid}' not found.",
            ];
        }

        /* проверка что это не корневая папка коллаборации */
        if ($UserFile->is_collaborated) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_FS_SYNC_COLLABORATION_MOVE,
                'info'    => "Can't move or rename root-collaboration folder.",
            ];
        }

        /* Проверка прав лицензии */
        if ($check_collaboration_access) {
            $ret_lic_acc = self::checkLicenseAccess($UserNode, $UserFile);
            if (!$ret_lic_acc['access']) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_LICENSE_ACCESS,
                    'info' => isset($ret_lic_acc['info'])
                        ? $ret_lic_acc['info']
                        : "Haven't access to move folder. It has another owner NodeId.",
                ];
            }
        }

        /* проверка что файл не удаили до этого */
        if ($UserFile->last_event_type == UserFileEvents::TYPE_DELETE) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info'    => "Folder was deleted. You can't do any actions with this folder.",
            ];
        }

        /* проверка существования папки назначения (destination) */
        if (!$this->new_parent_folder_uuid) { $this->new_parent_folder_uuid = ""; }
        if (mb_strlen($this->new_parent_folder_uuid)) {
            $parent = UserFiles::findOne([
                'file_uuid' => $this->new_parent_folder_uuid,
                'is_folder' => UserFiles::TYPE_FOLDER,
                'user_id'   => $UserNode->user_id,
                'is_deleted' => UserFiles::FILE_UNDELETED,
            ]);
            if (!$parent) {
                return [
                    'result'  => "error",
                    'errcode' => self::ERROR_FS_SYNC_PARENT_NOT_FOUND,
                    'info'    => "Destination folder does not exist.",
                    'debug'    => "Destination folder with folder_uuid='{$this->new_parent_folder_uuid}' does not exist.",
                ];
            }
            $folder_parent_id = $parent->file_id;
            $folder_parent_uuid = $parent->file_uuid;
            $folder_parent_collaboration_id = $parent->collaboration_id;
        } else {
            $parent = null;
            $folder_parent_id = 0;
            $folder_parent_uuid = null;
            $folder_parent_collaboration_id = null;
        }
        /** @var \common\models\UserFiles $parent */
        $file_parent_id_before_event = $UserFile->file_parent_id;
        $file_renamed = ($UserFile->file_name != $this->new_folder_name);
        $file_moved = ($UserFile->file_parent_id != $folder_parent_id);
        $is_moved = ($file_parent_id_before_event != $folder_parent_id);

        /*Проверка максимальной длинны пути*/
        $maxPath = Yii::$app->db
            ->createCommand("SELECT verify_path_length(:file_id, :file_parent_id, :new_name, :separator, :maxPathAllow, :includeDeleted, false) as test;", [
                'file_id'        => $UserFile->file_id,
                'file_parent_id' => $folder_parent_id,
                'new_name'       => $this->new_folder_name,
                'separator'      => DIRECTORY_SEPARATOR,
                'maxPathAllow'   => UserFiles::FILE_PATH_MAX_LENGTH,
                'includeDeleted' => true,
            ])
            ->queryOne();
        if (!isset($maxPath['test']) || !$maxPath['test']) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_FILE_PATH_MAX_LENGTH,
                'info'    => "Path for folder or file is too long (more than " . UserFiles::FILE_PATH_MAX_LENGTH . " bytes).",
            ];
        }

        /* проверка прав коллаборации */
        if ($check_collaboration_access) {
            /* проверка самой перемещаемой папки */
            if ($UserFile->collaboration_id && !$UserFile->is_collaborated) {
                $Colleague = UserColleagues::findOne([
                    'user_id' => $UserNode->user_id,
                    'collaboration_id' => $UserFile->collaboration_id,
                ]);
                //if (!in_array($Colleague->colleague_permission, [UserColleagues::PERMISSION_EDIT, UserColleagues::PERMISSION_OWNER])) {
                if (!$Colleague ||
                    ($Colleague->colleague_status != UserColleagues::STATUS_JOINED) ||
                    !in_array($Colleague->colleague_permission, [UserColleagues::PERMISSION_EDIT, UserColleagues::PERMISSION_OWNER])) {
                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_COLLABORATION_ACCESS,
                        'info' => "Haven't access to change collaboration.",
                    ];
                }
            }
            /* проверка папки куда перемещается папка */
            if ($parent && $parent->collaboration_id) {
                $Colleague = UserColleagues::findOne([
                    'user_id' => $UserNode->user_id,
                    'collaboration_id' => $parent->collaboration_id,
                ]);
                if (!$Colleague ||
                    ($Colleague->colleague_status != UserColleagues::STATUS_JOINED) ||
                    !in_array($Colleague->colleague_permission, [UserColleagues::PERMISSION_EDIT, UserColleagues::PERMISSION_OWNER])) {
                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_COLLABORATION_ACCESS,
                        'info' => "Haven't access to change collaboration.",
                    ];
                }
            }
        }

        /* получение к полному пути каталога до его перемещения (нужно для создания копии папки при коллаборации) */
        if (!$UserFile->collaboration_id && $folder_parent_collaboration_id) {
            $source_relative_path = UserFiles::getFullPath($UserFile);
        }
        if ($UserFile->collaboration_id && $folder_parent_collaboration_id && ($folder_parent_collaboration_id != $UserFile->collaboration_id)) {
            $source_relative_path = UserFiles::getFullPath($UserFile);
        }

        /* Проверка прав лицензии (для папки перемещаемой в другую папку) */
        $ret_lic_acc = self::checkLicenseAccess($UserNode, $parent);
        if (!$ret_lic_acc['access']) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_LICENSE_ACCESS,
                'info' => isset($ret_lic_acc['info'])
                    ? $ret_lic_acc['info']
                    : "Haven't access to move folder in folder. Destination folder has another owner NodeId.",
            ];
        }

        /* проверка что не перемещаем корневую главную папку */
        if (!$this->folder_uuid) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info'    => "Can't move root folder.",
            ];
        }

        /* проверка что не перемещаем папку в самое себя */
        if (!UserFiles::allowFolderMove($folder_parent_id, $UserFile->file_uuid)) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info'    => "Can't move top folder to her low folder. (Can't move parent to child)",
            ];
        }

        /* проверка что в папке назначения не существует файла или папки с таким же именем */
        $checkExistsUserFile = UserFiles::findOne([
            'file_name'      => $this->new_folder_name,
            'file_parent_id' => $folder_parent_id,
            'user_id'        => $UserNode->user_id
        ]);
        if ($checkExistsUserFile) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info'    => "Folder or file with this name already exist in destination folder.",
                'error_data' => [
                    'file_hash' => $checkExistsUserFile->file_md5,
                    'file_md5'  => $checkExistsUserFile->file_md5,
                    'file_size' => $checkExistsUserFile->file_size,
                    'file_name' => $checkExistsUserFile->file_name,
                ],
            ];
        }

        //echo "ok move {$UserFile->file_name} to " . (isset($parent) ? UserFiles::getFullPath($parent) : "rootFolder") ; exit;

        /* проверка ошибки синхронизации по признаку last_event_id */
        $max_event_id = UserFileEvents::find()
            ->andWhere(['file_id' => $UserFile->file_id])
            ->max('event_id');
        if (intval($max_event_id) !== intval($this->last_event_id)) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info'    => "Synchronization conflict.",
                'debug'    => "Synchronization conflict. (max_event_id={$max_event_id}; last_event_id={$this->last_event_id})",
            ];
        }

        /* Данные по папке до выполнения евента */
        $UserFile_OLD_collaboration_id  = $UserFile->collaboration_id;

        /* Проверка того что можно выполнить евент согласно правил коллаборации
        Если перемещается из коллаборации или между ними, то это не перемещение а копи+делет */
        if (
            (!$UserFile_OLD_collaboration_id && $folder_parent_collaboration_id) || // ++ Перемещение папки из папки без коллаборации в коллаборацию
            (!$folder_parent_collaboration_id && $UserFile_OLD_collaboration_id) || // ++ Перемещение папки из папки коллаборации в папку без коллаборации
            ($UserFile_OLD_collaboration_id && $folder_parent_collaboration_id && ($folder_parent_collaboration_id != $UserFile_OLD_collaboration_id)) // ++ Перемещение папки между коллаборациями
        ) {
            $transaction1 = Yii::$app->db->beginTransaction();

            /* нужно подменить ид ноды на фм при замене мув на копи+дел*/
            $UserNodeFM = self::registerNodeFM(Users::findIdentity($UserNode->user_id));

            $data['last_event_id'] = $this->last_event_id;
            $data['target_parent_folder_uuid'] = $folder_parent_uuid ? $folder_parent_uuid : "";
            $data['source_folder_uuid'] = $UserFile->file_uuid;
            $data['target_folder_name'] = $this->new_folder_name;

            $model = new NodeApi(['last_event_id', 'source_folder_uuid', 'target_folder_name']);
            if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
                $transaction1->rollBack();
                return [
                    'result' => "error",
                    'info' => "Load data error for model API",
                ];
            }
            $answer_copy = $model->folder_event_copy(
                $UserNodeFM,//$UserNode, /* нужно подменить ид ноды на фм при замене мув на копи+дел*/
                true
            );
            if (in_array($answer_copy['result'], ['queued', 'success'])) {
                $ret_result = $answer_copy['result'];

                $data2['last_event_id'] = UserFiles::last_event_id($UserFile->file_id);
                if (!$data2['last_event_id'] || $data2['last_event_id'] == 0) {
                    $transaction1->rollBack();
                    return [
                        'result' => "error",
                        'info' => "last_event_id error.",
                    ];
                }
                $data2['folder_uuid'] = $UserFile->file_uuid;

                $model = new NodeApi(['folder_uuid', 'last_event_id']);
                if (!$model->load(['NodeApi' => $data2]) || !$model->validate()) {
                    return [
                        'result' => "error",
                        'info' => "An internal server error occurred.",
                        'debug' => $model->getErrors(),
                    ];
                }

                $answer_delete = $model->folder_event_delete(
                    $UserNodeFM, //$UserNode, /* нужно подменить ид ноды на фм при замене мув на копи+дел*/
                    $sendEventToSignal,
                    $internalNodeFM,
                    $check_collaboration_access,
                    $send_collaboration_event
                );
                if ($answer_delete['result'] == "success") {
                    $transaction1->commit();
                    return [
                        'result'  => $ret_result,//"success",
                        'info' => "move-event stored successfully",
                        'data' => [
                            'is_copy_del_instead_move' => true,
                        ],
                    ];
                } else {
                    $transaction1->rollBack();
                    return [
                        'result'  => "error",
                        'errcode' => self::ERROR_MOVE_PROHIBITED,
                        'info'    => "File move to outside of collaboration is prohibited. Use copy and delete instead",
                        'debug'   => $answer_delete,
                    ];
                }

            } else {
                $transaction1->rollBack();
                return [
                    'result'  => "error",
                    'errcode' => self::ERROR_MOVE_PROHIBITED,
                    'info'    => "Folder move to outside of collaboration is prohibited. Use copy and delete instead",
                    'debug'   => $answer_copy,
                ];
            }
        }

        /* проверка что если это коллаба, то она не должна быть залочена */
        if ($check_collaboration_access && $UserFile->collaboration_id) {
            $mutex_collaboration_name = 'collaboration_id_' . $UserFile->collaboration_id;
            if (!$this->mutex->acquire($mutex_collaboration_name, MUTEX_WAIT_TIMEOUT)) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_FS_TRY_LATER,
                    'info' => "Collaboration is locked now, try later please..",
                ];
            }
        }

        /* */
        try {
            /* Начинаем транзакцию */
            $transaction = Yii::$app->db->beginTransaction();
            $UserFileEvent                          = new UserFileEvents();
            $UserFileEvent->event_uuid              = $this->event_uuid ? $this->event_uuid : self::uniq_uuid();
            $UserFileEvent->event_type              = UserFileEvents::TYPE_MOVE;
            $UserFileEvent->event_timestamp         = time();
            $UserFileEvent->last_event_id           = $this->last_event_id;
            $UserFileEvent->file_id                 = $UserFile->file_id;
            $UserFileEvent->diff_file_uuid          = null;
            $UserFileEvent->diff_file_size          = 0;
            $UserFileEvent->rev_diff_file_uuid      = null;
            $UserFileEvent->rev_diff_file_size      = 0;
            $UserFileEvent->file_hash_before_event  = null;
            $UserFileEvent->file_hash               = null;
            $UserFileEvent->node_id                 = $UserNode->node_id;
            $UserFileEvent->user_id                 = $UserNode->user_id;
            $UserFileEvent->file_name_before_event  = $UserFile->file_name;
            $UserFileEvent->file_name_after_event   = $this->new_folder_name;
            $UserFileEvent->file_size_before_event  = 0;
            $UserFileEvent->file_size_after_event   = 0;
            $UserFileEvent->parent_before_event = $file_parent_id_before_event;
            $UserFileEvent->parent_after_event = $folder_parent_id;
            $UserFileEvent->event_creator_user_id = (empty($this->event_creator_user_id)) ? $UserNode->user_id : $this->event_creator_user_id;
            $UserFileEvent->event_creator_node_id = (empty($this->event_creator_node_id)) ? $UserNode->node_id : $this->event_creator_node_id;

            if ($UserFileEvent->save()) {

                $User = Users::getPathNodeFS($UserFile->user_id);

                /* обновление информации о размере родительской папки и количества файлов в ней (где была папка до перемещения) */
                if ($file_moved) {
                    UserFiles::update_parents_size_and_count(
                        $User,
                        $UserFile,
                        -1 * $UserFile->file_size,
                        -1 * $UserFile->folder_children_count
                    );
                }

                $_old_relativePath = UserFiles::getFullPath($UserFile);
                $UserFile->is_outdated          = UserFiles::FILE_UNOUTDATED;
                $UserFile->file_name            = $this->new_folder_name;
                $UserFile->file_lastatime       = time();
                $UserFile->file_parent_id       = $folder_parent_id;
                $UserFile->last_event_type      = UserFileEvents::TYPE_MOVE;
                $UserFile->last_event_uuid      = $UserFileEvent->event_uuid;
                //$UserFile->last_event_id        = $UserFileEvent->event_id;
                $UserFile_OLD_collaboration_id  = $UserFile->collaboration_id;
                if (!$UserFile->is_collaborated) {
                    $UserFile->collaboration_id = $folder_parent_collaboration_id;
                }
                /** @var $parent \common\models\UserFiles */
                $old_share_group_hash = $UserFile->share_group_hash;
                if (!$UserFile->is_shared) {
                    $UserFile->share_group_hash = $parent ? $parent->share_group_hash : null;
                    $UserFile->share_lifetime   = $parent ? $parent->share_lifetime : null;
                    $UserFile->share_ttl_info   = $parent ? $parent->share_ttl_info : null;
                    $UserFile->share_created    = $parent ? $parent->share_created : null;
                    $UserFile->share_password   = $parent ? $parent->share_password : null;
                    $UserFile->share_group_hash ? $UserFile->generate_share_hash() : $UserFile->share_hash = null;
                }

                if ($UserFile->save()) {

                    /* обновление информации о размере родительской папки и количества файлов в ней (куда попала папка после перемещения) */
                    if ($file_moved) {
                        UserFiles::update_parents_size_and_count(
                            $User,
                            $UserFile,
                            $UserFile->file_size,
                            $UserFile->folder_children_count
                        );
                    }

                    /* шаринг. меняем групповую шару */
                    //UserFiles::changeChildrenGroupHash($UserFile, $UserFile->share_group_hash, $UserFile->collaboration_id);

                    $event_data[] = [
                        'operation' => "file_event",
                        'data'      => [
                            'event_id'                      => $UserFileEvent->event_id,
                            'event_uuid'                    => $UserFileEvent->event_uuid,
                            'last_event_id'                 => $UserFileEvent->last_event_id,
                            'event_type'                    => UserFileEvents::getType($UserFileEvent->event_type),
                            'event_type_int'                => $UserFileEvent->event_type,
                            'timestamp'                     => $UserFileEvent->event_timestamp,
                            'hash'                          => $UserFileEvent->file_hash,
                            'file_hash_before_event'        => $UserFileEvent->file_hash_before_event,
                            'file_hash_after_event'         => $UserFileEvent->file_hash,
                            'file_hash'                     => $UserFileEvent->file_hash,
                            'diff_file_uuid'                => $UserFileEvent->diff_file_uuid,
                            'diff_file_size'                => $UserFileEvent->diff_file_size,
                            'rev_diff_file_uuid'            => $UserFileEvent->rev_diff_file_uuid,
                            'rev_diff_file_size'            => $UserFileEvent->rev_diff_file_size,
                            'is_folder'                     => true,
                            'uuid'                          => $UserFile->file_uuid,
                            'file_id'                       => $UserFile->file_id,
                            'file_parent_id'                => $UserFile->file_parent_id,
                            'file_parent_id_before_event'   => $file_parent_id_before_event,
                            'file_name'                     => $UserFileEvent->file_name_after_event,
                            'file_name_before_event'        => $UserFileEvent->file_name_before_event,
                            'file_size'                     => 0,
                            'user_id'                       => $UserNode->user_id,
                            'node_id'                       => $UserNode->node_id,
                            'parent_folder_uuid'            => $parent ? $parent->file_uuid : null,
                            'parent_folder_name'            => $parent ? $parent->file_name : 'root',
                            'file_renamed'                  => $file_renamed,
                            'file_moved'                    => $file_moved,
                        ],
                    ];

                    /* Вызов самое себя в случае если имеется коллаборация по родительской папке */
                    if ($check_collaboration_access && !$UserFile->is_collaborated) {

                        /* Перемещение папки внутри коллаборации */
                        /* тут все предельно просто вызываем мтод перемещения как есть - самое себя */
                        //if ($UserFile_OLD_collaboration_id == $folder_parent_collaboration_id) {
                        if ($UserFile_OLD_collaboration_id == $folder_parent_collaboration_id && $folder_parent_collaboration_id) {

                            $event_data_redis = [];
                            $UserColleagues = UserColleagues::find()
                                ->where([
                                    'collaboration_id' => $UserFile_OLD_collaboration_id,
                                    'colleague_status' => UserColleagues::STATUS_JOINED,
                                ])
                                //->andWhere('(user_id!=:user_id) AND (user_id IS NOT NULL)', [':user_id' => $UserNode->user_id])
                                ->andWhere('(user_id IS NOT NULL)')
                                ->all();
                            /** @var \common\models\UserColleagues $colleague */
                            foreach ($UserColleagues as $colleague) {

                                if ($colleague->user_id == $UserNode->user_id) {

                                    /* создаем репорт для овнера о том что сделал сам овнер */
                                    if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                        $UserData = Users::findIdentity($colleague->user_id);
                                        if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                            $Report = ColleaguesReports::createNewReport(
                                                $event_data[0],
                                                $colleague,
                                                $UserNode
                                            );
                                        }
                                    }

                                } else {
                                    $UserFile_FindID = UserFiles::findOne([
                                        'file_uuid' => $this->folder_uuid,
                                        'is_folder' => UserFiles::TYPE_FOLDER,
                                        'user_id' => $colleague->user_id,
                                        'is_deleted' => UserFiles::FILE_UNDELETED,
                                    ]);
                                    //var_dump($UserFile_OLD_collaboration_id); exit;
                                    if ($UserFile_FindID) {
                                        $last_event_id = UserFileEvents::find()
                                            ->andWhere(['file_id' => $UserFile_FindID->file_id])
                                            ->max('event_id');

                                        $data['new_parent_folder_uuid'] = $folder_parent_uuid;
                                        $data['folder_uuid'] = $this->folder_uuid;
                                        $data['last_event_id'] = $last_event_id;
                                        $data['new_folder_name'] = $this->new_folder_name;
                                        $data['event_creator_user_id'] = $UserNode->user_id;
                                        $data['event_creator_node_id'] = $UserNode->node_id;

                                        $model = new NodeApi(['new_parent_folder_uuid', 'folder_uuid', 'last_event_id', 'new_folder_name']);
                                        $model->load(['NodeApi' => $data]);
                                        $model->validate();
                                        $answer = $model->folder_event_move(
                                            self::registerNodeFM(Users::findIdentity($colleague->user_id)),
                                            true,
                                            false,
                                            false
                                        );

                                        /* собираем евенты */
                                        if (isset($answer['event_data'])) {
                                            foreach ($answer['event_data'] as $k => $v) {
                                                $event_data_redis[] = $v;

                                                /* создаем репорт для овнера */
                                                if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                                    $UserData = Users::findIdentity($colleague->user_id);
                                                    if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                                        $Report = ColleaguesReports::createNewReport(
                                                            $v,
                                                            $colleague,
                                                            $UserNode
                                                        );
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            /* Отправка данных на вебсокет */
                            /* Отправляем в редис */
                            if (isset($event_data_redis) && sizeof($event_data_redis)) {
                                try {
                                    $this->redis->publish("collaboration:{$UserFile_OLD_collaboration_id}:fsevent", Json::encode($event_data_redis));
                                    $this->redis->save();
                                } catch (\Exception $e) {}
                            }

                        }

                        /* Перемещение папки из папки коллаборации в папку без коллаборации */
                        /* Для участников коллаборации это должно выглядеть как обычное удаление этого файла */
                        if (!$folder_parent_collaboration_id && $UserFile_OLD_collaboration_id) {

                            $event_data_redis = [];
                            $UserColleagues = UserColleagues::find()
                                ->where([
                                    'collaboration_id' => $UserFile_OLD_collaboration_id,
                                    'colleague_status' => UserColleagues::STATUS_JOINED,
                                ])
                                //->andWhere('(user_id!=:user_id) AND (user_id IS NOT NULL)', [':user_id' => $UserNode->user_id])
                                ->andWhere('(user_id IS NOT NULL)')
                                ->all();
                            /** @var \common\models\UserColleagues $colleague */
                            foreach ($UserColleagues as $colleague) {

                                if ($colleague->user_id == $UserNode->user_id) {

                                    /* создаем репорт для овнера о том что сделал сам овнер */
                                    if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                        $UserData = Users::findIdentity($colleague->user_id);
                                        if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                            $Report = ColleaguesReports::createNewReport(
                                                $event_data[0],
                                                $colleague,
                                                $UserNode
                                            );
                                        }
                                    }

                                } else {
                                    $UserFile_FindID = UserFiles::findOne([
                                        'file_uuid' => $this->folder_uuid,
                                        'is_folder' => UserFiles::TYPE_FOLDER,
                                        'user_id' => $colleague->user_id,
                                        'is_deleted' => UserFiles::FILE_UNDELETED,
                                    ]);
                                    //var_dump($UserFile_OLD_collaboration_id); exit;
                                    if ($UserFile_FindID) {
                                        $last_event_id = UserFileEvents::find()
                                            ->andWhere(['file_id' => $UserFile_FindID->file_id])
                                            ->max('event_id');


                                        $data['folder_uuid'] = $this->folder_uuid;
                                        $data['last_event_id'] = $last_event_id;
                                        $data['event_creator_user_id'] = $UserNode->user_id;
                                        $data['event_creator_node_id'] = $UserNode->node_id;

                                        $model = new NodeApi(['folder_uuid', 'last_event_id']);
                                        $model->load(['NodeApi' => $data]);
                                        $model->validate();
                                        $answer = $model->folder_event_delete(
                                            self::registerNodeFM(Users::findIdentity($colleague->user_id)),
                                            true,
                                            false,
                                            false
                                        );
                                        //var_dump($answer); exit;

                                        /* собираем евенты */
                                        if (isset($answer['event_data'])) {
                                            foreach ($answer['event_data'] as $k => $v) {
                                                $event_data_redis[] = $v;

                                                /* создаем репорт для овнера */
                                                if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                                    $UserData = Users::findIdentity($colleague->user_id);
                                                    if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                                        $Report = ColleaguesReports::createNewReport(
                                                            $v,
                                                            $colleague,
                                                            $UserNode
                                                        );
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            /* Отправка данных на вебсокет */
                            /* Отправляем в редис */
                            if (isset($event_data_redis) && sizeof($event_data_redis)) {
                                try {
                                    $this->redis->publish("collaboration:{$UserFile_OLD_collaboration_id}:fsevent", Json::encode($event_data_redis));
                                    $this->redis->save();
                                } catch (\Exception $e) {}
                            }

                        }

                        /* Перемещение папки из вне в папку коллаборации */
                        /* Для участников коллаборации это должно выглядеть как создание копии этой папки со всем ее содержимым (рекурсия) */
                        if (!$UserFile_OLD_collaboration_id && $folder_parent_collaboration_id) {
                            if (isset($source_relative_path)) {

                                $event_data_redis = [];
                                $UserColleagues = UserColleagues::find()
                                    ->where([
                                        'collaboration_id' => $folder_parent_collaboration_id,
                                        'colleague_status' => UserColleagues::STATUS_JOINED,
                                    ])
                                    //->andWhere('(user_id!=:user_id) AND (user_id IS NOT NULL)', [':user_id' => $UserNode->user_id])
                                    ->andWhere('(user_id IS NOT NULL)')
                                    ->all();
                                /** @var \common\models\UserColleagues $colleague */
                                foreach ($UserColleagues as $colleague) {

                                    if ($colleague->user_id == $UserNode->user_id) {

                                        /* создаем репорт для овнера о том что сделал сам овнер */
                                        if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                            $UserData = Users::findIdentity($colleague->user_id);
                                            if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                                $Report = ColleaguesReports::createNewReport(
                                                    $event_data[0],
                                                    $colleague,
                                                    $UserNode
                                                );
                                            }
                                        }

                                    } else {
                                        $data['copy_folder_uuid'] = $this->folder_uuid;
                                        $data['folder_uuid'] = $this->folder_uuid;
                                        $data['parent_folder_uuid'] = $folder_parent_uuid;
                                        $data['folder_name'] = $UserFile->file_name;
                                        $data['collaboration_id'] = $folder_parent_collaboration_id;
                                        $data['event_creator_user_id'] = $UserNode->user_id;
                                        $data['event_creator_node_id'] = $UserNode->node_id;

                                        /*
                                        $model = new NodeApi(['copy_folder_uuid', 'folder_uuid', 'folder_name']);
                                        $model->load(['NodeApi' => $data]);
                                        $model->validate();
                                        */

                                        $UserSource = Users::getPathNodeFS($UserFile->user_id);
                                        $source_full_path = $UserSource->_full_path . DIRECTORY_SEPARATOR . $source_relative_path;
                                        //$event_store = [];
                                        UserFiles::folderCopy(
                                            $source_full_path,
                                            self::registerNodeFM(Users::findIdentity($colleague->user_id)),
                                            $folder_parent_uuid,
                                            $folder_parent_collaboration_id,
                                            $event_data_redis
                                        );
                                        //var_dump($event_store);
                                        //exit;

                                        /* собираем евенты */
                                        /*
                                        if (isset($event_store) && sizeof($event_store)) {
                                            foreach ($event_store as $k => $v) {
                                                $event_data_redis[] = $v;
                                            }
                                        }
                                        */

                                        /* создаем репорт для овнера */
                                        if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                            $UserData = Users::findIdentity($colleague->user_id);
                                            if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                                $Report = ColleaguesReports::createNewReport(
                                                    $event_data_redis[0],
                                                    $colleague,
                                                    $UserNode
                                                );
                                            }
                                        }
                                    }
                                }

                                /* Отправка данных на вебсокет */
                                /* Отправляем в редис */
                                if (isset($event_data_redis) && sizeof($event_data_redis)) {
                                    try {
                                        $this->redis->publish("collaboration:{$folder_parent_collaboration_id}:fsevent", Json::encode($event_data_redis));
                                        $this->redis->save();
                                    } catch (\Exception $e) {}
                                }
                            }
                        }

                        /* Перемещение файла между коллаборациями */
                        if ($UserFile_OLD_collaboration_id && $folder_parent_collaboration_id && ($folder_parent_collaboration_id != $UserFile_OLD_collaboration_id)) {
                            if (isset($source_relative_path)) {
                                /* все сложно ))) */

                                /* внутри бывшей коллаборации файл должен быть удален */
                                $event_data_redis_1 = [];
                                $UserColleagues = UserColleagues::find()
                                    ->where([
                                        'collaboration_id' => $UserFile_OLD_collaboration_id,
                                        'colleague_status' => UserColleagues::STATUS_JOINED,
                                    ])
                                    //->andWhere('(user_id!=:user_id) AND (user_id IS NOT NULL)', [':user_id' => $UserNode->user_id])
                                    ->andWhere('(user_id IS NOT NULL)')
                                    ->all();
                                /** @var \common\models\UserColleagues $colleague */
                                foreach ($UserColleagues as $colleague) {

                                    if ($colleague->user_id == $UserNode->user_id) {

                                        /* создаем репорт для овнера о том что сделал сам овнер */
                                        if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                            $UserData = Users::findIdentity($colleague->user_id);
                                            if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                                $Report = ColleaguesReports::createNewReport(
                                                    $event_data[0],
                                                    $colleague,
                                                    $UserNode
                                                );
                                            }
                                        }

                                    } else {
                                        $UserFile_FindID = UserFiles::findOne([
                                            'file_uuid' => $this->folder_uuid,
                                            'is_folder' => UserFiles::TYPE_FOLDER,
                                            'user_id' => $colleague->user_id,
                                            'is_deleted' => UserFiles::FILE_UNDELETED,
                                        ]);
                                        //var_dump($UserFile_OLD_collaboration_id); exit;
                                        if ($UserFile_FindID) {
                                            $last_event_id = UserFileEvents::find()
                                                ->andWhere(['file_id' => $UserFile_FindID->file_id])
                                                ->max('event_id');

                                            $data['folder_uuid'] = $this->folder_uuid;
                                            $data['last_event_id'] = $last_event_id;
                                            $data['event_creator_user_id'] = $UserNode->user_id;
                                            $data['event_creator_node_id'] = $UserNode->node_id;

                                            $model = new NodeApi(['folder_uuid', 'last_event_id']);
                                            $model->load(['NodeApi' => $data]);
                                            $model->validate();
                                            $answer = $model->folder_event_delete(
                                                self::registerNodeFM(Users::findIdentity($colleague->user_id)),
                                                true,
                                                false,
                                                false
                                            );
                                            //var_dump($answer); exit;

                                            /* собираем евенты */
                                            if (isset($answer['event_data'])) {
                                                foreach ($answer['event_data'] as $k => $v) {
                                                    $event_data_redis_1[] = $v;

                                                    /* создаем репорт для овнера */
                                                    if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                                        $UserData = Users::findIdentity($colleague->user_id);
                                                        if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                                            $Report = ColleaguesReports::createNewReport(
                                                                $v,
                                                                $colleague,
                                                                $UserNode
                                                            );
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                                /* Отправка данных на вебсокет */
                                /* Отправляем в редис */
                                if (isset($event_data_redis_1) && sizeof($event_data_redis_1)) {
                                    try {
                                        $this->redis->publish("collaboration:{$UserFile_OLD_collaboration_id}:fsevent", Json::encode($event_data_redis_1));
                                        $this->redis->save();
                                    } catch (\Exception $e) {}
                                }


                                /* внутри новой коллаборации папка должна быть создана со всем ее содержимым (рекурсивно) */
                                $event_data_redis_2 = [];
                                $UserColleagues = UserColleagues::find()
                                    ->where([
                                        'collaboration_id' => $folder_parent_collaboration_id,
                                        'colleague_status' => UserColleagues::STATUS_JOINED,
                                    ])
                                    //->andWhere('(user_id!=:user_id) AND (user_id IS NOT NULL)', [':user_id' => $UserNode->user_id])
                                    ->andWhere('(user_id IS NOT NULL)')
                                    ->all();
                                /** @var \common\models\UserColleagues $colleague */
                                foreach ($UserColleagues as $colleague) {

                                    if ($colleague->user_id == $UserNode->user_id) {

                                        /* создаем репорт для овнера о том что сделал сам овнер */
                                        if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                            $UserData = Users::findIdentity($colleague->user_id);
                                            if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                                $Report = ColleaguesReports::createNewReport(
                                                    $event_data[0],
                                                    $colleague,
                                                    $UserNode
                                                );
                                            }
                                        }

                                    } else {
                                        $data['copy_folder_uuid'] = $this->folder_uuid;
                                        $data['folder_uuid'] = $this->folder_uuid;
                                        $data['parent_folder_uuid'] = $folder_parent_uuid;
                                        $data['folder_name'] = $UserFile->file_name;
                                        $data['collaboration_id'] = $folder_parent_collaboration_id;
                                        $data['event_creator_user_id'] = $UserNode->user_id;
                                        $data['event_creator_node_id'] = $UserNode->node_id;
                                        //var_dump($data);exit;

                                        /*
                                        $model = new NodeApi(['copy_folder_uuid', 'folder_uuid', 'folder_name']);
                                        $model->load(['NodeApi' => $data]);
                                        $model->validate();
                                        */

                                        $UserSource = Users::getPathNodeFS($UserFile->user_id);
                                        $source_full_path = $UserSource->_full_path . DIRECTORY_SEPARATOR . $source_relative_path;
                                        //var_dump($source_full_path); exit;
                                        //$event_store = [];
                                        UserFiles::folderCopy(
                                            $source_full_path,
                                            self::registerNodeFM(Users::findIdentity($colleague->user_id)),
                                            $folder_parent_uuid,
                                            $folder_parent_collaboration_id,
                                            $event_data_redis_2
                                        );
                                        //var_dump($event_store);
                                        //exit;

                                        /* собираем евенты */
                                        /*
                                        if (isset($event_store) && sizeof($event_store)) {
                                            foreach ($event_store as $k => $v) {
                                                $event_data_redis[] = $v;
                                            }
                                        }
                                        */

                                        /* создаем репорт для овнера */
                                        if ($colleague->colleague_permission == UserColleagues::PERMISSION_OWNER) {
                                            $UserData = Users::findIdentity($colleague->user_id);
                                            if ($UserData && $UserData->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                                                $Report = ColleaguesReports::createNewReport(
                                                    $event_data_redis_2[0],
                                                    $colleague,
                                                    $UserNode
                                                );
                                            }
                                        }
                                    }
                                }

                                /* Отправка данных на вебсокет */
                                /* Отправляем в редис */
                                if (isset($event_data_redis_2) && sizeof($event_data_redis_2)) {
                                    try {
                                        $this->redis->publish("collaboration:{$folder_parent_collaboration_id}:fsevent", Json::encode($event_data_redis_2));
                                        $this->redis->save();
                                    } catch (\Exception $e) {}
                                }
                            }
                        }

                    }

                    /* Создание файла со служебной инфой для отображения в ФС вебФМ */
                    $_new_relativePath = UserFiles::getFullPath($UserFile);
                    if (!$internalNodeFM) {
                        $old_file_name = $User->_full_path . DIRECTORY_SEPARATOR . $_old_relativePath;
                        $new_file_name = $User->_full_path . DIRECTORY_SEPARATOR . $_new_relativePath;
                        //var_dump($old_file_name); echo "<br />";
                        //var_dump($new_file_name); echo "<br />";
                        if (file_exists($old_file_name) && !file_exists($new_file_name)) {
                            FileSys::mkdir(dirname($new_file_name), UserFiles::CHMOD_DIR, true);
                            FileSys::move($old_file_name, $new_file_name);
                        }

                        $folder_info_file = $new_file_name . DIRECTORY_SEPARATOR . UserFiles::DIR_INFO_FILE;
                        UserFiles::createFileInfo($folder_info_file, $UserFile);
                    }

                    /* шаринг. меняем групповую шару */
                    if ($is_moved) {
                        /* если папка перемещена а НЕ переименована */
                        if ($parent) {
                            /* если есть перент (НЕ корень ФС) */
                            if (!$UserFile->is_shared) {
                                /* если переносимая папка НЕ является главной папкой шары */
                                if ($parent->share_group_hash && $old_share_group_hash && $old_share_group_hash != $parent->share_group_hash) {
                                    /* выносим чилдрена из одной расшаренной папки в другую расшаренную папку */
                                    $tmp = UserFiles::changeChildrenGroupHash($parent, $parent->share_group_hash, $parent->collaboration_id);
                                } elseif (!$parent->share_group_hash && $old_share_group_hash) {
                                    /* выносим чидрена из расшаренной папки в нерасшаренную */
                                    $tmp = UserFiles::changeChildrenGroupHash($parent, null, $parent->collaboration_id);
                                } elseif ($parent->share_group_hash && !$old_share_group_hash) {
                                    /* выносим чидрена из нерасшаренной папки в расшаренную */
                                    $tmp = UserFiles::changeChildrenGroupHash($parent, $parent->share_group_hash, $parent->collaboration_id);
                                } else {
                                    /* выносим чидрена из нерасшаренной папки в нерасшаренную */
                                    $need_change_collaboration_id = true;
                                }
                            } else {
                                /* если переносимая папка является главной папкой шары */
                                if ($parent->share_group_hash && $old_share_group_hash) {
                                    /* переносим расшаренную папку в другую расшаренную папку */
                                    $tmp = UserFiles::changeChildrenGroupHash($parent, $parent->share_group_hash, $parent->collaboration_id);
                                } else {
                                    /* переносим расшаренную папку в нерасшаренную папку */
                                    $need_change_collaboration_id = true;
                                }
                            }

                            /* смена ид коллаборации если в случаях если она не была изменена при смене шарингов */
                            if (isset($need_change_collaboration_id)) {
                                if ($parent->collaboration_id !== $UserFile->collaboration_id) {
                                    $tmp = UserFiles::changeChildrenCollaborationID($parent);
                                }
                            }
                        } else {
                            /* если НЕТ перента (корень ФС) */
                            if (!$UserFile->is_shared) {
                                /* если переносимая папка НЕ является главной папкой шары */
                                if ($old_share_group_hash) {
                                    /* выносим чилдрена из расшаренной папки в корень */
                                    $tmp = UserFiles::changeChildrenGroupHash($UserFile, null, $UserFile->collaboration_id);
                                } else {
                                    /* выносим чидрена из нерасшаренной папки в корень */
                                    $need_change_collaboration_id = true;
                                }
                            } else {
                                /* если переносимая папка является главной папкой шары */
                                $need_change_collaboration_id = true;
                            }

                            /* смена ид коллаборации если в случаях если она не была изменена при смене шарингов */
                            if (isset($need_change_collaboration_id)) {
                                if ($UserFile->collaboration_id) {
                                    $tmp = UserFiles::changeChildrenCollaborationID($UserFile);
                                }
                            }
                        }
                    }

                    /* успешное завершение транзакции */
                    $transaction->commit();

                    $ret = [
                        'result' => "success",
                        'info' => "folder-move-event stored successfully. Successfully moveed {$_old_relativePath} to {$_new_relativePath}",
                        'data' => [
                            'event_id'   => $UserFileEvent->event_id,
                            'event_uuid' => $UserFileEvent->event_uuid,
                            'timestamp'  => $UserFileEvent->event_timestamp,
                        ],
                    ];

                    /* Отправка евента на редис */
                    if (!in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT])) {
                        try {
                            $this->redis->publish("user:{$UserNode->user_id}:fs_events", Json::encode($event_data));
                            $this->redis->save();
                        } catch (\Exception $e) {
                            RedisSafe::createNewRecord(
                                RedisSafe::TYPE_FS_EVENTS,
                                $UserNode->user_id,
                                null,
                                Json::encode([
                                    'action'           => 'fs_events',
                                    'chanel'           => "user:{$UserNode->user_id}:fs_events",
                                    'user_id'          => $UserNode->user_id,
                                ])
                            );
                        }
                    }

                    /* Подготовка данных об евенте в общий массив возврата, если нужно */
                    if ($sendEventToSignal) {
                        $ret['event_data'] = $event_data;
                    }

                    /* Освобождаем от блокировки по мутексу, нужно для ФМ,
                     * который за один проход скрипта может делать несколько евентов цикле,
                     * а иначе он не может например удалить сразу две папки*/
                    $this->mutex->release($mutex_name);
                    if (isset($mutex_collaboration_name)) { $this->mutex->release($mutex_collaboration_name); }

                    return $ret;
                } else {
                    $transaction->rollBack();

                    return [
                        'result'  => "error",
                        'errcode' => self::ERROR_DATABASE_FAILURE,
                        'info' => "An internal server error occurred.",
                        'debug'    => $UserFile->getErrors(),
                    ];
                }
            } else {
                $transaction->rollBack();

                return [
                    'result'  => "error",
                    'errcode' => self::ERROR_DATABASE_FAILURE,
                    'info' => "An internal server error occurred.",
                    'debug'    => $UserFileEvent->getErrors(),
                ];
            }
        } catch (\Exception $e) {

            return [
                'result'  => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug'    => $e,
            ];
        }
    }

    /**
     * Метод для выдачи списка файлов с привязкой к событиям
     * @param $UserNode \common\models\UserNode
     * @return array
     */
    public function file_list($UserNode)
    {
        /*
        SELECT DISTINCT t2.file_id, t1.*
        FROM {{%user_files}} as t1
        INNER JOIN {{%user_file_events}} as t2 ON t1.file_id=t2.file_id
        WHERE (t1.user_id=98)
        AND (t1.last_event_type <> 2) #delete
        AND ( t2.event_id>3)
        */
        $data = UserFiles::find()
            ->alias('t1')
            ->innerJoin('{{%user_file_events}} as t2', 't1.file_id=t2.file_id')
            ->leftJoin('{{%user_files}} as t3', 't1.file_parent_id=t3.file_id')
            ->select([
                't1.file_id',
                't1.file_uuid',
                't1.file_name as file_name',
                't1.file_size',
                't1.file_lastatime',
                't1.file_created',
                't1.file_updated',
                't1.file_parent_id',
                't3.file_uuid as file_parent_uuid',
                't1.is_folder',
            ])
            ->distinct()
            ->andWhere('t1.user_id=:user_id', [':user_id' => $UserNode->user_id])
            ->andWhere('t1.last_event_type <> :last_event_type', [':last_event_type' => UserFileEvents::TYPE_DELETE])
            ->andWhere('t2.event_id > :last_event_id', [':last_event_id' => $this->last_event_id])
            ->asArray()
            ->all();
        foreach ($data as $k => $v) {
            $data[$k]['is_folder'] = intval($data[$k]['is_folder']) == 1 ? true : false;
        }
        return [
            'result' => "success",
            'info'   => "list files in data",
            'data'   => $data
        ];
    }

    /**
     * Метод для выдачи списка событий по файлам
     *
     * @param $UserNode \common\models\UserNode
     * @return array
     */
    public function file_events($UserNode)
    {
        $user_id = $UserNode->user_id;
        $node_id = $UserNode->node_id;
        $last_event_id = $this->last_event_id;

        $User = Users::findIdentity($user_id);
        if (!$User) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_USER_NOT_FOUND,
                'info'   => "User not found",
                'debug'   => "User not found for user_id={$user_id}",
            ];
        }
        if (in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT])) {
            $where_node_filter = "AND (t1.node_id = {$node_id} or (t1.user_id = {$user_id} and t1.erase_nested = 1))";
        } else {
            $where_node_filter = ""; //"/* no need node filter :node_id */";
        }

        $query = "SELECT count(*) as count_check
                  FROM {{%user_file_events}} as t1
                  INNER JOIN {{%user_files}} as t2 ON t1.file_id=t2.file_id and t1.user_id = t2.user_id
                  WHERE (t1.user_id = :user_id)
                  AND (t1.event_id < :last_event_id)
                  AND (t1.event_id > :checked_event_id)
                  {$where_node_filter}
                  AND (
                    (/*TODO: probably the condition will be removed after updating all records*/
                      t2.first_event_id is null or t2.last_event_id is null
                    )
                    OR
                    (
                      not(t2.is_outdated = :FILE_OUTDATED
                          AND (t1.event_type = :TYPE_DELETE OR t2.is_deleted = :FILE_DELETED))
                      AND
                      (
                        (
                          t2.is_folder = :TYPE_FOLDER
                          AND t1.event_id = t2.last_event_id
                        )
                        OR
                        (:node_without_backup = 1
                          AND t1.event_id = t2.last_event_id
                          AND t2.is_deleted = :FILE_UNDELETED
                        )
                        OR
                        (:node_without_backup = 0)
                      )
                    )
                  )";
        //$this->checked_event_id
        //$this->events_count_check
        $count_check = Yii::$app->db->createCommand($query, [
            'user_id' => $user_id,
            'last_event_id' => $last_event_id,
            'checked_event_id' => $this->checked_event_id,
            'TYPE_DELETE'      => UserFileEvents::TYPE_DELETE,
            'FILE_OUTDATED'    => UserFiles::FILE_OUTDATED,
            'FILE_DELETED'    => UserFiles::FILE_DELETED,
            'FILE_UNDELETED'    => UserFiles::FILE_UNDELETED,
            'TYPE_FOLDER'      => UserFiles::TYPE_FOLDER,
            'node_without_backup' => $this->node_without_backup,
        ])->queryOne();

        /**
         * если сверка не прошла - нужны все евенты event_id > checked_event_id с учетом групп
         * если сверка прошла - нужны все евенты с event_id > last_event_id с учетом групп
         * если две верхние выборки вернули ноль евентов - нужен один евент с event_id = last_event_id
         */

        $count_check['count_check'] = intval($count_check['count_check']);
        if ($count_check['count_check'] != $this->events_count_check) {
            $query = "SELECT
                        t1.event_id,
                        t1.event_uuid,
                        t1.event_type,
                        t1.event_timestamp as timestamp,
                        t1.last_event_id,
                        t2.file_uuid as uuid,
                        extract(epoch from t2.file_created)::bigint as file_created,
                        t1.diff_file_uuid,
                        t1.diff_file_size,
                        t1.rev_diff_file_uuid,
                        t1.rev_diff_file_size,
                        t3.file_uuid as parent_folder_uuid,
                        t2.file_name as file_name,
                        t2.file_size as file_size,
                        t2.is_folder as is_folder,
                        t2.is_owner as is_owner,
                        t2.is_collaborated as is_collaborated,
                        t1.file_hash_before_event,
                        t1.file_hash as file_hash_after_event,
                        t1.file_hash as hash,
                        t1.file_name_before_event,
                        t1.file_name_after_event,
                        t1.file_size_before_event,
                        t1.file_size_after_event,
                        t1.erase_nested
                      FROM {{%user_file_events}} as t1
                      INNER JOIN {{%user_files}} as t2 ON t1.file_id=t2.file_id and t1.user_id = t2.user_id
                      LEFT JOIN {{%user_files}} as t3 ON t1.parent_after_event=t3.file_id and t1.user_id = t3.user_id
                      WHERE t1.user_id = :user_id
                      {$where_node_filter}
                      AND (
                        (/*TODO: probably the condidtion will be removed after updating all records*/
                          t2.first_event_id is null or t2.last_event_id is null
                        )
                        OR
                        (
                          (/*file is known*/
                            t2.first_event_id <= :checked_event_id
                          )
                          OR
                          (/*file is not known*/
                            t2.first_event_id > :checked_event_id
                            AND (
                              not(t2.is_outdated = :FILE_OUTDATED
                                  AND (t1.event_type = :TYPE_DELETE OR t2.is_deleted = :FILE_DELETED))
                              AND
                              (
                                (
                                  t2.is_folder = :TYPE_FOLDER
                                  AND t1.event_id = t2.last_event_id
                                )
                                OR
                                (:node_without_backup = 1
                                  AND t1.event_id = t2.last_event_id
                                  AND t2.is_deleted = :FILE_UNDELETED
                                )
                                OR
                                (:node_without_backup = 0)
                              )
                            )
                          )
                        )
                      )
                      AND (
                        t1.event_id > :checked_event_id
                        OR t1.event_group_id IN (
                              SELECT DISTINCT ON (event_group_id) t1.event_group_id
                              FROM {{%user_file_events}} as t1
                              WHERE t1.user_id = :user_id
                              {$where_node_filter}
                              AND t1.event_id > :checked_event_id
                        )
                      )
                      ORDER BY t1.event_id ASC
                      LIMIT :limit OFFSET :offset";
            $data = Yii::$app->db
                ->createCommand($query, [
                    'user_id'          => $user_id,
                    'checked_event_id' => $this->checked_event_id,
                    'limit'            => $this->limit,
                    'offset'           => $this->offset,
                    'TYPE_DELETE'      => UserFileEvents::TYPE_DELETE,
                    'FILE_OUTDATED'    => UserFiles::FILE_OUTDATED,
                    'FILE_DELETED'    => UserFiles::FILE_DELETED,
                    'FILE_UNDELETED'    => UserFiles::FILE_UNDELETED,
                    'TYPE_FOLDER'      => UserFiles::TYPE_FOLDER,
                    'node_without_backup' => $this->node_without_backup,
                ])
                ->queryAll();
        } else {
            $query = "SELECT
                        t1.event_id,
                        t1.event_uuid,
                        t1.event_type,
                        t1.event_timestamp as timestamp,
                        t1.last_event_id,
                        t2.file_uuid as uuid,
                        extract(epoch from t2.file_created)::bigint as file_created,
                        t1.diff_file_uuid,
                        t1.diff_file_size,
                        t1.rev_diff_file_uuid,
                        t1.rev_diff_file_size,
                        t3.file_uuid as parent_folder_uuid,
                        t2.file_name as file_name,
                        t2.file_size as file_size,
                        t2.is_folder as is_folder,
                        t2.is_owner as is_owner,
                        t2.is_collaborated as is_collaborated,
                        t1.file_hash_before_event,
                        t1.file_hash as file_hash_after_event,
                        t1.file_hash as hash,
                        t1.file_name_before_event,
                        t1.file_name_after_event,
                        t1.file_size_before_event,
                        t1.file_size_after_event,
                        t1.erase_nested
                      FROM {{%user_file_events}} as t1
                      INNER JOIN {{%user_files}} as t2 ON t1.file_id=t2.file_id and t1.user_id = t2.user_id
                      LEFT JOIN {{%user_files}} as t3 ON t1.parent_after_event=t3.file_id and t1.user_id = t3.user_id
                      WHERE t1.user_id = :user_id
                      {$where_node_filter}
                      AND (
                        (/*TODO: probably the condidtion will be removed after updating all records*/
                          t2.first_event_id is null or t2.last_event_id is null
                        )
                        OR
                        (
                          (/*file is known*/
                            t2.first_event_id <= :last_event_id
                          )
                          OR
                          (/*file is not known*/
                            t2.first_event_id > :last_event_id
                            AND (
                              not(t2.is_outdated = :FILE_OUTDATED
                                  AND (t1.event_type = :TYPE_DELETE OR t2.is_deleted = :FILE_DELETED))
                              AND
                              (
                                (
                                  t2.is_folder = :TYPE_FOLDER
                                  AND t1.event_id = t2.last_event_id
                                )
                                OR
                                (:node_without_backup = 1
                                  AND t1.event_id = t2.last_event_id
                                  AND t2.is_deleted = :FILE_UNDELETED
                                )
                                OR
                                (:node_without_backup = 0)
                              )
                            )
                          )
                        )
                      )
                      AND (
                        t1.event_id > :last_event_id
                        /*
                        OR (
                          t1.event_id < :last_event_id
                          AND
                          t1.event_group_id IN (
                                SELECT DISTINCT ON (event_group_id) t1.event_group_id
                                FROM {{%user_file_events}} as t1
                                WHERE t1.user_id = :user_id
                                {$where_node_filter}
                                AND t1.event_id > :last_event_id
                          )
                          AND (
                                t1.event_group_id <> (SELECT event_group_id FROM {{%user_file_events}} WHERE event_id = :last_event_id)
                                OR
                                (SELECT event_group_id FROM {{%user_file_events}} WHERE event_id = :last_event_id) IS NULL
                          )
                        )
                        */
                      )
                      ORDER BY t1.event_id ASC
                      LIMIT :limit OFFSET :offset";
            $data = Yii::$app->db
                ->createCommand($query, [
                    'user_id'       => $user_id,
                    'last_event_id' => $this->last_event_id,
                    'limit'         => $this->limit,
                    'offset'        => $this->offset,
                    'TYPE_DELETE'      => UserFileEvents::TYPE_DELETE,
                    'FILE_OUTDATED'    => UserFiles::FILE_OUTDATED,
                    'FILE_DELETED'    => UserFiles::FILE_DELETED,
                    'FILE_UNDELETED'    => UserFiles::FILE_UNDELETED,
                    'TYPE_FOLDER'      => UserFiles::TYPE_FOLDER,
                    'node_without_backup' => $this->node_without_backup,
                ])
                ->queryAll();
        }
        //var_dump($data);exit;

        if (!sizeof($data)) {
            $query = "SELECT
                        t1.event_id,
                        t1.event_uuid,
                        t1.event_type,
                        t1.event_timestamp as timestamp,
                        t1.last_event_id,
                        t2.file_uuid as uuid,
                        extract(epoch from t2.file_created)::bigint as file_created,
                        t1.diff_file_uuid,
                        t1.diff_file_size,
                        t1.rev_diff_file_uuid,
                        t1.rev_diff_file_size,
                        t3.file_uuid as parent_folder_uuid,
                        t2.file_name as file_name,
                        t2.file_size as file_size,
                        t2.is_folder as is_folder,
                        t2.is_owner as is_owner,
                        t2.is_collaborated as is_collaborated,
                        t1.file_hash_before_event,
                        t1.file_hash as file_hash_after_event,
                        t1.file_hash as hash,
                        t1.file_name_before_event,
                        t1.file_name_after_event,
                        t1.file_size_before_event,
                        t1.file_size_after_event,
                        t1.erase_nested
                      FROM {{%user_file_events}} as t1
                      INNER JOIN {{%user_files}} as t2 ON t1.file_id=t2.file_id
                      LEFT JOIN {{%user_files}} as t3 ON t2.file_parent_id=t3.file_id
                      WHERE t1.user_id = :user_id
                      {$where_node_filter}
                      AND t1.event_id = :last_event_id
                      ORDER BY t1.event_id ASC";

            $data = Yii::$app->db
                ->createCommand($query, [
                    'user_id'       => $user_id,
                    'last_event_id' => $this->last_event_id,
                ])
                ->queryAll();
        }

        foreach ($data as $k=>$v) {
            $data[$k]['checked'] = true;
            $data[$k]['erase_nested'] = ($data[$k]['erase_nested'] == UserFileEvents::ERASE_NESTED_TRUE);
            $data[$k]['event_type'] = strtolower(UserFileEvents::getType($data[$k]['event_type']));
            $data[$k]['outdated'] = (intval($data[$k]['timestamp']) >= (time() - Preferences::getValueByKey('RestorePatchTTL', 2592000, 'int')))
                ? false
                : true;
        }

        return [
            'result' => "success",
            'info'   => "list events in data",
            'data'   => $data,
            //'where_node_filter' => $where_node_filter,
            'license_type' => $User->license_type,
            'user_id' => $User->user_id,
            //'phpversion' => phpversion(),
        ];
    }

    /**
     * Метод для регистрации события готовности патча
     * @param \common\models\UserNode $UserNode
     * @param \common\models\Users $User
     * @return array
     */
    public function patch_ready($UserNode, $User)
    {
        /* Поиск хотя бы одного евента с таким дифом для пользователя ноды */
        /*
        $UserFileEvent = UserFileEvents::find()
            ->where(['user_id' => $UserNode->user_id])
            ->andWhere('(diff_file_uuid = :diff_uuid) OR (rev_diff_file_uuid = :diff_uuid)', ['diff_uuid' => $this->diff_uuid])
            ->one();
        if (!$UserFileEvent) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_EVENT_NOT_FOUND,
                'info' => "Event with diff_file_uuid={$this->diff_uuid} or rev_diff_file_uuid={$this->diff_uuid} not found for user_id={$UserNode->user_id}."
            ];
        }
        UserFileEvents::updateAll(['diff_file_size'     => $this->diff_size], ['diff_file_uuid'     => $this->diff_uuid]);
        UserFileEvents::updateAll(['rev_diff_file_size' => $this->diff_size], ['rev_diff_file_uuid' => $this->diff_uuid]);
        */
        $query = "
            WITH ed_on as (
              --direct events for source user
              SELECT event_id, file_id, user_id
              FROM {{%user_file_events}}
              WHERE user_id = :user_id  --@@ user_id
              AND diff_file_uuid = :diff_uuid  --@@ diff_uuid
            ),
            er_on as (
              --reverse events for source user
              SELECT event_id, file_id, user_id
              FROM {{%user_file_events}}
              WHERE user_id = :user_id
              AND rev_diff_file_uuid = :diff_uuid  --@@ diff_uuid
            ),
            ff as (
              --files for which events will be updates
              SELECT DISTINCT file_id, user_id
              FROM ed_on
              UNION
              SELECT DISTINCT file_id, user_id
              FROM er_on
            ),
            co as (
              --collaborations for the files
              SELECT distinct collaboration_id
              FROM {{%user_files}}
              WHERE file_id in (SELECT file_id FROM ff)
              AND collaboration_id is not null
            ),
            us_co as (
              --colleagues for the files
              SELECT user_id
              FROM {{%user_colleagues}}
              WHERE collaboration_id in (SELECT collaboration_id FROM co)
              AND user_id <> :user_id  --@@ user_id
            ),
            ed_co as (
              --direct events of the collaborated files
              SELECT event_id
              FROM {{%user_file_events}}
              WHERE user_id in (SELECT user_id FROM us_co)
              AND diff_file_uuid = :diff_uuid  --@@ diff_uuid
            ),
            er_co as (
              --reverse events of the collaborated files
              SELECT event_id
              FROM {{%user_file_events}}
              WHERE user_id in (SELECT user_id FROM us_co)
              AND rev_diff_file_uuid = :diff_uuid  --@@ diff_uuid
            ),
            ed_all as (
              --all direct events to update
              SELECT ed_on.event_id
              FROM ed_on
              UNION
              SELECT ed_co.event_id
              FROM ed_co
            ),
            er_all as (
              --all reverse events to update
              SELECT er_on.event_id
              FROM er_on
              UNION
              SELECT er_co.event_id
              FROM er_co
            ),
            up_ed as (
              --update direct events
              UPDATE {{%user_file_events}}
              SET diff_file_size = :diff_size  --@@ diff_size
              WHERE event_id in (SELECT event_id FROM ed_all)
            ),
            up_er as (
              --update reverse events
              UPDATE {{%user_file_events}}
              SET rev_diff_file_size = :diff_size  --@@ diff_size
              WHERE event_id in (SELECT event_id FROM er_all)
            )
            --return colleagues for the files
            SELECT user_id FROM us_co
            UNION
            SELECT user_id FROM ff
        ";
        $res = Yii::$app->db->createCommand($query, [
            'user_id' => $UserNode->user_id,
            'diff_uuid' => $this->diff_uuid,
            'diff_size' => $this->diff_size,
        ])->queryAll();

        if (!sizeof($res)) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_EVENT_NOT_FOUND,
                'info' => "Event not found.",
                'debug' => "Event with diff_file_uuid={$this->diff_uuid} or rev_diff_file_uuid={$this->diff_uuid} not found for user_id={$UserNode->user_id}.",
            ];
        }

        /* Отправка евента на редис */
        if (!in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT])) {
            try {
                foreach ($res as $v) {
                    $this->redis->publish(
                        "user:" . $v['user_id'] . ":patches_info",
                        Json::encode([
                            'source_node_id' => $UserNode->node_id,
                            'patches_info' => [[
                                'diff_uuid' => $this->diff_uuid,
                                'diff_size' => $this->diff_size
                            ]]
                        ])
                    );
                }
                $this->redis->save();
            } catch (\Exception $e) {
                foreach ($res as $v) {
                    RedisSafe::createNewRecord(
                        RedisSafe::TYPE_PATCHES_INFO,
                        $v['user_id'],
                        null,
                        Json::encode([
                            'action'           => 'patches_info',
                            'chanel'           => "user:" . $v['user_id'] . ":patches_info",
                            'user_id'          => $v['user_id'],
                        ])
                    );
                }
            }
        }

        return [
            'result' => "success",
            'info'   => "Event updated successfully",
        ];
    }

    /**
     * Метод получения информации о размерах/готовности патчей.
     *
     * @param $UserNode \common\models\UserNode
     * @return array
     */
    public function patches_info($UserNode)
    {
        $direct = UserFileEvents::find()
            ->addSelect([
                //'event_id',
                'diff_uuid' => 'diff_file_uuid',
                'diff_size' => 'diff_file_size',
            ])
            ->where([
                'user_id' => $UserNode->user_id,
                'event_type' => UserFileEvents::TYPE_UPDATE,
            ])
            ->andWhere('(event_id >= :direct_patch_event_id) AND (last_event_id <= :last_event_id) AND (diff_file_size > 0)', [
                'direct_patch_event_id' => $this->direct_patch_event_id,
                'last_event_id'         => $this->last_event_id,
            ]);
            //->all();

        $reversed = UserFileEvents::find()
            ->addSelect([
                //'event_id',
                'diff_uuid' => 'rev_diff_file_uuid',
                'diff_size' => 'rev_diff_file_size',
            ])
            ->where([
                'user_id' => $UserNode->user_id,
                'event_type' => UserFileEvents::TYPE_UPDATE,
            ])
            ->andWhere('(event_id >= :reversed_patch_event_id) AND (last_event_id <= :last_event_id) AND (rev_diff_file_size > 0)', [
                'reversed_patch_event_id' => $this->reversed_patch_event_id,
                'last_event_id'         => $this->last_event_id,
            ]);
            //->all();

        if ($this->direct_patch_event_id > 0 && $this->reversed_patch_event_id > 0) {
            $res = $direct->union($reversed)->asArray()->all();
        } elseif ($this->direct_patch_event_id > 0) {
            $res = $direct->asArray()->all();
        } elseif ($this->reversed_patch_event_id > 0) {
            $res = $reversed->asArray()->all();
        } else {
            $res = null;
        }

        return [
            'result' => "success",
            'info'   => "patches info in data",
            'data'   => $res,
            /*
            'data'   => [
                self::DIFF_TYPE_DIRECT   => $direct,
                self::DIFF_TYPE_REVERSED => $reversed,
            ],
            */
        ];
    }

    /**
     * Метод для скачивания файла нодой
     * @param $UserNode \common\models\UserNode
     * @return \common\models\UserUploads
     */
    public function download($UserNode)
    {
        $UserUploads = UserUploads::findOne(['upload_id' => $this->upload_id]);
        if ($UserUploads) {
            if ($UserUploads->user_id == $UserNode->user_id) {
                //$User = Users::getPathNodeFS($UserNode->user_id);
                /*
                header('X-Accel-Redirect: ' . $UserUploads->upload_path);
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=' . FileSys::basename($UserUploads->upload_path));
                header('Content-Length: ' . $UserUploads->upload_size);
                */
                //var_dump("/" . FileSys::basename(Yii::$app->params['nodeVirtualFS']) . "/{$User->_relative_path}/{$UserUploads->upload_path}");exit;
                Yii::$app->response->format = Response::FORMAT_RAW;
                $headers = Yii::$app->response->headers;
                $headers->removeAll();
                $headers->add('Cache-Control', 'no-store, no-cache, must-revalidate');
                $headers->add('Expires', date("r"));
                $headers->add('X-Accel-Redirect', "/" . Yii::$app->params['userUploadsDir_for_XAccelRedirect'] . "/{$UserUploads->upload_saved_name}");
                $headers->add('Content-Type', 'application/octet-stream');
                $headers->add('Content-Disposition', 'attachment; filename=' . FileSys::basename($UserUploads->upload_path));
                //$headers->add('Content-Length', ''.$UserUploads->upload_size);
                Yii::$app->response->send();
                exit;
            } else {
                Yii::$app->response->setStatusCode(404);
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_UPLOADED_FILE_NOT_FOUND,
                    'info' => "File not found (user_id mismatch)",
                ];
            }
        } else {
            Yii::$app->response->setStatusCode(404);
            return [
                'result' => "error",
                'errcode' => self::ERROR_UPLOADED_FILE_NOT_FOUND,
                'info' => "File not found",
            ];
        }
    }

    /**
     * @param \common\models\UserNode $UserNode
     * @return array
     */
    public function uploaded_delete($UserNode)
    {
        $transaction = Yii::$app->db->beginTransaction();

        $query = "DELETE FROM {{%user_uploads}}
                  WHERE (upload_id = :upload_id)
                  AND (user_id = :user_id)
                  RETURNING upload_saved_name";
        $res = Yii::$app->db->createCommand($query, [
            'upload_id' => $this->upload_id,
            'user_id'   => $UserNode->user_id,
        ])->queryOne();

        if (!is_array($res) || !sizeof($res)) {
            $transaction->rollBack();
            return [
                'result' => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info' => "File does not exist.",
                'debug' => "File with upload_id='{$this->upload_id}' does not exist.",
            ];
        }

        try {
            $this->redis->publish("user:{$UserNode->user_id}:upload_cancel", Json::encode($this->upload_id));
            $this->redis->save();
        } catch (\Exception $e) {
            RedisSafe::createNewRecord(
                RedisSafe::TYPE_UPLOAD_EVENTS,
                $UserNode->user_id,
                null,
                Json::encode([
                    'action'           => 'upload_cancel',
                    'chanel'           => "user:{$UserNode->user_id}:upload_cancel",
                    'user_id'          => $UserNode->user_id,
                    'upload_id'        => $this->upload_id,
                ])
            );
        }

        @unlink(Yii::$app->params['userUploadsDir'] . DIRECTORY_SEPARATOR . $res['upload_saved_name']);
        $transaction->commit();
        return [
            'result' => "success",
            'info' => "Deleted.",
        ];
    }
    /************************* --- FILE EVENTS --- *************************/


    /************************ +++ SHARE EVENTS +++ *************************/
    /**
     * Метод для шаринга папки ли файла
     * @param \common\models\UserNode $UserNode
     * @param bool $sendEventToSignal
     * @param bool $internalNodeFM
     * @return array
     */
    public function sharing_enable($UserNode, $sendEventToSignal=false, $internalNodeFM=false)
    {
        //(!in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT]))
        $User = Users::findIdentity($UserNode->user_id);
        if (!$User || ($User->license_type == Licenses::TYPE_FREE_DEFAULT)) {
            //s$this->share_ttl = UserFiles::TTL_IMMEDIATELY;
            $this->share_ttl = UserFiles::TTL_WITHOUTEXPIRY;
            $this->share_password = null;
        }

        if (!$this->share_lifetime) { $this->share_lifetime = null; }
        if (!$this->share_password) { $this->share_password = null; }
        if (!$this->share_ttl || $this->share_ttl == UserFiles::TTL_IMMEDIATELY) {
            $this->share_lifetime = null;
        } else {
            $this->share_lifetime = date(SQL_DATE_FORMAT, time() + $this->share_ttl);//
        }

        /* Поиск файла или папки по UUID */
        $UserFile = UserFiles::findOne([
            'file_uuid' => $this->uuid,
            'user_id'   => $UserNode->user_id
        ]);

        /* Если лицензия фришная то по шарингу есть ограничения на шаринг папок */
        if ($User->license_type == Licenses::TYPE_FREE_DEFAULT) {

            /* если пытаются расшарить директорию - это запрещено */
            if ($UserFile->is_folder) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_LICENSE_ACCESS,
                    'info_fm' => Yii::t('app/flash-messages', "license_restriction_share_dir"), //"license-restriction-share-dir",
                    'info' => strip_tags(Yii::t('app/flash-messages', "license_restriction_share_dir")),
                ];
            }

        }

        //$License = Licenses::findByType($User->license_type);

        /* Ограничение расшаривания по размеру файла */
        if (($User->_ucl_max_shares_size > 0) && !$UserFile->is_folder) {
            if ($UserFile->file_size > $User->_ucl_max_shares_size) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_LICENSE_ACCESS,
                    'info' => Yii::t('app/flash-messages', "license_restriction_share_max_size", [
                        'license_max_shares_size' => Functions::file_size_format($User->_ucl_max_shares_size, 0, '', '')
                    ]),
                    'data' => [
                        'license_max_shares_size' => Functions::file_size_format($User->_ucl_max_shares_size, 0, '', ''),
                    ],
                ];
            }
        }

        /* если за сутки расшарено больше файлов чем позволено лицензией, то больше не даем */
        //var_dump($this->only_change_share_settings); exit;
        if (!$this->only_change_share_settings) {
            if ($User->_ucl_shares_count_in24 > 0) {
                if ($User->shares_count_in24 < $User->_ucl_shares_count_in24) {
                    $User->shares_count_in24++;
                    $User->save();
                } else {
                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_LICENSE_ACCESS,
                        //'info' => "license-restriction-3-in24",
                        'info_fm' => Yii::t('app/flash-messages', "license_restriction_3_in24", ['license_shares_count_in24' => $User->_ucl_shares_count_in24]),
                        'info' => strip_tags(Yii::t('app/flash-messages', "license_restriction_3_in24", ['license_shares_count_in24' => $User->_ucl_shares_count_in24])),
                        'data' => [
                            'license_shares_count_in24' => $User->_ucl_shares_count_in24,
                        ],
                    ];
                }
            }
        } else {
            if (!$UserFile->is_shared) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_LICENSE_ACCESS,
                    'info' => 'You try change share-setings for file that not shared. Access denied.',
                ];
            }
            if ($User->license_type == Licenses::TYPE_FREE_DEFAULT) {
                return [
                    'result' => "error",
                    'errcode' => self::ERROR_LICENSE_ACCESS,
                    'info' => "You can't change share-settings for file with Free license.",
                ];
            }
        }

//        $CountSharesForFreeIn24Hours = Preferences::getValueByKey('CountSharesForFreeIn24Hours', 3, 'integer');
//        if ($User->shares_count_in24 < $CountSharesForFreeIn24Hours) {
//            $User->shares_count_in24++;
//            $User->save();
//        } else {
//            return [
//                'result' => "error",
//                'errcode' => self::ERROR_LICENSE_ACCESS,
//                //'info' => "license-restriction-3-in24",
//                'info' => Yii::t('app/flash-messages', "license_restriction_3_in24"),
//            ];
//        }

            /* Если файл, то тогда со всех остальных файлов шара слетает */
//            $OtherShares = UserFiles::find()->where([
//                'user_id'   => $User->user_id,
//                'is_shared' => UserFiles::FILE_SHARED,
//            ])->all();
//            /** @var \common\models\UserFiles $share */
//            foreach ($OtherShares as $share) {
//                $data['uuid'] = $share->file_uuid;
//
//                /* создаем модель и вызываем метод для un-шаринга */
//                $model = new NodeApi(['uuid']);
//                $model->load(['NodeApi' => $data]);
//                //$model->validate();
//                $model->sharing_disable($UserNode, false, false);
//            }


        /* проверка существования исходного файла */
        if (!$UserFile){
            return [
                'result'  => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info'    => "File not found."
            ];
        }

        /* проверка что файл не удаили до этого */
        if ($UserFile->last_event_type == UserFileEvents::TYPE_DELETE) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info'    => "File was deleted. You can't do any actions with this file."
            ];
        }

        /* выбор метода шаринга папка или файл */
        if ($UserFile->is_folder == 1) {
            return $this->folder_share($UserFile, $sendEventToSignal, $internalNodeFM, $UserNode->node_id);
        } else {
            return $this->file_share($UserFile, $sendEventToSignal, $internalNodeFM, $UserNode->node_id);
        }
    }

    /**
     * Метод для file-share
     * @param \common\models\UserFiles $UserFile
     * @param bool $internalNodeFM
     * @param bool $sendEventToSignal
     * @param integer|null $node_id
     * @return array
     */
    private function file_share($UserFile, $sendEventToSignal=false, $internalNodeFM=false, $node_id=null)
    {
        /* Начинаем транзакцию */
        $transaction = Yii::$app->db->beginTransaction();
        $UserFile->is_shared        = UserFiles::FILE_SHARED;
        $UserFile->share_lifetime   = $this->share_lifetime;
        $UserFile->share_ttl_info   = $this->share_ttl;
        if (!$this->share_keep_password) {
            $UserFile->share_password   = $this->share_password;
        }
        $UserFile->share_created    = date(SQL_DATE_FORMAT);
        $UserFile->generate_share_hash();
        $UserFile->share_group_hash = null;
        if ($UserFile->save()) {

            $event_data = [
                'operation' => "sharing_enable",
                'data' => [
                    'uuid'           => $UserFile->file_uuid,
                    'share_hash'     => $UserFile->share_hash,
                    'share_link'     => UserFiles::getShareLink($UserFile->share_hash, false),
                    'share_password' => $UserFile->share_password ? true : false,
                    'share_created'  => $UserFile->share_created,
                    'share_lifetime' => $UserFile->share_lifetime,
                    'share_ttl_info' => $UserFile->share_ttl_info,
                ],
            ];

            /* успешное завершение транзакции */
            $transaction->commit();

            /* Создание файла со служебной инфой для отображения в ФС вебФМ */
            if (!$internalNodeFM) {
                $relativePath = UserFiles::getFullPath($UserFile);
                $User = Users::getPathNodeFS($UserFile->user_id);
                $file_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;

                if (!file_exists($file_name)) {
                    FileSys::touch($file_name, UserFiles::CHMOD_DIR, UserFiles::CHMOD_FILE);
                }
                UserFiles::createFileInfo($file_name, $UserFile);
            }

            $ret = [
                'result' => "success",
                'info'   => "sharing enabled successfully",
                'data'   => [
                    'share_hash'     => $UserFile->share_hash,
                    'share_lifetime' => $UserFile->share_lifetime,
                    'share_ttl_info' => $UserFile->share_ttl_info,
                    'share_password' => $UserFile->share_password,
                    'is_folder'      => false,
                    'share_link'     => UserFiles::getShareLink($UserFile->share_hash, false),
                ],
            ];

            /* Отправка евента на редис */
            try {
                $this->redis->publish("user:{$UserFile->user_id}:share_events", Json::encode($event_data));
                $this->redis->save();
            } catch (\Exception $e) {
                RedisSafe::createNewRecord(
                    RedisSafe::TYPE_SHARING_EVENTS,
                    $UserFile->user_id,
                    $node_id,
                    Json::encode([
                        'action'           => 'share_events',
                        'chanel'           => "user:{$UserFile->user_id}:share_events",
                        'user_id'          => $UserFile->user_id,
                    ])
                );
            }

            /* Подготовка данных об евенте в общий массив возврата, если нужно */
            if ($sendEventToSignal) {
                $ret['event_data'] = $event_data;
            }

            return $ret;
        } else {
            $transaction->rollBack();
            return [
                'result' => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $UserFile->getErrors(),
            ];
        }
    }

    /**
     * Метод для folder-share
     * @param \common\models\UserFiles $UserFile
     * @param bool $internalNodeFM
     * @param bool $sendEventToSignal
     * @param integer|null $node_id
     * @return array
     */
    private function folder_share($UserFile, $sendEventToSignal=false, $internalNodeFM=false, $node_id=null)
    {
        /* Начинаем транзакцию */
        $transaction = Yii::$app->db->beginTransaction();
        $UserFile->is_shared        = UserFiles::FILE_SHARED;
        $UserFile->share_lifetime   = $this->share_lifetime;
        $UserFile->share_ttl_info   = $this->share_ttl;
        if (!$this->share_keep_password) {
            $UserFile->share_password   = $this->share_password;
        }
        $UserFile->share_created    = date(SQL_DATE_FORMAT);
        $UserFile->generate_share_hash();
        $UserFile->share_group_hash = $UserFile->share_hash;
        if ($UserFile->save()) {

            /* шаринг. меняем групповую шару */
            UserFiles::changeChildrenGroupHash($UserFile, $UserFile->share_group_hash, $UserFile->collaboration_id);

            $event_data = [
                'operation' => "sharing_enable",
                'data' => [
                    'uuid'           => $UserFile->file_uuid,
                    'share_hash'     => $UserFile->share_hash,
                    'share_link'     => UserFiles::getShareLink($UserFile->share_hash, true),
                    'share_password' => $UserFile->share_password ? true : false,
                    'share_created'  => $UserFile->share_created,
                    'share_lifetime' => $UserFile->share_lifetime,
                    'share_ttl_info' => $UserFile->share_ttl_info,
                ],
            ];

            /* успешное завершение транзакции */
            $transaction->commit();

            /* Создание файла со служебной инфой для отображения в ФС вебФМ */
            if (!$internalNodeFM) {
                $relativePath = UserFiles::getFullPath($UserFile);
                $User = Users::getPathNodeFS($UserFile->user_id);
                $folder_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;
                if (!file_exists($folder_name)) {
                    FileSys::mkdir($folder_name, UserFiles::CHMOD_DIR, true);
                }
                $folder_info_file = $folder_name . DIRECTORY_SEPARATOR . UserFiles::DIR_INFO_FILE;
                if (!file_exists($folder_info_file)) {
                    FileSys::touch($folder_info_file, UserFiles::CHMOD_DIR, UserFiles::CHMOD_FILE);
                }
                UserFiles::createFileInfo($folder_info_file, $UserFile);
            }

            $ret = [
                'result' => "success",
                'info'   => "sharing enabled successfully",
                'data'   => [
                    'share_hash'     => $UserFile->share_hash,
                    'share_lifetime' => $UserFile->share_lifetime,
                    'share_ttl_info' => $UserFile->share_ttl_info,
                    'share_password' => $UserFile->share_password,
                    'is_folder'      => true,
                    'share_link'     => UserFiles::getShareLink($UserFile->share_hash, true),
                ],
            ];

            /* Отправка евента на редис */
            try {
                $this->redis->publish("user:{$UserFile->user_id}:share_events", Json::encode($event_data));
                $this->redis->save();
            } catch (\Exception $e) {
                RedisSafe::createNewRecord(
                    RedisSafe::TYPE_SHARING_EVENTS,
                    $UserFile->user_id,
                    $node_id,
                    Json::encode([
                        'action'           => 'share_events',
                        'chanel'           => "user:{$UserFile->user_id}:share_events",
                        'user_id'          => $UserFile->user_id,
                    ])
                );
            }

            /* Подготовка данных об евенте в общий массив возврата, если нужно */
            if ($sendEventToSignal) {
                $ret['event_data'] = $event_data;
            }

            return $ret;
        } else {
            $transaction->rollBack();
            return [
                'result'  => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug'    => $UserFile->getErrors(),
            ];
        }
    }

    /**
     * Метод для шаринга папки ли файла
     * @param \common\models\UserNode $UserNode
     * @param bool $sendEventToSignal
     * @param bool $internalNodeFM
     * @return array
     */
    public function sharing_disable($UserNode, $sendEventToSignal=false, $internalNodeFM=false)
    {
        /* Поиск файла или папки по UUID */
        $UserFile = UserFiles::findOne([
            'file_uuid'  => $this->uuid,
            'user_id'    => $UserNode->user_id,
            'is_deleted' => UserFiles::FILE_UNDELETED,
        ]);

        /* проверка существования исходного файла */
        if (!$UserFile){
            return [
                'result'  => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info'    => "File not found.",
            ];
        }

        /* проверка что файл не удаили до этого */
        if ($UserFile->last_event_type == UserFileEvents::TYPE_DELETE) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info'    => "File was deleted. You can't do any actions with this file.",
            ];
        }

        /* проверка что файл расшарен и есть смысл вызова */
        if (!$UserFile->share_hash) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_FS_SYNC,
                'info'    => "File is not shared.",
            ];
        }

        /* выбор метода шаринга папка или файл */
        if ($UserFile->is_folder == 1) {
            return $this->folder_unshare($UserFile, $sendEventToSignal, $internalNodeFM, $UserNode->node_id);
        } else {
            return $this->file_unshare($UserFile, $sendEventToSignal, $internalNodeFM, $UserNode->node_id);
        }
    }

    /**
     * Метод для регистрации события file-unshare
     * @param \common\models\UserFiles $UserFile
     * @param bool $internalNodeFM
     * @param bool $sendEventToSignal
     * @param integer|null $node_id
     * @return array
     */
    private function file_unshare($UserFile, $sendEventToSignal=false, $internalNodeFM=false, $node_id=null)
    {
        /* Начинаем транзакцию */
        $transaction = Yii::$app->db->beginTransaction();
        $UserFile->is_shared      = UserFiles::FILE_UNSHARED;
        $UserFile->share_lifetime = null;
        $UserFile->share_ttl_info = null;
        $UserFile->share_password = null;
        $UserFile->share_created  = null;
        $UserFile->share_hash     = null;
        if ($UserFile->file_parent_id) {
            $UserFileParent = UserFiles::findOne(['file_id' => $UserFile->file_parent_id]);
            if ($UserFileParent && $UserFileParent->share_group_hash) {
                $UserFile->share_lifetime   = $UserFileParent->share_lifetime;
                $UserFile->share_ttl_info   = $UserFileParent->share_ttl_info;
                $UserFile->share_password   = $UserFileParent->share_password;
                $UserFile->share_created    = $UserFileParent->share_created;
                $UserFile->generate_share_hash();
                $UserFile->share_group_hash = $UserFileParent->share_group_hash;
            }
        }
        if ($UserFile->save()) {

            $event_data = [
                'operation' => "sharing_disable",
                'data' => [
                    'uuid'     => $UserFile->file_uuid,
                ],
            ];

            /* успешное завершение транзакции */
            $transaction->commit();

            /* Создание файла со служебной инфой для отображения в ФС вебФМ */
            if (!$internalNodeFM) {
                $relativePath = UserFiles::getFullPath($UserFile);
                $User = Users::getPathNodeFS($UserFile->user_id);
                $file_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;

                if (!file_exists($file_name)) {
                    FileSys::touch($file_name, UserFiles::CHMOD_DIR, UserFiles::CHMOD_FILE);
                }
                UserFiles::createFileInfo($file_name, $UserFile);
            }

            $ret = [
                'result' => "success",
                'info'   => "sharing disabled successfully",
                'data'   => [
                    'uuid'      => $UserFile->file_uuid,
                ],
            ];

            /* Отправка евента на редис */
            try {
                $this->redis->publish("user:{$UserFile->user_id}:share_events", Json::encode($event_data));
                $this->redis->save();
            } catch (\Exception $e) {
                RedisSafe::createNewRecord(
                    RedisSafe::TYPE_SHARING_EVENTS,
                    $UserFile->user_id,
                    $node_id,
                    Json::encode([
                        'action'           => 'share_events',
                        'chanel'           => "user:{$UserFile->user_id}:share_events",
                        'user_id'          => $UserFile->user_id,
                    ])
                );
            }

            /* Подготовка данных об евенте в общий массив возврата, если нужно */
            if ($sendEventToSignal) {
                $ret['event_data'] = $event_data;
            }

            return $ret;
        } else {
            $transaction->rollBack();
            return [
                'result' => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug' => $UserFile->getErrors(),
            ];
        }
    }

    /**
     * Метод для регистрации события folder-unshare
     * @param \common\models\UserFiles $UserFile
     * @param bool $internalNodeFM
     * @param bool $sendEventToSignal
     * @param integer|null $node_id
     * @return array
     */
    private function folder_unshare($UserFile, $sendEventToSignal=false, $internalNodeFM=false, $node_id=null)
    {
        /* Начинаем транзакцию */
        $transaction = Yii::$app->db->beginTransaction();
        $UserFile->is_shared        = UserFiles::FILE_UNSHARED;
        $UserFile->share_lifetime   = null;
        $UserFile->share_ttl_info   = null;
        $UserFile->share_password   = null;
        $UserFile->share_created    = null;
        $UserFile->share_hash       = null;
        $UserFile->share_group_hash = null;

        if ($UserFile->file_parent_id) {
            $UserFileParent = UserFiles::findOne(['file_id' => $UserFile->file_parent_id]);
            if ($UserFileParent && $UserFileParent->share_group_hash) {
                $UserFile->share_lifetime   = $UserFileParent->share_lifetime;
                $UserFile->share_ttl_info   = $UserFileParent->share_ttl_info;
                $UserFile->share_password   = $UserFileParent->share_password;
                $UserFile->share_created    = $UserFileParent->share_created;
                $UserFile->generate_share_hash();
                $UserFile->share_group_hash = $UserFileParent->share_group_hash;
            }
            //UserFiles::changeChildrenGroupHash($UserFile, $UserFile->share_group_hash, $UserFile->collaboration_id);
        }

        if ($UserFile->save()) {

            /* шаринг. меняем групповую шару */
            UserFiles::changeChildrenGroupHash($UserFile, $UserFile->share_group_hash, $UserFile->collaboration_id);

            $event_data = [
                'operation' => "sharing_disable",
                'data' => [
                    'uuid'     => $UserFile->file_uuid,
                ],
            ];

            /* успешное завершение транзакции */
            $transaction->commit();

            /* Создание файла со служебной инфой для отображения в ФС вебФМ */
            if (!$internalNodeFM) {
                $relativePath = UserFiles::getFullPath($UserFile);
                $User = Users::getPathNodeFS($UserFile->user_id);
                $folder_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;
                if (!file_exists($folder_name)) {
                    FileSys::mkdir($folder_name, UserFiles::CHMOD_DIR, true);
                }
                $folder_info_file = $folder_name . DIRECTORY_SEPARATOR . UserFiles::DIR_INFO_FILE;
                if (!file_exists($folder_info_file)) {
                    FileSys::touch($folder_info_file, UserFiles::CHMOD_DIR, UserFiles::CHMOD_FILE);
                }
                UserFiles::createFileInfo($folder_info_file, $UserFile);
            }

            $ret = [
                'result' => "success",
                'info'   => "sharing disabled successfully",
                'data'   => [
                    'uuid'      => $UserFile->file_uuid,
                ],
            ];

            /* Отправка евента на редис */
            try {
                $this->redis->publish("user:{$UserFile->user_id}:share_events", Json::encode($event_data));
                $this->redis->save();
            } catch (\Exception $e) {
                RedisSafe::createNewRecord(
                    RedisSafe::TYPE_SHARING_EVENTS,
                    $UserFile->user_id,
                    $node_id,
                    Json::encode([
                        'action'           => 'share_events',
                        'chanel'           => "user:{$UserFile->user_id}:share_events",
                        'user_id'          => $UserFile->user_id,
                    ])
                );
            }

            /* Подготовка данных об евенте в общий массив возврата, если нужно */
            if ($sendEventToSignal) {
                $ret['event_data'] = $event_data;
            }

            return $ret;
        } else {
            $transaction->rollBack();
            return [
                'result'  => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug'    => $UserFile->getErrors(),
            ];
        }
    }
    /************************ --- SHARE EVENTS --- *************************/


    /******************** +++ COLLABORATION EVENTS --- *********************/

    /**
     * @param \common\models\Users $User
     * @return array
     */
    public function collaboration_join($User)
    {
        /* Ищем коллегу по его ИД */
        $UserColleague = UserColleagues::findOne(['colleague_id' => $this->colleague_id]);
        if (!$UserColleague) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_COLLABORATION_DATA,
                'info'    => "Collaboration is deleted now. You can't join it.",
            ];
        }

        /* Проверка что user_id из $UserColleague совпадает с $User. */
        /* Если не заполнено поле user_id в записи о коллеге */
        if (!$UserColleague->user_id) {
            if ($UserColleague->colleague_email !== $User->user_email) {
                return [
                    'status' => false,
                    'errcode' => self::ERROR_COLLABORATION_DATA,
                    'info' => "Wrong data (user_email mismatch). Access denied.",
                ];
            } else {
                $UserColleague->user_id = $User->user_id;
                if (!$UserColleague->save()) {
                    return [
                        'status' => false,
                        'errcode' => self::ERROR_DATABASE_FAILURE,
                        'info'   => "Can't change user_id from null to id of User",
                        'debug'   => $UserColleague->getErrors(),
                    ];
                }
            }
        } else if ($UserColleague->user_id !== $User->getId()) {
            return [
                'status' => false,
                'info' => "Wrong data (user_id mismatch). Access denied.",
            ];
        }

        /* Ищем коллаборацию для найденного коллеги */
        $UserCollaboration = UserCollaborations::findOne(['collaboration_id' => $UserColleague->collaboration_id]);
        if (!$UserCollaboration) {
            return [
                'status' => false,
                'errcode' => self::ERROR_COLLABORATION_DATA,
                'info' => "Can't find UserCollaboration for colleague_id={$this->colleague_id}",
            ];
        }

        /* Джойним юзера в эту папку (коллаборацию) */
        $data['colleague_message'] = '';
        $data['action'] = CollaborationApi::ACTION_EDIT;
        $data['access_type'] = $UserColleague->colleague_permission;
        $data['colleague_id'] = $UserColleague->colleague_id;
        $data['collaboration_id'] = $UserColleague->collaboration_id;
        $data['owner_user_id'] = $UserCollaboration->user_id;
        $data['uuid'] = $UserCollaboration->file_uuid;

        $required = [
            'action',
            'access_type',
            'colleague_id',
            'collaboration_id',
            //'owner_user_id',
            //'uuid'
        ];
        $model = new CollaborationApi($required);
        if (!$model->load(['CollaborationApi' => $data]) || !$model->validate()) {
            return [
                'result' => "error",
                'errcode' => self::ERROR_WRONG_DATA,
                'info' => $model->getErrors(),
            ];
        }
        $ret = $model->colleagueJoin();

        /* проверим есть ли еще папки от владельца этой коллабы */
        /* в которые он успел инвайтнуть юзера, до того как юзер заджойнился */
        /* если есть, то в эти папки джойним юзера автоматически уже*/
        if ($ret['status']) {
            $query = "SELECT
                            t2.colleague_permission,
                            t2.colleague_id,
                            t2.collaboration_id,
                            t1.user_id as owner_user_id,
                            t1.file_uuid
                          FROM {{%user_collaborations}} as t1
                          INNER JOIN {{%user_colleagues}} as t2 ON t1.collaboration_id = t2.collaboration_id
                          WHERE (t1.user_id = :owner_user_id)
                          AND (t2.user_id = :colleague_user_id)
                          AND (t1.collaboration_id != :current_collaboration_id)
                          AND (t2.colleague_status = :STATUS_INVITED)";
            $res = Yii::$app->db
                ->createCommand($query, [
                    'owner_user_id'            => $UserCollaboration->user_id,
                    'colleague_user_id'        => $UserColleague->user_id,
                    'current_collaboration_id' => $UserCollaboration->collaboration_id,
                    'STATUS_INVITED'           => UserColleagues::STATUS_INVITED,
                ])
                ->queryAll();

            if (is_array($res)) {
                foreach ($res as $v) {

                    unset($data);
                    $data['colleague_message'] = '';
                    $data['action'] = CollaborationApi::ACTION_EDIT;
                    $data['access_type'] = $v['colleague_permission'];
                    $data['colleague_id'] = $v['colleague_id'];
                    $data['collaboration_id'] = $v['collaboration_id'];
                    $data['owner_user_id'] = $v['owner_user_id'];
                    $data['uuid'] = $v['file_uuid'];

                    $required = [
                        'action',
                        'access_type',
                        'colleague_id',
                        'collaboration_id',
                        //'owner_user_id',
                        //'uuid'
                    ];
                    $model_other = new CollaborationApi($required);
                    if ($model_other->load(['CollaborationApi' => $data]) && $model_other->validate()) {
                        $model_other->colleagueJoin();
                    }
                }
            }
        }

        return $ret;
    }
    /******************** --- COLLABORATION EVENTS --- *********************/


    /*************************** +++ SIGNAL  +++ ***************************/
    /**
     * Метод выборки всех расшареных файлов для user_id
     * @return array
     */
    public function sharing_list()
    {
        if (!Users::findIdentity($this->user_id)) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_USER_NOT_FOUND,
                'info'    => "User not found.",
            ];
        }

        $SharingList = UserFiles::find()
            ->select([
                'file_uuid as uuid',
                'share_hash',
                'is_folder',
                'share_password',
                'share_created',
                'share_lifetime',
                'share_ttl_info',
            ])
            ->where([
                'user_id' => $this->user_id,
                'is_shared' => UserFiles::FILE_SHARED,
            ])
            ->andWhere("(share_lifetime > :share_lifetime) OR (share_lifetime IS NULL)", ['share_lifetime'  => date(SQL_DATE_FORMAT)])
            ->andWhere("last_event_type <> :last_event_type", ['last_event_type' => UserFileEvents::TYPE_DELETE])
            ->asArray()->all();

        foreach ($SharingList as $k=>$v) {
            if ($SharingList[$k]['share_password']) {
                $SharingList[$k]['share_password'] = true;
            } else {
                $SharingList[$k]['share_password'] = false;
            }
            $SharingList[$k]['share_link'] = UserFiles::getShareLink($SharingList[$k]['share_hash'], $SharingList[$k]['is_folder']);
            unset($SharingList[$k]['is_folder']);
        }

        return [
            'result' => "success",
            'info'   => "sharing list",
            'data'   => $SharingList,
        ];
    }

    /**
     * Метод для получения инфы по конкретной шаре
     * @return array
     */
    public function sharing_info()
    {
        if (!Users::findIdentity($this->user_id)) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_USER_NOT_FOUND,
                'info'    => "User not found.",
            ];
        }

        if (!$this->share_password) { $this->share_password = null; }

        $data = UserFiles::find()
            ->select([
                'file_id',
                'share_hash',
                'share_group_hash',
                'file_name as name',
                'file_size',
                //'diff_file_uuid',
                'is_folder',
                'is_shared',
                'share_password',
                'share_lifetime',
                'share_ttl_info',
                'file_md5 as file_hash',
            ])
            ->where([
                'share_hash' => $this->share_hash,
                'user_id'    => $this->user_id,
            ])
            ->andWhere("(share_lifetime > :share_lifetime) OR (share_lifetime IS NULL)", ['share_lifetime'  => date(SQL_DATE_FORMAT)])
            /*
            ->andWhere("(share_lifetime > :share_lifetime) OR ((share_lifetime IS NULL) AND (share_ttl_info != :TTL_IMMEDIATELY_DOWNLOADED))", [
                'share_lifetime'             => date(SQL_DATE_FORMAT),
                'TTL_IMMEDIATELY_DOWNLOADED' => UserFiles::TTL_IMMEDIATELY_DOWNLOADED,
            ])
            */
            ->andWhere("last_event_type <> :last_event_type", ['last_event_type' => UserFileEvents::TYPE_DELETE])
            ->asArray()
            ->one();
        if (is_array($data)) {

            //if (!$data['share_password'] || $data['share_password'] == $this->share_password) {
                if ($data['is_folder'] == UserFiles::TYPE_FOLDER) {

                    $file_id = ($data['is_shared'] == UserFiles::FILE_UNSHARED) ? $data['file_id'] : null;
                    $data['share_link'] = UserFiles::getShareLink($data['share_group_hash'], true, $file_id);
                    unset($data['file_size'], $data['diff_file_uuid']);
                    $data['childs'] = UserFiles::getChildren($data['share_group_hash'], $data['file_id']);

                } else {

                    /** @var \common\models\UserFileEvents $eventWithUuid */
                    $eventWithUuid = UserFileEvents::find()
                        ->select([
                            'event_id',
                            'event_uuid',
                        ])
                        ->where(['file_id' => $data['file_id']])
                        ->andWhere('event_type NOT IN (:event_delete)', ['event_delete' => UserFileEvents::TYPE_DELETE])
                        ->orderBy(['event_id' => SORT_DESC])
                        ->limit(1)
                        ->one();

                    if ($eventWithUuid) {
                        $data['event_uuid'] = $eventWithUuid->event_uuid;
                        $data['share_link'] = UserFiles::getShareLink($data['share_hash'], false);
                    } else {
                        $data = [];
                    }

                }

                if ($data['share_password']) {
                    $data['share_password'] = true;
                } else {
                    $data['share_password'] = false;
                }

                unset(
                    $data['is_folder'],
                    $data['file_id'],
                    $data['is_shared'],
                    $data['share_group_hash'],
                    //$data['share_password'],
                    $data['share_lifetime'],
                    $data['share_ttl_info']
                );
                return [
                    'result' => "success",
                    'info' => "sharing info",
                    'data' => $data,
                ];
            /*
            } else {
                return [
                    'result'  => "error",
                    'errcode' => self::ERROR_SHARE_WRONG_PASSWORD,
                    'info'    => "Wrong password for share.",
                ];
            }
            */
        } else {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_SHARE_NOT_FOUND,
                'info'    => "Share not found.",
            ];
        }
        //var_dump($data); exit;
    }

    /**
     * Метод для регистрации факта что шара была скачана и если эта шара TTL_IMMEDIATELY то она отменяется
     * @return array
     */
    public function share_downloaded()
    {
        $User = Users::findIdentity($this->user_id);
        if (!$User) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_USER_NOT_FOUND,
                'info'    => "User not found.",
            ];
        }

        $UserFile = UserFiles::findOne([
                'share_hash' => $this->share_hash,
                'user_id'    => $this->user_id,
            ]);
        if ($UserFile) {
            if ($UserFile->share_ttl_info == UserFiles::TTL_IMMEDIATELY) {
                $this->uuid = $UserFile->file_uuid;
                $UserNode = self::registerNodeFM($User);
                if ($UserNode) {
                    $ret = $this->sharing_disable($UserNode, false, false);
                    if ($ret['result'] === 'success') {
                        return [
                            'result' => 'success',
                            'info' => 'registered event',
                        ];
                    } else {
                        return $ret;
                    }
                } else {
                    return [
                        'result' => "error",
                        'errcode' => self::ERROR_ADD_NODE_FAILED,
                        'info' => "Failed register nodeFM.",
                    ];
                }
            } else {
                return [
                    'result' => 'success',
                    'info' => 'registered event',
                ];
            }
        } else {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_SHARE_NOT_FOUND,
                'info'    => "Share not found.",
            ];
        }
    }

    /**
     * Метод для обновления данных по ноде
     * @return array
     */
    public function nodeinfo()
    {
        $UserNode = UserNode::findIdentity($this->node_id);
        if (!$UserNode) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_NODE_NOT_FOUND,
                'info'    => "Node not found.",
                'debug'    => "Node with node_id={$this->node_id} not found.",
            ];
        }

        $UserNode->node_last_ip = $this->node_ip;
        $UserNode->node_online = $this->node_online == UserNode::ONLINE_ON ? UserNode::ONLINE_ON : UserNode::ONLINE_OFF;
        $UserNode->node_status = $this->node_status;
        if ($this->node_upload_speed) { $UserNode->node_upload_speed = intval(round($this->node_upload_speed)); }
        if ($this->node_download_speed) { $UserNode->node_download_speed = intval(round($this->node_download_speed)); }
        if ($this->node_disk_usage) { $UserNode->node_disk_usage = $this->node_disk_usage; }
        if ($UserNode->node_online == UserNode::ONLINE_OFF) {
            $UserNode->node_upload_speed = 0;
            $UserNode->node_download_speed = 0;
            if (!in_array($UserNode->node_status, [UserNode::STATUS_LOGGEDOUT, UserNode::STATUS_POWEROFF])) {
                $UserNode->node_status = UserNode::STATUS_POWEROFF;
            }
            if ($UserNode->node_wipe_status == UserNode::LOGOUT_STATUS_SUCCESS) {
                $UserNode->node_status = UserNode::STATUS_LOGGEDOUT;
            }
            if ($UserNode->node_wipe_status == UserNode::WIPE_STATUS_SUCCESS) {
                $UserNode->node_status = UserNode::STATUS_WIPED;
            }
        }
        if ($UserNode->save()) {
            return [
                'result' => "success",
                'info'   => "Updated node info",
            ];
        } else {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_DATABASE_FAILURE,
                'info' => "An internal server error occurred.",
                'debug'    => $UserNode->getErrors(),
            ];
        }
    }

    /**
     * Метод для обновления данных по ноде
     * @return array
     */
    public function nodelist()
    {
        if (!Users::findIdentity($this->user_id)) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_USER_NOT_FOUND,
                'info'    => "User not found.",
            ];
        }

        $UserNodes = UserNode::find()
            ->select([
                'node_id',
                'node_name',
                'node_useragent',
                'node_osname',
                'node_ostype',
                'node_devicetype',
                'node_status',
                'node_last_ip as node_ip',
                'node_online',
                'node_wipe_status',
                'node_updated',
            ])
            ->where(['user_id' => $this->user_id])
            ->andWhere("node_status != :node_status", ['node_status' => UserNode::STATUS_DELETED])
            ->andWhere("node_status != :node_status", ['node_status' => UserNode::STATUS_DEACTIVATED])
            ->andWhere("node_ostype != :node_ostype", ['node_ostype' => UserNode::OSTYPE_WEBFM])
            ->andWhere("node_devicetype != :node_devicetype", ['node_devicetype' => UserNode::DEVICE_BROWSER])
            ->asArray()
            ->all();

        foreach ($UserNodes as $k=>$v) {
            if ($v['node_devicetype'] == UserNode::DEVICE_BROWSER) {
                $v['node_online'] = UserNode::ONLINE_OFF;
                if (time() - strtotime($v['node_updated']) < UserNode::WebFMOnlineTimeout) {
                    $v['node_online'] = UserNode::ONLINE_ON;
                }
            }
            if ($v['node_wipe_status'] == UserNode::WIPE_STATUS_SUCCESS) {
                $UserNodes[$k]['node_status'] = UserNode::STATUS_WIPED;
            }
        }

        return [
            'result' => "success",
            'info'   => "Node list info in data",
            'data'   => $UserNodes,
        ];
    }

    /**
     * Выдает список файов
     * @return array
     */
    public function allfilelist()
    {
        $data = UserFiles::find()
            ->select(['file_uuid', 'file_name as file_name', 'file_size'])
            ->where(['user_id' => $this->user_id])
            //->andWhere('user_id=:user_id', [':user_id' => $this->user_id])
            ->asArray()
            ->all();

        return [
            'result' => "success",
            'info'   => "list files in data",
            'data'   => $data
        ];
    }

    /**
     * @return string
     */
    public static function site_token_key()
    {
        return md5(Yii::$app->session->getId());
    }

    /**
     * Генерация ключа сессии
     */
    public static function generate_site_token()
    {
        if (!Yii::$app->user->isGuest) {
            $data = [
                'user_id' => Yii::$app->user->identity->getId(),
                'node_id' => md5(time() . self::site_token_key()),
            ];
            Yii::$app->cache->set(self::site_token_key(), serialize($data), Yii::$app->session->getTimeout());
        }
    }

    /**
     * Метод проверки ключа сессии
     * @return array
     */
    public function check_site_token()
    {
        $res = Yii::$app->cache->get($this->site_token);
        if ($res) {
            $data = @unserialize($res);
            $data['node_id'] = md5(time() . self::site_token_key());
            return [
                'result' => "success",
                'info'   => "Token exist",
                'data'   => $data,
            ];
        } else {

            //TODO это убрать после запуска
            if (isset(Yii::$app->params['local_check_site_token']) && Yii::$app->params['local_check_site_token']) {
                return [
                    'result' => "success",
                    'info' => "Token exist",
                    'data' => ['user_id' => Yii::$app->params['local_check_site_token'], 'node_id' => md5(time() . microtime())],
                ];
            }

            return [
                'result'  => "error",
                'errcode' => self::ERROR_TOKEN_INVALID,
                'info'    => "Token does not exist",
            ];
        }
    }

    /**
     * Метод проверки авторизации ноды
     * @return array
     */
    public function checknodeauth()
    {
        $UserNode = UserNode::find()
            ->alias('t1')
            ->select([
                't1.user_id',
                't1.node_id',
                't1.node_name',
                't1.node_useragent',
                't1.node_osname',
                't1.node_ostype',
                't1.node_devicetype',
                't2.license_type',
            ])
            ->innerJoin('{{%users}} as t2', 't1.user_id=t2.user_id')
            ->andWhere([
                't2.user_remote_hash' => $this->user_hash,
                't1.node_hash' => $this->node_hash
            ])
            ->limit(1)
            ->asArray()
            ->one();

        if (!$UserNode) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_NODE_NOT_FOUND,
                'info'    => "Auth failed. Node not found or User not found.",
                'debug'   => "Auth failed. Node with node_id={$this->node_id} not found or User with user_hash not found.",
            ];
        }

        return [
            'result' => "success",
            'info'   => "Auth success",
            'data'   => $UserNode,
        ];
    }

    /**
     * Метод проверки авторизации шары
     * @return array
     */
    public function checkbrowserauth()
    {
        $Shares = UserFiles::find()
            ->select([
                'user_id',
                'uuid_short() as node_id',
                'share_password',
            ])
            ->where(['share_hash' => $this->share_hash])
            ->andWhere("(share_lifetime > :share_lifetime) OR (share_lifetime IS NULL)", ['share_lifetime'  => date(SQL_DATE_FORMAT)])
            ->andWhere("last_event_type <> :last_event_type", ['last_event_type' => UserFileEvents::TYPE_DELETE])
            ->orderBy(['file_id' => SORT_ASC])
            ->limit(1)
            ->asArray()
            ->one();

        if (!$Shares) {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_SHARE_NOT_FOUND,
                'info'    => "Auth failed. Share not found.",
            ];
        }

        if (!$this->share_password) { $this->share_password = null; }
        if ($Shares['share_password'] && ($Shares['share_password'] != $this->share_password)) {

            /* тут bad_login_count_tries++ и bad_login_last_timestamp = time() для этого ИП */
            /* это будет метод устанавливающий данные для ип */
            /* (создавать или обновлять уже существующую запись для ИП в таблице) */
            if ($this->share_password !== null) {
                BadLogins::setDataForIP($this->remote_ip, BadLogins::TYPE_LOCK_SHARE);
            }

            return [
                'result'  => "error",
                'errcode' => self::ERROR_SHARE_WRONG_PASSWORD,
                'info'    => "Auth failed. Share wrong password.",
            ];
        } else {

            /* если успешно авторизованы, то удалить из списка */
            BadLogins::removeIpFromList($this->remote_ip, BadLogins::TYPE_LOCK_SHARE);

        }

        return [
            'result' => "success",
            'info'   => "Auth success",
            'data'   => $Shares
        ];
    }

    /**
     * @return array
     */
    public function user_collaborations()
    {
        $query = "SELECT
                    t2.collaboration_id,
                    t2.file_uuid
                  FROM {{%user_colleagues}} as t1
                  INNER JOIN {{%user_collaborations}} as t2 ON t1.collaboration_id = t2.collaboration_id
                  WHERE (t1.user_id = :user_id)
                  AND (t1.colleague_status = :colleague_status)";

        $res = Yii::$app->db->createCommand($query, [
            'user_id'          => $this->user_id,
            'colleague_status' => UserColleagues::STATUS_JOINED,
        ])->queryAll();

        return [
            'result' => "success",
            'data'   => $res
        ];
    }

    /**
     * @return array
     */
    public function get_redis_safe()
    {
        $query = "SELECT
                    max(rs_id) as rs_id,
                    rs_type,
                    user_id,
                    node_id
                  FROM {{%redis_safe}}
                  GROUP BY rs_type, user_id, node_id
                  ORDER BY rs_id ASC
                  LIMIT 100";

        $res = Yii::$app->db->createCommand($query)->queryAll();
        if (is_array($res)) {
            $len = sizeof($res);
            if ($len) {

                /*
                $max_date = $res[$len - 1]['rs_created'];
                $query = "DELETE FROM {{%redis_safe}} WHERE rs_created <= :max_date";
                Yii::$app->db->createCommand($query, [
                    'max_date' => $max_date
                ])->query();
                */
                return [
                    'result' => "success",
                    'data'   => $res,
                ];
            }
        }

        return [
            'result' => "success",
            'data'   => [],
        ];
    }

    /**
     * @return array
     */
    public function redis_safe_done()
    {
        if ($this->node_id) {
            $deleted = RedisSafe::deleteAll("(rs_id <= :max_rs_id) AND (rs_type = :rs_type) AND (user_id = :user_id) AND (node_id = :node_id)", [
                'max_rs_id' => $this->rs_id,
                'rs_type' => $this->rs_type,
                'user_id' => $this->user_id,
                'node_id' => $this->node_id,
            ]);
        } else {
            $deleted = RedisSafe::deleteAll("(rs_id <= :max_rs_id) AND (rs_type = :rs_type) AND (user_id = :user_id) AND (node_id IS NULL)", [
                'max_rs_id' => $this->rs_id,
                'rs_type' => $this->rs_type,
                'user_id' => $this->user_id,
            ]);
        }
        /*
        $query = "DELETE FROM {{%redis_safe}} WHERE rs_id <= :max_rs_id";
        Yii::$app->db->createCommand($query, [
            'max_rs_id' => $this->rs_id
        ])->query();
        */

        return [
            'result'        => "success",
            'deleted_count' => $deleted,
        ];
    }

    /**
     * @return array
     */
    public function get_license_type()
    {
        $User = Users::findOne(['user_id' => $this->user_id]);
        if ($User) {
            return [
                'result' => "success",
                'data'   => $User->license_type,
            ];
        }

        return [
            'result'  => "error",
            'errcode' => self::ERROR_USER_NOT_FOUND,
            'info'    => "User not found.",
        ];
    }

    /**
     * @return array
     */
    public function get_remote_actions()
    {
        $query = "SELECT
                    t1.action_uuid,
                    t1.action_type,
                    t1.action_data,
                    CASE WHEN t1.action_end_time IS NOT NULL THEN 1 ELSE 0 END as done,
                    t2.node_status
                  FROM {{%remote_actions}} as t1
                  INNER JOIN {{%user_node}} as t2 ON t1.target_node_id = t2.node_id
                  WHERE (t1.user_id = :user_id)
                  AND (t1.target_node_id = :node_id)";

        $res = Yii::$app->db->createCommand($query, [
            'user_id' => $this->user_id,
            'node_id' => $this->node_id,
        ])->queryAll();
        if (is_array($res)) {

            foreach ($res as $k=>$v) {
                $res[$k]['action_data'] = unserialize($res[$k]['action_data']);
            }

            return [
                'result' => "success",
                'data'   => $res,
            ];

        } else {

            return [
                'result' => "success",
                'data'   => [],
            ];

        }

    }

    /**
     * @return array
     */
    public function get_uploads()
    {
        $query = "SELECT
                    t1.*,
                    t2.file_uuid as folder_uuid
                  FROM {{%user_uploads}} as t1
                  LEFT JOIN {{%user_files}} as t2 ON t1.file_parent_id = t2.file_id
                  WHERE (t1.user_id = :user_id)";

        $res = Yii::$app->db->createCommand($query, [
            'user_id' => $this->user_id,
        ])->queryAll();

        return [
            'result' => "success",
            'data'   => $res,
        ];
    }

    /**
     * return array
     */
    public function get_node_status()
    {
        $UserNode = UserNode::findOne(['node_id' => $this->node_id]);
        if ($UserNode) {
            return [
                'result' => "success",
                'data'   => [
                    'node_id'     => $UserNode->node_id,
                    'node_status' => $UserNode->node_status,
                ],
            ];
        } else {
            return [
                'result'  => "error",
                'errcode' => self::ERROR_NODE_NOT_FOUND,
                'info'    => "Node not found.",
                'debug'   => "Node with node_id={$this->node_id} not found.",
            ];
        }
    }

    /**
     * @return array
     */
    public function traffic_info()
    {
        $transaction = Yii::$app->db->beginTransaction();
        foreach ($this->traffic_data as $v) {
            $tl = new TrafficLog();
            $tl->event_uuid = $v['event_uuid'];
            $tl->interval = $v['interval'];
            $tl->tx_wd = $v['tx_wd'];
            $tl->rx_wd = $v['rx_wd'];
            $tl->tx_wr = $v['tx_wr'];
            $tl->rx_wr = $v['rx_wr'];
            $tl->is_share = $v['is_share'];
            $tl->user_id = $this->user_id;
            $tl->node_id = $this->node_id;
            if (!$tl->save()) {
                $transaction->rollBack();
                return [
                    'result'  => "error",
                    'errcode' => self::ERROR_DATABASE_FAILURE,
                    'info'    => "An internal server error occurred.",
                    'debug'   => $tl->getErrors(),
                ];
            }
        }
        $transaction->commit();
        return [
            'result' => "success",
            'data'   => 'Received',
        ];
        /*
        return [
            'result'  => "error",
            'errcode' => self::ERROR_DATABASE_FAILURE,
            'info'    => $tl->getErrors(),
        ];
        */
    }
    /*************************** --- SIGNAL  --- ***************************/


    /************************* +++ CONSOLE CRON +++ ************************/
    /**
     * @return string
     */
    public function deleteOldPatches()
    {
        //return "function is temporary unavailable. DEBUG in progress.";
        if ($this->DOP_onlyForUserId) {
            $WHERE_USER_ID = "AND (t1.user_id = {$this->DOP_onlyForUserId})";
        } else {
            $WHERE_USER_ID = "";
        }

        if ($this->DOP_restorePatchTTL !== null) {
            $old_timestamp = time() - $this->DOP_restorePatchTTL;
        } else {
            $old_timestamp = time() - Preferences::getValueByKey('RestorePatchTTL', 2592000, 'int');
        }

        $total_deleted_for_user = [];
        $folder_deleted_and_outdated = [];
        $total_deleted = 0;
        $current_file_id = 0;
        $i = 0;
        $limit = 100000;
        $output = "";
        $str = "Start main stage: SELECT events grouped by file_id and user_id LIMIT {$limit} \n";
        $output .= $str; Functions::debugEcho($str);
        $str = "Start at: " . date('Y-m-d H:i:s') . "\n\n";
        $output .= $str; Functions::debugEcho($str);

        $str = "Stage #1 - delete outdated events for single files and store outdated folders in array \n\n";
        $output .= $str; Functions::debugEcho($str);
        do {
            $offset = $limit * $i;
            //$str = "Iteration ". ($i+1) . " OFFSET = {$offset} \n\n";
            //$output .= $str; Functions::debugEcho($str);

            // тут еще сделать джойн с таблицей файлов для последующего анализа
            $query = "SELECT
                          max(t1.event_id) as event_id,
                          t1.file_id,
                          max(t1.user_id) as user_id,
                          max(t2.is_folder) as is_folder,
                          max(t2.is_deleted) as is_deleted,
                          max(t2.last_event_type) as last_event_type,
                          max(t2.is_outdated) as is_outdated,
                          max(t2.file_lastatime) as file_lastatime
                      FROM {{%user_file_events}} as t1
                      INNER JOIN {{%user_files}} as t2 ON t1.file_id = t2.file_id
                      WHERE (t1.event_timestamp < :event_timestamp)
                      -- AND (t1.user_id = 7722) --temporary debug filter
                      " . $WHERE_USER_ID . "
                      AND (t1.file_id >= :file_id)
                      GROUP BY t1.file_id
                      ORDER BY t1.file_id ASC
                      LIMIT {$limit}";
            //$str = "Start query: " . str_replace([':file_id', ':event_timestamp'], [$current_file_id, $old_timestamp], $query) . "\n\n at " . date('Y-m-d H:i:s') . "\n\n";
            //$output .= $str; Functions::debugEcho($str);
            $events = Yii::$app->db->createCommand($query, [
                'event_timestamp' => $old_timestamp,
                'file_id'         => $current_file_id,
            ])->queryAll();
            //$str = "Finish query at " . date('Y-m-d H:i:s') . "\n\n";
            //$output .= $str; Functions::debugEcho($str);

            $count_of_result = sizeof($events);

            $str = "Count of result = {$count_of_result}\n\n";
            $output .= $str; Functions::debugEcho($str);

            if ($count_of_result > 0) {

                foreach ($events as $key=>$event) {

                    $current_file_id = $event['file_id'];

                    /** удаляем все устаревшие евенты */
                    //$str = "Start query-delete for file_id={$event['file_id']} at " . date('Y-m-d H:i:s') . "\n\n";
                    //$output .= $str; Functions::debugEcho($str);
                    $deleted = UserFileEvents::deleteAll('(file_id = :file_id) AND (event_id < :event_id)', [
                        'file_id' => $event['file_id'],
                        'event_id' => $event['event_id'],
                    ]);
                    //$str = "Finish query-delete at " . date('Y-m-d H:i:s') . "\n\n";
                    //$output .= $str; Functions::debugEcho($str);

                    if ($deleted > 0) {
                        $total_deleted += $deleted;
                        isset($total_deleted_for_user[$event['user_id']])
                            ? $total_deleted_for_user[$event['user_id']] += $deleted
                            : $total_deleted_for_user[$event['user_id']] = $deleted;

                        $str = "Current file_id = {$current_file_id}, owner this file is user_id={$event['user_id']} \n";
                        $output .= $str; Functions::debugEcho($str);
                        $str = "Deleted {$deleted} events for file_id={$event['file_id']} \n";
                        $output .= $str; Functions::debugEcho($str);
                    }

                    /**
                     * Проверяем если это папка (is_folder=1)
                     * и если она удалена (is_deleted=1)
                     * и last_event_type=DELETE
                     * и file_lastatime < $old_timestamp
                     * и еще не помечена как is_outdated
                     * тогда нужно найти всех ее чилдренов
                     * и начисто удалить из базы сами файлы
                     * (евенты удаляся автоматически по внешнему ключу)
                     * для саймой же этой папки оставить только последний евент-делете
                     *
                     * !!! Только удалять всех чилдренов можно уже после того как сделана зачистка ФС
                     * которая делается ниже вне этого цикла (иначе в фс останутся файлы призраки, которые уже не истребить)
                     * А значит нужно просто на данном этапе записать в массив ИД таких папок и затем
                     * для этих ид выполнить вышеописанные процедуры
                     */
                    if (($event['is_folder'] == UserFiles::TYPE_FOLDER) &&
                        ($event['is_deleted'] == UserFiles::FILE_DELETED) &&
                        ($event['last_event_type'] == UserFileEvents::TYPE_DELETE) &&
                        ($event['is_outdated'] == UserFiles::FILE_UNOUTDATED) &&
                        ($event['file_lastatime'] < $old_timestamp)) {

                        $str = "Found deleted and outdated folder file_id={$event['file_id']} owner user_id={$event['user_id']}. Store it in array for next actions with it \n";
                        $output .= $str; Functions::debugEcho($str);

                        $folder_deleted_and_outdated[] = $event['file_id'];

                    }

                }

            }
            $i++;
        } while ($count_of_result >= $limit);

        $str = "Last file_id={$current_file_id} \n\n";
        $output .= $str; Functions::debugEcho($str);

        $str = "\n\n";
        $output .= $str; Functions::debugEcho($str);


        $str = "Stage #2 - delete all children for outdated folders which were stored in array \n\n";
        $output .= $str; Functions::debugEcho($str);
        /**
         * Теперь нужно обработать массив $folder_deleted_and_outdated
         * папки которые удалены и оутдейтед и содержат в себе чилдренов
         * Сначала нужно удалить из фс ФМа такие папки, что бы они не стали призраками
         * а затем ожно удалять всех чилдренов этих папок из самой бд
         */
        while (sizeof($folder_deleted_and_outdated)) {

            $slice_of_array = array_slice($folder_deleted_and_outdated, 0, 1000);
            array_splice($folder_deleted_and_outdated, 0, 1000);

            $UserFiles = UserFiles::find()
                ->where([
                    'last_event_type' => UserFileEvents::TYPE_DELETE,
                    'is_outdated' => UserFiles::FILE_UNOUTDATED,
                    'is_deleted' => UserFiles::FILE_DELETED,
                    'file_id' => $slice_of_array,
                ])->all();
            foreach ($UserFiles as $UserFile) {
                /** @var \common\models\UserFiles $UserFile */

                $str = "Delete children for folder file_id={$UserFile->file_id} \n";
                $output .= $str; Functions::debugEcho($str);

                /** Сначала удалим из ФС */
                $str = "First, try delete from fm file-system for folder (file_id={$UserFile->file_id})\n";
                $output .= $str; Functions::debugEcho($str);
                $relativePath = UserFiles::getFullPath($UserFile);
                if ($relativePath) {
                    $User = Users::getPathNodeFS($UserFile->user_id);
                    $file_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;
                    if ($UserFile->is_folder) {
                        FileSys::rmdir($file_name, true);
                    } else {
                        @unlink($file_name);
                    }
                }
                $UserFile->is_outdated = UserFiles::FILE_OUTDATED;
                if (!$UserFile->save()) {
                    $str = "Some error on save UserFile {$UserFile->file_id}\n";
                    $output .= $str; Functions::debugEcho($str);
                    $str = Json::encode($UserFile->getErrors()) . "\n";
                    $output .= $str; Functions::debugEcho($str);
                }

                /** Теперь сделаем удаление чилдренов для папок $folder_deleted_and_outdated */
                $str = "And on second, delete all children for this folder (file_id={$UserFile->file_id})\n";
                $output .= $str; Functions::debugEcho($str);
                $deleted = UserFiles::deleteAll("file_id IN (SELECT file_id FROM get_all_children_for(:file_id))", [
                    'file_id' => $UserFile->file_id
                ]);
                if ($deleted > 0) {
                    $total_deleted += $deleted;
                    isset($total_deleted_for_user[$UserFile->user_id])
                        ? $total_deleted_for_user[$UserFile->user_id] += $deleted
                        : $total_deleted_for_user[$UserFile->user_id] = $deleted;
                }

                $str = "Count deleted={$deleted}\n";
                $output .= $str;
                Functions::debugEcho($str);
            }
        }
        $str = "\n\n";
        $output .= $str;
        Functions::debugEcho($str);


        $str = "Stage #3 - send data to redis for each off user which events were deleted. And clean fs for FM. \n\n";
        $output .= $str; Functions::debugEcho($str);
        /** Теперь проанализирем массив $total_deleted_for_user и обработаем каждого из этих юзеров (отправка на редис, зачистка фс. обновление данных бд) */
        foreach ($total_deleted_for_user as $current_user_id => $count_deleted) {
            if ($count_deleted > 0) {

                $str = "Current user_id = {$current_user_id} \n";
                $output .= $str; Functions::debugEcho($str);
                $str = "Total deleted {$count_deleted} events for user_id={$current_user_id}\n";
                $output .= $str; Functions::debugEcho($str);

                /**
                 * Поиск всех файлов у которых последний last_event_type = TYPE_DELETE и file_lastatime < $old_timestamp
                 * Находим эти файлы и удаляем их из ФС затем удаляем сами файлы и их евенты
                 * Тут есть небольшой конфликт при удалении родительской папки в которой еще есть папки удаленные
                 * Но в общем итоге после обработки всех эелементов конфликт пропадает
                 */
                $str = "Delete all files which are deleted for user_id={$current_user_id} (Clean fm-fs)\n";
                $output .= $str; Functions::debugEcho($str);

                $UserFiles = UserFiles::find()
                    ->where([
                        'last_event_type' => UserFileEvents::TYPE_DELETE,
                        'is_outdated'     => UserFiles::FILE_UNOUTDATED,
                        'user_id'         => $current_user_id
                    ])
                    ->andWhere("file_lastatime < :event_timestamp", ['event_timestamp' => $old_timestamp])
                    ->orderBy(['is_folder' => SORT_ASC])
                    ->all();

                foreach ($UserFiles as $UserFile) {

                    /** @var \common\models\UserFiles $UserFile */
                    $relativePath = UserFiles::getFullPath($UserFile);
                    if ($relativePath) {
                        $User = Users::getPathNodeFS($UserFile->user_id);
                        $file_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;
                        if ($UserFile->is_folder) {
                            FileSys::rmdir($file_name, true);
                        } else {
                            @unlink($file_name);
                        }
                    }
                    $UserFile->is_outdated = UserFiles::FILE_OUTDATED;
                    $UserFile->save();
                    //$str = "Deleted {$UserFile->file_name} from fs for user_id={$UserFile->user_id} \n";
                    //$output .= $str; Functions::debugEcho($str);
                }
                //$str = "End clean fm-fs\n\n";
                //$output .= $str; Functions::debugEcho($str);


                /** отправка данных на редис для текущего юзера */
                $str = "Execute send data to redis for user_id={$current_user_id}.\n";
                $output .= $str; Functions::debugEcho($str);
                $query2 = "SELECT
                              event_id, event_uuid, user_id
                           FROM {{%user_file_events}}
                           WHERE user_id = :current_user_id
                           ORDER BY event_id ASC
                           LIMIT 1";
                $min_user_event = Yii::$app->db->createCommand($query2, [
                    'current_user_id' => $current_user_id
                ])->queryOne();
                /* Отправка на редис */
                try {
                    $this->redis->publish(
                        "user:{$current_user_id}:min_stored_event",
                        Json::encode([
                            'action_type' => 'min_stored_event',
                            'event_uuid' => $min_user_event['event_uuid'],
                        ])
                    );
                    $this->redis->save();
                } catch (\Exception $e) {}
                $str = "Sent data to user:{$current_user_id}:min_stored_event = {$min_user_event['event_uuid']}\n";
                $output .= $str; Functions::debugEcho($str);


                /** Обновление данных о юзере */
                Users::updateAll(['first_event_uuid_after_cron' => $min_user_event['event_uuid']], ['user_id' => $current_user_id]);


                /** Проверим есть ли евенты апдейта, и если нет, то запишем в файл инфу что нет откатов у него */
                $str = "Cleanup restore for file \n";
                $output .= $str; Functions::debugEcho($str);
                /*
                UserFiles::updateAll(
                    [
                        'is_updated' => UserFiles::FILE_UNUPDATED
                    ],
                    "(file_id NOT IN (
                            SELECT
                                file_id
                            FROM {{%user_file_events}}
                            WHERE (event_type = :type_update)
                            AND (user_id = :current_user_id)
                            AND (event_timestamp >= :event_timestamp)
                            GROUP BY file_id
                        )
                    )
                    AND (user_id = :current_user_id)
                    AND (is_updated = :FILE_UPDATED)",
                    [
                        'type_update'     => UserFileEvents::TYPE_UPDATE,
                        'event_timestamp' => $old_timestamp,
                        'current_user_id' => $current_user_id,
                        'FILE_UPDATED'    => UserFiles::FILE_UPDATED,
                    ]
                );
                */
                $query3 = "UPDATE {{%user_files}}
                           SET is_updated = :FILE_UNUPDATED
                           WHERE (file_id NOT IN (
                                SELECT
                                    file_id
                                FROM {{%user_file_events}}
                                WHERE (event_type = :type_update)
                                AND (user_id = :current_user_id)
                                AND (event_timestamp >= :event_timestamp)
                                GROUP BY file_id)
                           )
                           AND (user_id = :current_user_id)
                           AND (is_updated = :FILE_UPDATED)
                           AND (is_folder = :TYPE_FILE)
                           RETURNING file_id";
                $UserFilesUpdated = Yii::$app->db->createCommand($query3, [
                    'type_update'     => UserFileEvents::TYPE_UPDATE,
                    'event_timestamp' => $old_timestamp,
                    'current_user_id' => $current_user_id,
                    'FILE_UPDATED'    => UserFiles::FILE_UPDATED,
                    'FILE_UNUPDATED'  => UserFiles::FILE_UNUPDATED,
                    'TYPE_FILE'       => UserFiles::TYPE_FILE,
                ])->queryAll();
                foreach ($UserFilesUpdated as $file) {
                    $UserFile = UserFiles::findOne(['file_id' => $file['file_id']]);
                    /** @var \common\models\UserFiles $UserFile */
                    $relativePath = UserFiles::getFullPath($UserFile);
                    if ($relativePath) {
                        $User = Users::getPathNodeFS($UserFile->user_id);
                        $file_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;
                        if (!$UserFile->is_folder) {
                            UserFiles::createFileInfo($file_name, $UserFile);
                        }
                    }
                }
                //$str = "End Cleanup restore for file\n\n";
                //$output .= $str; Functions::debugEcho($str);
            }

        }

        $str = "Total deleted events for all system = {$total_deleted}\n";
        $output .= $str; Functions::debugEcho($str);
        $str = "Finish at: " . date('Y-m-d H:i:s') . "\n\n";
        $output .= $str; Functions::debugEcho($str);

        return $output;
    }

    /**
     * @return string
     */
    public function deleteOldPatches_20181115()
    //public function deleteOldPatches()
    {
        $old_timestamp = time() - Preferences::getValueByKey('RestorePatchTTL', 2592000, 'int');

        $total_deleted = 0;
        $current_user_id = 0;
        $i = 0;
        $limit = 100000;
        $output = "";
        $str = "Start main stage: SELECT events grouped by file_id and user_id LIMIT {$limit} \n";
        $output .= $str; Functions::debugEcho($str);
        $str = "Start at: " . date('Y-m-d H:i:s') . "\n\n";
        $output .= $str; Functions::debugEcho($str);
        do {
            $offset = $limit * $i;
            //$str = "Iteration ". ($i+1) . " OFFSET = {$offset} \n\n";
            //$output .= $str; Functions::debugEcho($str);
            $query = "SELECT
                          max(event_id) as event_id,
                          file_id,
                          user_id
                      FROM {{%user_file_events}}
                      WHERE event_timestamp < :event_timestamp
                      GROUP BY file_id, user_id
                      ORDER BY user_id ASC, file_id ASC
                      LIMIT {$limit} OFFSET {$offset}";
            $events = Yii::$app->db->createCommand($query, ['event_timestamp' => $old_timestamp])->queryAll();
            if (sizeof($events) > 0) {
                $current_user_id = $events[0]['user_id'];
                $total_deleted_for_user = 0;
                $str = "Current user_id = {$current_user_id} \n";
                $output .= $str; Functions::debugEcho($str);
                foreach ($events as $key=>$event) {
                    /** удаляем все устаревшие евенты */
                    $deleted = UserFileEvents::deleteAll('(file_id = :file_id) AND (event_id < :event_id)', [
                        'file_id' => $event['file_id'],
                        'event_id' => $event['event_id'],
                    ]);
                    $total_deleted += $deleted;
                    $total_deleted_for_user += $deleted;
                    //$str = "Deleted {$deleted} events for file_id={$event['file_id']} \n";
                    //$output .= $str; Functions::debugEcho($str);

                    /** если при переборе массива изменился юзер-ид, значит нужно выяснить какой последний евент был у $current_user_id и отправить данные на редис */
                    if (($current_user_id != $event['user_id']) || !isset($events[$key + 1])) {
                        if ($total_deleted_for_user > 0) {
                            $str = "Execute send data to redis for this user.\n";
                            $output .= $str; Functions::debugEcho($str);
                            $str = "Total deleted events for current_user = {$total_deleted_for_user}\n";
                            $output .= $str; Functions::debugEcho($str);
                            $query2 = "SELECT
                                          event_id, event_uuid, user_id
                                       FROM {{%user_file_events}}
                                       WHERE user_id = :current_user_id
                                       ORDER BY event_id ASC
                                       LIMIT 1";
                            $min_user_event = Yii::$app->db->createCommand($query2, ['current_user_id' => $current_user_id])->queryOne();
                            /* Отправка на редис */
                            try {
                                $this->redis->publish(
                                    "user:{$current_user_id}:min_stored_event",
                                    Json::encode([
                                        'action_type' => 'min_stored_event',
                                        'event_uuid' => $min_user_event['event_uuid'],
                                    ])
                                );
                                $this->redis->save();
                            } catch (\Exception $e) {}
                            /* Обновление данных о юзере */
                            Users::updateAll(['first_event_uuid_after_cron' => $min_user_event['event_uuid']], ['user_id' => $current_user_id]);
                            $str = "Sent data to user:{$current_user_id}:min_stored_event === {$min_user_event['event_uuid']}\n";
                            $output .= $str; Functions::debugEcho($str);

                            /* Поиск всех файлов у которых последний last_event_type = TYPE_DELETE и file_lastatime < $old_timestamp */
                            /* Находим эти файлы и удаляем их из ФС затем удаляем сами файлы и их евенты */
                            /* Тут есть небольшой конфликт при удалении родительской папки в которой еще есть папки удаленные */
                            /* Но в общем итоге после обработки всех эелементов конфликт пропадает */
                            $str = "Delete all files which are deleted (Clean fm-fs)\n";
                            $output .= $str; Functions::debugEcho($str);
                            $UserFiles = UserFiles::find()
                                ->where([
                                    'last_event_type' => UserFileEvents::TYPE_DELETE,
                                    'is_outdated'     => UserFiles::FILE_UNOUTDATED,
                                    'user_id'         => $current_user_id
                                ])
                                ->andWhere("file_lastatime < :event_timestamp", ['event_timestamp' => $old_timestamp])
                                ->orderBy(['is_folder' => SORT_ASC])
                                ->all();

                            foreach ($UserFiles as $UserFile) {

                                /** @var \common\models\UserFiles $UserFile */
                                $relativePath = UserFiles::getFullPath($UserFile);
                                if ($relativePath) {
                                    $User = Users::getPathNodeFS($UserFile->user_id);
                                    $file_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;
                                    if ($UserFile->is_folder) {
                                        FileSys::rmdir($file_name, true);
                                    } else {
                                        @unlink($file_name);
                                    }
                                }
                                $UserFile->is_outdated = UserFiles::FILE_OUTDATED;
                                $UserFile->save();
                                //$str = "Deleted {$UserFile->file_name} from fs for user_id={$UserFile->user_id} \n";
                                //$output .= $str; Functions::debugEcho($str);
                            }
                            $str = "End clean fm-fs\n\n";
                            $output .= $str; Functions::debugEcho($str);

                            /* Проверим есть ли евенты апдейта, и если нет, то запишем в файл инфу что нет откатов у него */
                            $str = "Cleanup restore for file\n";
                            $output .= $str; Functions::debugEcho($str);
                            UserFiles::updateAll(
                                [
                                    'is_updated' => UserFiles::FILE_UNUPDATED
                                ],
                                "(file_id NOT IN (
                                SELECT
                                    file_id
                                FROM {{%user_file_events}}
                                WHERE (event_type = :type_update)
                                AND (event_timestamp >= :event_timestamp)
                            )) AND (user_id = :current_user_id)",
                                [
                                    'type_update' => UserFileEvents::TYPE_UPDATE,
                                    'event_timestamp' => $old_timestamp,
                                    'current_user_id' => $current_user_id,
                                ]
                            );
                            $str = "End Cleanup restore for file\n\n";
                            $output .= $str; Functions::debugEcho($str);
                        } else {
                            //$str = "Total deleted events for current_user = {$total_deleted_for_user}\n\n";
                            //$output .= $str; Functions::debugEcho($str);
                        }

                        /* Смена курент-юзера */
                        $current_user_id = $event['user_id'];
                        $total_deleted_for_user = 0;
                        $str = "Current user_id = {$current_user_id} \n";
                        $output .= $str; Functions::debugEcho($str);
                    }
                }
            }
            $i++;
        } while (sizeof($events) > 0);
        $str = "Total deleted events for all system = {$total_deleted}\n";
        $output .= $str; Functions::debugEcho($str);
        $str = "Finish at: " . date('Y-m-d H:i:s') . "\n\n";
        $output .= $str; Functions::debugEcho($str);

        return $output;
    }

    /**
     * Удаление устаревших патчей
     */
    public function deleteOldPatches_2017()
    {
        $old_timestamp = time() - Preferences::getValueByKey('RestorePatchTTL', 2592000, 'int');

        /* Запрос для отправки на редис */
        echo "Start stage 1: SELECT query\n";
        /*
        $events = UserFileEvents::findBySql("
            SELECT
              t2.event_id, t2.event_uuid, t2.user_id
            FROM  (
                SELECT
                  max(event_id) as event_id,
                  user_id
                FROM {{%user_file_events}}
                WHERE event_timestamp < :event_timestamp
                GROUP BY user_id
            ) as t1
            INNER JOIN {{%user_file_events}} as t2 ON t1.event_id=t2.event_id
            ")
            ->params(['event_timestamp' => $old_timestamp])
            ->asArray()
            ->all();
        echo "Start stage 1: redis send (foreach)\n";
        foreach ($events as $event) {
            try {
                $this->redis->publish(
                    "user:{$event['user_id']}:min_stored_event",
                    Json::encode([
                        'action_type' => 'min_stored_event',
                        'event_uuid' => $event['event_uuid'],
                    ])
                );
                $this->redis->save();
            } catch (\Exception $e) {}
            Users::updateAll(['first_event_uuid_after_cron' => $event['event_uuid']], ['user_id' => $event['user_id']]);
            echo "Sent data to user:{$event['user_id']}:min_stored_event\n";
        }
        unset($events);
        */
        echo "End stage 1\n\n";

        /* Запрос для выборки тех евентов, которые удалять из базы */
        echo "Start stage 2: SELECT query \n";
        $events = UserFileEvents::findBySql("
            SELECT
              max(event_id) as event_id,
              file_id,
              user_id
            FROM {{%user_file_events}}
            WHERE event_timestamp < :event_timestamp
            GROUP BY file_id, user_id
            ")
            ->params(['event_timestamp' => $old_timestamp])
            ->asArray()
            ->all();
        echo "Start stage 2: foreach \n";
        $total_deleted = 0;
        foreach ($events as $event) {
            $deleted = UserFileEvents::deleteAll('(file_id = :file_id) AND (event_id < :event_id)', [
                'file_id'  => $event['file_id'],
                'event_id' => $event['event_id'],
            ]);
            $total_deleted += $deleted;
            echo "Deleted {$deleted} events for file_id={$event['file_id']} \n";
        }
        echo "End stage 2\n\n";

        /* Поиск всех файлов у которых последний last_event_type = TYPE_DELETE и file_lastatime < $old_timestamp */
        /* Находим эти файлы и удаляем их из ФС затем удаляем сами файлы и их евенты */
        /* Тут есть небольшой конфликт при удалении родительской папки в которой еще есть папки удаленные */
        /* Но в общем итоге после обработки всех эелементов конфликт пропадает */
        echo "Start stage 3: SELECT query\n";
        $UserFiles = UserFiles::find()
            ->where([
                'last_event_type' => UserFileEvents::TYPE_DELETE,
                'is_outdated'     => UserFiles::FILE_UNOUTDATED,
            ])
            ->andWhere("file_lastatime < :event_timestamp", ['event_timestamp' => $old_timestamp])
            ->orderBy(['is_folder' => SORT_ASC])
            ->all();

        echo "Start stage 3: foreach\n";
        foreach ($UserFiles as $UserFile) {
            //var_dump($UserFile);
            /** @var \common\models\UserFiles $UserFile */
            $relativePath = UserFiles::getFullPath($UserFile);
            if ($relativePath) {
                $User = Users::getPathNodeFS($UserFile->user_id);
                $file_name = $User->_full_path . DIRECTORY_SEPARATOR . $relativePath;
                if ($UserFile->is_folder) {
                    FileSys::rmdir($file_name, true);
                } else {
                    @unlink($file_name);
                }
            }
            $UserFile->is_outdated = UserFiles::FILE_OUTDATED;
            $UserFile->save();
            echo "Deleted from fs for user_id={$UserFile->user_id} \n";
        }
        echo "End stage 3\n\n";


        /* Проверим есть ли евенты апдейта, и если нет, то запишем в файл инфу что нет откатов у него */
        /*
        Выбрать все file_id из таблицы евентов (user_file_events) с event_type' => UserFileEvents::TYPE_UPDATE
        и event_timestamp >=  $old_timestamp
        Затем найти все файлы у которых file_id не совпадает с найденой выборкой
        Эти файлы и будут те у которых нет откатов и им установить is_updated' => UserFiles::FILE_UNUPDATED
        SELECT ( UPDATE ) * FROM {{%user_files}} WHERE file_id NOT IN (
            SELECT
            t2.file_id
            FROM {{%user_file_events}} as t2
            WHERE (t2.event_type=1)
            AND (t2.event_timestamp >=1509365216 )
        )
        */
        echo "Start stage 4: UPDATE\n";
        /*
        UserFiles::updateAll(
            [
                'is_updated' => UserFiles::FILE_UNUPDATED
            ],
            "file_id NOT IN (
                SELECT
                    file_id
                FROM {{%user_file_events}}
                WHERE (event_type = :type_update)
                AND (event_timestamp >= :event_timestamp)
            )",
            [
                'type_update'     => UserFileEvents::TYPE_UPDATE,
                'event_timestamp' => $old_timestamp
            ]
        );
        */
        echo "End stage 4\n\n";
        echo "Finish\n\n";
    }
}
