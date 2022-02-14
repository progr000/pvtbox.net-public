<?php

/* @var $this \yii\web\View */
/* @var $static_action string */

use yii\helpers\Url;
use common\models\Preferences;
use frontend\widgets\langSwitch\langSwitchWidget;

?>

<!-- .footer -->
<footer class="footer">

    <div class="footer__cont">

        <div class="footer__row">

            <div class="footer__col">
                <div class="footer-logo"><a href="<?= Url::to(['/'], CREATE_ABSOLUTE_URL) ?>"><img class="img-after-loader" data-src-after="/themes/orange/images/logo-footer_new.svg" src="" alt="<?= Yii::$app->name ?>" /></a></div>
            </div>

            <div class="footer__col">
                <div class="footer-menu">
                    <a name="name_copy" class="nolink"><?= Yii::t('app/footer', 'Copyright' , ['APP_NAME' => Yii::$app->name]) ?></a>
                </div>

                <div class="entered-bottom-menu dropup">
                    <div class="dropdown-toggle" data-toggle="dropdown"><?= Yii::t('app/header', 'Show_more') ?></div>
                    <ul class="dropdown-menu">
                        <li><a href="<?= Url::to(['/terms']) ?>" class="<?= ((Yii::$app->controller->id == 'page' && Yii::$app->session->get('page_controller_alias', null) == 'terms') || $static_action == 'terms') ? "active" : "" ?>"><?= Yii::t('app/header', 'Terms_and_Conditions') ?></a></li>
                        <li><a href="<?= Url::to(['/privacy']) ?>" class="<?= ((Yii::$app->controller->id == 'page' && Yii::$app->session->get('page_controller_alias', null) == 'privacy') || $static_action == 'privacy') ? "active" : "" ?>"><?= Yii::t('app/header', 'Privacy_Policy') ?></a></li>
                        <li><a href="<?= Url::to(['/sla']) ?>" class="<?= ((Yii::$app->controller->id == 'page' && Yii::$app->session->get('page_controller_alias', null) == 'sla') || $static_action == 'sla') ? "active" : "" ?>"><?= Yii::t('app/header', 'SLA') ?></a></li>
                        <li><a href="<?= Url::to(['/download']) ?>" class="<?= (Yii::$app->controller->id == 'download') ? "active" : "" ?>" target="_blank" rel="noopener"><?= Yii::t('app/header', 'Download') ?></a></li>
                        <li><a href="<?= Url::to(['/features']) ?>" class="<?= ((Yii::$app->controller->id == 'page' && Yii::$app->session->get('page_controller_alias', null) == 'features') || $static_action == 'features') ? "active" : "" ?>"><?= Yii::t('app/header', 'Features') ?></a></li>
                        <li><a href="<?= Url::to(['/pricing']) ?>" class="<?= ((Yii::$app->controller->id == 'page' && Yii::$app->session->get('page_controller_alias', null) == 'pricing') || $static_action == 'pricing') ? "active" : "" ?>"><?= Yii::t('app/header', 'Pricing') ?></a></li>
                        <li><a href="<?= Url::to(['/blog']) ?>"><?= Yii::t('app/header', 'Blog') ?></a></li>
                        <li><a href="<?= Url::to(['/faq']) ?>" class="<?= ((Yii::$app->controller->id == 'page' && Yii::$app->session->get('page_controller_alias', null) == 'faq') || $static_action == 'faq') ? "active" : "" ?>"><?= Yii::t('app/header', 'FAQ') ?></a></li>
                        <li><a href="<?= Url::to(['/about']) ?>" class="<?= ((Yii::$app->controller->id == 'page' && Yii::$app->session->get('page_controller_alias', null) == 'about') || $static_action == 'about') ? "active" : "" ?>"><?= Yii::t('app/header', 'About_us') ?></a></li>
                        <li class="show-at-less-than-900"><a href="<?= Url::to(['/support']) ?>" class="<?= (Yii::$app->controller->id == 'site' && Yii::$app->controller->action->id == "support") ? "active" : "" ?>"><?= Yii::t('app/header', 'Support') ?></a></li>
                        <li><a href="<?= Url::to(['/user/logout']) ?>" data-method="post" tabindex="-1"><?= Yii::t('app/header', 'Logout') ?></a></li>
                    </ul>
                </div>

                <!-- <span class="footer-text"><?= Yii::t('app/footer', 'All_right_reserved', ['DATE' => date('Y'), 'APP_NAME' => Yii::$app->name]) ?></span> -->
                <!--<a class="footer-link" href="javascript:void(0)"><?= Preferences::getValueByKey('adminEmail') ?></a>-->

            </div>

            <div class="footer__col">

                <div class="social-networks">
                    <a class="social-networks__twitter" href="<?= Preferences::getValueByKey('seoTwitterLink'); ?>" target="_blank" rel="noopener" aria-label="twitter"><img src="/themes/orange/images/null.png" border="0" alt="twitter" /></a>
                </div>

                <?= langSwitchWidget::widget() ?>

            </div>

        </div>

    </div>

</footer>
<!-- END .footer -->

<?=
$this->render('modal', [
    //'form_login' => $this->context->model_login,
    'form_signup' => new \frontend\models\forms\SignupForm(),
    'form_request_reset' => new \frontend\models\forms\PasswordResetRequestForm(),
]);
?>

