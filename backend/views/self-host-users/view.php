<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\SelfHostUsers */

$this->title = $model->shu_id;
?>
<div class="self-host-users-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Change', ['update', 'id' => $model->shu_id], ['class' => 'btn btn-primary']) ?>
        <?php /* Html::a('Удалить', ['delete', 'id' => $UserModel->user_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) */ ?>
        <?= Html::a('To the list', ['index'], ['class' => 'btn btn-default']) ?>
        <!-- <a href="javascript:void(0)" onclick="window.history.back();" class="btn btn-default">К списку</a>-->
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'shu_id',
            'shu_company',
            'shu_name',
            'shu_email:email',
            'shu_created',
            'shu_updated',
            'shu_status',

            'shu_support_status',
            //'shu_support_cost',
            //'shu_support_requested',

            'shu_brand_status',
            //'shu_brand_cost',
            //'shu_brand_requested',

            'shu_business_status',

            'license_count_available',
            'license_count_used',
            'license_mismatch',
            //'user_id',
        ],
    ]) ?>

</div>
