<?php
/****DELETE-IT-FILE-IN-SH****/
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\search\SoftwareSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="download-links-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'software_id') ?>

    <?= $form->field($model, 'software_type') ?>

    <?= $form->field($model, 'software_title') ?>

    <?= $form->field($model, 'software_url') ?>

    <?= $form->field($model, 'software_file') ?>

    <?php // echo $form->field($model, 'software_version') ?>

    <?php // echo $form->field($model, 'software_created') ?>

    <?php // echo $form->field($model, 'software_status') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
