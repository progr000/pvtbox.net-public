<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Servers */

$this->title = $model->server_title . " ({$model->server_url})";
//$this->params['breadcrumbs'][] = ['label' => 'Servers', 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="servers-view col-lg-8">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Change', ['update', 'id' => $model->server_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete',  ['delete', 'id' => $model->server_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('To the list', ['index'], ['class' => 'btn btn-default']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'server_id',
            'server_type',
            'server_title',
            'server_url:url',
            //'server_ip',
            //'server_port',
            'server_status',
        ],
    ]) ?>

</div>
