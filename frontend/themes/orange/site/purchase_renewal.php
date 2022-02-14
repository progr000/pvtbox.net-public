<?php

/* @var $this yii\web\View */
/* @var $model \frontend\models\forms\PurchaseForm */
/* @var $User \common\models\Users */
/* @var $license_info array */

use yii\bootstrap\ActiveForm;
use common\models\Licenses;
use common\models\Preferences;
use common\models\UserLicenses;

$this->title = Yii::t('app/purchase', 'title');
//$this->registerJsFile('themes/orange/js/purchase.js', ['depends' => 'yii\web\JqueryAsset']);

/* регистрируем яваскрипт */
//$str  = Preferences::getJsStringForPricing();
//$str .= "var DELTA_SUM_FROM_PRO = $DELTA_SUM_FROM_PRO;";
//$this->registerJs($str, View::POS_END);

if ($User->license_type == Licenses::TYPE_PAYED_PROFESSIONAL) {
    $license_count = 1;
    $priceForPeriod = $User->license_period == Licenses::PERIOD_MONTHLY
        ? Preferences::getValueByKey('PricePerMonthForLicenseProfessional', 99.99, 'float')
        : Preferences::getValueByKey('PricePerYearForLicenseProfessional', 99.99, 'float') * 12;
} else {
    $license_info = UserLicenses::getLicenseCountInfoForUser($User->user_id);
    $license_count = $license_info['total'];
    $priceForPeriod = $User->license_period == Licenses::PERIOD_MONTHLY
        ? Preferences::getValueByKey('PricePerMonthUserForLicenseBusiness', 99.99, 'float')
        : Preferences::getValueByKey('PricePerYearUserForLicenseBusiness', 99.99, 'float') * 12;
}

$isPayInitialized = $model->User->checkPaymentInitialized();
?>

<!-- .payment -->
<div class="payment">

    <div class="pricing__cont">
    
        <span class="title-min"><?= Yii::t('app/purchase', 'Account_type') ?> <b><?= Licenses::getType($User->license_type) ?></b></span>

        <div class="payment__block">

            <?php
            $form = ActiveForm::begin([
                'id' => "form-purchase",
                'action'  => "/site/set-renewal",
                'method' => 'post',
                'options' => [
                    'role' => "form",
                    'onsubmit'   => $isPayInitialized ? "return false" : "return true",
                ],
            ]);
            ?>

                <?php
                if ($User->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {

                    //$model->user_company_name = $model->User->user_company_name;
                    echo $form->field($model, 'user_company_name')
                        ->textInput([
                            'placeholder' => $model->getAttributeLabel('user_company_name'),
                            'autocomplete' => "off",
                        ])
                        ->label(false);

                    //$model->admin_full_name = $model->User->admin_full_name;
                    echo $form->field($model, 'admin_full_name')
                        ->textInput([
                            'placeholder' => $model->getAttributeLabel('admin_full_name'),
                            'autocomplete' => "off",
                        ])
                        ->label(false);

                    echo '
                        <div class="form-group field-count-of-licenses">
                            License amount - ' . $license_info['total'] . '
                        </div>';
                }
                ?>

                <div class="form-total">
                    <span class="hidden" id="price-for-period"><?= $priceForPeriod ?></span>
                    <span>Total:</span>
                    <b id="set-total-on-select"><?= number_format($priceForPeriod * $license_count, 2, '.', '') ?></b><b>$</b>
                </div>

                <?php

                ?>
                <button class="btn-big <?= $isPayInitialized ? "btn-notActive" : '' ?> masterTooltip" type="submit" title="<?= $isPayInitialized ? Yii::t('app/purchase', "already_initialized") : '' ?>"><?= Yii::t('app/purchase', "Renew") ?></button>

            <?php
            ActiveForm::end();
            ?>

        </div>

    </div>

</div>
<!-- END .payment -->

