<?php
namespace frontend\modules\api\controllers;

use Yii;
use frontend\models\NodeApi;

class FileEventsController extends MainController
{
    /**
     * Метод для регистрации события create
     * @param array $data
     * @return array
     */
    protected function file_event_create($data)
    {
        if (!array_key_exists('folder_uuid', $data)) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => "folder_uuid is required. Set null or empty string, if you need root."
            ];
        }
        $model = new NodeApi(['node_hash', 'user_hash', 'file_name', 'file_size', 'diff_file_size', 'hash']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
                'error_data' => $model->error_data,
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        return $model->file_event_create($res['UserNode'], false, false, true, true);
    }

    /**
     * Метод для регистрации события update
     * @param array $data
     * @return array
     */
    protected function file_event_update($data)
    {
        $model = new NodeApi(['node_hash', 'user_hash', 'file_uuid', 'file_size', 'last_event_id', 'diff_file_size', 'rev_diff_file_size', 'hash']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
                'error_data' => $model->error_data,
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        return $model->file_event_update($res['UserNode'], false, false, true, true);
    }

    /**
     * Метод для регистрации события delete
     * @param array $data
     * @return array
     */
    protected function file_event_delete($data)
    {
        $model = new NodeApi(['node_hash', 'user_hash', 'file_uuid', 'last_event_id']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
                'error_data' => $model->error_data,
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        return $model->file_event_delete($res['UserNode'], false, false, true, true);
    }

    /**
     * Метод для регистрации события move
     * @param array $data
     * @return array
     */
    protected function file_event_move($data)
    {
        if (!array_key_exists('new_folder_uuid', $data)) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => "new_folder_uuid is required. Set null or empty string, if you need root."
            ];
        }
        $model = new NodeApi(['node_hash', 'user_hash', 'file_uuid', 'last_event_id', 'new_file_name']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
                'error_data' => $model->error_data,
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        return $model->file_event_move($res['UserNode'], false, false, true, true);
    }

    /**
     * Метод для регистрации события folder copy
     * @param array $data
     * @return array
     */
    protected function folder_event_copy($data)
    {
        if (!array_key_exists('target_parent_folder_uuid', $data)) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => "target_parent_folder_uuid is required. Set null or empty string, if you need root."
            ];
        }
        $model = new NodeApi(['node_hash', 'user_hash', 'last_event_id', 'source_folder_uuid', 'target_folder_name']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
                'error_data' => $model->error_data,
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        return $model->folder_event_copy($res['UserNode'], true);
    }

    /**
     * Метод для регистрации события folder create
     * @param array $data
     * @return array
     */
    protected function folder_event_create($data)
    {
        if (!array_key_exists('parent_folder_uuid', $data)) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => "parent_folder_uuid is required. Set null or empty string, if you need root."
            ];
        }
        $model = new NodeApi(['node_hash', 'user_hash', 'folder_name']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
                'error_data' => $model->error_data,
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        return $model->folder_event_create($res['UserNode'], false, false, true, true);
    }

    /**
     * Метод для регистрации события folder delete
     * @param array $data
     * @return array
     */
    protected function folder_event_delete($data)
    {
        $model = new NodeApi(['node_hash', 'user_hash', 'folder_uuid', 'last_event_id']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
                'error_data' => $model->error_data,
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        return $model->folder_event_delete($res['UserNode'], false, false, true, true);
    }

    /**
     * Метод для регистрации события folder move
     * @param array $data
     * @return array
     */
    protected function folder_event_move($data)
    {
        if (!array_key_exists('new_parent_folder_uuid', $data)) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => "new_parent_folder_uuid is required. Set null or empty string, if you need root."
            ];
        }
        $model = new NodeApi(['node_hash', 'user_hash', 'folder_uuid', 'last_event_id', 'new_folder_name']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors(),
                'error_data' => $model->error_data,
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res \common\models\Users */
        /* @var $res \common\models\UserNode */

        return $model->folder_event_move($res['UserNode']);
    }

    /**
     * Метод для выдачи списка файлов с привязкой к событиям
     * @param array $data
     * @return array
     */
    protected function file_list($data)
    {
        $model = new NodeApi(['node_hash', 'user_hash', 'last_event_id']);
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

        return $model->file_list($res['UserNode']);
    }

    /**
     * Метод для выдачи списка евентов с привязкой к событиям
     * @param array $data
     * @return array
     */
    protected function file_events($data)
    {
        $model = new NodeApi(['node_hash', 'user_hash', 'last_event_id', 'limit', 'offset', 'checked_event_id', 'events_count_check', 'node_without_backup']);
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

        return $model->file_events($res['UserNode']);
    }

    /**
     * Метод для регистрации события готовности патча
     * @param array $data
     * @return array
     */
    protected function patch_ready($data)
    {
        $model = new NodeApi(['node_hash', 'user_hash', 'diff_uuid', 'diff_size']);
        if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }

        if ($model->diff_size == 0) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => "If diff is ready it can't be equal 0.",
            ];
        }

        $res = $this->getUserAndUserNode($model);
        if ($res['result'] === "error") {
            return $res;
        }
        /* @var $res[User] \common\models\Users */
        /* @var $res[UserNode] \common\models\UserNode */

        return $model->patch_ready($res['UserNode'], $res['User']);
    }

    /**
     * @return array
     */
    protected function download($data)
    {
        $model = new NodeApi(['node_hash', 'user_hash', 'upload_id']);
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

        return $model->download($res['UserNode']);
    }
}
