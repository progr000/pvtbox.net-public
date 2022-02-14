<?php
/** @var \common\models\Users $User */
?>

<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" style="text-align: center;">
    <h3>Professional TEST daily pay form:</h3>
    <input type="hidden" name="cmd" value="_s-xclick">
    <input type="hidden" name="hosted_button_id" value="K3KRFFBWQWN38">
    <input type="hidden" name="item_name" value="<?= $User->user_id ?>" />
    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_subscribeCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
    <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
