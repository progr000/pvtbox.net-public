<?php
/****DELETE-IT-FILE-IN-SH****/
use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\Software;

/* @var $this yii\web\View */
/* @var $model common\models\Software */

$this->title = $model->software_id;
//$this->params['breadcrumbs'][] = ['label' => 'Applications Management', 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="download-links-view col-lg-8">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Change', ['update', 'id' => $model->software_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->software_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('To the list', ['index'], ['class' => 'btn btn-default']) ?>
    </p>
    <?php
    if ($model->software_program_type == Software::PROGRAM_TYPE_FILE) {
        $attr = 'software_file_name';
    } else {
        $attr = 'software_url';
    }
    ?>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'software_id',
            [
                'attribute' => 'software_type',
                'value' => Software::getType($model->software_type),
            ],
            'software_description',
            $attr,
            'software_version',
            'software_created',
            'software_updated',
            [
                'attribute' => 'software_status',
                'value' => Software::getStatus($model->software_status),
            ],
        ],
    ]) ?>

</div>
