<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\models\forms\ResetPasswordForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Смена пароля';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment">

    <div class="pricing__cont">

        <span class="title-min"><b>Create new password</b></span>
        <br />

        <div class="payment__block">

            <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>

                <?= $form->field($model, 'new_password')
                    ->passwordInput(['placeholder' => $model->getAttributeLabel('new_password'), 'autocomplete' => "off"])
                    ->label(false)
                ?>

                <?= $form->field($model, 'repeat_password')
                    ->passwordInput(['placeholder' => $model->getAttributeLabel('repeat_password'), 'autocomplete' => "off"])
                    ->label(false)
                ?>

                <div class="form-group">
                    <?= Html::submitButton('Change password', ['class' => 'btn-big']) ?>
                </div>

            <?php ActiveForm::end(); ?>

        </div>

    </div>

</div>
