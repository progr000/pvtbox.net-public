<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use common\models\Preferences;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\Preferences2Search */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Preferences';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="preferences-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Preferences', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'pref_id',
            'pref_title',
            'pref_key',
            'pref_value',
            [
                'attribute' => 'pref_category',
                'width' => '150px',
                'filter' => Preferences::categoriesLabels(),
                'value' => function ($data) {
                    return Preferences::categoryLabel($data->pref_category);
                },
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'width' => '80px;',
                'vAlign' => 'top',
                'template' => '{update} {delete}',
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
