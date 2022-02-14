<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%paypal_pays}}".
 *
 * @property string $pp_id
 * @property string $pp_payment_id
 * @property string $user_id
 * @property string $transfer_id
 * @property string $pp_token
 * @property string $pp_payer_id
 * @property string $pp_txn_id
 * @property double $pp_sum
 * @property string $pp_sku
 * @property integer $pp_status
 * @property string $pp_status_info
 * @property string $pp_created
 * @property string $pp_updated
 *
 * @property Users $user
 */
class PaypalPays extends ActiveRecord
{
    /*
     * created
     * approved
     * Canceled_Reversal    // Отменено
     * Completed            // Успешно завершен
     * Declined             // Отказано
     * Expired              // Истек срок
     * Failed               // Ошибка
     * In-Progress          // В процессе обработки
     * Partially_Refunded   // Частично возвращен
     * Pending              // Ожидает решения
     * Processed            // Обрабатывается
     * Refunded             // Возврат
     * Reversed             // Обратный
     * Voided               // Анулируется
     * */
    const STATUS_UNPAYED = 0;
    const STATUS_PAYED = 1;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%paypal_pays}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'pp_created',
                'updatedAtAttribute' => 'pp_updated',
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
            [['user_id', 'pp_sum'], 'required'],
            [['user_id', 'transfer_id'], 'integer'],
            [['pp_sum'], 'number'],
            ['pp_status', 'integer', 'min'=>self::STATUS_UNPAYED, 'max'=>self::STATUS_PAYED],
            ['pp_status', 'default', 'value' => self::STATUS_UNPAYED],
            [['pp_payment_id', 'pp_token', 'pp_payer_id', 'pp_status_info', 'pp_txn_id'], 'string', 'max' => 30],
            ['pp_sku', 'string', 'max' => 50],
            [['transfer_id'], 'unique'],
            [['pp_payment_id'], 'unique'],
            [['pp_token'], 'unique'],
            [['pp_txn_id'], 'unique'],
            [['pp_sku'], 'unique'],
            [['transfer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Transfers::className(), 'targetAttribute' => ['transfer_id' => 'transfer_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pp_id' => 'Pp ID',
            'pp_payment_id' => 'Pp Payment ID',
            'user_id' => 'User ID',
            'transfer_id' => 'Transfer ID',
            'pp_token' => 'Pp Token',
            'pp_payer_id' => 'Pp  Payer ID',
            'pp_sum' => 'Pp Sum',
            'pp_sku' => 'Internal UNIT ID',
            'pp_status' => 'Pp Status',
            'pp_status_info' => 'Status Info',
            'pp_created' => 'Pp Created',
            'pp_updated' => 'Pp Updated',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransfer()
    {
        return $this->hasOne(Transfers::className(), ['transfer_id' => 'transfer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
    }

    /**
     * @param int|string $id
     * @return PaypalPays|null
     */
    public static function findIdentity($id)
    {
        return static::findOne(['pp_id' => $id]);
    }

    /**
     * Finds Pays by pp_payment_id
     *
     * @param string $pp_payment_id
     * @return PaypalPays|null
     */
    public static function findByPaymentId($pp_payment_id)
    {
        return static::findOne(['pp_payment_id' => $pp_payment_id]);
    }

    /**
     * Finds Pays by $pp_token
     *
     * @param string $pp_token
     * @return PaypalPays|null
     */
    public static function findByToken($pp_token)
    {
        return static::findOne(['pp_token' => $pp_token]);
    }
}
