<?php
/** @var $this yii\web\View */
/** @var string $id */
/** @var string $billed */
/** @var string $license */
/** @var \common\models\Users $User */
/** @var \frontend\models\forms\PurchaseForm $model */

use yii\web\View;
use yii\helpers\Url;
use common\models\Preferences;
use common\models\Licenses;
use common\models\UserLicenses;
use common\models\UserServerLicenses;
use frontend\models\forms\PurchaseForm;
use frontend\assets\v20190812\purchaseAsset;

purchaseAsset::register($this);

$BILLED_MONTHLY  = Licenses::getBilledByPeriod(Licenses::PERIOD_MONTHLY);
$BILLED_ANNUALLY = Licenses::getBilledByPeriod(Licenses::PERIOD_ANNUALLY);
$BILLED_ONETIME  = Licenses::getBilledByPeriod(Licenses::PERIOD_ONETIME);

/* регистрируем яваскрипт */
$str  = Preferences::getJsStringForPricing();
$str .= "var BILLED_MONTHLY = '" . $BILLED_MONTHLY . "';\n";
$str .= "var BILLED_ANNUALLY = '" . $BILLED_ANNUALLY . "';\n";
$str .= "var BILLED_ONETIME = '" . $BILLED_ONETIME . "';\n";
if ($User->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
    $licInfo = UserLicenses::getLicenseCountInfoForUser($User->user_id);
    $str .= "var count_licenses_total = {$licInfo['total']};\n";
    $str .= "var count_licenses_used  = {$licInfo['used']};\n";
    $licServerInfo = UserServerLicenses::getLicenseCountInfoForUser($User->user_id);
    $str .= "var count_server_licenses_total = {$licServerInfo['total']};\n";
    $str .= "var count_server_licenses_used  = {$licServerInfo['used']};\n";
}
$this->registerJs($str, View::POS_END);

$model->os1 = ($User->user_company_name) ? $User->user_company_name : '';
$model->os2   = ($User->admin_full_name)   ? $User->admin_full_name   : '';

/**/
if ($billed == Licenses::getBilledByPeriod(Licenses::PERIOD_MONTHLY)) {
    $display_monthly  = "block";
    $display_annually = "none";
    $checked_monthly  = 'checked="checked"';
    $checked_annually = '';
    $active_monthly   = 'active';
    $active_annually  = '';
} else {
    $display_monthly  = "none";
    $display_annually = "block";
    $checked_monthly  = '';
    $checked_annually = 'checked="checked"';
    $active_monthly   = '';
    $active_annually  = 'active';
}
if ($license == PurchaseForm::LICENSE_ID_PROFESSIONAL) {
    $display_professional = "";
    $display_business = "none";
} else {
    $display_professional = "none";
    $display_business = "";
}

$radio_annually_hidden = "";
$radio_monthly_hidden = "";
if ($User->license_period != Licenses::PERIOD_NOT_SET) {
    //$billed_locked = ""
    if ($User->license_period == Licenses::PERIOD_MONTHLY) { $radio_annually_hidden = "hidden"; }
    if ($User->license_period == Licenses::PERIOD_ANNUALLY) { $radio_monthly_hidden = "hidden"; }
}
?>

