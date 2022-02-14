<?php
/****DELETE-IT-FILE-IN-SH****/
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\News;

/* @var $this yii\web\View */
/* @var $model common\models\News */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="news-form col-lg-6">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'news_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'news_text')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'news_status')->dropDownList(News::statusLabels()) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create News' : 'Change News', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        &nbsp;
        <?= Html::buttonInput('Cancel', ['name' => 'btnBack', 'class' => 'btn btn-default', 'onclick' => "history.go(-1)"]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
