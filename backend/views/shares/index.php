<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use common\helpers\Functions;
use common\helpers\FileSys;
use common\models\UserFiles;
use backend\assets\UsersAsset;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\QueuedEventsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

UsersAsset::register($this);

$this->title = 'All Shares';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="all-shares-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'file_id',
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
                'value' => function ($data) {
                    return '<a href="/users/view?id=' . $data->user_id . '">' . $data->_user_email . '</a>';
                },
                /*
                'value' => function ($data) {
                    //var_dump($data);exit;
                    return $data->users->_user_email;
                },
                */
            ],

            [
                'attribute' => 'share_created',
                //'filter' => false,
                'format' => 'raw',
                'hAlign'=>'center',
                'width' => '15%',
                'value' => function ($model) {
                    return $model->share_created;
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
                                        $('#sharesandcollaborationssearch-share_created').val('').trigger('change');
                                    }",
                        /*
                        "hide.daterangepicker" => "function(ev, picker) {
                            if(picker.startDate._isValid==false){
                                $('#sharesandcollaborationssearch-share_created').val('').trigger('change');
                                return;
                            }
                            if(picker.endDate._isValid==false){
                                $('#sharesandcollaborationssearch-share_created').val('').trigger('change');
                                return;
                            }
                        }",
                        */

                        "apply.daterangepicker" => "function(ev, picker) {
                                console.log(picker.startDate._isValid);
                                if(picker.startDate._isValid==false){
                                    $('#sharesandcollaborationssearch-share_created').val('').trigger('change');
                                    return false;
                                }
                                if(picker.endDate._isValid==false){
                                    $('#sharesandcollaborationssearch-share_created').val('').trigger('change');
                                    return false;
                                }
                                var val = picker.startDate.format(picker.locale.format) + picker.locale.separator + picker.endDate.format(picker.locale.format);

                                //picker.element[0].children[1].textContent = val;
                                //$(picker.element[0].nextElementSibling).val(val);
                                console.log(val);
                                $('#sharesandcollaborationssearch-share_created').val(val).trigger('change');
                                //return;
                            }",

                    ],
                ]),
            ],
            [
                'attribute' => 'file_name',
                'format' => 'raw',
                'hAlign'=>'center',
                'width' => '30%',
                'value' => function ($model) {
                    /** @var \common\models\UserFiles $model */
                    return '<a href="javascript:void(0)" class="show-full-path" data-file-id="' . $model->file_id . '" onclick="javascript:void(0)" title="' . $model->file_name . '" >' . (FileSys::formatFileName($model->file_name, 60)) . '</a>';
                },
            ],

            [
                'attribute' => 'is_folder',
                'width' => '1%',
                'filter' => [UserFiles::TYPE_FOLDER => 'folder', UserFiles::TYPE_FILE => 'file'],
                'value' => function ($model) {
                    return ($model->is_folder ? 'folder' : 'file');
                },
            ],
            [
                'attribute' => 'file_size',
                'filter' => false,
                'format' => 'raw',
                'hAlign'=>'center',
                'width' => '10%',
                'value' => function ($model) {
                    return Functions::file_size_format($model->file_size);
                },
            ],
            [
                'attribute' => 'share_hash',
                'label' => "Share link",
                //'filter' => false,
                'format' => 'raw',
                'hAlign'=>'center',
                'width' => '10%',
                'value' => function ($model) {
                    /** @var \common\models\UserFiles $model */
                    if (!$model->is_deleted) {
                        if ($model->is_shared) {
                            if ($model->is_folder) {
                                $link = UserFiles::getShareLink($model->share_group_hash, $model->is_folder);
                            } else {
                                $link = UserFiles::getShareLink($model->share_hash, $model->is_folder);
                            }
                        } else {
                            if ($model->share_group_hash) {
                                if ($model->is_folder) {
                                    $link = UserFiles::getShareLink($model->share_group_hash, $model->is_folder, $model->file_id);
                                } else {
                                    $link = UserFiles::getShareLink($model->share_hash, $model->is_folder, $model->file_id);
                                }
                            } else {

                            }
                        }
                    } else {
                        $link = 'locked';
                    }
                    if (isset($link)) {
                        if ($link == 'locked') {
                            return 'not available<br />File deleted';
                        } else {
                            //if ($model->share_is_locked) {
                            $lock_link = '<a href="javascript:void(0)" class="lock-unlock-share-link" dta-file-id="' . $model->file_id . '" data-share-is-locked="' . ( (integer) !((bool) $model->share_is_locked)) . '" data-name-lock="Lock" data-name-unlock="UnLock">' . ($model->share_is_locked ? 'UnLock' : 'Lock') . '</a>';
                            //}
                            return '<a href="' . $link . '" target="_blank">Link</a> <br /> [' . $lock_link . ']';
                        }
                    } else {
                        return 'not shared';
                    }
                    //return ($model->share_hash) ? $model->share_hash : 'not shared';
                },
            ],

        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>

<?= $this->render('/layouts/modal') ?>

