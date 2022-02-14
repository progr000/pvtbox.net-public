<?php
/** @var $this yii\web\View */
/** @var string $id */
/** @var string $billed */
/** @var string $license */
/** @var \common\models\Users $User */
/** @var \frontend\models\forms\PurchaseForm $model */

use yii\web\View;
use common\models\Preferences;
use common\models\Licenses;
use common\models\UserLicenses;
use frontend\models\forms\PurchaseForm;
use frontend\assets\orange\purchaseAsset;

//var_dump($billed);
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
<div id="license-type" class="hidden" data-license-type="<?= $license ?>" data-billed-var="<?= $billed ?>"></div>
<?php
if ($User->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
    ?>
    <div id="alert-text-for-less-licenses-than-before" class="hidden"><?= Yii::t('app/purchase', 'You_buy_licenses_less') ?></div>
    <?php
    }
?>
<div class="row pp-form-main-container">

    <div class="pp-form-border">

        <h3><table><tr><td><span>1</span></td><td><?= Yii::t('app/purchase', 'How_would_you_like_to_pay') ?></td></tr></table></h3>

        <div class="form-button" data-toggle="buttons">
            <label class="btn btn-radio active" for="type-pay-paypal">
                <input type="radio" name="pay-type" value="paypal" id="type-pay-paypal" checked="checked" />
                <?= Yii::t('app/purchase', 'PayPal') ?>
            </label>
            <!--
            <label class="btn btn-radio" for="type-pay-other">
                <input type="radio" name="pay-type" value="other" id="type-pay-other" />
                <?= Yii::t('app/purchase', 'Other') ?>
            </label>
            -->
        </div>


        <?php
            if ($billed != Licenses::getBilledByPeriod(Licenses::PERIOD_ONETIME)) {
            ?>
            <h3><table><tr><td><span>2</span></td><td><?= Yii::t('app/purchase', 'How_long_billing_period') ?></td></tr></table></h3>

            <div class="form-button" data-toggle="buttons">
                <label id="" class="btn btn-radio <?= ""/*$radio_monthly_hidden*/ ?> <?= $active_monthly ?>" for="period-pay-paypal">
                    <input type="radio" name="radio-billed" value="monthly" id="period-pay-paypal" <?= $checked_monthly ?> />
                    <?= Yii::t('app/purchase', 'Monthly') ?>
                </label>
                <label class="btn btn-radio <?= ""/*$radio_annually_hidden*/ ?> <?= $active_annually ?>" for="period-pay-other">
                    <input type="radio" name="radio-billed" value="annually" id="period-pay-other" <?= $checked_annually ?> disabled="disabled" />
                    <?= Yii::t('app/purchase', 'Annually') ?>
                </label>
                <span class="save-sum-info"><?= Yii::t('app/purchase', 'Save') ?>: $<span class="save-sum-val"></span></span>
            </div>
            <?php
            }
        ?>

        <?php
        if ($license == PurchaseForm::LICENSE_ID_BUSINESS) {
            echo "<h3><table><tr><td><span>3</span></td><td>" . Yii::t('app/purchase', 'Fill in the company name, select the number of licenses and the number of server licenses (if necessary) please.') . "</td></tr></table></h3>";
        }
        ?>

        <div class="pp-all-forms" style="_border: 1px solid #FF0000;">
            <div class="pp-all-forms-container" style="_border: 1px solid #00FF00;">
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

            </div>
        </div>

    </div>


</div>


