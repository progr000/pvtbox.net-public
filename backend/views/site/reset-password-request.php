<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;

$this->title = 'Reset Password';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>

            <?= $form->field($model, 'admin_email')
                ->textInput([
                    'type'         => "email",
                    'placeholder'  => "E-Mail",
                    'autocomplete' => "off",
                ])
                ->label(false)
            ?>

            <?php
            /*
            echo $form->field($model, 'reCaptcha')->widget(
                    \himiklab\yii2\recaptcha\ReCaptcha::className(),
                    ['siteKey' => Preferences::getValueByKey('reCaptchaPublicKey')])
                ->label(false);
            */
            ?>

            <div class="form-group">
                <?= Html::submitButton('Reset', ['class' => 'btn btn-primary', 'name' => 'reset-button']) ?>
                <a href="<?= Url::to('/site/login', CREATE_ABSOLUTE_URL) ?>" class="btn btn-default">Back to login</a>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
