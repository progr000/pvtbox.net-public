<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Pages */

$this->title = 'Editing static page';// . $model->page_id;
//$this->params['breadcrumbs'][] = ['label' => 'Статические страницы', 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->page_id, 'url' => ['view', 'id' => $model->page_id]];
//$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="pages-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
