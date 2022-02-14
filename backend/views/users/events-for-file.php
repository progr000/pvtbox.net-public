<?php
/* @var $this yii\web\View */
/* @var $model backend\models\search\EventsForFileSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use kartik\grid\GridView;
use common\helpers\FileSys;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => null,

    'pjax'=>false,
    'pjaxSettings' => [],
    'panel' => [
        'before' => false,
        'after' => false,
        'footer' => false,
        'heading' => false,
        'footerOptions' => ['class' => 'hidden'],
    ],
    'summary' => false,
    'pager' => false,
    //'header' => false,
    //'showHeader' => false,
    'showFooter' => false,
    //'showCaption' => false,
    'showPageSummary' => false,
    'columns' => [

        [
            'attribute' => 'event_id',
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '1%',
        ],
        [
            'attribute' => 'event_type',
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '1%',
            'value' => function ($model) {
                /* @var $model backend\models\search\EventsForFileSearch */
                return $model->getType($model->event_type);
            },
        ],
        [
            'attribute' => 'event_timestamp',
            'label' => 'Date',
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '10%',
            'value' => function ($model) {
                return date(SQL_DATE_FORMAT, $model->event_timestamp);
            },
        ],
        [
            'attribute' => 'file_name_before_event',
            'label' => "Name before",
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '20%',
            'value' => function ($model) {
                return FileSys::formatFileName($model->file_name_before_event, 50);
            },
        ],
        [
            'attribute' => 'file_name_after_event',
            'label' => "Name after",
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '20%',
            'value' => function ($model) {
                return FileSys::formatFileName($model->file_name_after_event, 50);
            },
        ],

        [
            'attribute' => '_node_name',
            'label' => 'NodeName',
            'format' => 'raw',
            'value' => function ($model) {
                return '<a href="/users/view?UserNodeSearch[tab]=&UserNodeSearch[node_id]=' . $model->node_id . '&id=' . $model->user_id . '">' . $model->node->node_name . '</a>';
                //return $model->node->node_name;
            },
            'width' => '10%',
        ],

        [
            'attribute' => 'parent_before_event',
            'label' => "Parent before",
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '10%',
            'value' => function ($model) {
                return '<a href="/users/view?UserFilesSearch[tab]=&UserFilesSearch[file_id]=' . $model->parent_before_event . '&id=' . $model->user_id . '">' . $model->parent_before_event . '</a>';
            }
        ],

        [
            'attribute' => 'parent_after_event',
            'label' => "Parent after",
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '10%',
            'value' => function ($model) {
                return '<a href="/users/view?UserFilesSearch[tab]=&UserFilesSearch[file_id]=' . $model->parent_after_event . '&id=' . $model->user_id . '">' . $model->parent_before_event . '</a>';
            }
        ],

    ],
]);