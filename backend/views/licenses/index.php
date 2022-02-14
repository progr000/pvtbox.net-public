<?php
/****DELETE-IT-FILE-IN-SH****/
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\LicensesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Licenses Management';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="licenses-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php /* echo Html::a('Create Licenses', ['create'], ['class' => 'btn btn-success']) */ ?>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => null,
        'pjax'=>true,
        'pjaxSettings' => [],

        'summary' => false,

        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            //'license_id',
            //'license_type',

            [
                'attribute' => 'license_description',
                'encodeLabel' => false,
                'label'=>'Description',
                'width' => '25%',
                'vAlign' => 'top',
            ],

            [
                'attribute' => 'license_limit_bytes',
                'encodeLabel' => false,
                'label'=>'Limitations<br />bytes<br /><small>If 0 then<br />no limit</small>',
                'width' => '10%',
                'vAlign' => 'top',
            ],

            [
                'attribute' => 'license_limit_days',
                'encodeLabel' => false,
                'label'=>'Number of<br />free days.<br /><small>If 0 then<br />no limit</small>',
                'width' => '10%',
                'vAlign' => 'top',
            ],

            [
                'attribute' => 'license_limit_nodes',
                'encodeLabel' => false,
                'label'=>'Number of<br />available<br />nodes.<br /><small>If 0 then<br />no limit</small>',
                'width' => '10%',
                'vAlign' => 'top',
            ],

            [
                'attribute' => 'license_count_available',
                'encodeLabel' => false,
                'label'=>'Number of<br />licenses<br />available.',
                'width' => '10%',
                'vAlign' => 'top',
            ],

            [
                'attribute' => 'license_shares_count_in24',
                'encodeLabel' => false,
                'label'=>'Number of<br />Shares<br />per day.<br /><small>If 0 then<br />no limit</small>',
                'width' => '10%',
                'vAlign' => 'top',
            ],

            [
                'attribute' => 'license_max_shares_size',
                'encodeLabel' => false,
                'label'=>'Maximum<br />size of the<br />file to be<br />shared (bytes)<br /><small>If 0 then<br />no limit</small>',
                'width' => '10%',
                'vAlign' => 'top',
            ],

            [
                'attribute' => 'license_max_count_children_on_copy',
                'encodeLabel' => false,
                'label'=>'Maximum<br />allowed<br />children<br />in folder<br />on it copy<br />operation.<br /><small>If 0 then<br />no limit</small>',
                'width' => '10%',
                'vAlign' => 'top',
            ],

            [
                'attribute' => 'license_block_server_nodes_above_bought',
                'encodeLabel' => false,
                'label'=>'Lock<br />or not<br />logins (api)<br />from server<br />nodes<br />if no more<br />available<br />license for it',
                'width' => '10%',
                'vAlign' => 'top',
            ],

            [
                'class' => 'kartik\grid\ActionColumn',
                'width' => '5%',
                'vAlign' => 'top',
                'template' => '{update}',
            ],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
