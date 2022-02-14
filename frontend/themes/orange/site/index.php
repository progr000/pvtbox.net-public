<?php

/* @var $this yii\web\View */
/* @var $model_signup2 \frontend\models\forms\SignupForm2 */
/* @var $software array */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use common\models\Preferences;
use frontend\assets\orange\animationAsset;
use frontend\assets\orange\downloadAsset;

$this->title = Yii::t('app/index', 'title');
if (!isset(Yii::$app->params['no_show_animation_on_index'])) {
    animationAsset::register($this);
}
downloadAsset::register($this);
?>
<!-- .home -->
<div class="home">

    <div class="home__cont">

        <div class="home__row">

            <div class="home__col">
                <picture class="picture-home-img" id="container-picture">
                    <source data-src-edge-safari="/themes/orange/images/home-2x_1648.jpg" data-src-other="/themes/orange/images/home-2x_1648.webp" srcset="" media="(min-width: 1440px)" />
                    <source data-src-edge-safari="/themes/orange/images/home-2x_1440.jpg" data-src-other="/themes/orange/images/home-2x_1440.webp" srcset="" media="(min-width: 1360px)" />
                    <source data-src-edge-safari="/themes/orange/images/home-2x_1360.jpg" data-src-other="/themes/orange/images/home-2x_1360.webp" srcset="" media="(min-width: 1280px)" />
                    <source data-src-edge-safari="/themes/orange/images/home-2x_1280.jpg" data-src-other="/themes/orange/images/home-2x_1280.webp" srcset="" media="(min-width: 1024px)" />
                    <source data-src-edge-safari="/themes/orange/images/home-2x_1024.jpg" data-src-other="/themes/orange/images/home-2x_1024.webp" srcset="" media="(min-width: 800px)" />
                    <source data-src-edge-safari="/themes/orange/images/home-2x_800.jpg"  data-src-other="/themes/orange/images/home-2x_800.webp"  srcset="" media="(min-width: 768px)" />
                    <img id="container-home-img" class="home-img -img-after-loader" -data-src-after="/themes/orange/images/home-2x_800.jpg" src="" alt="mock" />
                </picture>
            </div>

            <div class="home__col">

                <div class="home-logo"><a name="logo" _href="javascript:void(0)"><img class="img-after-loader" data-src-after="/themes/orange/images/logo-big_new.svg" src="" alt="<?= Yii::$app->name ?>" /></a></div>

                <div class="home-info">
                    <?= Yii::t('app/index', 'home_info') ?>
                </div>

                <div class="home-get-button">

                    <a class="btn-big download-link" href="#"><?= Yii::t('app/index', 'Get_AppName_Free') ?></a>

                    <div id="create-account-button-ios" class="create-account-ios" style="display: none;">
                        <?= Yii::t('app/download', 'You_will_need_a_AppName') ?>
                        <a href="#" class="signup-dialog" data-toggle="modal" data-target="#entrance" data-whatever="reg" style=""><?= Yii::t('app/download', 'Create_an_account') ?></a>
                        <!-- <br /><span class="btn-default signup-dialog" data-toggle="modal" data-target="#entrance" data-whatever="reg" style="margin-top: 5px;">Create an account</span>-->
                    </div>

                    <?= $this->render('/download/other_platforms', ['software' => $software]) ?>
                </div>

                <div class="icon-brand">
                    <span class="icon-brand-android" data-href="javascript:void(0)"></span>
                    <span class="icon-brand-unix" data-href="javascript:void(0)"></span>
                    <span class="icon-brand-apple" data-href="javascript:void(0)"></span>
                    <span class="icon-brand-windows" data-href="javascript:void(0)"></span>
                </div>

            </div>

        </div>

    </div>

</div>
<!-- END .home -->


<!-- .advantage -->
<style>

</style>
<div class="informList">

    <div class="advantage__cont video-container-main">

        <div class="title"><h2><?= Yii::t('app/index', 'Video_Presentation') ?></h2></div>

        <div class="advantage__row video-container">

            <iframe title="<?= Yii::t('app/index', 'Video_Presentation') ?>" width="784" height="441" src="https://www.youtube.com/embed/mh3mxcRQvAc" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>

        </div>

    </div>

</div>
<!-- END .advantage -->


