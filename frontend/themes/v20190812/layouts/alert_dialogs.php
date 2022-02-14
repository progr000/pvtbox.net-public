<?php
/* @var $this \yii\web\View */
/* @var $user \common\models\Users */

use common\widgets\Alert;
use common\models\Users;
use common\models\Preferences;
use common\models\Licenses;

/*
'error'   => 'alert-error',   - red
'danger'  => 'alert-danger',  - yellow
'warning' => 'alert-warning'  - yellow
'info'    => 'alert-info',    - blue
'success' => 'alert-success', - green
*/
/*
Yii::$app->session->setFlash('test_1', [
    'message'   => 'test-alert-message1',
    'ttl'       => 0,
    'showClose' => true,
    'alert_id' => 'alert-test-1',
    'type' => 'danger',
    'alert_action' => 'test1-action',
    //'class' => 'alert-danger',
    //'auto_close_callback' => 'alert(1)',
]);
Yii::$app->session->setFlash('test_2', [
    'message'   => 'test-alert-message2',
    'ttl'       => 0,
    'showClose' => false,
    'alert_id' => 'alert-test-2',
    'type' => 'danger',
    //'class' => 'alert-danger',
    //'auto_close_callback' => 'alert(1)',
]);
*/

/* Если пользователь еще не подтвердил свой емейл */
if (!Yii::$app->user->isGuest &&
    ($user->user_status != Users::STATUS_CONFIRMED) &&
    ($user->user_closed_confirm == Users::CONFIRM_UNCLOSED)) {
        Yii::$app->session->setFlash('alert_confirm_email', [
            'message'   =>
                (
                $user->license_type == \common\models\Licenses::TYPE_FREE_TRIAL
                    ? Yii::t('app/flash-messages', 'Confirm_email_plus_trial')
                    : Yii::t('app/flash-messages', 'Confirm_email')
                ),
            'ttl'       => 0,
            'showClose' => true,
            'alert_id' => 'alert-confirm-email',
            'type' => 'danger',
        ]);
}

