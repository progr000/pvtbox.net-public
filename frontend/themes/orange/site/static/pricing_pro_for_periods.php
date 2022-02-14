<?php
/* @var $this yii\web\View */
/* @var $User common\models\Users */

use yii\web\View;
use yii\helpers\Url;
use common\models\Licenses;
use common\models\Preferences;
use common\models\Users;
use frontend\models\forms\PurchaseForm;

Yii::$app->session->set('after_signup_login_redirect_to', ['/pricing']);
$this->title = Yii::t('app/pricing', 'title');

$this->registerJsFile('themes/orange/js/pricing.js', ['depends' => 'yii\web\JqueryAsset']);

/* Подготовка сумм */
if ($User && $User->license_period == Licenses::PERIOD_ANNUALLY) {
    $USER_BILLED_PERIOD = Licenses::getBilledByPeriod(Licenses::PERIOD_ANNUALLY);
    $sum_professional = number_format(Preferences::getValueByKey('PricePerYearForLicenseProfessional', 99.99, 'float'), 2, '.', '');
    $sum_business     = number_format(Preferences::getValueByKey('PricePerYearUserForLicenseBusiness', 99.99, 'float'), 2, '.', '');
} else {
    $USER_BILLED_PERIOD =  Licenses::getBilledByPeriod(Licenses::PERIOD_MONTHLY);
    $sum_professional = number_format(Preferences::getValueByKey('PricePerMonthForLicenseProfessional', 99.99, 'float'), 2, '.', '');
    $sum_business     = number_format(Preferences::getValueByKey('PricePerMonthUserForLicenseBusiness', 99.99, 'float'), 2, '.', '');
}

$BILLED_MONTHLY  = Licenses::getBilledByPeriod(Licenses::PERIOD_MONTHLY);
$BILLED_ANNUALLY = Licenses::getBilledByPeriod(Licenses::PERIOD_ANNUALLY);

/* регистрируем яваскрипт */
$str  = Preferences::getJsStringForPricing();
$str .= "var USER_BILLED_PERIOD = '{$USER_BILLED_PERIOD}';\n";
$str .= "var USER_LICENSE_PERIOD = " . ($User ? $User->license_period : Licenses::PERIOD_NOT_SET) . ";\n";
$str .= "var BILLED_MONTHLY = '" . $BILLED_MONTHLY . "'\n";
$str .= "var BILLED_ANNUALLY = '" . $BILLED_ANNUALLY . "'\n";
$this->registerJs($str, View::POS_END);

