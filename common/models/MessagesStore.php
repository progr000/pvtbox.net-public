<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%messages_store}}".
 *
 * @property int $ms_id
 * @property string $ms_created
 * @property string $ms_type
 * @property string $ms_data
 * @property int $user_id
 *
 * @property Users $user
 */
class MessagesStore extends ActiveRecord
{
    const TYPE_SUPPORT = 'support';
    const TYPE_PRICING = 'pricing';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%messages_store}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'ms_created',
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
            [['ms_type', 'ms_data'], 'required'],
            [['ms_created'], 'safe'],
            [['ms_data'], 'safe'],
            [['user_id'], 'default', 'value' => null],
            [['user_id'], 'integer'],
            [['ms_type'], 'in', 'range' => [self::TYPE_PRICING, self::TYPE_SUPPORT]],
            [['user_id'], 'exist', 'skipOnError' => true, 'skipOnEmpty' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ms_id' => 'ID',
            'ms_created' => 'Created',
            'ms_type' => 'Type',
            'ms_data' => 'Message',
            'user_id' => 'User ID',
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
    public static function getTypes()
    {
        return [
            self::TYPE_PRICING   => self::TYPE_PRICING,
            self::TYPE_SUPPORT  => self::TYPE_SUPPORT,
        ];
    }
}
