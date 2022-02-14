<?php
namespace frontend\models;

use common\models\Preferences;
use common\models\UserLicenses;
use Yii;
use yii\base\Model;
use yii\helpers\Json;
use yii\helpers\Url;
use common\helpers\Functions;
use common\models\UserPayments;
use common\models\Licenses;
use common\models\Users;

/**
 * NodeApi
 *
 * @property string user_password
 * @property \yii\redis\Connection $redis
 */
class CryptonatorApi extends Model
{
    //https://api.cryptonator.com/api/merchant/v1/startpayment?merchant_id=3043697132544b39ddbf71f65aeeed6b&item_name=test&invoice_amount=10&invoice_currency=usd
    //const urlApi = "https://api.cryptonator.com/api/merchant/v1";
    const urlApi = "https://pvtbox.net/other";

    public $error_data = null;
    public $dynamic_rules = null;

    private $secretKey;

    public $merchant_id;
    public $invoice_id;

    public $invoice_created;
    public $invoice_expires;
    public $date_time;

    public $invoice_amount;
    public $checkout_amount;

    public $invoice_currency;
    public $checkout_currency;

    public $invoice_url;
    public $checkout_address;

    public $invoice_status;

    public $item_name;
    public $item_description;
    public $order_id;

    public $secret_hash;

    public $raw_data;


    /**************************** +++ GLOBAL +++ ***************************/
    /**
     * CryptonatorApi constructor.
     * @param array $required_fields Поля которые будут проверяться на наличие в джсоне
     */
    public function __construct(array $required_fields = [])
    {
        /**TODO Этот параметр потом нужно вынести в настройки админки */
        $this->merchant_id = "3043697132544b39ddbf71f65aeeed6b";
        $this->secretKey   = "22b1f1688739b5b0a2231032ef3bfa16";

        if (is_array($required_fields) && sizeof($required_fields)) {
            $this->dynamic_rules = [[$required_fields, 'required', 'message' => 'Fields ' . implode(', ', $required_fields) . ' are required.']];
        }

        parent::__construct();
    }

    /**
     * Правила валидации данных
     * @return array
     */
    public function rules()
    {
        $rules = [
            [['merchant_id', 'invoice_id'], 'string', 'length' => 32],
            [['invoice_created', 'invoice_expires', 'date_time'], 'integer'],
            [['invoice_amount', 'checkout_amount'], 'number'],
            [['invoice_currency', 'checkout_currency'], 'string', 'max' => 10],
            [['checkout_address'], 'string', 'max' => 100],
            [['invoice_url'], 'string', 'max' => 255],
            [['invoice_status'], 'string', 'max' => 15],
            [['item_name', 'item_description'], 'string', 'max' => 255],
            [['order_id'], 'string', 'max' => 20],
            [['invoice_status'],'in', 'range' => [
                UserPayments::STATUS_UNPAID,
                UserPayments::STATUS_CONFIRMING,
                UserPayments::STATUS_PAID,
                UserPayments::STATUS_CANCELED,
                UserPayments::STATUS_MISPAID,
            ]],
            [['secret_hash'], 'string', 'length' => 40],
            [['raw_data'], 'string'],
        ];
        if (is_array($this->dynamic_rules)) {
            return array_merge($this->dynamic_rules, $rules);
        } else {
            return $rules;
        }
    }
    /**************************** --- GLOBAL --- ***************************/

    /**
     * @return string
     */
    public function startPayment()
    {
        //?merchant_id=3043697132544b39ddbf71f65aeeed6b&item_name=test&invoice_amount=10&invoice_currency=usd
        return self::urlApi . Url::to([
            '/startpayment',
            'merchant_id'      => $this->merchant_id,
            'item_name'        => $this->item_name,
            'invoice_amount'   => $this->invoice_amount,
            'invoice_currency' => $this->invoice_currency,
            'order_id'         => $this->order_id,
            'item_description' => $this->item_description,
            'language'         => Yii::$app->language,
            'success_url'      => Yii::$app->urlManager->createAbsoluteUrl(['purchase/return', 'status' => 'success']),
            'failed_url'       => Yii::$app->urlManager->createAbsoluteUrl(['purchase/return', 'status' => 'error']),
        ]);
    }

