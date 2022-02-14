<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;

/**
 * This is the model class for table "{{%queued_events}}".
 *
 * @property string $event_uuid уникальный идентификатор события которое однозначно определяет состояние файла в момент события
 * @property string $job_id id задачи, которая находится в очереди
 * @property int $node_id id ноды, на которой возникло событие ссылка на user_node.node_id
 * @property int $user_id id пользователя у которого возникло событие, ссылка на users.user_id.
 * @property string $job_type
 * @property string $job_status
 * @property string $job_created
 * @property string $job_started
 * @property string $job_finished
 * @property string $queue_id
 *
 * @property UserNode $node
 * @property Users $user
 */
class QueuedEvents extends ActiveRecord
{
    protected static $CACHE_TTL = 3600;

    const TYPE_COPY_FOLDER     = 'CopyFolder';
    const TYPE_COLLEAGUE_ADD   = 'ColleagueAdd';
    const TYPE_COLLEAGUE_DEL   = 'ColleagueDelete';
    const TYPE_DEL_OLD_PATCHES = 'DeleteOldPatches';
    const TYPE_TESTS_EXECUTION = 'TestExecution';

    const STATUS_WAITING  = 'waiting';
    const STATUS_DELAYED  = 'delayed';
    const STATUS_RESERVED = 'reserved';
    const STATUS_FINISHED = 'done';
    const STATUS_CANCELED = 'canceled';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%queued_events}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'job_created',
                'updatedAtAttribute' => null,
                'value' => function() { return date(SQL_DATE_FORMAT); }
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['job_type', 'job_status', 'job_created', 'job_started', 'job_finished'], 'safe'],
            [['node_id', 'user_id'], 'default', 'value' => null],
            [['node_id', 'user_id'], 'integer'],
            [['event_uuid', 'job_id'], 'string', 'max' => 32],
            [['queue_id'], 'string', 'max' => 20],
            [['event_uuid', 'user_id'], 'unique', 'targetAttribute' => ['event_uuid', 'user_id']],
            [['node_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserNode::className(), 'targetAttribute' => ['node_id' => 'node_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'event_uuid' => 'event_uuid',
            'job_id'     => 'JobID',
            'node_id'    => 'NodeID',
            'user_id'    => 'UserID',
            'queue_id'   => 'QueueID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return array
     */
    public static function queuedStatuses()
    {
        return [
            self::STATUS_WAITING  => "waiting",
            self::STATUS_DELAYED  => "delayed",
            self::STATUS_RESERVED => "reserved",
            self::STATUS_FINISHED => "done",
            self::STATUS_CANCELED => "canceled",
        ];
    }

    /**
     * @return array
     */
    public static function queuedTypes()
    {
        return [
            self::TYPE_COPY_FOLDER      => "CopyFolder",
            self::TYPE_COLLEAGUE_ADD    => "ColleagueAdd",
            self::TYPE_COLLEAGUE_DEL    => "ColleagueDelete",
            self::TYPE_DEL_OLD_PATCHES  => "DeleteOldPatches",
            self::TYPE_TESTS_EXECUTION  => "TestExecution",
        ];
    }

    /**
     * Invalidate Cache
     */
    public static function invalidateCache()
    {
        TagDependency::invalidate(Yii::$app->cache, [
            'QueuedEvents',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        self::invalidateCache();
    }
}
