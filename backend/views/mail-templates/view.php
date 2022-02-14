<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\MailTemplates;
use common\models\Languages;

/* @var $this yii\web\View */
/* @var $model common\models\MailTemplates */

$this->title = $model->template_subject;
//$this->params['breadcrumbs'][] = ['label' => 'Mail Templates', 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mail-templates-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Изменить', ['update', 'id' => $model->template_id], ['class' => 'btn btn-primary']) ?>
        <!--
        <?= Html::a('Удалить',  ['delete', 'id' => $model->template_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        -->
        <?= Html::a('К списку', ['/mail-templates'], ['name' => 'btnBack', 'class' => 'btn btn-default']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'template_id',
            [
                'attribute' => 'template_key',
                'value' => MailTemplates::keyLabel($model->template_key),
            ],
            [
                'attribute' => 'template_lang',
                'value' => Languages::langLabel($model->template_lang),
            ],
            'template_from_email:email',
            'template_from_name',
            'template_subject',
            'template_body_html:ntext',
            'template_body_text:ntext',
        ],
    ]) ?>

</div>
