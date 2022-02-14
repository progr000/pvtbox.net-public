<?php

/* @var $this yii\web\View */
/* @var $model_signup2 \frontend\models\forms\SignupForm2 */
/* @var $software array */
/* @var $traf array */

use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use common\helpers\Functions;
use common\models\Preferences;
use frontend\assets\v20190812\indexGuestAsset;
use frontend\assets\v20190812\trafAsset;
use frontend\assets\v20190812\animationAsset;
use frontend\assets\v20190812\downloadAsset;

$this->title = Yii::t('app/index', 'title');

indexGuestAsset::register($this);
trafAsset::register($this);
$no_show_animation_on_index = (isset(Yii::$app->params['no_show_animation_on_index']) && Yii::$app->params['no_show_animation_on_index']);
$no_show_video_index = (isset(Yii::$app->params['no_show_video_index']) && Yii::$app->params['no_show_video_index']);
if (!$no_show_animation_on_index) {
    //animationAsset::register($this);
}
downloadAsset::register($this);
?>
<!-- begin Index-page content -->

<!-- begin top-section -->
<div class="container">
    <!--begin .promo-->
    <div class="promo">
        <div class="promo__inner">
            <div class="promo__text">
                <div class="promo__title" style="text-align: center"><?= Yii::t('app/index', 'home_info1') ?></div>
                <div class="download-buttons-container">
                    <p><?= Yii::t('app/index', 'home_info2') ?></p>
                    <p class="small-version"><?= Yii::t('app/index', 'home_info3') ?></p>
                    <a class="btn action-btn wide-btn saas-btn download-link-" href="<?= Url::to(['/pricing', 'type' => 'saas'], CREATE_ABSOLUTE_URL) ?>">
                        <span><?= Yii::t('app/index', 'SaaS_version') ?></span>
                    </a>
                    <a class="btn action-btn wide-btn self-host-btn download-link-" href="<?= Url::to(['/pricing', 'type' => 'self'], CREATE_ABSOLUTE_URL) ?>">
                        <span><?= Yii::t('app/index', 'Self_hosted_version') ?></span>
                    </a>
                </div>
                <?php if (false) { ?>
                <div id="create-account-button-ios" class="create-account-ios" style="display: none;">
                    <?= Yii::t('app/download', 'You_will_need_a_AppName') ?>
                    <a href="#" class="signup-dialog" data-toggle="modal" data-target="#entrance" data-whatever="reg" style=""><?= Yii::t('app/download', 'Create_an_account') ?></a>
                </div>
                <?php } ?>
                <div style="display: none;">
                    <?= $this->render('/download/other_platforms', ['software' => $software]) ?>
                </div>
                <ul class="platforms">
                    <li class="animated-item animated-now" title="MacOs &amp; iOS">
                        <svg class="icon icon-system-ios">
                            <use xlink:href="#system-ios"></use>
                        </svg>
                    </li>
                    <li class="animated-item animated-now" title="Windows">
                        <svg class="icon icon-system-windows">
                            <use xlink:href="#system-windows"></use>
                        </svg>
                    </li>
                    <li class="animated-item animated-now" title="Android">
                        <svg class="icon icon-system-android">
                            <use xlink:href="#system-android"></use>
                        </svg>
                    </li>
                    <li class="animated-item animated-now" title="Linux">
                        <svg class="icon icon-system-unix">
                            <use xlink:href="#system-unix"></use>
                        </svg>
                    </li>
                </ul>


            </div>
            <div class="promo__media">
                <picture>
                    <source type="image/webp" srcset="/assets/v20190812-min/images/soft.webp">
                    <source type="image/jpeg" srcset="/assets/v20190812-min/images/soft.png">
                    <img src="/assets/v20190812-min/images/soft.png" alt="">
                </picture>
            </div>
        </div>
    </div>
    <div class="promo__media-mobile">
        <picture>
            <source type="image/webp" srcset="/assets/v20190812-min/images/soft.webp">
            <source type="image/jpeg" srcset="/assets/v20190812-min/images/soft.png">
            <img src="/assets/v20190812-min/images/soft.png" alt="">
        </picture>
    </div>
    <!--end .promo-->
