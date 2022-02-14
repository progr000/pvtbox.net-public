<?php
namespace frontend\models\forms;

use Yii;
use yii\base\Model;
use common\models\Users;
use common\models\Transfers;
use common\models\PaypalPays;

/**
 * Transfer form
 */
class TransferForm extends Model
{
    public $transfer_type;
    public $transfer_status;
    public $transfer_sum = '10.00';
    public $transfer_params;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['transfer_type', 'transfer_sum'], 'required'],
            ['transfer_type', 'integer', 'min'=>Transfers::TYPE_ROBOX, 'max'=>Transfers::TYPE_PAYPAL],
            ['transfer_type', 'default', 'value' => Transfers::TYPE_ROBOX],
            ['transfer_sum', 'double'],
            ['transfer_sum', 'default', 'value' => $this->transfer_sum],
        ];
    }

    /**
     * attribute for input fields.
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'transfer_sum' => 'transfer_sum:',
            'transfer_type' => 'transfer_type:',
            'transfer_params' => '',
        ];
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function startTransfer()
    {
        if ($this->validate()) {
            if ($this->transfer_type == Transfers::TYPE_PAYPAL) {
                $pp_sku = md5(time() . rand(0, 1000) . Yii::$app->user->identity->getId());
                $ret = Yii::$app->paypal->payThroughPayPal($this->transfer_sum, 'Оплата услуги на сайте '.Yii::$app->name, $pp_sku);
                if ($ret) {
                    $pp = new PaypalPays();
                    $pp->pp_payment_id = $ret['paymentId'];
                    $pp->user_id = Yii::$app->user->identity->getId();
                    //$pp->transfer_id = 0; // NULL
                    $pp->pp_token = $ret['token'];
                    //$pp->pp_payer_id = ''; // NULL
                    $pp->pp_sum = $this->transfer_sum;
                    $pp->pp_sku = $pp_sku;
                    $pp->pp_status = PaypalPays::STATUS_UNPAYED;
                    $pp->pp_status_info = 'Unknown';
                    if ($pp->save()) {
                        return $ret['redirect_to'];
                    }
                }
            } else {
                $transfer = new Transfers();
                $transfer->user_id = Yii::$app->user->identity->getId();
                $transfer->transfer_sum = $this->transfer_sum;
                $transfer->transfer_type = $this->transfer_type;
                $transfer->transfer_status = Transfers::STATUS_NEW;
                if ($transfer->save()) {
                    //$user = Users::findIdentity(Yii::$app->user->identity->user_id);
                    //$user->user_balance += $this->transfer_sum;
                    //$user->save();
                    return $transfer;
                }
            }
        }
        return null;
    }

}
