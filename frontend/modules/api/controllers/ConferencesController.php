<?php
namespace frontend\modules\api\controllers;

use Yii;
use common\models\Licenses;
use frontend\models\NodeApi;
use frontend\models\ConferenceApi;

class ConferencesController extends MainController
{

    /**
     * Метод для получения списка конференций юзера
     * @param array $data
     * @return array
     */
    protected function get_list_conferences($data)
    {
        $model = new ConferenceApi(['node_hash', 'user_hash']);
        if (!$model->load(['ConferenceApi' => $data]) || !$model->validate()) {
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

        /* @var \common\models\Users $res['User'] */
        /* @var \common\models\UserNode $res['UserNode'] */

        if ($res['User']->license_type == Licenses::TYPE_FREE_DEFAULT) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_LICENSE_ACCESS,
            ];
        }

        $ret = $model->getListConferences($res['User']);

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
     * Метод для получения списка доступных участников для добавления в конференцию
     * @param array $data
     * @return array
     */
    protected function get_list_available_participants($data)
    {
        $model = new ConferenceApi(['node_hash', 'user_hash', 'conference_id']);
        if (!$model->load(['ConferenceApi' => $data]) || !$model->validate()) {
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

        /* @var \common\models\Users $res['User'] */
        /* @var \common\models\UserNode $res['UserNode'] */

        if ($res['User']->license_type == Licenses::TYPE_FREE_DEFAULT) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_LICENSE_ACCESS,
            ];
        }

        $ret = $model->getListAvailableParticipants($res['User']);

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
     * Метод для получения списка участников конференции
     * @param array $data
     * @return array
     */
    protected function get_list_participants($data)
    {
        $model = new ConferenceApi(['node_hash', 'user_hash', 'conference_id']);
        if (!$model->load(['ConferenceApi' => $data]) || !$model->validate()) {
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

        /* @var \common\models\Users $res['User'] */
        /* @var \common\models\UserNode $res['UserNode'] */

        if ($res['User']->license_type == Licenses::TYPE_FREE_DEFAULT) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_LICENSE_ACCESS,
            ];
        }

        $ret = $model->getListParticipants($res['User']);

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
     * Метод для установки списка участников конференции
     * @param array $data
     * @return array
     */
    protected function set_list_participants($data)
    {
        $model = new ConferenceApi(['node_hash', 'user_hash', 'conference_id', 'conference_name', /*'participants'*/]);
        if (!$model->load(['ConferenceApi' => $data]) || !$model->validate()) {
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

        /* @var \common\models\Users $res['User'] */
        /* @var \common\models\UserNode $res['UserNode'] */

        if ($res['User']->license_type == Licenses::TYPE_FREE_DEFAULT) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_LICENSE_ACCESS,
            ];
        }

        $ret = $model->setListParticipants($res['User']);

        if ($ret['status']) {
            return [
                'result' => "success",
                'data'   => $ret['data'],
            ];
        } else {
            return [
                'result' => "error",
                'info'   => strip_tags($ret['info']),
                'debug'  => (isset($ret['debug']) ? $ret['debug'] : '')
            ];
        }
    }

    /**
     * Метод для принятия инвайта в конфу партиципантом
     * @param array $data
     * @return array
     */
    protected function accept_invitation($data)
    {
        $model = new ConferenceApi(['node_hash', 'user_hash', 'participant_id']);
        if (!$model->load(['ConferenceApi' => $data]) || !$model->validate()) {
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

        /* @var \common\models\Users $res['User'] */
        /* @var \common\models\UserNode $res['UserNode'] */

        if ($res['User']->license_type == Licenses::TYPE_FREE_DEFAULT) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_LICENSE_ACCESS,
            ];
        }

        $ret = $model->acceptInvitation($res['User']);

        if ($ret['status']) {
            return [
                'result' => "success",
                'data'   => isset($ret['data']) ? $ret['data'] : [],
            ];
        } else {
            return [
                'result' => "error",
                'info'   => strip_tags($ret['info']),
                'debug'  => (isset($ret['debug']) ? $ret['debug'] : '')
            ];
        }
    }

    /**
     * Метод для выхода из крнференции или для ее удаления
     * @param array $data
     * @return array
     */
    protected function cancel_conference($data)
    {
        $model = new ConferenceApi(['node_hash', 'user_hash', 'conference_id']);
        if (!$model->load(['ConferenceApi' => $data]) || !$model->validate()) {
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

        /* @var \common\models\Users $res['User'] */
        /* @var \common\models\UserNode $res['UserNode'] */

        if ($res['User']->license_type == Licenses::TYPE_FREE_DEFAULT) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_LICENSE_ACCESS,
            ];
        }

        $ret = $model->cancelConference($res['User']);

        if ($ret['status']) {
            return [
                'result' => "success",
            ];
        } else {
            return [
                'result' => "error",
                'info'   => strip_tags($ret['info']),
                'debug'  => (isset($ret['debug']) ? $ret['debug'] : '')
            ];
        }
    }

    /**
     * Метод для входа в крнференцию (опен, джойн)
     * @param array $data
     * @return array
     */
    protected function open_conference($data)
    {
        $model = new ConferenceApi(['node_hash', 'user_hash', 'conference_id']);
        if (!$model->load(['ConferenceApi' => $data]) || !$model->validate()) {
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

        /* @var \common\models\Users $res['User'] */
        /* @var \common\models\UserNode $res['UserNode'] */

        if ($res['User']->license_type == Licenses::TYPE_FREE_DEFAULT) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_LICENSE_ACCESS,
            ];
        }

        $ret = $model->openConference($res['User']);

        if ($ret['status']) {
            return [
                'result' => "success",
                'data'   => $ret['data'],
            ];
        } else {
            return [
                'result' => "error",
                'info'   => strip_tags($ret['info']),
                'debug'  => (isset($ret['debug']) ? $ret['debug'] : '')
            ];
        }
    }

    /**
     * Метод для генерации новой гостевой ссылки конференции
     * @param array $data
     * @return array
     */
    protected function generate_new_guest_link($data)
    {
        $model = new ConferenceApi(['node_hash', 'user_hash', 'conference_id']);
        if (!$model->load(['ConferenceApi' => $data]) || !$model->validate()) {
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

        /* @var \common\models\Users $res['User'] */
        /* @var \common\models\UserNode $res['UserNode'] */

        if ($res['User']->license_type == Licenses::TYPE_FREE_DEFAULT) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_LICENSE_ACCESS,
            ];
        }

        $ret = $model->generateGuestLink($res['User']);

        if ($ret['status']) {
            return [
                'result' => "success",
                'data'   => $ret['data'],
            ];
        } else {
            return [
                'result' => "error",
                'info'   => strip_tags($ret['info']),
                'debug'  => (isset($ret['debug']) ? $ret['debug'] : '')
            ];
        }
    }
}
