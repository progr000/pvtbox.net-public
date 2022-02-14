<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use common\helpers\Functions;

/**
 * This is the model class for table "{{%remote_actions}}".
 *
 * @property string $action_id
 * @property string $action_uuid
 * @property string $action_type
 * @property string $action_data
 * @property string $source_node_id
 * @property string $target_node_id
 * @property string $user_id
 * @property string $action_init_time
 * @property string $action_end_time
 */
class RemoteActions extends ActiveRecord
{

    const TYPE_LOGOUT      = 'logout';
    const TYPE_WIPE        = 'wipe';
    const TYPE_CREDENTIALS = 'credentials';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%remote_actions}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'action_init_time',
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
            //[['action_type'], 'string'],
            [['action_type', 'user_id', 'target_node_id', 'source_node_id'], 'required'],
            [['action_type'], 'in', 'range' => [
                self::TYPE_LOGOUT,
                self::TYPE_WIPE,
                self::TYPE_CREDENTIALS,
            ]],
            [['source_node_id', 'target_node_id', 'user_id'], 'integer'],
            [['action_init_time', 'action_end_time'], 'validateDateField', 'skipOnEmpty' => true],
            [['action_init_time', 'action_end_time', 'action_data'], 'safe'],
            [['action_uuid'], 'string', 'length' => 32],
            [['action_uuid'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id']],
            [['target_node_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserNode::className(), 'targetAttribute' => ['target_node_id' => 'node_id']],
            [['source_node_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserNode::className(), 'targetAttribute' => ['source_node_id' => 'node_id']],
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateDateField($attribute, $params)
    {
        $check = Functions::checkDateIsValidForDB($this->$attribute);
        if (!$check) {
            $this->addError($attribute, 'Invalid date format');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'action_id' => 'ID',
            'action_uuid' => 'uuid-операции, unique, not null',
            'action_type' => 'тип операции',
            'action_data' => 'дополнительные данные по акшену',
            'source_node_id' => 'Информация об источнике, с которого была инициирована операция, для сайта ноль, для ноды node_id',
            'target_node_id' => 'Идентификатор node_id целевой ноды',
            'user_id' => 'Идентификатор user_id для ускорения отбора по пользователю',
            'action_init_time' => 'содержит время когда была инициирована операция (время создания записи в БД).',
            'action_end_time' => 'содержит время когда операция была завершена, если получен ответ от целевой ноды об успешном окончании операции.',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
    }
}
