<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\helpers\Json;
use yii\base\Exception;
use common\models\UserFiles;
use common\models\UserFileEvents;
use common\models\Preferences;
use common\models\Licenses;
use common\models\RedisSafe;

/**
 * CollaborationApi
 *
 * @property integer $event_id
 * @property integer $user_id
 *
 * @property \common\models\UserFileEvents $UserFileEvent
 * @property \common\models\Users $UserOwner
 * @property \yii\redis\Connection $redis
 * @property \yii\mutex\FileMutex $mutex
 *
 */
class RestorePatchApi extends Model
{

    protected $UserOwner;
    protected $UserFileEvent;
    protected $redis;
    protected $mutex;

    public $event_id;
    public $user_id;

    public $dynamic_rules = null;

    /**************************** +++ GLOBAL +++ ***************************/
    /**
     * NodeApi constructor.
     * @param array $required_fields Поля которые будут проверяться на наличие в джсоне
     */
    public function __construct(array $required_fields = [])
    {
        if (is_array($required_fields) && sizeof($required_fields)) {
            $this->dynamic_rules = [[$required_fields, 'required', 'message' => 'Fields ' . implode(', ', $required_fields) . ' are required.']];
        }
        $this->redis = Yii::$app->redis;
        $this->mutex = Yii::$app->mutex;

        parent::__construct();
    }

    /**
     * Правила валидации данных
     * @return array
     */
    public function rules()
    {
        $rules = [
            [['event_id', 'user_id'], 'integer'],
        ];
        if (is_array($this->dynamic_rules)) {
            return array_merge($this->dynamic_rules, $rules);
        } else {
            return $rules;
        }
    }
    /**************************** --- GLOBAL --- ***************************/

