<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%tikets_messages}}".
 *
 * @property string $message_id
 * @property string $message_created
 * @property string $message_text
 * @property integer $message_read_user
 * @property integer $message_read_admin
 * @property integer $message_deleted_user
 * @property integer $message_deleted_admin
 * @property string $tiket_id
 * @property string $user_id
 * @property string $admin_id
 *
 * @property Tikets $tk
 */
class TiketsMessages extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tikets_messages}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'message_created',
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
            [['message_text'], 'required'],
            [['message_text'], 'string'],
            [['message_read_user', 'message_read_admin', 'message_deleted_user', 'message_deleted_admin', 'tiket_id', 'user_id', 'admin_id'], 'integer'],
            //[['message_read_user', 'message_read_admin', 'message_deleted_user', 'message_deleted_admin', 'user_id', 'admin_id'], 'default', 'value' => 0],
            [['tiket_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tikets::className(), 'targetAttribute' => ['tiket_id' => 'tiket_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'message_id' => 'ID',
            'message_created' => 'Creation date',
            'message_text' => 'Message text',
            'message_read_user' => 'Tkm Read User',
            'message_read_admin' => 'Tkm Read Admin',
            'message_deleted_user' => 'Tkm Deleted User',
            'message_deleted_admin' => 'Tkm Deleted Admin',
            'tiket_id' => 'TicketID',
            'user_id' => 'UserID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTikets()
    {
        return $this->hasOne(Tikets::className(), ['tiket_id' => 'tiket_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
    }

}
