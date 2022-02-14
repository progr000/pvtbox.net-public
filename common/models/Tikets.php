<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%tikets}}".
 *
 * @property string $tiket_id
 * @property string $tiket_created
 * @property string $tiket_theme
 * @property string $tiket_email
 * @property string $tiket_name
 * @property integer $tiket_count_new_user
 * @property integer $tiket_count_new_admin
 * @property string $user_id
 * @property string $admin_id
 *
 * @property TiketsMessages[] $tiketsMessages
 */
class Tikets extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tikets}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'tiket_created',
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
            [['tiket_theme', 'tiket_email', 'tiket_name'], 'required'],
            [['tiket_count_new_user', 'tiket_count_new_admin', 'user_id', 'admin_id'], 'integer'],
            [['tiket_count_new_user', 'tiket_count_new_admin', 'user_id', 'admin_id'], 'default', 'value' => 0],
            [['tiket_theme'], 'string', 'max' => 255],
            [['tiket_email', 'tiket_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tiket_id' => 'ID',
            'tiket_created' => 'Creation date',
            'tiket_theme' => 'Ticket theme',
            'tiket_email' => 'Author email',
            'tiket_name' => 'Author name',
            'tiket_count_new_user' => 'Count of new for user',
            'tiket_count_new_admin' => 'Count of new for admin',
            'user_id' => 'UserID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTiketsMessages()
    {
        return $this->hasMany(TiketsMessages::className(), ['tiket_id' => 'tiket_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
    }

}
