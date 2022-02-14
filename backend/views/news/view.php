<?php
/****DELETE-IT-FILE-IN-SH****/
use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\News;

/* @var $this yii\web\View */
/* @var $model common\models\News */

$this->title = 'Просмотр новости: ' . ' ' . $model->news_name;
/*
$this->params['breadcrumbs'][] = ['label' => 'News', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
*/
?>
<div class="news-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Change', ['update', 'id' => $model->news_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete',  ['delete', 'id' => $model->news_id], [
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
            'news_id',
            'news_name',
            'news_text:ntext',
            [
                'attribute' => 'news_status',
                'value' => News::statusLabel($model->news_status),
            ],
            'news_created',
            'news_updated',
        ],
    ]) ?>

</div>
