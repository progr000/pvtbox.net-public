<?php

/* @var $this yii\web\View */
/* @var $user common\models\Users */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['user/confirm-registration', 'token' => $user->password_reset_token]);
?>
Hello <?= $user->user_name ?>,

Follow the link below to confirm your registration:

<?= $resetLink ?>
