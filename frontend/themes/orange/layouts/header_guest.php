<?php

/* @var $this \yii\web\View */
/* @var $static_action string */

use yii\widgets\Menu;
use yii\helpers\Url;

?>

<!-- .header -->
<div class="header header--fix">

    <div class="header-strip">

        <div class="header-strip__cont">

            <div class="header-strip__row">

                <div class="header-strip__col">
                    <span class="header-strip-text"><?= Yii::t('app/header', 'p2p_text') ?></span>
                </div>

                <div class="header-strip__col">
                    <a class="header-strip-link" href="<?= Url::to(['/support'], CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('app/header', 'Support') ?></a>
                    <a class="header-strip-link-small" href="<?= Url::to(['/support'], CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('app/header', 'Support_small') ?></a>
                </div>

            </div>

        </div>

    </div>


    <div class="header-inform">

        <div class="header-inform__cont">

            <div class="header-inform__row">

                <div class="header-inform__col">

                    <div class="logo">
                        <a href="<?= Url::to(['/'], CREATE_ABSOLUTE_URL) ?>"><img class="img-after-loader" data-src-after="/themes/orange/images/logo_new.svg" src="" alt="<?= Yii::$app->name ?>" /></a>
                        <span class="btn-default login-dialog" id="btn-login-dialog-mobile" data-toggle="modal" data-target="#entrance" data-whatever="log">Sign in</span>
                    </div>

                </div>

                <div class="header-inform__col">

                    <div class="menu">
                        <?php
                        //var_dump(\Yii::$app->controller->id);
                        echo Menu::widget([
                            'activateItems' => true,
                            'activeCssClass' => 'active',
                            'encodeLabels' => false,
                            'items' => [
                                [
                                    'label' => Yii::t('app/header', 'Download'),
                                    'active'=>(Yii::$app->controller->id == 'download'),
                                    'url' => Url::to(['/download'], CREATE_ABSOLUTE_URL)
                                    //'template' => '<a href="{url}" target="_blank" rel="noopener">{label}</a>'
                                ],
                                [
                                    'label' => Yii::t('app/header', 'Features'),
                                    'active'=>(
                                        (Yii::$app->controller->id == 'page' && Yii::$app->session->get('page_controller_alias', null) == 'features')
                                        ||
                                        $static_action == 'features'
                                    ),
                                    'url' => Url::to(['/features'], CREATE_ABSOLUTE_URL)
                                ],
                                [
                                    'label' => Yii::t('app/header', 'Pricing'),
                                    'active'=>(
                                        (Yii::$app->controller->id == 'page' && Yii::$app->session->get('page_controller_alias', null) == 'pricing')
                                        ||
                                        $static_action == 'pricing'
                                    ),
                                    'url' => Url::to(['/pricing'], CREATE_ABSOLUTE_URL)
                                ],
                                [
                                    'label' => Yii::t('app/header', 'Blog'),
                                    'active'=>(
                                        (Yii::$app->controller->id == 'page' && Yii::$app->session->get('page_controller_alias', null) == 'blog')
                                        ||
                                        $static_action == 'blog'
                                    ),
                                    'url' => Url::to(['/blog'], CREATE_ABSOLUTE_URL)
                                ],
                                [
                                    'label' => '<span data-toggle="modal" data-target="#entrance" data-whatever="reg" class="signup-dialog">' . Yii::t('app/header', 'Registration') . '</span>',
                                ],
                            ],
                        ]);
                        ?>
                    </div>

                    <span class="btn-default login-dialog" id="btn-login-dialog" data-toggle="modal" data-target="#entrance" data-whatever="log"><?= Yii::t('app/header', 'SignIn') ?></span>

                </div>

            </div>

        </div>

    </div>

</div>
<!-- END .header -->
