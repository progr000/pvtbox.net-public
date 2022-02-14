<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Json;
use backend\models\Admins;
use common\helpers\Functions;
use common\models\Users;
use common\models\Mailq;
use common\models\Licenses;
use common\models\Preferences;
use common\models\UserLicenses;
use common\models\Pages;
use common\models\Servers;
use common\models\Sessions;
use common\models\UserActionsLog;
use common\models\UserCollaborations;
use common\models\UserColleagues;
use common\models\UserFileEvents;
use common\models\UserFiles;
use common\models\UserNode;
use common\models\UserPayments;
use common\models\UserServerLicenses;
use common\models\UserUploads;
use frontend\models\NodeApi;

/**
 * Site controller
 */
class ConsoleController extends Controller
{
    public $mail_id;
    public $status;
    public $description;

    public $userId;
    public $restorePatchTTL;

    public $user_email;
    public $user_password;
    public $license_key;

    public $SignalAccessKey;

    public $server_type;
    public $server_url;
    public $server_ip;
    public $server_port;
    public $server_login;
    public $server_password;
    public $server_description;

    /**
     * При разработке консольного приложения принято использовать код возврата.
     * Принято, код 0 (ExitCode::OK) означает, что команда выполнилась удачно.
     * Если команда вернула код больше нуля, то это говорит об ошибке.
     */

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        $ret = parent::options($actionID);

        if ($actionID == 'mail-status'){
            return array_merge($ret, [
                'mail_id',
                'status',
                'description',
            ]);
        }

        if ($actionID == 'delete-old-patches'){
            return array_merge($ret, [
                'userId',
                'restorePatchTTL',
            ]);
        }

        if ($actionID == 'create-user'){
            return array_merge($ret, [
                'user_email',
                'user_password',
                'license_key',
            ]);
        }

        if ($actionID == 'create-signal-access-key'){
            return array_merge($ret, [
                'SignalAccessKey',
            ]);
        }

        if ($actionID == 'create-server'){
            return array_merge($ret, [
                'server_type',
                'server_url',
                'server_ip',
                'server_port',
                'server_login',
                'server_password',
                'server_description',
            ]);
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        echo "\n";
        return parent::beforeAction($action);
    }

    /**
     * Установка статуса для емейла по ео ИД
     * пример строки вызова: ./yii console/mail-status --mail_id=UNIQUE_ID --status=bounced --description="some text"
     * @return bool
     */
    public function actionMailStatus()
    {
        //echo $this->mail_id . "\n";
        //echo $this->status . "\n";
        //echo $this->description . "\n";
        if (!$this->mail_id || !$this->status) {
            echo "Params 'mail_id' and 'status' are required.\n";
            echo "Usage example:\n";
            echo './yii console/mail-status --mail_id=UNIQUE_ID --status=bounced --description="some text"' . "\n\n";
            return ExitCode::NOINPUT;
        }

        $cnt = 0;
        $cnt = Mailq::updateAll([
            'mailer_letter_status' => $this->status,
            'mailer_description' => $this->description],

            ['mailer_letter_id' => $this->mail_id]);
        Mailq::invalidateCache();
        echo "OK - updated {$cnt} record.\n\n";

        return ExitCode::OK;
    }

    /**
     * Удаление устаревших патчей для юзера через консольный скрипт
     * Запускать в 00:00 каждый день
     * пример строки в крон файле: "0 0 * * * ./yii console/delete-old-patches"
     * @return bool
     */
    public function actionDeleteOldPatches()
    {
        if ($this->userId === null || $this->restorePatchTTL === null) {
            echo "Params 'userId' and 'restorePatchTTL' are required.\n";
            echo "Usage example:\n";
            echo './yii console/delete-old-patches --userId=111 --restorePatchTTL=0' . "\n\n";
            return ExitCode::NOINPUT;
        }

        $User = Users::findIdentity($this->userId);
        if (!$User) {
            echo Json::encode([
                'result'  => "error",
                'errcode' => NodeApi::ERROR_USER_NOT_FOUND,
                'info'    => "User not fond for user_id={$this->userId}",
            ]);
            return ExitCode::DATAERR;
        }

        $User->user_dop_status = Users::DOP_IN_PROGRESS;
        $User->save();

        $model = new NodeApi(['DOP_onlyForUserId', 'DOP_restorePatchTTL']);
        if (!$model->load(['NodeApi' => [
                'DOP_onlyForUserId'   => $this->userId,
                'DOP_restorePatchTTL' => $this->restorePatchTTL,
            ]
            ]) || !$model->validate()) {
            $task_log = Json::encode([
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ]);

            $User->user_dop_log = $task_log;
            $User->user_dop_status = Users::DOP_IS_COMPLETE;
            $User->save();

            return ExitCode::DATAERR;
        }

        try {
            $task_log = $model->deleteOldPatches();
        } catch (\Exception $e) {
            $task_log = Json::encode($e);
        }

        $User->user_dop_log = $task_log;
        $User->user_dop_status = Users::DOP_IS_COMPLETE;
        $User->save();

        return ExitCode::OK;
    }