</div>
<!-- end top-section -->


<!-- begin Pvtbox_private_cloud -->
<div class="private-cloud bg-section other-sections">
    <div class="container centered">
        <div class="centered">
            <picture>
                <source srcset="/assets/v20190812-min/images/private-cloud.svg" media="(max-width: 840px)"><img src="/assets/v20190812-min/images/private-cloud.svg" alt="<?= Yii::$app->name ?>" />
            </picture>

            <h2 class="page-section-title"><?= Yii::t('app/index', 'Pvtbox_private_cloud') ?></h2>
            <div class="ppc-block-wrap">
                <?= Yii::t('app/index', 'Pvtbox_private_cloud_text') ?>
            </div>

            <div class="ppc-link">
                <a class="btn action-btn wide-btn saas-btn download-link-" href="<?= Url::to(['/pricing', 'type' => 'saas'], CREATE_ABSOLUTE_URL) ?>">
                    <span><?= Yii::t('app/index', 'SaaS_version') ?></span>
                </a>
                <a class="btn action-btn wide-btn self-host-btn download-link-" href="<?= Url::to(['/pricing', 'type' => 'self'], CREATE_ABSOLUTE_URL) ?>">
                    <span><?= Yii::t('app/index', 'Self_hosted_version') ?></span>
                </a>
            </div>
        </div>
    </div>
</div>
<!-- end Pvtbox_private_cloud -->

<!-- begin The_best_at_its_best -->
<div class="private-cloud bg-section other-sections open-source-section">
    <div class="container centered">
        <div class="centered">

            <picture>
                <source srcset="/assets/v20190812-min/images/muscle-arm.svg" media="(max-width: 840px)"><img src="/assets/v20190812-min/images/muscle-arm.svg" alt="<?= Yii::t('app/index', 'The_best_at_its_best') ?>" />
            </picture>

            <h2 class="page-section-title"><?= Yii::t('app/index', 'The_best_at_its_best') ?></h2>
            <div class="ppc-block-wrap">
                <?= Yii::t('app/index', 'The_best_at_its_best_text') ?>
            </div>

        </div>
    </div>
</div>
<!-- end The_best_at_its_best -->

<!-- begin Focus_on_high_speed -->
<div class="private-cloud bg-section other-sections">
    <div class="container centered">
        <div class="centered">

            <svg class="icon icon-high-speed">
                <use xlink:href="#high-speed"></use>
            </svg>

            <h2 class="page-section-title"><?= Yii::t('app/index', 'Focus_on_high_speed') ?></h2>
            <div class="ppc-block-wrap">
                <?= Yii::t('app/index', 'Focus_on_high_speed_text') ?>
            </div>

        </div>
    </div>
</div>
<!-- end Focus_on_high_speed -->


<?php if (!$no_show_animation_on_index) { ?>
    <!-- begin Peer2peerImage -->
    <div class="p2p-animation bg-section other-sections">
        <h2 class="page-section-title"><?= Yii::t('app/index', 'Peer2peerImage') ?></h2>
        <div class="video-p2p"
             id="animation_container"
             data-mp4="/assets/v20190812-min/videos/p2p.mp4"
             data-webm="/assets/v20190812-min/videos/p2p.webm">
            <video id="video-p2p-object"
                   autoplay=""
                   loop=""
                   muted=""
                   playsinline=""
                   webkit-playsinline=""
                   preload="none"
                   src=""></video>
        </div>
    </div>
    <!-- end Peer2peerImage -->
<?php } ?>


