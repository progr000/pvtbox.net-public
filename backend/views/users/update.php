<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\Users */
/* @var $Admin \backend\models\Admins */
/* @var $password yii\base\DynamicModel */

$this->title = 'Modify user: ' . $user->user_email . ' (ID=' . $user->user_id . ')';
/*
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->user_id, 'url' => ['view', 'id' => $model->user_id]];
$this->params['breadcrumbs'][] = 'Update';
*/
?>
<div class="users-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'user' => $user,
        'password' => $password,
        'Admin' => $Admin,
    ]) ?>

</div>
