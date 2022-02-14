<?php
/****DELETE-IT-FILE-IN-SH****/
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Licenses */

$this->title = 'Update Licenses: ' . $model->license_id;
//$this->params['breadcrumbs'][] = ['label' => 'Licenses', 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->license_id, 'url' => ['view', 'id' => $model->license_id]];
//$this->params['breadcrumbs'][] = 'Update';
?>
<div class="licenses-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