<!-- begin Quick_setup_and_Affordable_solution -->
<div class="bg-section other-sections other-sections-for-pc">
    <div class="container">
        <div class="control-scheme">
            <table class="compare-table">
                <thead>
                <tr>
                    <th class="centered">
                        <picture>
                            <source srcset="/assets/v20190812-min/images/clock.svg" media="(max-width: 840px)"><img src="/assets/v20190812-min/images/clock.svg" alt="clock" />
                        </picture>
                    </th>
                    <th class="centered">
                        <picture>
                            <source srcset="/assets/v20190812-min/images/purse.svg" media="(max-width: 840px)"><img src="/assets/v20190812-min/images/purse.svg" alt="purse" />
                        </picture>
                    </th>
                </tr>
                <tr>
                    <th class="centered"><?= Yii::t('app/index', 'Quick_and_easy_to_set_up') ?></th>
                    <th class="centered"><?= Yii::t('app/index', 'Convenient_and_Affordable') ?></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <?= Yii::t('app/index', 'Quick_and_easy_to_set_up_text') ?>
                    </td>
                    <td>
                        <?= Yii::t('app/index', 'Convenient_and_Affordable_text') ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="bg-section other-sections other-sections-for-mobile">
    <div class="container centered">
        <div class="centered">

            <picture>
                <source srcset="/assets/v20190812-min/images/clock.svg" media="(max-width: 840px)"><img src="/assets/v20190812-min/images/clock.svg" alt="clock" />
            </picture>

            <h2 class="page-section-title"><?= Yii::t('app/index', 'Quick_and_easy_to_set_up') ?></h2>
            <div class="ppc-block-wrap">
                <?= Yii::t('app/index', 'Quick_and_easy_to_set_up_text') ?>
            </div>

        </div>
    </div>
</div>
<div class="bg-section other-sections other-sections-for-mobile">
    <div class="container centered">
        <div class="centered">

            <picture>
                <source srcset="/assets/v20190812-min/images/purse.svg" media="(max-width: 840px)"><img src="/assets/v20190812-min/images/purse.svg" alt="purse" />
            </picture>

            <h2 class="page-section-title"><?= Yii::t('app/index', 'Convenient_and_Affordable') ?></h2>
            <div class="ppc-block-wrap">
                <?= Yii::t('app/index', 'Convenient_and_Affordable_text') ?>
            </div>

        </div>
    </div>
</div>
<!-- end Quick_setup_and_Affordable_solution -->


<!-- begin TRAF -->
<div class="page-section other-sections">
    <div class="container centered">
        <div class="centered">

            <h2 class="page-section-title"><?= Yii::t('app/index', 'Transferred via Pvtbox') ?></h2>
            <div class="">

                <div class="traf-info" id="traf-info-check" data-checksum="<?= $traf['checksum'] ?>">

                            <div class="odometer-theme-example odometer-today">
                                <div id="traf-today"
                                     class="od-inline"
                                     -class="odometer odometer-theme-car odometer-animating-up"
                                     data-traf-calc="<?= $traf['today_amount_prev'] ?>"
                                     data-traf-prev="<?= $traf['today_amount_prev'] ?>"
                                     data-traf-current="<?= $traf['today_amount_current'] ?>"
                                     data-traf-interval="<?= $traf['time_interval'] ?>"><?= Functions::file_size_format($traf['today_amount_prev']*1024*1024, 1, 'MB', '', true) ?></div>
                                <div id="traf-today-power" class="od-inline">MB</div>
                                <p class="od-block">Today</p>
                            </div>

                            <div class="odometer-theme-example odometer-month">
                                <div id="traf-month"
                                     class="od-inline"
                                     -class="odometer odometer-theme-car odometer-animating-up"
                                     data-traf-calc="<?= $traf['month_amount_prev'] ?>"
                                     data-traf-prev="<?= $traf['month_amount_prev'] ?>"
                                     data-traf-current="<?= $traf['month_amount_current'] ?>"
                                     data-traf-interval="<?= $traf['time_interval'] ?>"><?= Functions::file_size_format($traf['month_amount_prev']*1024*1024, 3, 'GB', '', true) ?></div>
                                <div id="traf-month-power" class="od-inline">GB</div>
                                <p class="od-block">This month</p>
                            </div>

                            <div class="odometer-theme-example odometer-total">
                                <div id="traf-total"
                                     class="od-inline"
                                     -class="odometer odometer-theme-train-station odometer-animating-up"
                                     data-traf-calc="<?= $traf['total_amount_prev'] ?>"
                                     data-traf-prev="<?= $traf['total_amount_prev'] ?>"
                                     data-traf-current="<?= $traf['total_amount_current'] ?>"
                                     data-traf-interval="<?= $traf['time_interval'] ?>"><?= Functions::file_size_format($traf['total_amount_prev']*1024*1024, 3, 'GB', '', true) ?></div>
                                <div id="traf-total-power" class="od-inline">GB</div>
                                <p class="od-block">All time</p>
                            </div>

                </div>

            </div>

        </div>
    </div>
