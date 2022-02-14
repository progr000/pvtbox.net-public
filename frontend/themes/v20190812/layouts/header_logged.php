<?php

/* @var $this \yii\web\View */
/* @var $static_action string */
/* @var $user \common\models\Users */

use yii\helpers\Url;
use common\helpers\Functions;
use common\models\Licenses;
use common\models\Servers;

$data_token = frontend\models\NodeApi::site_token_key();
?>
<!-- begin .top -->
<div class="top delta-height-div">
    <div class="top__inner">
        <div class="top__descriptor"><?= Yii::t('app/header', 'p2p_text') ?></div>
        <div class="top__links"><a href="<?= Yii::getAlias('@docsWeb') ?>"><?= Yii::t('app/header', 'User_guide') ?></a><a class="<?= (Yii::$app->controller->id == 'site' && Yii::$app->controller->action->id == "support") ? "active" : "" ?>" href="<?= Url::to('/support', CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('app/header', 'Support') ?></a></div>
    </div>
</div>
<!-- end .top -->
<!-- begin .page-header (top menu) -->
<header class="page-header page-header--admin js-page-header delta-height-div">
    <div class="page-header-holder">
        <div class="page-header__inner">
            <?php if (Yii::$app->controller->id == 'user' && Yii::$app->controller->action->id == "files") { ?>
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
            <?=
            //var_dump(Yii::$app->controller->id);
            (Yii::$app->controller->id == 'admin-panel')
                ? '<span class="admin-link">' . Yii::t('app/header', 'Admin_panel') . '</span><span class="count-new-reports"></span>'
                : (
                    ($user->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN && $user->enable_admin_panel)
                        ? '<a class="admin-link" href="' . Url::to('/admin-panel', CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'Admin_panel') . '<span class="count-new-reports"></span></a>'
                        : ''
                );

            if ($user->license_type == Licenses::TYPE_FREE_TRIAL) {
                echo '<a class="admin-link" href="' . Url::to('/support?try=business-admin-functionality', CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'try_business_admin') . '</a>';
            }
            ?>
            <div class="msg"></div>
            <div class="admin-controls">
                <div class="admin-control">
                    <div class="admin-type -js-tooltip">
                        <?php
                        if (!in_array($user->license_type, [Licenses::TYPE_PAYED_BUSINESS_ADMIN, Licenses::TYPE_PAYED_BUSINESS_USER])) {
                            ?>
                            <a id="link-to-pricing-in-header"
                               href="<?= in_array($user->license_type, [Licenses::TYPE_FREE_DEFAULT, Licenses::TYPE_FREE_TRIAL, Licenses::TYPE_PAYED_BUSINESS_USER])
                                   ? Url::to('/pricing', CREATE_ABSOLUTE_URL)
                                   : Url::to('/user/profile?tab=2', CREATE_ABSOLUTE_URL) ?>">
                                <span class="masterTooltip" title="<?=
                                $user->license_type == Licenses::TYPE_PAYED_PROFESSIONAL
                                    ? Yii::t('app/header', "Unlimited")
                                    : (
                                $user->license_expire
                                    ? Yii::t('app/header', "till", ['till' => Functions::formatPostgresDate(Yii::$app->params['date_format'], $user->license_expire)])
                                    : Yii::t('app/header', "till_for_free")
                                )
                                ?>"><?= Licenses::getType($user->license_type); ?></span>
                            </a>
                            <?php
                        } else {
                            ?>
                            <span class="masterTooltip" title="<?=
                            $user->license_expire
                                ? Yii::t('app/header', "till", ['till' => Functions::formatPostgresDate(Yii::$app->params['date_format'], $user->license_expire)])
                                : Yii::t('app/header', "till_for_free")
                            ?>"><?= Licenses::getType($user->license_type) ?></span>
                            <?php
                        }

                        ?>
                    </div>
                </div>
                <?php
                $notif_signal = Servers::getSignal();
                ?>
                <div class="admin-control admin-control--notify"
                     id="wss-data-notifications"
                     data-token="<?= $data_token ?>"
                     data-wss-url="wss://<?= isset($notif_signal[0]) ? $notif_signal[0]->server_url : 'null' ?>/ws/webfm/<?= $data_token ?>?mode=notifications">
                    <a class="-js-tooltip masterTooltip notify-link"
                       <?=
                       (Yii::$app->controller->id == 'user' && Yii::$app->controller->action->id == "notifications")
                           ? 'name="notifications"'
                           : 'href="' . Url::to('/user/notifications', CREATE_ABSOLUTE_URL) . '"'
                       ?>
                       title="<?= Yii::t('app/header', 'Notifications') ?>">
                        <svg class="icon icon-bell">
                            <use xlink:href="#bell"></use>
                        </svg>
                        <span id="count-new-notifications">
                            <!--<b>33</b>-->
                        </span>
                    </a>
                </div>
                <div class="admin-control">
                    <div class="user-menu-wrap js-dropdown">
                        <div class="user-menu-label">
                            <div class="user-short color-<?= $user->_color ?>"><?= $user->_sname ?></div>
                            <div class="user-email"><?= $user->user_email ?></div>
                        </div>
                        <ul class="user-menu js-droplist">
                            <li class="user-menu__item user-menu__item--mob"><?=
                                (Yii::$app->controller->id == 'user' && Yii::$app->controller->action->id == "files")
                                    ? '<span>' . Yii::t('app/header', 'Home') . '</span>'
                                    : '<a href="' . Url::to('/user/files', CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'Home') . '</a>'
                                ?></li>
                            <?php if ($user->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN && $user->enable_admin_panel) { ?>
                            <li class="user-menu__item user-menu__item--mob"><?=
                                (Yii::$app->controller->id == 'admin-panel')
                                    ? '<span>' . Yii::t('app/header', 'Admin_panel') . '</span>'
                                    : '<a href="' . Url::to('/admin-panel', CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'Admin_panel') . '</a>'
                                ?></li>
                            <?php } ?>
                            <?php if ($user->license_type == Licenses::TYPE_FREE_TRIAL) { ?>
                            <li class="user-menu__item user-menu__item--mob"><!--
                             --><a href="<?= Url::to('/support?try=business-admin-functionality', CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('app/header', 'try_business_admin') ?></a><!--
                         --></li>
                            <?php } ?>
                            <li class="user-menu__item"><?=
                                (Yii::$app->controller->id == 'user' && Yii::$app->controller->action->id == "devices")
                                    ? '<span>' . Yii::t('app/header', 'My_Devices') . '</span>'
                                    : '<a href="' . Url::to('/user/devices', CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'My_Devices') . '</a>'
                                ?></li>
                            <li class="user-menu__item"><?=
                                (Yii::$app->controller->id == 'download' && Yii::$app->controller->action->id == "install")
                                    ? '<span>' . Yii::t('app/header', 'Install_Application') . '</span>'
                                    : '<a href="' . Url::to('/download/install', CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'Install_Application') . '</a>'
                                ?></li>
                            <li class="user-menu__item"><?=
                                (Yii::$app->controller->id == 'user' && Yii::$app->controller->action->id == "profile")
                                    ? '<span>' . Yii::t('app/header', 'Settings') . '</span>'
                                    : '<a href="' . Url::to('/user/profile', CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'Settings') . '</a>'
                                ?></li>
                            <li class="user-menu__item"><?=
                                ((Yii::$app->controller->id == 'page' && Yii::$app->session->get('page_controller_alias', null) == 'faq') || $static_action == 'faq')
                                    ? '<span>' . Yii::t('app/header', 'FAQ') . '</span>'
                                    : '<a href="' . Url::to('/faq', CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'FAQ') . '</a>'
                                ?></li>
                            <li class="user-menu__item"><a href="<?= Url::to('/user/logout', CREATE_ABSOLUTE_URL) ?>" data-method-off="post"><?= Yii::t('app/header', 'Logout') ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- end .page-header (top menu) -->

<?php
if ($user->license_type != Licenses::TYPE_FREE_DEFAULT) {
    if (!(Yii::$app->controller->id == 'user' && Yii::$app->controller->action->id == "conferences") && !(Yii::$app->controller->id == 'conferences')) {
        ?>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css"/>
        <!-- begin #conference-button -->
        <a href="<?= Url::to('/user/conferences', CREATE_ABSOLUTE_URL) ?>" class="-masterTooltip a-conferences"
           title="">
            <div id="conference-button" class="conferences">
                <div class="conference-icon"></div>
            </div>
        </a>
        <div class="label-conferences-container">
            <div class="label-text"><?= Yii::t('user/conferences', 'button_tooltip') ?></div>
            <i class="fa fa-play label-arrow"></i>
        </div>
        <!-- end #conference-button -->
        <?php
    }
}
?>