if ($User) {
    if (in_array($User->payment_already_initialized, [Users::PAYMENT_INITIALIZED, Users::PAYMENT_PROCESSED]) && in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT, Licenses::TYPE_FREE_TRIAL])) {
        $button_lock_title = Yii::t('app/purchase', "You_are_already_init_license_buy", ['license_type' => Licenses::getType($User->license_type)]);
    } else {
        $button_lock_title = Yii::t('app/purchase', "You_are_already_buy_license", ['license_type' => Licenses::getType($User->license_type)]);
    }
}
?>
<!-- .pricing -->
<div class="pricing">
    <div class="pricing__cont">
        <div class="title">
            <h2><?= Yii::t('app/pricing', 'pricing__title') ?></h2>
        </div>

        <div class="pricing__text">
            <p><?= Yii::t('app/pricing', 'pricing__text') ?></p>
        </div>

        <div class="pricing__button" data-toggle="buttons">
            <label for="radio_billed_monthly" class="btn btn-radio-min <?= $USER_BILLED_PERIOD == $BILLED_MONTHLY ? "active" : "" ?>"><input autocomplete="off" id="radio_billed_monthly" name="radio-billed" value="<?= $BILLED_MONTHLY ?>" type="radio" <?= $USER_BILLED_PERIOD == $BILLED_MONTHLY ? 'checked="checked"' : "" ?> /><?= Yii::t('app/pricing', 'Billed_monthly') ?></label>
            <label for="radio_billed_annually" class="btn btn-radio-min <?= $USER_BILLED_PERIOD == $BILLED_ANNUALLY ? "active" : "" ?>"><input autocomplete="off" id="radio_billed_annually" name="radio-billed" value="<?= $BILLED_ANNUALLY ?>" type="radio" <?= $USER_BILLED_PERIOD == $BILLED_ANNUALLY ? 'checked="checked"' : "" ?> /><?= Yii::t('app/pricing', 'Billed_annually') ?></label>
        </div>

        <div class="pricing__block">
            <div class="pricing__box">
                <div class="pricing__box-cont">

                    <div class="pricing__head">
                        <span class="pricing__head-title"><?= Yii::t('app/pricing', 'head_title_Starter') ?></span>
                        <span class="pricing__head-inform"><?= Yii::t('app/pricing', 'head_inform_Starter') ?></span>
                        <span class="pricing__head-price"><b><?= Yii::t('app/pricing', 'head_price_Starter') ?></b></span>
                        <span class="pricing__head-time --pricing__head-time-month"><?= Yii::t('app/pricing', 'head_time_Starter_month') ?></span>
                        <!--<span class="pricing__head-time pricing__head-time-year"><?= Yii::t('app/pricing', 'head_time_Starter_year') ?></span>-->
                        <span class="pricing__head-start"><?= Yii::t('app/pricing', 'head_start_Starter') ?></span>
                        <div class="pricing__head-free">
                            <!--<a class="signup-dialog" href="javascript:void(0)"></a>-->
                            <span></span>
                        </div>
                        <div class="pricing__sticker_off"><span><?= Yii::t('app/pricing', 'head_sticker_Starter') ?></span></div>
                    </div>

                    <div class="pricing__body">
                        <div class="pricing__body-button">
                            <?=
                            Yii::$app->user->isGuest
                                ? '<a class="btn-default" href="' . Url::to(['/download'], CREATE_ABSOLUTE_URL) . '" target="_blank" rel="noopener">' . Yii::t('app/pricing', 'Download_now') . '</a>'
                                : (
                                    !in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT, Licenses::TYPE_FREE_TRIAL])
                                        ? '<a class="btn-default btn-notActive masterTooltip" href="javascript:void(0)" onclick="return false;" title="' . $button_lock_title . '">' . Yii::t('app/pricing', 'Download_now') . '</a>'
                                        : '<a class="btn-default" href="' . Url::to(['/download'], CREATE_ABSOLUTE_URL) . '" target="_blank" rel="noopener">' . Yii::t('app/pricing', 'Download_now') . '</a>'
                                )
                            ?>
                        </div>
                        <div class="pricing__body-version"><?= Yii::t('app/pricing', 'head_version_Starter') ?></div>
                        <div class="pricing__body-list">
                            <ul>
                                <?= Yii::t('app/pricing', 'body_list_Starter') ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pricing__box">
                <div class="pricing__box-cont">

                    <div class="pricing__head">
                        <span class="pricing__head-title"><?= Yii::t('app/pricing', 'head_title_PRO') ?></span>
                        <span class="pricing__head-inform"><?= Yii::t('app/pricing', 'head_inform_PRO') ?></span>
                        <span class="pricing__head-price">$ <span style="font-weight: 600;" id="price-professional"><?= $sum_professional ?></span sty></span>
                        <span class="pricing__head-time pricing__head-time-month"><?= Yii::t('app/pricing', 'head_time_PRO_month') ?></span>
                        <span class="pricing__head-time pricing__head-time-year"><?= Yii::t('app/pricing', 'head_time_PRO_year') ?></span>
                        <span class="pricing__head-start"><?= Yii::t('app/pricing', 'head_start_PRO') ?></span>
                        <div class="pricing__head-free -<?= Yii::$app->user->isGuest ? '' : 'hidden' ?>">
                            <a href="<?= Url::to(['/download'], CREATE_ABSOLUTE_URL) ?>" target="_blank" rel="noopener"><?= Yii::t('app/pricing', 'Try_for_free') ?></a>
                            <span><?= Yii::t('app/pricing', 'or') ?></span>
                        </div>
                        <div class="pricing__sticker" id="professional-save-sticker" style="display: none;"><span><b><?= Yii::t('app/pricing', 'Save') ?></b><b id="professional-save-sticker-text"></b></span></div>
                    </div>

                    <div class="pricing__body">
                        <div class="pricing__body-button">
                            <?=
                            Yii::$app->user->isGuest
                                ? '<a class="btn-default signup-dialog" href="#" data-toggle="modal" data-target="#entrance" data-whatever="reg">' . Yii::t('app/pricing', 'Purchase_now') . '</a>'
                                : (
                                    //in_array($User->license_type, [Licenses::TYPE_PAYED_PROFESSIONAL, Licenses::TYPE_PAYED_BUSINESS_ADMIN, Licenses::TYPE_PAYED_BUSINESS_USER])
                                    (in_array($User->license_type, [/*Licenses::TYPE_PAYED_BUSINESS_ADMIN,*/ Licenses::TYPE_PAYED_BUSINESS_USER]) || in_array($User->payment_already_initialized, [Users::PAYMENT_INITIALIZED, Users::PAYMENT_PROCESSED]))
                                        ? '<a class="btn-default btn-notActive masterTooltip" href="javascript:void(0)" onclick="return false;" title="' . $button_lock_title . '">' . Yii::t('app/pricing', 'Purchase_now') . '</a>'
                                        : '<a class="btn-default" id="link-professional" href="' . Url::to(['/purchase/summary?license=professional'], CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/pricing', 'Purchase_now') . '</a>'
                                )
                            ?>
                        </div>
                        <div class="pricing__body-version"><span><b><?= Yii::t('app/pricing', 'head_version_PRO') ?></b></span></div>
                        <div class="pricing__body-list">
                            <ul>
                                <?= Yii::t('app/pricing', 'body_list_PRO') ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pricing__box">
                <div class="pricing__box-cont">

                    <div class="pricing__head">
                        <span class="pricing__head-title"><?= Yii::t('app/pricing', 'head_title_Business') ?></span>
                        <span class="pricing__head-inform"><?= Yii::t('app/pricing', 'head_inform_Business') ?></span>
                        <span class="pricing__head-price">$ <span style="font-weight: 600;" id="price-business"><?= $sum_business ?></span></span>
                        <span class="pricing__head-time pricing__head-time-month"><?= Yii::t('app/pricing', 'head_time_month_Business') ?></span>
                        <span class="pricing__head-time pricing__head-time-year"><?= Yii::t('app/pricing', 'head_time_year_Business') ?></span>
                        <span class="pricing__head-start"><?= Yii::t('app/pricing', 'head_start_Business', ['license_count_available' => PurchaseForm::MIN_LICENSE_COUNT]) ?></span>
                        <div class="pricing__head-free -<?= Yii::$app->user->isGuest ? '' : 'hidden' ?>">
                            <a href="<?= Url::to(['/download'], CREATE_ABSOLUTE_URL) ?>" target="_blank" rel="noopener"><?= Yii::t('app/pricing', 'Try_for_free') ?></a>
                            <span><?= Yii::t('app/pricing', 'or') ?></span>
                        </div>
                        <div class="pricing__sticker" id="ideal-for-business-sticker"><span><?= Yii::t('app/pricing', 'head_sticker_Business') ?></span></div>
                        <div class="pricing__sticker" id="business-save-sticker" style="display: none;"><span><b><?= Yii::t('app/pricing', 'Save') ?></b><b id="business-save-sticker-text"></b></span></div>
                    </div>

                    <div class="pricing__body">
                        <div class="pricing__body-button">
                            <?=
                            Yii::$app->user->isGuest
                                ? '<a class="btn-default signup-dialog" href="#" data-toggle="modal" data-target="#entrance" data-whatever="reg">' . Yii::t('app/pricing', 'Purchase_now') . '</a>'
                                : (
                                    //in_array($User->license_type, [Licenses::TYPE_PAYED_BUSINESS_ADMIN, Licenses::TYPE_PAYED_BUSINESS_USER])
                                    (in_array($User->license_type, [/*Licenses::TYPE_PAYED_PROFESSIONAL,*/ Licenses::TYPE_PAYED_BUSINESS_USER]) || in_array($User->payment_already_initialized, [Users::PAYMENT_INITIALIZED, Users::PAYMENT_PROCESSED]))
                                        ? '<a class="btn-default btn-notActive masterTooltip" href="javascript:void(0)" onclick="return false;" title="' . $button_lock_title . '">' . Yii::t('app/pricing', 'Purchase_now') . '</a>'
                                        : '<a class="btn-default" id="link-business" href="' . Url::to(['/purchase/summary?license=business'], CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/pricing', 'Purchase_now') . '</a>'
                                )
                            ?>
                        </div>
                        <div class="pricing__body-version"><span><b><?= Yii::t('app/pricing', 'head_version_Business') ?></b></span></div>
                        <div class="pricing__body-list">
                            <ul>
                                <?= Yii::t('app/pricing', 'body_list_Business') ?>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <span class="pricing__info"><?= Yii::t('app/pricing', 'pricing__info') ?></span>

        <div class="means-payment"><span><?= Yii::t('app/pricing', 'We_accept') ?></span> <a class="means-payment__visa" href="javascript:void(0)">&nbsp;</a> <a class="means-payment__masterCard" href="javascript:void(0)">&nbsp;</a> <a class="means-payment__maestro" href="javascript:void(0)">&nbsp;</a> <a class="means-payment__americanExpress" href="javascript:void(0)">&nbsp;</a> <!--<a class="means-payment__bitcoin" href="javascript:void(0)">&nbsp;</a> --><a class="means-payment__payPal" href="javascript:void(0)">&nbsp;</a></div>
    </div>
</div>
<!-- END .pricing -->