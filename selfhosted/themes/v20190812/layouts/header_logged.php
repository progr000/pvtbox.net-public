<?php

/* @var $this \yii\web\View */
/* @var $static_action string */

use yii\helpers\Url;
use common\helpers\Functions;
use common\models\Licenses;

$data_token = frontend\models\NodeApi::site_token_key();
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
<header class="page-header page-header--admin js-page-header">
    <div class="page-header-holder">
        <div class="page-header__inner">
            <?php if (Yii::$app->controller->id == 'site' && Yii::$app->controller->action->id == "index") { ?>
                <div class="logo hide-on-mobile-when-fm-and-width-less-than-540 hide-less-than-540">
                    <picture>
                        <source srcset="/assets/v20190812-min/images/logo-white.svg" media="(max-width: 540px)"><img src="/assets/v20190812-min/images/logo.svg" alt="<?= Yii::$app->name ?>" />
                    </picture>
                </div>
            <?php } else { ?>
                <a class="logo hide-on-mobile-when-fm-and-width-less-than-540 hide-less-than-540" href="<?= Url::to('/', CREATE_ABSOLUTE_URL) ?>">
                    <picture>
                        <source srcset="/assets/v20190812-min/images/logo-white.svg" media="(max-width: 540px)"><img src="/assets/v20190812-min/images/logo.svg" alt="<?= Yii::$app->name ?>" />
                    </picture>
                </a>
            <?php } ?>
            <div class="msg"></div>
            <div class="admin-controls">
                <div class="admin-control">
                    <div class="admin-type -js-tooltip">

                    </div>
                </div>

                <div class="admin-control">
                    <div class="user-menu-wrap js-dropdown">
                        <div class="user-menu-label">
                            <div class="user-short color-<?= $user->_color ?>"><?= $user->_sname ?></div>
                            <div class="user-email"><?= $user->shu_email ?></div>
                        </div>
                        <ul class="user-menu js-droplist">
                            <li class="user-menu__item"><a href="<?= Url::to('/site/logout', CREATE_ABSOLUTE_URL) ?>" data-method-off="post"><?= Yii::t('app/header', 'Logout') ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- end .page-header (top menu) -->
