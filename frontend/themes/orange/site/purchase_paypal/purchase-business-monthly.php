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
        'id'     => "pbm-form",
        'target' => "_top",
        'style'  => "text-align: center;",
    ],
]);
?>
<!--<h3>Business monthly pay form:</h3>-->
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" id="hosted-button-id-monthly" value="WEEM497EM5Z8C">
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
        'id'           => "pbm-os1",
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
        'id'           => "pbm-os2",
    ])
    ->label(false)
?>

<input type="hidden" name="on0" value="Licenses amount (server-user)">
<div class="form-group field-count-of-licenses required">
    <div class="select select-color-orange select-timezone">
        <select name="os0" id="pbm-os0">
            <option data-value="3"  value="0-3">3 <?= Yii::t('app/purchase', 'licenses') ?>: $17.97 - <?= Yii::t('app/purchase', 'monthly') ?></option>
            <option data-value="5"  value="0-5">5 <?= Yii::t('app/purchase', 'licenses') ?>: $29.95 - <?= Yii::t('app/purchase', 'monthly') ?></option>
            <option data-value="10" value="0-10">10 <?= Yii::t('app/purchase', 'licenses') ?>: $59.90 - <?= Yii::t('app/purchase', 'monthly') ?></option>
            <option data-value="15" value="0-15">15 <?= Yii::t('app/purchase', 'licenses') ?>: $89.85 - <?= Yii::t('app/purchase', 'monthly') ?></option>
            <option data-value="20" value="0-20">20 <?= Yii::t('app/purchase', 'licenses') ?>: $119.80 - <?= Yii::t('app/purchase', 'monthly') ?></option>
            <option data-value="25" value="0-25">25 <?= Yii::t('app/purchase', 'licenses') ?>: $149.75 - <?= Yii::t('app/purchase', 'monthly') ?></option>
            <option data-value="30" value="0-30">30 <?= Yii::t('app/purchase', 'licenses') ?>: $179.70 - <?= Yii::t('app/purchase', 'monthly') ?></option>
        </select>
        <p class="help-block help-block-error"></p>
    </div>
</div>

<!--<h3 style="float: left;"><span>4</span> dsdsdsdsdsd</h3>-->
<div class="form-group field-count-of-licenses required">
    <div class="select select-color-orange select-timezone">
        <select name="server_license_count" id="server-license-count-monthly">
            <option data-form-id="WEEM497EM5Z8C" value="0">0 <?= Yii::t('app/purchase', 'server_licenses') ?>: $0 - <?= Yii::t('app/purchase', 'monthly') ?></option>
            <option data-form-id="444HMWBZRBSY4" value="1">1 <?= Yii::t('app/purchase', 'server_licenses') ?>: $79.99 - <?= Yii::t('app/purchase', 'monthly') ?></option>
            <option data-form-id="W4EH83RCGKAJL" value="2">2 <?= Yii::t('app/purchase', 'server_licenses') ?>: $159.98 - <?= Yii::t('app/purchase', 'monthly') ?></option>
        </select>
        <p class="help-block help-block-error"></p>
    </div>
</div>

<div class="pp-total-info" style="">
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
