<?php
namespace frontend\modules\api\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use common\helpers\Functions;
use common\models\Maintenance;
use common\models\Preferences;
use frontend\models\NodeApi;
use frontend\models\ShApi;

class SelfHostedController extends Controller
{
    private $error = "";
    private static $ALLOWED_METHODS = [
        'license_check_data',
        'license_check_result',
        'license_check',
    ];

    /**
     * Позволяет приходить запросам на этот скрипт и акшены перечисленные в массиве
     * с других доменов а не только с домена где стоит этот скрипт
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        set_time_limit(0);
        if (in_array($action->id, ['index'])) {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * Проверяет валидность массива $request полученного методом POST из JSON строки
     * @param array $request
     * @return bool
     */
    private function validate($request)
    {
        if (($request === null) || empty($request['action']) || empty($request['data'])) {
            $this->error = "Invalid JSON";
            return false;
        }

        if (!in_array($request['action'], self::$ALLOWED_METHODS)) {
            $this->error = "Not allowed method in JSON";
            return false;
        }

        return true;
    }

    /**
     * Основная ф-ия обработки запросов от питонщиков (роутер методов nodeinfo и т.д.)
     * Возвращает массив для ответа в формате JSON
     * @return array
     */
    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->language = "en";

        // если система на обслуживании
        if (Yii::$app->params['Stop_NodeApi_and_FM']) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_STOPPED_FOR_MAINTENANCE,
                'info'    => "NodeApi and FileManager are suspended for maintenance.",
            ];
        }

        // если система на обслуживании
        $Maintenance = Maintenance::getMaintenance();
        if ($Maintenance->maintenance_suspend_api) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_STOPPED_FOR_MAINTENANCE,
                'info'    => "NodeApi and FileManager are suspended for maintenance.",
                'message' => nl2br(str_replace([
                    '{maintenance_start}',
                    '{maintenance_finish}',
                    '{maintenance_left}',
                ], [
                    date(Yii::$app->params['datetime_short_format'], $Maintenance->maintenance_start_int),
                    date(Yii::$app->params['datetime_short_format'], $Maintenance->maintenance_finish_int),
                    Functions::getHumanReadableLeftTime($Maintenance->maintenance_left_int),
                ], $Maintenance->maintenance_text)),
            ];
        }

        $request = json_decode(Yii::$app->request->getRawBody(), true);

        // Валидный ли джсон
        if (!$this->validate($request)) {
            return [
                'result'  => "error",
                'errcode' => ShApi::ERROR_INVALID_JSON,
                'info'    => "Bad request ({$this->error})"
            ];
        }

        // если все верхние проверки пройдены то выдаем ответ на конкретный акшен
        return $this->{$request['action']}($request['data']);
    }

    /**
     * Метод license_check_data запрос приходит от сигнального на сервер https://domain (СХ сайт юзера)
     * @param array $data
     * @return array
     */
    protected function license_check_data($data)
    {
        $model = new ShApi(['signal_passphrase']);
        if (!$model->load(['ShApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => ShApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => ShApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        return $model->license_check_data();
    }

    /**
     * Метод license_check запрос приходит от сигнального на сервер https://pvtbox.net
     * с данными полученными в запросе license_check_data
     * @param array $data
     * @return array
     */
    public function license_check($data)
    {
        $model = new ShApi(['shu_user_hash', 'license_count_used']);
        if (!$model->load(['ShApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => ShApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }
        return $model->license_check();
    }

    /**
     * Метод license_check_result запрос приходит от сигнального на сервер https://domain (СХ сайт юзера)
     * @param array $data
     * @return array
     */
    protected function license_check_result($data)
    {
        $model = new ShApi(['signal_passphrase', 'result']);
        if (!$model->load(['ShApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => ShApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => ShApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        return $model->license_check_result();
    }
}
