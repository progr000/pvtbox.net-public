<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $downloadLink string */

//$downloadLink = Yii::$app->urlManager->createAbsoluteUrl($downloadLink);
?>
<div class="download-mobile">
    <p>Hello,</p>

    <p>Follow the link below to download and install the application:</p>

    <p><?= Html::a(Html::encode($downloadLink), $downloadLink) ?></p>
</div>
