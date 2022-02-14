<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\ServersSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="servers-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'server_id') ?>

    <?= $form->field($model, 'server_type') ?>

    <?= $form->field($model, 'server_title') ?>

    <?= $form->field($model, 'server_url') ?>

    <?= $form->field($model, 'server_ip') ?>

    <?php // echo $form->field($model, 'server_port') ?>

    <?php // echo $form->field($model, 'server_status') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
