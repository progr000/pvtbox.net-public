<?php
/* @var $form_request_reset \frontend\models\forms\PasswordResetRequestForm */

use yii\bootstrap\Modal;
use yii\bootstrap\ActiveForm;
use common\models\Preferences;
use common\models\Users;

$user = Yii::$app->user->identity;


if (Yii::$app->user->isGuest && isset($this->context->model_login)) {

    Modal::begin([
        'options' => [
            'id' => 'signup-login-modal',
        ],
        'clientOptions' => [
            'keyboard' => false,
            'backdrop' => 'static',
        ],
        //'closeButton' => ['id' => 'close-button-sl'],
        'closeButton' => false,
        'header' => '<div type="button" class="close" data-dismiss="modal" aria-label="Close" id="close-button-sl"></div>',
        'size' => '',
    ]);
    ?>
    <div class="form-block">

        <div class="form-button" data-toggle="buttons">
            <label class="btn btn-radio active" for="radio-login">
                <input type="radio" id="radio-login" name="radio-login" value="signup" autocomplete="off">
                <?= Yii::t('forms/login-signup-form', 'already_have_account', ['APP_NAME' => Yii::$app->name]) ?>
            </label>
            <label class="btn btn-radio" for="radio-signup">
                <input type="radio" id="radio-signup" name="radio-login" value="login" autocomplete="off">
                <?= Yii::t('forms/login-signup-form', 'dont_have_account', ['APP_NAME' => Yii::$app->name]) ?>
            </label>
        </div>

        <div class="form-cont">

            <div id="signup-tab">
                <?= $this->render('auth/_signup', ['model' => $form_signup]); ?>
            </div>

            <div id="login-tab" style="display: none;">
                <?= $this->render('auth/_login', ['model' => $this->context->model_login]); ?>
            </div>

            <div id="rules-tab" style="display: none;">
                <?= $this->render('auth/_rules'); ?>
            </div>

        </div>

    </div>

    <?php
    Modal::end();
}

if (Yii::$app->user->isGuest) {

    Modal::begin([
        'options' => [
            'id' => 'reset-password-modal',
        ],
        'clientOptions' => [
            'keyboard' => false,
            'backdrop' => 'static',
        ],
        //'closeButton' => ['id' => 'close-button-rc'],
        'closeButton' => false,
        'header' => '<div type="button" class="close" data-dismiss="modal" aria-label="Close"></div>',
        'size' => '',
    ]);
    ?>
    <div class="form-block">
        <?php
        $form = ActiveForm::begin([
            'id' => 'form-reset',
            'action' => '/user/request-password-reset',
            'options' => [
                //'onsubmit' => "return false",
                'class'    => "form-box active",
                'novalidate' => "novalidate",
            ],
            //'enableClientValidation' => true,
            //'validateOnSubmit' => true,
            //'enableAjaxValidation' => true,
        ]);
        ?>
        <span class="modal-title"><?= Yii::t('forms/reset-password-form', 'Reset_password') ?></span>
        <?=
        $form->field($form_request_reset, 'user_email', ['enableAjaxValidation' => false])
            ->textInput([
                'type' => "email",
                'placeholder' => $form_request_reset->getAttributeLabel('user_email'),
                'autocomplete' => "off"
            ])
            ->label(false)
        ?>

        <?php
        $cnt = Yii::$app->cache->get(Yii::$app->params['ResetPasswordCacheKey']);
        if (!$cnt) {
            $cnt = 1;
            Yii::$app->cache->set(Yii::$app->params['ResetPasswordCacheKey'], $cnt);
        }
        ?>

        <div id="reset-captcha-container" class="captcha-container">
            <?php
            if ($cnt > Preferences::getValueByKey('ResetPasswordCountNoCaptcha', 1, 'int')) {

                echo $form->field($form_request_reset, 'reCaptchaResetPassword')
                    ->widget(\himiklab\yii2\recaptcha\ReCaptcha::className(), ['siteKey' => Preferences::getValueByKey('reCaptchaPublicKey')])
                    ->label(false);

            }
            ?>
        </div>

        <input type="submit" name="reset-button" value="<?= Yii::t('forms/reset-password-form', 'Reset_password_button') ?>" class="btn-big" />
        <div class="img-progress" title="loading..."></div>

        <?php ActiveForm::end(); ?>
    </div>
    <?php
    Modal::end();


    Modal::begin([
        'options' => [
            'id' => 'request-reset-sent-modal',
        ],
        'clientOptions' => [
            'keyboard' => false,
            'backdrop' => 'static',
        ],
    //'closeButton' => ['id' => 'close-button-rc'],
        'closeButton' => false,
        'header' => '<div type="button" class="close" data-dismiss="modal" aria-label="Close"></div>',
        'size' => '',
    ]);
    ?>
    <div class="form-block">
        <span class="modal-title"><?= Yii::t('forms/reset-password-form', 'Instructions_recovery_sent') ?></span>
    </div>
    <?php
    Modal::end();


    Modal::begin([
        'options' => [
            'id' => 'request-reset-email-not-found-modal',
        ],
        'clientOptions' => [
            'keyboard' => false,
            'backdrop' => 'static',
        ],
        //'closeButton' => ['id' => 'close-button-rc'],
        'closeButton' => false,
        'header' => '<div type="button" class="close" data-dismiss="modal" aria-label="Close"></div>',
        'size' => '',
    ]);
    ?>
    <div class="form-block">
        <span class="modal-title"><?= Yii::t('forms/reset-password-form', 'Instructions_recovery_sent') ?></span>
    </div>
    <?php
    Modal::end();
}

