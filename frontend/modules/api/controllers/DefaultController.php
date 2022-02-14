<?php
namespace frontend\modules\api\controllers;

use Yii;
use common\models\Users;
use common\models\UserNode;
use frontend\models\NodeApi;
use common\models\BadLogins;

class DefaultController extends MainController
{
    /**
     * Метод для регистрации нового пользователя и его ноды в системе
     * Возвращает массив для ответа
     *
     * @param array $data - массив данных для регистрации, сформированный из полученного JSON
     * @return array
     */
    protected function signup($data)
    {
        /* для СХ запрещена регистрация через апи */
        if (Yii::$app->params['self_hosted']) {
            return [
                'result' => "error",
                'errcode' => NodeApi::ERROR_DENIED_FOR_SELF_HOSTED,
                'info' => "Denied for self-hosted version",
            ];
        }

        $model = new NodeApi([
            'user_email',
            'user_password',
            'node_hash',
            'node_name',
            'node_osname',
            'node_ostype',
            'node_devicetype',
        ]);

        /**/
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
            ];
        }

        /**/
        if (UserNode::findByHash($model->node_hash)) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_NODEHASH_EXIST,
                'info'    => "node_hash already exist.",
            ];
        }

        /**/
        if (Users::findByEmail($model->user_email)) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_EMAIL_EXIST,
                'info'    => "User with this E-Mail already registered.",
            ];
        }

        return $model->signup();
    }

    /**
     * Метод для добавления новой ноды к существующему пользователю
     * Возвращает массив для ответа
     *
     * @param array $data - массив данных для добавления ноды, сформированный из полученного JSON
     * @return array
     */
    protected function addNode($data)
    {
        $model = new NodeApi([
            'user_email',
            'user_password',
            'node_hash',
            'node_name',
            'node_osname',
            'node_ostype',
            'node_devicetype',
        ]);

        /**/
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
            ];
        }

        /**/
        $User = Users::findByEmail($model->user_email);
        if (!$User || !$User->validatePassword($model->user_password, false)) {
            $User = null;
            return [
                'result'         => "error",
                'errcode'        => NodeApi::ERROR_USER_NOT_FOUND,
                'info'           => "Email or password you have entered is incorrect",
                'debug'          => "User not found. (user_email NOT FOUND or validatePassword failed).",
            ];
        }

        /* проверка на self-hosted */
        $testSH = self::checkAllowForSelfHosted($User);
        if (isset($testSH['result'])) {
            return $testSH;
        }

        /**/
        if (UserNode::findByHash($model->node_hash)) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_NODE_EXIST,
                'info'    => "This node_hash already added.",
            ];
        }

        $addNode = $model->addNode($User);
        $UserNode = $addNode['UserNode'];
        if ($UserNode) {
            return [
                'result'    => "success",
                'user_hash' => $User->user_remote_hash,
                'info'      => "Added successfully.",
            ];
        } else {
            return [
                'result'  => "error",
                'errcode' => $addNode['errcode'],
                'info'    => $addNode['info'],
            ];
        }
    }

    /**
     * Метод для авторизации пользователя в системе
     * Возвращает массив для ответа
     *
     * @param array $data - массив данных для авторизации, сформированный из полученного JSON
     * @return array
     */
    protected function login($data)
    {
        if (isset($data['user_hash']) && ($data['user_hash'] !== null)) {
            unset($data['user_email'], $data['user_password']);
            $model = new NodeApi([
                'user_hash',
                'node_hash',
                'node_name',
                'node_osname',
                'node_ostype',
                'node_devicetype',
            ]);
        } else {
            unset($data['user_hash']);
            $model = new NodeApi([
                'node_hash',
                'user_email',
                'user_password',
                'node_name',
                'node_osname',
                'node_ostype',
                'node_devicetype',
            ]);
        }

        /**/
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
            ];
        }

        /**/
        $UserNode = UserNode::findByHash($model->node_hash);
        if ($UserNode && in_array($UserNode->node_status, [UserNode::STATUS_DELETED, UserNode::STATUS_WIPED])) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_BAD_NODE_STATUS,
                'info'    => "This node has node_status=" . UserNode::statusLabel($UserNode->node_status) . ". Login denied with this status. Register new node please.",
            ];
        }

        /**/
        $User = null;
        if ($model->user_hash) {
            $User = Users::findByUserRemoteHash($model->user_hash);
        } elseif ($model->user_email && $model->user_password) {

            /* IP */
            $ip = null;
            $ip = Yii::$app->request->getUserIP();
            if (!$ip) { $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null; }
            if (!$ip) { $ip = '127.0.0.1'; }

            /* тут проверка что не было лока по ИП для метода логина по емейл + пароль */
            /* это будет метод, который проверяет нет ли лока для данного ИП */
            $bl_ret = BadLogins::checkIsIpLocked($ip, BadLogins::TYPE_LOCK_LOGIN);
            if ($bl_ret['status']) {
                return [
                    'result'  => "error",
                    'errcode' => NodeApi::ERROR_LOCKED_CAUSE_TOO_MANY_BAD_LOGIN,
                    'info'    => $bl_ret['info'],
                    'data'    => $bl_ret['data'],
                ];
            }

            /* тут поиск юзера и валидация пароля */
            $User = Users::findByEmail($model->user_email);
            if (!$User || !$User->validatePassword($model->user_password, false)) {
                $User = null;

                /* тут bad_login_count_tries++ и bad_login_last_timestamp = time() для этого ИП */
                /* это будет метод устанавливающий данные для ип */
                /* (создавать или обновлять уже существующую запись для ИП в таблице) */
                BadLogins::setDataForIP($ip, BadLogins::TYPE_LOCK_LOGIN);
            } else {

                /* если успешно авторизованы, то удалить из списка */
                BadLogins::removeIpFromList($ip, BadLogins::TYPE_LOCK_LOGIN);
                BadLogins::removeIpFromList($ip, BadLogins::TYPE_LOCK_RESET);

            }
        }

        /**/
        if (!$User) {

            if ($UserNode) {
                $remote_actions = NodeApi::getRemoteActions($UserNode->node_id);
            } else {
                $remote_actions = [];
            }

            return [
                'result'         => "error",
                'remote_actions' => $remote_actions,
                'errcode'        => NodeApi::ERROR_USER_NOT_FOUND,
                'info'           => "Email or password you have entered is incorrect",
                'debug'          => "User not found. (user_hash NOT FOUND).",
            ];
        }

        /* проверка на self-hosted */
        $testSH = self::checkAllowForSelfHosted($User);
        if (isset($testSH['result'])) {
            return $testSH;
        }

        /**/
        if (!$UserNode) {
            if ($model->user_email && $model->user_password) {
                $addNode = $model->addNode($User);
                $UserNode = $addNode['UserNode'];
                if (!$UserNode) {
                    return [
                        'result'  => "error",
                        'errcode' => $addNode['errcode'],
                        'info'    => $addNode['info'],
                    ];
                }
            } else {
                return [
                    'result'  => "error",
                    'errcode' => NodeApi::ERROR_NODE_NOT_FOUND,
                    'info'    => "Email or password you have entered is incorrect",
                    'debug'   => "User not found. (node_hash NOT FOUND).",
                ];
            }
        }

        /**/
        if ($User->user_id != $UserNode->user_id) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_USER_NODE_MISMATCH,
                'info'    => "User_id mismatch for user_hash and node_hash.",
            ];
        }

        return $model->login($User, $UserNode);
    }

    /**
     * Метод для смены пароля
     *
     * @param array $data
     * @return array
     */
    protected function changepassword($data)
    {
        $model = new NodeApi(['node_hash', 'user_hash', 'old_password', 'new_password']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        if (!$res['User']->validatePassword($model->old_password, false)) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_OLDPASSWD,
                'info'    => "old_password is wrong.",
            ];
        }

        return $model->changepassword($res['User']);
    }

    /**
     * Метод для смены пароля
     *
     * @param array $data
     * @return array
     */
    protected function resetpassword($data)
    {
        $model = new NodeApi(['node_hash', 'user_email']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
            ];
        }

        /* IP */
        $ip = null;
        $ip = Yii::$app->request->getUserIP();
        if (!$ip) { $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null; }
        if (!$ip) { $ip = '127.0.0.1'; }

        /* тут проверка что не было лока по ИП для метода логина по емейл + пароль */
        /* это будет метод, который проверяет нет ли лока для данного ИП */
        $bl_ret = BadLogins::checkIsIpLocked($ip, BadLogins::TYPE_LOCK_RESET);
        if ($bl_ret['status']) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_LOCKED_CAUSE_TOO_MANY_BAD_LOGIN,
                'info'    => $bl_ret['info'],
                'data'    => $bl_ret['data'],
            ];
        }

        /* тут установка параметров блокировки ип */
        BadLogins::setDataForIP($ip, BadLogins::TYPE_LOCK_RESET);

        /* поиск юзера по емейлу */
        $User = Users::findByEmail($model->user_email);
        if (!$User) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_USER_NOT_FOUND,
                'info'    => "User not found."
            ];
        }

        /* проверка на self-hosted */
        $testSH = self::checkAllowForSelfHosted($User);
        if (isset($testSH['result'])) {
            return $testSH;
        }

