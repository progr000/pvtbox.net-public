<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\forms\SignupForm */

use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use common\models\Preferences;

?>
<?php
$form = ActiveForm::begin([
    'id'     => "form-signup",
    'action' => Url::to(['/site/signup'], CREATE_ABSOLUTE_URL),
    'options' => [
        //'onsubmit'   => "return false",
        'class'      => "form-box active",
        'novalidate' => "novalidate",
    ],
    //'enableAjaxValidation' => true,
    //'enableClientValidation' => true,
    //'validateOnSubmit' => false,
]);
?>

    <?php /* echo $form->field($model, 'user_name')*/ ?>

    <?= $form->field($model, 'shu_company')
        ->textInput([
            'placeholder' => $model->getAttributeLabel('shu_company'),
            'autocomplete' => "off",
            'aria-label'   => $model->getAttributeLabel('shu_company'),
        ])
        ->label(false)
    ?>

    <?= $form->field($model, 'shu_name')
        ->textInput([
            'placeholder' => $model->getAttributeLabel('shu_name'),
            'autocomplete' => "off",
            'aria-label'   => $model->getAttributeLabel('shu_name'),
        ])
        ->label(false)
    ?>

    <?= $form->field($model, 'shu_email', ['enableAjaxValidation' => true])
        ->textInput([
            'id' => 'signupform-user_email',
            'placeholder' => $model->getAttributeLabel('shu_email'),
            'autocomplete' => "off",
            'aria-label'   => $model->getAttributeLabel('shu_email'),
            'readonly' => (isset($user) && isset($user->user_email)),
            'disabled' => (isset($user) && isset($user->user_email)),
        ])
        ->label(false)
    ?>

    <?= $form->field($model, 'password')
        ->passwordInput([
            'placeholder'  => $model->getAttributeLabel('password'),
            'autocomplete' => "off",
            'aria-label'   => $model->getAttributeLabel('password'),
        ])
        ->label(false)
    ?>

    <?= $form->field($model, 'password_repeat')
        ->passwordInput([
            'placeholder'  => $model->getAttributeLabel('password_repeat'),
            'autocomplete' => "off",
            'aria-label'   => $model->getAttributeLabel('password_repeat'),
        ])
        ->label(false)
    ?>

    <?= "" /*
    $form->field($model, 'shu_support_status', [
        'template' => "",
        'inputTemplate' => "",
        'options' => ['class' => "check-wrap private form-group"],
        'checkboxTemplate'=>'
                    {input}
                    <label for="shu-support-status" id="label-support-status"><span></span><span>' . Yii::t('forms/login-signup-form', 'shu_support_status') . '</span></label>
                    <div class="form-group-hint">{error}{hint}</div>',
    ])
        ->checkbox(['id' => "shu-support-status", 'value' => true, 'autocomplete' => "off", 'inputTemplate' => ""])
        ->label(false) */
    ?>

    <?= "" /*
    $form->field($model, 'shu_brand_status', [
        'template' => "",
        'inputTemplate' => "",
        'options' => ['class' => "check-wrap private form-group"],
        'checkboxTemplate'=>'
                    {input}
                    <label for="shu-brand-status" id="label-brand-status"><span></span><span>' . Yii::t('forms/login-signup-form', 'shu_brand_status') . '</span></label>
                    <div class="form-group-hint">{error}{hint}</div>',
    ])
        ->checkbox(['id' => "shu-brand-status", 'value' => true, 'autocomplete' => "off", 'inputTemplate' => ""])
        ->label(false) */
    ?>

    <?=
    $form->field($model, 'promo_code')
        ->textInput([
            'placeholder'  => $model->getAttributeLabel('promo_code') . Yii::t('forms/login-signup-form', 'if_you_have_one'),
            'autocomplete' => "off",
            'aria-label'   => $model->getAttributeLabel('promo_code'),
        ])
        ->label(false)
    ?>

    <div id="signup-captcha-container" class="captcha-container" style="margin-top: 20px;">
        <?php
        //echo $form->field($model, 'reCaptchaSignup')->widget(\himiklab\yii2\recaptcha\ReCaptcha::className())->label(false);

        $cnt = intval(Yii::$app->cache->get(Yii::$app->params['RegisterCacheKey']));
        if ($cnt > Preferences::getValueByKey('RegisterCountNoCaptcha', 1, 'int')) {

            echo $form->field($model, 'reCaptchaSignup1')
                ->widget(\himiklab\yii2\recaptcha\ReCaptcha::className(), ['siteKey' => Preferences::getValueByKey('reCaptchaPublicKey')])
                ->label(false);

        }

        ?>
    </div>

    <input type="submit" name="signup-button" id="signup-button-form1" value="<?= Yii::t('forms/login-signup-form', 'signUpNow') ?>" class="btn-big signup-button btn primary-btn wide-btn" />
    <div class="img-progress" title="loading..."></div>

<?php
ActiveForm::end();
?>