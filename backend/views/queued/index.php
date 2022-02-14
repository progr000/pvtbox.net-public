<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use backend\models\search\QueuedEventsSearch;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\QueuedEventsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Queued Events';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="queued-events-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'job_id',
                'width' => '5%',
            ],
            [
                'width' => '5%',
                'attribute' => 'queue_id',
                'filter' => QueuedEventsSearch::getQueuesIds(),
            ],
            [
                'attribute' => '_user_email',
                'width' => '15%',
                'label' => 'E-mail',
                'format' => 'raw',
                'value' => function ($data) {
                    if ($data->user) {
                        return '<a href="/users/view?id=' . $data->user_id . '">' . $data->user->user_email . '</a>';
                    } else {
                        return 'No user need';
                    }
                },
                /*
                'value' => function ($data) {
                    //var_dump($data);exit;
                    return $data->users->_user_email;
                },
                */
            ],
            [
                'attribute' => 'job_type',
                'width' => '10%',
                'filter'=> QueuedEventsSearch::queuedTypes(),
                'value' => function ($data) {
                    return $data->job_type;
                },
            ],
            [
                'attribute' => 'job_status',
                'width' => '10%',
                'filter'=> QueuedEventsSearch::queuedStatuses(),
                'value' => function ($data) {
                    return $data->job_status;
                },
            ],
            'job_created',
            'job_started',
            'job_finished',

            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
