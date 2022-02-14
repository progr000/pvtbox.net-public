<?php
namespace frontend\models\Jobs;

use Yii;
use yii\queue\JobInterface;
use yii\base\BaseObject;
use common\models\UserFiles;
use common\models\Users;
use common\models\QueuedEvents;
use frontend\models\CollaborationApi;
use yii\queue\RetryableJobInterface;

/**
 * ColleagueAddJob
 *
 * @property integer $User_for_Colleague_user_id
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
 * @property string $join_or_include
 * @property string $event_uuid_from_node
 */
class ColleagueAddJob extends BaseObject implements JobInterface //RetryableJobInterface
{
    public $User_for_Colleague_user_id;
    public $CollaboratedFolder_file_id;

    public $UserCollaboration_collaboration_id;
    public $UserCollaboration_file_uuid;
    public $UserCollaboration_user_id;

    public $UserOwner_user_id;
    public $UserOwner_user_email;
    public $UserOwner__full_path;

    public $UserColleague_colleague_email;
    public $UserColleague_user_id;
    public $UserColleague_colleague_id;

    public $join_or_include;

    public $event_uuid_from_node; // этот параметр тут только для того что бы найти по нему запись в QueuedEvents

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
        $User_for_Colleague = Users::findIdentity($this->User_for_Colleague_user_id);
        $CollaboratedFolder = UserFiles::findOne(['file_id' => $this->CollaboratedFolder_file_id]);
        if ($CollaboratedFolder && $User_for_Colleague) {
            CollaborationApi::colleagueAdd_exec(
                $redis,
                $User_for_Colleague,
                $CollaboratedFolder,
                $this->UserCollaboration_collaboration_id,
                $this->UserCollaboration_file_uuid,
                $this->UserCollaboration_user_id,
                $this->UserOwner_user_id,
                $this->UserOwner_user_email,
                $this->UserOwner__full_path,
                $this->UserColleague_colleague_email,
                $this->UserColleague_user_id,
                $this->UserColleague_colleague_id,
                $this->join_or_include,
                $this->event_uuid_from_node
            );
        }

        if ($QueuedEvent) {
            $QueuedEvent->job_status   = QueuedEvents::STATUS_FINISHED;
            $QueuedEvent->job_finished = date(SQL_DATE_FORMAT);
            $QueuedEvent->save();
        }
    }

    /*
    public function getTtr()
    {
        return 60 * 15;
    }

    public function canRetry($attempt, $error)
    {
        //return ($attempt < 5) && ($error instanceof TemporaryException);
        return true;//($error instanceof Exceptio);
    }
    */
}
