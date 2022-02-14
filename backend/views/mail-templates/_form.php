<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\MailTemplates;
use common\models\Languages;
use conquer\codemirror\CodemirrorWidget;
use conquer\codemirror\CodemirrorAsset;

/* @var $this yii\web\View */
/* @var $model common\models\MailTemplates */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="mail-templates-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'template_key')->dropDownList(MailTemplates::keyLabels()) ?>

    <?= $form->field($model, 'template_lang')->dropDownList(Languages::langLabels()) ?>

    <?= $form->field($model, 'template_from_email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'template_from_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'template_subject')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'template_body_html')->widget(
        CodemirrorWidget::className(),
        [
            'assets' => [
                CodemirrorAsset::MODE_XML,
                CodemirrorAsset::ADDON_SEARCH_MATCH_HIGHLIGHTER,
                CodemirrorAsset::KEYMAP_EMACS,
                CodemirrorAsset::ADDON_COMMENT,
                CodemirrorAsset::ADDON_DIALOG,
                CodemirrorAsset::ADDON_SEARCHCURSOR,
                CodemirrorAsset::ADDON_SEARCH,
            ],
            'settings' => [
                'lineNumbers' => true,
                'mode' => 'text/html',
                'keyMap' => 'emacs',
            ],
        ]
    )//->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'template_body_text')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Изменить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        &nbsp;
        <?= Html::buttonInput('Отмена', ['name' => 'btnBack', 'class' => 'btn btn-default', 'onclick' => "history.go(-1)"]) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <div class="legend">
        <b>Варианты замены в шаблонах:</b>
        <br />
        <br />

        <ul>
            <li>{{app_name}} - Название сайта</li>
            <li>{{download-app-url}} - Ссылка на скачивание приложения</li>
            <li>{{confirm-registration-url}} - Ссылка для подтверждения регистрации</li>
            <li>{{change-password-url}} - Ссылка для смены пароля</li>
            <li>{{reset-password-url}} - Ссылка для сброса пароля</li>
            <li>{{user_email}} - E-Mail зарегистрированного пользоватея</li>
            <li>{{user_name}} - Имя зарегистрированного пользователя</li>
            <li>{{share_link}} - ссылка на шару, которую пользователь отправляет по емейлу</li>
        </ul>
    </div>
</div>
