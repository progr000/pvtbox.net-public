<?php
/* @var $form_request_reset \frontend\models\forms\PasswordResetRequestForm */
/* @var $form_signup \frontend\models\forms\SignupForm */

use yii\bootstrap\ActiveForm;
use common\models\Users;

/** @var \common\models\Users $user */
$user = Yii::$app->user->identity;


if (Yii::$app->user->isGuest && isset($this->context->model_login)) {
?>
<!-- begin signup-login-modals -->
<div class="popup top-popup" id="auth-popup">
    <div class="popup__inner">
        <div class="tabs-wrap">
            <ul class="tabs tabs--form js-tabs">
                <li class="tabs__item active" data-current-form-id="signupform" data-replace-form-id="loginform"><span></span><span><?= Yii::t('forms/login-signup-form', 'already_have_account', ['APP_NAME' => Yii::$app->name]) ?></span></li>
                <li class="tabs__item" data-current-form-id="loginform" data-replace-form-id="signupform"><span></span><span><?= Yii::t('forms/login-signup-form', 'dont_have_account', ['APP_NAME' => Yii::$app->name]) ?></span></li>
            </ul>
            <div class="tabs-content">
                <div class="box visible">

                    <?= $this->render('auth/_login', ['model' => $this->context->model_login]); ?>

                </div>
                <div class="box">

                    <?= $this->render('auth/_signup', ['model' => $form_signup]); ?>

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


if (!Yii::$app->user->isGuest) {
    if ($user->user_status != Users::STATUS_CONFIRMED) {
        ?>
<!-- begin #resend-confirm-modal -->
<div class="popup top-popup" id="resend-confirm-modal">
    <div class="popup__inner">
        <?php $form = ActiveForm::begin([
            'id' => 'resend-confirm-form',
            'action' => '/user/resend-confirm',
            'options' => [
                'class'    => "img-progress-form",
            ],
        ]); ?>
        <div class="popup-form-title"><?= Yii::t('forms/resend-confirm-form', 'Confirm_email_address') ?></div>
        <input id="users-user_email"
               type="email"
               name="Users[user_email]"
               value="<?= $user->user_email ?>"
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
    <a class="hidden js-open-form" href="#" id="trigger-pretty-confirm-modal" data-src="#pretty-confirm-modal" data-modal="true" data-no-close-other-fancy="1"></a>
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
