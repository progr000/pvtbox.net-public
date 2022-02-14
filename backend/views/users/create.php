<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\Users */
/* @var $Admin \backend\models\Admins */
/* @var $password yii\base\DynamicModel */

$this->title = 'Создание нового пользователя';
/*
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
*/
?>
<div class="users-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'user' => $user,
        'password' => $password,
        'Admin' => $Admin,
    ]) ?>

</div>
