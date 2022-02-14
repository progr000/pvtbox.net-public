<?php

/* @var $this yii\web\View */
/* @var $model_signup2 \frontend\models\forms\SignupForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\Preferences;
use frontend\assets\orange\MainCssAsset;
use frontend\assets\orange\AppAsset;

//MainCssAsset::register($this);
//AppAsset::register($this);

$this->title = Yii::t('app/index', 'title_entrance');

?>
        <!-- .entrance -->
        <div class="entrance" id="entrance-page">

             <div class="entrance__cont">

                  <div class="title"><h2><?= Yii::t('forms/login-signup-form', 'Start_using_now', ['APP_NAME' => Yii::$app->name]) ?></h2></div>


                  <div class="form-block">

                       <div class="form-button" data-toggle="buttons">
                            <label for="radio-login" class="btn btn-radio active">
                                 <input type="radio" id="radio-login" name="radio-login" value="signup" autocomplete="off" checked="checked">
                                 <?= Yii::t('forms/login-signup-form', 'already_have_account', ['APP_NAME' => Yii::$app->name]) ?>
                            </label>
                            <label for="radio-signup" class="btn btn-radio">
                                 <input type="radio" id="radio-signup" name="radio-login" value="login" autocomplete="off">
                                 <?= Yii::t('forms/login-signup-form', 'dont_have_account', ['APP_NAME' => Yii::$app->name]) ?>
                            </label>
                       </div>

                       <div class="form-cont">

                            <div id="signup-tab" style="display: none;">
                                 <?= $this->render('/layouts/auth/_signup', ['model' => $form_signup]);?>
                            </div>

                            <div id="login-tab" style="display: none;">
                                 <?= $this->render('/layouts/auth/_login', ['model' => $form_login]);?>
                            </div>

                            <div id="rules-tab" style="display: none;">
                                 <?= $this->render('/layouts/auth/_rules');?>
                            </div>

                       </div>


                  </div>


             </div>

        </div>
        <!-- END .entrance -->

