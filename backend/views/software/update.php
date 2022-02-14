<?php
/****DELETE-IT-FILE-IN-SH****/
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Software */

$this->title = 'Editing the uploaded program';// . $model->software_id;
//$this->params['breadcrumbs'][] = ['label' => 'Application Management', 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->software_id, 'url' => ['view', 'id' => $model->software_id]];
//$this->params['breadcrumbs'][] = 'Update';
?>
<div class="download-links-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
