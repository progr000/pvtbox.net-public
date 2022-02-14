<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Servers;

/* @var $this yii\web\View */
/* @var $model common\models\Servers */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="servers-form col-lg-6">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'server_title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'server_url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'server_login')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'server_password')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'server_type')->dropDownList(Servers::serverTypes()) ?>

    <?php
        //echo $form->field($model, 'server_ip')->textInput(['maxlength' => true]);

        //echo $form->field($model, 'server_port')->textInput()
    ?>

    <?= $form->field($model, 'server_status')->dropDownList(Servers::serverStatus()) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Change', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        &nbsp;
        <?= Html::buttonInput('Cancel', ['name' => 'btnBack', 'class' => 'btn btn-default', 'onclick' => "history.go(-1)"]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
