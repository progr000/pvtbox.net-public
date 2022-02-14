<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\search\PagesSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pages-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'page_id') ?>

    <?= $form->field($model, 'page_created') ?>

    <?= $form->field($model, 'page_updated') ?>

    <?= $form->field($model, 'page_status') ?>

    <?= $form->field($model, 'page_title') ?>

    <?php // echo $form->field($model, 'page_name') ?>

    <?php // echo $form->field($model, 'page_alias') ?>

    <?php // echo $form->field($model, 'page_keywords') ?>

    <?php // echo $form->field($model, 'page_description') ?>

    <?php // echo $form->field($model, 'page_text') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
