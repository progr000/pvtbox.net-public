<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Tabs;

/* @var $this yii\web\View */
/* @var $model common\models\Pages */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pages-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php echo Tabs::widget([
        'items' => [
            [
                'label' => 'Page Data',
                'content' => $this->render('_form_tab_page', ['model' => $model, 'form'=>$form]),
                'active' => true
            ],
            [
                'label' => 'SEO',
                'content' => $this->render('_form_tab_seo', ['model' => $model, 'form'=>$form]),
            ],

        ],
    ]);
    ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Change', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        &nbsp;
        <?= Html::buttonInput('Cancel', ['name' => 'btnBack', 'class' => 'btn btn-default', 'onclick' => "history.go(-1)"]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
