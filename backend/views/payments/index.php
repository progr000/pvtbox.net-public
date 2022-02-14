<?php
/****DELETE-IT-FILE-IN-SH****/
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use common\models\Users;
use common\models\UserPayments;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\UserPaymentsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Payment Management';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payments-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php //echo $this->render('_search', ['model' => $searchModel]); ?>

    <!--
    <p>
        <?= Html::a('Add payment manually', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    -->
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
        //'summary' => 'Показаны записи с <b>{begin, number}</b> по <b>{end, number}</b> из <b>{totalCount, number}</b>.',

        'columns' => [

            [
                'attribute' => 'pay_id',
                'width' => '80px',
            ],

            [
                'attribute' => 'pay_date',
                //s'format' => ['date', 'php:d/m/Y H:i:s'],
            ],

            [
                'attribute' => '_user_email',
                'label' => 'E-mail',
                'format' => 'raw',
                'value' => function ($data) {
                    return '<a href="/users/view?id=' . $data->user_id . '">' . $data->user->_user_email . '</a>';
                },
                /*
                'value' => function ($data) {
                    //var_dump($data);exit;
                    return $data->users->_user_email;
                },
                */
            ],

            [
                'attribute' => 'pay_amount',
                'hAlign' => 'right',
                'width' => '100px',
                'value' => function ($data) {
                    return number_format($data->pay_amount, 2, '.', "'");
                },
            ],

            [
                'attribute' => 'pay_status',
                'width' => '150px',
                'filter'=>UserPayments::payStatuses(),
                'value' => function ($data) {
                    return $data->pay_status;
                },
            ],

            [
                'attribute' => 'pay_type',
                'width' => '150px',
                'filter'=> Users::getPayTypesFilter(),
                'value' => function ($data) {
                    return Users::getPayTypeName($data->pay_type);
                },
            ],

            [
                'attribute' => 'merchant_amount',
                'hAlign' => 'right',
                'width' => '100px',
                'value' => function ($data) {
                    return number_format($data->merchant_amount, 2, '.', "'");
                },
            ],

            [
                'attribute' => 'merchant_status',
                'width' => '150px',
                //'filter'=>UserPayments::payStatuses(),
                'value' => function ($data) {
                    return $data->merchant_status;
                },
            ],

            [
                'class' => 'kartik\grid\ActionColumn',
                'width' => '80px;',
                'vAlign' => 'top',
                'template' => '{view}', //'{view} {update}',
            ],

        ],
    ]);
    Pjax::end();
    ?>
</div>
