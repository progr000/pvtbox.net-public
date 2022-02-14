<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use backend\models\Admins;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\AdminsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Admins';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admins-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create New Role', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'admin_id',
            'admin_name',
            'admin_email:email',
            //'auth_key',
            //'password_hash',
            //'password_reset_token',
            'admin_created',
            //'admin_updated',

            [
                'attribute' =>'admin_status',
                'filter' => Admins::getStatuses(),
                'value' => function($data) {
                    return Admins::getStatus($data->admin_status);
                }
            ],

            [
                'attribute' =>'admin_role',
                'filter' => Admins::getRoles(),
                'value' => function($data) {
                    return Admins::getRole($data->admin_role);
                }
            ],

            [
                'class'=>'kartik\grid\ActionColumn',
                'template' => '{update}  {delete}',
            ],
        ],
    ]); ?>


</div>