<!-- .advantage -->
<div class="advantage">


    <div class="advantage__cont">

        <div class="title"><h2><?= Yii::t('app/index', 'What_benefits') ?></h2></div>

        <div class="advantage__row">

            <div class="advantage__col">

                <div class="advantage-box advantage-confidentiality">
                    <div class="advantage-img"></div>
                    <h4><?= Yii::t('app/index', 'Confidentiality') ?></h4>
                    <div class="advantage-text">
                        <p><?= Yii::t('app/index', 'Confidentiality_text') ?></p>
                    </div>
                    <a href="<?= Url::to(['/features#confidentiality'], CREATE_ABSOLUTE_URL) ?>" title=""><?= Yii::t('app/index', 'More_info') ?></a>
                </div>

            </div>

            <div class="advantage__col">

                <div class="advantage-box advantage-security">
                    <div class="advantage-img"></div>
                    <h4><?= Yii::t('app/index', 'Security') ?></h4>
                    <div class="advantage-text">
                        <p><?= Yii::t('app/index', 'Security_text') ?></p>
                    </div>
                    <a href="<?= Url::to(['/features#security'], CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('app/index', 'More_info') ?></a>
                </div>

            </div>

            <div class="advantage__col">

                <div class="advantage-box advantage-speed">
                    <div class="advantage-img"></div>
                    <h4><?= Yii::t('app/index', 'High_Speed') ?></h4>
                    <div class="advantage-text">
                        <p><?= Yii::t('app/index', 'High_Speed_text') ?></p>
                    </div>
                    <a href="<?= Url::to(['/features#speed'], CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('app/index', 'More_info') ?></a>
                </div>

            </div>

        </div>

    </div>

</div>
<!-- END .advantage -->




<!-- .informList -->
<div class="informList">

    <div class="advantage__cont" style="padding-top: 20px; padding-bottom: 30px;">

        <div class="title" style="padding-bottom: 10px;"><h2><?= Yii::t('app/index', 'Full_control') ?></h2></div>

        <div class="row row-big-cloud hide_" style="display: flex; align-items:center; margin-top: 20px;">

            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6" style="padding-right: 10px;">

                <div class="alert-cloud float-right pull-right">
                    <?= Yii::t('app/index', 'Automatic_selective') ?>
                </div>

            </div>

            <div class="col-lg-6 col-md-6 col-sm-6">

                <img class="img-after-loader" data-src-after="/themes/orange/images/cloud_v2.jpg" src="" alt="cloud" style="width: 100%;" />

            </div>

            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6" style="padding-left: 10px;">

                <div class="alert-cloud">
                    <?= Yii::t('app/index', 'Protected_external') ?>
                </div>

            </div>

        </div>

        <div class="row row-big-cloud hide_" style="padding-top: 10px;">

            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">

            </div>

            <div class="col-lg-6 col-md-6 col-sm-6">

                <div class="alert-cloud">
                    <?= Yii::t('app/index', 'Own_trusted_cloud') ?>
                </div>

            </div>

            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">

            </div>

        </div>

        <div class="row row-small-cloud hide" style="padding: 0px 5px 0px 5px; text-align: center;">

            <img class="img-after-loader" data-src-after="/themes/orange/images/cloud_v2.jpg" src="" alt="cloud" style="width: 100%; max-width: 500px;" />

            <div class="alert-cloud"><?= Yii::t('app/index', 'Automatic_selective_2') ?></div>

            <div class="alert-cloud"><?= Yii::t('app/index', 'Own_trusted_cloud_2') ?></div>

            <div class="alert-cloud"><?= Yii::t('app/index', 'Protected_external_2') ?></div>

        </div>

    </div>

</div>
<!-- END .informList -->




<!-- .peer-2-peer-image -->
<div class="advantage" style="margin-top: 20px;">

    <div class="advantage__cont">

        <div class="title"><h2><?= Yii::t('app/index', 'Peer2peerImage') ?></h2></div>

        <div class="advantage__row" style="padding: 0px;">

            <?php if (!isset(Yii::$app->params['no_show_animation_on_index'])) { ?>
            <div id="animation_container" style="background-color:rgba(255, 255, 255, 1.00); width:1150px; height:450px; /*border: 1px solid #FF0000;*/  margin: auto;">
                <canvas id="canvas" width="1150" height="450" style="position: absolute; display: block; background-color:rgba(255, 255, 255, 1.00);"></canvas>
                <div id="dom_overlay_container" style="pointer-events:none; overflow:hidden; width:1150px; height:450px; position: absolute; left: 0px; top: 0px; display: block;">
                </div>
            </div>
            <?php } ?>

        </div>

    </div>

