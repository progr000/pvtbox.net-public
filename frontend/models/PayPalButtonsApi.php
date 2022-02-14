<?php
namespace frontend\models;

use common\models\UserServerLicenses;
use Yii;
use yii\base\Model;
use yii\helpers\Url;
use common\helpers\Functions;
use common\models\UserPayments;
use common\models\Licenses;
use common\models\Users;
use common\models\Preferences;
use common\models\UserColleagues;
use common\models\UserLicenses;
use frontend\models\forms\ShareElementForm;

/**
 * NodeApi
 *
 * @property \common\models\Users $User
 */
class PayPalButtonsApi extends Model
{
    const TYPE_SIGNUP    = 'subscr_signup';
    const TYPE_PAYMENT   = 'subscr_payment';
    const TYPE_CANCEL    = 'subscr_cancel';
    const TYPE_FAILED    = 'subscr_failed';
    const TYPE_EOT       = 'subscr_eot';
    const TYPE_SUSPENDED = 'recurring_payment_suspended_due_to_max_failed_payment';
    const TYPE_WEBACCEPT = 'web_accept';

    const ITEM_PRO_ONETIME    = 'professional_peer_onetime';
    const ITEM_PRO_DAY        = 'professional_peer_day';
    const ITEM_PRO_MONTH      = 'professional_peer_month';
    const ITEM_PRO_YEAR       = 'professional_peer_year';
    const ITEM_BUSINESS_DAY   = 'business_peer_day';
    const ITEM_BUSINESS_MONTH = 'business_peer_month';
    const ITEM_BUSINESS_YEAR  = 'business_peer_year';

    public $txn_id, $txn_type, $subscr_id, $payer_id, $item_number, $ipn_track_id;
    public $mc_currency;
    public $verify_sign;
    public $item_name;
    public $mc_fee, $payment_fee, $payment_gross, $amount3;
    public $option_selection1, $option_selection2, $option_selection3;

    public $license_count, $server_license_count;

    public $raw_data;

    protected $User;
    /**************************** +++ VALIDATION RULES +++ ***************************/

