<?php
/****DELETE-IT-FILE-IN-SH****/
use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Licenses */

$this->title = 'Create Licenses';
$this->params['breadcrumbs'][] = ['label' => 'Licenses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="licenses-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
