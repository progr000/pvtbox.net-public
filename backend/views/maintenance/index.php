<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use common\models\Maintenance;

/* @var $this yii\web\View */

$this->title = 'Site Maintenance';

$btn_click_title = 'when you click on the button, all parameters from this page will be changed and not those above the button';
?>
<div class="setting-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="maintenance setting-form col-lg-6">

        <?php $form = ActiveForm::begin(); ?>

        <!--<input type="hidden" name="Maintenance[maintenance_can_login]" value="1" />-->

        <!-- ******************************* -->
        <div class="delimiter"></div>

        <?= $form->field($model, 'maintenance_suspend_site')->dropDownList([
            1 => 'Yes',
            0 => 'No',
        ]) ?>
        <div class="vars-helper">
            Site will be suspended if this field is set to <b> Yes </b>
        </div>

        <?= $form->field($model, 'maintenance_suspend_fm')->dropDownList([
            1 => 'Yes',
            0 => 'No',
        ]) ?>
        <div class="vars-helper">
            FileManager will be suspended if this field is set to <b> Yes </b>
        </div>

        <?= $form->field($model, 'maintenance_suspend_api')->dropDownList([
            1 => 'Yes',
            0 => 'No',
        ]) ?>
        <div class="vars-helper">
            Api will be suspended if this field is set to <b> Yes </b>
        </div>

        <?= $form->field($model, 'maintenance_suspend_share')->dropDownList([
            1 => 'Yes',
            0 => 'No',
        ]) ?>
        <div class="vars-helper">
            Shares will be suspended if this field is set to <b> Yes </b>
        </div>

        <?= $form->field($model, 'maintenance_suspend_blog')->dropDownList([
            1 => 'Yes',
            0 => 'No',
        ]) ?>
        <div class="vars-helper">
            Blog will be suspended if this field is set to <b> Yes </b>
        </div>

        <div class="form-group">
            <?= Html::submitButton('Set', ['class' => 'btn btn-primary masterTooltip', 'title' => $btn_click_title]) ?>
        </div>

        <!-- ******************************* -->
        <div class="delimiter first-el"></div>

        <?= $form->field($model, 'maintenance_finish')->widget(\kartik\datetime\DateTimePicker::classname(), [
            'options' => ['placeholder' => 'Enter finish time ...'],
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd hh:ii',
            ]
        ]) ?>
        <div class="vars-helper">
            Set here the date (time) when site maintenance is expected to be completed.
        </div>

        <?= $form->field($model, 'maintenance_show_empty_page')->dropDownList([
            1 => 'Yes',
            0 => 'No',
        ]) ?>
        <div class="vars-helper">
            Will be shown empty (white) page with message if this field set to <b> Yes </b>
            <br />
            It applies only to the site
        </div>

        <?= $form->field($model, 'maintenance_can_login')->dropDownList([
            1 => 'Yes',
            0 => 'No',
        ]) ?>
        <div class="vars-helper">
            Users will be logged out from the site when they try to enter the member zone,
            and will not be able to log in while the site is under maintenance ([Active]=Yes and [Can login]=No).
            <br />
            It applies only to the site
        </div>

        <div class="form-group">
            <?= Html::submitButton('Set', ['class' => 'btn btn-primary masterTooltip', 'title' => $btn_click_title]) ?>
        </div>

        <!-- ******************************* -->
        <div class="delimiter"></div>

        <?= $form->field($model, 'maintenance_type')->dropDownList(Maintenance::$array_types, [
            'class' => 'maintenance-type',
            'id' => 'maintenance-type',
        ]) ?>

        <?= $form->field($model, 'maintenance_text')->textarea([
            'maxlength' => true,
            'id' => 'maintenance-text',
            'placeholder' => 'Enter text of message about site maintenance here',
        ]) ?>
        <div class="vars-helper">
            <b>{maintenance_start}</b> -  will be replaced by date from field [Start time]<br />
            <b>{maintenance_finish}</b> -  will be replaced by date that you set in field [Finish time]<br />
            <b>{maintenance_left}</b> - will be replaced by the difference between the [Finish time] and the current time
        </div>

        <div id="example-maintenance" class="alert-success">
            Example of maintenance message. Example of maintenance message.<br />
            Example of maintenance message. Example of maintenance message.
        </div>

        <?= $form->field($model, 'maintenance_can_close')->dropDownList([
            1 => 'Yes',
            0 => 'No',
        ]) ?>
        <div class="vars-helper">
            The maintenance message will have a close button [x] if this field is set to <b>Yes</b>
        </div>

        <?= $form->field($model, 'maintenance_ttl')->textInput([]) ?>
        <div class="vars-helper">
            (time to automatically close the maintenance message, 0 :: no automatic close)
        </div>

        <!-- ******************************* -->
        <div class="delimiter"></div>

        <?= $form->field($model, 'maintenance_start')->textInput([
                'placeholder' => 'Enter start time ...',
                'readonly' => 'readonly',
        ]) ?>
        <div class="vars-helper">
            Date(time) when maintenance service start (set automatically when press button [Set])
        </div>

        <div class="form-group">
            <?= Html::submitButton('Set', ['class' => 'btn btn-primary masterTooltip', 'title' => $btn_click_title]) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
