<?php

use yii\helpers\Html;
//use yii\grid\GridView;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use common\helpers\Functions;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\TiketsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->title = 'Tikets';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tikets-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="row">
        <div class="col-sm-6">
            <!-- <?= Html::a('Create Tikets', ['create'], ['class' => 'btn btn-success']) ?> -->
            <?= Html::a('Показать непрочитанные', ['/tikets', 'TiketsSearch' => ['showNew' => 1]], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Сбросить фильтр', ['/tikets'], ['class' => 'btn btn-default']) ?>
        </div>
        <div class="col-sm-6 text-right">
            <?= Html::a('Новый тикет', ['/tikets/create'], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
    <p></p>

    <?php
    Pjax::begin();
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'rowOptions' => function ($model, $key, $index, $grid)
        {
            if($model->tiket_count_new_user > 0) {
                return ['style' => 'font-weight: bold;'];
            }
        },

        'pjax'=>true,
        'pjaxSettings' => [],
        'panel' => [
            'before' => false,
            'after' => false,
        ],
        'summary' => 'Показаны записи с <b>{begin, number}</b> по <b>{end, number}</b> из <b>{totalCount, number}</b>.',

        /*
            // 'tiket_count_new_user',
            // 'tiket_count_new_admin',
            // 'user_id',
        */
        'columns' => [
            [
                'attribute' => 'tiket_id',
                'width' => '80px',
            ],
            [
                'attribute' => 'tiket_created',
                'format' => ['date', 'php:d/m/Y H:i:s'],
            ],
            //'tiket_email:email',
            /*
            [
                'attribute' => 'tiket_email',
                'value' => function($data) {
                    return $data->tiket_email . "\n". $data->tiket_name;
                }
            ],
            */
            [
                'attribute' => 'tiket_theme',
                'hAlign'=>'right',
            ],
            [
                'class'=>'kartik\grid\ActionColumn',
                'width' => '110px',
                'vAlign' => 'top',
                'template' => '{view} {delete}',
                'buttons' => [

                    'delete' => function ($url, $model) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-trash"></span>',
                            //$url.'&qs='.base64_encode(Yii::$app->request->queryString),
                            $url.Functions::prepareQS(['id', '_pjax']),
                            [
                                'title' => "Удалить",
                                'data-confirm' => "Вы действительно хотите удалить данную запись?",
                                'data-method' => "post",
                                'data-pjax' => "w0",
                            ]
                        );
                    },

                ],
            ],

        ],
    ]);
    Pjax::end();
    ?>
</div>
