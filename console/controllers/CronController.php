<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\Json;
use common\helpers\Functions;
use common\models\Users;
use common\models\UserFiles;
use common\models\Preferences;
use common\models\Licenses;
use common\models\Notifications;
use common\models\CronInfo;
use common\models\TrafficLog;
use common\models\MailTemplatesStatic;
use common\models\UserActionsLog;
use common\models\UserAlertsLog;
use frontend\models\NodeApi;

/**
 * Site controller
 */
class CronController extends Controller
{
    public $task_start;
    public $task_finish;
    public $task_log;

    /**
     * Позволяет приходить запросам на этот скрипт и акшены перечисленные в массиве
     * с других доменов а не только с домена где стоит этот скрипт
     *
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->task_start = date(SQL_DATE_FORMAT);
        $this->task_log = "In progress...";
        return parent::beforeAction($action);
    }

    /**
     * @return string
     */
    public function setTaskFinish()
    {
        $this->task_finish = date(SQL_DATE_FORMAT);
        return $this->task_finish;
    }

    /**
     * Удаление устаревших патчей
     * Запускать в 00:00 каждый день
     * пример строки в крон файле: "0 0 * * * /var/www/Direct-link/yii cron/delete-old-patches"
     * @return bool
     */
    public function actionDeleteOldPatches()
    {
        CronInfo::setInfoForCronTask('cron/delete-old-patches', $this->task_start, null, 'Every day at 00:00', $this->task_log);

        $model = new NodeApi();

        try {
            $this->task_log = $model->deleteOldPatches();
        } catch (\Exception $e) {
            $this->task_log = Json::encode($e);
        }

        CronInfo::setInfoForCronTask('cron/delete-old-patches', $this->task_start, $this->setTaskFinish(), 'Every day at 00:00', $this->task_log);

        return true;
    }

    /**
     * Подсчет количества шар и коллабораций на текущий момент.
     * Затем будет высчитываться прирост или убыль этих значений
     * за текущие сутки на главной странице админки
     * Запускать в 00:00 каждый день
     * пример строки в крон файле: "0 0 * * * /var/www/Direct-link/yii cron/calculate-count-shares-and-collaborations"
     * @return bool
     */
    public function actionCalculateCountSharesAndCollaborations()
    {
        CronInfo::setInfoForCronTask('cron/calculate-count-shares-and-collaborations', $this->task_start, null, 'Every day at 00:00', $this->task_log);

        $this->task_log = "";

        $sql = "SELECT
                  'PrefHiddenTotalSharesCount' as pref_key,
                  count(*) as cnt,
                  1 as sortf
                FROM {{%user_files}}
                WHERE is_shared = :FILE_SHARED
                  UNION ALL
                SELECT
                  'PrefHiddenTotalCollaborationsCount' as pref_key,
                  count(*) as cnt,
                  2 as sortf
                FROM {{%user_collaborations}} as t1
                INNER JOIN {{%user_files}} as t2 ON (t1.file_uuid=t2.file_uuid) AND (t1.user_id=t2.user_id)
                WHERE  t2.file_id IS NOT NULL
                ORDER BY sortf ASC
                ";

        $res = Yii::$app->db->createCommand($sql, [
            'FILE_SHARED'       => UserFiles::FILE_SHARED,
        ])->queryAll();

        foreach ($res as $v) {
            $str =  "{$v['pref_key']} = {$v['cnt']}\n";
            $this->task_log .= $str; Functions::debugEcho($str);
            $re = Preferences::setValueByKey($v['pref_key'], $v['cnt'], Preferences::CATEGORY_HIDDEN);
            //var_dump($re);
        }

        CronInfo::setInfoForCronTask('cron/calculate-count-shares-and-collaborations', $this->task_start, $this->setTaskFinish(), 'Every day at 00:00', $this->task_log);

        return true;
    }

