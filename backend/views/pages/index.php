<?php
/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\PagesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use common\models\Languages;
use common\models\Pages;

$this->title = 'Static pages';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pages-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Add new Page', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'summary' => '',
        'columns' => [
            [
                'attribute' => 'page_id',
                'width' => '80px',
            ],
            'page_created',
            'page_name',
            [
                'attribute' => 'page_lang',
                //'width' => '150px',
                'filter'=>Languages::langLabels(),
                'value' => function ($data) {
                    return Languages::langLabel($data->page_lang);
                },
            ],
            [
                'attribute' => 'page_status',
                //'width' => '150px',
                'filter'=>Pages::statuses(),
                'value' => function ($data) {
                    return Pages::getStatus($data->page_status);
                },
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'width' => '80px;',
                'vAlign' => 'top',
                'template' => '{view} {update} {delete}',

                'buttons' => [
                    'view' => function ($url) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-eye-open"></span>',
                            $url,
                            [
                                'title' => 'Просмотр1',
                                'data-pjax' => '0',
                                'target' => '_blank',
                            ]
                        );
                    },
                ],

            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
