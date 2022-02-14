<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $software array */

use frontend\assets\orange\downloadAsset;

/* assets */
downloadAsset::register($this);

$this->title = Yii::t('app/download', 'title');

$this->title = Yii::t('app/download', 'Download_application');
?>

<div class="anything">

    <div class="anything__cont">

        <div class="site-contact" <?= Yii::$app->user->isGuest ? '' /*'style="padding-top: 100px;"'*/ : '' ?>>



                <div class="col-12" style="text-align: center;">

                    <div id="row-loading-indicator" class="row div-row-download">
                        <div class="small-loading" title="loading..."></div>
                    </div>

                    <div class="row div-row-download" id="div-download-descktop" style="padding-top: 0px; display: none;">
                        <h4 style="color: #666666; margin-bottom: 0px;"><?= Yii::t('app/download', 'Download_start_auto', ['version' => '']) ?></h4>
                        <?= Yii::t('app/download', 'if_not_start') ?>
                        <a href="#" class="download-link"><?= Yii::t('app/download', 'click_here') ?></a>
                        <?=Yii::t('app/download', 'Load_take_time')?>
                    </div>

                    <div class="row div-row-download download-android" id="div-download-android" style="display: none;">
                        <a class="download-link" href="#"><img alt="Get it on Google Play" class="img-after-loader" data-src-after="/themes/orange/images/download-image-android.png" src="" /></a>
                    </div>

                    <div class="row div-row-download download-android" id="div-download-iphone" style="display: none;">
                        <div id="create-account-button" style="display: none;">
                            <?= Yii::t('app/download', 'You_will_need_a_AppName') ?>
                            <a href="#" class="signup-dialog" data-toggle="modal" data-target="#entrance" data-whatever="reg" style=""><?= Yii::t('app/download', 'Create_an_account') ?></a>
                            <!-- <br /><span class="btn-default signup-dialog" data-toggle="modal" data-target="#entrance" data-whatever="reg" style="margin-top: 5px;">Create an account</span>-->
                        </div>
                        <a class="download-link" href="#"><img alt="Get it on App Store" class="img-after-loader" data-src-after="/themes/orange/images/download-image-ios.png" src="" /></a>
                    </div>

                    <div id="show-other-platform" class="row -div-row-download" style="display: none; margin: 5px 0 30px 0;">
                        <a href="#" class="download-other-platforms" style=" font-weight: 600; font-size: small;" ><?= Yii::t('app/download', 'other_platforms', ['APP_NAME' => Yii::$app->name]) ?></a>
                    </div>

                    <?= $this->render('other_platforms', ['software' => $software]) ?>


                </div>

        </div>


    </div>

</div>