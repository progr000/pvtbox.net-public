<?php
/* @var $this yii\web\View */
/* @var $model_changeemail \frontend\models\forms\ChangeEmailForm */
/* @var $model_changetimezone \frontend\models\forms\SetTimeZoneOffsetForm */
/* @var $user \common\models\Users */

use yii\bootstrap\ActiveForm;
use common\helpers\Functions;
use common\models\Users;
use common\models\Licenses;
use common\models\Preferences;

?>
<!-- begin #change-password-modal -->
<div class="popup top-popup" id="change-password-modal">
    <div class="popup__inner">
        <?php $form = ActiveForm::begin(['id' => 'form-profile', 'action'=>['profile']]); ?>
            <div class="popup-form-title"><?= Yii::t('user/profile', 'After_click_sent_instruct') ?></div>
            <input class="btn primary-btn wide-btn"
                   type="submit"
                   name="ChangePasswordStep1"
                   value="<?= Yii::t('forms/login-signup-form', 'OK') ?>" />
            <div class="img-progress" title="loading..."></div>
        <?php ActiveForm::end(); ?>
    </div>
    <button class="btn popup-close-btn js-close-popup" type="button" title="<?= Yii::t('app/common', "Close") ?>">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg>
    </button>
</div>
<!-- end #change-password-modal -->

<!-- begin #change-timezone-modal -->
<div class="popup top-popup" id="change-timezone-modal">
    <div class="popup__inner">
        <?php $form = ActiveForm::begin(['id' => 'form-change-timezone', 'action'=>['profile']]); ?>
            <div class="popup-form-title"><?= Yii::t('user/profile', 'Change_timezone') ?></div>
            <div class="select-wrap">
                <?php
                echo $form->field($model_changetimezone, 'timezone_offset_seconds', [
                    'template'=>'{label}<div class="select select-color-orange select-timezone">{input}{hint}{error}</div>'
                ])->dropDownList(Functions::get_list_of_timezones(Yii::$app->language), [
                    'id'              => "timezone-vars",
                    'class'           => "js-select",
                    'aria-label'      => $model_changetimezone->getAttributeLabel('timezone_offset_seconds'),
                ])->label(false);
                ?>
            </div>
            <input class="btn primary-btn wide-btn"
                   type="submit"
                   name="ChangeTimeZone"
                   value="<?= Yii::t('user/profile', 'OK') ?>" />
            <div class="img-progress" title="loading..."></div>
        <?php ActiveForm::end(); ?>
    </div>
    <button class="btn popup-close-btn js-close-popup" type="button" title="<?= Yii::t('app/common', "Close") ?>">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg>
    </button>
</div>
<!-- begin #change-timezone-modal -->

