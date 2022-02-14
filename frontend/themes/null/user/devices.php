<?php

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider*/

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use common\models\UserNode;

$this->title = 'Устройства';
$this->params['breadcrumbs'][] = ['label' => 'Сеансы', 'url' => ['sessions']];
$this->params['breadcrumbs'][] = ['label' => 'Устройства'];
?>
<div class="site-contact">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-lg-12">

            <?php Pjax::begin(); ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                //'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'node_useragent',
                    'node_name',
                    [
                        'attribute' => 'node_online',
                        'format' => 'raw',
                        'value' => function ($model) {
                            if ($model->node_online > 0) { $color = "#00aa00"; } else { $color = "#aa0000"; }
                            return '<span class="badge" style="background-color: ' . $color . ';">' . UserNode::onlineLabel($model->node_online) . '</span>' ;
                        },
                    ],
                    'node_country',
                    'node_city',
                    'node_last_ip',
                    //'node_created',
                    'node_updated',
                    [
                        'class'=>'kartik\grid\ActionColumn',
                        'template' => '{unlink}',
                        'buttons' => [
                            'unlink' => function ($url) {
                                return Html::a(
                                    '<span class="glyphicon glyphicon-remove"></span>',
                                    $url,
                                    [
                                        'title' => 'Отвязать устройство',
                                        'data-pjax' => '1',
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
    </div>

</div>
