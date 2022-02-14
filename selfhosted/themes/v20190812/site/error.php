<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = $name;
?>
<!-- begin Error-page content -->
<div class="content container">
    <h1 class="centered"><?= Html::encode($this->title) ?></h1>
    <div class="page-section-description">
        <?= nl2br(Html::encode($message)) ?>
    </div>
</div>
<!-- end Error-page content -->