    /**
     * Правила валидации данных
     * @return array
     */
    public function rules()
    {
        return [
            [['item_name', 'verify_sign', 'mc_currency', 'subscr_id', 'payer_id', 'ipn_track_id'], 'required'],
            [['txn_id', 'subscr_id', 'payer_id', 'item_number', 'ipn_track_id'], 'string', 'max' => 50],
            [['mc_currency'], 'string', 'max' => 5],
            [['verify_sign'], 'string', 'max' => 255],
            [['item_name'], 'integer'],

            [['txn_type'], 'string', 'max' => 255],
            [['txn_type'], 'in', 'range' => [
                self::TYPE_SIGNUP,
                self::TYPE_PAYMENT,
                self::TYPE_CANCEL,
                self::TYPE_FAILED,
                self::TYPE_EOT,
                self::TYPE_SUSPENDED,
                self::TYPE_WEBACCEPT,
            ]],
            [['txn_type'], 'validatorForTxnType', 'skipOnError' => false],

            [['item_number'], 'in', 'range' => [
                self::ITEM_BUSINESS_DAY,
                self::ITEM_BUSINESS_MONTH,
                self::ITEM_BUSINESS_YEAR,
                self::ITEM_PRO_DAY,
                self::ITEM_PRO_MONTH,
                self::ITEM_PRO_YEAR,
                self::ITEM_PRO_ONETIME,
            ]],
            [['item_number'], 'validatorForItemNumber', 'skipOnError' => false],

            [['item_name'], 'validatorForItemName', 'skipOnError' => false],

            [['mc_fee', 'payment_fee', 'payment_gross', 'amount3'], 'number'],

            //[['option_selection1'], 'integer'],
            //[['option_selection1'], 'in', 'range' => [3, 5, 10, 15, 20, 25, 30]],
            [['option_selection1'], 'string'],
            [['option_selection1'],
                'match',
                'pattern' => '/^[0-9]{1}\-[0-9]{1,2}$/u',
                'message' => 'option_selection1 wrong format'
            ],

            [['option_selection2', 'option_selection3'], 'string'],
            [['option_selection2', 'option_selection3'], 'filter', 'filter' => function ($value) {
                $value = mb_substr($value, 0, 50);
                return $value;
            }],

            [['raw_data'], 'string'],
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validatorForTxnType($attribute, $params)
    {
        if (!in_array($this->txn_type, [self::TYPE_PAYMENT, self::TYPE_SIGNUP])) {
            $this->option_selection1 = null;
            $this->option_selection2 = null;
            $this->option_selection3 = null;
        }
        if (in_array($this->txn_type, [self::TYPE_PAYMENT, self::TYPE_WEBACCEPT]) &&
            !isset($this->mc_fee, $this->payment_fee, $this->payment_gross, $this->txn_id, $this->item_number)) {
            $error_txt = "Can't be empty, when txn_type=" . $this->txn_type;
            $this->addError('mc_fee', $error_txt);
            $this->addError('payment_fee', $error_txt);
            $this->addError('payment_gross', $error_txt);
            $this->addError('txn_id', $error_txt);
            $this->addError('item_number', $error_txt);
        }

        if (in_array($this->txn_type, [self::TYPE_CANCEL, self::TYPE_SIGNUP]) && !isset($this->amount3, $this->item_number)) {
            $error_txt = "Can't be empty, when txn_type=" . $this->txn_type;
            $this->addError('amount3', $error_txt);
            $this->addError('item_number', $error_txt);
        }
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validatorForItemNumber($attribute, $params)
    {
        if (in_array($this->item_number, [self::ITEM_BUSINESS_DAY, self::ITEM_BUSINESS_YEAR, self::ITEM_BUSINESS_MONTH]) &&
            in_array($this->txn_type, [self::TYPE_PAYMENT, self::TYPE_SIGNUP]) &&
            !isset($this->option_selection1, $this->option_selection2, $this->option_selection3)) {
            $error_txt = "Can't be empty, when item_number IN [" . self::ITEM_BUSINESS_DAY . ", " . self::ITEM_BUSINESS_MONTH . ", " . self::ITEM_BUSINESS_YEAR . "]";
            $this->addError('option_selection1', $error_txt);
            $this->addError('option_selection2', $error_txt);
            $this->addError('option_selection3', $error_txt);
        }
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validatorForItemName($attribute, $params)
    {
        $this->User = Users::findIdentity($this->item_name);
        if (!$this->User) {
            $this->addError('item_name', "User with user_id={$this->item_name} not found");
        }
    }
    /**************************** --- VALIDATION RULES --- ***************************/

    /**
     * @return array
     */
    public function processPayment()
    {
        if (!$this->User) {
            return [
                'status' => false,
                'info'   => 'Validation need. User need.',
            ];
        }

        if (UserPayments::findOne([
            'merchant_id' => $this->subscr_id,
            'merchant_unique_pay_id' => $this->txn_id,
        ])) {
            return [
                'status' => false,
                'info'   => "ERROR Transaction with {$this->subscr_id} + {$this->txn_id} already registered.",
            ];
        }

        if (in_array($this->txn_type, [self::TYPE_PAYMENT, self::TYPE_SIGNUP, self::TYPE_WEBACCEPT])) {
            if (in_array($this->item_number, [self::ITEM_BUSINESS_YEAR, self::ITEM_BUSINESS_MONTH, self::ITEM_BUSINESS_DAY])) {
                $ret = $this->processPaymentForBusiness();
            } elseif (in_array($this->item_number, [self::ITEM_PRO_YEAR, self::ITEM_PRO_MONTH, self::ITEM_PRO_DAY])) {
                $ret = $this->processPaymentForProfessional();
            } elseif (in_array($this->item_number, [self::ITEM_PRO_ONETIME])) {
                $ret = $this->processPaymentForProfessionalOneTime();
            }
        } else {
            $ret = $this->cancelPayment();
        }

        return $ret;
    }

    /**
     * @return array
     * @throws \yii\db\Exception
     */
    protected function cancelPayment()
    {
        $transaction = Yii::$app->db->beginTransaction();

        $UserPayment = new UserPayments();
        $UserPayment->merchant_raw_data = $this->raw_data;
        $UserPayment->merchant_id = $this->subscr_id;
        $UserPayment->merchant_unique_pay_id = $this->txn_id ? $this->txn_id : md5(uniqid().time());
        $UserPayment->merchant_status = $this->txn_type;
        $UserPayment->merchant_currency = $this->mc_currency;
        $UserPayment->pay_currency = UserPayments::CURRENCY_USD;

        $UserPayment->user_id = $this->User->user_id;
        $UserPayment->license_count = 0;
        $UserPayment->license_type = $this->User->license_type;
        $UserPayment->license_period = $this->User->license_period;

        $UserPayment->pay_for = null;

        $UserPayment->merchant_amount = 0.00;
        $UserPayment->pay_amount = 0.00;
        $UserPayment->pay_status = UserPayments::STATUS_CANCELED;

        $UserPayment->license_expire = null;
        $UserPayment->pay_type = Users::PAY_CARD;

        if (in_array($this->txn_type, [self::TYPE_EOT, self::TYPE_SUSPENDED/*, self::TYPE_FAILED*/])) {
            $this->User->payment_already_initialized = Users::PAYMENT_NOT_INITIALIZED;
            $this->User->payment_init_date = null;
        }

        if (!$UserPayment->save() || !$this->User->save()) {
            $transaction->rollBack();

            //var_dump($UserPayment->getErrors());
            //var_dump($this->User->getErrors()); exit;
            return [
                'status' => false,
                'info'   => "ERROR Canceled",
            ];
        }

        $transaction->commit();

        return [
            'status' => true,
            'info'   => "OK Canceled",
        ];
    }

    /**
     * @return array
     * @throws \yii\db\Exception
     */
    protected function processPaymentForBusiness()
    {
        $transaction = Yii::$app->db->beginTransaction();

        $tmp = explode('-', $this->option_selection1);
        $this->license_count = intval($tmp[1]);
        $this->server_license_count = intval($tmp[0]);

        $UserPayment = new UserPayments();
        $UserPayment->merchant_raw_data = $this->raw_data;
        $UserPayment->merchant_id = $this->subscr_id;
        $UserPayment->merchant_unique_pay_id = $this->txn_id ? $this->txn_id : md5(uniqid().time());
        $UserPayment->merchant_status = $this->txn_type;
        $UserPayment->merchant_currency = $this->mc_currency;
        $UserPayment->pay_currency = UserPayments::CURRENCY_USD;

        $UserPayment->user_id = $this->User->user_id;
        $UserPayment->pay_type = Users::PAY_CARD;
        $UserPayment->license_count = $this->license_count;
        $UserPayment->license_type = Licenses::TYPE_PAYED_BUSINESS_ADMIN;
        $UserPayment->license_period = ($this->item_number == self::ITEM_BUSINESS_DAY)
            ? Licenses::PERIOD_DAILY
            : (
                ($this->item_number == self::ITEM_BUSINESS_MONTH)
                    ? Licenses::PERIOD_MONTHLY
                    : Licenses::PERIOD_ANNUALLY
            );
        //$UserPayment->license_expire
        $UserPayment->pay_for = ($this->User->license_type != Licenses::TYPE_PAYED_BUSINESS_ADMIN)
            ? UserPayments::CODE_PAY_FOR_BUSINESS
            : (
                ($this->User->payment_already_initialized == Users::PAYMENT_PROCESSED)
                    ? UserPayments::CODE_PAY_FOR_RENEWAL
                    : UserPayments::CODE_PAY_FOR_BUSINESS_AGAIN
              );

        if ($this->txn_type == self::TYPE_PAYMENT) {

            $UserPayment->merchant_amount = $this->payment_gross;
            $UserPayment->pay_amount = $this->payment_gross;
            $UserPayment->pay_status = UserPayments::STATUS_PAID;

            if ($UserPayment->pay_for == UserPayments::CODE_PAY_FOR_RENEWAL) {
                /** Это в случае продления ранее купленных лицензий */
                $new_expire = strtotime($this->User->license_expire) + $UserPayment->license_period * 86400;
                $lic_end = date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp($new_expire));

                UserLicenses::updateAll(['lic_end' => $lic_end], ['lic_owner_user_id' => $this->User->user_id]);
                UserServerLicenses::updateAll(['lic_srv_end' => $lic_end], ['lic_srv_owner_user_id' => $this->User->user_id]);

            } elseif ($UserPayment->pay_for == UserPayments::CODE_PAY_FOR_BUSINESS) {
                /** Это в случае первой покупки бизнес лицензии (когда до этого был какой то другой тип лицензии) */
                UserLicenses::deleteAll(['lic_owner_user_id' => $this->User->user_id]);
                UserServerLicenses::deleteAll(['lic_srv_owner_user_id' => $this->User->user_id]);

                $lic_start = date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(time()));
                $lic_end = date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(time() + $UserPayment->license_period * 86400));

                $lic_lastpay = time();
                $lic_group_id = time();
                /* создаем user-licenses */
                for ($i = 1; $i <= $UserPayment->license_count; $i++) {
                    $lic = new UserLicenses();
                    $lic->lic_start = $lic_start;
                    $lic->lic_end = $lic_end;
                    $lic->lic_period = $UserPayment->license_period;
                    $lic->lic_owner_user_id = $this->User->user_id;
                    $lic->lic_colleague_user_id = ($i == 1) ? $this->User->user_id : null;
                    $lic->lic_colleague_email = ($i == 1) ? $this->User->user_email : null;
                    $lic->lic_lastpay_timestamp = $lic_lastpay;
                    $lic->lic_group_id = $lic_group_id;
                    $lic->save();
                }

                /* создаем user-server-licenses */
                for ($i = 1; $i <= $this->server_license_count; $i++) {
                    $lic = new UserServerLicenses();
                    $lic->lic_srv_start = $lic_start;
                    $lic->lic_srv_end = $lic_end;
                    $lic->lic_srv_period = $UserPayment->license_period;
                    $lic->lic_srv_owner_user_id = $this->User->user_id;
                    $lic->lic_srv_colleague_user_id = null;
                    $lic->lic_srv_node_id = null;
                    $lic->lic_srv_lastpay_timestamp = $lic_lastpay;
                    $lic->lic_srv_group_id = $lic_group_id;
                    $lic->save();
                }

                $this->User->user_company_name = $this->option_selection2;
                $this->User->admin_full_name   = $this->option_selection3;
            } else {
                /** Это в случае перепокупки бизнеса, когда была бизнес, потом ее отменили и покупают заново */
                $lic_start = date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(time()));
                $lic_end = date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(time() + $UserPayment->license_period * 86400));

                /** обработка user-license */
                $licInfo = UserLicenses::getLicenseCountInfoForUser($this->User->user_id);
                if ($UserPayment->license_count >= $licInfo['total']) {
                    // тут просто добавить нужное количество лицензий и обновить их даты
                    // количество которое нужно добавить считаем по формуле
                    // $UserPayment->license_count - $licInfo['total']
                    $delta = $UserPayment->license_count - $licInfo['total'];
                    if ($delta > 0) {
                        $lic_lastpay = time();
                        $lic_group_id = time();
                        for ($i = 1; $i <= $delta; $i++) {
                            $lic = new UserLicenses();
                            $lic->lic_start = $lic_start;
                            $lic->lic_end = $lic_end;
                            $lic->lic_period = $UserPayment->license_period;
                            $lic->lic_owner_user_id = $this->User->user_id;
                            $lic->lic_colleague_user_id = null;
                            $lic->lic_colleague_email =  null;
                            $lic->lic_lastpay_timestamp = $lic_lastpay;
                            $lic->lic_group_id = $lic_group_id;
                            $lic->save();
                        }
                    }
                    UserLicenses::updateAll(['lic_end' => $lic_end], ['lic_owner_user_id' => $this->User->user_id]);
                } else {
                    if ($UserPayment->license_count >= $licInfo['used']) {
                        // тут довольно просто, нужно удалить у юзера определеннон количество лицензий которые еще не задействованы
                        // количество которое нужно удалить ввысчитаем по формуле $licInfo['total'] - $UserPayment->license_count
                        $delta = $licInfo['total'] - $UserPayment->license_count;
                        if ($delta > 0) {
                            $query = "DELETE FROM {{%user_licenses}} WHERE ctid IN (
                                        SELECT ctid FROM {{%user_licenses}}
                                        WHERE (lic_owner_user_id = :owner_user_id)
                                        AND (lic_colleague_user_id IS NULL)
                                        AND (lic_colleague_email IS NULL)
                                        LIMIT :delta
                                      )";
                            $res = Yii::$app->db->createCommand($query, [
                                'owner_user_id' => $this->User->user_id,
                                'delta' => $delta,
                            ])->execute();
                        }

                        UserLicenses::updateAll(['lic_end' => $lic_end], ['lic_owner_user_id' => $this->User->user_id]);
                    } else {
                        // а вот тут уже сложнее, нужно будет сначала удалить незадействованные лицензии если они есть
                        // затем высчитать разницу которая остается и согласно этой разнице
                        // выбрать столько же коллаборантов, удалить их из коллабораций
                        // лицензии освободятся и удалить эти освобожденные лицензии

                        /* Высчитываем склько коллаборантов нужно удалить */
                        $delta = $licInfo['used'] - $UserPayment->license_count;

                        /* находим и удаляем коллаборантов которых стоит удалить в первую очередь согласно тому как им назначены права и папки */
                        $query = "SELECT * FROM get_all_collaborated_colleagues(:owner_user_id)
                                  WHERE (1=1)
                                  AND (is_owner = 0)
                                  AND (colleague_permission != :PERMISSION_OWNER)
                                  AND ((license_type NOT IN (:PAYED_PROFESSIONAL, :PAYED_BUSINESS_ADMIN)) OR (license_type IS NULL))
                                  AND ((owner_collaboration_user_id = :owner_user_id) OR (owner_collaboration_user_id IS NULL))
                                  ORDER BY license_type DESC, awaiting_permissions DESC, colleague_status DESC, colleague_joined_date DESC
                                  LIMIT :delta";

                        $res = Yii::$app->db->createCommand($query, [
                            'owner_user_id'        => $this->User->user_id,
                            'PERMISSION_OWNER'     => UserColleagues::PERMISSION_OWNER,
                            'PAYED_PROFESSIONAL'   => Licenses::TYPE_PAYED_PROFESSIONAL,
                            'PAYED_BUSINESS_ADMIN' => Licenses::TYPE_PAYED_BUSINESS_ADMIN,
                            'delta'                => $delta,
                            //'owner_permission' => UserColleagues::PERMISSION_OWNER,
                            //'queued_del'       => UserColleagues::STATUS_QUEUED_DEL,
                            //'joined'           => UserColleagues::STATUS_JOINED,
                        ])->queryAll();

                        foreach ($res as $colleague) {
                            //var_dump($colleague);
                            $model = new ShareElementForm(['colleague_email']);
                            $model->colleague_email = $colleague['colleague_email'];
                            $model->owner_user_id = $this->User->user_id;
                            if ($model->validate()) {
                                $model->adminPanelColleagueDelete(false);
                            }
                        }
                        //exit;

                        /* удаляем освободившиеся лицензии */
                        $licInfo = UserLicenses::getLicenseCountInfoForUser($this->User->user_id);
                        $delta = $licInfo['total'] - $UserPayment->license_count;
                        $query = "DELETE FROM {{%user_licenses}} WHERE ctid IN (
                                        SELECT ctid FROM {{%user_licenses}}
                                        WHERE (lic_owner_user_id = :owner_user_id)
                                        AND (lic_colleague_user_id IS NULL)
                                        AND (lic_colleague_email IS NULL)
                                        LIMIT :delta
                                      )";
                        $res = Yii::$app->db->createCommand($query, [
                            'owner_user_id' => $this->User->user_id,
                            'delta' => $delta,
                        ])->execute();

                        UserLicenses::updateAll(['lic_end' => $lic_end], ['lic_owner_user_id' => $this->User->user_id]);
                    }
                }

