<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Servers */

$this->title = 'Изменение записи о сервере: ' . $model->server_title . " ({$model->server_url})";
//$this->params['breadcrumbs'][] = ['label' => 'Servers', 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->server_id, 'url' => ['view', 'id' => $model->server_id]];
//$this->params['breadcrumbs'][] = 'Update';
?>
<div class="servers-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
