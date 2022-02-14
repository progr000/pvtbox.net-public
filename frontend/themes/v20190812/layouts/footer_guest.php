<?php

/* @var $this \yii\web\View */
/* @var $static_action string */

use yii\helpers\Url;
use common\models\Preferences;
use frontend\widgets\langSwitch\langSwitchWidget;

?>
<!-- begin .page-footer -->
<footer class="page-footer">
    <div class="container">
        <div class="row">
            <div class="page-footer-col"><!--
                --><?php if (Yii::$app->controller->id == 'site' && Yii::$app->controller->action->id == "index") {
                    ?><span class="logo"><img src="/assets/v20190812-min/images/logo-white.svg" alt="<?= Yii::$app->name ?>" /></span><?php
                } else {
                ?><a class="logo" href="<?= Url::to('/', CREATE_ABSOLUTE_URL) ?>"><img src="/assets/v20190812-min/images/logo-white.svg" alt="<?= Yii::$app->name ?>" /></a><?php
                } ?><!--
                --><div class="copyright"><?= Yii::t('app/footer', 'All_right_reserved', ['DATE' => date('Y'), 'APP_NAME' => Yii::$app->name]) ?></div><!--
            --></div>
            <div class="page-footer-col">
                <ul class="secondary-menu"><!-- эти коменты - костыли https://stackoverflow.com/questions/5078239/how-do-i-remove-the-space-between-inline-block-elements
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
                    --><li class="secondary-menu__item"><?=
                        ((Yii::$app->controller->id == 'page' && Yii::$app->session->get('page_controller_alias', null) == 'features') || $static_action == 'features')
                            ? '<span>' . Yii::t('app/header', 'Features') . '</span>'
                            : '<a href="' . Url::to('/features', CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'Features') . '</a>'
                        ?></li><!--
                    --><li class="secondary-menu__item"><?=
                        ((Yii::$app->controller->id == 'page' && Yii::$app->session->get('page_controller_alias', null) == 'pricing') || $static_action == 'pricing')
                            ? '<span>' . Yii::t('app/header', 'Pricing') . '</span>'
                            : '<a href="' . Url::to('/pricing', CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'Pricing') . '</a>'
                        ?></li><!---
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
                    --><li class="secondary-menu__item"><?=
                        ((Yii::$app->controller->id == 'page' && Yii::$app->session->get('page_controller_alias', null) == 'affiliate') || $static_action == 'affiliate')
                            ? '<span>' . Yii::t('app/header', 'Affiliate_program') . '</span>'
                            : '<a href="' . Url::to('/affiliate', CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'Affiliate_program') . '</a>'
                        ?></li><!--
                    --><li class="secondary-menu__item"><?=
                        (Yii::$app->controller->id == 'blog' || $static_action == 'blog')
                            ? '<a href="' . Url::to('/?signup', CREATE_ABSOLUTE_URL) . '">' .Yii::t('app/header', 'Registration') . '</a>'
                            : '<a href="#" class="js-open-form signup-dialog" data-src="#auth-popup" data-tab="2" data-toggle="modal" data-target="#entrance" data-whatever="reg">' .Yii::t('app/header', 'Registration') . '</a>'
                        ?></li><!--
             --></ul>
            </div>
            <div class="page-footer-col">
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
<!-- end .page-footer -->
