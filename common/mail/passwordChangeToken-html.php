<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\Users */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['user/change-password', 'token' => $user->password_reset_token]);
?>
<div class="password-reset">
    <p>Hello <?= Html::encode($user->user_name) ?>,</p>

    <p>Follow the link below to change your password:</p>

    <p><?= Html::a(Html::encode($resetLink), $resetLink) ?></p>
</div>
