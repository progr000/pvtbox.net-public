<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $software array */

use frontend\assets\v20190812\downloadAsset;

/* assets */
downloadAsset::register($this);

$this->title = Yii::t('app/download', 'title');

$this->title = Yii::t('app/download', 'Download_application');
?>
<!-- begin Loading indicator -->
<div id="row-loading-indicator" class="content container div-row-download">
    <div class="small-loading" title="loading..."></div>
</div>
<!-- end Loading indicator -->
<!-- begin Download-page content -->
<div class="content container" id="is-download-page" style="display: none;">
    <h1><?= Yii::t('app/download', 'Download') ?></h1>
    <div class="download-block">
        <div class="div-row-download download-android" id="div-download-android" style="display: none;">
            <a class="download-link" href="#"><img alt="Get it on Google Play" class="img-after-loader" data-src-after="/assets/v20190812-min/images/download-image-android.png" src="/assets/v20190812-min/images/download-image-android.png" /></a>
        </div>

        <div class="div-row-download download-android" id="div-download-iphone" style="display: none;">
            <div id="create-account-button" style="display: none;">
                <?= Yii::t('app/download', 'You_will_need_a_AppName') ?>
                <a href="#" class="signup-dialog" data-toggle="modal" data-target="#entrance" data-whatever="reg" style=""><?= Yii::t('app/download', 'Create_an_account') ?></a>
            </div>
            <a class="download-link" href="#"><img alt="Get it on App Store" class="img-after-loader" data-src-after="/assets/v20190812-min/images/download-image-ios.png" src="/assets/v20190812-min/images/download-image-ios.png" /></a>
        </div>

        <div class="div-row-download" id="div-download-descktop">
            <p><?= Yii::t('app/download', 'Download_start_auto', ['version' => '']) ?></p>
            <p><?= Yii::t('app/download', 'if_not_start') ?> <a href="#" class="download-link" download="download"><?= Yii::t('app/download', 'click_here') ?></a></p>
        </div>
        <p></p>
        <p><a href="#" class="download-other-platforms"><?= Yii::t('app/download', 'other_platforms', ['APP_NAME' => Yii::$app->name]) ?>:</a></p>
        <?= $this->render('other_platforms', ['software' => $software]) ?>
    </div>
</div>
<!-- end Download-page content -->
