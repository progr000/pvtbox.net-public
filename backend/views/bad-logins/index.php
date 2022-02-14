<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\BadLoginsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Bad Logins';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bad-logins-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            //'bl_id',
            //'bl_created',
            //'bl_updated',


            [
                'attribute' => 'bl_type',
                'filter' => \common\models\BadLogins::typesList(),
            ],

            [
                'attribute' => 'bl_ip',
            ],

            [
                'attribute' => 'bl_count_tries',
                'label' => 'Count tries',
                'encodeLabel' => false,
                'filter' => false,
            ],

            [
                'attribute' => 'bl_last_timestamp',
                'label' => 'Last try at',
                'encodeLabel' => false,
                'filter' => false,
                'value' => function($data) {
                    return date(SQL_DATE_FORMAT, $data->bl_last_timestamp);
                }
            ],

            [
                'attribute' => 'bl_locked',
                'label' => 'Locked or not',
                'encodeLabel' => false,
                'filter' => false,
                'value' => function($data) {
                    return ($data->bl_locked) ? 'LOCKED' : 'FREE';
                }
            ],

            [
                'attribute' => 'bl_lock_seconds',
                'label' => 'Count seconds for lock',
                'encodeLabel' => false,
                'filter' => false,
                'value' => function($data) {
                    return ($data->bl_lock_seconds) ? $data->bl_lock_seconds : '';
                }
            ],

            [
                //'class' => 'yii\grid\ActionColumn',
                'class'=>'kartik\grid\ActionColumn',
                'width' => '1%',
                'vAlign' => 'top',
                'template' => '{delete}',
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
