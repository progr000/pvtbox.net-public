<?php
/* @var $this yii\web\View */
/* @var $UserModel common\models\Users */
/* @var $UserFilesSearchModel backend\models\search\UserFilesSearch */
/* @var $UserFilesSearchDataProvider yii\data\ActiveDataProvider */
/* @var $totalFsInfo array */

use yii\helpers\Html;
use kartik\grid\GridView;
use common\helpers\Functions;
use common\helpers\FileSys;
use common\models\UserFiles;

if (!empty($_GET['UserFilesSearch']['show_deleted'])) {
    $show_deleted_checked = ' checked="checked"';
} else {
    $show_deleted_checked = '';
}

echo GridView::widget([
    'dataProvider' => $UserFilesSearchDataProvider,
    'filterModel' => $UserFilesSearchModel,

    'pjax'=>false,
    'pjaxSettings' => [],
    'panel' => [
        'before' => false,
        //'after' => Functions::getLegend(Users::statusParams()),
    ],
    'summary' => '<label style="margin-right: 100px;"><input type="checkbox" class="users-show-hide-deleted" ' . $show_deleted_checked . '> Show deleted</label>
            Total folders: <b>' . $totalFsInfo['folders_total'] . '</b>,
            Deleted folders: <b>' . $totalFsInfo['folders_deleted'] . '</b>
            Total files: <b>' . $totalFsInfo['files_total'] . '</b>,
            Deleted files: <b>' . $totalFsInfo['files_deleted']. '</b>,
            Showing <b>{begin, number}</b>-<b>{end, number}</b> of <b>{totalCount, number}</b> items.',
    'pager' => [
        'firstPageLabel' => 'First',
        'lastPageLabel'  => 'Last'
    ],
    //'layout'=>"{test}\n{summary}\n{items}\n{pager}",
    'rowOptions' => function ($model, $key, $index, $grid) {
        //var_dump($model->file_name);
        return ($model->is_deleted) ? ['class' => 'file-is-deleted'] : [];
    },
    'columns' => [
        [
            'attribute' => 'tab',
            //'filter' => ['node-info'],
            'hidden' => true,
            //'value' => function ($model) { return 'node-info'; },
        ],

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
                $short = Functions::concatString($model->file_uuid, 6);
                return '<a href="javascript:void(0)" class="masterTooltip" onclick="alert(\'' . $model->file_uuid . '\'); return false;" title="' . $model->file_uuid . '" >' . $short . '</a>';
            },
        ],

        [
            'attribute' => 'file_name',
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '20%',
            'value' => function ($model) {
                /** @var \common\models\UserFiles $model */
                return '<a href="javascript:void(0)" class="masterTooltip show-full-path" data-file-id="' . $model->file_id . '" onclick="javascript:void(0)" title="' . $model->file_name . '" >' . (FileSys::formatFileName($model->file_name, 50)) . '</a>';
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

        /*
        [
            'attribute' => 'is_deleted',
            'width' => '1%',
            'filter' => [UserFiles::FILE_DELETED => 'Deleted', UserFiles::FILE_UNDELETED => 'UnDeleted'],
            'value' => function ($model) {
                return ($model->is_deleted ? 'Deleted' : '');
            },
        ],
        */

        [
            'attribute' => 'file_size',
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
                        return 'not available';
                    } else {
                        //if ($model->share_is_locked) {
                        $lock_link = '<a href="javascript:void(0)" class="lock-unlock-share-link" dta-file-id="' . $model->file_id . '" data-share-is-locked="' . ( (integer) !((bool) $model->share_is_locked)) . '" data-name-lock="Lock" data-name-unlock="UnLock">' . ($model->share_is_locked ? 'UnLock' : 'Lock') . '</a>';
                        //}
                        return '<a href="' . $link . '" target="_blank" class="fa fa-share" style="font-size: 24px; color: #337ab7;">shared</a> <br /> [' . $lock_link . ']';
                    }
                } else {
                    //return 'not shared';
                    return null;
                }
                //return ($model->share_hash) ? $model->share_hash : 'not shared';
            },
        ],

        [
            'attribute' => 'collaboration_id',
            'label' => "Collabo<br />ration",
            'encodeLabel' => false,
            //'filter' => false,
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '10%',
            'value' => function ($model) use ($UserModel) {
                if ($model->collaboration_id) {
                    return "<a class=\"fa fa-users masterTooltip\" title='collaboration_id={$model->collaboration_id}' style=\"font-size:24px;color: #337ab7;\" href=\"/users/view?id={$UserModel->user_id}&UserCollaborationsSearch[tab]=&UserCollaborationsSearch[collaboration_id]={$model->collaboration_id}\"></a>";
                } else {
                    return null;
                }
            },
        ],

        [
            'attribute' => 'file_created',
            'label' => 'Created',
            //'filter' => false,
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '10%',
            'value' => function ($model) {
                return $model->file_created;
            },

            'filterType' => GridView::FILTER_DATE_RANGE,
            'filterWidgetOptions' =>([
                'model' => $UserFilesSearchModel,
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
                                        $('#userfilessearch-file_created').val('').trigger('change');
                                    }",
                    /*
                    "hide.daterangepicker" => "function(ev, picker) {
                        if(picker.startDate._isValid==false){
                            $('#userfilessearch-file_created').val('').trigger('change');
                            return;
                        }
                        if(picker.endDate._isValid==false){
                            $('#userfilessearch-file_created').val('').trigger('change');
                            return;
                        }
                    }",
                    */

                    "apply.daterangepicker" => "function(ev, picker) {
                                console.log(picker.startDate._isValid);
                                if(picker.startDate._isValid==false){
                                    $('#userfilessearch-file_created').val('').trigger('change');
                                    return false;
                                }
                                if(picker.endDate._isValid==false){
                                    $('#userfilessearch-file_created').val('').trigger('change');
                                    return false;
                                }
                                var val = picker.startDate.format(picker.locale.format) + picker.locale.separator + picker.endDate.format(picker.locale.format);

                                //picker.element[0].children[1].textContent = val;
                                //$(picker.element[0].nextElementSibling).val(val);
                                console.log(val);
                                $('#userfilessearch-file_created').val(val).trigger('change');
                                //return;
                            }",

                ],
            ]),
        ],

        [
            'attribute' => 'file_lastatime',
            //'filter' => false,
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '10%',
            'value' => function ($model) {
                return date(SQL_DATE_FORMAT, $model->file_lastatime);
            },

            'filterType' => GridView::FILTER_DATE_RANGE,
            'filterWidgetOptions' =>([
                'model' => $UserFilesSearchModel,
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
                                        $('#userfilessearch-file_lastatime').val('').trigger('change');
                                    }",
                    /*
                    "hide.daterangepicker" => "function(ev, picker) {
                        if(picker.startDate._isValid==false){
                            $('#userfilessearch-file_lastatime').val('').trigger('change');
                            return;
                        }
                        if(picker.endDate._isValid==false){
                            $('#userfilessearch-file_lastatime').val('').trigger('change');
                            return;
                        }
                    }",
                    */

                    "apply.daterangepicker" => "function(ev, picker) {
                                console.log(picker.startDate._isValid);
                                if(picker.startDate._isValid==false){
                                    $('#userfilessearch-file_lastatime').val('').trigger('change');
                                    return false;
                                }
                                if(picker.endDate._isValid==false){
                                    $('#userfilessearch-file_lastatime').val('').trigger('change');
                                    return false;
                                }
                                var val = picker.startDate.format(picker.locale.format) + picker.locale.separator + picker.endDate.format(picker.locale.format);

                                //picker.element[0].children[1].textContent = val;
                                //$(picker.element[0].nextElementSibling).val(val);
                                console.log(val);
                                $('#userfilessearch-file_lastatime').val(val).trigger('change');
                                //return;
                            }",

                ],
            ]),

        ],

        [
            'class'=>'kartik\grid\ActionColumn',
            'width' => '110px',
            'vAlign' => 'top',
            //'template' => '{change-status} {profile} {view} {update} {delete}',
            //'template' => '{change-status} {view} {update} {delete}',
            //'template' => '{nodes}<br />{events}',
            'template' => '{events}',
            'buttons' => [

                'nodes' => function ($url, $model) {
                    //var_dump($model); exit;
                    return Html::a(
                        '<span class="glyphicon glyphicon-nodes" data-file-id="' . $model->file_id . '">Nodes</span>',
                        '#',
                        [
                            'title' => 'Nodes',
                            'data-pjax' => '0',
                            'target' => '_blank',
                        ]
                    );
                },
                'events' => function ($url, $model) {
                    //var_dump($model); exit;
                    $buttons = Html::a(
                        '<span class="glyphicon glyphicon-events glyphicon-list-alt" style="font-size:24px; color: #337ab7" data-file-id="' . $model->file_id . '"></span>',
                        '#',
                        [
                            'title' => 'Events',
                            'data-pjax' => '0',
                            'target' => '_blank',
                            'class' => 'masterTooltip',
                        ]
                    );
                    return $buttons;
                },
            ],
        ],

    ],
]);
