<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use common\models\Servers;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ServersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $data common\models\Servers */

$this->title = 'Servers Management';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="servers-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create new Server', ['create'], ['class' => 'btn btn-success']) ?>
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
            'after' => "",
        ],
        'summary' => 'Показаны записи с <b>{begin, number}</b> по <b>{end, number}</b> из <b>{totalCount, number}</b>.',

        'columns' => [
            [
                'attribute' => 'server_id',
                'width' => '80px',
            ],
            [
                'attribute' => 'server_type',
                'label' => 'Type',
                'width' => '150px',
                'filter' => Servers::serverTypes(),
                'value' => function ($data) {
                    return Servers::getType($data->server_type);
                },
            ],

            [
                'attribute' => 'server_title',
                'label' => 'Description',
            ],
            'server_url:url',
            //'server_ip',
            // 'server_port',
            [
                'attribute' => 'server_status',
                'label' => 'Status',
                'width' => '150px',
                'filter' => Servers::serverStatus(),
                'value' => function ($data) {
                    return Servers::getStatus($data->server_status);
                },
            ],

            [
                'class' => 'kartik\grid\ActionColumn',
                'width' => '80px;',
                'vAlign' => 'top',
                'template' => '{view} {update} {delete}',
            ],

        ],
    ]);
    Pjax::end();
    ?>
</div>
