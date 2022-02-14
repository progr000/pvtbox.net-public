<?php
namespace frontend\models\Jobs;

use Yii;
use yii\queue\JobInterface;
use yii\base\BaseObject;
use common\models\UserFiles;
use common\models\UserNode;
use common\models\QueuedEvents;
use frontend\models\NodeApi;

/**
 * CopyFolderJob
 *
 * @property integer $folder_parent_id
 * @property integer $file_id
 * @property string $node_hash
 * @property string $target_folder_name
 * @property \yii\redis\Connection $redis
 * @property integer $max_timestamp
 * @property string|null $event_uuid_from_node
 */
class CopyFolderJob extends BaseObject implements JobInterface
{
    public $folder_parent_id;
    public $target_folder_name;
    public $file_id;
    public $node_hash;
    public $max_timestamp;
    public $event_uuid_from_node;

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
        $UserFile = UserFiles::findOne(['file_id' => $this->file_id]);
        $UserNode = UserNode::findByHash($this->node_hash);
        /** @var \common\models\UserFiles $parent */
        if ($this->folder_parent_id) {
            $parent = UserFiles::findOne([
                'file_id' => $this->folder_parent_id,
                'is_folder' => UserFiles::TYPE_FOLDER,
                'user_id' => $UserNode->user_id,
                //'is_deleted' => UserFiles::FILE_UNDELETED,
            ]);
            if (!$parent) {
                return [
                    'result'  => "error",
                    'errcode' => NodeApi::ERROR_DATABASE_FAILURE,
                    'info'    => "DB error. Parent folder with file_id='{$this->folder_parent_id}' does not exist."
                ];
            }
        } else {
            $parent = null;
        }

        $ret = NodeApi::folder_event_copy_exec(
            $redis,
            $UserFile,
            $UserNode,
            $parent,
            $this->target_folder_name,
            $this->max_timestamp,
            $this->event_uuid_from_node
        );

        if ($QueuedEvent) {
            $QueuedEvent->job_status   = QueuedEvents::STATUS_FINISHED;
            $QueuedEvent->job_finished = date(SQL_DATE_FORMAT);
            $QueuedEvent->save();
        }

        return $ret;
    }
}
