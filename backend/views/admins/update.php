<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Admins */
/* @var $current backend\models\Admins */

$this->title = 'Update Admins: ' . $model->admin_id;
//$this->params['breadcrumbs'][] = ['label' => 'Admins', 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->admin_id, 'url' => ['view', 'id' => $model->admin_id]];
//$this->params['breadcrumbs'][] = 'Update';
?>
<div class="admins-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model'   => $model,
        'current' => $current,
        'password_model' => $password_model,
    ]) ?>

</div>