    /**
     * Делает зачистку в БД для селф-хостед (преинит)
     */
    public function actionClearAllDataInBd()
    {
        /* Удаляем все записи из таблицы админов */
        Admins::deleteAll();

        /* Удаляем все статик-страницы из базы */
        Pages::deleteAll();

        /* Удаляем все записи о серверах */
        Servers::deleteAll();

        /* Удаляем все записи из таблицы юзеров */
        Users::deleteAll();

        /* на всякий случай */
        UserFileEvents::deleteAll();
        UserFiles::deleteAll();
        UserLicenses::deleteAll();
        UserServerLicenses::deleteAll();
        UserUploads::deleteAll();
        UserNode::deleteAll();
        UserPayments::deleteAll();
        UserCollaborations::deleteAll();
        UserColleagues::deleteAll();
        UserActionsLog::deleteAll();
        UserActionsLog::deleteAll();
        Sessions::deleteAll();

        /**/
        Preferences::setValueByKey('adminEmail', '', Preferences::CATEGORY_BASE);
        Preferences::setValueByKey('supportEmail_TECHNICAL', '', Preferences::CATEGORY_BASE);
        Preferences::setValueByKey('supportEmail_OTHER', '', Preferences::CATEGORY_BASE);
        Preferences::setValueByKey('supportEmail_LICENSES', '', Preferences::CATEGORY_BASE);

        Preferences::setValueByKey('seoTwitterLink', '', Preferences::CATEGORY_SEO);
        Preferences::setValueByKey('seoFbLink', '', Preferences::CATEGORY_SEO);
        Preferences::setValueByKey('seoVkLink', '', Preferences::CATEGORY_SEO);

        Preferences::setValueByKey('seoAdditionalMetaTagsAll', '', Preferences::CATEGORY_SEO);
        Preferences::setValueByKey('seoAdditionalMetaTagsGuest', '', Preferences::CATEGORY_SEO);
        Preferences::setValueByKey('seoAdditionalMetaTagsMember', '', Preferences::CATEGORY_SEO);

        Preferences::setValueByKey('reCaptchaPublicKey', '', Preferences::CATEGORY_RECAPTCHA);
        Preferences::setValueByKey('reCaptchaSecretKey', '', Preferences::CATEGORY_RECAPTCHA);
        Preferences::setValueByKey('reCaptchaGoogleAcc', '', Preferences::CATEGORY_RECAPTCHA);

        Preferences::setValueByKey('paypalSellerEmail', '', Preferences::CATEGORY_HIDDEN);
        Preferences::setValueByKey('SignalAccessKey', '', Preferences::CATEGORY_HIDDEN);

        Preferences::setValueByKey('PricePerMonthForLicenseProfessional', '1.00', Preferences::CATEGORY_HIDDEN);
        Preferences::setValueByKey('PricePerMonthUserForLicenseBusiness', '1.00', Preferences::CATEGORY_HIDDEN);
        Preferences::setValueByKey('PricePerYearForLicenseProfessional', '1.00', Preferences::CATEGORY_HIDDEN);
        Preferences::setValueByKey('PricePerYearUserForLicenseBusiness', '1.00', Preferences::CATEGORY_HIDDEN);
        Preferences::setValueByKey('PriceOneTimeForLicenseProfessional', '1.00', Preferences::CATEGORY_HIDDEN);

        echo "Done\n";
        return ExitCode::OK;
    }

