<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%redis_safe}}".
 *
 * @property int $rs_id Id
 * @property string $rs_created Дата создания записи в таблице
 * @property string $rs_type Тип утерянной записи для редис
 * @property string $rs_data Данные утерянной записи для редис
 * @property int $user_id User ID
 * @property int $node_id Node ID
 *
 * @property Users $user
 */
class RedisSafe extends ActiveRecord
{
    const TYPE_COLLABORATION_CHANGES = 'collaboration_changes';
    const TYPE_LICENSE_CHANGES       = 'license_type_changes';
    const TYPE_SHARING_EVENTS        = 'sharing_events';
    const TYPE_REMOTE_ACTIONS        = 'remote_actions';
    const TYPE_FS_EVENTS             = 'fs_events';
    const TYPE_PATCHES_INFO          = 'patches_info';
    const TYPE_UPLOAD_EVENTS         = 'upload_events';
    const TYPE_NODE_STATUS           = 'node_status';
    const TYPE_NOTIFICATIONS_COUNT   = 'notifications_count';
    const TYPE_REPORTS_COUNT         = 'reports_count';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%redis_safe}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'rs_created',
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
            [['rs_created'], 'safe'],
            [['rs_type'], 'required'],
            [['rs_data'], 'string'],
            [['user_id', 'node_id'], 'default', 'value' => null],
            [['user_id', 'node_id'], 'integer'],
            [['rs_type'], 'string', 'max' => 32],
            [['rs_type'], 'in', 'range' => [
                self::TYPE_COLLABORATION_CHANGES,
                self::TYPE_UPLOAD_EVENTS,
                self::TYPE_FS_EVENTS,
                self::TYPE_PATCHES_INFO,
                self::TYPE_LICENSE_CHANGES,
                self::TYPE_REMOTE_ACTIONS,
                self::TYPE_SHARING_EVENTS,
                self::TYPE_NODE_STATUS,
            ]],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'rs_id' => 'Id',
            'rs_created' => 'Creation date',
            'rs_type' => 'Type of lost record',
            'rs_data' => 'Data of lost record',
            'user_id' => 'UserID',
            'node_id' => 'NodeID',
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
     * @param string $rs_type
     * @param integer $user_id
     * @param integer|null $node_id
     * @param string|null $rs_data
     * @return bool
     */
    public static function createNewRecord($rs_type, $user_id, $node_id=null, $rs_data=null)
    {
        $RedisSafe = new RedisSafe();
        $RedisSafe->user_id = $user_id;
        $RedisSafe->node_id = $node_id;
        $RedisSafe->rs_type = $rs_type;
        $RedisSafe->rs_data = $rs_data;
        return $RedisSafe->save();
    }
}
