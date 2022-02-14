<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\forms\SignupForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use common\models\Preferences;

?>
<?php
$form = ActiveForm::begin([
    'id'     => "form-signup",
    'action' => Url::to(['/user/signup'], CREATE_ABSOLUTE_URL),
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

    <?php
        $def_val = Yii::$app->session->get('is_from_guest_sign', null);
        echo $form->field($model, 'user_email', ['enableAjaxValidation' => true])
                        ->textInput($def_val ? [
                 'type'         => "email",
                 'placeholder'  => $model->getAttributeLabel('user_email'),
                 'autocomplete' => "off",
                 'value'        => $def_val,
                 'aria-label'   => $model->getAttributeLabel('user_email'),
             ] : [
                 'type'         => "email",
                 'placeholder'  => $model->getAttributeLabel('user_email'),
                 'autocomplete' => "off",
                 'aria-label'   => $model->getAttributeLabel('user_email'),
             ])
             ->label(false)
    ?>

    <?=
        $form->field($model, 'password')
             ->passwordInput([
                 'placeholder'  => $model->getAttributeLabel('password'),
                 'autocomplete' => "off",
                 'aria-label'   => $model->getAttributeLabel('password'),
             ])
             ->label(false)
    ?>

    <?=
        $form->field($model, 'password_repeat')
             ->passwordInput([
                 'placeholder'  => $model->getAttributeLabel('password_repeat'),
                 'autocomplete' => "off",
                 'aria-label'   => $model->getAttributeLabel('password_repeat'),
             ])
             ->label(false)
    ?>

    <div id="signup-captcha-container" class="captcha-container">
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

    <?=
        $form->field($model, 'acceptRules', [
            'template' => "",
            'inputTemplate' => "",
            'checkboxTemplate'=>'
                <div class="form-group create-account-check" -data-toggle="buttons">
                    <label class="btn btn-checkbox active" for="accept-rules" id="label-accept-rules">
                        {input}
                        ' . Yii::t('forms/login-signup-form', 'I_have_read_accept') . '
                    </label>
                    <a class="-rules-dialod" -href="javascript:void(0)" href="' . Url::to(['/terms'], CREATE_ABSOLUTE_URL) . '" target="_blank">' . Yii::t('forms/login-signup-form', 'Terms_and_Conditions') . '</a>
                    {error}
                    {hint}
                </div>',
        ])
            ->checkbox(['id' => "accept-rules", 'value' => true, 'autocomplete' => "off", 'inputTemplate' => ""])
            ->label(false)
    ?>

    <input type="submit" name="signup-button" id="signup-button-form1" value="<?= Yii::t('forms/login-signup-form', 'signUpNow') ?>" class="btn-big signup-button" />
    <div class="img-progress" title="loading..."></div>

<?php
ActiveForm::end();
?>
