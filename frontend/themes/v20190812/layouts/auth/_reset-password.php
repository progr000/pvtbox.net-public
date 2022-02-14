<?php
/* @var $form_request_reset \frontend\models\forms\PasswordResetRequestForm */

use yii\bootstrap\ActiveForm;
use common\models\Preferences;

$form = ActiveForm::begin([
    'id' => 'form-reset',
    'action' => '/user/request-password-reset',
    'options' => [
        //'onsubmit' => "return false",
        'class'    => "form-box active img-progress-form",
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
        'autocomplete' => "off",
        'aria-label'   => $form_request_reset->getAttributeLabel('user_email'),
    ])
    ->label(false)
?>

<?php
$reCaptchaPublicKey = Preferences::getValueByKey('reCaptchaPublicKey');
$cnt = Yii::$app->cache->get(Yii::$app->params['ResetPasswordCacheKey']);
if (!$cnt) {
    $cnt = 1;
    Yii::$app->cache->set(Yii::$app->params['ResetPasswordCacheKey'], $cnt);
}
if (!$reCaptchaPublicKey) {
    $cnt = 1;
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

<input type="submit" name="reset-button" value="<?= Yii::t('forms/reset-password-form', 'Reset_password_button') ?>" class="btn primary-btn wide-btn" />
<div class="img-progress" title="loading..."></div>

<?php ActiveForm::end(); ?>
