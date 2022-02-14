<?php
/** @var \common\models\Users $User */
/** @var \frontend\models\forms\PurchaseForm $model */

use yii\bootstrap\ActiveForm;

?>

<?php
$form = ActiveForm::begin([
    'action'  => "https://www.paypal.com/cgi-bin/webscr",
    'method' => 'post',
    'options' => [
        'id'     => "pbd-form",
        'target' => "_top",
        'style'  => "text-align: center;",
        //'accept-charset' => Yii::$app->charset,
        //'accept-charset' => "ISO-8859-1",
    ],
]);
?>
<h3>Business TEST daily pay form:</h3>
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="WJD38YY7KLZYN">
<input type="hidden" name="item_name" value="<?= $User->user_id ?>" />

<input type="hidden" name="on1" value="Company name">
<!--
company_name
<input type="text" name="____os1" maxlength="200">
-->
<?php
echo $form->field($model, 'os1')
    ->textInput([
        'placeholder'  => $model->getAttributeLabel('os1'),
        'autocomplete' => "off",
        'name'         => "os1",
        'id'           => "pbd-os1",
    ])
    ->label(false)
?>

<input type="hidden" name="on2" value="Administrator full name">
<!--
admin_email
<input type="text" name="____os2" maxlength="200">
-->
<?php
echo $form->field($model, 'os2')
    ->textInput([
        'placeholder'  => $model->getAttributeLabel('os2'),
        'autocomplete' => "off",
        'name'         => "os2",
        'id'           => "pbd-os2",
    ])
    ->label(false)
?>

<input type="hidden" name="on0" value="Licenses amount">
<div class="form-group field-count-of-licenses required">
    <div class="select select-color-orange select-timezone">
        <select name="os0" id="pbd-os0">
            <option value="5">5 licenses: $0.15 USD - daily</option>
            <option value="10">10 licenses: $0.25 USD - daily</option>
            <option value="20">20 licenses: $0.45 USD - daily</option>
            <option value="50">50 licenses: $0.90 USD - daily</option>
        </select>
        <p class="help-block help-block-error"></p>
    </div>
</div>

<!-- Set recurring payments until canceled. -->
<input type="hidden" name="src" value="1">
<!-- PayPal reattempts failed recurring payments. -->
<input type="hidden" name="sra" value="1">

<input type="hidden" name="currency_code" value="USD">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_subscribeCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
<?php
ActiveForm::end();
?>
