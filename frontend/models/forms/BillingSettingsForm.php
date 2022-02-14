<?php
namespace frontend\models\forms;

use Yii;
use yii\base\Model;
use common\models\Licenses;
use common\models\UserLicenses;
use common\models\Users;
use common\models\Preferences;
use common\models\UserPayments;

/**
 * Signup form
 */
class BillingSettingsForm extends Model
{


    public $license_period;
    public $billed;
    public $pay_type;

    /**/
    public $_required = [];

    /** @var \common\models\Users $User */
    public $User;

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
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            //[['license_period', 'pay_type'], 'required'],
            [['license_period'], 'integer'],
            [['license_period'], 'in', 'range' => [Licenses::PERIOD_MONTHLY, Licenses::PERIOD_ANNUALLY]],
            [['billed'], 'in', 'range' => [Licenses::getBilledByPeriod(Licenses::PERIOD_MONTHLY), Licenses::getBilledByPeriod(Licenses::PERIOD_ANNUALLY)]],
            [['pay_type'], 'in', 'range' => [Users::PAY_CARD, Users::PAY_CRYPTO]],
        ];

        if (sizeof($this->_required)) {
            $rules[] = $this->_required;
        }

        return $rules;
    }

    /**
     * @return bool
     */
    public function changeBilled()
    {
        $this->User->license_period = Licenses::getPeriodByBilled($this->billed);
        return $this->User->save();
    }

    /**
     * @return bool
     */
    public function changePayType()
    {
        $this->User->pay_type = $this->pay_type;
        return $this->User->save();
    }
}
