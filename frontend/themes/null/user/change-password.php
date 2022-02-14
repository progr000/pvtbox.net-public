<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Users */
/* @var $form ActiveForm */
?>
<div class="site-changepassword">

    <div class="row">
        <div class="col-lg-5">
            <h1>Смена пароля (шаг 2):</h1>
            <p>Пожалуйста заполните все данные для смены пароля:</p>

            <?php $form = ActiveForm::begin(['id' => 'form-changePassword']); ?>

                <?= $form->field($model, 'token')->label(false)->hiddenInput(['value' => Yii::$app->request->get('token')]); ?>
                <?= $form->field($model, 'old_password')->passwordInput() ?>
                <?= $form->field($model, 'new_password')->passwordInput() ?>
                <?= $form->field($model, 'repeat_password')->passwordInput() ?>

                <div class="form-group">
                    <?= Html::submitButton('Изменить пароль', ['class' => 'btn btn-primary', 'name' => 'ChangePasswordStep2']) ?>
                </div>
            <?php ActiveForm::end(); ?>

        </div>
    </div>

</div><!-- site-profile -->
