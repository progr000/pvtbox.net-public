<?php
/****DELETE-IT-FILE-IN-SH****/
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\TiketsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//$this->title = 'Tikets';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tikets-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <!-- <?= Html::a('Create Tikets', ['create'], ['class' => 'btn btn-success']) ?> -->
        <?= Html::a('Показать непрочитанные', ['/tikets', 'TiketsSearch' => ['showNew' => 1]], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Сбросить фильтр', ['/tikets'], ['class' => 'btn btn-default']) ?>
    </p>

    <?php
    Pjax::begin();
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'rowOptions' => function ($model, $key, $index, $grid)
        {
            if($model->tiket_count_new_admin > 0) {
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
            'tiket_email:email',
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
                'template' => '{view} {profile} {delete}',
                'buttons' => [
                    'profile' => function ($url, $model) {
                        if ($model->user_id > 0) {
                            return Html::a(
                                '<span class="glyphicon glyphicon-user"></span>',
                                ['/users/view', 'id' => $model->user_id],
                                [
                                    'title' => 'Профиль пользователя',
                                    'data-pjax' => '0',
                                    'target' => '_blank',
                                ]
                            );
                        } else {
                            return "";
                        }
                    },
                ],
            ],

        ],
    ]);
    Pjax::end();
    ?>
</div>
