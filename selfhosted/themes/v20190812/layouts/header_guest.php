<?php

/* @var $this \yii\web\View */
/* @var $static_action string */

use yii\widgets\Menu;
use yii\helpers\Url;

?>
<!-- begin .top -->
<div class="top">
    <div class="top__inner">
        <div class="top__descriptor"><?= Yii::t('app/header', 'p2p_text') ?></div>
        <div class="top__links"><a class="" href="<?= Url::to('/support', CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('app/header', 'Support') ?></a></div>
    </div>
</div>
<!-- end .top -->
<!-- begin .page-header (top menu) -->
<header class="page-header js-page-header">
    <div class="page-header-holder">
        <div class="page-header__inner">
            <?php if (Yii::$app->controller->id == 'site' && Yii::$app->controller->action->id == "index") { ?>
                <div class="logo">
                    <picture>
                        <source srcset="/assets/v20190812-min/images/logo-white.svg" media="(max-width: 540px)"><img src="/assets/v20190812-min/images/logo.svg" alt="<?= Yii::$app->name ?>" />
                    </picture>
                </div>
            <?php } else { ?>
                <a class="logo" href="<?= Url::to('/', CREATE_ABSOLUTE_URL) ?>">
                    <picture>
                        <source srcset="/assets/v20190812-min/images/logo-white.svg" media="(max-width: 540px)"><img src="/assets/v20190812-min/images/logo.svg" alt="<?= Yii::$app->name ?>" />
                    </picture>
                </a>
            <?php } ?>
            <div class="main-menu-wrap js-main-menu">
                <?php
                echo Menu::widget([
                    'options' => [
                        'class' => "main-menu",
                    ],
                    'activateItems' => true,
                    'activeCssClass' => 'active',
                    'encodeLabels' => false,
                    'items' => [
                        [
                            'options' => ['class' => "main-menu__item hide-more-than-541"],
                            'label' => Yii::t('app/header', 'Support'),
                            'active' => false,
                            'url' => Url::to('/support', CREATE_ABSOLUTE_URL),
                        ],
                    ],
                ]);
                ?>
            </div>
            <button id="trigger-login-dialog" class="btn primary-btn sm-btn auth-btn js-open-form" type="button" data-src="#auth-popup" data-tab="1"><?= Yii::t('app/header', 'SignIn') ?></button>
            <button class="btn main-menu-btn js-main-menu-btn" type="button" aria-label="main-menu"><span class="hamburger"><span></span><span></span><span></span><span></span></span></button>
        </div>
    </div>
</header>
<!-- end .page-header (top menu) -->
