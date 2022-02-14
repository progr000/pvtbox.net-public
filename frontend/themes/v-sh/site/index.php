<?php

/* @var $this yii\web\View */
/* @var $model_signup2 \frontend\models\forms\SignupForm2 */
/* @var $software array */

use frontend\assets\v20190812\downloadAsset;

$this->title = Yii::t('app/index', 'title');

$no_show_animation_on_index = (isset(Yii::$app->params['no_show_animation_on_index']) && Yii::$app->params['no_show_animation_on_index']);
downloadAsset::register($this);
?>
<!-- begin Index-page content -->
<div class="container" style="padding-top: 60px;">
    <!--begin .promo-->
    <h2 class="page-section-title"><?= Yii::t('app/index', 'LOGIN INTO ACCOUNT') ?></h2>
    <div class="reg">

        <?= $this->render('/layouts/auth/_login', ['model' => $this->context->model_login]); ?>

    </div>
    <!--end .promo-->
</div>
<!-- end Index-page content -->

