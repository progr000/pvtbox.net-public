<?php
/** @var \common\models\Users $User */
/** @var \frontend\models\forms\PurchaseForm $model */

use frontend\assets\v20190812\purchaseSuccessAsset;

purchaseSuccessAsset::register($this);

?>

<div style="text-align: center">
    <span class="title-min glyphicon glyphicon-ok" style="font-size: xx-large; color: #2b542c;"></span>
    <br />
    <span class="title-min">
        You have paid successfully.<br />
        Your subscription will be renewed in a few moments.
    </span>
</div>