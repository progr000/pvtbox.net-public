<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use common\helpers\FileSys;
use backend\assets\UsersAsset;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\QueuedEventsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

UsersAsset::register($this);

$this->title = 'All Collaborations';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="all-collaborations-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'collaboration_id',
                'format' => 'raw',
                'hAlign'=>'center',
                'width' => '1%',
            ],
            [
                'attribute' => 'file_uuid',
                'format' => 'raw',
                'hAlign'=>'center',
                'width' => '20px',
                'value' => function ($model) {
                    /** @var \common\models\UserFiles $model */
                    $short = mb_substr($model->file_uuid, 0, 6) . '...';
                    return '<a href="javascript:void(0)" class="" onclick="alert(\'' . $model->file_uuid . '\'); return false;" title="' . $model->file_uuid . '" >' . $short . '</a>';
                },
            ],
            [
                'attribute' => '_user_email',
                'label' => 'Owner',
                'format' => 'raw',
                'width' => '15%',
                'value' => function ($data) {
                    return '<a href="/users/view?id=' . $data->user_id . '">' . $data->user->user_email . '</a>';
                },
                /*
                'value' => function ($data) {
                    //var_dump($data);exit;
                    return $data->users->_user_email;
                },
                */
            ],

            [
                'attribute' => 'collaboration_created',
                //'filter' => false,
                'format' => 'raw',
                'hAlign'=>'center',
                'width' => '15%',
                'value' => function ($model) {
                    return $model->collaboration_created;
                },

                'filterType' => GridView::FILTER_DATE_RANGE,
                'filterWidgetOptions' =>([
                    'model' => $searchModel,
                    //'attribute' => 'created_at_range',
                    'presetDropdown' => false,
                    'defaultPresetValueOptions' => ['style'=>'display:none'],
                    'convertFormat' => true,
                    'initRangeExpr' => false,
                    'pluginOptions' => [
                        'alwaysShowCalendars' => true,
                        'locale' => [
                            'format' => 'Y-m-d',
                            'cancelLabel' => 'Clear',
                        ],

                        'ranges' => [
                            //"Clear" => ["", ""],
                            "Today" => [date('Y-m-d'), date('Y-m-d')],
                            "Yesterday" => [date('Y-m-d', time()-86400), date('Y-m-d', time()-86400)],
                            "Last 7 Days" => [date('Y-m-d', time()-7*86400), date('Y-m-d', time())],
                            "Last 30 Days" => [date('Y-m-d', time()-30*86400), date('Y-m-d', time())],
                            "This Month" => [date('Y-m-01', time()), date('Y-m-d', time())],
                            //"Prev Month" => [date('Y-m-01', time()-27*86400), date('Y-m-d', time())],
                        ],

                    ],
                    'pluginEvents' => [
                        "cancel.daterangepicker" => "function(ev, picker) {
                                        //alert(1);
                                        //picker.element[0].children[1].textContent = '';
                                        //$(picker.element[0].nextElementSibling).val('').trigger('change');
                                        $('#sharesandcollaborationssearch-collaboration_created').val('').trigger('change');
                                    }",
                        /*
                        "hide.daterangepicker" => "function(ev, picker) {
                            if(picker.startDate._isValid==false){
                                $('#sharesandcollaborationssearch-collaboration_created').val('').trigger('change');
                                return;
                            }
                            if(picker.endDate._isValid==false){
                                $('#sharesandcollaborationssearch-collaboration_created').val('').trigger('change');
                                return;
                            }
                        }",
                        */

                        "apply.daterangepicker" => "function(ev, picker) {
                                console.log(picker.startDate._isValid);
                                if(picker.startDate._isValid==false){
                                    $('#sharesandcollaborationssearch-collaboration_created').val('').trigger('change');
                                    return false;
                                }
                                if(picker.endDate._isValid==false){
                                    $('#sharesandcollaborationssearch-collaboration_created').val('').trigger('change');
                                    return false;
                                }
                                var val = picker.startDate.format(picker.locale.format) + picker.locale.separator + picker.endDate.format(picker.locale.format);

                                //picker.element[0].children[1].textContent = val;
                                //$(picker.element[0].nextElementSibling).val(val);
                                console.log(val);
                                $('#sharesandcollaborationssearch-collaboration_created').val(val).trigger('change');
                                //return;
                            }",

                    ],
                ]),
            ],

            [
                'attribute' => '_file_name',
                'format' => 'raw',
                'hAlign'=>'center',
                //'width' => '30%',
                'value' => function ($model) {
                    /** @var \common\models\UserCollaborations $model */
                    return '<a href="javascript:void(0)" class="show-full-path" data-file-id="' . $model->file->file_id . '" onclick="javascript:void(0)" title="' . $model->file->file_name . '" >' . (FileSys::formatFileName($model->file->file_name, 60)) . '</a>';
                },
            ],

            [
                //'class' => 'yii\grid\ActionColumn',
                'class'=>'kartik\grid\ActionColumn',
                'width' => '1%',
                'vAlign' => 'top',
                'template' => '{view-colleagues}',
                'buttons' => [

                    'view-colleagues' => function ($url, $model) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-user"></span>',
                            '#',
                            [
                                'title' => 'View colleagues',
                                'data-pjax' => '0',
                                'target' => '_blank',
                                'data-collaboration-id' => $model->collaboration_id,
                                'class' => 'show-colleagues',
                            ]
                        );
                    },
                ],
            ],

        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>

<?= $this->render('/layouts/modal') ?>

