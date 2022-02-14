<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\Admins;
use common\models\Users;
use common\models\Licenses;
use common\models\Preferences;

/* @var $this yii\web\View */
/* @var $user common\models\Users */
/* @var $Admin \backend\models\Admins */
/* @var $password yii\base\DynamicModel */
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

    <?= $form->field($user, 'user_email')->textInput(['maxlength' => true]) ?>

    <?= '' /*$form->field($user, 'user_balance')->textInput()*/ ?>

    <?= $user->isNewRecord ? $form->field($password, 'password')->passwordInput() : "" ?>

    <?= $form->field($user, 'user_status')->dropDownList(Users::statusLabels()) ?>

    <?= $form->field($user, 'user_created')->textInput()->label('Registration date<br /><small style="color: #FF0000;">(Has affect for the trial license.)</small>') ?>

    <?= showButtons($user) ?>




    <?php if (!Yii::$app->params['self_hosted']) { ?>
    <!-- ******************************* -->
    <div class="delimiter">License info</div>

    <?= $form->field($user, 'license_type')->dropDownList(Licenses::licenseTypes(true), ['id'=>'user-change-form-license-type']) ?>


    <div id="license-expire-field">
    <?= $form->field($user, 'license_expire')->textInput()->label('Date when license is expire<br /><small style="color: #FF0000;">(Does not affect the trial license. Only for Pro/Business)</small>') ?>
    </div>
    <div id="license-expired-info" style="display_: none;">
        For license_type = FREE_TRIAL can't set expire date.<br />
        It depends on the date of registration<br />
        License expire at
        <?php
            $TrialDays = Licenses::getCountDaysTrialLicense();
            $BonusPeriodLicense_hours = Preferences::getValueByKey('BonusPeriodLicense', 72, 'integer');
            if ($user->user_status == Users::STATUS_CONFIRMED) {
                $BonusTrialForEmailConfirm_days = Preferences::getValueByKey('BonusTrialForEmailConfirm', 7, 'integer');
                $BonusTrialForEmailConfirm_days_text = " + bonus for email confirmation {$BonusTrialForEmailConfirm_days} days";
            } else {
                $BonusTrialForEmailConfirm_days = 0;
                $BonusTrialForEmailConfirm_days_text = "";
            }
            $bonus_plus = $TrialDays * 86400 + $BonusPeriodLicense_hours * 3600 + $BonusTrialForEmailConfirm_days * 86400;
            $expire = date(SQL_DATE_FORMAT, strtotime($user->user_created) + $bonus_plus);

            $ret = $expire . "<br /><span class=\"small\" style=\"color: #FF0000;\"> (The license is designed for {$TrialDays} days + bonus {$BonusPeriodLicense_hours} hours{$BonusTrialForEmailConfirm_days_text})</span>";
            echo $ret;
        ?>
        <br /><br />
    </div>

    <?= $form->field($user, 'license_period')->dropDownList(Licenses::licensesBilledVars(true))->label('License Period<br /><small style="color: #FF0000;">(You must set a value for Pro/Business licenses)</small>') ?>


    <div id="id_license_business_from">
    <?php
    //if ($user->license_business_from) {
        echo $form->field($user, 'license_business_from')
            ->textInput([
                //'readonly' => 'readonly',
            ])
            ->label('Current business-admin UserID <br /><small style="color: #FF0000;">(You must set a business-admin UserID for business-user license )</small>');
    //}
    ?>
    </div>

    <div id="-id_previous_license_business_from">
    <?php
    if ($user->previous_license_business_from) {
        echo $form->field($user, 'previous_license_business_from')
            ->textInput()
            ->label('Previous business-admin UserID <br /><small style="color: #FF0000;">(Don\'t touch it if don\'t know what is it)</small>');
    }
    ?>
    </div>

    <?= showButtons($user) ?>


    <!-- ******************************* -->
    <div class="delimiter">Payment info</div>

    <?= $form->field($user, 'pay_type')->dropDownList(Users::getPayTypesFilter())->label('Pay Type<br /><small style="color: #FF0000;">(You must set a value for Pro/Business licenses)</small>') ?>

    <?= $form->field($user, 'payment_already_initialized')->dropDownList([Users::PAYMENT_PROCESSED => 'Processed', Users::PAYMENT_INITIALIZED => 'Initialized', Users::PAYMENT_NOT_INITIALIZED => 'No']) ?>

    <?= showButtons($user) ?>


    <!-- ******************************* -->
    <div class="delimiter">Seller info</div>

    <?= $form->field($user, 'has_personal_seller')->dropDownList([Users::YES => 'Yes', Users::NO => 'No']) ?>

    <?php if ($user->isNewRecord) { $user->user_ref_id = $Admin->admin_id; } ?>
    <?= $form->field($user, 'user_ref_id')->textInput([
        'readonly' => ($Admin->admin_role != Admins::ROLE_ROOT),
    ])->label('Seller Id') ?>

    <?= showButtons($user) ?>


    <!-- ******************************* -->
    <div class="delimiter">Personal user limitation</div>

    <div id="enable-admin-panel-div" style="display: none;">
        <?php
        echo $form->field($user, 'enable_admin_panel')->dropDownList([
            Users::ADMIN_PANEL_ENABLE  => 'Yes',
            Users::ADMIN_PANEL_DISABLE => 'No',
        ], [
            'id' => 'enable-admin-panel',
        ]);
        ?>
    </div>

    <?= $form->field($user, 'upl_limit_nodes')
        ->textInput()
        ->label('
            Limit nodes (upl_limit_nodes):<br />
            <small style="color: #FF0000;">empty</small><small> :: defined by license;</small><br />
            <small style="color: #FF0000;">0</small><small> :: no limit;</small><br />
            <small style="color: #FF0000;">unsigned integer</small><small> :: then limit;</small><br />
            ')
    ?>

    <?= $form->field($user, 'upl_shares_count_in24')
        ->textInput()
        ->label('
            Limit count shares peer day (upl_shares_count_in24):<br />
            <small style="color: #FF0000;">empty</small><small> :: defined by license;</small><br />
            <small style="color: #FF0000;">0</small><small> :: no limit;</small><br />
            <small style="color: #FF0000;">unsigned integer</small><small> :: then limit;</small><br />
            ')
    ?>

    <?= $form->field($user, 'upl_max_shares_size')
        ->textInput()
        ->label('
            Limit share size (upl_max_shares_size):<br />
            <small style="color: #FF0000;">empty</small><small> :: defined by license;</small><br />
            <small style="color: #FF0000;">0</small><small> :: no limit;</small><br />
            <small style="color: #FF0000;">unsigned integer</small><small> :: then limit;</small><br />
            ')
    ?>

    <?= $form->field($user, 'upl_max_count_children_on_copy')
        ->textInput()
        ->label('
            Maximum children on copy (upl_max_count_children_on_copy):<br />
            <small style="color: #FF0000;">empty</small><small> :: defined by license;</small><br />
            <small style="color: #FF0000;">0</small><small> :: no limit;</small><br />
            <small style="color: #FF0000;">unsigned integer</small><small> :: then limit;</small><br />
            ')
    ?>

    <?= $form->field($user, 'upl_block_server_nodes_above_bought')
        ->dropDownList([
            "" => 'Defined by license',
            1 => 'Yes',
            0 => 'No',
        ])
        ->label('Block login from server node above bought (upl_block_server_nodes_above_bought):')
    ?>

    <?= showButtons($user) ?>

    <?php } ?>

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