    /**
     * Очистка устаревших записей логов
     * Запускать в 00:00 каждый день
     * пример строки в крон файле: "0 0 * * * /var/www/Direct-link/yii cron/delete-old-logs"
     * @return bool
     */
    public function actionDeleteOldLogs()
    {
        CronInfo::setInfoForCronTask('cron/delete-old-logs', $this->task_start, null, 'Every day at 00:00', $this->task_log);

        $this->task_log = "";

        $expired = date(SQL_DATE_FORMAT, time() - 86400*30);

        $cnt = TrafficLog::deleteAll('record_created < :record_created', ['record_created' => $expired]);
        $str = "Deleted from TrafficLog records which date is less than '{$expired}'.\n\nCount deleted records = {$cnt}\n\n";
        $this->task_log .= $str; Functions::debugEcho($str);

        $cnt = UserAlertsLog::deleteAll('alert_created < :alert_created', ['alert_created' => $expired]);
        $str = "Deleted from UserAlertsLog records which date is less than '{$expired}'.\n\nCount deleted records = {$cnt}\n\n";
        $this->task_log .= $str; Functions::debugEcho($str);

        $cnt = UserActionsLog::deleteAll('action_created < :action_created', ['action_created' => $expired]);
        $str = "Deleted from UserActionsLog records which date is less than '{$expired}'.\n\nCount deleted records = {$cnt}\n\n";
        $this->task_log .= $str; Functions::debugEcho($str);

        CronInfo::setInfoForCronTask('cron/delete-old-logs', $this->task_start, $this->setTaskFinish(), 'Every day at 00:00', $this->task_log);

    }

    /**
     * Обнуление счетчика шар для фришных лицензий
     * Запускать в 00:00 каждый день
     * пример строки в крон файле: "0 0 * * * /var/www/Direct-link/yii cron/reset-share-count-in24"
     * @return bool
     */
    public function actionResetShareCountIn24()
    {
        CronInfo::setInfoForCronTask('cron/reset-share-count-in24', $this->task_start, null, 'Every day at 00:00', $this->task_log);

        $cnt = Users::updateAll(['shares_count_in24' => 0]);
        $this->task_log =  "Reset shares successfully fo FREE.\n\nCount updated records (users) = {$cnt}\n\n";
        //Functions::debugEcho($this->task_log);

        CronInfo::setInfoForCronTask('cron/reset-share-count-in24', $this->task_start, $this->setTaskFinish(), 'Every day at 00:00', $this->task_log);

        return true;
    }

    /**
     * Отправка емейла о том, что лицензия скоро истекает
     * Запускать в 00:00 каждый день
     * пример строки в крон файле: "0 0 * * * /var/www/Direct-link/yii cron/send-email-license-will-expire-soon"
     * @return bool
     */
    public function actionSendEmailLicenseWillExpireSoon()
    {
        CronInfo::setInfoForCronTask('cron/send-email-license-will-expire-soon', $this->task_start, null, 'Every day at 00:00', $this->task_log);

        $this->task_log = "";

        $BonusPeriodLicense = Preferences::getValueByKey('BonusPeriodLicense', 72, 'integer') * 3600;
        $UsersExpireSoon = Users::find()
            ->where([
                'license_type' => [Licenses::TYPE_PAYED_BUSINESS_ADMIN, Licenses::TYPE_PAYED_PROFESSIONAL],
                //'pay_type'     => Users::PAY_CRYPTO,
            ])
            ->andWhere("(user_status = :user_status) AND (license_expire BETWEEN :license_expire1 AND :license_expire2)", [
                'user_status'    => Users::STATUS_CONFIRMED,
                'license_expire1' => date(SQL_DATE_FORMAT, time()),
                'license_expire2' => date(SQL_DATE_FORMAT, time() + $BonusPeriodLicense),
            ])
            ->all();

        /** @var \common\models\Users $User */
        foreach ($UsersExpireSoon as $User) {

            if  (!Users::isAutoPayType($User->pay_type)) {

                if ($User->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                    $pay_link = Yii::$app->urlManager->createAbsoluteUrl(['purchase/business']);
                } else {
                    $pay_link = Yii::$app->urlManager->createAbsoluteUrl(['purchase/professional']);
                }

                /* Емейл на почту */

                MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_LicenseExpireSoon, $User->user_email, [
                    'license_type' => Licenses::getType($User->license_type),
                    'license_expire' => Functions::formatPostgresDate(Yii::$app->params['datetime_short_format'], $User->license_expire),
                    'user_email' => $User->user_email,
                    'user_name' => $User->user_name,
                    'user_company_name' => $User->user_company_name,
                    'pay_link' => $pay_link,
                ]);
                $str = "Created email about license ({$User->license_type}) expire soon for user_id={$User->user_id}, user_email={$User->user_email}, LicenseExpireDate={$User->license_expire} \n";
                $this->task_log .= $str; Functions::debugEcho($str);

            }

        }

