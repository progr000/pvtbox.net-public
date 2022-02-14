<?php
namespace frontend\models\forms;

use Yii;
use yii\base\Model;
use common\helpers\Functions;
use common\models\Licenses;
use common\models\UserLicenses;
use common\models\Users;
use common\models\Preferences;
use common\models\UserPayments;
use frontend\models\CryptonatorApi;
use yii\helpers\Json;

/**
 * Signup form
 */
class PurchaseForm extends Model
{
    /**/
    const MIN_LICENSE_COUNT = 3;
    const MAX_LICENSE_COUNT = 50;

    /**/
    const ID_PROFESSIONAL = "professional";
    const ID_BUSINESS     = "business";
    const ID_RENEWAL      = "renewal";
    const ID_SUCCESS      = "success";
    const ID_RETURN       = "return";
    const ID_CANCEL       = "cancel";
    const ID_SUMMARY      = "summary";
    const ID_INITIALIZED  = "already-initialized";

    /**/
    const LICENSE_ID_PROFESSIONAL = "professional";
    const LICENSE_ID_BUSINESS     = "business";

    /**/
    const ERROR_SUCCESS         = 'SUCCESS';
    const ERROR_BUSINESS_USER   = 'BUSINESS_USER';
    const ERROR_ALREADY_PRO     = 'ALREADY_PRO';
    const ERROR_CANT_DOWNGRADE  = 'CANT_DOWNGRADE';
    const ERROR_PERIOD_MISMATCH = 'PERIOD_MISMATCH';

    /**/
    public $admin_full_name, $os2;
    public $user_company_name, $os1;
    public $license_type;
    public $license_count;
    public $license_period;
    public $id;
    public $billed;
    public $pay_type;

    /**/
    public $_required = [];

    /** @var \common\models\Users $User */
    public $User;
    public $error_code;

    /**
     * PurchaseForm constructor.
     * @param array $required
     * @param array $config
     */
    public function __construct(array $required=array(), array $config=array())
    {
        parent::__construct($config);
        if ($required && sizeof($required)) {
            $this->_required = [$required, 'required'];
        }

        $this->User = Yii::$app->user->identity;
    }

