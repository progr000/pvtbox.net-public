<?php
namespace frontend\modules\api\controllers;

use frontend\models\ConferenceApi;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use common\helpers\Functions;
use common\models\Maintenance;
use common\models\BadLogins;
use common\models\Preferences;
use common\models\UserNode;
use frontend\models\NodeApi;
use frontend\models\ShApi;

class SignalController extends Controller
{
    private $error = "";
    private static $ALLOWED_METHODS = [
        'nodeinfo',
        'nodelist',
        'checknodeauth',
        'checkbrowserauth',
        'sharing_list',
        'sharing_info',
        'allfilelist',
        'file_list',
        'file_events',
        'check_site_token',
        'patches_info',
        'share_downloaded',
        'user_collaborations',
        'get_redis_safe',
        'redis_safe_done',
        'get_license_type',
        'get_remote_actions',
        'get_uploads',
        'get_node_status',
        'traffic_info',
        'close_room',
        'check_participant_auth',
    ];

    /**
     * Позволяет приходить запросам на этот скрипт и акшены перечисленные в массиве
     * с других доменов а не только с домена где стоит этот скрипт
     *
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1G');
        if (in_array($action->id, ['index'])) {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * Проверяет валидность массива $request полученного методом POST из JSON строки
     *
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
     *
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
                'errcode' => NodeApi::ERROR_INVALID_JSON,
                'info'    => "Bad request ({$this->error})"
            ];
        }

        // Проверка только если это Self-Hosted
        if (Yii::$app->params['self_hosted']) {
            /* если это сх, то нужно проверить в БД количество бизнес-админов если больше одного то выдать ошибку системы (это хак) */
            /* если это сх, то нужно проверить в БД наличие юзеров с лицензией отличной от бизнес-админ и бизнес-юзер, если есть - ошибка*/
            $test = ShApi::check_sh_system_integrity();
            if (!$test['status']) {
                Yii::$app->cache->delete('last_license_check');
                return [
                    'result'  => "error",
                    'errcode' => ShApi::ERROR_DATABASE_INTEGRITY,
                    'info'    => $test['info'],
                ];
            } else {
                Yii::$app->cache->set('sh_integrity_passed', true, ShApi::INTEGRITY_CHECK_TTL);
            }

