<?php
/** @var \common\models\Users $User */

use common\models\Preferences;

?>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" style="text-align: center;">
    <!--<h3>Professional annually pay form:</h3>-->
    <input type="hidden" name="cmd" value="_s-xclick">
    <input type="hidden" name="hosted_button_id" value="SU689AX3V7RF4">
    <input type="hidden" name="item_name" value="<?= $User->user_id ?>" />
    <div class="pp-total-info" style="">
        <span class="pp-span-total-info"><?= Yii::t('app/purchase', 'Total') ?>: $<span class="pp-total-sum-val"><?= number_format(Preferences::getValueByKey('PricePerYearForLicenseProfessional', 99.99, 'float') * 12, 2, '.', '') ?></span></span>
    </div>
    <!--<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_subscribeCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">-->
    <!--<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">-->
    <input type="submit" name="_submit" value="<?= Yii::t('app/purchase', 'Subscribe') ?>" class="btn-default" />
</form>
<div class="pp-cards-img">
    <p style="font-size: 8pt;"><?= Yii::t('app/purchase', 'By_clicking_Subscribe') ?></p>
    <img src="/themes/orange/images/pp-img-cards.gif" />
</div>