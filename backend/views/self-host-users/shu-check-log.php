<?php

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\SelfHostUsersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use kartik\grid\GridView;

?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    //'filterModel' => $searchModel,

    'pjax'=>true,
    'pjaxSettings' => [],
    'panel' => false,
    //'summary' => 'Показаны записи с <b>{begin, number}</b> по <b>{end, number}</b> из <b>{totalCount, number}</b>.',
    //'summary' => "Showing {begin, number}-{end, number} of {totalCount, number} users.",

    'columns' => [


        [
            'attribute' => 'check_ip',
            'label' => 'IP',
            'encodeLabel' => false,
        ],

        [
            'attribute' => 'check_created',
            'label' => 'Date',
            'encodeLabel' => false,
        ],

    ],
]);
?>