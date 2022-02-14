<?php
/****DELETE-IT-FILE-IN-SH****/
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Software;

/* @var $this yii\web\View */
/* @var $model common\models\Software */
/* @var $form yii\widgets\ActiveForm */

$script = <<< JS
function showFileOrUrl()
{
    $('.div-file-or-url').hide();
    $('#div-software-' + $("input[name='Software[software_program_type]']:checked").val()).show();
}
$(document).ready(function(){
    $("input:radio[name='Software[software_program_type]']").change(function () {
        showFileOrUrl();
    });
    showFileOrUrl();
});
JS;
$this->registerJs($script);
if (!$model->software_program_type) {
    $model->software_program_type = Software::PROGRAM_TYPE_FILE;
}
?>

<div class="download-links-form col-lg-6">

    <?php $form = ActiveForm::begin([
        'enableAjaxValidation' => true,
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

    <?= $form->field($model, 'software_sort')->textInput() ?>

    <?= $form->field($model, 'software_type', ['enableAjaxValidation' => true])->dropDownList(Software::linkTypes(), ['onchange' => "$('#software-software_version').val('')"]) ?>

    <?= $form->field($model, 'software_description')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'software_program_type')->radioList($model->listProgramTypes())->label(false); ?>
    <div class="div-file-or-url" id="div-software-file">
        <?=
        ($model->isNewRecord
            ? ""
            : $form->field($model, 'software_file_name')->textInput(['readonly' => 'readonly'])) .
        $form->field($model, 'software_file', ['enableAjaxValidation' => false])->fileInput()
        ?>
    </div>

    <div class="div-file-or-url" id="div-software-url">
        <?= $form->field($model, 'software_url', ['enableAjaxValidation' => false])->textInput(['maxlength' => true]) ?>
    </div>

    <?= $form->field($model, 'software_version', ['enableAjaxValidation' => true])->textInput(['maxlength' => true]) ?>

    <?= $model->isNewRecord ? "" : $form->field($model, 'software_status')->dropDownList(Software::linkStatuses()) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Upload' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        &nbsp;
        <?= Html::buttonInput('Cancel', ['name' => 'btnBack', 'class' => 'btn btn-default', 'onclick' => "history.go(-1)"]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