                /** обработка user-server-license */
                $licSrvInfo = UserServerLicenses::getLicenseCountInfoForUser($this->User->user_id);
                if ($this->server_license_count >= $licSrvInfo['total']) {
                    // тут просто добавить нужное количество лицензий и обновить их даты
                    // количество которое нужно добавить считаем по формуле
                    // $this->server_license_count - $licInfo['total']
                    $delta = $this->server_license_count - $licSrvInfo['total'];
                    if ($delta > 0) {
                        $lic_lastpay = time();
                        $lic_group_id = time();
                        for ($i = 1; $i <= $delta; $i++) {
                            $lic = new UserServerLicenses();
                            $lic->lic_srv_start = $lic_start;
                            $lic->lic_srv_end = $lic_end;
                            $lic->lic_srv_period = $UserPayment->license_period;
                            $lic->lic_srv_owner_user_id = $this->User->user_id;
                            $lic->lic_srv_colleague_user_id = null;
                            $lic->lic_srv_node_id =  null;
                            $lic->lic_srv_lastpay_timestamp = $lic_lastpay;
                            $lic->lic_srv_group_id = $lic_group_id;
                            $lic->save();
                        }
                    }
                    UserServerLicenses::updateAll(['lic_srv_end' => $lic_end], ['lic_srv_owner_user_id' => $this->User->user_id]);
                } else {
                    // удаляем сначала незадействованные лицензии (node_id = null)
                    // а затем уже задействованные
                    // количество удаляемых всего высчитываем по формуле $licInfo['total'] - $this->server_license_count
                    $delta = $licSrvInfo['total'] - $this->server_license_count;
                    if ($delta > 0) {
                        $query = "DELETE FROM {{%user_server_licenses}} WHERE ctid IN (
                                    SELECT ctid FROM {{%user_server_licenses}}
                                    WHERE (lic_srv_owner_user_id = :owner_user_id)
                                    ORDER BY lic_srv_node_id DESC NULLS FIRST
                                    LIMIT :delta
                                  ) RETURNING lic_srv_id, lic_srv_node_id";
                        $res = Yii::$app->db->createCommand($query, [
                            'owner_user_id' => $this->User->user_id,
                            'delta' => $delta,
                        ])->execute();
                    }

