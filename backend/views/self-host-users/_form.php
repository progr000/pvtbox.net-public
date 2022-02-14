<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Users;
use common\models\SelfHostUsers;

/* @var $this yii\web\View */
/* @var $user common\models\Users */
/* @var $is_create boolean */
/* @var $form yii\widgets\ActiveForm */

/**
 * @param $user
 * @return string
 */
function showButtons($user)
{
    if (!$user->isNewRecord) {
        return
            '<div class="form-group">' .
            Html::submitButton('Change', ['class' => 'btn btn-primary']) .
            '&nbsp;' .
            Html::buttonInput('Cancel', ['name' => 'btnBack', 'class' => 'btn btn-default', 'onclick' => "history.go(-1)"]) .
            '</div>';
    }
    return "";
}
?>

<div class="users-form col-lg-6">

    <?php $form = ActiveForm::begin(); ?>

    <!-- ******************************* -->
    <?php /*$form->field($user, 'user_name')->textInput(['maxlength' => true]) */?>

    <?php
    if ($user->isNewRecord) {
        ?>
        <label>
            <input type="checkbox" name="send_email_about_registration" value="1" />
            Send email to user about it registration
        </label>
        <?php
    }
    ?>


    <!-- ******************************* -->
    <div class="delimiter first-el">Personal user info</div>

    <?= $form->field($user, 'shu_email')->textInput(['maxlength' => true]) ?>

    <?= '' /*$form->field($user, 'user_balance')->textInput()*/ ?>

    <?= $form->field($user, 'shu_status')->dropDownList(SelfHostUsers::getStatuses()) ?>

    <?= $form->field($user, 'shu_created')->textInput()
        //->label('Registration date<br /><small style="color: #FF0000;">(Has affect for the trial license.)</small>')
    ?>

    <?= showButtons($user) ?>


    <!-- ******************************* -->
    <div class="delimiter">License info</div>

    <?= $form->field($user, 'shu_business_status')->dropDownList(SelfHostUsers::getBusinessStatuses()) ?>

    <?= $form->field($user, 'shu_support_status')->dropDownList(SelfHostUsers::getSupportStatuses()) ?>

    <?= $form->field($user, 'shu_brand_status')->dropDownList(SelfHostUsers::getBusinessStatuses()) ?>

    <?= $form->field($user, 'license_count_available')->textInput(['readonly' => false]) ?>

    <?= $form->field($user, 'license_count_used')->textInput(['readonly' => true]) ?>

    <?= showButtons($user) ?>


    <!-- ******************************* -->
    <?php if ($user->isNewRecord) { ?>
    <div class="delimiter"></div>

    <div class="form-group">
        <?= Html::submitButton($user->isNewRecord ? 'Create' : 'Change', ['class' => $user->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        &nbsp;
        <a class="btn btn-default" href="/users">Cancel</a>
    </div>
    <?php } ?>


    <!-- ******************************* -->
    <?php ActiveForm::end(); ?>

</div>
