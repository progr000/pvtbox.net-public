<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;

/**
 * This is the model class for table "{{%user_payments}}".
 *
 * @property string $pay_id
 * @property string $pay_date
 * @property double $pay_amount
 * @property string $pay_currency
 * @property string $pay_type
 * @property string $pay_for
 * @property string $pay_status
 * @property string $license_type
 * @property int $license_count
 * @property int $license_period
 * @property string $license_expire
 * @property string $user_id
 * @property string $merchant_id
 * @property string $merchant_unique_pay_id
 * @property string $merchant_created
 * @property string $merchant_updated
 * @property double $merchant_amount
 * @property string $merchant_currency
 * @property string $merchant_status
 * @property string $merchant_raw_data
 *
 * @property Users $user
 */
class UserPayments extends ActiveRecord
{
    private static $CACHE_TTL = 3600;

    const CODE_PAY_FOR_PROFESSIONAL       = 'professional';
    const CODE_PAY_FOR_BUSINESS           = 'business';
    const CODE_PAY_FOR_PRO_TO_BUSINESS    = 'from-pro-to-business';
    const CODE_PAY_FOR_BUSINESS_INCREASE  = 'business-increase';
    const CODE_PAY_FOR_RENEWAL            = 'license-renewal';
    const CODE_PAY_FOR_PROFESSIONAL_AGAIN = 'professional-again';
    const CODE_PAY_FOR_BUSINESS_AGAIN     = 'business-again';

    const STATUS_UNPAID     = "unpaid";
    const STATUS_CONFIRMING = "confirming";
    const STATUS_PAID       = "paid";
    const STATUS_CANCELED   = "canceled";
    const STATUS_MISPAID    = "mispaid";
    const STATUS_INFORM     = "inform";

    const CURRENCY_USD = 'usd';

    const MERCHANT_CRYPTONATOR = 'Cryptonator';
    const MERCHANT_PAYPAL      = 'PayPal';

    /**
     *
     */
    public static function payStatuses()
    {
        return [
            self::STATUS_UNPAID => self::STATUS_UNPAID,
            self::STATUS_CONFIRMING => self::STATUS_CONFIRMING,
            self::STATUS_PAID => self::STATUS_PAID,
            self::STATUS_CANCELED => self::STATUS_CANCELED,
            self::STATUS_MISPAID => self::STATUS_MISPAID,
            self::STATUS_INFORM => self::STATUS_INFORM,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_payments}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'merchant_created',
                'updatedAtAttribute' => 'merchant_updated',
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
            [['pay_date', 'license_expire', 'merchant_created', 'merchant_updated'], 'safe'],
            [['pay_amount', 'license_type'], 'required'],
            [['pay_amount', 'merchant_amount'], 'number'],
            [['license_count', 'license_period', 'user_id'], 'integer'],
            [['license_count'], 'default', 'value' => 0],
            [['user_id'], 'default', 'value' => null],
            [['merchant_raw_data'], 'string'],
            [['pay_currency', 'merchant_currency'], 'string', 'max' => 10],
            [['pay_type', 'pay_status', 'merchant_unique_pay_id'], 'string', 'max' => 50],
            [['pay_for', 'merchant_status'], 'string', 'max' => 255],
            [['license_type'], 'string', 'max' => 20],
            [['merchant_id'], 'string', 'max' => 32],

            [['license_period'], 'in', 'range' => [
                Licenses::PERIOD_NOT_SET,
                Licenses::PERIOD_DAILY,
                Licenses::PERIOD_MONTHLY,
                Licenses::PERIOD_ANNUALLY,
                Licenses::PERIOD_ONETIME,
            ]],
            [['license_period'], 'default', 'value' => Licenses::PERIOD_NOT_SET],

            [['merchant_id', 'merchant_unique_pay_id'], 'unique', 'targetAttribute' => ['merchant_id', 'merchant_unique_pay_id']],
            [['user_id'], 'exist', 'skipOnEmpty' => true, 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'pay_id' => 'Id',
            'pay_date' => 'Payment date',
            'pay_sum' => 'Payment sum',
            'pay_type' => 'Payment type',
            'pay_for' => 'Payment info',
            'license_type' => 'Pay for license_type',
            'license_count' => 'Pay for license_count',
            'user_id' => 'Owner of payment',
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
     * Invalidate Cache
     */
    protected function invalidateCache()
    {
        TagDependency::invalidate(Yii::$app->cache, [
            'UserPayment.pay_id.' . $this->pay_id,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->pay_date = date(SQL_DATE_FORMAT);
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->invalidateCache();
    }

    /**
     * @param int|string $id
     * @return UserPayments|null
     */
    public static function findIdentity($id)
    {
        return self::getDb()->cache(
            function($db) use($id) {
                return static::findOne(['pay_id' => $id]);
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'UserPayment.pay_id.' . $id])
        );
    }
}
