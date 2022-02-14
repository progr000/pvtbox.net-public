<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use frontend\widgets\news\NewsWidget;

$this->title = Yii::t('app', 'sn_NFS');
?>
<div class="site-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="body-content">

        <?= NewsWidget::widget(['message' => 'Good morning']) ?>

    </div>
</div>
