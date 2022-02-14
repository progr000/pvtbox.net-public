<?php

/* @var $this \yii\web\View */
/* @var $user \common\models\Users */

use common\helpers\Functions;
use common\models\Licenses;
use yii\helpers\Url;

/* */
if (isset(Yii::$app->params['no_lock_fm_action']) && Yii::$app->params['no_lock_fm_action']) {
    echo "<div id=\"no-check-online-nodes\" style=\"display: none;\"></div>\n";
}

?>
<!-- <?= $user->user_id ?> -->
<!-- <?php /* echo time() - common\models\Preferences::getValueByKey('RestorePatchTTL', 2592000, 'int') */ ?> -->
<!-- .header -->
<div class="header header--entered">

    <div class="header-strip">

        <div class="header-strip__cont">

            <div class="header-strip__row">

                <div class="header-strip__col">
                    <span class="header-strip-text"><?= Yii::t('app/header', 'p2p_text') ?></span>
                </div>

                <div class="header-strip__col">
                    <a class="header-strip-link <?= (Yii::$app->controller->id == 'site' && Yii::$app->controller->action->id == "support") ? "active" : "" ?>" href="<?= Url::to(['/support'], CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('app/header', 'Support') ?></a>
                </div>

            </div>

        </div>

    </div>


    <div class="header-inform">

        <div class="header-inform__cont">

            <div class="header-inform__row">

                <div class="header-inform__col">

                    <div class="logo"><a href="<?= Url::to(['/'], CREATE_ABSOLUTE_URL) ?>"><img class="img-after-loader" data-src-after="/themes/orange/images/logo_new.svg" src="" alt="<?= Yii::$app->name ?>" /></a></div>

                    <?=
                    //var_dump(Yii::$app->controller->id);
                    (Yii::$app->controller->id == 'admin-panel')
                        ? '<span class="header-title">' . Yii::t('app/header', 'Admin_panel') . '</span>'
                        : (
                            ($user->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN && $user->enable_admin_panel)
                                ? '<a class="header-title" href="' . Url::to(['/admin-panel'], CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'Admin_panel') . '</a>'
                                : ''
                        );
                    ?>
                </div>

                <div class="header-inform__col">

                    <?= $this->render('alert_dialogs', ['user' => $user]); ?>

                </div>

                <div class="header-inform__col">

                    <span class="header-type">
                        <!--Type:-->
                        <?php
                        if (!in_array($user->license_type, [Licenses::TYPE_PAYED_BUSINESS_ADMIN, Licenses::TYPE_PAYED_BUSINESS_USER])) {
                            ?>
                            <a id="link-to-pricing-in-header" href="<?= in_array($user->license_type, [Licenses::TYPE_FREE_DEFAULT, Licenses::TYPE_FREE_TRIAL, Licenses::TYPE_PAYED_BUSINESS_USER]) ? Url::to(['/pricing'], CREATE_ABSOLUTE_URL) : Url::to(['/user/profile?tab=2'], CREATE_ABSOLUTE_URL) ?>">
                                <b class="masterTooltip" title="<?=
                                    $user->license_type == Licenses::TYPE_PAYED_PROFESSIONAL
                                        ? Yii::t('app/header', "Unlimited")
                                        : (
                                            $user->license_expire
                                                ? Yii::t('app/header', "till", ['till' => Functions::formatPostgresDate(Yii::$app->params['date_format'], $user->license_expire)])
                                                : Yii::t('app/header', "till_for_free")
                                        )
                                ?>"><?= Licenses::getType($user->license_type); ?></b>
                            </a>
                            <?php
                        } else {
                            ?>
                            <b class="masterTooltip" title="<?=
                                $user->license_expire
                                    ? Yii::t('app/header', "till", ['till' => Functions::formatPostgresDate(Yii::$app->params['date_format'], $user->license_expire)])
                                    : Yii::t('app/header', "till_for_free")
                            ?>"><?= Licenses::getType($user->license_type) ?></b>
                            <?php
                        }

                        ?>
                    </span>

                    <?php
                    /*
                    if (in_array($user->license_type, [Licenses::TYPE_FREE_DEFAULT, Licenses::TYPE_FREE_TRIAL])) {
                        echo '<a class="btn-min" href="' . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'Update_to_PRO') . '</a>';
                    }
                    */
                    if ($user->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                        /*
                        echo '
                            <div class="developments dropdown">
                                <div class="dropdown-toggle" data-toggle="dropdown">
                                    <span id="count-new-notifications">
                                        <!--<b>4</b>-->
                                    </span>
                                </div>
                                <ul class="dropdown-menu">
                                    <li id="menu-count-new-notifications"><!--<b>4</b>--><a href="' . Url::to(['/user/notifications'], CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'Notifications') . '</a></li>
                                    <li id="menu-count-new-events"><!--<b>4</b>--><a href="' . Url::to(['/admin-panel?tab=3'], CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'Events') . '</a></li>
                                </ul>
                            </div>
                        ';
                        */
                        echo '
                            <div class="developments">
                                <div class="not-dropdown-toggle">
                                    <a href="' . Url::to(['/user/notifications'], CREATE_ABSOLUTE_URL) . '" class="masterTooltip" title="' . Yii::t('app/header', 'Notifications') . '">
                                    <span id="count-new-notifications">
                                        <!--<b>4</b>-->
                                    </span>
                                    </a>
                                </div>
                            </div>
                        ';
                    } else {
                        echo '
                            <div class="developments">
                                <div class="not-dropdown-toggle">
                                    <a href="' . Url::to(['/user/notifications'], CREATE_ABSOLUTE_URL) . '" class="masterTooltip" title="' . Yii::t('app/header', 'Notifications') . '">
                                    <span id="count-new-notifications">
                                        <!--<b>4</b>-->
                                    </span>
                                    </a>
                                </div>
                            </div>
                        ';
                    }
                    ?>


                    <div class="entered-menu dropdown" id="member-main-menu">
                        <div class="dropdown-toggle" data-toggle="dropdown"><span class="color-<?= $user->_color ?>"><b><?= $user->_sname ?></b></span><?= $user->user_email ?></div>
                        <ul class="dropdown-menu">
                            <li class="show-at-less-than-900"><a href="<?= Url::to(['/user/files'], CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('app/header', 'Home') ?></a></li>
                            <?php
                                if ($user->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN && $user->enable_admin_panel) {
                                    echo '<li class="show-at-less-than-1152"><a href="' . Url::to(['/admin-panel'], CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'Admin_panel') . '</a></li>';
                                }
                            ?>
                            <li><a href="<?= Url::to(['/user/devices'], CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('app/header', 'My_Devices') ?></a></li>
                            <li><a href="<?= Url::to(['/download/install'], CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('app/header', 'Install_Application') ?></a></li>
                            <li><a href="<?= Url::to(['/user/profile'], CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('app/header', 'Settings') ?></a></li>
                            <li><a href="<?= Url::to(['/faq'], CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('app/header', 'FAQ') ?></a></li>
                            <li><a href="<?= Url::to(['/user/logout'], CREATE_ABSOLUTE_URL) ?>" data-method="post"><?= Yii::t('app/header', 'Logout') ?></a></li>
                        </ul>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
<!-- END .header -->