        CronInfo::setInfoForCronTask('cron/send-email-license-will-expire-soon', $this->task_start, $this->setTaskFinish(), 'Every day at 00:00', $this->task_log);

        return true;
    }

    /**
     * Отправка емейла о том, что лицензия истекла и если закончился и бонусный перод - сброс на фрии
     * Запускать в 00:00 каждый день
     * пример строки в крон файле: "0 0 * * * /var/www/Direct-link/yii cron/send-email-license-expired"
     * @return bool
     */
    public function actionSendEmailLicenseExpired()
    {
        CronInfo::setInfoForCronTask('cron/send-email-license-expired', $this->task_start, null, 'Every day at 00:00', $this->task_log);

        $this->task_log = "";
        $str = "Start at: " . date('Y-m-d H:i:s') . "\n\n";
        $this->task_log .= $str; Functions::debugEcho($str);

        $trial_check_expire = date(SQL_DATE_FORMAT, time() - Licenses::getCountDaysTrialLicense() * 86400);


        $limit = 100;
        $iteration = 0;
        do {
            $query = "SELECT * FROM {{%users}}
                  WHERE (user_status != :STATUS_BLOCKED)
                  AND (
                    ((license_type IN (:TYPE_PAYED_BUSINESS_ADMIN, :TYPE_PAYED_PROFESSIONAL)) AND (license_expire <= :license_expire))
                    OR
                    ((license_type = :TYPE_FREE_TRIAL) AND (user_created <= :trial_check_expire))
                  )
                  ORDER BY user_id
                  LIMIT {$limit}";
            $UsersExpired = Users::findBySql($query, [
                'TYPE_PAYED_BUSINESS_ADMIN' => Licenses::TYPE_PAYED_BUSINESS_ADMIN,
                'TYPE_PAYED_PROFESSIONAL' => Licenses::TYPE_PAYED_PROFESSIONAL,
                'TYPE_FREE_TRIAL' => Licenses::TYPE_FREE_TRIAL,
                'STATUS_BLOCKED' => Users::STATUS_BLOCKED,
                'license_expire' => date(SQL_DATE_FORMAT, time()),
                'trial_check_expire' => $trial_check_expire,
            ])->all();

            $iteration++;
            $count_of_result = sizeof($UsersExpired);

            $str = "Iteration #{$iteration}, selected {$count_of_result} records (users) \n\n";
            $this->task_log .= $str; Functions::debugEcho($str);

            if ($count_of_result > 0) {

                //$transaction = Yii::$app->db->beginTransaction();

                /** @var \common\models\Users $User */
                foreach ($UsersExpired as $User) {

                    if ($User->license_type != Licenses::TYPE_FREE_TRIAL) {
                        if ($User->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                            //$pay_link = Yii::$app->urlManager->createAbsoluteUrl(['user/profile', 'tab' => 2]);
                            //$pay_link_data = ['user/profile', 'tab' => 2];
                            $pay_link = Yii::$app->urlManager->createAbsoluteUrl(['pricing']);
                            $pay_link_data = ['pricing'];
                        } else {
                            //$pay_link = Yii::$app->urlManager->createAbsoluteUrl(['user/profile', 'tab' => 2]);
                            //$pay_link_data = ['user/profile', 'tab' => 2];
                            $pay_link = Yii::$app->urlManager->createAbsoluteUrl(['pricing']);
                            $pay_link_data = ['pricing'];
                        }

                        if ($User->expired_notif_sent == Users::EXPIRED_NOTIF_NOT_SENT) {
                            /* Нотиф в мемберку */
                            $notif = new Notifications();
                            $notif->user_id = $User->user_id;
                            $notif->notif_isnew = Notifications::IS_NEW;
                            $notif->notif_type = Notifications::TYPE_LICENSE_EXPIRED;
                            $notif->notif_data = serialize([
                                'search' => [
                                    '{license_type}',
                                    '{license_expire}',
                                    '{user_email}',
                                    '{user_name}',
                                    '{user_company_name}',
                                    //'{pay_link}',
                                ],
                                'replace' => [
                                    Licenses::getType($User->license_type),
                                    Functions::formatPostgresDate(Yii::$app->params['datetime_short_format'], $User->license_expire),
                                    $User->user_email,
                                    $User->user_name,
                                    $User->user_company_name,
                                    //$pay_link,
                                ],
                                'links_data' => [
                                    'pay_link' => $pay_link_data,
                                ],
                            ]);
                            $notif->save();
                            $str = "Created notif about license ({$User->license_type}) expired for user_id={$User->user_id} user_email={$User->user_email}, LicenseExpireDate={$User->license_expire} \n\n";
                            $this->task_log .= $str; Functions::debugEcho($str);

                            /* Емейл на почту */
                            if ($User->user_status == Users::STATUS_CONFIRMED) {
                                MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_LicenseExpired, $User->user_email, [
                                    'license_type' => Licenses::getType($User->license_type),
                                    'license_expire' => Functions::formatPostgresDate(Yii::$app->params['datetime_short_format'], $User->license_expire),
                                    'user_email' => $User->user_email,
                                    'user_name' => $User->user_name,
                                    'user_company_name' => $User->user_company_name,
                                    'pay_link' => $pay_link,
                                ]);
                                $str = "Created email about license ({$User->license_type}) expired for user_id={$User->user_id}, user_email={$User->user_email}, LicenseExpireDate={$User->license_expire} \n\n";
                                $this->task_log .= $str; Functions::debugEcho($str);
                            }

                            $User->expired_notif_sent = Users::EXPIRED_NOTIF_SENT;
                            if (!$User->save()) {
                                $str = "!!! ERROR on save User: " . Json::encode($User->getErrors());
                                $this->task_log .= $str; Functions::debugEcho($str);
                            }
                        } else {
                            $str = "Email was sent early (no need repeatedly sent) (Email about license ({$User->license_type}) expired for user_id={$User->user_id}, user_email={$User->user_email}, LicenseExpireDate={$User->license_expire}) \n\n";
                            $this->task_log .= $str; Functions::debugEcho($str);
                        }

                        /* Если лицензия уже истекла (просрочен бонусный период) то смена на фрии */
                        if ($User->license_expire) {
                            $BonusPeriodLicense = Preferences::getValueByKey('BonusPeriodLicense', 72, 'integer') * 3600;
                            if (strtotime($User->license_expire) + $BonusPeriodLicense < time()) {
                                $str = "Set the FREE_DEFAULT license (was {$User->license_type}) for user_id={$User->user_id}, user_email={$User->user_email}, LicenseExpireDate={$User->license_expire} \n\n";
                                $this->task_log .= $str; Functions::debugEcho($str);
                                $User->license_type = Licenses::TYPE_FREE_DEFAULT;
                                if (!$User->save()) {
                                    $str = "!!! ERROR on save User: " . Json::encode($User->getErrors());
                                    $this->task_log .= $str; Functions::debugEcho($str);
                                }
                            }
                        }
                    } else {
                        $str = "Set the FREE_DEFAULT license (was {$User->license_type}) for user_id={$User->user_id}, user_email={$User->user_email}, RegistrationDate={$User->user_created} \n\n";
                        $this->task_log .= $str; Functions::debugEcho($str);

                        //$User->license_type = Licenses::TYPE_FREE_DEFAULT;
                        $User->license_expire = null;
                        if (!$User->save()) {
                            $str = "!!! ERROR on save User: " . Json::encode($User->getErrors());
                            $this->task_log .= $str; Functions::debugEcho($str);
                        }
                    }
                }

                //$transaction->commit();

            }

        } while ($count_of_result >= $limit);

        $str = "Finish at: " . date('Y-m-d H:i:s') . "\n\n";
        $this->task_log .= $str; Functions::debugEcho($str);

        CronInfo::setInfoForCronTask('cron/send-email-license-expired', $this->task_start, $this->setTaskFinish(), 'Every day at 00:00', $this->task_log);

        return true;
    }

}
