<?php
/* @var $this yii\web\View */
/* @var $UserModel common\models\Users */
/* @var $UserFileEventsSearchModel backend\models\search\UserFileEventsSearch */
/* @var $UserFileEventsSearchDataProvider yii\data\ActiveDataProvider */

use kartik\grid\GridView;
use common\helpers\FileSys;
use common\helpers\Functions;
use common\models\UserFileEvents;

echo GridView::widget([
    'dataProvider' => $UserFileEventsSearchDataProvider,
    'filterModel' => $UserFileEventsSearchModel,

    'pjax'=>false,
    'pjaxSettings' => [],
    'panel' => [
        'before' => false,
        //'after' => Functions::getLegend(Users::statusParams()),
    ],
    //'summary' => 'Показаны записи с <b>{begin, number}</b> по <b>{end, number}</b> из <b>{totalCount, number}</b>.',
    'pager' => [
        'firstPageLabel' => 'First',
        'lastPageLabel'  => 'Last'
    ],
    'columns' => [
        [
            'attribute' => 'tab',
            //'filter' => ['node-info'],
            'hidden' => true,
            //'value' => function ($model) { return 'node-info'; },
        ],

        [
            'attribute' => 'event_id',
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '1%',
        ],

        [
            'attribute' => 'file_id',
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '1%',
            'value' => function ($model) use ($UserModel) {
                /* @var $model backend\models\search\EventsForFileSearch */
                return '<a href="/users/view?id=' . $UserModel->user_id . '&UserFilesSearch[tab]=&UserFilesSearch[file_id]=' . $model->file_id . '">' .$model->file_id. '</a>';
            },
        ],

        [
            'attribute' => 'event_type',
            'format' => 'raw',
            'hAlign'=>'center',
            'filter' => UserFileEvents::eventTypes(),
            'width' => '1%',
            'value' => function ($model) {
                /* @var $model backend\models\search\EventsForFileSearch */
                return $model->getType($model->event_type);
            },
        ],

        [
            'attribute' => 'event_timestamp',
            'label' => 'Date',
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '10%',
            'value' => function ($model) {
                return date(SQL_DATE_FORMAT, $model->event_timestamp);
            },

            'filterType' => GridView::FILTER_DATE_RANGE,
            'filterWidgetOptions' =>([
                'model' => $UserFileEventsSearchModel,
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
                                        $('#userfileeventssearch-event_timestamp').val('').trigger('change');
                                    }",
                    /*
                    "hide.daterangepicker" => "function(ev, picker) {
                        if(picker.startDate._isValid==false){
                            $('#userfileeventssearch-event_timestamp').val('').trigger('change');
                            return;
                        }
                        if(picker.endDate._isValid==false){
                            $('#userfileeventssearch-event_timestamp').val('').trigger('change');
                            return;
                        }
                    }",
                    */

                    "apply.daterangepicker" => "function(ev, picker) {
                                console.log(picker.startDate._isValid);
                                if(picker.startDate._isValid==false){
                                    $('#userfileeventssearch-event_timestamp').val('').trigger('change');
                                    return false;
                                }
                                if(picker.endDate._isValid==false){
                                    $('#userfileeventssearch-event_timestamp').val('').trigger('change');
                                    return false;
                                }
                                var val = picker.startDate.format(picker.locale.format) + picker.locale.separator + picker.endDate.format(picker.locale.format);

                                //picker.element[0].children[1].textContent = val;
                                //$(picker.element[0].nextElementSibling).val(val);
                                console.log(val);
                                $('#userfileeventssearch-event_timestamp').val(val).trigger('change');
                                //return;
                            }",

                ],
            ]),

        ],

        [
            'attribute' => 'file_name_before_event',
            'label' => "Name before",
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '18%',
            'value' => function ($model) {
                return '<a href="javascript:void(0)" class="show-only-path masterTooltip" data-file-id="' . $model->file_id . '" data-file-name="' . $model->file_name_before_event . '" onclick="javascript:void(0)" title="' . $model->file_name_before_event . '" >' . (FileSys::formatFileName($model->file_name_before_event, 30)) . '</a>';
            },
        ],
        [
            'attribute' => 'file_name_after_event',
            'label' => "Name after",
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '18%',
            'value' => function ($model) {
                return '<a href="javascript:void(0)" class="show-only-path masterTooltip" data-file-id="' . $model->file_id . '" data-file-name="' . $model->file_name_after_event . '" onclick="javascript:void(0)" title="' . $model->file_name_after_event . '" >' . (FileSys::formatFileName($model->file_name_after_event, 30)) . '</a>';
            },
        ],

        [
            'attribute' => '_node_name',
            'label' => 'NodeName',
            'format' => 'raw',
            'value' => function ($model) {
                if (is_object($model->node)) {
                    $full_node_name = $model->node->node_name;
                    $node_name = $full_node_name;
                    if (mb_strlen($node_name) > 15) {
                        $node_name = Functions::concatString($node_name, 15);
                    }
                } else {
                    $node_name = 'Unknown';
                    $full_node_name = $node_name;
                }
                return '<a class="masterTooltip" href="/users/view?UserNodeSearch[tab]=&UserNodeSearch[node_id]=' . $model->node_id . '&id=' . $model->user_id . '" title="' . $full_node_name . '">' . Functions::concatString($node_name, 15) . '</a>';
                //return $model->node->node_name;
            },
            'width' => '8%',
        ],

        [
            'attribute' => 'parent_before_event',
            'label' => "Parent before",
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '8%',
            'value' => function ($model) {
                return '<a href="/users/view?UserFilesSearch[tab]=&UserFilesSearch[file_id]=' . $model->parent_before_event . '&id=' . $model->user_id . '">' . $model->parent_before_event . '</a>';
            }
        ],

        [
            'attribute' => 'parent_after_event',
            'label' => "Parent after",
            'format' => 'raw',
            'hAlign'=>'center',
            'width' => '8%',
            'value' => function ($model) {
                return '<a href="/users/view?UserFilesSearch[tab]=&UserFilesSearch[file_id]=' . $model->parent_after_event . '&id=' . $model->user_id . '">' . $model->parent_before_event . '</a>';
            }
        ],

    ],
]);