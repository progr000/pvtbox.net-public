<?php
/* @var $this yii\web\View */
/* @var $UserModel common\models\Users */
/* @var $UserPaymentsSearchModel backend\models\search\UserPaymentsSearch */
/* @var $UserPaymentsSearchDataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use kartik\grid\GridView;
use common\models\Users;
use backend\models\search\UserPaymentsSearch;

echo GridView::widget([
    'dataProvider' => $UserPaymentsSearchDataProvider,
    'filterModel' => $UserPaymentsSearchModel,

    'pjax' => false,
    'pjaxSettings' => [],
    'panel' => [
        'before' => false,
        //'after' => Functions::getLegend(Users::statusParams()),
    ],
    //'summary' => 'Показаны записи с <b>{begin, number}</b> по <b>{end, number}</b> из <b>{totalCount, number}</b>.',
    'pager' => [
        'firstPageLabel' => 'First',
        'lastPageLabel' => 'Last'
    ],

    'columns' => [

        [
            'attribute' => 'tab',
            //'filter' => ['node-info'],
            'hidden' => true,
            //'value' => function ($model) { return 'node-info'; },
        ],

        [
            'attribute' => 'pay_id',
            'width' => '80px',
        ],

        [
            'attribute' => 'pay_date',
            //s'format' => ['date', 'php:d/m/Y H:i:s'],
        ],

        [
            'attribute' => 'pay_amount',
            'hAlign' => 'right',
            'width' => '100px',
            'value' => function ($data) {
                return number_format($data->pay_amount, 2, '.', "'");
            },
        ],

        [
            'attribute' => 'pay_status',
            'width' => '150px',
            'filter' => UserPaymentsSearch::payStatuses(),
            'value' => function ($data) {
                return $data->pay_status;
            },
        ],

        [
            'attribute' => 'pay_type',
            'width' => '150px',
            'filter'=> Users::getPayTypesFilter(),
            'value' => function ($data) {
                return Users::getPayTypeName($data->pay_type);
            },
        ],

        [
            'attribute' => 'merchant_amount',
            'hAlign' => 'right',
            'width' => '100px',
            'value' => function ($data) {
                return number_format($data->merchant_amount, 2, '.', "'");
            },
        ],

        [
            'attribute' => 'merchant_status',
            'width' => '150px',
            //'filter'=>UserPayments::payStatuses(),
            'value' => function ($data) {
                return $data->merchant_status;
            },
        ],

        [
            'class' => 'kartik\grid\ActionColumn',
            'width' => '80px;',
            'vAlign' => 'top',
            'template' => '{view}', //'{view} {update}',

            'buttons' => [
                'view' => function ($url, $model) {
                    //var_dump($model); exit;
                    $buttons = Html::a(
                        '<span class="glyphicon glyphicon-eye-open"></span>',
                        '/payments/view?id=' . $model->pay_id,
                        [
                            'title' => 'View',
                            'data-pjax' => '0',
                            'target' => '_blank',
                            'class' => 'masterTooltip',
                        ]
                    );
                    return $buttons;
                },
            ],

        ],

    ],

]);