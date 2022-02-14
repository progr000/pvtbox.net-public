<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\PaypalPays;
use common\models\Transfers;

/**
 * Login form
 */
class PaypalPaysCheck extends Model
{
    public $status;
    public $paymentId;
    public $token;
    public $PayerID;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'in', 'range' => ['success', 'canceled']],
            ['token', 'required'],
            ['PayerID', 'default', 'value' => null],
            ['paymentId', 'default', 'value' => 'Unknown'],
            [['paymentId', 'token', 'PayerID'], 'string', 'max' => 30],
        ];
    }

    /**
     * Logs in a user using the provided fu_hs.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function checkPay()
    {
        if ($this->validate()) {

            if ($this->status === 'canceled') {
                PaypalPays::findByToken($this->token)->delete();
                Yii::$app->getSession()->setFlash('error', Yii::t('app/flash-messages', 'payPal_YouCanceledPay'));
                return false;
            }

            $pp = PaypalPays::findByPaymentId($this->paymentId);

            if (!$pp) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app/flash-messages', 'payPal_ErrorWrongParametersGiven'));
                return false;
            }

            if (!$this->PayerID) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app/flash-messages', 'payPal_ErrorWrongPayerID'));
                return false;
            }

            if ($pp->user_id !== Yii::$app->user->identity->getId()) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app/flash-messages', 'payPal_ErrorWrongUser'));
                return false;
            }

            if ($pp->pp_token !== $this->token) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app/flash-messages', 'payPal_ErrorWrongToken'));
                return false;
            }

            if (Transfers::findIdentity($pp->transfer_id)) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app/flash-messages', 'payPal_PaymentsAlreadyProcessed'));
                return false;
            }

            // *** START TRANSACTION ***
            $transaction = Yii::$app->db->beginTransaction();
            $transfer = new Transfers();
            $transfer->user_id = Yii::$app->user->identity->getId();
            $transfer->transfer_sum = $pp->pp_sum;
            $transfer->transfer_type = Transfers::TYPE_PAYPAL;
            $transfer->transfer_status = Transfers::STATUS_WORK;
            if ($transfer->save()) {
                $pp->pp_payer_id = $this->PayerID;
                $pp->pp_status = PaypalPays::STATUS_UNPAYED;
                $pp->pp_status_info = 'created';
                $pp->transfer_id = $transfer->transfer_id;

                if ($pp->save()) {

                    $transaction->commit();

                    $pp_txn_id = Yii::$app->paypal->payThroughPayPalConfirm($this->paymentId, $this->PayerID);
                    if ($pp_txn_id) {
                        $pp->pp_txn_id = $pp_txn_id;
                        $pp->pp_status_info = 'approved';
                        $pp->save();
                    }

                    Yii::$app->getSession()->setFlash('success', Yii::t('app/flash-messages', 'payPal_PaymentsSuccess'));
                    return true;

                } else { Yii::$app->getSession()->setFlash('error', Yii::t('app/flash-messages', 'payPal_ErrorPyaPalSavingFail')); }
            } else { Yii::$app->getSession()->setFlash('error', Yii::t('app/flash-messages', 'payPal_ErrorTransferSavingFail')); }
            $transaction->rollBack();
            // *** END TRANSACTION ***

        } else { Yii::$app->getSession()->setFlash('error', Yii::t('app/flash-messages', 'payPal_ValidationFail')); }

        return false;
    }

}
