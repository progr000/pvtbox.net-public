<?php

//use yii\helpers\ArrayHelper;
//use himiklab\ckeditor\CKEditor;
/*composer require "himiklab/yii2-ckeditor-widget" : "*", */
use mihaildev\ckeditor\CKEditor;
use common\models\Languages;
use common\models\Pages;

?>

<div class="well">
    <?= $form->field($model, 'page_status')->dropDownList(Pages::statuses()) ?>

    <?= $form->field($model, 'page_lang')->dropDownList(Languages::langLabels()) ?>

    <?= $form->field($model, 'page_alias')->textInput(['maxlength' => true]) ?>
    <hr />
    <?= $form->field($model, 'page_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'page_text')->textarea(['style' => "width: 100%; height: 500px;"]) ?>
    <?php
//    echo $form->field($model, 'page_text')->widget(CKEditor::className(),[
//        'editorOptions' => [
//            'preset' => 'full', //разработанны стандартные настройки basic, standard, full данную возможность не обязательно использовать
//            'inline' => false, //по умолчанию false
//        ],
//    ])
    ?>
</div>
