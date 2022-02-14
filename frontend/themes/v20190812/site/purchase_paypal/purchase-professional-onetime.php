<?php
/** @var \common\models\Users $User */

use common\models\Preferences;

?>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" style="text-align: center;">
    <input type="hidden" name="cmd" value="_s-xclick">
    <input type="hidden" name="hosted_button_id" value="D6WE5AQARCWJN">
    <input type="hidden" name="item_name" value="<?= $User->user_id ?>" />

    <div class="payment-frm__total">
        <div class="summary pp-span-total-info"><span><?= Yii::t('app/purchase', 'Total') ?>: $</span><span class="pp-total-sum-val"><?= number_format(Preferences::getValueByKey('PriceOneTimeForLicenseProfessional', 99.99, 'float'), 2, '.', '') ?></span></div>
    </div>
    <div class="payment-frm__action">
        <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
        <button class="btn primary-btn payment-frm__submit" type="submit"><?= Yii::t('app/purchase', 'BuyNow') ?></button>
        <p><?= Yii::t('app/purchase', 'By_clicking_BuyNow') ?></p>
    </div>

</form>
