<?php

/** @var $this yii\web\View */
/** @var $SelfHostUser \common\models\SelfHostUsers */
/** @var $form_signup \frontend\models\forms\SignupForm */

$this->title = 'Self Hosted Admin Panel';
?>
<!-- begin index-page content -->
<div class="">
    <div class="container" style="padding: 40px 0 40px 0;">
        <h2 class="page-section-title"><?= Yii::t('app/index', 'CREATE_SELF_HOST_ACCOUNT') ?></h2>
        <div class="reg">

            <?= $this->render('/layouts/auth/_signup', ['model' => $form_signup]); ?>

        </div>
    </div>
</div>
<!-- end index-page content -->
