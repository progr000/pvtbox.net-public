<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\Languages;
use common\models\Pages;

/* @var $this yii\web\View */
/* @var $model common\models\Pages */

$this->title = 'View page information for ID =' . $model->page_id;
//$this->params['breadcrumbs'][] = ['label' => 'Статические страницы', 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pages-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Change', ['update', 'id' => $model->page_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->page_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('To the list', ['index'], ['class' => 'btn btn-default']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'page_id',
            'page_created',
            'page_updated',
            [
                'attribute' => 'page_status',
                'value' => Pages::getStatus($model->page_status)
            ],
            [
                'attribute' => 'template_lang',
                'value' => Languages::langLabel($model->page_lang),
            ],
            'page_title',
            'page_name',
            'page_alias',
            'page_keywords',
            'page_description',
            'page_text:ntext',
        ],
    ]) ?>

</div>