</div>
<!-- END .peer-2-peer-image -->




<?php
if (Yii::$app->user->isGuest) {
    ?>

    <!-- .create-account -->
    <div class="create-account">

        <div class="create-account__cont">

            <span class="create-account__title">
                <?= Yii::t('app/index', 'CREATE_AN_ACCOUNT') ?>
            </span>

            <?php
            $form = ActiveForm::begin([
                'id'     => "form-signup2",
                'action' => Url::to(['/user/signup2'], CREATE_ABSOLUTE_URL),
                'options' => [
                    //'onsubmit' => "return false",
                    'class'    => "create-account__form",
                ],
                //'enableAjaxValidation' => true,
                //'enableClientValidation' => true,
                //'validateOnSubmit' => false,
            ]);
            ?>

            <?=
            $form->field($model_signup2, 'user_email2', ['enableAjaxValidation' => true])
                ->textInput([
                    'type' => "email",
                    'placeholder' => $model_signup2->getAttributeLabel('user_email2'),
                    'autocomplete' => "off",
                    'aria-label' => $model_signup2->getAttributeLabel('user_email2'),
                    //'id' => "signupform2-user_email",
                    //'name' => "Signup2Form[user_email]",
                ])
                ->label(false)
            ?>

            <?=
            $form->field($model_signup2, 'password2'/*, ['enableClientValidation' => false,]*/)
                ->passwordInput([
                    'placeholder' => $model_signup2->getAttributeLabel('password2'),
                    'autocomplete' => "off",
                    'aria-label' => $model_signup2->getAttributeLabel('password2'),
                    //'id' => "signupform2-password",
                    //'name' => "Signup2Form[password]",
                ])
                ->label(false)
            ?>

            <?=
            $form->field($model_signup2, 'password_repeat2'/*, ['enableClientValidation' => false,]*/)
                ->passwordInput([
                    'placeholder' => $model_signup2->getAttributeLabel('password_repeat2'),
                    'autocomplete' => "off",
                    'aria-label' => $model_signup2->getAttributeLabel('password_repeat2'),
                    //'id' => "signupform2-password_repeat",
                    //'name' => "Signup2Form[password_repeat]",
                ])
                ->label(false)
            ?>

            <div id="signup2-captcha-container" class="captcha-container">
                <?php
                $cnt = intval(Yii::$app->cache->get(Yii::$app->params['RegisterCacheKey']));
                if ($cnt > Preferences::getValueByKey('RegisterCountNoCaptcha', 1, 'int')) {

                    echo $form->field($model_signup2, 'reCaptchaSignup2')
                        ->widget(\himiklab\yii2\recaptcha\ReCaptcha::className(), ['siteKey' => Preferences::getValueByKey('reCaptchaPublicKey')])
                        ->label(false);

                }
                ?>
            </div>

            <?=
            $form->field($model_signup2, 'acceptRules2', [
                'template' => "",
                'inputTemplate' => "",
                'checkboxTemplate'=>'
                        <div class="form-group create-account-check" -data-toggle="buttons">
                            <label class="btn btn-checkbox active" for="accept-rules2" id="label-accept-rules2">
                                {input}
                                ' . Yii::t('forms/login-signup-form', 'I_have_read_accept') . '
                            </label>
                            <a class="-rules-dialod" -href="javascript:void(0)" href="' . Url::to(['/terms'], CREATE_ABSOLUTE_URL) . '" target="_blank" rel="noopener">' . Yii::t('forms/login-signup-form', 'Terms_and_Conditions') . '</a>
                            {error}
                            {hint}
                        </div>',
            ])
                ->checkbox(['id' => "accept-rules2", 'value' => true, 'autocomplete' => "off", 'inputTemplate' => ""])
                ->label(false)
            ?>

            <div class="form-group" style="text-align: center; margin-bottom: 0px;">
                <?= '' /*Html::submitButton(Yii::t('forms/login-signup-form', 'SignUpForFree'), ['class' => 'btn-big', 'name' => 'signup-button', 'id' => 'signup-button-form2'])*/ ?>
                <input type="submit" name="signup-button" id="signup-button-form2" value="<?= Yii::t('forms/login-signup-form', 'SignUpForFree') ?>" class="btn-big signup-button" />
                <div class="img-progress" title="loading..."></div>
            </div>

            <?php
            ActiveForm::end();
            ?>

        </div>

    </div>
    <!-- END .create-account -->
    <?php
}
?>