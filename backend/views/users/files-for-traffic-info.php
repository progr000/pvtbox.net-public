<?php
/* @var $this yii\web\View */
/* @var $model backend\models\search\TrafficSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $type string */

use kartik\grid\GridView;
use common\helpers\FileSys;
use common\helpers\Functions;

$coll = [
    [
        'attribute' => 'record_created',
        'label' => 'Date',
        'format' => 'raw',
        'hAlign'=>'center',
        'width' => '10%',
        'value' => function ($model) {
            return $model['record_created'];
        },
    ],
    [
        'attribute' => 'file_name',
        'label' => "File",
        'format' => 'raw',
        'hAlign'=>'center',
        'width' => '20%',
        'value' => function ($model) {
            return FileSys::formatFileName($model['file_name'], 50);
        },
    ],
    [
        'attribute' => 'type_of_data',
        'label' => "Type of Data",
        'format' => 'raw',
        'hAlign'=>'center',
        'width' => '10%',
        'value' => function ($model) {
            return $model['type_of_data'];
        },
    ],
    [
        'attribute' => 'node_name',
        'label' => "Node Name",
        'format' => 'raw',
        'hAlign'=>'center',
        'width' => '10%',
        'value' => function ($model) {
            return '<a href="/users/view?UserNodeSearch[tab]=&UserNodeSearch[node_id]=' . $model['node_id'] . '&id=' . $model['user_id'] . '">' . $model['node_name'] . '</a>';
        },
    ],

    [
        'attribute' => 'is_share',
        'label' => "Is Share",
        'format' => 'raw',
        'hAlign'=>'center',
        'width' => '20%',
        'value' => function ($model) {
            return $model['is_share'] ? "Yes" : "No";
        },
    ],
];

// WD
if (in_array($type, ['rx_wd', 'total_wd', 'total_rx'])) {
    $coll[] = [
        'attribute' => 'rx_wd',
        'label' => "p2p in (rx_wd)",
        'format' => 'raw',
        'hAlign'=>'center',
        'width' => '20%',
        'value' => function ($model) {
            return Functions::file_size_format($model['rx_wd']);
        },
    ];
}
if (in_array($type, ['tx_wd', 'total_wd', 'total_tx'])) {
    $coll[] = [
        'attribute' => 'tx_wd',
        'label' => "p2p out (tx_wd)",
        'format' => 'raw',
        'hAlign'=>'center',
        'width' => '20%',
        'value' => function ($model) {
            return Functions::file_size_format($model['tx_wd']);
        },
    ];
}

// WR
if (in_array($type, ['rx_wr', 'total_wr', 'total_rx'])) {
    $coll[] = [
        'attribute' => 'rx_wr',
        'label' => "turn in (rx_wr)",
        'format' => 'raw',
        'hAlign'=>'center',
        'width' => '20%',
        'value' => function ($model) {
            return Functions::file_size_format($model['rx_wr']);
        },
    ];
}
if (in_array($type, ['tx_wr', 'total_wr', 'total_tx'])) {
    $coll[] = [
        'attribute' => 'tx_wr',
        'label' => "turn out (tx_wr)",
        'format' => 'raw',
        'hAlign'=>'center',
        'width' => '20%',
        'value' => function ($model) {
            return Functions::file_size_format($model['tx_wr']);
        },
    ];
}

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
    'columns' => $coll,
]);