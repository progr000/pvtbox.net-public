<?php

/* @var $this \yii\web\View */
/* @var $static_action string */

use yii\widgets\Menu;
use yii\helpers\Url;

$active_download = (Yii::$app->controller->id == 'download');
$active_features = ((Yii::$app->controller->id == 'page' && Yii::$app->session->get('page_controller_alias', null) == 'features') || $static_action == 'features');
$active_pricing  = ((Yii::$app->controller->id == 'page' && Yii::$app->session->get('page_controller_alias', null) == 'pricing') || $static_action == 'pricing');
$active_blog     = (Yii::$app->controller->id == 'blog' || $static_action == 'blog');
$active_support  = (Yii::$app->controller->id == 'site' && Yii::$app->controller->action->id == "support");

?>
<!-- begin .top -->
<div class="top">
    <div class="top__inner">
        <div class="top__descriptor"><?= Yii::t('app/header', 'p2p_text') ?></div>
        <div class="top__links"><a href="<?= Yii::getAlias('@docsWeb') ?>"><?= Yii::t('app/header', 'User_guide') ?></a><a class="<?= $active_support ? "active" : "" ?>" href="<?= Url::to('/support', CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('app/header', 'Support') ?></a></div>
    </div>
</div>
<!-- end .top -->

<!-- begin .page-header (top menu) -->
<header class="page-header js-page-header">
    <div class="page-header-holder">
        <div class="page-header__inner pulse">
            <?php if (Yii::$app->controller->id == 'site' && Yii::$app->controller->action->id == "index") { ?>
                <div class="logo">
                    <picture>
                        <source srcset="/assets/v20190812-min/images/logo-white.svg" media="(max-width: 540px)"><img class="header-img-logo-black" src="/assets/v20190812-min/images/logo.svg" alt="<?= Yii::$app->name ?>" /><img class="header-img-logo-white" src="/assets/v20190812-min/images/logo-white.svg" alt="<?= Yii::$app->name ?>" />
                    </picture>
                </div>
            <?php } else { ?>
                <a class="logo" href="<?= Url::to('/', CREATE_ABSOLUTE_URL) ?>">
                    <picture>
                        <source srcset="/assets/v20190812-min/images/logo-white.svg" media="(max-width: 540px)"><img class="header-img-logo-black" src="/assets/v20190812-min/images/logo.svg" alt="<?= Yii::$app->name ?>" /><img class="header-img-logo-white" src="/assets/v20190812-min/images/logo-white.svg" alt="<?= Yii::$app->name ?>" />
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
                            'options' => ['class' => "main-menu__item"],
                            'label' => $active_download ? '<span>' . Yii::t('app/header', 'Download') . '</span>' : Yii::t('app/header', 'Download'),
                            'active' => $active_download,
                            'url' => $active_download ? null : Url::to('/download', CREATE_ABSOLUTE_URL)
                            //'template' => '<a href="{url}" target="_blank" rel="noopener">{label}</a>'
                        ],
                        [
                            'options' => ['class' => "main-menu__item"],
                            'label' => $active_features ? '<span>' . Yii::t('app/header', 'Features') . '</span>' : Yii::t('app/header', 'Features'),
                            'active' => $active_features,
                            'url' => $active_features ? null : Url::to('/features', CREATE_ABSOLUTE_URL)
                        ],
                        [
                            'options' => ['class' => "main-menu__item"],
                            'label' => $active_pricing ? '<span>' . Yii::t('app/header', 'Pricing') . '</span>' : Yii::t('app/header', 'Pricing'),
                            'active' => $active_pricing,
                            'url' => $active_pricing ? null : Url::to('/pricing', CREATE_ABSOLUTE_URL)
                        ],
                        [
                            'options' => ['class' => "main-menu__item"],
                            'label' => $active_blog ? '<span>' . Yii::t('app/header', 'Blog') . '</span>' : Yii::t('app/header', 'Blog'),
                            'active' => $active_blog,
                            'url' => $active_blog ? null : Url::to('/blog', CREATE_ABSOLUTE_URL)
                        ],
                        [
                            'options' => ['class' => "main-menu__item hide-more-than-541"],
                            'label' => Yii::t('app/header', 'User_guide'),
                            'active' => false,
                            'url' => Yii::getAlias('@docsWeb'),
                        ],
                        [
                            'options' => ['class' => "main-menu__item hide-more-than-541"],
                            'label' => $active_support ? '<span>' . Yii::t('app/header', 'Support') . '</span>' : Yii::t('app/header', 'Support'),
                            'active' => $active_support,
                            'url' => $active_support ? null : Url::to('/support', CREATE_ABSOLUTE_URL)
                        ],
                        [
                            'options' => ['class' => "main-menu__item"],
                            'label' => $active_blog
                                ? '<a href="' . Url::to('/?signup', CREATE_ABSOLUTE_URL) . '">'. Yii::t('app/header', 'Registration') . '</a>'
                                : '<a id="trigger-signup-dialog" class="js-open-form" href="#" data-src="#auth-popup" data-tab="2">'. Yii::t('app/header', 'Registration') . '</a>',
                        ],
                    ],
                ]);
                ?>
            </div>
            <?=
            $active_blog
                ? '<a class="btn primary-btn sm-btn auth-btn" href="' . Url::to('/?login', CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'SignIn') . '</a>'
                : '<button id="trigger-login-dialog" class="btn primary-btn sm-btn auth-btn js-open-form" type="button" data-src="#auth-popup" data-tab="1">' . Yii::t('app/header', 'SignIn') . '</button>'
            ?>
            <button class="btn main-menu-btn js-main-menu-btn" type="button" aria-label="main-menu"><span class="hamburger"><span></span><span></span><span></span><span></span></span></button>
        </div>
    </div>
</header>
<!-- end .page-header (top menu) -->
