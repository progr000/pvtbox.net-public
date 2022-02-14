<?php
/****DELETE-IT-FILE-IN-SH****/
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Licenses */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="licenses-form col-lg-6">

    <?php $form = ActiveForm::begin(); ?>

    <?php /* echo $form->field($model, 'license_type')->textInput(['maxlength' => true])*/ ?>

    <?= $form->field($model, 'license_description')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'license_limit_bytes')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'license_limit_days')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'license_limit_nodes')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'license_count_available')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'license_shares_count_in24')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'license_max_shares_size')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'license_max_count_children_on_copy')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'license_block_server_nodes_above_bought')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        &nbsp;
        <?= Html::buttonInput('Cancel', ['name' => 'btnBack', 'class' => 'btn btn-default', 'onclick' => "history.go(-1)"]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
