<?php
/* @var $this yii\web\View */
/* @var $UserModel common\models\Users */
/* @var $UserCollaborationsSearchModel backend\models\search\UserCollaborationsSearch */
/* @var $UserCollaborationsSearchDataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use kartik\grid\GridView;
use common\helpers\Functions;

echo GridView::widget([
    'dataProvider' => $UserCollaborationsSearchDataProvider,
    'filterModel' => $UserCollaborationsSearchModel,

    'pjax' => false,
    'pjaxSettings' => [],
    'panel' => [
        'before' => false,
        //'after' => Functions::getLegend(Users::statusParams()),
    ],
    //'summary' => 'Показаны записи с <b>{begin, number}</b> по <b>{end, number}</b> из <b>{totalCount, number}</b>.',
    'pager' => [
        'firstPageLabel' => 'First',
        'lastPageLabel' => 'Last'
    ],
    'columns' => [
        [
            'attribute' => 'tab',
            //'filter' => ['collaborations-info'],
            'hidden' => true,
            //'value' => function ($model) { return 'collaborations-info'; },
        ],

        [
            'attribute' => 'collaboration_id',
            'format' => 'raw',
            'hAlign' => 'center',
            'width' => '1%',
            'value' => function ($model) use ($UserModel) {
                return '<a href="/users/view?id=' . $UserModel->user_id . '&UserFilesSearch[tab]=&UserFilesSearch[collaboration_id]=' . $model->collaboration_id . '">' . $model->collaboration_id . '</a>';
            },
        ],
        [
            'attribute' => '_file_name',
            'format' => 'raw',
            'hAlign' => 'center',
            //'width' => '10%',
            'value' => function ($model) use ($UserModel) {
                /* @var $model backend\models\search\UserFilesSearch */
                return '<a class="masterTooltip" href="/users/view?UserFilesSearch[tab]=&UserFilesSearch[file_id]=' . $model->file_id . '&id=' . $UserModel->user_id . '" title="' . $model->file_name . '">' . Functions::concatString($model->file_name, 50) . '</a>';
            },
        ],
        [
            'attribute' => 'collaboration_owner_or_colleague',
            'label' => 'Owner or Colleague',
            'width' => '5%',
            'value' => function ($model) use ($UserModel) {
                //return $model->user_id;
                if ($model->user_id == $UserModel->user_id) {
                    return 'owner';
                } else {
                    return 'colleague';
                }
            },
        ],
        [
            //'class' => 'yii\grid\ActionColumn',
            'class'=>'kartik\grid\ActionColumn',
            'width' => '1%',
            'vAlign' => 'top',
            'template' => '{view-files}&nbsp;&nbsp;{view-colleagues}',
            'buttons' => [

                'view-files' => function ($url, $model) use ($UserModel) {
                    return Html::a(
                        '<span class="glyphicon glyphicon-list-alt"></span>',
                        "/users/view?id={$UserModel->user_id}&UserFilesSearch[tab]=&UserFilesSearch[collaboration_id]={$model->collaboration_id}",
                        [
                            'title' => 'View collaboratet files',
                            //'data-pjax' => '0',
                            //'target' => '_blank',
                            'data-collaboration-id' => $model->collaboration_id,
                            //'class' => 'show-colleagues',
                            'class' => 'masterTooltip',
                        ]
                    );
                },

                'view-colleagues' => function ($url, $model) {
                    return Html::a(
                        '<span class="glyphicon glyphicon-user"></span>',
                        '#',
                        [
                            'title' => 'View colleagues',
                            'data-pjax' => '0',
                            'target' => '_blank',
                            'data-collaboration-id' => $model->collaboration_id,
                            'class' => 'show-colleagues masterTooltip',
                        ]
                    );
                },
            ],
        ],
    ],
]);