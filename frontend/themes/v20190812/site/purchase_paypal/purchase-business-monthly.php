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
        'id'     => "pbm-form",
        'target' => "_top",
        'class' => "payment-frm",
    ],
]);
?>
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" id="hosted-button-id-monthly" value="WEEM497EM5Z8C">
<input type="hidden" name="item_name" value="<?= $User->user_id ?>" />
<input type="hidden" name="on1" value="Company name">
<input type="hidden" name="on2" value="Administrator full name">
<input type="hidden" name="on0" value="Licenses amount (server-user)">
<input type="hidden" name="currency_code" value="USD">

<div class="steps-item__choice">
    <div class="form-row-flex">
        <?= $form->field($model, 'os1')
            ->textInput([
                'placeholder'  => $model->getAttributeLabel('os1'),
                'autocomplete' => "off",
                'name'         => "os1",
                'id'           => "pbm-os1",
            ])
            ->label(false)
        ?>
        <?= $form->field($model, 'os2')
            ->textInput([
                'placeholder'  => $model->getAttributeLabel('os2'),
                'autocomplete' => "off",
                'name'         => "os2",
                'id'           => "pbm-os2",
            ])
            ->label(false)
        ?>
    </div>
    <div class="form-row-flex">
        <div class="select-wrap">
            <select class="js-select" name="os0" id="pbm-os0" aria-required="true" aria-invalid="false">
                <option data-value="3"  value="0-3">3 <?= Yii::t('app/purchase', 'licenses') ?>: $17.97 - <?= Yii::t('app/purchase', 'monthly') ?></option>
                <option data-value="5"  value="0-5">5 <?= Yii::t('app/purchase', 'licenses') ?>: $29.95 - <?= Yii::t('app/purchase', 'monthly') ?></option>
                <option data-value="10" value="0-10">10 <?= Yii::t('app/purchase', 'licenses') ?>: $59.90 - <?= Yii::t('app/purchase', 'monthly') ?></option>
                <option data-value="15" value="0-15">15 <?= Yii::t('app/purchase', 'licenses') ?>: $89.85 - <?= Yii::t('app/purchase', 'monthly') ?></option>
                <option data-value="20" value="0-20">20 <?= Yii::t('app/purchase', 'licenses') ?>: $119.80 - <?= Yii::t('app/purchase', 'monthly') ?></option>
                <option data-value="25" value="0-25">25 <?= Yii::t('app/purchase', 'licenses') ?>: $149.75 - <?= Yii::t('app/purchase', 'monthly') ?></option>
                <option data-value="30" value="0-30">30 <?= Yii::t('app/purchase', 'licenses') ?>: $179.70 - <?= Yii::t('app/purchase', 'monthly') ?></option>
            </select>
        </div>
        <div class="select-wrap">
            <select class="js-select" name="server_license_count" id="server-license-count-monthly" aria-required="true" aria-invalid="false">
                <option data-form-id="WEEM497EM5Z8C" value="0">0 <?= Yii::t('app/purchase', 'server_licenses') ?>: $0 - <?= Yii::t('app/purchase', 'monthly') ?></option>
                <option data-form-id="444HMWBZRBSY4" value="1">1 <?= Yii::t('app/purchase', 'server_licenses') ?>: $79.99 - <?= Yii::t('app/purchase', 'monthly') ?></option>
                <option data-form-id="W4EH83RCGKAJL" value="2">2 <?= Yii::t('app/purchase', 'server_licenses') ?>: $159.98 - <?= Yii::t('app/purchase', 'monthly') ?></option>
            </select>
        </div>
    </div>
</div>
<div class="payment-frm__total">
    <div class="summary"><span><?= Yii::t('app/purchase', 'Total') ?>: $</span><span class="pp-total-sum-val"></span></div>
    <div class="discount save-sum-info -js-discount-label"><span><?= Yii::t('app/purchase', 'Save') ?>: $</span><span class="save-sum-val visible"></span></div>
</div>
<div class="payment-frm__action">
    <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
    <button class="btn primary-btn payment-frm__submit" type="submit"><?= Yii::t('app/purchase', 'Subscribe') ?></button>
    <p><?= Yii::t('app/purchase', 'By_clicking_Subscribe') ?></p>
</div>
<?php
ActiveForm::end();
?>