    /**
     * Создаст нового юзера по user_email и user_password
     * пример строки запуска: "./yii console/create-user --user_email=UNIQUE_EMAIL --user_password=PLAIN_PASSWORD --license_key=KEY"
     * @return int
     */
    public function actionCreateUser()
    {
        if (!$this->user_email || !$this->user_password) {
            echo "Params 'user_email', 'user_password' and 'license_key' are required.\n";
            echo "Usage example:\n";
            echo './yii console/create-user --user_email=UNIQUE_EMAIL --user_password=PLAIN_PASSWORD --license_key=KEY' . "\n\n";
            return ExitCode::NOINPUT;
        }

        $User = Users::findByEmail($this->user_email);
        if ($User) {
            echo Json::encode([
                'result'  => "error",
                'errcode' => "USER_EXIST",
                'info'    => "User already exist",
            ]). "\n";
            return ExitCode::DATAERR;
        }

        $transaction = Yii::$app->db->beginTransaction();

        /* Создаем админа системы в бекенде */
        $admin = new Admins();
        $admin->admin_name   = Functions::getNameFromEmail($this->user_email);
        $admin->admin_email  = $this->user_email;
        $admin->admin_role   = Admins::ROLE_ROOT;
        $admin->admin_status = Admins::STATUS_ACTIVE;
        $admin->setPassword($this->user_password);
        $admin->generateAuthKey();
        $admin->generatePasswordResetToken();
        if (!$admin->save()) {
            echo Json::encode([
                    'result'  => "error",
                    'errcode' => "DB_ERROR",
                    'info'    => $admin->getErrors(),
                ]). "\n";
            $transaction->rollBack();
            return ExitCode::DATAERR;
        }

        /* Создаем бизнес-админ-юзера во фронтенде */
        $user                     = new Users();
        $user->user_name          = Functions::getNameFromEmail($this->user_email);
        $user->user_email         = $this->user_email;
        $user->license_type       = Licenses::TYPE_PAYED_BUSINESS_ADMIN;
        $user->license_expire     = date(SQL_DATE_FORMAT, time() + 365 * 100 * 86400);
        $user->license_period     = Licenses::PERIOD_ANNUALLY;
        $user->pay_type           = Users::PAY_CARD;
        $user->user_last_ip       = '127.0.0.1';
        $user->license_key_for_sh = $this->license_key;
        $user->user_status        = Users::STATUS_CONFIRMED;
        $user->setPassword($this->user_password);
        $user->generateAuthKey();

        //$user->user_status = Users::STATUS_BLOCKED;
        $user->generatePasswordResetToken();

        if ($user->save()) {

            $lic = new UserLicenses();
            $lic->lic_start = $user->user_created;
            $lic->lic_end = $user->license_expire;
            $lic->lic_period = $user->license_period;
            $lic->lic_owner_user_id = $user->user_id;
            $lic->lic_colleague_user_id = $user->user_id;
            $lic->lic_colleague_email = $user->user_email;
            $lic->lic_lastpay_timestamp = time();
            $lic->lic_group_id = $lic->lic_lastpay_timestamp;
            if (!$lic->save()) {
                echo Json::encode([
                        'result'  => "error",
                        'errcode' => "DB_ERROR",
                        'info'    => $lic->getErrors(),
                    ]). "\n";
                $transaction->rollBack();
                return ExitCode::DATAERR;
            }

            Preferences::setValueByKey('adminEmail', $this->user_email);
            Preferences::setValueByKey('supportEmail_TECHNICAL', $this->user_email);
            Preferences::setValueByKey('supportEmail_OTHER', $this->user_email);
            Preferences::setValueByKey('supportEmail_LICENSES', $this->user_email);

            $transaction->commit();
            echo "User created.\n";
            return ExitCode::OK;
        }

        $transaction->rollBack();
        echo Json::encode([
                'result'  => "error",
                'errcode' => "DB_ERROR",
                'info'    => $user->getErrors(),
            ]). "\n";
        return ExitCode::DATAERR;
    }

    /**
     * Запишет значение SignalAccessKey в настройки
     * пример строки запуска: "./yii console/create-signal-access-key --SignalAccessKey=KEY_VALUE"
     * @return int
     */
    public function actionCreateSignalAccessKey()
    {
        if (!$this->SignalAccessKey) {
            echo "Params 'SignalAccessKey' required.\n";
            echo "Usage example:\n";
            echo './yii console/create-signal-access-key --SignalAccessKey=KEY_VALUE' . "\n\n";
            return ExitCode::NOINPUT;
        }

        Preferences::setValueByKey('SignalAccessKey', $this->SignalAccessKey, Preferences::CATEGORY_NODEAPI, 'Access key to API for signaling server');
        echo "SignalAccessKey wrote.\n";
        return ExitCode::OK;
    }

    /**
     * Делает зачистку в БД таблицы серверов
     * ./yii console/clear-servers-bd
     * @return int
     */
    public function actionClearServersBd()
    {
        /* Удаляем все записи о серверах */
        Servers::deleteAll();
        echo "Servers table is clear. Now create new records for it by the command ./yii console/create-server --params ...\n";
        return ExitCode::OK;
    }

    /**
     * Создает новую запись о сервере в таблице серверов
     * ./yii console/create-server --server_type=TURN|STUN|SIGN|PROXY --server_url=URL [--server_login=LOGIN --server_password=PASSWORD --server_description=DESCRIPTION]
     * @return int
     */
    public function actionCreateServer()
    {
        if (!$this->server_url || !$this->server_type) {
            echo "Params 'server_type' and 'server_url' are required.\n";
            echo "Usage example:\n";
            echo './yii console/create-server --server_type=TURN|STUN|SIGN|PROXY --server_url=URL [--server_login=LOGIN --server_password=PASSWORD --server_description=DESCRIPTION]' . "\n\n";
            return ExitCode::NOINPUT;
        }

        $Server = new Servers();
        $Server->server_type = $this->server_type;
        $Server->server_url = $this->server_url;
        $Server->server_ip = '0.0.0.0';
        $Server->server_port = '0';
        $Server->server_login = $this->server_login ? $this->server_login : '';
        $Server->server_password = $this->server_password ? $this->server_password : '';
        $Server->server_title = $this->server_description ? $this->server_description : $Server->server_url . "[". $this->server_type . "]";
        $Server->server_status = Servers::SERVER_ACTIVE_YES;

        if (!$Server->save()) {
            echo Json::encode([
                    'result'  => "error",
                    'errcode' => "DB_ERROR",
                    'info'    => $Server->getErrors(),
                ]). "\n";
            return ExitCode::DATAERR;
        }

        echo "Server created.\n";
        return ExitCode::OK;
    }
}
