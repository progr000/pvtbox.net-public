<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Pages */

$this->title = 'Add new static page';
//$this->params['breadcrumbs'][] = ['label' => 'Статические страницы', 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pages-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
