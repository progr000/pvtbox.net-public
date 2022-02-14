<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\UserActionsLog */

$this->title = $model->record_id;
//$this->params['breadcrumbs'][] = ['label' => 'Mailqs', 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mailq-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('To the list', ['index'], ['class' => 'btn btn-default']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'record_id',
            'action_created',
            'action_url',
            'action_type',
            [
                'attribute' => 'action_raw_data',
                'format' => 'raw',
                'value' => nl2br($model->action_raw_data),
            ],
            'user_id',
            //'site_url:url',
            'site_absolute_url:url',
        ],
    ]) ?>

</div>