//        /* поиск ноды по ее хешу */
//        $UserNode = UserNode::findByHash($model->node_hash);
//        if (!$UserNode) {
//            return [
//                'result'  => "error",
//                'errcode' => NodeApi::ERROR_NODE_NOT_FOUND,
//                'info'    => "User not found. (node_hash NOT FOUND)."
//            ];
//        }
//
//        /* проверка что нода не удалена */
//        if (in_array($UserNode->node_status, [UserNode::STATUS_DELETED, UserNode::STATUS_WIPED])) {
//            return [
//                'result'  => "error",
//                'errcode' => NodeApi::ERROR_BAD_NODE_STATUS,
//                'info'    => "This node has node_status=" . UserNode::statusLabel($UserNode->node_status) . ". Any action denied with this status. Register new node please."
//            ];
//        }
//
//        /* проверка что нода принадлежит этому юзеру */
//        if ($User->user_id != $UserNode->user_id) {
//            return [
//                'result'  => "error",
//                'errcode' => NodeApi::ERROR_USER_NODE_MISMATCH,
//                'info'    => "User_id mismatch for user_hash and node_hash."
//            ];
//        }

        return $model->resetpassword();
    }

    /**
     * Метод для разлогинивания ноды

     * @param array $data - массив данных для авторизации, сформированный из полученного JSON
     * @return array
     */
    protected function logout($data)
    {
        $model = new NodeApi(['user_hash', 'node_hash']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        return $model->logout($res['UserNode']);
    }

    /**
     * Метод для удаления ноды у существующего пользователя
     * Возвращает массив для ответа
     *
     * @param array $data - массив данных для добавления ноды, сформированный из полученного JSON
     * @return array
     */
    protected function delNode($data)
    {
        $model = new NodeApi(['user_hash', 'node_hash', 'node_id']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        if ($res['UserNode']->node_id == $model->node_id) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_CANT_SELF_DELETE,
                'info'    => "Node can't delete itself",
            ];
        }

        return $model->delNode($res['User']);
    }

    /**
     * Метод для скрытия ноды у существующего пользователя
     * Возвращает массив для ответа
     *
     * @param array $data - массив данных ноды, сформированный из полученного JSON
     * @return array
     */
    protected function hideNode($data)
    {
        $model = new NodeApi(['user_hash', 'node_hash', 'node_id']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        if ($res['UserNode']->node_id == $model->node_id) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_CANT_SELF_HIDE,
                'info'    => "Node can't hide itself",
            ];
        }

        return $model->hideNode($res['User']);
    }

    /**
     * Метод для обращения в саппорт
     * Возвращает массив для ответа
     *
     * @param array $data - массив данных для добавления ноды, сформированный из полученного JSON
     * @return array
     */
    protected function support($data)
    {
        $model = new NodeApi(['user_hash', 'node_hash', 'subject', 'body']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        return $model->support($res['User']);
    }

    /**
     * Метод для получения информации о лицензии
     *
     * @param array $data - массив данных для получения инфы по лицензии, сформированный из полученного JSON
     * @return array
     */
    protected function license($data)
    {
        $model = new NodeApi(['user_hash', 'node_hash']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        return $model->license($res['User']);
    }

    /**
     * Метод для синхронизации времени ноды
     *
     * @param array $data
     * @return array
     */
    protected function gettime($data)
    {
        $model = new NodeApi(['node_hash']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
            ];
        }

        if (!UserNode::findByHash($model->node_hash)) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_NODE_NOT_FOUND,
                'info'    => "User not found. (node_hash NOT FOUND).",
            ];
        }

        return $model->gettime();
    }

    /**
     * Метод для получения количества оставшихся байт по беспл. лицензии
     *
     * @param array $data
     * @return array
     */
    protected function turn_get_bytes($data)
    {
        $model = new NodeApi(['node_hash', 'user_hash', 'bytes']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        return $model->turn_get_bytes($res['User']);
    }

    /**
     * Метод для возврата незадействованного количества байт в случае ошибки при скачивании
     *
     * @param array $data
     * @return array
     */
    protected function turn_set_bytes($data)
    {
        $model = new NodeApi(['node_hash', 'user_hash', 'bytes']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        return $model->turn_set_bytes($res['User']);
    }

    /**
     * Метод для выполнения logout || wipe
     *
     * @param array $data
     * @return array
     */
    protected function execute_remote_action($data)
    {
        $model = new NodeApi(['node_hash', 'user_hash', 'target_node_id', 'action_type']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        return $model->execute_remote_action($res['UserNode'], $res['User']);
    }

    /**
     * Метод для фиксации успеха logout || wipe
     *
     * @param array $data
     * @return array
     */
    protected function remote_action_done($data)
    {
        $model = new NodeApi(['node_hash', 'user_hash', 'action_uuid']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        return $model->remote_action_done($res['UserNode']);
    }

    /**
     * Метод для получения ссылки для логина через токен
     *
     * @param array $data
     * @return array
     */
    protected function get_token_login_link($data)
    {
        $model = new NodeApi(['node_hash', 'user_hash']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        return $model->get_token_login_link($res['User']);
    }

    protected function getNotifications($data)
    {
        $model = new NodeApi(['node_hash', 'user_hash', 'limit']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }

        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        return $model->getNotifications($res['User']);
    }
}
