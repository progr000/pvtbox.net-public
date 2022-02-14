<?php
/* @var $this yii\web\View */
/* @var $model \frontend\models\search\SessionsSearch */
/* @var $model_changeemail \frontend\models\forms\ChangeEmailForm */
/* @var $form \yii\widgets\ActiveForm */
/* @var $user \common\models\Users */
/* @var $dataProviderPayments \yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\widgets\ListView;
use yii\widgets\Pjax;
use common\helpers\Functions;
use common\models\Licenses;
use common\models\Preferences;
use common\models\Users;
use common\models\UserLicenses;
use common\models\UserServerLicenses;


/* Подготовка сумм */
$lock_change_link = false;
if ($user->license_period == Licenses::PERIOD_NOT_SET) {
    $USER_BILLED_PERIOD = Licenses::getBilledByPeriod(Licenses::PERIOD_MONTHLY);
} else {
    $USER_BILLED_PERIOD =  Licenses::getBilledByPeriod($user->license_period);
}
if (!in_array($user->license_type, [Licenses::TYPE_PAYED_BUSINESS_ADMIN, Licenses::TYPE_PAYED_PROFESSIONAL])) {
    $lock_change_link = true;
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

$BILLED_MONTHLY  = Licenses::getBilledByPeriod(Licenses::PERIOD_MONTHLY);
$BILLED_ANNUALLY = Licenses::getBilledByPeriod(Licenses::PERIOD_ANNUALLY);

$isPayInitialized = $user->checkPaymentInitialized();
?>

<div class="inputForm inputForm--name">

    <!-- Pay Type -->
    <div class="billingForm__cont">
        <div class="billingForm__box">
            <div class="billingForm__title">
                <span><?= Yii::t('user/billing', 'Payment_method') ?></span>
            </div>
        </div>
        <div class="billingForm__box">
            <div class="billingForm__title">
                <b><?= Yii::t('user/billing', $user->pay_type) ?></b>
            </div>
        </div>
        <div class="billingForm__box">
            <?php
            if ($user->license_type != Licenses::TYPE_PAYED_PROFESSIONAL) {
                if ($lock_change_link) {
                    ?>
                    <a class="link-change billing-change-pay-type masterTooltip"
                       title="<?= Yii::t('user/billing', 'Contact_support') ?>"
                       -href="<?= Url::to(['/pricing'], CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('user/billing', 'Change') ?></a>
                    <?php
                } else {
                    ?>
                    <span class="link-change billing-change-pay-type masterTooltip"
                          title="<?= Yii::t('user/billing', 'Contact_support') ?>" -data-toggle="modal"
                          -data-target="#change-pay-type"><?= Yii::t('user/billing', 'Change') ?></span>
                    <?php
                }
            }
            ?>
        </div>
    </div>

    <!-- Billing Period -->
    <div class="billingForm__cont">
        <div class="billingForm__box">
            <div class="billingForm__title">
                <span><?= Yii::t('user/billing', 'Billing_period') ?></span>
            </div>
        </div>
        <div class="billingForm__box">
            <div class="billingForm__title">
                <b><?= Licenses::getBilledByPeriod($user->license_period, true) ?></b>
            </div>
        </div>
        <div class="billingForm__box">
            <?php
            if ($user->license_type != Licenses::TYPE_PAYED_PROFESSIONAL) {
                if ($lock_change_link) {
                    ?>
                    <a class="link-change billing-change-billing-period masterTooltip"
                       title="<?= Yii::t('user/billing', 'Contact_support') ?>"
                       -href="<?= Url::to(['/pricing'], CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('user/billing', 'Change') ?></a>
                    <?php
                } else {
                    ?>
                    <span class="link-change billing-change-billing-period masterTooltip"
                          title="<?= Yii::t('user/billing', 'Contact_support') ?>" -data-toggle="modal"
                          -data-target="#change-billing-period"><?= Yii::t('user/billing', 'Change') ?></span>
                    <?php
                }
            }
            ?>
        </div>
    </div>

    <!-- Next pay date -->
    <div class="billingForm__cont">
        <div class="billingForm__box">
            <div class="billingForm__title">
                <span><?= Yii::t('user/billing', 'Next_pay') ?></span>
            </div>
        </div>
        <div class="billingForm__box">
            <div class="billingForm__title">
                <b><?= $user->license_type == Licenses::TYPE_PAYED_PROFESSIONAL
                        ? Yii::t('user/billing', 'Never_unlimited_account')
                        : (
                            $user->license_expire
                                ? Functions::formatPostgresDate(Yii::$app->params['date_format'], $user->license_expire)
                                : Yii::t('user/billing', 'not_set')
                        )
                ?></b>
            </div>
        </div>
        <div class="billingForm__box">
            <?php
            if (!Users::isAutoPayType($user->pay_type)) {
                if (Licenses::checkIsExpireSoon($user->license_expire) || Licenses::checkIsExpired($user->license_expire)) {
                    //echo '<a class="btn-default ' . ($isPayInitialized ? "btn-notActive" : '') . ' masterTooltip" href="' . ($isPayInitialized ? "#" : Url::to(['/purchase/renewal'], CREATE_ABSOLUTE_URL)) . '" title="' . ($isPayInitialized ? Yii::t('app/purchase', "already_initialized") : '') . '">' . Yii::t('user/billing', 'Renew_now') . '</a>';
                }
            } elseif (in_array($user->license_type, [Licenses::TYPE_FREE_DEFAULT, Licenses::TYPE_FREE_TRIAL])) {
                echo '<a class="btn-default ' . ($isPayInitialized ? "btn-notActive" : '') . ' masterTooltip" href="' . ($isPayInitialized ? "#" : Url::to(['/pricing'], CREATE_ABSOLUTE_URL)) . '" title="' . ($isPayInitialized ? Yii::t('app/purchase', "already_initialized") : '') . '">' . Yii::t('user/billing', 'Purchase_now') . '</a>';
            }
            ?>
        </div>
    </div>

    <!-- Number of purchased and available licenses -->
    <?php
    if ($user->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
        $license_info = UserLicenses::getLicenseCountInfoForUser($user->user_id);
        ?>
        <div class="billingForm__cont">
            <div class="billingForm__box">
                <div class="billingForm__title">
                    <span><?= Yii::t('user/billing', 'License_info') ?></span>
                </div>
            </div>
            <div class="billingForm__box">
                <div class="billingForm__title">
                    <?= Yii::t('user/billing', 'Total') ?> <a href="<?= Url::to(['/admin-panel?tab=2'], CREATE_ABSOLUTE_URL) ?>"><?= $license_info['total'] ?></a><br />
                    <?= Yii::t('user/billing', 'Used') ?> <a href="<?= Url::to(['/admin-panel?tab=2'], CREATE_ABSOLUTE_URL) ?>"><?= $license_info['used'] ?></a><br />
                    <?= Yii::t('user/billing', 'Available') ?> <a href="<?= Url::to(['/admin-panel?tab=2'], CREATE_ABSOLUTE_URL) ?>"><?= $license_info['unused'] ?></a>
                </div>
            </div>
            <div class="billingForm__box">

            </div>
        </div>

        <?php
        $server_license_info = UserServerLicenses::getLicenseCountInfoForUser($user->user_id);
        ?>
        <div class="billingForm__cont">
            <div class="billingForm__box">
                <div class="billingForm__title">
                    <span><?= Yii::t('user/billing', 'Server_license_info') ?></span>
                </div>
            </div>
            <div class="billingForm__box">
                <div class="billingForm__title">
                    <?= Yii::t('user/billing', 'Total') ?> <a href="<?= Url::to(['/admin-panel?tab=4'], CREATE_ABSOLUTE_URL) ?>"><?= $server_license_info['total'] ?></a><br />
                    <?= Yii::t('user/billing', 'Used') ?> <a href="<?= Url::to(['/admin-panel?tab=4'], CREATE_ABSOLUTE_URL) ?>"><?= $server_license_info['used'] ?></a><br />
                    <?= Yii::t('user/billing', 'Available') ?> <a href="<?= Url::to(['/admin-panel?tab=4'], CREATE_ABSOLUTE_URL) ?>"><?= $server_license_info['unused'] ?></a>
                </div>
            </div>
            <div class="billingForm__box">

            </div>
        </div>

        <?php
    }
    ?>

    <!-- Payment list -->
    <div class="billingForm__cont">
        <div class="billingForm__box">
            <div class="billingForm__title">
                <span><?= Yii::t('user/billing', 'Payment_history') ?></span>
            </div>
        </div>
        <div class="billingForm__box"></div>
        <div class="billingForm__box"></div>
    </div>

    <!-- Payment list table -->
    <div class="table table--settings">

        <div class="table__head-cont" style="padding-right: 0px;">

            <div class="table__head">
                <div class="table__head-box"><span>Date</span></div>
                <div class="table__head-box"><span>Sum</span></div>
                <div class="table__head-box"><span>For</span></div>
                <div class="table__head-box"><span>Type</span></div>
                <div class="table__head-box"><span>Status</span></div>
            </div>

        </div>

        <?php Pjax::begin(); ?>
        <?php
        $minPageSize = 5;
        $count = $dataProviderPayments->count;
        $lost = isset($dataProviderPayments->pagination->pageSize) ? $dataProviderPayments->pagination->pageSize - $count : $minPageSize - $count;
        ?>
        <?=
        ListView::widget([
            'dataProvider' => $dataProviderPayments,
            //'itemOptions' => ['class' => 'item'],
            'itemOptions' => [
                'tag' => false,
                'class' => '',
            ],
            'layout' => '<div class="scrollbar-box"><div class="table__body-cont">' . "{items}" . '</div></div>' . "\n{pager}",
            'emptyText' => $this->render('profile_billing_list_nodata'),
            'emptyTextOptions' => ['tag' => false],
            //'layout' => "{pager}\n{summary}\n{items}\n{pager}",
            //'summary' => 'Показано {count} из {totalCount}',
            'itemView' => function ($searchModelPayments, $key, $index, $widget) use ($lost, $count) {
                $lost_row = '';
                if ($lost>0 && ($index == $count - 1)) {
                    for ($i=1; $i<=$lost; $i++) {
                        $lost_row .= $this->render('profile_billing_list_item_empty');
                    }
                }
                /** @var $model \frontend\models\search\UserNodeSearch */
                return $this->render('profile_billing_list_item', ['searchModelPayments' => $searchModelPayments]) . $lost_row;
            },
        ]);
        ?>
        <?php Pjax::end(); ?>

        <!--
        <div class="inform-boxBottom">
            <div class="inform inform-inlineMin"><p>* Inform box.</p></div>
        </div>
        -->

    </div>
</div>

<?php
// Modal Password
Modal::begin([
'options' => ['id' => 'change-pay-type'],
//'closeButton' => ['id' => 'close-button-chpassword'],
'closeButton' => false,
'header' => '<div type="button" class="close" data-dismiss="modal" aria-label="Close"></div>',
'size' => '',
]);
?>
    <div class="form-block form-change-billing-period">
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
<?php
Modal::end();
?>

<?php
// Modal Password
Modal::begin([
    'options' => ['id' => 'change-billing-period'],
    //'closeButton' => ['id' => 'close-button-chpassword'],
    'closeButton' => false,
    'header' => '<span class="modal-header-title">Change billing period</span><div type="button" class="close" data-dismiss="modal" aria-label="Close"></div>',
    'size' => '',
]);
?>
    <div class="form-block form-change-billing-period">
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
<?php
Modal::end();
?>