<?php

/* @var $this yii\web\View */
/* @var $code_error string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = Yii::t('app/status', 'title', ['error_name' => Yii::t('app/status', $code_error . '_name')]);
$this->registerJs(
<<<'JS'
$(document).ready(function() {
    if (typeof parent != 'undefined' && typeof parent.closeDownloadIframe != 'undefined') {
        //parent.closeDownloadIframe("Download task failure. File not available");
        //parent.closePreviewIframe();
    }
});
JS
, \yii\web\View::POS_END);

?>
<div class="features">

    <div class="features__cont">

        <div class="title">
            <h2><?= Yii::t('app/status', $code_error . '_code') ?></h2>
            <br />
            <h2><?= Yii::t('app/status', $code_error . '_name') ?></h2>
            <br />
            <h3><?= Yii::t('app/status', $code_error . '_text') ?></h3>
        </div>

    </div>

</div>