</div>
<!-- end TRAF -->


<!-- begin phone-video -->
<div class="bg-section other-sections">
    <div class="container centered">
        <h2 class="page-section-title"><?= Yii::t('app/index', 'Mobile_ready') ?></h2>

        <div class="video-gif">
            <video id="video-gif-object"
                   autoplay=""
                   loop=""
                   muted=""
                   playsinline=""
                   webkit-playsinline=""
                   preload="none"
                   src="/assets/v20190812-min/videos/video-gif.mp4"></video>
        </div>
        <div class="description-video-gif">
            <div>
                <?= Yii::t('app/index', 'Mobile_ready_abz1') ?>
                <br /><br />
                <?= Yii::t('app/index', 'Mobile_ready_abz2') ?>
                <br /><br />
                <?= Yii::t('app/index', 'Mobile_ready_abz3') ?>
                <br /><br />
                <?= Yii::t('app/index', 'Mobile_ready_abz4') ?>
            </div>
        </div>
    </div>
</div>
<!-- end phone-video -->


<!-- begin Smart-work-on-any-platform -->
<div class="private-cloud bg-section other-sections smart-section">
    <div class="container centered">
        <div class="centered">
            <picture>
                <source srcset="/assets/v20190812-min/images/smart_platform_icon.png" media="(max-width: 840px)"><img src="/assets/v20190812-min/images/smart_platform_icon.png" alt="smart" />
            </picture>

            <h2 class="page-section-title"><?= Yii::t('app/index', 'Smart_work_on_any_platform') ?></h2>
            <div class="ppc-block-wrap">
                <div class="smart-right">
                    <img src="/assets/v20190812-min/images/smart_work_on_any_platform.png" alt="" />
                </div>
                <div class="smart-left">
                    <div>
                        <?= Yii::t('app/index', 'Smart_work_on_any_platform_abz1') ?>
                        <br /><br />
                        <?= Yii::t('app/index', 'Smart_work_on_any_platform_abz2') ?>
                        <br /><br />
                        <?= Yii::t('app/index', 'Smart_work_on_any_platform_abz3') ?>
                        <br /><br />
                        <?= Yii::t('app/index', 'Smart_work_on_any_platform_abz4') ?>
                    </div>
                </div>
            </div>

            <div class="ppc-link">
                <a class="btn action-btn wide-btn saas-btn download-link-" href="<?= Url::to(['/pricing', 'type' => 'saas'], CREATE_ABSOLUTE_URL) ?>">
                    <span><?= Yii::t('app/index', 'SaaS_version') ?></span>
                </a>
                <a class="btn action-btn wide-btn self-host-btn download-link-" href="<?= Url::to(['/pricing', 'type' => 'self'], CREATE_ABSOLUTE_URL) ?>">
                    <span><?= Yii::t('app/index', 'Self_hosted_version') ?></span>
                </a>
            </div>
        </div>
    </div>
</div>
<!-- end Smart-work-on-any-platform -->


<?php if (!$no_show_video_index) { ?>
<!-- begin Youtube -->
<div class="promo-video bg-section other-sections">
    <div class="container">
        <h2 class="page-section-title"><?= Yii::t('app/index', 'Video_Presentation') ?></h2>
        <div class="video-wrap centered">
            <div class="youtube" id="mh3mxcRQvAc" data-params="modestbranding=0&amp;showinfo=0&amp;vq=hd720"></div>
        </div>
    </div>
</div>
<!-- end Youtube -->
<?php } ?>


