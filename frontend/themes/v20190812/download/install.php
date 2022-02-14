<?php

/* @var $this yii\web\View */

use yii\helpers\Url;

$this->title = Yii::t('app/install', 'title');

?>
<!-- begin Install-page content -->
<div class="content container">
    <div class="install">
        <div class="install__title"><?= Yii::t('app/install', 'You_success_register') ?></div>
        <div class="install__info">
            <p><?= Yii::t('app/install', 'Install_Application', ['APP_NAME' => Yii::$app->name]) ?></p>
            <p><?= Yii::t('app/install', 'The_more_devices', ['APP_NAME' => Yii::$app->name]) ?></p>
        </div>
        <a class="btn action-btn md-btn" href="<?= Url::to(['/download'], CREATE_ABSOLUTE_URL) ?>">
            <svg class="icon icon-download">
                <use xlink:href="#download"></use>
            </svg><span><?= Yii::t('app/install', 'Download_for_free') ?></span>
        </a>
    </div>
</div>
<!-- end Install-page content -->