    /**
     *
     */
    public function initValuesForForm()
    {
        /**/
        if ($this->id == self::ID_PROFESSIONAL) {
            $this->license_type = Licenses::TYPE_PAYED_PROFESSIONAL;
            $this->license_count = 1;
        } else {
            $this->license_type = Licenses::TYPE_PAYED_BUSINESS_ADMIN;
            $this->license_count = self::MIN_LICENSE_COUNT;
        }

        /**/
        if ($this->billed == Licenses::getBilledByPeriod(Licenses::PERIOD_MONTHLY)) {
            $this->license_period = Licenses::PERIOD_MONTHLY;
        } else {
            $this->license_period = Licenses::PERIOD_ANNUALLY;
        }

        /**/
        $this->pay_type = Users::PAY_CARD;

        /**/
        $this->admin_full_name = $this->User->admin_full_name;
        $this->user_company_name = $this->User->user_company_name;
        $this->error_code = self::ERROR_SUCCESS;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['license_type', 'license_period', /*'admin_full_name', 'user_company_name',*/ 'license_count', 'pay_type'], 'required'],
            [['license_type'], 'in', 'range' => [Licenses::TYPE_PAYED_PROFESSIONAL, Licenses::TYPE_PAYED_BUSINESS_ADMIN]],
            [['admin_full_name', 'user_company_name', 'os1', 'os2'], 'filter', 'filter' => 'trim'],
            [['admin_full_name', 'user_company_name', 'os1', 'os2'], 'string', 'min' => 2, 'max' => 50],
            [['license_period'], 'integer'],
            [['license_period'], 'in', 'range' => [Licenses::PERIOD_MONTHLY, Licenses::PERIOD_ANNUALLY]],
            //[['license_count'], 'integer', 'min' =>self::MIN_LICENSE_COUNT, 'max' => self::MAX_LICENSE_COUNT],
            [['license_count'], 'integer'],
            [['license_count'], 'checkLicenseCount'],
            [['id'], 'in', 'range' => [self::ID_SUCCESS, self::ID_BUSINESS, self::ID_PROFESSIONAL, self::ID_RENEWAL, self::ID_RETURN]],
            [['billed'], 'in', 'range' => [Licenses::getBilledByPeriod(Licenses::PERIOD_MONTHLY), Licenses::getBilledByPeriod(Licenses::PERIOD_ANNUALLY)]],
            [['pay_type'], 'in', 'range' => [Users::PAY_CARD, Users::PAY_CRYPTO]],
            [['pay_type'], 'default', 'value' => Users::PAY_CARD],
        ];

        if (sizeof($this->_required)) {
            $rules[] = $this->_required;
        }

        return $rules;
    }

    public function checkLicenseCount($attribute, $params)
    {
        if ($this->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN && ($this->$attribute < self::MIN_LICENSE_COUNT || $this->$attribute > self::MAX_LICENSE_COUNT)) {
            $this->addError($attribute, 'Wrong license count');
        } elseif ($this->license_type == Licenses::TYPE_PAYED_PROFESSIONAL && $this->$attribute != 1) {
            $this->addError($attribute, 'Wrong license count');
        }
    }

    /**
     * attributes for input fields.
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'user_company_name' => Yii::t('forms/purchase-form', 'Company_name'),
            'admin_full_name'   => Yii::t('forms/purchase-form', 'Administrator_full_name'),
            'os1' => Yii::t('forms/purchase-form', 'Company_name'),
            'os2' => Yii::t('forms/purchase-form', 'Administrator_full_name'),
            'license_count'     => Yii::t('forms/purchase-form', 'license_count'),
        ];
    }

    public function checkAllowPeriod()
    {
        if (!in_array($this->User->license_period, [Licenses::PERIOD_MONTHLY, Licenses::PERIOD_ANNUALLY])) {
            return true;
        }
        if ($this->license_period == $this->User->license_period) {
            return true;
        }
        $this->error_code = self::ERROR_PERIOD_MISMATCH;
        return false;
    }

    /**
     * @return bool
     */
    public function checkAllowProfessional()
    {
        if (in_array($this->User->license_type, [Licenses::TYPE_FREE_TRIAL, Licenses::TYPE_FREE_DEFAULT])) {
            $this->error_code = self::ERROR_SUCCESS;
            return true;
        } else {
            $this->error_code = self::ERROR_CANT_DOWNGRADE;
            if ($this->User->license_type == Licenses::TYPE_PAYED_PROFESSIONAL) {
                $this->error_code = self::ERROR_ALREADY_PRO;
            }
            if ($this->User->license_type == Licenses::TYPE_PAYED_BUSINESS_USER) {
                $this->error_code = self::ERROR_BUSINESS_USER;
            }
            return false;
        }
    }

    /**
     * @return bool
     */
    public function purchaseProfessional()
    {
        /* Если какой либо платеж был ранее инициирован и не завершен еще, нельзя провести следующий */
        if ($this->User->checkPaymentInitialized()) {
            return [
                'status' => false,
                'url'    => false,
                'info'   => 'Payment already initialized. Wait until it finished',
            ];
        }

        /**/
        if (in_array($this->User->license_period, [0, $this->license_period])) {

            $transaction = Yii::$app->db->beginTransaction();

            //TO-DO эту 1 строку ТАК ЖЕ нужно будет ДОБАВИТЬ в скрипт обработчик ответов криптонатора
            $lic_end = date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(time() + $this->license_period * 86400));

            //TO-DO эти 2 строки нужно будет перенести в скрипт обработчик ответов криптонатора
            //--++$this->User->license_type = Licenses::TYPE_PAYED_PROFESSIONAL;
            //--++$this->User->license_expire = $lic_end;

            $this->User->license_period = $this->license_period;
            $this->User->pay_type = $this->pay_type;
            $this->User->payment_already_initialized = Users::PAYMENT_INITIALIZED;
            $this->User->payment_init_date = date(SQL_DATE_FORMAT, time());
            $this->User->expired_notif_sent = Users::EXPIRED_NOTIF_NOT_SENT;

            if ($this->User->save()) {

                $priceForPeriod = ($this->license_period == Licenses::PERIOD_MONTHLY)
                    ? Preferences::getValueByKey('PricePerMonthForLicenseProfessional', 99.99, 'float')
                    : Preferences::getValueByKey('PricePerYearForLicenseProfessional', 99.99, 'float') * 12;
                $paymentSum = number_format($priceForPeriod, 2, '.', '');

                $UserPayment = new UserPayments();
                $UserPayment->license_count    = 1;
                $UserPayment->license_type     = Licenses::TYPE_PAYED_PROFESSIONAL;
                $UserPayment->license_expire   = $lic_end;
                $UserPayment->license_period   = $this->license_period;
                $UserPayment->pay_for          = UserPayments::CODE_PAY_FOR_PROFESSIONAL;
                $UserPayment->pay_amount       = $paymentSum;
                $UserPayment->pay_currency     = UserPayments::CURRENCY_USD;
                $UserPayment->pay_type         = $this->pay_type;
                $UserPayment->user_id          = $this->User->user_id;
                $UserPayment->merchant_status   = UserPayments::STATUS_UNPAID;
                $UserPayment->merchant_currency = UserPayments::CURRENCY_USD;

                if ($UserPayment->save()) {

                    if ($this->pay_type == Users::PAY_CRYPTO) {

                        /** ++Оплата через Криптонатор */

                        $cryptonatorApi = new CryptonatorApi(['item_name', 'invoice_amount', 'invoice_currency', 'order_id']);
                        $cryptonatorApi->load(['CryptonatorApi' => [
                            'item_name' => Yii::t('user/billing', 'pay_for_' . UserPayments::CODE_PAY_FOR_PROFESSIONAL),
                            'invoice_amount' => $UserPayment->pay_sum,
                            'invoice_currency' => $UserPayment->invoice_currency,
                            //'item_description' => '',
                            'order_id' => (string)$UserPayment->pay_id,
                        ]]);
                        if ($cryptonatorApi->validate()) {

                            $transaction->commit();

                            $url = $cryptonatorApi->startPayment();

                            $messageLog = [
                                'status' => 'Платеж успешно создан.',
                                'url' => $url,
                            ];
                            Yii::info($messageLog, 'payment_created'); //запись в лог

                            return [
                                'status' => true,
                                'url'    => $url,
                                'info'   => 'Ok',
                            ];
                            //var_dump($url);
                            //exit;
                        } else {
                            $transaction->rollBack();
                            return [
                                'status' => false,
                                'url'    => false,
                                'info'   => Json::encode($cryptonatorApi->getErrors()),
                            ];
                        }
                        /** --Оплата через Криптонатор */

                    } else {

                        /** ++Оплата через карту */
                        /** --Оплата через карту */
                        $transaction->rollBack();
                        return [
                            'status' => false,
                            'url'    => false,
                            'info'   => 'Pay by card is not ready',
                        ];

                    }
                } else {
                    $transaction->rollBack();
                    return [
                        'status' => false,
                        'url'    => false,
                        'info'   => Json::encode($UserPayment->getErrors()),
                    ];
                }
            } else {
                $transaction->rollBack();
                return [
                    'status' => false,
                    'url'    => false,
                    'info'   => Json::encode($this->User->getErrors()),
                ];
            }
        }

        return [
            'status' => false,
            'url'    => false,
            'info'   => 'License period error',
        ];
    }

    /**
     * @return bool
     */
    public function checkAllowBusiness()
    {
        if (!in_array($this->User->license_type, [Licenses::TYPE_PAYED_BUSINESS_USER])) {
            $this->error_code = self::ERROR_SUCCESS;
            return true;
        } else {
            $this->error_code = self::ERROR_BUSINESS_USER;
            return false;
        }
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function purchaseBusiness()
    {
        /* Если какой либо платеж был ранее инициирован и не завершен еще, нельзя провести следующий */
        if ($this->User->checkPaymentInitialized()) {
            return [
                'status' => false,
                'url'    => false,
                'info'   => 'Payment already initialized. Wait until it finished',
            ];
        }

        /**/
        if (in_array($this->User->license_period, [0, $this->license_period])) {
            $transaction = Yii::$app->db->beginTransaction();

            /*
            Докупка Business User лицензий

            На странице Admin Panel -> Billing отображается количество использованных и доступных лицензий. Так же там присутствует кнопка Add more позволяющая докупить лицензий.

            При докупке лицензии оплачивается полная стоимость этой лицензии, при этом сдвигается дата следующей оплаты за все лицензии.
            Сдвиг даты высчитывается по формуле:
            N * (P - Pd * D) / L / Pd, где:
            N - количество приобретаемых лицензий,
            P - цена одной приобретаемой лицензии,
            Pd - цена одной приобретаемой лицензии за один день,
            D - разница в днях между датой следующей оплаты за лицензии (тех что были ранее куплены) и текущей датой,
            L - число лицензий после приобретения новых.

            Полученный результат округляется в большую сторону до целого числа дней и прибавляется к дате следующей оплаты за лицензии (тех что были куплены ранее)
            */

            /* Если первая покупка лицензий, то все просто - дата следующей оплаты через выбранный период (месяц или год) */
            //TO-DO весь этот расчет ТАК ЖЕ нужно будет ДОБАВИТЬ в скрипт обработчик ответов криптонатора
            $priceForPeriod = $this->license_period == Licenses::PERIOD_MONTHLY
                ? Preferences::getValueByKey('PricePerMonthUserForLicenseBusiness', 99.99, 'float')
                : Preferences::getValueByKey('PricePerYearUserForLicenseBusiness', 99.99, 'float') * 12;
            $lic_end_timestamp = time() + $this->license_period * 86400;
            $pay_for = UserPayments::CODE_PAY_FOR_BUSINESS;

            /* если это докупка лицензий */
            if ($this->User->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {

                $pay_for = UserPayments::CODE_PAY_FOR_BUSINESS_INCREASE;
                $lic_end_timestamp = strtotime($this->User->license_expire);

                $N = $this->license_count;
                $P = $priceForPeriod;
                $Pd = $priceForPeriod / $this->license_period;
                $lic_info = UserLicenses::getLicenseCountInfoForUser($this->User->getId());
                $L = $lic_info['total'] + $this->license_count;
                $D = ($lic_end_timestamp - time()) / 86400;

                $delta = ceil($N * ($P - $Pd * $D) / $L / $Pd);
                //var_dump($delta); exit;
                $lic_end_timestamp = $lic_end_timestamp + $delta * 86400;
            }

            $lic_start = date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(time()));
            $lic_end   = date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp($lic_end_timestamp));
            //var_dump($lic_end); exit;

            //TO-DO весь этот цикл создания записей о лицензиях нужно будет ПЕРЕНЕСТИ в скрипт обработчик ответов криптонатора
            /*--++
            $lic_lastpay = time();
            $lic_group_id = time();
            //$this->license_count;
            $test = UserLicenses::findOne([
                'lic_colleague_user_id' => $this->User->getId(),
                'lic_owner_user_id' => $this->User->getId(),
            ]);
            //var_dump($test); exit;
            for ($i = 1; $i <= $this->license_count; $i++) {
                $lic = new UserLicenses();
                $lic->lic_start = $lic_start;
                $lic->lic_end = $lic_end;
                $lic->lic_period = $this->license_period;
                $lic->lic_owner_user_id = $this->User->getId();
                $lic->lic_colleague_user_id = (!$test && $i == 1) ? $this->User->getId() : null;
                $lic->lic_colleague_email = (!$test && $i == 1) ? $this->User->user_email : null;
                $lic->lic_lastpay_timestamp = $lic_lastpay;
                $lic->lic_group_id = $lic_group_id;
                $lic->save();
            }
            ++--*/

            //TO-DO эту строку так же нужно ПЕРЕНЕСТИ в скрипт обработчик ответов криптонатора
            /* обновим даты у всех лицензий юзера, а не только у тех которые только что он докупил (если это докупка) */
            //--++UserLicenses::updateAll(['lic_end' => $lic_end], ['lic_owner_user_id' => $this->User->getId()]);

            /* Если это переход с про на бизнес */
            $DELTA_SUM_FROM_PRO = 0.00;
            if ($this->User->license_type == Licenses::TYPE_PAYED_PROFESSIONAL) {
                $pay_for = UserPayments::CODE_PAY_FOR_PRO_TO_BUSINESS;
                $DELTA_SUM_FROM_PRO = $this->license_period == Licenses::PERIOD_MONTHLY
                    ? Preferences::getValueByKey('PricePerMonthForLicenseProfessional', 99.99, 'float')
                    : Preferences::getValueByKey('PricePerYearForLicenseProfessional', 99.99, 'float') * 12;
            }

            //TO-DO эти две строки перенести в скрипт обработчик ответа криптонатора
            //--++$this->User->license_type = Licenses::TYPE_PAYED_BUSINESS_ADMIN;
            //--++$this->User->license_expire = $lic_end;
            //--++$this->User->license_count_available += $this->license_count;

            $this->User->user_company_name = $this->user_company_name;
            $this->User->admin_full_name = $this->admin_full_name;
            $this->User->license_period = $this->license_period;
            $this->User->pay_type = $this->pay_type;
            $this->User->payment_already_initialized = Users::PAYMENT_INITIALIZED;
            $this->User->payment_init_date = date(SQL_DATE_FORMAT, time());
            $this->User->expired_notif_sent = Users::EXPIRED_NOTIF_NOT_SENT;

            if ($this->User->save()) {

                $paymentSum = number_format($priceForPeriod * $this->license_count - $DELTA_SUM_FROM_PRO, 2, '.', '');

                //var_dump($paymentSum); exit;
                $UserPayment = new UserPayments();
                $UserPayment->license_count    = $this->license_count;
                $UserPayment->license_type     = Licenses::TYPE_PAYED_BUSINESS_ADMIN;
                $UserPayment->license_period   = $this->license_period;
                $UserPayment->license_expire   = $lic_end;
                $UserPayment->pay_for          = $pay_for;
                $UserPayment->pay_amount       = $paymentSum;
                $UserPayment->pay_currency     = UserPayments::CURRENCY_USD;
                $UserPayment->pay_type         = $this->pay_type;
                $UserPayment->user_id          = $this->User->user_id;
                $UserPayment->merchant_status  = UserPayments::STATUS_UNPAID;
                $UserPayment->merchant_currency = UserPayments::CURRENCY_USD;

                if ($UserPayment->save()) {

                    if ($this->pay_type == Users::PAY_CRYPTO) {

                        /** ++Оплата через Криптонатор */
                        $cryptonatorApi = new CryptonatorApi(['item_name', 'invoice_amount', 'invoice_currency', 'order_id']);
                        $cryptonatorApi->load(['CryptonatorApi' => [
                            'item_name' => Yii::t('user/billing', 'pay_for_' . $pay_for),
                            'invoice_amount' => $UserPayment->pay_sum,
                            'invoice_currency' => $UserPayment->invoice_currency,
                            //'item_description' => '',
                            'order_id' => (string)$UserPayment->pay_id,
                        ]]);
                        if ($cryptonatorApi->validate()) {

                            $transaction->commit();

                            $url = $cryptonatorApi->startPayment();

                            $messageLog = [
                                'status' => 'Платеж успешно создан.',
                                'url' => $url,
                            ];
                            Yii::info($messageLog, 'payment_created'); //запись в лог

                            return [
                                'status' => true,
                                'url'    => $url,
                                'info'   => 'Ok',
                            ];
                            //var_dump($url);
                            //exit;
                        } else {
                            $transaction->rollBack();
                            return [
                                'status' => false,
                                'url'    => false,
                                'info'   => Json::encode($cryptonatorApi->getErrors()),
                            ];
                        }
                        /** --Оплата через Криптонатор */

                    } else {

                        /** ++Оплата через карту */
                        /** --Оплата через карту */
                        $transaction->rollBack();
                        return [
                            'status' => false,
                            'url'    => false,
                            'info'   => 'Pay by card is not ready',
                        ];

                    }

                } else {
                    $transaction->rollBack();
                    return [
                        'status' => false,
                        'url'    => false,
                        'info'   => Json::encode($UserPayment->getErrors()),
                    ];
                }
            } else {
                $transaction->rollBack();
                return [
                    'status' => false,
                    'url'    => false,
                    'info'   => Json::encode($this->User->getErrors()),
                ];
            }
        }

        return [
            'status' => false,
            'url'    => false,
            'info'   => 'License period error',
        ];
    }

    /**
     * @return bool
     */
    public function renewal()
    {
        /* Если какой либо платеж был ранее инициирован и не завершен еще, нельзя провести следующий */
        if ($this->User->checkPaymentInitialized()) {
            return [
                'status' => false,
                'url'    => false,
                'info'   => 'Payment already initialized. Wait until it finished',
            ];
        }


        if (Users::isAutoPayType($this->User->pay_type)) {
            return [
                'status' => false,
                'url'    => false,
                'info'   => 'Your payment type assumes an automatic charge',
            ];
        }

        /**/
        /*
        var_dump(Licenses::checkIsExpireSoon($this->User->license_expire));
        var_dump(Licenses::checkIsExpired($this->User->license_expire));
        var_dump((Licenses::checkIsExpireSoon($this->User->license_expire) || Licenses::checkIsExpired($this->User->license_expire)));
        exit;
        */
        if (Licenses::checkIsExpireSoon($this->User->license_expire) || Licenses::checkIsExpired($this->User->license_expire)) {

            if ($this->User->license_type == Licenses::TYPE_PAYED_PROFESSIONAL) {
                $license_count = 1;
                $priceForPeriod = $this->User->license_period == Licenses::PERIOD_MONTHLY
                    ? Preferences::getValueByKey('PricePerMonthForLicenseProfessional', 99.99, 'float')
                    : Preferences::getValueByKey('PricePerYearForLicenseProfessional', 99.99, 'float') * 12;
            } else {
                $license_info = UserLicenses::getLicenseCountInfoForUser($this->User->user_id);
                $license_count = $license_info['total'];
                $priceForPeriod = $this->User->license_period == Licenses::PERIOD_MONTHLY
                    ? Preferences::getValueByKey('PricePerMonthUserForLicenseBusiness', 99.99, 'float')
                    : Preferences::getValueByKey('PricePerYearUserForLicenseBusiness', 99.99, 'float') * 12;
            }


            $transaction = Yii::$app->db->beginTransaction();

            //TO-DO эти 2 строки ТАК ЖЕ нужно будет ДОБАВИТЬ в скрипт обработчик ответов криптонатора
            $new_expire = strtotime($this->User->license_expire) + $this->User->license_period * 86400;
            $lic_end = date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp($new_expire));

            $paymentSum = number_format($priceForPeriod * $license_count, 2, '.', '');
            //var_dump($paymentSum); exit;
            $UserPayment = new UserPayments();
            $UserPayment->license_count    = $license_count;
            $UserPayment->license_type     = $this->User->license_type;
            $UserPayment->license_period   = $this->User->license_period;
            $UserPayment->license_expire   = $lic_end;
            $UserPayment->pay_for          = UserPayments::CODE_PAY_FOR_RENEWAL;
            $UserPayment->pay_amount       = $paymentSum;
            $UserPayment->pay_currency     = UserPayments::CURRENCY_USD;
            $UserPayment->pay_type         = $this->User->pay_type;
            $UserPayment->user_id          = $this->User->user_id;
            $UserPayment->merchant_status  = UserPayments::STATUS_UNPAID;
            $UserPayment->merchant_currency = UserPayments::CURRENCY_USD;

            if ($UserPayment->save()) {

                if ($this->user_company_name) {
                    $this->User->user_company_name = $this->user_company_name;
                }
                if ($this->admin_full_name) {
                    $this->User->admin_full_name = $this->admin_full_name;
                }
                $this->User->payment_already_initialized = Users::PAYMENT_INITIALIZED;
                $this->User->payment_init_date = date(SQL_DATE_FORMAT, time());
                $this->User->expired_notif_sent = Users::EXPIRED_NOTIF_NOT_SENT;

                //TO-DO эту 1 строку нужно будет перенести в скрипт обработчик ответов криптонатора
                //--++$this->User->license_expire = $lic_end;

                if ($this->User->save()) {

                    //TO-DO эту 1 строку нужно будет перенести в скрипт обработчик ответов криптонатора
                    //--++UserLicenses::updateAll(['lic_end' => $this->User->license_expire], ['lic_owner_user_id' => $this->User->user_id]);

                    if ($this->User->pay_type == Users::PAY_CRYPTO) {

                        /** ++Оплата через Криптонатор */
                        $payment_params = [
                            'item_name' => Yii::t('user/billing', 'pay_for_' . UserPayments::CODE_PAY_FOR_RENEWAL),
                            'invoice_amount' => $UserPayment->pay_sum,
                            'invoice_currency' => $UserPayment->invoice_currency,
                            //'item_description' => '',
                            'order_id' => (string)$UserPayment->pay_id,
                        ];
                        $cryptonatorApi = new CryptonatorApi(['item_name', 'invoice_amount', 'invoice_currency', 'order_id']);
                        $cryptonatorApi->load(['CryptonatorApi' => $payment_params]);
                        if ($cryptonatorApi->validate()) {

                            $transaction->commit();

                            $url = $cryptonatorApi->startPayment();

                            /* ++Запись в лог данных о платеже */
                            $messageLog = [
                                'status' => 'Платеж успешно создан.',
                                'url' => $url,
                                'payment_params' => $payment_params,
                            ];
                            Yii::info($messageLog, 'payment_created');
                            /* -- */

                            return [
                                'status' => true,
                                'url'    => $url,
                                'info'   => 'Ok',
                            ];
                        } else {
                            $transaction->rollBack();
                            return [
                                'status' => false,
                                'url'    => false,
                                'info'   => Json::encode($cryptonatorApi->getErrors()),
                            ];
                        }
                        /** --Оплата через Криптонатор */

                    } else {

                        /** ++Оплата через карту */
                        /** --Оплата через карту */
                        $transaction->rollBack();
                        return [
                            'status' => false,
                            'url'    => false,
                            'info'   => 'Pay by card is not ready',
                        ];

                    }

                } else {
                    $transaction->rollBack();
                    return [
                        'status' => false,
                        'url'    => false,
                        'info'   => Json::encode($this->User->getErrors()),
                    ];
                }
            } else {
                $transaction->rollBack();
                return [
                    'status' => false,
                    'url'    => false,
                    'info'   => Json::encode($UserPayment->getErrors()),
                ];
            }

        }
        return [
            'status' => false,
            'url'    => false,
            'info'   => 'License is not expire',
        ];
    }
}
