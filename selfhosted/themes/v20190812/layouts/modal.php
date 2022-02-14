<?php
/* @var $form_request_reset \frontend\models\forms\PasswordResetRequestForm */
/** @var \common\models\SelfHostUsers $user */

use yii\bootstrap\ActiveForm;
use common\models\SelfHostUsers;

$user = Yii::$app->user->identity;

if (Yii::$app->user->isGuest && isset($this->context->model_login)) {
?>
<!-- begin signup-login-modals -->
<div class="popup top-popup" id="auth-popup">
    <div class="popup__inner">
        <div class="tabs-wrap">

            <div class="tabs-content" style="margin-top: 30px;">
                <div class="box visible">

                    <?= $this->render('auth/_login', ['model' => $this->context->model_login]); ?>

                </div>
            </div>
        </div>
    </div>
    <button class="btn popup-close-btn js-close-popup" type="button" title="<?= Yii::t('app/common', "Close") ?>">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg>
    </button>
</div>
<!-- end signup-login-modals -->

<?php
}


if (Yii::$app->user->isGuest) {
?>
<!-- begin reset-password-service-modals -->
<div class="popup top-popup" id="reset-password">
    <div class="popup__inner">

        <?= $this->render('auth/_reset-password', ['form_request_reset' => $form_request_reset]); ?>

    </div>
    <button class="btn popup-close-btn js-close-popup" type="button" title="<?= Yii::t('app/common', "Close") ?>">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg>
    </button>
</div>

<div class="popup top-popup" id="reset-password-ok">
    <div class="popup__inner">

        <span class="modal-title"><?= Yii::t('forms/reset-password-form', 'Instructions_recovery_sent') ?></span>

    </div>
    <button class="btn popup-close-btn js-close-popup" type="button" title="<?= Yii::t('app/common', "Close") ?>">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg>
    </button>
</div>

<div class="popup top-popup" id="reset-password-error">
    <div class="popup__inner">

        <span class="modal-title"><?= Yii::t('forms/reset-password-form', 'Failed_reset_password') ?></span>

    </div>
    <button class="btn popup-close-btn js-close-popup" type="button" title="<?= Yii::t('app/common', "Close") ?>">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg>
    </button>
</div>
<!-- end reset-password-service-modals -->
<?php
}
?>

<?php
if (!Yii::$app->user->isGuest) {
    if ($user->shu_status != SelfHostUsers::STATUS_CONFIRMED) {
        ?>
        <!-- begin #resend-confirm-modal -->
        <div class="popup top-popup" id="resend-confirm-modal">
            <div class="popup__inner">
                <?php $form = ActiveForm::begin(['id' => 'resend-confirm-form', 'action' => '/user/resend-confirm']); ?>
                <div class="popup-form-title"><?= Yii::t('forms/resend-confirm-form', 'Confirm_email_address') ?></div>
                <input id="users-user_email"
                       type="email"
                       name="Users[user_email]"
                       value="<?= $user->shu_email ?>"
                       readonly="readonly"
                       disabled="disabled"
                       autocomplete="off"
                       aria-label="E-Mail"
                       aria-required="true" />
                <input class="btn primary-btn wide-btn"
                       type="submit"
                       value="<?= Yii::t('forms/resend-confirm-form', 'Resend_message') ?>" />
                <div class="img-progress" title="loading..."></div>
                <?php ActiveForm::end(); ?>
            </div>
            <button class="btn popup-close-btn js-close-popup" type="button" title="<?= Yii::t('app/common', "Close") ?>">
                <svg class="icon icon-close">
                    <use xlink:href="#close"></use>
                </svg>
            </button>
        </div>
        <!-- end #resend-confirm-modal -->
        <?php
    }
}
?>


<!-- begin #pretty-confirm-modal -->
<div class="popup top-popup" id="pretty-confirm-modal">
    <a class="hidden js-open-form" href="#" id="trigger-pretty-confirm-modal" data-src="#pretty-confirm-modal" data-modal="true"></a>
    <div class="popup__inner" style="text-align: center">
        <span class="modal-title" id="pretty-confirm-question-text" style="text-align: center;"><?= Yii::t('app/common', "Are_you_sure") ?></span>
        <input type="button"
               id="-button-confirm-yes"
               name="confirm-button"
               value="<?= Yii::t('app/common', 'Yes') ?>"
               class="button-confirm-yes btn primary-btn sm-btn orange-btn js-close-popup confirm-yes" />
        <input type="button"
               id="-button-confirm-no"
               name="cancel-button"
               value="<?= Yii::t('app/common', 'No') ?>"
               class="button-confirm-no btn primary-btn sm-btn white-btn js-close-popup confirm-no" />
    </div>
    <button id="confirm-close-x" class="button-confirm-no btn popup-close-btn js-close-popup" type="button" title="<?= Yii::t('app/common', "Close") ?>">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg>
    </button>
</div>
<!-- end #pretty-confirm-modal -->

<!-- begin #pretty-alert-modal -->
<div class="popup top-popup" id="pretty-alert-modal">
    <a class="hidden js-open-form" href="#" id="trigger-pretty-alert-modal" data-src="#pretty-alert-modal"></a>
    <div class="popup__inner" style="text-align: center">
        <span class="modal-title" id="pretty-alert-modal-text" style="text-align: center;"></span>
        <input type="button"
               id="-button-alert-ok"
               name="confirm-button"
               value="<?= Yii::t('app/common', 'OK') ?>"
               class="button-alert-ok btn primary-btn sm-btn orange-btn js-close-popup" />
    </div>
    <button id="alert-close-x" class="hidden button-alert-ok btn popup-close-btn js-close-popup" type="button" title="<?= Yii::t('app/common', "Close") ?>">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg>
    </button>
</div>
<!-- end #pretty-alert-modal -->

<?= $this->render('cookie_and_badge'); ?>
