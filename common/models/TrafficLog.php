<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%traffic_log}}".
 *
 * @property int $record_id ID
 * @property string $record_created Date
 * @property int $user_id id пользователя, ссылка на users.user_id.
 * @property int $node_id id ноды, ссылка на user_node.node_id
 * @property string $event_uuid уникальный идентификатор события
 * @property int $interval к-во секунд за которое считался трафик (передано/принято)
 * @property int $tx_wd передано по webrtc p2p
 * @property int $rx_wd принято по webrtc p2p
 * @property int $tx_wr передано по webrtc relay
 * @property int $rx_wr принято по webrtc relay
 * @property int $is_share Признак что трафик был по расшаренному файлу
 *
 * @property UserNode $node
 * @property Users $user
 */
class TrafficLog extends ActiveRecord
{
    const IS_SHARE  = 1;
    const NOT_SHARE = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%traffic_log}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'record_created',
                'updatedAtAttribute' => null,
                'value' => function() { return date(SQL_DATE_FORMAT); }
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['record_created'], 'required'],
            [['record_created'], 'safe'],
            [['user_id', 'node_id', 'interval', 'tx_wd', 'rx_wd', 'tx_wr', 'rx_wr', 'is_share'], 'default', 'value' => 0],
            [['user_id', 'node_id', 'interval', 'tx_wd', 'rx_wd', 'tx_wr', 'rx_wr', 'is_share'], 'integer'],
            [['is_share'], 'in', 'range' => [self::IS_SHARE, self::NOT_SHARE]],
            [['event_uuid'], 'string', 'max' => 32],
            //[['event_uuid', 'user_id'], 'unique', 'targetAttribute' => ['event_uuid', 'user_id']],
            [['node_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserNode::className(), 'targetAttribute' => ['node_id' => 'node_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'record_id' => 'ID',
            'record_created' => 'Date',
            'user_id' => 'UserID',
            'node_id' => 'NodeID',
            'event_uuid' => 'event_uuid',
            'interval' => 'Number of seconds for traffic calculate (transfer/received)',
            'tx_wd' => 'transfer по webrtc p2p',
            'rx_wd' => 'received по webrtc p2p',
            'tx_wr' => 'transfer по webrtc relay',
            'rx_wr' => 'received по webrtc relay',
            'is_share' => 'flag that is traffic by shared file',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNode()
    {
        return $this->hasOne(UserNode::className(), ['node_id' => 'node_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
    }
}
