<?php
namespace frontend\modules\api\controllers;

use Yii;
use frontend\models\NodeApi;
use frontend\models\CollaborationApi;
use common\models\UserColleagues;

class SharingController extends MainController
{

    /**
     * Метод для регистрации события share file
     * @param array $data
     * @return array
     */
    protected function sharing_enable($data)
    {
        if (!array_key_exists('share_ttl', $data)) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => "share_ttl is required. Set null, if you want unlimited date."
            ];
        }

        if (!array_key_exists('share_password', $data)) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => "share_password is required. Set null, if you don't want use password."
            ];
        }


        $model = new NodeApi(['node_hash', 'user_hash', 'uuid']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        return $model->sharing_enable($res['UserNode']);
    }

    /**
     * Метод для регистрации события unshare file
     * @param array $data
     * @return array
     */
    protected function sharing_disable($data)
    {
        $model = new NodeApi(['node_hash', 'user_hash', 'uuid']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        return $model->sharing_disable($res['UserNode']);
    }



    /**
     * Получение всех данных коллаборации по папке
     * @param array $data
     * @return array
     */
    protected function collaboration_info($data)
    {
        $model = new CollaborationApi(['node_hash', 'user_hash', 'uuid']);
        if (!$model->load(['CollaborationApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        $model->initOwner($res['UserNode']->user_id);

        $ret = $model->collaborationInfo();
        if ($ret['status']) {
            return [
                'result' => "success",
                'data'   => $ret['data'],
            ];
        } else {
            return [
                'result' => "error",
                'info'   => strip_tags($ret['info']),
            ];
        }
    }

    /**
     * Добавление коллеги в коллаборацию
     * @param array $data
     * @return array
     */
    protected function colleague_add($data)
    {
        $data['action'] = CollaborationApi::ACTION_ADD;
        $model = new CollaborationApi(['node_hash', 'user_hash', 'uuid', 'access_type', 'colleague_email', 'action']);
        if (!$model->load(['CollaborationApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        $model->initOwner($res['UserNode']->user_id);

        $test = CollaborationApi::check_is_colleague_joined_before($model->colleague_email, $res['UserNode']->user_id);
        if ($test) {
            $ret = $model->colleagueAdd();
        } else {
            $ret = $model->colleagueInvite();
        }

        //$ret = $model->colleagueAdd();
        if ($ret['status']) {
            return [
                'result' => "success",
                'data'   => $ret['data'],
            ];
        } else {
            return [
                'result' => "error",
                'info'   => strip_tags($ret['info']),
            ];
        }
    }

    /**
     * Удаление коллеги из коллаборацию
     * @param array $data
     * @return array
     */
    protected function colleague_delete($data)
    {
        $data['is_from_recursion'] = true;
        $data['action']            = CollaborationApi::ACTION_DELETE;
        $data['access_type']       = UserColleagues::PERMISSION_DELETE;
        $model = new CollaborationApi(['node_hash', 'user_hash', 'uuid', 'access_type', 'colleague_id', 'action']);
        if (!$model->load(['CollaborationApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        $model->initOwner($res['UserNode']->user_id);

        $ret = $model->colleagueDelete();
        if ($ret['status']) {
            return [
                'result' => "success",
                'data'   => $ret['data'],
            ];
        } else {
            return [
                'result' => "error",
                'info'   => strip_tags($ret['info']),
            ];
        }
    }

    /**
     * Изменение прав коллеги в коллаборацию
     * @param array $data
     * @return array
     */
    protected function colleague_edit($data)
    {
        $data['action'] = CollaborationApi::ACTION_EDIT;
        $model = new CollaborationApi(['node_hash', 'user_hash', 'uuid', 'access_type', 'colleague_id', 'action']);
        if (!$model->load(['CollaborationApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        $model->initOwner($res['UserNode']->user_id);

        $ret = $model->colleagueEdit();
        if ($ret['status']) {
            return [
                'result' => "success",
                'data'   => $ret['data'],
            ];
        } else {
            return [
                'result' => "error",
                'info'   => strip_tags($ret['info']),
            ];
        }
    }

    /**
     * Полная отмена коллаборации для папки (по сути удаление всех коллег)
     * @param array $data
     * @return array
     */
    protected function collaboration_cancel($data)
    {
        $model = new CollaborationApi(['node_hash', 'user_hash', 'uuid']);
        if (!$model->load(['CollaborationApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        $model->initOwner($res['UserNode']->user_id);

        $ret = $model->collaborationDelete();
        if ($ret['status']) {
            return [
                'result' => "success",
            ];
        } else {
            return [
                'result' => "error",
                'info'   => strip_tags($ret['info']),
            ];
        }
    }

    /**
     * Позволяет пользователю покинуть коллаборацию
     * @param array $data
     * @return array
     */
    protected function collaboration_leave($data)
    {
        $data['is_from_recursion'] = true;
        $data['action']            = CollaborationApi::ACTION_DELETE;
        $data['access_type']       = UserColleagues::PERMISSION_DELETE;
        $model = new CollaborationApi(['node_hash', 'user_hash', 'uuid', 'access_type', 'action']);
        if (!$model->load(['CollaborationApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        $query = "SELECT
                    t1.user_id, t1.collaboration_id, t2.colleague_id
                  FROM {{%user_collaborations}} as t1
                  INNER JOIN {{%user_colleagues}} as t2 ON t1.collaboration_id = t2.collaboration_id
                  WHERE (t1.file_uuid = :file_uuid)
                  AND (t2.user_id = :user_id)";
        $res2 = Yii::$app->db->createCommand($query, [
            'file_uuid' => $model->uuid,
            'user_id'   => $res['UserNode']->user_id,
        ])->queryOne();

        if (!sizeof($res2)) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_COLLABORATION_ACCESS,
                'info'    => "Access error. You are not in this collaboration",
            ];
        }

        $model->colleague_id = $res2['colleague_id'];
        $model->initOwner($res2['user_id']);

        $ret = $model->colleagueDelete();
        if ($ret['status']) {
            return [
                'result' => "success",
                'data'   => $ret['data'],
            ];
        } else {
            return [
                'result' => "error",
                'info'   => strip_tags($ret['info']),
            ];
        }
    }

    /**
     * Позволяет пользователю вступить в коллаборацию по приглашению
     * @param array $data
     * @return array
     */
    public function collaboration_join($data)
    {
        $model = new NodeApi(['node_hash', 'user_hash', 'colleague_id']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        $ret = $model->collaboration_join($res['User']);
        $ret['result'] = $ret['status'] ? 'success' : 'error';
        if (!$ret['status'] && !isset($ret['errcode'])) {
            $ret['errcode'] = NodeApi::ERROR_COLLABORATION_DATA;
        }
        if (isset($ret['info'])) {
            $ret['info'] = strip_tags($ret['info']);
        }

        return $ret;
    }
}
