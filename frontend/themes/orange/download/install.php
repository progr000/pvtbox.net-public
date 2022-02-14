<?php

/* @var $this yii\web\View */

use yii\helpers\Url;

$this->title = Yii::t('app/install', 'title');

?>

<div class="anything">

    <div class="anything__cont">
        <div class="title"><h2><?= Yii::t('app/install', 'You_success_register') ?></h2></div>
        <div id="install-header">
            <h1><?= Yii::t('app/install', 'Install_Application', ['APP_NAME' => Yii::$app->name]) ?></h1>
            <h3><?= Yii::t('app/install', 'The_more_devices', ['APP_NAME' => Yii::$app->name]) ?></h3>
            <a href="<?= Url::to(['/download'], CREATE_ABSOLUTE_URL) ?>" class="btn-big" target="_blank" rel="noopener">
                <span id="button-text"><?= Yii::t('app/install', 'Download_for_free') ?></span>
            </a>
        </div>
    </div>

</div>