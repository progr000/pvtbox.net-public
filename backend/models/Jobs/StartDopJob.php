<?php
namespace backend\models\Jobs;

use Yii;
use yii\queue\JobInterface;
use yii\base\BaseObject;
use common\models\QueuedEvents;
use common\models\Users;
use backend\models\forms\DeleteOldPatches;

/**
 * CopyFolderJob
 *
 * @property integer $user_id
 * @property integer $restorePatchTTL
 * @property string|null $unique_id
 */
class StartDopJob extends BaseObject implements JobInterface
{
    public $user_id;
    public $restorePatchTTL;
    public $unique_id;

    /**
     * @param \yii\queue\Queue $queue
     * @return array
     */
    public function execute($queue)
    {
        $QueuedEvent = QueuedEvents::findOne(['event_uuid' => $this->unique_id]);
        if ($QueuedEvent) {
            $QueuedEvent->job_status  = QueuedEvents::STATUS_DELAYED;
            $QueuedEvent->job_started = date(SQL_DATE_FORMAT);
            $QueuedEvent->save();
        }

        $User = Users::findIdentity($this->user_id);
        $ret = DeleteOldPatches::startDop_exec(
            $User,
            $this->restorePatchTTL
        );

        if ($QueuedEvent) {
            $QueuedEvent->job_status   = QueuedEvents::STATUS_FINISHED;
            $QueuedEvent->job_finished = date(SQL_DATE_FORMAT);
            $QueuedEvent->save();
        }

        return $ret;
    }
}
