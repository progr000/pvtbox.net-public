<?php

use kartik\grid\GridView;
use common\models\UserNode;
use common\helpers\Functions;

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
            'attribute' => 'node_id',
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '50px',
        ],

        [
            'attribute' => 'node_online',
            'format' => 'raw',
            'hAlign'=>'center',
            'filter' => UserNode::onlineLabels(),
            'value' => function ($model) {
                if ($model->node_online) {
                    return '<span class="badge" style="background-color: #25BB02">&nbsp;</span>';
                } else {
                    return '<span class="badge" style="background-color: #CCCCCC">&nbsp;</span>';
                }
            },
            'width' => '50px',
        ],

        [
            'attribute' => 'node_name',
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '15%',
        ],
        [
            'attribute' => 'node_osname',
            /*
            'filter' => UserNode::osLabels(),
            'value' => function ($model) {
                return UserNode::osLabel($model->node_ostype);
            },
            */
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '15%',
        ],

        [
            'attribute' => 'node_devicetype',
            'filter' => UserNode::devicesLabels(),
            'value' => function ($model) {
                return UserNode::deviceLabel($model->node_devicetype);
            },
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '15%',
        ],

        [
            'attribute' => 'node_disk_usage',
            'filter' => false,
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '5%',
            'value' => function ($model) {
                return Functions::file_size_format($model->node_disk_usage);
            },
        ],

        [
            'attribute' => 'node_last_ip',
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '5%',
        ],

        [
            'attribute' => 'node_created',
            'filter' => false,
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '20%',
        ],

        [
            'attribute' => 'node_updated',
            'filter' => false,
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '20%',
        ],

        [
            'attribute' => 'node_status',
            'format' => 'raw',
            'hAlign'=>'center',
            'filter' => UserNode::statusLabels(),
            'value' => function ($model) {
                return UserNode::statusLabel($model->node_status);
            },
            'width' => '100px',
        ],
    ],
]);