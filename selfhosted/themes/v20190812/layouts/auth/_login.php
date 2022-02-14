<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\forms\LoginForm */

use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use common\models\Preferences;

?>
<?php
$form = ActiveForm::begin([
    'id' => "form-login",
    'action'  => Url::to(['/site/login'], CREATE_ABSOLUTE_URL),
    'options' => [
        //'onsubmit'   => "return false",
        'onsubmit'   => "return onSubmitLogin()",
        'class'      => "form-box active",
        'novalidate' => "novalidate",
    ],
]);
?>

    <?php
        $def_val = Yii::$app->session->get('is_from_guest_login', null);
        echo $form->field($model, 'shu_email')
             ->textInput($def_val ? [
                 'id'           => 'loginform-user_email',
                 'type'         => "email",
                 'placeholder'  => $model->getAttributeLabel('user_email'),
                 'autocomplete' => "off",
                 'value'        => $def_val,
                 'aria-label'   => $model->getAttributeLabel('user_email'),
             ] : [
                 'id'           => 'loginform-user_email',
                 'type'         => "email",
                 'placeholder'  => $model->getAttributeLabel('user_email'),
                 'autocomplete' => "off",
                 'aria-label'   => $model->getAttributeLabel('user_email'),
             ])
             ->label(false)
    ?>

    <?=
        $form->field($model, 'password'/*, ['enableAjaxValidation' => false]*/)
             ->passwordInput([
                 'placeholder'  => $model->getAttributeLabel('password'),
                 'autocomplete' => "off",
                 'aria-label'   => $model->getAttributeLabel('password'),
             ])
             ->label(false)
    ?>

    <?php
    $cnt = Yii::$app->cache->get(Yii::$app->params['LoginCacheKey']);
    if (!$cnt) {
        $cnt = 1;
        Yii::$app->cache->set(Yii::$app->params['LoginCacheKey'], $cnt);
    }
    ?>

    <div id="login-captcha-container" class="captcha-container">
        <?php

        //echo $form->field($model, 'reCaptchaLogin')->widget(\himiklab\yii2\recaptcha\ReCaptcha::className())->label(false);

        if ($cnt > Preferences::getValueByKey('LoginCountNoCaptcha', 1, 'int')) {

            echo $form->field($model, 'reCaptchaLogin')
                ->widget(\himiklab\yii2\recaptcha\ReCaptcha::className(), ['siteKey' => Preferences::getValueByKey('reCaptchaPublicKey')])
                ->label(false);
        }

        ?>
    </div>

    <?php /* echo $form->field($model, 'rememberMe')->checkbox()*/ ?>

    <div class="form-group">
        <input class="btn primary-btn wide-btn" type="submit" name="login-button" value="<?= Yii::t('forms/login-signup-form', 'signIn') ?>" />
        <div class="img-progress" title="loading..."></div>
    </div>
    <div class="centered">
        <a class="forgot-password-link form-link js-open-form" id="trigger-reset-password-dialog" href="#" data-src="#reset-password"><?= Yii::t('forms/login-signup-form', 'Remind_password') ?></a>
        <a class="forgot-password-link form-link js-open-form hidden" id="trigger-reset-password-ok-dialog" href="#" data-src="#reset-password-ok"><?= Yii::t('forms/login-signup-form', 'Remind_password') ?></a>
    </div>

<?php
ActiveForm::end();
?>
