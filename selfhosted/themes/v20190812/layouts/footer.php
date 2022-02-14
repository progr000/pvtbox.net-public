<?php

/* @var $this \yii\web\View */
/* @var $static_action string */

use yii\helpers\Url;
use common\models\Preferences;

?>
<!-- begin .page-footer-->
<footer class="page-footer page-footer--admin">
    <div class="container">
        <div class="row">
            <div class="page-footer-col"><!--
                --><?php if (Yii::$app->controller->id == 'site' && Yii::$app->controller->action->id == "index") {
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
                     --><li class="secondary-menu__item hide-more-than-541"><?=
                        (Yii::$app->controller->id == 'site' && Yii::$app->controller->action->id == "support")
                            ? '<span>' . Yii::t('app/header', 'Support') . '</span>'
                            : '<a href="' . Url::to('/support', CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/header', 'Support') . '</a>'
                        ?></li><!--
                     --><li class="secondary-menu__item"><!--
                          --><a href="<?= Yii::getAlias('@frontendWeb') ?>"><?= Yii::t('app/header', 'Go_back_to_frontendWeb', ['frontendWeb' => Yii::getAlias('@frontendDomain')]) ?></a><!--
                     --></li><!--
                    --><?= ($user) ? '<li class="secondary-menu__item"><a href="' . Url::to('/site/logout', CREATE_ABSOLUTE_URL) . '" data-method="post">' . Yii::t('app/header', 'Logout') . '</a></li>' : '' ?><!--
             --></ul>

                <button class="secondary-menu-btn btn js-sec-menu-btn-2" type="button"><span class="hamburger"><span></span><span></span><span></span><span></span></span></button>

                <div class="social">
                    <a class="social__item" href="<?= Preferences::getValueByKey('seoTwitterLink'); ?>" aria-label="Our Twitter">
                        <svg class="icon icon-twitter">
                            <use xlink:href="#twitter"></use>
                        </svg>
                    </a>
                </div>
                <?= '' /*langSwitchWidget::widget()*/ ?>
            </div>
        </div>
    </div>
</footer>
<!-- end .page-footer-->

