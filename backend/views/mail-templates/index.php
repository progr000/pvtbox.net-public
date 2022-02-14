<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use common\models\MailTemplates;
use common\models\Languages;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\MailTemplatesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $data MailTemplates */

$this->title = 'Mail Templates';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mail-templates-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <!-- <?= Html::a('Создать новый темплейт', ['create'], ['class' => 'btn btn-success']) ?> -->
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
                'attribute' => 'template_id',
                'width' => '80px',
            ],
            [
                'attribute' => 'template_key',
                //'width' => '150px',
                'filter'=> MailTemplates::keyLabels(),
                'value' => function ($data) {
                    return MailTemplates::keyLabel($data->template_key);
                },
            ],
            [
                'attribute' => 'template_lang',
                //'width' => '150px',
                'filter'=>Languages::langLabels(),
                'value' => function ($data) {
                    return Languages::langLabel($data->template_lang);
                },
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'width' => '80px;',
                'vAlign' => 'top',
                'template' => '{view} {update}',
            ],

        ],
    ]);
    Pjax::end();
    ?>
</div>
