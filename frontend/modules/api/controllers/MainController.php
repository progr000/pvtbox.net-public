<?php
/*
 * при добавлении нового метода (method) типа signup, login, addNode,
 * добавьте в бд соответствующие настройки для
 * ограничения доступа COUNT_ALLOWED_method и PERIOD_ALLOWED_method
 * добавить эти параметры можно на странице БЕКЕНДА (админки) preferences/index
 * */
namespace frontend\modules\api\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\web\UploadedFile;
use common\helpers\Functions;
use common\models\Maintenance;
use common\models\Users;
use common\models\UserNode;
use common\models\Preferences;
use common\models\Licenses;
use frontend\models\NodeApi;
use frontend\models\ShApi;
use frontend\models\forms\UploadLogsForm;

class MainController extends Controller
{
    const STUN_TTL = 3600; //600

    private $error = "";
    private static $STUN_CACHE_KEY;
    private static $ALLOWED_METHODS = [
        'stun',

        'signup',
        'changepassword',
        'resetpassword',
        'logout',
        'login',
        'support',
        'addNode',
        'delNode',
        'hideNode',
        'license',
        'gettime',
        'turn_get_bytes',
        'turn_set_bytes',
        'execute_remote_action',
        'remote_action_done',
        'get_token_login_link',
        'getNotifications',

        'patch_ready',
        'file_event_create',
        'file_event_update',
        'file_event_delete',
        'file_event_move',
        'folder_event_copy',
        'folder_event_create',
        'folder_event_delete',
        'folder_event_move',
        'file_list',
        'file_events',
        'download',

        'sharing_enable',
        'sharing_disable',
        'colleague_add',
        'colleague_delete',
        'colleague_edit',
        'collaboration_cancel',
        'collaboration_leave',
        'collaboration_info',
        'collaboration_join',

        'get_list_conferences',
        'get_list_available_participants',
        'get_list_participants',
        'set_list_participants',
        'accept_invitation',
        'cancel_conference',
        'open_conference',
        'generate_new_guest_link',
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
        self::$STUN_CACHE_KEY = md5('stun' . $_SERVER['REMOTE_ADDR'] . 'stun');
        if (in_array($action->id, ['index', 'upload'])) {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * Проверяет возможность доступа к действию заданному строкой $action
     *
     * @param string $action
     * @return bool
     */
    private function checkAccess($action)
    {
        $COUNT_ALLOWED = Preferences::getValueByKey('COUNT_ALLOWED_' . $action, null, 'int');
        $PERIOD_ALLOWED = Preferences::getValueByKey('PERIOD_ALLOWED_' . $action, null, 'int');
        if ($COUNT_ALLOWED && $PERIOD_ALLOWED) {
            // если нет кеша - то выполняем метод (set $cnt=1)
            // если количество в кеше ($cnt) меньше чем $COUNT_ALLOWED выполняем метод ($cnt++)
            // если количество в кеше ($cnt) больше чем $COUNT_ALLOWED и (текущее время - время в кеше) больше чем $PERIOD_ALLOWED то Выполняем метод и сбрасываем данные кеша и ставим новый кеш
            // если количество в кеше ($cnt) больше чем $COUNT_ALLOWED и (текущее время - время в кеше) меньше чем $PERIOD_ALLOWED то !!НЕ выполняем метод
            $CACHE_KEY = md5($_SERVER['REMOTE_ADDR'] . $action);
            $rescache = Yii::$app->cache->get($CACHE_KEY);
            if ($rescache !== false) {
                $rescache = @unserialize($rescache);
                if (isset($rescache['cnt'], $rescache['time'])) {
                    //var_dump(time()-$rescache['time']);
                    if ($rescache['cnt'] < $COUNT_ALLOWED) {

                        $rescache['cnt']++;
                        Yii::$app->cache->set($CACHE_KEY, serialize(['cnt' => $rescache['cnt'], 'time' => $rescache['time']]), $PERIOD_ALLOWED);
                        return true;

                    } elseif (time() - $rescache['time'] > $PERIOD_ALLOWED) {

                        Yii::$app->cache->set($CACHE_KEY, serialize(['cnt' => 1, 'time' => time()]), $PERIOD_ALLOWED);
                        return true;

                    } else {

                        return false;

                    }
                } else {
                    Yii::$app->cache->set($CACHE_KEY, serialize(['cnt' => 1, 'time' => time()]), $PERIOD_ALLOWED);
                    return true;
                }
            } else {
                Yii::$app->cache->set($CACHE_KEY, serialize(['cnt' => 1, 'time' => time()]), $PERIOD_ALLOWED);
                return true;
            }
        } else {
            return true;
        }
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

        if (!method_exists($this, $request['action'])) {
            $this->error = "Method not allowed for this api url";
            return false;
        }

        return true;
    }

    /**
     * Специальный метод-заглушка, через который выполняются все тесты для АПИ
     * @param string $action
     * @param array $data
     * @return array
     */
    public function actionTests($action, $data)
    {
        if (!in_array($action, self::$ALLOWED_METHODS) && !in_array($action, ['getUserAndUserNode'])) {
            return [
                'result'  => "error",
                'errcode' => 'INVALID_METHOD',
                'info'    => "Invalid method.",
            ];
        }

        if (!method_exists($this, $action)) {
            return [
                'result'  => "error",
                'errcode' => 'NOT_EXISTED_METHOD',
                'info'    => "Method not existed.",
            ];
        }
        return $this->{$action}($data);
    }

    /**
     * Метод для аплоада логов от нод
     * @return array
     */
    public function actionUpload()
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

        // Проверка конфигурации
        if (!isset(Yii::$app->params['logUploadsDir'])) {
            Yii::error("В конфиге системы не задан параметр 'logUploadsDir'. Работа без этого параметра невозможна.");
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_YII_CONF,
                'info'    => "Yii configure error. You need set param 'logUploadsDir'."
            ];
        }

        // Проверка что есть аплоад файлов
        if (!isset($_FILES)) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_UPLOAD_NO_FILES_GIVEN,
                'info'    => "No files given for upload."
            ];
        }

        // разрешен ли такой запрос
        if (!$this->checkAccess('upload')) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_TOO_MANY_TRIES,
                'info'    => "Too many tries."
            ];
        }

        // есть ли в кеше stun. Если нет в кеше в ответе info устанавливаем в flst обязательно
        // что бы ноды (девайсы) знали что нужно запрашивать новый стун
        if (!Yii::$app->cache->get(self::$STUN_CACHE_KEY)) {
            return [
                'result'  => "error",
                'errcode' => "FLST",
                'info'    => "flst"
            ];
        }

        // проверка что это _POST
        if (!Yii::$app->request->isPost) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => '_POST required',
            ];
        }

        // Проверка валидности данных user_hash, node_hash, node_sign в запросе
        $model = new NodeApi(['user_hash', 'node_hash', 'node_sign']);
        if (!$model->load(['NodeApi' => Yii::$app->request->post()]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_NODE_SIGN_NOT_FOUND,
                'info'    => $model->getErrors(),
            ];
        }

        // Валидация сигнатуры node_sign
        if (!$model->validateSignature()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                'info'    => "Signature validate error.",
                'debug'   => [
                    'node_sign' => $model->node_sign,
                    //'node_sign_WAIT' => hash("sha512", $model->node_hash . ip2long($_SERVER['REMOTE_ADDR'])),
                    'node_hash' => $model->node_hash,
                    'ip'        => $_SERVER['REMOTE_ADDR'],
                    'ip2long'   => ip2long($_SERVER['REMOTE_ADDR']),
                ],
            ];
        }

        // Обработка аплоада
        $modelUpl = new UploadLogsForm();
        $modelUpl->uploadedFile = UploadedFile::getInstance($modelUpl, 'uploadedFile');
        if ($modelUpl->upload()) {
            // file is uploaded successfully
            return [
                'result' => "success",
                'file_name' => $modelUpl->getResFileName(),
            ];
        }

        // если ошибка
        return [
            'result'  => "error",
            'errcode' => NodeApi::ERROR_FS_TRY_LATER,
            'info'    => $modelUpl->getErrors()
        ];
    }

    /**
     * Основная ф-ия обработки запросов от питонщиков (роутер методов signup, login, addNode и т.д.)
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

        // получаем боди запроса
        $request = json_decode(Yii::$app->request->getRawBody(), true);

        // Проверка конфигурации
        if (!isset(Yii::$app->params['nodeVirtualFS'])) {
            Yii::error("В конфиге системы не задан параметр 'nodeVirtualFS'. Работа без этого параметра невозможна.");
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_YII_CONF,
                'info'    => "Yii configure error. You need set param 'nodeVirtualFS'."
            ];
        }

        // Валидный ли джсон
        if (!$this->validate($request)) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_INVALID_JSON,
                'info'    => "Bad request ({$this->error})."
            ];
        }

        // разрешен ли такой запрос
        if (!$this->checkAccess($request['action'])) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_TOO_MANY_TRIES,
                'info'    => "Too many tries."
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

        if (!in_array($request['action'], ['stun'])) {
            // есть ли в кеше stun. Если нет в кеше в ответе info устанавливаем в flst обязательно
            // что бы ноды (девайсы) знали что нужно запрашивать новый стун
            if (!Yii::$app->cache->get(self::$STUN_CACHE_KEY)) {
                return [
                    'result'  => "error",
                    'errcode' => "FLST",
                    'info'    => "flst"
                ];
            }

            // Проверка наличия node_hash и node_sign в запросе
            $model = new NodeApi(['node_hash', 'node_sign']);
            //var_dump($request['data']);exit;

            /* ++ Дурацкая приблуда - чисто для питонщиков - что бы на этом этапе не проверяло другие параметры кроме сигнатуры */
            $sign = $request['data'];
            foreach ($sign as $k=>$v)
                if (!in_array($k, ['node_hash', 'node_sign']))
                    unset($sign[$k]);
            /* -- Дурацкая приблуда - чисто для питонщиков - что бы на этом этапе не проверяло другие параметры кроме сигнатуры */

            //if (!$model->load(['NodeApi' => $request['data']]) || !$model->validate()) {
            if (!$model->load(['NodeApi' => $sign]) || !$model->validate()) {
                return [
                    'result'  => "error",
                    'errcode' => NodeApi::ERROR_NODE_SIGN_NOT_FOUND,
                    'info'    => $model->getErrors()
                ];
            }

            // Валидация сигнатуры node_sign
            if (!$model->validateSignature()) {
                return [
                    'result'  => "error",
                    'errcode' => NodeApi::ERROR_SIGNATURE_INVALID,
                    'info'    => "Signature validate error.",
                    'debug'   => [
                        'node_sign' => $model->node_sign,
                        //'node_sign_WAIT' => hash("sha512", $model->node_hash . ip2long($_SERVER['REMOTE_ADDR'])),
                        'node_hash' => $model->node_hash,
                        'ip'        => $_SERVER['REMOTE_ADDR'],
                        'ip2long'   => ip2long($_SERVER['REMOTE_ADDR']),
                    ],
                ];
            }

            $data = ['node_id' => md5(time())];
            Yii::$app->cache->set(md5($model->node_hash), serialize($data), Yii::$app->session->getTimeout());
        }

        return $this->{$request['action']}($request['data']);
    }

    /**
     * @param \common\models\Users $User
     * @return array|bool
     */
    protected function checkAllowForSelfHosted($User)
    {
        if (Yii::$app->params['self_hosted']) {
            if (!in_array($User->license_type, [Licenses::TYPE_PAYED_BUSINESS_ADMIN, Licenses::TYPE_PAYED_BUSINESS_USER])) {
                return [
                    'result' => "error",
                    'errcode' => NodeApi::ERROR_DENIED_FOR_THIS_LICENSE_ON_SELF_HOSTED,
                    'info' => "Denied for this license on self-hosted version",
                ];
            }
        }
        return true;
    }

    /**
     * @param \frontend\models\NodeApi|\frontend\models\CollaborationApi|\frontend\models\ConferenceApi $model
     * @return array
     */
    protected function getUserAndUserNode($model)
    {
        /* проверка наличия нужных хешей в запросе джсон */
        if (!$model->user_hash || !$model->node_hash) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_USERHASH_NODEHASH,
                'info'    => "Wrong model data."
            ];
        }

        /* поиск юзера по его хешу */
        $User = Users::findByUserRemoteHash($model->user_hash);
        if (!$User) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_USER_NOT_FOUND,
                'info'    => "User not found. (user_hash NOT FOUND)."
            ];
        }

        /* проверка на self-hosted */
        $testSH = self::checkAllowForSelfHosted($User);
        if (isset($testSH['result'])) {
            return $testSH;
        }

        /* поиск ноды по ее хешу */
        $UserNode = UserNode::findByHash($model->node_hash);
        if (!$UserNode) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_NODE_NOT_FOUND,
                'info'    => "User not found. (node_hash NOT FOUND)."
            ];
        }

        /* проверка что нода не удалена */
        if (in_array($UserNode->node_status, [UserNode::STATUS_DELETED, UserNode::STATUS_WIPED])) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_BAD_NODE_STATUS,
                'info'    => "This node has node_status=" . UserNode::statusLabel($UserNode->node_status) . ". Any action denied with this status. Register new node please."
            ];
        }

        /* проверка что нода принадлежит этому юзеру */
        if ($User->user_id != $UserNode->user_id) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_USER_NODE_MISMATCH,
                'info'    => "User_id mismatch for user_hash and node_hash."
            ];
        }

        /* устанавливаем юзеру тот ип с которого он пришел */
        $User->user_last_ip  = Yii::$app->request->getUserIP();

        /* сохраняем параметры юзера */
        $User->save();

        /* возврат в случае успеха (все проверки пройдены) */
        return [
            'result'   => "success",
            'User'     => $User,
            'UserNode' => $UserNode
        ];
    }

    /**
     * Метод для получения stun
     *
     * @param array $data - массив данных для получения stun
     * @return array
     */
    protected function stun($data)
    {
        $model = new NodeApi(['get']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        //Yii::$app->cache->set(self::$STUN_CACHE_KEY, $_SERVER['REMOTE_ADDR'], self::STUN_TTL);
        Yii::$app->cache->set(self::$STUN_CACHE_KEY, $_SERVER['REMOTE_ADDR']);

        return [
            'result' => "success",
            'info'   => ip2long($_SERVER['REMOTE_ADDR'])
        ];
    }
}
