<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $model common\models\Users */
/* @var $model_changename common\models\Users */
/* @var $model_changeemail common\models\Users */
/* @var $form ActiveForm */

$this->params['breadcrumbs'][] = ['label' => 'Профиль'];
$this->params['breadcrumbs'][] = ['label' => 'Безопасность', 'url' => ['sessions']];
?>
<div class="site-profile">

    <div class="row">
        <div class="col-lg-5">

            <h1>Настройки профиля:</h1>

            <a href="#" class="profile-change-name">Изменить имя</a>
            <br />
            <a href="#" class="profile-change-email">Изменить емайл</a>
            <br />
            <a href="#" class="profile-change-password">Изменить пароль</a>

        </div>
    </div>

</div><!-- site-profile -->
<?php
$this->registerJsFile('/js/modal.profile_change.js', ['depends' => 'yii\web\JqueryAsset']);

// Modal Name
Modal::begin([
    'options' => ['id' => 'change-name-modal'],
    'closeButton' => ['id' => 'close-button-chname'],
    'header' => '<b>Смена имени:</b>',
    'size' => '',
]);
?>
    <?php $form = ActiveForm::begin(['id' => 'form-change-name', 'action'=>['profile']]); ?>
    <div class="form-group">
        <?= $form->field($model_changename, 'user_name')->textInput(['value' => Yii::$app->user->identity->user_name]) ?>

        <?= Html::submitButton('Смена имени', ['class' => 'btn btn-primary', 'name' => 'ChangeName']) ?>
    </div>
    <?php ActiveForm::end(); ?>
<?php
Modal::end();

// Modal Email
Modal::begin([
    'options' => ['id' => 'change-email-modal'],
    'closeButton' => ['id' => 'close-button-chemail'],
    'header' => '<b>Смена E-Mail:</b>',
    'size' => '',
]);
?>
    <?php $form = ActiveForm::begin(['id' => 'form-change-email', 'action'=>['profile']]); ?>
    <div class="form-group">
        <?= $form->field($model_changeemail, 'user_email', ['enableAjaxValidation' => true])->textInput(['value' => Yii::$app->user->identity->user_email]) ?>

        <?= Html::submitButton('Смена E-Mail', ['class' => 'btn btn-primary', 'name' => 'ChangeEmail']) ?>
    </div>
    <?php ActiveForm::end(); ?>
<?php
Modal::end();

// Modal Password
Modal::begin([
    'options' => ['id' => 'change-password-modal'],
    'closeButton' => ['id' => 'close-button-chpassword'],
    'header' => '<b>Смена пароля (шаг 1):</b>',
    'size' => '',
]);
?>
    <p>После нажатия на кнопку на емейл будет выслана инструкция по смене пароля:</p>
    <?php $form = ActiveForm::begin(['id' => 'form-profile', 'action'=>['profile']]); ?>
    <div class="form-group">
        <?= Html::submitButton('Смена пароля', ['class' => 'btn btn-primary', 'name' => 'ChangePasswordStep1']) ?>
    </div>
    <?php ActiveForm::end(); ?>
<?php
Modal::end();
?>
