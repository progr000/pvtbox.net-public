<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

?>

<div id="rules-content" style="height: 300px; overflow-y: auto; padding: 5px;">
    <?= Yii::t('app/terms', 'html_text') ?>
</div>
<hr />

<?= Html::button(Yii::t('forms/login-signup-form', 'Back', ['APP_NAME' => Yii::$app->name]), ['onclick' => 'showSignup()', 'class' => 'btn-big', 'name' => 'signup2-button']); ?>
