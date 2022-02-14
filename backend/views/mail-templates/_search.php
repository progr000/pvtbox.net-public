<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\search\MailTemplatesSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="mail-templates-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'template_id') ?>

    <?= $form->field($model, 'template_key') ?>

    <?= $form->field($model, 'template_lang') ?>

    <?= $form->field($model, 'template_from_email') ?>

    <?= $form->field($model, 'template_from_name') ?>

    <?php // echo $form->field($model, 'template_subject') ?>

    <?php // echo $form->field($model, 'template_body_html') ?>

    <?php // echo $form->field($model, 'template_body_text') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