/* Проверка сроков лицензии */
if (!Yii::$app->user->isGuest) {

    if (!in_array($user->license_type, [Licenses::TYPE_FREE_DEFAULT, Licenses::TYPE_PAYED_BUSINESS_USER])) {

        $BonusPeriodLicense = Preferences::getValueByKey('BonusPeriodLicense', 72, 'integer') * 3600;

        /* Если у пользователя истекает лицензия */
        if (Yii::$app->session->get('alert_license_expire_soon', true)) {
            if ($user->license_type != Licenses::TYPE_FREE_TRIAL) {
                $license_expire = $user->license_expire;
            } else {

                if ($user->user_status == Users::STATUS_CONFIRMED) {
                    $BonusTrialForEmailConfirm = Preferences::getValueByKey('BonusTrialForEmailConfirm', 14, 'integer');
                    $append_period = $BonusTrialForEmailConfirm * 86400;
                } else {
                    $append_period = 0;
                }

                $BonusPeriodLicense = Preferences::getValueByKey('BonusPeriodLicense', 72, 'integer') * 3600;
                $license_expire = date(SQL_DATE_FORMAT, strtotime($user->user_created) + Licenses::getCountDaysTrialLicense() * 86400 + $append_period);
            }

            if (Licenses::checkIsExpireSoon($license_expire) && $user->pay_type != Users::PAY_CARD) {
                $expire = strtotime($license_expire);
                $now = time();
                $diff = ceil(($expire - $now) / 86400);
                if (!((isset($_GET['action']) && $_GET['action'] == "pricing") || Yii::$app->controller->action->id == 'purchase')) {
                    Yii::$app->session->setFlash('alert_license_expire_soon', [
                        'message' =>
                            Yii::t('app/flash-messages', $user->license_type == Licenses::TYPE_FREE_TRIAL ? 'Your_license_expire_soon_free' : 'Your_license_expire_soon_payed', [
                                'link' => Yii::$app->urlManager->createAbsoluteUrl($user->license_type == Licenses::TYPE_FREE_TRIAL ? "/pricing" : "/purchase/renewal"),
                                'days' => $diff,
                            ]),
                        'ttl' => 0,
                        'showClose' => true,
                        'alert_id' => 'alert-license-expire-soon',
                        'type' => 'danger',
                    ]);
                }
            }
        }

        /* Если лицензия уже истекла */
        if (Yii::$app->session->get('alert_license_expired', true)) {
            if ($user->license_expire && Licenses::checkIsExpired($user->license_expire)) {
                if (!((isset($_GET['action']) && $_GET['action'] == "pricing") || in_array(Yii::$app->controller->action->id, ['purchase', 'profile']))) {
                    Yii::$app->session->setFlash('alert_license_expired', [
                        'message' =>
                            Yii::t('app/flash-messages', 'Your_license_is_expired', [
                                'link' => Yii::$app->urlManager->createAbsoluteUrl('/user/profile?tab=2')
                            ]),
                        'ttl' => 0,
                        'showClose' => true,
                        'alert_id' => 'alert-license-expired',
                        'type' => 'error',
                    ]);
                }
            }
        }

    }
}
?>
<!-- begin #flash-tpl -->
<div id="flash-tpl" style="display: none;">

    <span id="flash-copied-ok"><?= Yii::t('app/flash-messages', "flash_copied_ok") ?></span>
    <span id="flash-share-sent-ok"><?= Yii::t('app/flash-messages', "flash_share_sent_ok") ?></span>
    <span id="flash-guest-link-sent-ok"><?= Yii::t('app/flash-messages', "flash_guest_room_link_sent_ok") ?></span>
    <span id="flash-success-changed-for-collaborate-folder"><?= Yii::t('app/flash-messages', "flash_success_changed_for_collaborate_folder") ?></span>
    <span id="flash-success-deleted-from-collaborate-folder"><?= Yii::t('app/flash-messages', "flash_success_deleted_from_collaborate_folder") ?></span>
    <span id="flash-success-added-to-collaborate-folder"><?= Yii::t('app/flash-messages', "flash_success_added_to_collaborate_folder") ?></span>
    <span id="flash-success-restored-patch"><?= Yii::t('app/flash-messages', "flash_success_restored_patch") ?></span>
    <span id="flash-conference_room_was_opened"><?= Yii::t('app/flash-messages', "flash_conference_room_was_opened") ?></span>

    <span id="flash-license-restriction"><?= Yii::t('app/flash-messages', "license_restriction") ?></span>
    <span id="flash-request-password-reset-error"><?= Yii::t('app/flash-messages', 'RequestPasswordReset_error')?></span>

    <div class="alert">
        <button type="button" class="close close-alert" data-dismiss="alert" aria-hidden="true">×</button>
        <span class="flash-message">{flash-message}</span>
    </div>

</div>
<!-- end #flash-tpl -->

<!-- begin #alert-block-container -->
<div id="alert-block-container" class="container alert-messages delta-height-div">
    <?php echo Alert::widget(); ?>
    <div id="alert-no-nodes-online" class="alert-error alert fade in hidden" style="display: none;"><?= Yii::t('app/flash-messages', 'No_Nodes_Online') ?></div>
</div>
<!-- end #alert-block-container -->


<!-- begin #alert-template -->
<div id="alert-template" style="display: none;">
    <div class="mc-snackbar">
        <div class="mc-snackbar-container mc-snackbar-container--snackbar-icon">
            <div class="mc-snackbar-icon success"></div>
            <p class="mc-snackbar-title">{alert-message}</p>
            <button class="mc-snackbar-actions mc-button-styleless mc-snackbar-close">
                <span class="mc-button-content"><?= Yii::t('app/flash-messages', 'Close') ?></span>
            </button>
        </div>
    </div>
</div>
<!-- end #alert-template -->

<!-- begin #alert-snackbar-container -->
<div class="mc-snackbar-holder-backdrop" id="alert-snackbar-container"></div>
<!-- end #alert-snackbar-container -->
