<?php

/* @var $this yii\web\View */
/* @var $code_error string */
/* @var $exception Exception */

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
<!-- begin Status-page content -->
<div class="content container">
    <h1 class="centered"><?= Yii::t('app/status', $code_error . '_code') ?></h1>
    <div class="page-section-description">
        <?= Yii::t('app/status', $code_error . '_name') ?>
        <br /><br />
        <?= Yii::t('app/status', $code_error . '_text') ?>
    </div>
</div>
<!-- end Status-page content -->
