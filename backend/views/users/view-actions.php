<?php

use yii\helpers\Html;
use kartik\grid\GridView;

echo GridView::widget([

    'dataProvider' => $UserActionsLogSearchDataProvider,
    'filterModel' => $UserActionsLogSearchModel,

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
            //'filter' => ['node-info'],
            'hidden' => true,
            //'value' => function ($model) { return 'node-info'; },
        ],

        [
            'attribute' => 'action_created',
            'label' => 'Date',

            'filterType' => GridView::FILTER_DATE_RANGE,
            'filterWidgetOptions' =>([
                'model' => $UserActionsLogSearchModel,
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
                                        $('#useractionslogsearch-action_created').val('').trigger('change');
                                    }",
                    /*
                    "hide.daterangepicker" => "function(ev, picker) {
                        if(picker.startDate._isValid==false){
                            $('#useractionslogsearch-action_created').val('').trigger('change');
                            return;
                        }
                        if(picker.endDate._isValid==false){
                            $('#useractionslogsearch-action_created').val('').trigger('change');
                            return;
                        }
                    }",
                    */

                    "apply.daterangepicker" => "function(ev, picker) {
                                console.log(picker.startDate._isValid);
                                if(picker.startDate._isValid==false){
                                    $('#useractionslogsearch-action_created').val('').trigger('change');
                                    return false;
                                }
                                if(picker.endDate._isValid==false){
                                    $('#useractionslogsearch-action_created').val('').trigger('change');
                                    return false;
                                }
                                var val = picker.startDate.format(picker.locale.format) + picker.locale.separator + picker.endDate.format(picker.locale.format);

                                //picker.element[0].children[1].textContent = val;
                                //$(picker.element[0].nextElementSibling).val(val);
                                console.log(val);
                                $('#useractionslogsearch-action_created').val(val).trigger('change');
                                //return;
                            }",

                ],
            ]),
        ],
        [
            'attribute' => 'action_url',
            'label' => 'Action url',
        ],
        'action_type',
        //'action_raw_data',
        //'user_id',
        //'site_url:url',
        'site_absolute_url:url',


        [
            'class' => 'kartik\grid\ActionColumn',
            'width' => '80px;',
            'vAlign' => 'top',
            'template' => '{view}', //'{view} {update}',

            'buttons' => [
                'view' => function ($url, $model) {
                    //var_dump($model); exit;
                    $buttons = Html::a(
                        '<span class="glyphicon glyphicon-eye-open"></span>',
                        '/actions-log/view?id=' . $model->record_id,
                        [
                            'title' => 'View',
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