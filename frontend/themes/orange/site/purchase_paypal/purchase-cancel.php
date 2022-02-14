<?php
/** @var \common\models\Users $User */
/** @var \frontend\models\forms\PurchaseForm $model */

use yii\helpers\Url;

?>

<div style="text-align: center">
    <span class="title-min glyphicon glyphicon-ban-circle" style="font-size: xx-large; color: #A02121;"></span>
    <br />
    <span class="title-min">
        Your transaction was unsuccessful<br />
        Try again or <a href="<?= Url::to(['/support'], CREATE_ABSOLUTE_URL) ?>">contact us</a>
    </span>
</div>