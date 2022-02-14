<?php
/* @var $this yii\web\View */
/* @var $UserNodeSearchModel backend\models\search\UserNodeSearch */
/* @var $UserNodeSearchDataProvider yii\data\ActiveDataProvider */

use kartik\grid\GridView;
use common\helpers\Functions;
use common\models\UserNode;

echo GridView::widget([
    'dataProvider' => $UserNodeSearchDataProvider,
    'filterModel' => $UserNodeSearchModel,

    'pjax'=>false,
    'pjaxSettings' => [],
    'panel' => [
        'before' => false,
        //'after' => Functions::getLegend(Users::statusParams()),
    ],
    //'summary' => 'Показаны записи с <b>{begin, number}</b> по <b>{end, number}</b> из <b>{totalCount, number}</b>.',
    'pager' => [
        'firstPageLabel' => 'First',
        'lastPageLabel'  => 'Last'
    ],
    'columns' => [
        [
            'attribute' => 'tab',
            //'filter' => ['node-info'],
            'hidden' => true,
            //'value' => function ($model) { return 'node-info'; },
        ],

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
                /** @var $model \common\models\UserNode */
                if ($model->node_devicetype == UserNode::DEVICE_BROWSER) {
                    $model->node_online = UserNode::ONLINE_OFF;
                    if (time() - strtotime($model->node_updated) < UserNode::WebFMOnlineTimeout) {
                        $model->node_online = UserNode::ONLINE_ON;
                    }
                }

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
            'width' => '14%',
            'value' => function ($model) {
                /** @var \common\models\UserNode $model */
                return '<a href="javascript:void(0)" class="masterTooltip show-node-name" data-node-name="' . $model->node_name . '" onclick="javascript:void(0)" title="' . $model->node_name . '" >' . (Functions::concatString($model->node_name, 15)) . '</a>';
            },
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
            'width' => '14%',
        ],

        [
            'attribute' => 'node_devicetype',
            'filter' => UserNode::devicesLabels(),
            'value' => function ($model) {
                return UserNode::deviceLabel($model->node_devicetype);
            },
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '14%',
        ],

        [
            'attribute' => 'is_server',
            'label' => 'Is server',
            'filter' => [0 => "No", 1 => "Yes"],
            'value' => function ($model) {
                return ($model->is_server) ? "Yes" : "No";
            },
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '3%',
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
            'width' => '18%',
        ],

        [
            'attribute' => 'node_updated',
            'filter' => false,
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '18%',
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

        [
            'attribute' => 'node_prev_status',
            'format' => 'raw',
            'encodeLabel' => false,
            'hAlign'=>'center',
            'filter' => UserNode::statusLabels(),
            'value' => function ($model) {
                return UserNode::statusLabel($model->node_prev_status);
            },
            'width' => '100px',
        ],
    ],
]);