<?php if (false) { ?>
<!-- begin Benefits -->
<div class="benefits-section page-section other-sections">
    <div class="container">
        <h2 class="page-section-title"><?= Yii::t('app/index', 'What_benefits') ?></h2>
        <div class="benefits">
            <div class="benefits-item">
                <div class="benefits-item__icon-wrap">
                    <svg class="icon icon-confidentiality">
                        <use xlink:href="#confidentiality"></use>
                    </svg>
                </div>
                <div class="benefits-item__body">
                    <div class="benefits-item__title"><?= Yii::t('app/index', 'Confidentiality') ?></div>
                    <div class="benefits-item__desc" id="confidentiality-text-label"><?= Yii::t('app/index', 'Confidentiality_text') ?></div><!--
                 --><a class="benefits-item__link"
                       aria-labelledby="confidentiality-text-label"
                       aria-describedby="confidentiality-text-label"
                       aria-label="Read more about confidentiality"
                       href="<?= Url::to(['/features#confidentiality'], CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('app/index', 'More_info') ?></a><!--
             --></div>
            </div>
            <div class="benefits-item">
                <div class="benefits-item__icon-wrap">
                    <svg class="icon icon-security">
                        <use xlink:href="#security"></use>
                    </svg>
                </div>
                <div class="benefits-item__body">
                    <div class="benefits-item__title"><?= Yii::t('app/index', 'Security') ?></div>
                    <div class="benefits-item__desc" id="security-text-label"><?= Yii::t('app/index', 'Security_text') ?></div><!--
                 --><a class="benefits-item__link"
                       aria-labelledby="security-text-label"
                       aria-describedby="security-text-label"
                       aria-label="Read more about security"
                       href="<?= Url::to(['/features#security'], CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('app/index', 'More_info') ?></a><!--
             --></div>
            </div>
            <div class="benefits-item">
                <div class="benefits-item__icon-wrap">
                    <svg class="icon icon-high-speed">
                        <use xlink:href="#high-speed"></use>
                    </svg>
                </div>
                <div class="benefits-item__body">
                    <div class="benefits-item__title"><?= Yii::t('app/index', 'High_Speed') ?></div>
                    <div class="benefits-item__desc" id="speed-text-label"><?= Yii::t('app/index', 'High_Speed_text') ?></div><!--
                 --><a class="benefits-item__link"
                       aria-labelledby="speed-text-label"
                       aria-describedby="speed-text-label"
                       aria-label="Read more about speed"
                       href="<?= Url::to(['/features#speed'], CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('app/index', 'More_info') ?></a><!--
             --></div>
            </div>
        </div>
    </div>
</div>
<!-- end Benefits -->
<?php } ?>


