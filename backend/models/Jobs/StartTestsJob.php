<?php
namespace backend\models\Jobs;

use Yii;
use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;
use common\models\QueuedEvents;

/**
 * CopyFolderJob
 *
 * @property integer $user_id
 * @property integer $restorePatchTTL
 * @property string|null $unique_id
 */
class StartTestsJob extends BaseObject implements RetryableJobInterface
{
    public $report_start_script_yii;
    public $report_start_script_db;
    public $report_lock_file;
    public $unique_id;


    public function getTtr()
    {
        return 10 * 60;
    }

    public function canRetry($attempt, $error)
    {
        Yii::$app->cache->delete('TestExecutionInProgress');

        $QueuedEvent = QueuedEvents::findOne(['event_uuid' => $this->unique_id]);
        if ($QueuedEvent) {
            $QueuedEvent->job_status  = QueuedEvents::STATUS_CANCELED;
            $QueuedEvent->job_finished = date(SQL_DATE_FORMAT);
            $QueuedEvent->save();
        }

        return false;
    }

    /**
     * @param \yii\queue\Queue $queue
     * @return array
     */
    public function execute($queue)
    {
        echo "start\n";
        $QueuedEvent = QueuedEvents::findOne(['event_uuid' => $this->unique_id]);
        if ($QueuedEvent) {
            $QueuedEvent->job_status  = QueuedEvents::STATUS_DELAYED;
            $QueuedEvent->job_started = date(SQL_DATE_FORMAT);
            $QueuedEvent->save();
        }

        if (!file_exists($this->report_lock_file)) {
            echo "progress... \n";
            if ($this->report_start_script_yii) {
                $out = shell_exec($this->report_start_script_yii);
                sleep(5);
            }
            if ($this->report_start_script_db) {
                $out = shell_exec($this->report_start_script_db);
            }
        } else {
            $out = 'Tests are already in progress. Wait until its finished';
        }

        if ($QueuedEvent) {
            $QueuedEvent->job_status   = QueuedEvents::STATUS_FINISHED;
            $QueuedEvent->job_finished = date(SQL_DATE_FORMAT);
            $QueuedEvent->save();
        }

        echo "end\n";
        Yii::$app->cache->delete('TestExecutionInProgress');
        //echo $out;

        return $out;
    }
}
