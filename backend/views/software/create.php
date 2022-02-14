<?php
/****DELETE-IT-FILE-IN-SH****/
use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Software */

$this->title = 'Загрузка приложения';
$this->params['breadcrumbs'][] = ['label' => 'Управление приложениями', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="download-links-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
