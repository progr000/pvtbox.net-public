<?php
/* @var $this yii\web\View */
/* @var $model backend\models\search\ColleaguesForCollaborationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use kartik\grid\GridView;
use common\models\UserColleagues;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => null,

    'pjax'=>false,
    'pjaxSettings' => [],
    'panel' => [
        'before' => false,
        'after' => false,
        'footer' => false,
        'heading' => false,
        'footerOptions' => ['class' => 'hidden'],
    ],
    'summary' => false,
    'pager' => false,
    //'header' => false,
    //'showHeader' => false,
    'showFooter' => false,
    //'showCaption' => false,
    'showPageSummary' => false,
    'columns' => [

        [
            'attribute' => 'colleague_id',
            'label' => 'ColleagueId',
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '1%',
        ],
        [
            'attribute' => 'user_id',
            'label' => 'UserId',
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '1%',
        ],
        [
            'attribute' => 'colleague_email',
            'label' => 'E-Mail',
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '10%',

        ],
        [
            'attribute' => 'colleague_status',
            'label' => "Status",
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '10%',
            /*
            'value' => function ($model) {
                return FileSys::formatFileName($model->file_name_before_event, 50);
            },
            */
        ],

        [
            'attribute' => 'colleague_invite_date',
            'label' => "Date",
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '10%',

            'value' => function ($model) {
                return ($model->colleague_status == UserColleagues::STATUS_JOINED)
                    ? $model->colleague_joined_date
                    : $model->colleague_invite_date;
            }

        ],

        [
            'attribute' => 'colleague_permission',
            'label' => "Permission",
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '10%',
            /*
            'value' => function ($model) {
                return FileSys::formatFileName($model->file_name_after_event, 50);
            },
            */
        ],

    ],
]);