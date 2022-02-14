<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\MailTemplates */

$this->title = 'Создание шаблона для письма';
//$this->params['breadcrumbs'][] = ['label' => 'Mail Templates', 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mail-templates-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