            /*
             * если это сх, то нужно проверить наличие флага-ключа в мемкеше,
             * он хранится 36 часов (задано в frontend\models\ShApi.php)
             * если этого флага в кеше уже нет, то знчит нужно заблокировать
             * работу системы и показать заглушку
             */
            if (!Yii::$app->cache->get('last_license_check')) {
                Yii::$app->cache->delete('last_license_check');
                return [
                    'result'  => "error",
                    'errcode' => ShApi::ERROR_SELF_HOSTED_CLIENT_BLOCKED,
                    'info'    => $test['info'],
                ];
            }
        }

        // если все верхние проверки пройдены то выдаем ответ на конкретный акшен
        return $this->{$request['action']}($request['data']);
    }

    /**
     * Метод для обновления данных по ноде
     *
     * @param array $data
     * @return array
     */
    protected function nodeinfo($data)
    {
        $model = new NodeApi(['signal_passphrase', 'node_id', 'node_ip', 'node_status']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", $model->node_id . $model->node_ip . $model->node_status . Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        return $model->nodeinfo();
    }

    /**
     * Метод для обновления данных по ноде
     *
     * @param array $data
     * @return array
     */
    protected function nodelist($data)
    {
        $model = new NodeApi(['signal_passphrase', 'user_id']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", $model->user_id . Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        return $model->nodelist();
    }

    /**
     * Метод получения списка расшаренных файлов
     *
     * @param array $data
     * @return array
     */
    protected function sharing_list($data)
    {
        $model = new NodeApi(['signal_passphrase', 'user_id']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", $model->user_id . Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        return $model->sharing_list();
    }

    /**
     * Метод для получения инфы по конкретной шаре
     *
     * @param array $data
     * @return array
     */
    protected function sharing_info($data)
    {
        $model = new NodeApi(['signal_passphrase', 'share_hash', 'user_id']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", $model->user_id . $model->share_hash . Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        return $model->sharing_info();
    }

    /**
     * Метод для отметки того что скачивание было выполнено
     * (нужно для случаев IMMEDIATELY - отключения шары сразу после скачивания)
     *
     * @param array $data
     * @return array
     */
    protected function share_downloaded($data)
    {
        $model = new NodeApi(['signal_passphrase', 'share_hash', 'user_id']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", $model->user_id . $model->share_hash . Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        return $model->share_downloaded();
    }

    /**
     * Метод получения списка всех файлов пользователя
     *
     * @param array $data
     * @return array
     */
    protected function allfilelist($data)
    {
        $model = new NodeApi(['signal_passphrase', 'user_id']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        return $model->allfilelist();
    }

    /**
     * Метод получения списка файлов с определенного евента
     *
     * @param array $data
     * @return array
     */
    protected function file_list($data)
    {
        $model = new NodeApi(['signal_passphrase', 'node_id', 'last_event_id']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", $model->node_id . $model->last_event_id . Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        $UserNode = UserNode::findOne(['node_id' => $model->node_id]);
        if (!$UserNode) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_NODE_NOT_FOUND,
                'info'    => 'Node not found'
            ];
        }

        return $model->file_list($UserNode);
    }

    /**
     * Метод получения списка евентов
     *
     * @param array $data
     * @return array
     */
    protected function file_events($data)
    {
        $model = new NodeApi(['signal_passphrase', 'node_id', 'last_event_id', 'limit', 'offset', 'checked_event_id', 'events_count_check', 'node_without_backup']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", $model->node_id . $model->last_event_id . Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        $UserNode = UserNode::findOne(['node_id' => $model->node_id]);
        if (!$UserNode) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_NODE_NOT_FOUND,
                'info'    => 'Node not found'
            ];
        }

        return $model->file_events($UserNode);
    }

    /**
     * Метод получения информации о размерах/готовности патчей.
     *
     * @param array $data
     * @return array
     */
    protected function patches_info($data)
    {
        $model = new NodeApi(['signal_passphrase', 'node_id', 'direct_patch_event_id', 'reversed_patch_event_id', 'last_event_id']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", $model->node_id .
                                                         $model->direct_patch_event_id .
                                                         $model->reversed_patch_event_id .
                                                         $model->last_event_id .
                                                         Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        $UserNode = UserNode::findOne(['node_id' => $model->node_id]);
        if (!$UserNode) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_NODE_NOT_FOUND,
                'info'    => 'Node not found'
            ];
        }

        return $model->patches_info($UserNode);
    }

    /**
     * Метод проверки ключа сессии
     *
     * @param array $data
     * @return array
     */
    protected function check_site_token($data)
    {
        $model = new NodeApi(['signal_passphrase', 'site_token']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", $model->site_token . Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        return $model->check_site_token();
    }

    /**
     * Метод проверки авторизации ноды
     *
     * @param array $data
     * @return array
     */
    protected function checknodeauth($data)
    {
        $model = new NodeApi(['signal_passphrase', 'user_hash', 'node_hash']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", $model->user_hash . $model->node_hash . Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        return $model->checknodeauth();
    }

    /**
     * Метод проверки авторизации шары
     *
     * @param array $data
     * @return array
     */
    protected function checkbrowserauth($data)
    {
        $model = new NodeApi(['signal_passphrase', 'share_hash', 'remote_ip']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", $model->share_hash . Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        /* тут проверка что не было лока по ИП для метода */
        /* это будет метод, который проверяет нет ли лока для данного ИП */
        $bl_ret = BadLogins::checkIsIpLocked($model->remote_ip, BadLogins::TYPE_LOCK_SHARE);
        if ($bl_ret['status']) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_LOCKED_CAUSE_TOO_MANY_BAD_LOGIN,
                'info'    => $bl_ret['info'],
                'data'    => $bl_ret['data'],
            ];
        }

        return $model->checkbrowserauth();
    }

    /**
     * @param array $data
     * @return array
     */
    protected function user_collaborations($data)
    {
        $model = new NodeApi(['signal_passphrase', 'user_id']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", $model->user_id . Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        return $model->user_collaborations();
    }

    /**
     * @param array $data
     * @return array
     */
    protected function get_redis_safe($data)
    {
        $model = new NodeApi(['signal_passphrase']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", 'get_redis_safe' . Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        return $model->get_redis_safe();
    }

    /**
     * @param array $data
     * @return array
     */
    protected function redis_safe_done($data)
    {
        $model = new NodeApi(['signal_passphrase', 'rs_id', 'rs_type', 'user_id']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", $model->rs_id . Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        return $model->redis_safe_done();
    }

    /**
     * @param array $data
     * @return array
     */
    protected function get_license_type($data)
    {
        $model = new NodeApi(['signal_passphrase', 'user_id']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", $model->user_id . Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        return $model->get_license_type();
    }

    /**
     * @param array $data
     * @return array
     */
    protected function get_remote_actions($data)
    {
        $model = new NodeApi(['signal_passphrase', 'user_id', 'node_id']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", $model->user_id . $model->node_id . Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        return $model->get_remote_actions();
    }

    /**
     * @param array $data
     * @return array
     */
    protected function get_uploads($data)
    {
        $model = new NodeApi(['signal_passphrase', 'user_id']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", $model->user_id . Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        return $model->get_uploads();
    }

    /**
     * @param array $data
     * @return array
     */
    protected function get_node_status($data)
    {
        $model = new NodeApi(['signal_passphrase', 'user_id', 'node_id']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", $model->user_id . Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        return $model->get_node_status();
    }

    /**
     * @param array $data
     * @return array
     */
    protected function traffic_info($data)
    {
        $model = new NodeApi(['signal_passphrase', 'traffic_data', 'user_id', 'node_id']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", $model->user_id . $model->node_id . Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        $UserNode = UserNode::findOne(['node_id' => $model->node_id]);
        if (!$UserNode) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_NODE_NOT_FOUND,
                'info'    => "Node not found",
            ];
        }

        if ($UserNode->user_id != $model->user_id) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_USER_NODE_MISMATCH,
                'info'    => "user_node.user_id <> user_id",
            ];
        }

        return $model->traffic_info();
    }

    /**
     * @param array $data
     * @return array
     */
    protected function close_room($data)
    {
        $model = new ConferenceApi(['signal_passphrase', 'room_uuid']);
        if (!$model->load(['ConferenceApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", $model->room_uuid . Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        return $model->closeRoom();
    }

    /**
     * @param array $data
     * @return array
     */
    protected function check_participant_auth($data)
    {
        $model = new ConferenceApi(['signal_passphrase', 'room_uuid', 'user_hash']);
        if (!$model->load(['ConferenceApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->signal_passphrase !== hash("sha512", $model->room_uuid . $model->user_hash .  Preferences::getValueByKey('SignalAccessKey'))) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error."
            ];
        }

        return $model->checkParticipantAuth();
    }
}
