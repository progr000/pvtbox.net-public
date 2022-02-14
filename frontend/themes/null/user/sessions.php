<?php

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider*/

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Сеансы';
$this->params['breadcrumbs'][] = ['label' => 'Сеансы'];
$this->params['breadcrumbs'][] = ['label' => 'Устройства', 'url' => ['log-devices']];
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
                    'sess_useragent',
                    //'sess_countrycode',
                    'sess_country',
                    'sess_city',
                    'sess_ip',
                    'sess_action',
                    'sess_created',
                    //['class'=>'kartik\grid\ActionColumn'],
                ],
            ]); ?>
            <?php Pjax::end(); ?>


        </div>
    </div>

</div>
