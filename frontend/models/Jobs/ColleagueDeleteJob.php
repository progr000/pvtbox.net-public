<?php
namespace frontend\models\Jobs;

use Yii;
use yii\queue\JobInterface;
use yii\base\BaseObject;
use common\models\UserFiles;
use common\models\QueuedEvents;
use frontend\models\CollaborationApi;

/**
 * ColleagueDeleteJob
 *
 * @property string $CollaboratedFolder_file_id
 * @property integer $UserCollaboration_collaboration_id
 * @property string $UserCollaboration_file_uuid
 * @property integer $UserCollaboration_user_id
 * @property integer $UserOwner_user_id
 * @property string $UserOwner_user_email
 * @property string $UserOwner__full_path
 * @property string $UserColleague_colleague_email
 * @property integer $UserColleague_user_id
 * @property integer $UserColleague_colleague_id
 * @property bool $is_colleague_self_leave
 * @property string $event_uuid_from_node
 */
class ColleagueDeleteJob extends BaseObject implements JobInterface
{
    public $CollaboratedFolder_file_id;

    public $UserCollaboration_collaboration_id;
    public $UserCollaboration_file_uuid;
    public $UserCollaboration_user_id;

    public $UserOwner_user_id;
    public $UserOwner_user_email;
    public $UserOwner__full_path;

    public $UserColleague_user_id;
    public $UserColleague_colleague_id;

    public $event_uuid_from_node; // этот параметр тут только для того что бы найти по нему запись в QueuedEvents
    public $is_colleague_self_leave;

    /**
     * @param \yii\queue\Queue $queue
     * @return array
     */
    public function execute($queue)
    {
        $QueuedEvent = QueuedEvents::findOne(['event_uuid' => $this->event_uuid_from_node]);
        if ($QueuedEvent) {
            $QueuedEvent->job_status  = QueuedEvents::STATUS_DELAYED;
            $QueuedEvent->job_started = date(SQL_DATE_FORMAT);
            $QueuedEvent->save();
        }

        $redis = Yii::$app->redis;
        $CollaboratedFolder = UserFiles::findOne(['file_id' => $this->CollaboratedFolder_file_id]);

        if ($CollaboratedFolder) {
            CollaborationApi::colleagueDelete_exec(
                $redis,
                $CollaboratedFolder,
                $this->UserCollaboration_collaboration_id,
                $this->UserCollaboration_file_uuid,
                $this->UserCollaboration_user_id,
                $this->UserOwner_user_id,
                $this->UserOwner_user_email,
                $this->UserOwner__full_path,
                $this->UserColleague_user_id,
                $this->UserColleague_colleague_id,
                $this->is_colleague_self_leave,
                $this->event_uuid_from_node
            );
        }

        if ($QueuedEvent) {
            $QueuedEvent->job_status   = QueuedEvents::STATUS_FINISHED;
            $QueuedEvent->job_finished = date(SQL_DATE_FORMAT);
            $QueuedEvent->save();
        }
    }
}
