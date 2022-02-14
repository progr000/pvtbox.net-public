<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%transfers}}".
 *
 * @property string $transfer_id
 * @property string $user_id
 * @property number $transfer_sum
 * @property integer $transfer_type
 * @property integer $transfer_status
 * @property string $transfer_created
 * @property string $transfer_updated
 */
class Transfers extends ActiveRecord
{

    const STATUS_NEW = 0;
    const STATUS_WORK = 1;
    const STATUS_DONE = 2;
    const STATUS_COMPLETION = 3;
    const STATUS_CLOSED = 4;
    const STATUS_REJECTED = 5;

    const TYPE_ROBOX = 0;
    const TYPE_PAYPAL = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%transfers}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'transfer_created',
                'updatedAtAttribute' => 'transfer_updated',
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
            ['user_id', 'required'],
            ['user_id', 'integer'],
            ['transfer_type', 'integer', 'min'=>self::TYPE_ROBOX, 'max'=>self::TYPE_PAYPAL],
            ['transfer_status', 'integer', 'min'=>self::STATUS_NEW, 'max'=>self::STATUS_REJECTED],
            [['transfer_sum'], 'required'],
            [['transfer_sum'], 'number'],
            ['transfer_status', 'default', 'value' => self::STATUS_NEW],
            ['transfer_type', 'default', 'value' => self::TYPE_ROBOX],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'transfer_id' => 'Id',
            'user_id' => 'UserID',
            'transfer_sum' => 'Sum',
            'transfer_type' => 'transfer type',
            'transfer_status' => 'Status',
            'transfer_created' => 'Create date',
            'transfer_updated' => 'Update date',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
    }

    /**
     * returns list of statuses in array
     *
     * @return array
     */
    public static function statusLabels()
    {
        return [
            self::STATUS_NEW => 'Новый', // белый
            self::STATUS_WORK => 'В обработке', // желтый
            self::STATUS_DONE => 'Выполнен', // зеленый (этот зачисляется на баланс, остальные нет)
            self::STATUS_COMPLETION => 'На доработке', //оранжевый
            self::STATUS_CLOSED => 'Закрыт', // черный
            self::STATUS_REJECTED => 'Отклонен', // красный
        ];
    }

    /**
     * return status name by transfer_status value
     * @param integer $transfer_status
     *
     * @return string | null
     */
    public static function statusLabel($transfer_status)
    {
        $labels = self::statusLabels();
        return isset($labels[$transfer_status]) ? $labels[$transfer_status] : null;
    }

    /**
     * returns list of types in array
     *
     * @return array
     */
    public static function typeParams()
    {
        return [
            self::TYPE_ROBOX    => ['name' => 'Robokassa', 'className'=>'classRobokassa', 'url'=>'https://robokassa.ru'],
            self::TYPE_PAYPAL   => ['name' => 'PayPal',    'className'=>'classPayPal',    'url'=>'https://paypal.com'],
        ];
    }


    /**
     * return type name by transfer_type value
     * @param integer $transfer_type
     *
     * @return string | null
     */
    public static function typeLabel($transfer_type)
    {
        $labels = self::typeParams();
        return isset($labels[$transfer_type]) ? $labels[$transfer_type]['name'] : null;
    }

    /**
     * return type url by transfer_type value
     * @param integer $transfer_type
     *
     * @return string | null
     */
    public static function typeUrl($transfer_type)
    {
        $labels = self::typeParams();
        return isset($labels[$transfer_type]) ? $labels[$transfer_type]['url'] : null;
    }

    /**
     * return type className by transfer_type value
     * @param integer $transfer_type
     *
     * @return string|null
     */
    public static function typeClass($transfer_type)
    {
        $labels = self::typeParams();
        return isset($labels[$transfer_type]) ? $labels[$transfer_type]['className'] : null;
    }

    /**
     * returns list of types in array
     *
     * @return array
     */
    public static function typeLabels()
    {
        $typeLabels = [];
        $typeParams = self::typeParams();
        foreach ($typeParams as $k=>$v)
            $typeLabels[$k] = $v['name'];

        return $typeLabels;
    }

    /**
     * @param int|string $id
     * @return Transfers|null
     */
    public static function findIdentity($id)
    {
        return static::findOne(['transfer_id' => $id]);
    }

}