    /**
     * @param \common\models\Users $User
     * @param bool $start_from_next_event - если true то сначала ищется стартовый евент апдейта который идет после евента который указан в $this->event_id
     * @return array
     */
    public function restorePatch($User, $start_from_next_event = true)
    {
        /** @var \common\models\UserFileEvents $UserFileEvent */
        if ($start_from_next_event) {
            $before_start_event = UserFileEvents::findOne(['event_id' => $this->event_id]);
            if (!$before_start_event) {
                return [
                    'status' => false,
                    'info'   => "Event (before start) not found.",
                ];
            }
            $UserFileEvent = UserFileEvents::find()
                ->where("(event_id > :event_id) AND (file_id = :file_id) AND (event_type = :TYPE_UPDATE)", [
                    'event_id'    => $before_start_event->event_id,
                    'file_id'     => $before_start_event->file_id,
                    'TYPE_UPDATE' => UserFileEvents::TYPE_UPDATE
                ])
                ->orderBy(['event_id' => SORT_ASC])
                ->limit(1)
                ->one();
        } else {
            /**/
            $UserFileEvent = UserFileEvents::findOne([
                'event_id' => $this->event_id,
                'event_type' => UserFileEvents::TYPE_UPDATE
            ]);
        }

        /* Поиск заданного евента */
        if (!$UserFileEvent) {
            return [
                'status' => false,
                'info'   => "Event not found.",
            ];
        }

        /* проверка что евент принадлежит пользователю */
        if ($UserFileEvent->user_id != $User->user_id) {
            return [
                'status' => false,
                'info'   => "Access error. You are not owner of this event",
            ];
        }

        /* проверка что этот евент еще не устарел */
        if ($UserFileEvent->event_timestamp < (time() - Preferences::getValueByKey('RestorePatchTTL', 2592000, 'int'))) {
            return [
                'status' => false,
                'info'   => "Access error. This event is obsolete.",
            ];
        }

        /* проверка что этот евент имеет не нулевой rev_diff_file_size */
        if (!$UserFileEvent->rev_diff_file_size && $UserFileEvent->file_size_before_event) {
            return [
                'status' => false,
                'info'   => "Can't restore this patch (rev_diff_file_size=0) and (file_size_before_event>0)",
            ];
        }

        /* Проверка что более поздние дифы уже имеют не нулевой rev_diff_file_size */
        $NullRevDiffEvents = UserFileEvents::find()
            ->where([
                'file_id' => $UserFileEvent->file_id,
                'event_type' => UserFileEvents::TYPE_UPDATE,
            ])
            ->andWhere('((rev_diff_file_size=0) OR (rev_diff_file_size IS NULL)) AND (file_size_before_event>0)')
            ->andWhere('event_timestamp >= :event_timestamp', ['event_timestamp' => $UserFileEvent->event_timestamp])
            ->one();
        if ($NullRevDiffEvents) {
            return [
                'status' => false,
                'info'   => "Can't restore this patch (later diff have a rev_diff_file_size=0)",
            ];
        }

        /* Ищем файл в базе */
        $UserFile = UserFiles::findOne(['file_id' => $UserFileEvent->file_id]);
        if (!$UserFile) {
            return [
                'status' => false,
                'info'   => "Database error. Can't find UserFile",
            ];
        }

        /* Если файл будет после рестора иметь такой же хеш как и сейчас, нет смысла делать рестор */
        if ($UserFile->file_md5 == $UserFileEvent->file_hash_before_event) {
            return [
                'status' => false,
                'info'   => "File is already in this state.",
            ];
        }

        /* Нода веб-фм */
        $UserNode = NodeApi::registerNodeFM($User);
        if (!$UserNode) {
            return [
                'status' => false,
                'info'   => "Database error. Can't find UserNode",
            ];
        }

        /* Проверим нет ли лока на выполнение ФС действий вследствие друхих действий этого юзера */
        $mutex_name = 'user_id_' . $UserNode->user_id;
        if (!$this->mutex->acquire($mutex_name, MUTEX_WAIT_TIMEOUT)) {
            return [
                'status' => false,
                'info' => "File or folder is locked now, try later please.",
            ];
        }

        /* проверка что если это коллаба, то она не должна быть залочена */
        if ($UserFile->collaboration_id) {
            $mutex_collaboration_name = 'collaboration_id_' . $UserFile->collaboration_id;
            if (!$this->mutex->acquire($mutex_collaboration_name, MUTEX_WAIT_TIMEOUT)) {
                return [
                    'status' => false,
                    'info' => "Collaboration is locked now, try later please..",
                ];
            }
        }

        /* Применение откатов (восстанавливаем патч) */
        $transaction = Yii::$app->db->beginTransaction();

        $fileversions = UserFileEvents::find()
            ->select([
                'event_id',
                'event_timestamp',
                'diff_file_uuid',
                'diff_file_size',
                'rev_diff_file_uuid',
                'rev_diff_file_size',
                'file_size_before_event',
                'file_size_after_event',
                'file_hash',
                'file_hash_before_event',
            ])
            ->where([
                'file_id'    => $UserFileEvent->file_id,
                'event_type' => UserFileEvents::TYPE_UPDATE,
            ])
            //->andWhere('event_timestamp >= :event_timestamp', ['event_timestamp' => $UserFileEvent->event_timestamp])
            ->andWhere('event_id >= :event_id', ['event_id' => $UserFileEvent->event_id])
            ->orderBy(['event_id' => SORT_DESC])
            ->asArray()
            ->all();

        $event_data = [];
        $cnt_event = sizeof($fileversions);
        $i = 0;
        foreach ($fileversions as $k=>$v) {
            $i++;
            $data['file_uuid']     = $UserFile->file_uuid;
            $data['file_size']     = $v['file_size_before_event'];
            $data['last_event_id'] = UserFileEvents::find()
                ->andWhere(['file_id' => $UserFileEvent->file_id])
                ->max('event_id');
            if (!$data['last_event_id']) {
                $transaction->rollBack();
                return [
                    'status' => false,
                    'info'   => "Database error. Can't find last_event_id",
                ];
            }
            $data['is_restore_patch'] = true;
            $data['diff_file_uuid'] = $v['rev_diff_file_uuid'];
            $data['diff_file_size'] = $v['rev_diff_file_size'];
            $data['rev_diff_file_uuid'] = $v['diff_file_uuid'];
            $data['rev_diff_file_size'] = $v['diff_file_size'];
            $data['event_invisible'] = UserFileEvents::EVENT_INVISIBLE;
            $data['hash'] = $v['file_hash_before_event'];
            /*
            if ($i == 1) {
                $data['event_invisible'] = UserFileEvents::EVENT_VISIBLE;
            }*/
            if ($k == $cnt_event-1) {
                $data['event_invisible'] = UserFileEvents::EVENT_VISIBLE;
            }
            //var_dump($data);exit;


            $model = new NodeApi(['file_uuid', 'file_size', 'last_event_id', 'diff_file_size', 'rev_diff_file_size', 'hash']);
            if (!$model->load(['NodeApi' => $data]) || !$model->validate()) {
                return [
                    'result'  => "error",
                    'errcode' => NodeApi::ERROR_WRONG_DATA,
                    'info'    => $model->getErrors()
                ];
            }

            $res = $model->file_event_update($UserNode, true, false, true, true);
            if ($res['result'] == 'error') {
                $transaction->rollBack();
                return [
                    'status' => false,
                    'info'   => $res['info'],
                ];
            }
            if (isset($res['event_data'])) {
                foreach ($res['event_data'] as $ev) {
                    $event_data[] = $ev;
                }
            }

        }

        if (!in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT])) {
            try {
                $this->redis->publish("user:{$UserNode->user_id}:fs_events", Json::encode($event_data));
                $this->redis->save();
            } catch (\Exception $e) {
                RedisSafe::createNewRecord(
                    RedisSafe::TYPE_FS_EVENTS,
                    $UserNode->user_id,
                    null,
                    Json::encode([
                        'action'           => 'fs_events',
                        'chanel'           => "user:{$UserNode->user_id}:fs_events",
                        'user_id'          => $UserNode->user_id,
                    ])
                );
            }
        }

        $transaction->commit();

        $this->mutex->release($mutex_name);
        if (isset($mutex_collaboration_name)) { $this->mutex->release($mutex_collaboration_name); }

        return [
            'status'     => true,
            'info'       => "Success restore patch",
            'event_data' => $event_data,
        ];

    }
}