<div class="payment"
     id="license-type"
     data-license-type="<?= $license ?>"
     data-billed-var="<?= $billed ?>">

    <?php if ($User->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) { ?>
    <div id="alert-text-for-less-licenses-than-before" class="hidden"><?= Yii::t('app/purchase', 'You_buy_licenses_less') ?></div>
    <div id="alert-text-for-less-server-licenses-than-before" class="hidden"><?= Yii::t('app/purchase', 'You_buy_server_licenses_less') ?></div>
    <?php } ?>

    <div class="payment__inner">

            <ol class="steps-list number-list">

                <li class="steps-item">
                    <div class="steps-item__title"><?= Yii::t('app/purchase', 'How_would_you_like_to_pay') ?></div>
                    <div class="steps-item__choice">
                        <div class="form-row">
                            <div class="check-wrap">
                                <input type="radio"
                                       name="pay-type"
                                       value="paypal"
                                       id="type-pay-paypal"
                                       checked="checked" />
                                <label for="type-pay-paypal"><span></span><span><?= Yii::t('app/purchase', 'PayPal') ?></span></label>
                            </div>
                        </div>
                    </div>
                </li>

                <?php if ($billed != Licenses::getBilledByPeriod(Licenses::PERIOD_ONETIME)) { ?>
                <li class="steps-item">
                    <div class="steps-item__title"><?= Yii::t('app/purchase', 'How_long_billing_period') ?></div>
                    <div class="steps-item__choice">
                        <div class="form-row">
                            <div class="check-wrap">
                                <input class="-js-toggle-discount"
                                       type="radio"
                                       name="radio-billed"
                                       value="monthly"
                                       id="period-pay-paypal" <?= $checked_monthly ?> />
                                <label for="period-pay-paypal"><span></span><span><?= Yii::t('app/purchase', 'Monthly') ?></span></label>
                            </div>
                            <div class="check-wrap">
                                <input class="-is-discount -js-toggle-discount"
                                       type="radio"
                                       name="radio-billed"
                                       value="annually"
                                       -disabled="disabled"
                                       id="period-pay-other" <?= $checked_annually ?> />
                                <label for="period-pay-other"><span></span><span><?= Yii::t('app/purchase', 'Annually') ?></span></label>
                            </div>
                            <div class="discount -js-discount-label save-sum-info" style="display: none;"><span><?= Yii::t('app/purchase', 'Save') ?>: $</span><span class="save-sum-val -visible"></span></div>
                        </div>
                    </div>
                </li>
                <?php } ?>

                <li class="steps-item <?=  ($license == PurchaseForm::LICENSE_ID_BUSINESS) ? "" : "no-number" ?>">
                    <?php if ($license == PurchaseForm::LICENSE_ID_BUSINESS) { ?>
                    <div class="steps-item__title"><?=  Yii::t('app/purchase', 'Fill in the company name, select the number of licenses and the number of server licenses (if necessary) please.') ?></div>
                    <?php } ?>

                    <div class="pp-forms-by-period" id="form-monthly" style="display: <?= $display_monthly ?>;">
                        <?php
                        if ($license == PurchaseForm::LICENSE_ID_PROFESSIONAL) {
                            if ($billed != Licenses::getBilledByPeriod(Licenses::PERIOD_ONETIME)) {
                                echo $this->render('purchase-professional-monthly', ['User' => $User, 'model' => $model]);
                            } else {
                                echo $this->render('purchase-professional-onetime', ['User' => $User, 'model' => $model]);
                            }
                        } else {
                            echo $this->render('purchase-business-monthly', ['User' => $User, 'model' => $model]);
                        }
                        ?>
                    </div>

                    <div class="pp-forms-by-period" id="form-annually" style="display: <?= $display_annually ?>;">
                        <?php
                        if ($license == PurchaseForm::LICENSE_ID_PROFESSIONAL) {
                            if ($billed != Licenses::getBilledByPeriod(Licenses::PERIOD_ONETIME)) {
                                echo $this->render('purchase-professional-annually', ['User' => $User, 'model' => $model]);
                            } else {
                                echo $this->render('purchase-professional-onetime', ['User' => $User, 'model' => $model]);
                            }
                        } else {
                            echo $this->render('purchase-business-annually', ['User' => $User, 'model' => $model]);
                        }
                        ?>
                    </div>

                </li>

            </ol>


        <form class="payment-frm">
            <ul class="payments-list">
                <li class="payment-item payment-item--sm"><img src="/assets/v20190812-min/images/payments/visa.svg" alt=""></li>
                <li class="payment-item payment-item--sm"><img src="/assets/v20190812-min/images/payments/mastercard.svg" alt=""></li>
                <li class="payment-item payment-item--sm"><img src="/assets/v20190812-min/images/payments/maestro.svg" alt=""></li>
                <li class="payment-item payment-item--sm"><img src="/assets/v20190812-min/images/payments/american-express.svg" alt=""></li>
                <li class="payment-item payment-item--sm"><img src="/assets/v20190812-min/images/payments/paypal.svg" alt=""></li>
            </ul>
            <?php if ($license == PurchaseForm::LICENSE_ID_BUSINESS) { ?>
            <p><?= Yii::t('app/purchase', 'Want_more_licenses') ?> <a href="<?= Url::to('/support', CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('app/purchase', 'Contact_Us') ?></a></p>
            <?php } ?>
        </form>

    </div>
</div>


