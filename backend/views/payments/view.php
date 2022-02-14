<?php
/****DELETE-IT-FILE-IN-SH****/
use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\Users;

/* @var $this yii\web\View */
/* @var $model common\models\UserPayments */

$this->title = 'View payment information for ID =' . $model->pay_id;
/*
$this->params['breadcrumbs'][] = ['label' => 'UserPayments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
*/
?>
<div class="payments-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <!--
        <?= Html::a('Change', ['update', 'id' => $model->pay_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete',  ['delete', 'id' => $model->pay_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        -->
        <?= Html::a('To the list', ['index'], ['class' => 'btn btn-default']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'pay_id',
            'user_id',
            'user.user_email',
            'pay_date',
            'pay_amount',
            'pay_currency',
            'pay_status',
            [
                'attribute' => 'pay_type',
                'value' => Users::getPayTypeName($model->pay_type),
            ],
            'pay_for',

            'merchant_amount',
            'merchant_currency',
            //'merchant_updated',
            [
                'attribute' => 'merchant_status',
                'value' => $model->merchant_status,
            ],

            'merchant_raw_data',
        ],
    ]) ?>

</div>
