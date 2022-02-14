<?php
namespace backend\models\forms;

use Yii;
use yii\base\Model;
use yii\helpers\Json;
use common\models\Users;
use common\models\QueuedEvents;
use backend\models\Jobs\StartDopJob;
use frontend\models\NodeApi;

/**
 * Password reset form
 *
 * @property \yii\queue\file\Queue $queue2
 *
 */
class DeleteOldPatches extends Model
{
    protected $queue2;

    public $userId;
    public $restorePatchTTL;

    public function __construct()
    {
        $this->queue2 = (isset(Yii::$app->queue2) && method_exists(Yii::$app->queue2, 'push')) ? Yii::$app->queue2 : false;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'restorePatchTTL'    => 'restorePatchTTL',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['restorePatchTTL', 'userId'], 'required'],
            [['restorePatchTTL', 'userId'], 'integer', 'min' => 0],
        ];
    }

    /**
     * @param \common\models\Users $User
     * @return array
     */
    public function startDop($User)
    {
        if ($User->user_dop_status ==  Users::DOP_IN_PROGRESS) {
            return [
                'status' => false,
                'info'   => 'DeleteOldPatches in progress for this user.',
            ];
        }

        $User->user_dop_log = 'In progress...';
        $User->user_dop_status = Users::DOP_IN_PROGRESS;
        $User->save();

        if ($this->queue2) {
            /* выполняем остальную часть через очередь */
            $unique_id = md5(uniqid(rand(), true));
            $job_id = $this->queue2->push(new StartDopJob([
                'user_id'              => $User->user_id,
                'restorePatchTTL'      => $this->restorePatchTTL,
                'unique_id'            => $unique_id,
            ]));

            $QueuedEvent = new QueuedEvents();
            $QueuedEvent->event_uuid = $unique_id;
            $QueuedEvent->job_id     = (string) $job_id;
            $QueuedEvent->user_id    = $User->user_id;
            $QueuedEvent->node_id    = null;
            $QueuedEvent->job_status = QueuedEvents::STATUS_WAITING;
            $QueuedEvent->job_type   = QueuedEvents::TYPE_DEL_OLD_PATCHES;
            $QueuedEvent->queue_id   = 'queue2';
            $QueuedEvent->save();
            return [
                'status' => true,
                'result' => "queued",
                'info' => "startDop stored in queue successfully",
                'data' => [
                    'job_id'     => $QueuedEvent->job_id,
                ],
            ];
        } else {
            /* выполняем напрямую */
            return self::startDop_exec(
                $User,
                $this->restorePatchTTL
            );
        }
    }

    /**
     * @param \common\models\Users $User
     * @param integer $restorePatchTTL
     * @return array
     */
    public static function startDop_exec($User, $restorePatchTTL)
    {
        $model = new NodeApi(['DOP_onlyForUserId', 'DOP_restorePatchTTL']);
        if (!$model->load(['NodeApi' => [
                'DOP_onlyForUserId' => $User->user_id,
                'DOP_restorePatchTTL' => $restorePatchTTL,
            ]]) || !$model->validate()
        ) {

            $User->user_dop_log = Json::encode($model->getErrors());
            $User->user_dop_status = Users::DOP_IS_COMPLETE;
            $User->save();

            return [
                'status' => false,
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info' => $model->getErrors(),
            ];
        }

        try {
            $task_log = $model->deleteOldPatches();
        } catch (\Exception $e) {
            $task_log = Json::encode($e);
        }

        $User->user_dop_log = $task_log;
        $User->user_dop_status = Users::DOP_IS_COMPLETE;
        $User->save();

        return [
            'status' => true,
            'result' => 'ok',
        ];
    }
}
