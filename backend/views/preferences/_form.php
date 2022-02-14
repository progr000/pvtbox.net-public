<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Preferences;

/* @var $this yii\web\View */
/* @var $model common\models\Preferences */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="preferences-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'pref_title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'pref_key')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'pref_value')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'pref_category')->dropDownList(Preferences::categoriesLabels()) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
