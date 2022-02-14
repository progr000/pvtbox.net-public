<?php

/* @var $this yii\web\View */
/* @var $model \frontend\models\forms\PurchaseForm */
/* @var $User \common\models\Users */

use yii\web\View;
use yii\bootstrap\ActiveForm;
use common\models\Licenses;
use common\models\Preferences;
use common\models\Users;
use frontend\models\forms\PurchaseForm;

$this->title = Yii::t('app/purchase', 'title');
$this->registerJsFile('themes/orange/js/purchase.js', ['depends' => 'yii\web\JqueryAsset']);

$DELTA_SUM_FROM_PRO = 0.00;
//var_dump($model->license_period);
if (!Yii::$app->user->isGuest) {
    $User = Yii::$app->user->identity;
    if ($User->license_type == Licenses::TYPE_PAYED_PROFESSIONAL) {
        $DELTA_SUM_FROM_PRO = $model->license_period == Licenses::PERIOD_MONTHLY
            ? Preferences::getValueByKey('PricePerMonthForLicenseProfessional', 99.99, 'float')
            : Preferences::getValueByKey('PricePerYearForLicenseProfessional', 99.99, 'float') * 12;
    }
}
/* регистрируем яваскрипт */
$str  = Preferences::getJsStringForPricing();
$str .= "var DELTA_SUM_FROM_PRO = $DELTA_SUM_FROM_PRO;";
$this->registerJs($str, View::POS_END);

$isPayInitialized = $model->User->checkPaymentInitialized();
?>

<!-- .payment -->
<div class="payment">

    <div class="pricing__cont">
    
        <span class="title-min"><?= Yii::t('app/purchase', 'Account_type') ?> <b><?= Licenses::getType(Licenses::TYPE_PAYED_BUSINESS_ADMIN) ?></b></span>

        <div class="payment__block">

            <?php
            $form = ActiveForm::begin([
                'id' => "form-purchase",
                'action'  => "/site/set-license-type",
                'method' => 'post',
                'options' => [
                    'role' => "form",
                    'onsubmit'   => $isPayInitialized ? "return false" : "return true",
                ],
            ]);
            ?>

                <?php
                echo $form->field($model, 'license_type')->hiddenInput()->label(false);
                echo $form->field($model, 'license_period')->hiddenInput()->label(false);
                ?>

                <?php
                //$model->user_company_name = $model->User->user_company_name;
                echo $form->field($model, 'user_company_name')
                    ->textInput([
                        'placeholder'  => $model->getAttributeLabel('user_company_name'),
                        'autocomplete' => "off",
                    ])
                    ->label(false)
                ?>

                <?php
                //$model->admin_full_name = $model->User->admin_full_name;
                echo $form->field($model, 'admin_full_name')
                    ->textInput([
                        'placeholder'  => $model->getAttributeLabel('admin_full_name'),
                        'autocomplete' => "off",
                    ])
                    ->label(false)
                ?>

                <?php
                $vars = [];
                for ($i=PurchaseForm::MIN_LICENSE_COUNT; $i<=PurchaseForm::MAX_LICENSE_COUNT; $i++) {
                    $vars[$i] = Yii::t('app/purchase', 'License_amount', ['count' => $i]); //"License amount - {$i}";
                }
                echo $form->field($model, 'license_count', [
                    'template'=>'{label}<div class="select-simple">{input}{hint}{error}</div>'
                ])->dropDownList($vars, [
                    'id'              => "count-of-licenses",
                    'class'           => "selectpicker",
                    'data-actionsBox' => "true",
                    'data-size'       => "5",
                ])->label(false);
                ?>

                <div class="form-total">
                    <span class="hidden" id="price-for-period"><?php
                        $priceForPeriod = $model->license_period == Licenses::PERIOD_MONTHLY
                            ? Preferences::getValueByKey('PricePerMonthUserForLicenseBusiness', 99.99, 'float')
                            : Preferences::getValueByKey('PricePerYearUserForLicenseBusiness', 99.99, 'float') * 12;
                        echo $priceForPeriod;
                        ?></span>
                    <span>Total:</span>
                    <b id="set-total-on-select"><?= number_format($priceForPeriod * $model->license_count - $DELTA_SUM_FROM_PRO, 2, '.', '') ?></b><b>$</b>
                </div>

                <div class="payment__button" data-toggle="buttons">
                    <label for="PurchaseForm_pay_type_card" class="btn btn-radio-min <?= in_array($model->User->pay_type, [Users::PAY_CARD, Users::PAY_NOTSET]) ? 'active' : '' ?>"><input type="radio" id="PurchaseForm_pay_type_card" name="PurchaseForm[pay_type]" autocomplete="off" value="<?= Users::PAY_CARD ?>" <?= in_array($model->User->pay_type, [Users::PAY_CARD, Users::PAY_NOTSET]) ? 'checked="checked"' : '' ?>><b class="btn-payPal"></b><?= Users::getPayTypeName(Users::PAY_CARD) ?></label>
                    <label for="PurchaseForm_pay_type_crypto" class="btn btn-radio-min <?= $model->User->pay_type == Users::PAY_CRYPTO ? 'active' : '' ?>"><input type="radio" id="PurchaseForm_pay_type_crypto" name="PurchaseForm[pay_type]" autocomplete="off" value="<?= Users::PAY_CRYPTO ?>" <?= $model->User->pay_type == Users::PAY_CRYPTO ? 'checked="checked"' : '' ?>><b class="btn-bitcoin-"></b><?= Users::getPayTypeName(Users::PAY_CRYPTO) ?></label>
                </div>

                <button class="btn-big <?= $isPayInitialized ? "btn-notActive" : '' ?> masterTooltip" type="submit" title="<?= $isPayInitialized ? Yii::t('app/purchase', "already_initialized") : '' ?>"><?= Yii::t('app/purchase', 'Purchase_button') ?></button>

            <?php
            ActiveForm::end();
            ?>

        </div>

    </div>

</div>
<!-- END .payment -->

