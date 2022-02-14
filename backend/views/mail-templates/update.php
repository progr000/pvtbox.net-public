<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\MailTemplates */

$this->title = 'Изменение шаблона для письма: ' . $model->template_subject;
//$this->params['breadcrumbs'][] = ['label' => 'Mail Templates', 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->template_id, 'url' => ['view', 'id' => $model->template_id]];
//$this->params['breadcrumbs'][] = 'Update';
?>
<div class="mail-templates-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
