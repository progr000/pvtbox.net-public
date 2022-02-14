<?php
/* @var $this yii\web\View */
/* @var $UserModel common\models\Users */
/* @var $TrafficSearchModel backend\models\search\TrafficSearch */
/* @var $TrafficSearchDataProvider yii\data\ActiveDataProvider */

use kartik\grid\GridView;
use common\helpers\Functions;

echo GridView::widget([
    'dataProvider' => $TrafficSearchDataProvider,
    'filterModel' => null,

    'pjax'=>false,
    'pjaxSettings' => [],
    'panel' => [
        'before' => false,
        'after' => false,
        //'footer' => false,
        'heading' => false,
        //'footerOptions' => ['class' => 'hidden'],
    ],
    'summary' => false,
    'pager' => [
        'firstPageLabel' => 'First',
        'lastPageLabel'  => 'Last'
    ],
    //'header' => false,
    //'showHeader' => false,
    //'showFooter' => false,
    //'showCaption' => false,
    //'showPageSummary' => true,


    'columns' => [
        [
            //'attribute' => 'time',
            'attribute' => 'tmstmp',
            'label' => 'Date',
            'width' => '15%',
        ],

        [
            'attribute' => 'sum_rx_wd',
            'label' => 'p2p in (rx_wd)',
            'width' => '7%',
            'format'    => 'raw',
            'value' => function ($model) use ($UserModel) {
                return '<a href="javascript:void(0)" class="show-files-for-traffic-info" data-type="rx_wd" data-date="' . $model['tmstmp'] . '" data-user-id="' . $UserModel->user_id . '">' . Functions::file_size_format($model['sum_rx_wd']) . '</a>';
            },
        ],
        [
            'attribute' => 'sum_tx_wd',
            'label' => 'p2p out (tx_wd)',
            'width' => '7%',
            'format'    => 'raw',
            'value' => function ($model) use ($UserModel)  {
                return '<a href="javascript:void(0)" class="show-files-for-traffic-info" data-type="tx_wd" data-date="' . $model['tmstmp'] . '" data-user-id="' . $UserModel->user_id . '">' . Functions::file_size_format($model['sum_tx_wd']) . '</a>';
            },
        ],
        [
            'attribute' => 'total_wd',
            'label' => 'p2p total (total wd)',
            'width' => '7%',
            'format'    => 'raw',
            'value' => function ($model) use ($UserModel)  {
                return '<a href="javascript:void(0)" class="show-files-for-traffic-info" data-type="total_wd" data-date="' . $model['tmstmp'] . '" data-user-id="' . $UserModel->user_id . '">' . Functions::file_size_format($model['total_wd']) . '</a>';
            },
            /*
            'options' => [ 'style' => "background-color: #FF0000 !important;" ],
            'contentOptions' => [ 'style' => "background-color: #FF0000 !important;" ],
            */
            'options' => ['class' => 'total-column'],
            'contentOptions' => ['class' => 'total-column'],
        ],


        [
            'attribute' => 'sum_rx_wr',
            'label' => 'turn in (rx_wr)',
            'width' => '7%',
            'format'    => 'raw',
            'value' => function ($model) use ($UserModel)  {
                return '<a href="javascript:void(0)" class="show-files-for-traffic-info" data-type="rx_wr" data-date="' . $model['tmstmp'] . '" data-user-id="' . $UserModel->user_id . '">' . Functions::file_size_format($model['sum_rx_wr']) . '</a>';
            },
        ],
        [
            'attribute' => 'sum_tx_wr',
            'label' => 'turn out (tx_wr)',
            'width' => '7%',
            'format'    => 'raw',
            'value' => function ($model) use ($UserModel)  {
                return '<a href="javascript:void(0)" class="show-files-for-traffic-info" data-type="tx_wr" data-date="' . $model['tmstmp'] . '" data-user-id="' . $UserModel->user_id . '">' . Functions::file_size_format($model['sum_tx_wr']) . '</a>';
            },
        ],
        [
            'attribute' => 'total_wr',
            'label' => 'turn total (total wr)',
            'width' => '7%',
            'format'    => 'raw',
            'value' => function ($model) use ($UserModel)  {
                return '<a href="javascript:void(0)" class="show-files-for-traffic-info" data-type="total_wr" data-date="' . $model['tmstmp'] . '" data-user-id="' . $UserModel->user_id . '">' . Functions::file_size_format($model['total_wr']) . '</a>';
            },
            'options' => ['class' => 'total-column'],
            'contentOptions' => ['class' => 'total-column'],
        ],

        /*
        [
            'attribute' => 'sum_rx_ws',
            'label' => 'ws in',
            'width' => '7%',
            'value' => function ($model) {
                return Functions::file_size_format($model['sum_rx_ws']);
            },
        ],
        [
            'attribute' => 'sum_tx_ws',
            'label' => 'ws out',
            'width' => '7%',
            'value' => function ($model) {
                return Functions::file_size_format($model['sum_tx_ws']);
            },
        ],
        [
            'attribute' => 'total_ws',
            'label' => 'ws total',
            'width' => '7%',
            'value' => function ($model) {
                return Functions::file_size_format($model['total_ws']);
            },
        ],
        */

        [
            'attribute' => 'total_rx',
            'label' => 'Total in',
            'width' => '7%',
            'format'    => 'raw',
            'value' => function ($model) use ($UserModel)  {
                return '<a href="javascript:void(0)" class="show-files-for-traffic-info" data-type="total_rx" data-date="' . $model['tmstmp'] . '" data-user-id="' . $UserModel->user_id . '">' . Functions::file_size_format($model['total_rx']) . '</a>';
            },
            'options' => ['class' => 'total-column2'],
            'contentOptions' => ['class' => 'total-column2'],
        ],
        [
            'attribute' => 'total_tx',
            'label' => 'Total out',
            'width' => '7%',
            'format'    => 'raw',
            'value' => function ($model) use ($UserModel)  {
                return '<a href="javascript:void(0)" class="show-files-for-traffic-info" data-type="total_tx" data-date="' . $model['tmstmp'] . '" data-user-id="' . $UserModel->user_id . '">' . Functions::file_size_format($model['total_tx']) . '</a>';
            },
            'options' => ['class' => 'total-column2'],
            'contentOptions' => ['class' => 'total-column2'],
        ],

    ],

]);