<!-- begin Advantages_of_peer_to_peer -->
<div class="bg-section other-sections">
    <div class="container">
        <h2 class="page-section-title"><?= Yii::t('app/index', 'Advantages_of_peer_to_peer') ?></h2>
        <div class="control-scheme">
            <table class="compare-table">
                <thead>
                <tr>
                    <th><?= Yii::t('app/index', 'Client_Server_Architecture') ?></th>
                    <th><?= Yii::t('app/index', 'Peer_to_Peer_Architecture') ?></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <ul>
                            <li><?= Yii::t('app/index', 'Client_Server_1') ?></li>
                            <li><?= Yii::t('app/index', 'Client_Server_2') ?></li>
                            <li><?= Yii::t('app/index', 'Client_Server_3') ?></li>
                            <li><?= Yii::t('app/index', 'Client_Server_4') ?></li>
                            <li><?= Yii::t('app/index', 'Client_Server_5') ?></li>
                            <li><?= Yii::t('app/index', 'Client_Server_6') ?></li>
                            <li><?= Yii::t('app/index', 'Client_Server_7') ?></li>
                            <li><?= Yii::t('app/index', 'Client_Server_8') ?></li>
                            <li><?= Yii::t('app/index', 'Client_Server_9') ?></li>
                        </ul>
                    </td>
                    <td>
                        <ul>
                            <li><?= Yii::t('app/index', 'Peer_to_Peer_1') ?></li>
                            <li><?= Yii::t('app/index', 'Peer_to_Peer_2') ?></li>
                            <li><?= Yii::t('app/index', 'Peer_to_Peer_3') ?></li>
                            <li><?= Yii::t('app/index', 'Peer_to_Peer_4') ?></li>
                            <li><?= Yii::t('app/index', 'Peer_to_Peer_5') ?></li>
                            <li><?= Yii::t('app/index', 'Peer_to_Peer_6') ?></li>
                            <li><?= Yii::t('app/index', 'Peer_to_Peer_7') ?></li>
                            <li><?= Yii::t('app/index', 'Peer_to_Peer_8') ?></li>
                            <li><?= Yii::t('app/index', 'Peer_to_Peer_9') ?></li>

                        </ul>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- end Advantages_of_peer_to_peer -->


<!-- begin Open_source_and_transparent -->
<div class="private-cloud bg-section other-sections open-source-section">
    <div class="container centered">
        <div class="centered">

            <picture>
                <source srcset="/assets/v20190812-min/images/open_source_and_transparent_icon.png" media="(max-width: 840px)"><img src="/assets/v20190812-min/images/open_source_and_transparent_icon.png" alt="smart" />
            </picture>

            <h2 class="page-section-title"><?= Yii::t('app/index', 'Open_source_and_transparent') ?></h2>
            <div class="ppc-block-wrap">
                <?= Yii::t('app/index', 'Open_source_and_transparent_text') ?>
            </div>

        </div>
    </div>
</div>
<!-- end Open_source_and_transparent -->

