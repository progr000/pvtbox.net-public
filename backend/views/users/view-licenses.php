<?php
/* @var $this yii\web\View */
/* @var $UserLicensesSearchModel backend\models\search\UserLicensesSearch */
/* @var $UserLicensesSearchDataProvider yii\data\ActiveDataProvider */
/* @var $UserServerLicensesSearchModel backend\models\search\UserServerLicensesSearch */
/* @var $UserServerLicensesSearchDataProvider yii\data\ActiveDataProvider */

use kartik\grid\GridView;
use common\helpers\Functions;
use common\models\Licenses;

echo GridView::widget([
    'dataProvider' => $UserLicensesSearchDataProvider,
    'filterModel' => $UserLicensesSearchModel,
    'pjax' => false,
    'pjaxSettings' => [],
    'panel' => [
        'before' => false,
        //'after' => Functions::getLegend(Users::statusParams()),
        //'type' => GridView::TYPE_PRIMARY,
        'heading' => 'User-License Information (License for collaborations)',
    ],
    //'summary' => 'Показаны записи с <b>{begin, number}</b> по <b>{end, number}</b> из <b>{totalCount, number}</b>.',
    'pager' => [
        'firstPageLabel' => 'First',
        'lastPageLabel' => 'Last'
    ],
    'columns' => [
        [
            'attribute' => 'tab',
            //'filter' => ['licenses-info'],
            'hidden' => true,
            //'value' => function ($model) { return 'node-info'; },
        ],

        [
            'attribute' => 'lic_id',
            'format' => 'raw',
            'hAlign' => 'center',
            'width' => '1%',
        ],
        [
            'attribute' => 'lic_period',
            'format' => 'raw',
            'hAlign' => 'center',
            'width' => '10%',
            'value' => function ($model) {
                /* @var $model backend\models\search\UserLicensesSearch */
                return Licenses::getBilledByPeriod($model->lic_period, true);
            },
        ],

        [
            'attribute' => 'lic_colleague_email',
            'label' => 'Colleague',
            'format' => 'raw',
            'hAlign' => 'center',
            'width' => '20%',
            'value' => function ($model) {
                /* @var $model backend\models\search\UserLicensesSearch */
                if ($model->lic_colleague_user_id) {
                    return "<a class='masterTooltip' href=\"/users/view?id={$model->lic_colleague_user_id}\" title=\"{$model->lic_colleague_email}\">" . Functions::concatString($model->lic_colleague_email, 30) . "</a>";
                } else {
                    return $model->lic_colleague_email;
                }
            },
        ],

        [
            'attribute' => 'lic_start',
            'label' => "Date start",
            'format' => 'raw',
        ],

        [
            'attribute' => 'lic_end',
            'label' => "Date end",
            'format' => 'raw',
        ],

    ],
]);

echo "<hr />";

echo GridView::widget([
    'dataProvider' => $UserServerLicensesSearchDataProvider,
    'filterModel' => $UserServerLicensesSearchModel,
    'pjax' => false,
    'pjaxSettings' => [],
    'panel' => [
        'before' => false,
        //'after' => Functions::getLegend(Users::statusParams()),
        //'type' => GridView::TYPE_PRIMARY,
        'heading' => 'Server-License Information (License for server-nodes)',
    ],
    //'summary' => 'Показаны записи с <b>{begin, number}</b> по <b>{end, number}</b> из <b>{totalCount, number}</b>.',
    'pager' => [
        'firstPageLabel' => 'First',
        'lastPageLabel' => 'Last'
    ],
    'columns' => [
        [
            'attribute' => 'tab',
            //'filter' => ['licenses-info'],
            'hidden' => true,
            //'value' => function ($model) { return 'node-info'; },
        ],

        [
            'attribute' => 'lic_srv_id',
            'format' => 'raw',
            'hAlign' => 'center',
            'width' => '1%',
        ],
        [
            'attribute' => 'lic_srv_period',
            'format' => 'raw',
            'hAlign' => 'center',
            'width' => '10%',
            'value' => function ($model) {
                /* @var $model backend\models\search\UserServerLicensesSearch */
                return Licenses::getBilledByPeriod($model->lic_srv_period, true);
            },
        ],

        [
            'attribute' => 'lic_srv_colleague_email',
            'label' => 'Colleague',
            'format' => 'raw',
            'hAlign' => 'center',
            'width' => '20%',
            'value' => function ($model) {
                /* @var $model backend\models\search\UserServerLicensesSearch */
                if ($model->lic_srv_colleague_user_id) {
                    return "<a class='masterTooltip' href=\"/users/view?id={$model->lic_srv_colleague_user_id}\" title=\"{$model->lic_srv_colleague_email}\">" . Functions::concatString($model->lic_srv_colleague_email, 30) . "</a>";
                } else {
                    return $model->lic_srv_colleague_email;
                }
            },
        ],

        [
            'attribute' => 'node_name',
            'label' => 'NodeName',
            'format' => 'raw',
            'hAlign' => 'center',
            'width' => '20%',
            'value' => function ($model) {
                /* @var $model backend\models\search\UserServerLicensesSearch */
                if ($model->node_name) {
                    return "<a class='masterTooltip' href=\"/users/view?id={$model->node_name}\" title=\"{$model->node_name}\">" . Functions::concatString($model->node_name, 30) . "</a>";
                } else {
                    return $model->node_name;
                }
            },
        ],

        [
            'attribute' => 'lic_srv_start',
            'label' => "Date start",
            'format' => 'raw',
        ],

        [
            'attribute' => 'lic_srv_end',
            'label' => "Date end",
            'format' => 'raw',
        ],

    ],
]);