<?php
if (false) {

    if ($user->license_period == Licenses::PERIOD_NOT_SET) {
        $USER_BILLED_PERIOD = Licenses::getBilledByPeriod(Licenses::PERIOD_MONTHLY);
    } else {
        $USER_BILLED_PERIOD =  Licenses::getBilledByPeriod($user->license_period);
    }
    $BILLED_MONTHLY  = Licenses::getBilledByPeriod(Licenses::PERIOD_MONTHLY);
    $BILLED_ANNUALLY = Licenses::getBilledByPeriod(Licenses::PERIOD_ANNUALLY);

    if (!in_array($user->license_type, [Licenses::TYPE_PAYED_BUSINESS_ADMIN, Licenses::TYPE_PAYED_PROFESSIONAL])) {
        $sum_billed_monthly = '';
        $sum_billed_annually = '';
    } else {

        if ($user->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
            $sum_billed_monthly = number_format(Preferences::getValueByKey('PricePerMonthUserForLicenseBusiness', 99.99, 'float'), 2, '.', '');
            $sum_billed_annually = number_format(Preferences::getValueByKey('PricePerYearUserForLicenseBusiness', 99.99, 'float'), 2, '.', '');
        } else {
            $sum_billed_monthly = number_format(Preferences::getValueByKey('PricePerMonthForLicenseProfessional', 99.99, 'float'), 2, '.', '');
            $sum_billed_annually = number_format(Preferences::getValueByKey('PricePerYearForLicenseProfessional', 99.99, 'float'), 2, '.', '');
        }
    }
    ?>
<!-- begin #change-pay-type -->
<div class="popup" id="change-pay-type">
    <div class="popup__inner">
        <?php $form = ActiveForm::begin(['id' => 'form-change-pay-type', 'action'=>['change-pay-type']]); ?>
        <div class="form-button change-pay-type" data-toggle="buttons">
            <label for="radio-card" class="btn btn-radio <?= ($user->pay_type == Users::PAY_CARD) ? 'active' : '' ?>" style="display: block;">
                <input id="radio-card" name="BillingSettingsForm[pay_type]" value="<?= Users::PAY_CARD ?>" autocomplete="off" type="radio" <?= ($user->pay_type == Users::PAY_CARD) ? 'checked="checked"' : '' ?> />
                <?= Yii::t('user/billing', Users::PAY_CARD) ?> <?= ($user->pay_type == Users::PAY_CARD) ? '(current)' : ''?>
            </label>
            <label for="radio-crypto" class="btn btn-radio <?= ($user->pay_type == Users::PAY_CRYPTO) ? 'active' : '' ?>">
                <input id="radio-crypto" name="BillingSettingsForm[pay_type]" value="<?= Users::PAY_CRYPTO ?>" autocomplete="off" type="radio" <?= ($user->pay_type == Users::PAY_CRYPTO) ? 'checked="checked"' : '' ?> />
                <?= Yii::t('user/billing', Users::PAY_CRYPTO) ?> <?= ($user->pay_type == Users::PAY_CRYPTO) ? '(current)' : ''?>
            </label>
        </div>

        <input type="submit" name="Change" value="<?= Yii::t('user/billing', 'Confirm changes') ?>" class="btn-default" />
        <input type="button" name="Cancel" data-dismiss="modal" value="<?= Yii::t('user/billing', 'Cancel') ?>" class="btn-default cancel" />
        <div class="img-progress" title="loading..."></div>
        <?php ActiveForm::end(); ?>
    </div>
    <button class="btn popup-close-btn js-close-popup" type="button" title="<?= Yii::t('app/common', "Close") ?>">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg>
    </button>
</div>
<!-- end #change-pay-type -->

<!-- begin #change-billing-period -->
<div class="popup" id="change-billing-period">
    <div class="popup__inner">
        <?php $form = ActiveForm::begin(['id' => 'form-change-billing-period', 'action'=>['change-billing-period']]); ?>
        <div class="form-button change-billing-period" data-toggle="buttons">
            <label for="radio-monthly" class="btn btn-radio <?= ($USER_BILLED_PERIOD == $BILLED_MONTHLY) ? 'active' : '' ?>" style="display: block;">
                <input id="radio-monthly" name="BillingSettingsForm[billed]" value="<?= $BILLED_MONTHLY ?>" autocomplete="off" type="radio" <?= ($USER_BILLED_PERIOD == $BILLED_MONTHLY) ? 'checked="checked"' : '' ?> />
                Billed monthly $<?= $sum_billed_monthly ?> <?= ($USER_BILLED_PERIOD == $BILLED_MONTHLY) ? '(current)' : ''?>
            </label>
            <label for="radio-annually" class="btn btn-radio <?= ($USER_BILLED_PERIOD == $BILLED_ANNUALLY) ? 'active' : '' ?>">
                <input id="radio-annually" name="BillingSettingsForm[billed]" value="<?= $BILLED_ANNUALLY ?>" autocomplete="off" type="radio" <?= ($USER_BILLED_PERIOD == $BILLED_ANNUALLY) ? 'checked="checked"' : '' ?> />
                Billed annually $<?= $sum_billed_annually ?> <?= ($USER_BILLED_PERIOD == $BILLED_ANNUALLY) ? '(current)' : ''?>
            </label>
        </div>

        <input type="submit" name="Change" value="<?= Yii::t('user/billing', 'Confirm changes') ?>" class="btn-default" />
        <input type="button" name="Cancel" data-dismiss="modal" value="<?= Yii::t('user/billing', 'Cancel') ?>" class="btn-default cancel" />
        <div class="img-progress" title="loading..."></div>
        <?php ActiveForm::end(); ?>
    </div>
    <button class="btn popup-close-btn js-close-popup" type="button" title="<?= Yii::t('app/common', "Close") ?>">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg>
    </button>
</div>
<!-- end #change-billing-period -->
<?php
}
?>