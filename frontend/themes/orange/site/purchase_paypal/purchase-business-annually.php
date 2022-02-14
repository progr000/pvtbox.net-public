<?php
/** @var \common\models\Users $User */
/** @var \frontend\models\forms\PurchaseForm $model */

use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

?>

<?php
$form = ActiveForm::begin([
    'action'  => "https://www.paypal.com/cgi-bin/webscr",
    'method' => 'post',
    'options' => [
        'id'     => "pba-form",
        'target' => "_top",
        'style'  => "text-align: center;",
    ],
]);
?>
<!-- <h3>Business annually pay form:</h3>-->
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" id="hosted-button-id-annually" value="JFZ5HCK2YWUZQ">
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
        'id'           => "pba-os1",
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
        'id'           => "pba-os2",
    ])
    ->label(false)
?>

<input type="hidden" name="on0" value="Licenses amount (server-user)">
<div class="form-group field-count-of-licenses required">
    <div class="select select-color-orange select-timezone">
        <select name="os0" id="pba-os0">
            <option data-value="3"  value="0-3">3 <?= Yii::t('app/purchase', 'licenses') ?>: $179.97 - <?= Yii::t('app/purchase', 'annually') ?></option>
            <option data-value="5"  value="0-5">5 <?= Yii::t('app/purchase', 'licenses') ?>: $299.95 - <?= Yii::t('app/purchase', 'annually') ?></option>
            <option data-value="10" value="0-10">10 <?= Yii::t('app/purchase', 'licenses') ?>: $599.89 - <?= Yii::t('app/purchase', 'annually') ?></option>
            <option data-value="15" value="0-15">15 <?= Yii::t('app/purchase', 'licenses') ?>: $899.84 - <?= Yii::t('app/purchase', 'annually') ?></option>
            <option data-value="20" value="0-20">20 <?= Yii::t('app/purchase', 'licenses') ?>: $1199.78 - <?= Yii::t('app/purchase', 'annually') ?></option>
            <option data-value="25" value="0-25">25 <?= Yii::t('app/purchase', 'licenses') ?>: $1499.73 - <?= Yii::t('app/purchase', 'annually') ?></option>
            <option data-value="30" value="0-30">30 <?= Yii::t('app/purchase', 'licenses') ?>: $1799.68 - <?= Yii::t('app/purchase', 'annually') ?></option>
        </select>
        <p class="help-block help-block-error"></p>
    </div>
</div>

<div class="form-group field-count-of-licenses required">
    <div class="select select-color-orange select-timezone">
        <select name="server_license_count" id="server-license-count-annually">
            <option data-form-id="JFZ5HCK2YWUZQ" value="0">0 <?= Yii::t('app/purchase', 'server_licenses') ?>: $0 - <?= Yii::t('app/purchase', 'annually') ?></option>
            <option data-form-id="EADDFC45SEJTG" value="1">1 <?= Yii::t('app/purchase', 'server_licenses') ?>: $799.80 - <?= Yii::t('app/purchase', 'annually') ?></option>
            <option data-form-id="WVV84PA48CB6E" value="2">2 <?= Yii::t('app/purchase', 'server_licenses') ?>: $1599.60 - <?= Yii::t('app/purchase', 'annually') ?></option>
        </select>
        <p class="help-block help-block-error"></p>
    </div>
</div>

<div class="pp-total-info" style="">
    <span class="save-sum-info" style="float: left;"><?= Yii::t('app/purchase', 'Save') ?>: $<span class="save-sum-val"></span></span>
    <span>&nbsp;</span>
    <span class="pp-span-total-info" style="float: right;"><?= Yii::t('app/purchase', 'Total') ?>: $<span class="pp-total-sum-val"></span></span>
</div>
<input type="hidden" name="currency_code" value="USD">
<!--<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_subscribeCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">-->
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
<input type="submit" name="_submit" value="<?= Yii::t('app/purchase', 'Subscribe') ?>" class="btn-default" />
<?php
ActiveForm::end();
?>
<div class="pp-cards-img">
    <p style="font-size: 8pt;"><?= Yii::t('app/purchase', 'By_clicking_Subscribe') ?></p>
    <img src="/themes/orange/images/pp-img-cards.gif" />
</div>
<span class="pp-more-licenses">Want more licenses ? <a href="<?= Url::to(['/support'], CREATE_ABSOLUTE_URL) ?>">Contact Us!</a></span>
