<?php

/* @var $this \yii\web\View */
/* @var $static_action string */

use yii\helpers\Url;
use common\models\Preferences;
use frontend\widgets\langSwitch\langSwitchWidget;

?>
<!-- begin .page-footer-->
<footer class="page-footer page-footer--admin">
    <div class="container">
        <div class="row">
            <div class="page-footer-col"><!--
                --><?php if (Yii::$app->controller->id == 'user' && Yii::$app->controller->action->id == "files") {
                    ?><span class="logo"><img src="/assets/v20190812-min/images/logo-white.svg" alt="<?= Yii::$app->name ?>" /></span><?php
                } else {
                    ?><a class="logo" href="<?= Url::to('/', CREATE_ABSOLUTE_URL) ?>"><img src="/assets/v20190812-min/images/logo-white.svg" alt="<?= Yii::$app->name ?>" /></a><?php
                } ?><!--
         --></div>
            <div class="page-footer-col">
                <div class="copyright"><?= Yii::t('app/footer', 'All_right_reserved', ['DATE' => date('Y'), 'APP_NAME' => Yii::$app->name]) ?></div>
            </div>
            <div class="page-footer-col">
                <ul class="secondary-menu js-sec-menu-2"><!-- эти коменты - костыли https://stackoverflow.com/questions/5078239/how-do-i-remove-the-space-between-inline-block-elements
                    --><li class="secondary-menu__item"><?=
                        ((Yii::$app->controller->id == 'page' && Yii::$app->session->get('page_controller_alias', null) == 'terms') || $static_action == 'terms')
                            ? '<span>' . Yii::t('app/header', 'Terms_and_Conditions') . '</span>'
                            : '<a href="' . Url::to('/terms', CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'Terms_and_Conditions') . '</a>'
                        ?></li><!--
                    --><li class="secondary-menu__item"><?=
                        ((Yii::$app->controller->id == 'page' && Yii::$app->session->get('page_controller_alias', null) == 'privacy') || $static_action == 'privacy')
                            ? '<span>' . Yii::t('app/header', 'Privacy_Policy') . '</span>'
                            : '<a href="' . Url::to('/privacy', CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'Privacy_Policy') . '</a>'
                        ?></li><!--
                    --><li class="secondary-menu__item"><?=
                        ((Yii::$app->controller->id == 'page' && Yii::$app->session->get('page_controller_alias', null) == 'sla') || $static_action == 'sla')
                            ? '<span>' . Yii::t('app/header', 'SLA') . '</span>'
                            : '<a href="' . Url::to('/sla', CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'SLA') . '</a>'
                        ?></li><!--
                    --><li class="secondary-menu__item"><?=
                        (Yii::$app->controller->id == 'download' && Yii::$app->controller->action->id == "index")
                            ? '<span>' . Yii::t('app/header', 'Download') . '</span>'
                            : '<a href="' . Url::to('/download', CREATE_ABSOLUTE_URL) . '" target="_blank" rel="noopener">' . Yii::t('app/header', 'Download') . '</a>'
                        ?></li><!--
                     --><li class="secondary-menu__item hide-more-than-541"><?=
                        (Yii::$app->controller->id == 'site' && Yii::$app->controller->action->id == "support")
                            ? '<span>' . Yii::t('app/header', 'Support') . '</span>'
                            : '<a href="' . Url::to('/support', CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'Support') . '</a>'
                        ?></li><!--
                    --><li class="secondary-menu__item"><?=
                        ((Yii::$app->controller->id == 'page' && Yii::$app->session->get('page_controller_alias', null) == 'faq') || $static_action == 'faq')
                            ? '<span>' . Yii::t('app/header', 'FAQ') . '</span>'
                            : '<a href="' . Url::to('/faq', CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'FAQ') . '</a>'
                        ?></li><!--
                    --><li class="secondary-menu__item"><a href="<?= Yii::getAlias('@docsWeb') ?>"><?= Yii::t('app/header', 'User_guide') ?></a></li><!--
                    --><li class="secondary-menu__item"><?=
                        ((Yii::$app->controller->id == 'page' && Yii::$app->session->get('page_controller_alias', null) == 'about') || $static_action == 'about')
                            ? '<span>' . Yii::t('app/header', 'About_us') . '</span>'
                            : '<a href="' . Url::to('/about', CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'About_us') .' </a>'
                        ?></li><!--
                    --><li class="secondary-menu__item"><a href="<?= Url::to('/user/logout', CREATE_ABSOLUTE_URL) ?>" data-method="post"><?= Yii::t('app/header', 'Logout') ?></a></li><!--
             --></ul>

                <button class="secondary-menu-btn btn js-sec-menu-btn-2" type="button"><span class="hamburger"><span></span><span></span><span></span><span></span></span></button>

                <div class="social">
                    <a class="social__item" href="<?= Preferences::getValueByKey('seoTwitterLink'); ?>" aria-label="Our Twitter">
                        <svg class="icon icon-twitter">
                            <use xlink:href="#twitter"></use>
                        </svg>
                    </a>
                </div>
                <?= langSwitchWidget::widget() ?>
            </div>
        </div>
    </div>
</footer>
<!-- end .page-footer-->

