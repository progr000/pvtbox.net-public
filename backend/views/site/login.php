<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\models\forms\LoginForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;

$this->title = 'Login';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                <?= $form->field($model, 'admin_email')
                    ->textInput([
                        'type'         => "email",
                        'placeholder'  => "E-Mail",
                        'autocomplete' => "off",
                    ])
                    ->label(false)
                ?>

                <?= $form->field($model, 'password')
                    ->passwordInput([
                        'placeholder'  => "Password",
                        'autocomplete' => "off"
                    ])
                    ->label(false)
                ?>

                <?= $form->field($model, 'rememberMe')
                    ->checkbox()
                    ->label("Remember")
                ?>

                <a href="<?= Url::to(['/site/reset-password-request']) ?>" class="form-link reset-dialod">Reset password</a>
                <br /><br />

                <div class="form-group">
                    <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
