<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%shu_check_log}}".
 *
 * @property int $record_id
 * @property int $shu_id
 * @property string $check_ip
 * @property string $check_created
 * @property string $check_data
 *
 * @property SelfHostUsers $shu
 */
class ShuCheckLog extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shu_check_log}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'check_created',
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
            [['shu_id'], 'default', 'value' => null],
            [['shu_id'], 'integer'],
            //[['check_created'], 'required'],
            [['check_created'], 'safe'],
            [['check_data'], 'string'],
            [['check_ip'], 'string', 'max' => 30],
            [['shu_id'], 'exist', 'skipOnError' => true, 'targetClass' => SelfHostUsers::className(), 'targetAttribute' => ['shu_id' => 'shu_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'record_id' => 'Record ID',
            'shu_id' => 'Shu ID',
            'check_ip' => 'Check Ip',
            'check_created' => 'Check Created',
            'check_data' => 'Check Data',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShu()
    {
        return $this->hasOne(SelfHostUsers::className(), ['shu_id' => 'shu_id']);
    }
}
