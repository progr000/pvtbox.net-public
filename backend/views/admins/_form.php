<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\Admins;

/* @var $this yii\web\View */
/* @var $model backend\models\Admins */
/* @var $form yii\widgets\ActiveForm */
/* @var $current backend\models\Admins */

?>

<div class="admins-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'admin_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'admin_email')->textInput([
        'maxlength' => true,
        'readonly'  => ($model->admin_id == $current->admin_id),
    ]) ?>

    <?php
    if ($model->admin_id != $current->admin_id) {
        echo $form->field($model, 'admin_status')->dropDownList(Admins::getStatuses());

        echo $form->field($model, 'admin_role')->dropDownList(Admins::getRoles());
    }
    ?>

    <?php
    if (!$model->isNewRecord) {
        if ($model->admin_id == $current->admin_id) {

            echo $form->field($password_model, 'current_password')
                ->passwordInput([
                    'placeholder'  => "Current Password",
                    'autocomplete' => "off"
                ]);

        }
    }
    ?>

    <?= $form->field($password_model, 'password')
        ->passwordInput([
            'placeholder'  => "Password",
            'autocomplete' => "off"
        ])
    ?>
    <?php
    if (!$model->isNewRecord)
        echo '<div class="vars-helper">enter new password end retype it, if you want change it</div>';
    ?>

    <?= $form->field($password_model, 'password_repeat')
        ->passwordInput([
            'placeholder'  => "Repeat Password",
            'autocomplete' => "off"
        ])
    ?>
    <?php
    if (!$model->isNewRecord)
        echo '<div class="vars-helper">enter new password end retype it, if you want change it</div>';
    ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
