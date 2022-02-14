<?php
/****DELETE-IT-FILE-IN-SH****/
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use common\helpers\Functions;
use common\models\News;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\NewsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'News management';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="news-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Add news', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php
    Pjax::begin();
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'pjax'=>true,
        'pjaxSettings' => [],
        'panel' => [
            'before' => false,
            'after' => Functions::getLegend(News::statusParams()),
        ],
        //'summary' => 'Показаны записи с <b>{begin, number}</b> по <b>{end, number}</b> из <b>{totalCount, number}</b>.',

        'columns' => [

            [
                'attribute' => 'news_id',
                'width' => '70px',
            ],
            [
                'attribute' => 'news_created',
                'format' => ['date', 'php:d/m/Y H:i:s'],
                'width'=>'170px',
            ],
            //'news_updated',
            'news_name',
            //'news_text:ntext',
            [
                'attribute' => 'news_status',
                'format' => 'raw',
                'hAlign'=>'center',
                'filter' => News::statusLabels(),
                'value' => function ($model) {
                    $color = "#000000";
                    $color = News::statusColor($model->news_status);
                    return '<span class="badge" style="background-color: '.$color.'">&nbsp;</span>';
                },
                'width' => '80px',
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'width' => '80px',
                'vAlign' => 'top',
                'template' => '{view} {update} {delete}',
            ],

        ],
    ]);
    Pjax::end();
    ?>

</div>
