<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\forms\PasswordResetRequestForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\Preferences;

$this->title = 'Запрос на сброс пароля';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-request-password-reset">
    <!--
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Пожалуйста, укажите ваш E-Mail который вы использовали для регистрации.<br /> Ссылка для сброса пароля будет отправлена на ваш E-Mail.</p>
    -->

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>

                <?= $form->field($model, 'user_email') ?>

                <?= $form->field($model, 'reCaptcha')->widget(
                    \himiklab\yii2\recaptcha\ReCaptcha::className(),
                    ['siteKey' => Preferences::getValueByKey('reCaptchaPublicKey')]
                ) ?>

                <div class="form-group">
                    <?= Html::submitButton('Сбросить пароль', ['class' => 'btn btn-primary']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