<!-- begin Full_control -->
<div class="page-section other-sections">
    <div class="container">
        <h2 class="page-section-title"><?= Yii::t('app/index', 'GET') ?><br /><?= Yii::t('app/index', 'Full_control') ?></h2>
        <div class="control-scheme">
            <?php if (false) { ?>
            <div class="control-scheme__text">
                <div><?= Yii::t('app/index', 'Automatic_selective') ?></div>
            </div>
            <?php } ?>
            <div class="control-scheme__media">
                <picture>
                    <source type="image/webp" srcset="/assets/v20190812-min/files/cloud.webp">
                    <source type="image/jpeg" srcset="/assets/v20190812-min/files/cloud.jpg">
                    <img src="/assets/v20190812-min/files/cloud.jpg" alt="">
                </picture>
                <?php if (false) { ?><div><?= Yii::t('app/index', 'Own_trusted_cloud') ?></div><?php } ?>
            </div>
            <?php if (false) { ?>
            <div class="control-scheme__text">
                <div><?= Yii::t('app/index', 'Protected_external') ?></div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
<!-- end Full_control -->


<?php
if (Yii::$app->user->isGuest) {
    ?>
    <!-- begin .create-account -->
    <div class="<?= !$no_show_animation_on_index ? "page-section" : "bg-section" ?> other-sections">
        <div class="container">
            <h2 class="page-section-title"><?= Yii::t('app/index', 'CREATE_AN_ACCOUNT') ?></h2>
            <div class="reg">

                <?php
                $form = ActiveForm::begin([
                    'id'     => "form-signup2",
                    'action' => Url::to(['/user/signup2'], CREATE_ABSOLUTE_URL),
                    'options' => [
                        //'onsubmit' => "return false",
                        'class'    => "create-account__form img-progress-form",
                    ],
                    //'enableAjaxValidation' => true,
                    //'enableClientValidation' => true,
                    //'validateOnSubmit' => false,
                ]);
                ?>

                <?=
                $form->field($model_signup2, 'user_email2', ['enableAjaxValidation' => true])
                    ->textInput([
                        'type' => "email",
                        'placeholder' => $model_signup2->getAttributeLabel('user_email2'),
                        'autocomplete' => "off",
                        'aria-label' => $model_signup2->getAttributeLabel('user_email2'),
                        //'id' => "signupform2-user_email",
                        //'name' => "Signup2Form[user_email]",
                        'readonly'     => $model_signup2->email_read_only,
                        'class'        => "form-control" . ($model_signup2->email_read_only ? " input-notActive" : ""),
                    ])
                    ->label(false)
                ?>

                <?=
                $form->field($model_signup2, 'password2'/*, ['enableClientValidation' => false,]*/)
                    ->passwordInput([
                        'placeholder' => $model_signup2->getAttributeLabel('password2'),
                        'autocomplete' => "off",
                        'aria-label' => $model_signup2->getAttributeLabel('password2'),
                    ])
                    ->label(false)
                ?>

                <?=
                $form->field($model_signup2, 'password_repeat2'/*, ['enableClientValidation' => false,]*/)
                    ->passwordInput([
                        'placeholder' => $model_signup2->getAttributeLabel('password_repeat2'),
                        'autocomplete' => "off",
                        'aria-label' => $model_signup2->getAttributeLabel('password_repeat2'),
                    ])
                    ->label(false)
                ?>

                <?=
                $form->field($model_signup2, 'promo_code')
                    ->textInput([
                        'placeholder'  => $model_signup2->getAttributeLabel('promo_code') . Yii::t('forms/login-signup-form', 'if_you_have_one'),
                        'autocomplete' => "off",
                        'aria-label'   => $model_signup2->getAttributeLabel('promo_code'),
                    ])
                    ->label(false)
                ?>

                <div id="signup2-captcha-container" class="captcha-container">
                    <?php
                    $reCaptchaPublicKey = Preferences::getValueByKey('reCaptchaPublicKey');
                    $cnt = intval(Yii::$app->cache->get(Yii::$app->params['RegisterCacheKey']));
                    if (!$cnt) {
                        $cnt = 1;
                        Yii::$app->cache->set(Yii::$app->params['RegisterCacheKey'], $cnt);
                    }
                    if (!$reCaptchaPublicKey) {
                        $cnt = 1;
                    }
                    if ($cnt > Preferences::getValueByKey('RegisterCountNoCaptcha', 1, 'int')) {

                        echo $form->field($model_signup2, 'reCaptchaSignup2')
                            ->widget(\himiklab\yii2\recaptcha\ReCaptcha::className(), ['siteKey' => Preferences::getValueByKey('reCaptchaPublicKey')])
                            ->label(false);

                    }
                    ?>
                </div>

                <?=
                $form->field($model_signup2, 'acceptRules2', [
                    'template' => "",
                    'inputTemplate' => "",
                    'options' => ['class' => "check-wrap private form-group"],
                    'checkboxTemplate'=>'
                        {input}
                        <label class="btn btn-checkbox active" for="accept-rules2" id="label-accept-rules2"><span></span><span>' . Yii::t('forms/login-signup-form', 'I_have_read_accept') . ' &nbsp;<a href="' . Url::to(['/terms'], CREATE_ABSOLUTE_URL) . '" target="_blank" rel="noopener">' . Yii::t('forms/login-signup-form', 'Terms_and_Conditions') . '</a></span></label>
                        <div class="form-group-hint">{error}{hint}</div>',
                ])
                    ->checkbox(['id' => "accept-rules2", 'value' => true, 'autocomplete' => "off", 'inputTemplate' => ""])
                    ->label(false)
                ?>

                <input type="submit" name="signup-button" id="signup-button-form2" value="<?= Yii::t('forms/login-signup-form', 'SignUpForFree') ?>" class="btn-big signup-button btn primary-btn wide-btn" />
                <div class="img-progress" title="loading..."></div>

                <?php
                ActiveForm::end();
                ?>

            </div>
        </div>
    </div>
    <!-- end .create-account -->
    <?php
}
?>
<!-- begin Index-page content -->