if (!Yii::$app->user->isGuest) {
    //if ($user->user_status < Users::STATUS_CONFIRMED) {
        Modal::begin([
            'options' => [
                'id' => 'resend-confirm-modal',
            ],
            'clientOptions' => [
                'keyboard' => false,
                'backdrop' => 'static',
            ],
            //'closeButton' => ['id' => 'close-button-rc'],
            'closeButton' => false,
            'header' => '<div type="button" class="close" data-dismiss="modal" aria-label="Close"></div>',
            'size' => '',
        ]);
        ?>
        <div class="form-block">
            <?php $form = ActiveForm::begin(['id' => 'resend-confirm-form', 'action' => '/user/resend-confirm']); ?>
            <span class="modal-title"><?= Yii::t('forms/resend-confirm-form', 'Confirm_email_address') ?></span>
            <?= $form->field($user, 'user_email')
                ->textInput([
                    'type' => "email",
                    'readonly' => true,
                    'class' => 'form-control form-control-notActive',
                    'autocomplete' => "off",
                    //'value' => Yii::$app->user->identity->user_email
                ])
                ->label(false)
            ?>
            <input type="submit" name="contact-button" value="<?= Yii::t('forms/resend-confirm-form', 'Resend_message') ?>" class="btn-big" />

            <?php ActiveForm::end(); ?>
        </div>
        <?php
        Modal::end();
    //}
}
?>

<?php
Modal::begin([
    'options' => [
        'id' => 'pretty-confirm-modal',
    ],
    'clientOptions' => [
        'keyboard' => false,
        'backdrop' => 'static',
    ],
    //'closeButton' => ['id' => 'close-button-rc'],
    'closeButton' => false,
    'header' => '<br />',
    'size' => '',
]);
?>
<div class="form-block">

    <span class="modal-title" id="pretty-confirm-question-text" style="text-align: center;">Are yo sure?</span>

    <input type="button" id="button-confirm-yes" name="confirm-button" value="<?= Yii::t('app/common', 'Yes') ?>" class="btn-empty confirm-yes orange" />
    <input type="button" id="button-confirm-no" name="cancel-button" value="<?= Yii::t('app/common', 'No') ?>" class="btn-empty confirm-no" />

</div>
<?php
Modal::end();
?>

<?php
Modal::begin([
    'options' => [
        'id' => 'pretty-alert-modal',
    ],
    'clientOptions' => [
        'keyboard' => false,
        'backdrop' => 'static',
    ],
    //'closeButton' => ['id' => 'close-button-rc'],
    'closeButton' => false,
    'header' => '<br />',
    'size' => '',
]);
?>
<div class="form-block">

    <span class="modal-title" id="pretty-alert-modal-text" style="text-align: center;"></span>

    <input type="button" id="button-alert-ok" name="confirm-button" value="<?= Yii::t('app/common', 'OK') ?>" class="btn-empty confirm-yes orange" />

</div>
<?php
Modal::end();
?>

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

<div class="mc-snackbar-holder-backdrop" id="alert-snackbar-container"></div>

<?php
if (Yii::$app->user->isGuest) {
    ?>
    <div class="badge-link" id="respect-privacy-badge" data-max-width-for-show="767">
        <a class="badge-link__wrap respect-privacy-close" href="#">
            <div class="badge-link__thumb">
                <div>&nbsp;</div>
                <!--<img class="badge-link__thumb__img" src="assets/onboarding/robot-icon-frameless.svg">-->
            </div>
            <p class="badge-link__title js-badge-link__title"><?= Yii::t('app/common', 'At_private_respect_privacy', ['APP_NAME' => Yii::$app->name]); ?></p>
            <ol class="badge-link__bullets">
                <li class="badge-link__bullet"><span class="badge-link__bullet-num">1</span><?= Yii::t('app/common', 'Store_files_on_your_devices'); ?></li>
                <li class="badge-link__bullet"><span class="badge-link__bullet-num">2</span><?= Yii::t('app/common', 'Have_no_access_to_your_files'); ?></li>
                <li class="badge-link__bullet"><span class="badge-link__bullet-num">3</span><?= Yii::t('app/common', 'Never_sell_your_information'); ?></li>
            </ol>
            <div class="badge-link__btn-group">
                <span class="badge-link__btn btn btn--primary js-badge-link-button btn-default"><?= Yii::t('app/common', 'OK') ?></span>
            </div>
            <span class="ddgsi badge-link__close js-badge-link-dismiss">×</span>
        </a>
    </div>

    <div id="respect-privacy-layer" class="mod mod-cookielayer" style="display: none;">
        <div class="cookie-layer__content">
        <span class="cookie-layer__text">
            <span class="respect-privacy-layer-header"><?= Yii::t('app/common', 'At_private_respect_privacy', ['APP_NAME' => Yii::$app->name]); ?></span>
            <br />
            1. <?= Yii::t('app/common', 'Store_files_on_your_devices'); ?>
            <br />
            2. <?= Yii::t('app/common', 'Have_no_access_to_your_files'); ?>
            <br />
            3. <?= Yii::t('app/common', 'Never_sell_your_information'); ?>
        </span>
            <button class="respect-privacy-layer__button respect-privacy-close"><?= Yii::t('app/common', 'OK') ?></button>
        </div>
    </div>

    <?php
}
?>

<div id="cookie-policies-layer" class="mod mod-cookielayer" style="display: none;">
    <div class="cookie-layer__content">
        <span class="cookie-layer__text">
            <?= Yii::t('app/common', 'We_use_cookies'); ?>
        </span>
        <button class="cookie-layer__button"><?= Yii::t('app/common', 'OK') ?></button>
    </div>
</div>
