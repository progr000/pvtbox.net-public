<?php

namespace frontend\modules\paypal\controllers;

use frontend\models\PayPalButtonsApi;
use Yii;
use yii\base\ErrorException;
use yii\helpers\Json;
use yii\web\Response;
use yii\web\Controller;
use yii\base\DynamicModel;
use common\models\PaypalPays;
use common\models\Transfers;
use common\models\Users;
use common\models\Preferences;

/**
 * Default controller for the PayPal module
 * @property \frontend\components\PayPal $paypal
 */
class DefaultController extends Controller
{
    /** @var \frontend\components\PayPal $paypal */
    protected $paypal;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (is_object(Yii::$app->paypal) && get_class(Yii::$app->paypal) == 'frontend\components\PayPal') {
            $this->paypal = Yii::$app->paypal;
        } else {
            throw new ErrorException('PayPal module not configured');
        }
    }

    /**
     * Позволяет приходить запросам на этот скрипт и акшены перечисленные в массиве
     * с других доменов а не только с домена где стоит этот скрипт
     *
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, ['index', 'ipn'])) {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * Start it once. Its create a plan for paypal-subscriptions
     * @return array
     */
    public function actionCreatePlans()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $neededPlans = [
            'PricePerMonthForLicenseProfessional' => 'MONTH',
            'PricePerYearForLicenseProfessional'  => 'YEAR',
            'PricePerMonthUserForLicenseBusiness' => 'MONTH',
            'PricePerYearUserForLicenseBusiness'  => 'YEAR',
        ];
        $ret = [];
        foreach ($neededPlans as $k=>$v) {
            unset($plan);
            if ($v == 'YEAR') {
                $cost = Preferences::getValueByKey($k) * 12;
            } else {
                $cost = Preferences::getValueByKey($k);
            }
            $plan = $this->paypal->createPlan(
                $k,
                $k,
                $cost,
                'USD',
                $v
            );

            //var_dump($plan);exit;
            if (is_object($plan) && get_class($plan) == 'PayPal\Api\Plan') {
                $PDS = $plan->getPaymentDefinitions();
                //var_dump($PDS[0]->getId()); exit;
                $ret[$k] = [
                    'PlanId' => $plan->getId(),
                    'PaymentDefinitionId' => $PDS[0]->getId(),
                    'PaymentDefinitionCost' => $PDS[0]->getAmount()->getValue(),
                ];
                Preferences::setValueByKey("PP-{$k}", Json::encode($ret[$k]));
            }
        }

        return $ret;
    }

    /**
     * Обработка HTTP-запроса от PeyPal на изменение статуса платежа
     *
     * @return string
     */
    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_RAW;
        //var_dump(111);
        //exit;
        /*
        Для отладки
        $_POST['payer_id'] = 'UGAWLZAJGKKBE';
        $_POST['txn_id'] = '7K408775VL203262R';
        $_POST['receiver_email'] = 'progr000-facilitator@gmail.com';
        $_POST['item_number1'] = 'f956a6d54ce10985348611c0e2ccad2a';
        $_POST['mc_gross'] = '10.00';
        $_POST['payment_status'] = 'Pending'; //Completed, In-Progres
        */

        if (empty(Preferences::getValueByKey('paypalSellerEmail'))) {
            $this->paypal->logging('ERROR', "В настройках системы не задан параметр paypalSellerEmail. Работа без этого параметра невозможна.");
            return "ERROR: Yii configure error. You need set param paypalSellerEmail.";
        }

        if (!$this->paypal->checkIPN()) {
            $this->paypal->logging('ERROR', "Проверка данных, пришедших на IPN скрипт через сервер PayPal вернула INVALID. Возможно хакерский запрос. ", ['_POST'=>$_POST]);
            return "ERROR: Check IPN failed. PayPal said INVALID";
        }

        $model = new DynamicModel(['payer_id', 'txn_id', 'receiver_email', 'item_number1', 'mc_gross', 'payment_status']);
        $model->addRule(['payer_id', 'txn_id', 'receiver_email', 'item_number1', 'mc_gross', 'payment_status'], 'required');
        $model->addRule(['payer_id', 'txn_id', 'payment_status'], 'string', ['max' => 30]);
        $model->addRule('item_number1', 'string', ['max'=>50]);
        $model->addRule('receiver_email', 'email');
        $model->addRule('mc_gross', 'number');

        $data[$model->formName()] = $_POST;
        if (!$model->load($data) || !$model->validate()) {
            $this->paypal->logging('ERROR', "Входящие POST-данные ошибочные.", ['_POST'=>$_POST, 'validate'=>$model->getErrors()]);
            return "ERROR: Wrong POST data.";
        }

        if (Preferences::getValueByKey('paypalSellerEmail') != $model->receiver_email) {
            $this->paypal->logging('ERROR', "Ошибка безопасности. receiver_email не совпадает с paypalSellerEmail.", ['_POST'=>$_POST]);
            return "ERROR: Check IPN failed (receiver wrong).";
        }

        /* @var $pp PaypalPays */
        $pp = PaypalPays::findOne(['pp_txn_id' => $model->txn_id]);
        if (!$pp) {
            $pp = PaypalPays::findOne(['pp_sku' => $model->item_number1]);
        }

        if (!$pp) {
            $this->paypal->logging('ERROR', "Не найдена запись PaypalPays для пришедших данных.", ['_POST'=>$_POST]);
            return "ERROR: PaypalPays record not found.";
        }

        if (($pp->pp_payer_id != $model->payer_id) || ((float)$pp->pp_sum != (float)$model->mc_gross)) {
            $this->paypal->logging('ERROR', "Не совпадает сумма платежа в базе и переданная на скрипт IPN.", ['_POST'=>$_POST]);
            return "ERROR: PaypalPays record parameters mismatch.";
        }

        $transfer = Transfers::findIdentity($pp->transfer_id);
        if (!$transfer) {
            $this->paypal->logging('ERROR', "Ошибка БД. Не найдена запись Transfers для записи PaypalPays.", ['_POST'=>$_POST]);
            return "ERROR: No Transfers record found for this PaypalPays record.";
        }

        $user = Users::findIdentity($pp->user_id);
        if (!$user) {
            $this->paypal->logging('ERROR', "Ошибка БД. Не найдена запись Users для записи PaypalPays.", ['_POST'=>$_POST]);
            return "ERROR: No Users record found for this PaypalPays record.";
        }


        $pp->pp_status_info = $model->payment_status;
        if ((strtolower($model->payment_status) == 'completed') && ($pp->pp_status == PaypalPays::STATUS_UNPAYED)) {
            $pp->pp_status = PaypalPays::STATUS_PAYED;
            $transfer->transfer_status = Transfers::STATUS_DONE;
            $user->user_balance = $user->user_balance + $transfer->transfer_sum;
        } else if ((strtolower($model->payment_status) != 'completed') && ($pp->pp_status == PaypalPays::STATUS_PAYED)) {
            $pp->pp_status = PaypalPays::STATUS_UNPAYED;
            $transfer->transfer_status = Transfers::STATUS_WORK;
            $user->user_balance = $user->user_balance - $transfer->transfer_sum;
        }

        // *** Start Transaction
        $transaction = Yii::$app->db->beginTransaction();
        if ($pp->save() && $transfer->save() && $user->save()) {
            $transaction->commit();
            return "OK";
        }
        $transaction->rollBack();
        // *** End transaction

        $this->paypal->logging('ERROR', "Транзакция не удалась.", ['_POST'=>$_POST, 'PaypalPays'=>$pp, 'Transfers'=>$transfer, 'Users'=>$user]);
        return "ERROR: Failed transaction";
    }

    /**
     * @return string
     */
    public function actionIpn()
    {
        /*
        Поле item_number будет принимать такие значения:
        business_peer_day
        business_peer_month
        business_peer_year
        professional_peer_day
        professional_peer_month
        professional_peer_year

        month
        1lic/m  = 4.99
        5lic/m  = 24.95
        10lic/m = 49.90
        20lic/m = 99.80
        50lic/m = 249.50

        year
        1lic/m  = 4.16
        1lic/y  = 49.92
        5lic/y  = 249.60
        10lic/y = 499.20
        20lic/y = 998.40
        50lic/y = 2496.00
        */

        Yii::$app->response->format = Response::FORMAT_RAW;

        $ret  = "_POST:\n";
        $ret .= var_export($_POST, true);
        $ret .= "\n\n";
        $this->paypal->debugging($ret);

        /** запрос не прошел валидацию */
        /*
        if (!$this->paypal->checkIpnForPpButton(Yii::$app->request->post())) {
            $this->paypal->debugging("Check IPN failed. See ipn_answer.txt for details\n");
            return "ERROR check IPN query through paypal.";
        }
        */

        /** Пейпел меняет имена полей СУКА!!! в зависимости от статуса платежа. Ебанутый сука!!! Поэтому придется переопределить их тут  */
        if (!isset($_POST['mc_currency'])) {
            $_POST['mc_currency'] = 'USD';
        }
        if (isset($_POST['recurring_payment_id']) && !isset($_POST['subscr_id'])) {
            $_POST['subscr_id'] = $_POST['recurring_payment_id'];
        }
        if (isset($_POST['product_name']) && !isset($_POST['item_name'])) {
            $_POST['item_name'] = $_POST['product_name'];
        }
        if (isset($_POST['txn_type'], $_POST['txn_id'], $_POST['ipn_track_id'], $_POST['payer_id']) && $_POST['txn_type'] == PayPalButtonsApi::TYPE_WEBACCEPT) {
            $_POST['subscr_id'] = md5($_POST['txn_id'] . $_POST['ipn_track_id'] . $_POST['payer_id']);
        }

        //var_dump($_POST);
        /** некорректный набор данных */
        $ppb = new PayPalButtonsApi();
        if (!$ppb->load(['PayPalButtonsApi' => Yii::$app->request->post()]) || !$ppb->validate()) {
            $err = var_export($ppb->getErrors(), true);
            $this->paypal->debugging($err);
            //var_dump($err);
            return "ERROR post data is not correct.";
        }

        $ppb->raw_data = var_export(Yii::$app->request->post(), true);
        $ret = $ppb->processPayment();

        return $ret['info'];
        //return "OK";
    }
}