    public function generateHash()
    {
        //merchant_id&invoice_id&invoice_created&invoice_expires&invoice_amount&invoice_currency&invoice_status&invoice_url&
        //order_id&checkout_address&checkout_amount&checkout_currency&date_time&secret
        return sha1($this->merchant_id . '&' .
                    $this->invoice_id . '&' .
                    $this->invoice_created . '&' .
                    $this->invoice_expires . '&' .
                    $this->invoice_amount . '&' .
                    $this->invoice_currency . '&' .
                    $this->invoice_status . '&' .
                    $this->invoice_url . '&' .
                    $this->order_id . '&' .
                    $this->checkout_address . '&' .
                    $this->checkout_amount . '&' .
                    $this->checkout_currency . '&' .
                    $this->date_time . '&' .
                    $this->secretKey);
    }

    /**
     * @return array
     * @throws \yii\db\Exception
     */
    public function registerNotification()
    {
        $UserPayment = UserPayments::findIdentity($this->order_id);

        /* если такой платежки нет в базе */
        if (!$UserPayment) {
            return [
                'status' => false,
                'info'   => "UserPayment with pay_id(order_id) = {$this->order_id} not found.",
            ];
        }

        /* если статус платежа в базе уже равен ОТМЕНЕН */
        if ($UserPayment->pay_status == UserPayments::STATUS_CANCELED) {
            return [
                'status' => false,
                'info'   => "UserPayment with pay_id(order_id) = {$this->order_id} has status {" . UserPayments::STATUS_CANCELED . "}",
            ];
        }

        /* если статус платежа в базе уже равен ОПЛАЧЕН*/
        if ($UserPayment->pay_status == UserPayments::STATUS_PAID) {
            return [
                'status' => false,
                'info'   => "UserPayment with pay_id(order_id) = {$this->order_id} is already has status {" . UserPayments::STATUS_PAID . "}",
            ];
        }

        /* если суммы не совпадает */
        if ($this->invoice_amount < $UserPayment->pay_amount) {
            return [
                'status' => false,
                'info'   => 'invoice_sum from cryptonator is smaller than pay_sum',
            ];
        }

        /* если валюта не совпадает */
        if ($this->invoice_currency != $UserPayment->merchant_currency) {
            return [
                'status' => false,
                'info'   => 'invoice_currency from cryptonator is mismatch with invoice_currency in bd',
            ];
        }

        $User = Users::findIdentity($UserPayment->user_id);
        if (!$User) {
            return [
                'status' => false,
                'info'   => 'User for this pay_id not found',
            ];
        }

        /* обработка платежа */
        $transaction = Yii::$app->db->beginTransaction();

        $UserPayment->merchant_id       = UserPayments::MERCHANT_CRYPTONATOR;
        $UserPayment->merchant_unique_pay_id = $this->invoice_id;
        $UserPayment->merchant_amount   = $this->invoice_amount;
        $UserPayment->merchant_status   = $this->invoice_status;
        $UserPayment->merchant_raw_data = $this->raw_data;

        if ($UserPayment->save()) {

            /* если платеж со статусом = оплачен, то нужно провести все операции по изменению лицензий */
            if ($UserPayment->merchant_status == UserPayments::STATUS_PAID) {

                if ($UserPayment->pay_for == UserPayments::CODE_PAY_FOR_PROFESSIONAL) {

                    $lic_end = date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(time() + $UserPayment->license_period * 86400));
                    $User->license_type = Licenses::TYPE_PAYED_PROFESSIONAL;
                    $User->license_expire = $lic_end;
                    $User->license_period = $UserPayment->license_period;
                    $User->pay_type = $UserPayment->pay_type;

                } elseif (in_array($UserPayment->pay_for, [UserPayments::CODE_PAY_FOR_BUSINESS, UserPayments::CODE_PAY_FOR_BUSINESS_INCREASE, UserPayments::CODE_PAY_FOR_PRO_TO_BUSINESS])) {

                    $priceForPeriod = $UserPayment->license_period == Licenses::PERIOD_MONTHLY
                        ? Preferences::getValueByKey('PricePerMonthUserForLicenseBusiness', 99.99, 'float')
                        : Preferences::getValueByKey('PricePerYearUserForLicenseBusiness', 99.99, 'float') * 12;

                    /* Если первая покупка лицензий, то все просто - дата следующей оплаты через выбранный период (месяц или год) */
                    $lic_end_timestamp = time() + $UserPayment->license_period * 86400;

                    /* если это докупка лицензий */
                    if ($User->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {

                        $lic_end_timestamp = strtotime($User->license_expire);

                        $N = $UserPayment->license_count;
                        $P = $priceForPeriod;
                        $Pd = $priceForPeriod / $UserPayment->license_period;
                        $lic_info = UserLicenses::getLicenseCountInfoForUser($User->user_id);
                        $L = $lic_info['total'] + $UserPayment->license_count;
                        $D = ($lic_end_timestamp - time()) / 86400;

                        $delta = ceil($N * ($P - $Pd * $D) / $L / $Pd);
                        //var_dump($delta); exit;
                        $lic_end_timestamp = $lic_end_timestamp + $delta * 86400;
                    }

                    $lic_start = date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(time()));
                    $lic_end = date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp($lic_end_timestamp));

                    $lic_lastpay = time();
                    $lic_group_id = time();
                    $test = UserLicenses::findOne([
                        'lic_colleague_user_id' => $User->user_id,
                        'lic_owner_user_id' => $User->user_id,
                    ]);
                    for ($i = 1; $i <= $UserPayment->license_count; $i++) {
                        $lic = new UserLicenses();
                        $lic->lic_start = $lic_start;
                        $lic->lic_end = $lic_end;
                        $lic->lic_period = $UserPayment->license_period;
                        $lic->lic_owner_user_id = $User->user_id;
                        $lic->lic_colleague_user_id = (!$test && $i == 1) ? $User->user_id : null;
                        $lic->lic_colleague_email = (!$test && $i == 1) ? $User->user_email : null;
                        $lic->lic_lastpay_timestamp = $lic_lastpay;
                        $lic->lic_group_id = $lic_group_id;
                        $lic->save();
                    }

                    /* обновим даты у всех лицензий юзера, а не только у тех которые только что он докупил (если это докупка) */
                    UserLicenses::updateAll(['lic_end' => $lic_end], ['lic_owner_user_id' => $User->user_id]);

                    $User->license_type = Licenses::TYPE_PAYED_BUSINESS_ADMIN;
                    $User->license_expire = $lic_end;
                    $User->license_count_available += $UserPayment->license_count;
                    $User->license_period = $UserPayment->license_period;
                    $User->pay_type = $UserPayment->pay_type;

                } elseif ($UserPayment->pay_for == UserPayments::CODE_PAY_FOR_RENEWAL) {

                    $new_expire = strtotime($User->license_expire) + $User->license_period * 86400;
                    $lic_end = date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp($new_expire));

                    $User->license_expire = $lic_end;
                    UserLicenses::updateAll(['lic_end' => $lic_end], ['lic_owner_user_id' => $User->user_id]);

                }

                $User->payment_already_initialized = Users::PAYMENT_NOT_INITIALIZED;
                $User->payment_init_date = null;

            /* если же платеж еще не получил статус оплачен, то нужно проверить какой статус и в зависимости от статуса выполнить действия */
            } else {

                if (in_array($UserPayment->merchant_status, [UserPayments::STATUS_CANCELED])) {

                    $User->payment_already_initialized = Users::PAYMENT_NOT_INITIALIZED;
                    $User->payment_init_date = null;

                } else {

                    $User->payment_already_initialized = Users::PAYMENT_INITIALIZED;
                    $User->payment_init_date = date(SQL_DATE_FORMAT, time());

                }

            }

            if ($User->save()) {
                $transaction->commit();
                return [
                    'status' => true,
                ];
            } else {
                $transaction->rollBack();
                return [
                    'status' => false,
                    'info'   => $User->getErrors(),
                ];
            }
        } else {
            $transaction->rollBack();
            return [
                'status' => false,
                'info'   => $UserPayment->getErrors(),
            ];
        }
    }
}