                    UserServerLicenses::updateAll(['lic_srv_end' => $lic_end], ['lic_srv_owner_user_id' => $this->User->user_id]);
                }

            }
            $UserPayment->license_expire = $lic_end;
            $this->User->license_expire = $lic_end;
            $this->User->license_type = Licenses::TYPE_PAYED_BUSINESS_ADMIN;
            $this->User->license_period = $UserPayment->license_period;
            $this->User->pay_type = $UserPayment->pay_type;

            $info = "OK Business ({$UserPayment->pay_for}) server-licence count {$this->server_license_count}, license count {$UserPayment->license_count} till {$this->User->license_expire}";

            $this->User->payment_already_initialized = Users::PAYMENT_PROCESSED;
            $this->User->payment_init_date = date(SQL_DATE_FORMAT);
        } else {

            $UserPayment->merchant_amount = $this->amount3;
            $UserPayment->pay_amount = 0.00;
            $UserPayment->pay_status = UserPayments::STATUS_INFORM;

            $info = "OK Business ({$UserPayment->pay_for}) IS INFORM (no payed)";

            $this->User->payment_already_initialized = Users::PAYMENT_INITIALIZED;
            $this->User->payment_init_date = date(SQL_DATE_FORMAT);
        }


        if (!$UserPayment->save() || !$this->User->save()) {
            $transaction->rollBack();

            return [
                'status' => false,
                'info'   => "ERROR Business ({$UserPayment->pay_for})",
            ];
        }

        $transaction->commit();

        return [
            'status' => true,
            'info'   => $info,
        ];
    }

    /**
     * @return array
     * @throws \yii\db\Exception
     */
    protected function processPaymentForProfessional()
    {
        $transaction = Yii::$app->db->beginTransaction();

        $UserPayment = new UserPayments();
        $UserPayment->merchant_raw_data = $this->raw_data;
        $UserPayment->merchant_id = $this->subscr_id;
        $UserPayment->merchant_unique_pay_id = $this->txn_id ? $this->txn_id : md5(uniqid().time());
        $UserPayment->merchant_status = $this->txn_type;
        $UserPayment->merchant_currency = $this->mc_currency;
        $UserPayment->pay_currency = UserPayments::CURRENCY_USD;

        $UserPayment->user_id = $this->User->user_id;
        $UserPayment->pay_type = Users::PAY_CARD;
        $UserPayment->license_count = 1;
        $UserPayment->license_type = Licenses::TYPE_PAYED_PROFESSIONAL;
        $UserPayment->license_period = ($this->item_number == self::ITEM_PRO_DAY)
            ? Licenses::PERIOD_DAILY
            : (
                ($this->item_number == self::ITEM_PRO_MONTH)
                    ? Licenses::PERIOD_MONTHLY
                    : Licenses::PERIOD_ANNUALLY
            );
        //$UserPayment->license_expire
        //var_dump($this->User->license_type);exit;
        $UserPayment->pay_for = ($this->User->license_type != Licenses::TYPE_PAYED_PROFESSIONAL)
            ? UserPayments::CODE_PAY_FOR_PROFESSIONAL
            : (
                ($this->User->payment_already_initialized == Users::PAYMENT_PROCESSED)
                    ? UserPayments::CODE_PAY_FOR_RENEWAL
                    : UserPayments::CODE_PAY_FOR_PROFESSIONAL_AGAIN
              );
        //var_dump($UserPayment->pay_for);exit;

        if ($this->txn_type == self::TYPE_PAYMENT) {

            $UserPayment->merchant_amount = $this->payment_gross;
            $UserPayment->pay_amount = $this->payment_gross;
            $UserPayment->pay_status = UserPayments::STATUS_PAID;

            if ($UserPayment->pay_for == UserPayments::CODE_PAY_FOR_RENEWAL) {
                $new_expire = strtotime($this->User->license_expire) + $UserPayment->license_period * 86400;
                $lic_end = date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp($new_expire));
            } else {
                $lic_end = date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(time() + $UserPayment->license_period * 86400));
            }
            $UserPayment->license_expire = $lic_end;
            $this->User->license_expire = $lic_end;
            $this->User->license_type = Licenses::TYPE_PAYED_PROFESSIONAL;
            $this->User->license_period = $UserPayment->license_period;
            $this->User->pay_type = $UserPayment->pay_type;

            $info = "OK Professional ({$UserPayment->pay_for}) till {$this->User->license_expire}";

            $this->User->payment_already_initialized = Users::PAYMENT_PROCESSED;
            $this->User->payment_init_date = date(SQL_DATE_FORMAT);
        } else {

            $UserPayment->merchant_amount = $this->amount3;
            $UserPayment->pay_amount = 0.00;
            $UserPayment->pay_status = UserPayments::STATUS_INFORM;

            $info = "OK Professional ({$UserPayment->pay_for}) IS INFORM (no payed)";

            $this->User->payment_already_initialized = Users::PAYMENT_INITIALIZED;
            $this->User->payment_init_date = date(SQL_DATE_FORMAT);
        }

        if (!$UserPayment->save() || !$this->User->save()) {
            $transaction->rollBack();

            return [
                'status' => false,
                'info'   => "ERROR Professional ({$UserPayment->pay_for})",
            ];
        }

        $transaction->commit();

        return [
            'status' => true,
            'info'   => $info,
        ];
    }

    /**
     * @return array
     * @throws \yii\db\Exception
     */
    protected function processPaymentForProfessionalOneTime()
    {
        $transaction = Yii::$app->db->beginTransaction();

        $UserPayment = new UserPayments();
        $UserPayment->merchant_raw_data = $this->raw_data;
        $UserPayment->merchant_id = $this->subscr_id;
        $UserPayment->merchant_unique_pay_id = $this->txn_id ? $this->txn_id : md5(uniqid().time());
        $UserPayment->merchant_status = $this->txn_type;
        $UserPayment->merchant_currency = $this->mc_currency;
        $UserPayment->pay_currency = UserPayments::CURRENCY_USD;

        $UserPayment->user_id = $this->User->user_id;
        $UserPayment->pay_type = Users::PAY_CARD;
        $UserPayment->license_count = 1;
        $UserPayment->license_type = Licenses::TYPE_PAYED_PROFESSIONAL;
        $UserPayment->license_period = Licenses::PERIOD_ONETIME;
        //$UserPayment->license_expire
        //var_dump($this->User->license_type);exit;
        $UserPayment->pay_for = UserPayments::CODE_PAY_FOR_PROFESSIONAL;
        //var_dump($UserPayment->pay_for);exit;

        //if ($this->txn_type == self::TYPE_WEBACCEPT) {

            $UserPayment->merchant_amount = $this->payment_gross;
            $UserPayment->pay_amount = $this->payment_gross;
            $UserPayment->pay_status = UserPayments::STATUS_PAID;

            $lic_end = date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(time() + $UserPayment->license_period * 86400));

            $UserPayment->license_expire = $lic_end;
            $this->User->license_expire = $lic_end;
            $this->User->license_type = Licenses::TYPE_PAYED_PROFESSIONAL;
            $this->User->license_period = $UserPayment->license_period;
            $this->User->pay_type = $UserPayment->pay_type;

            $info = "OK Professional ({$UserPayment->pay_for}) till {$this->User->license_expire}";

            $this->User->payment_already_initialized = Users::PAYMENT_PROCESSED;
            $this->User->payment_init_date = date(SQL_DATE_FORMAT);
        //}

        if (!$UserPayment->save() || !$this->User->save()) {
            $transaction->rollBack();

            return [
                'status' => false,
                'info'   => "ERROR Professional ({$UserPayment->pay_for})",
            ];
        }

        $transaction->commit();

        return [
            'status' => true,
            'info'   => $info,
        ];
    }

}
