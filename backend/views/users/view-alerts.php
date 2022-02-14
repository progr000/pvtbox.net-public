<?php
/* @var $this yii\web\View */
/* @var $UserModel common\models\Users */
/* @var $UserAlertLogSearchModel backend\models\search\UserAlertsLogSearch */
/* @var $UserAlertLogSearchDataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use kartik\grid\GridView;
use common\helpers\FileSys;
use backend\models\search\UserAlertsLogSearch;

echo GridView::widget([

    'dataProvider' => $UserAlertLogSearchDataProvider,
    'filterModel' => $UserAlertLogSearchModel,

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
            'attribute' => 'alert_created',
            'label' => 'Date',

            'filterType' => GridView::FILTER_DATE_RANGE,
            'filterWidgetOptions' =>([
                'model' => $UserAlertLogSearchModel,
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
                                        $('#useralertslogsearch-alert_created').val('').trigger('change');
                                    }",
                    /*
                    "hide.daterangepicker" => "function(ev, picker) {
                        if(picker.startDate._isValid==false){
                            $('#useralertslogsearch-alert_created').val('').trigger('change');
                            return;
                        }
                        if(picker.endDate._isValid==false){
                            $('#useralertslogsearch-alert_created').val('').trigger('change');
                            return;
                        }
                    }",
                    */

                    "apply.daterangepicker" => "function(ev, picker) {
                                console.log(picker.startDate._isValid);
                                if(picker.startDate._isValid==false){
                                    $('#useralertslogsearch-alert_created').val('').trigger('change');
                                    return false;
                                }
                                if(picker.endDate._isValid==false){
                                    $('#useralertslogsearch-alert_created').val('').trigger('change');
                                    return false;
                                }
                                var val = picker.startDate.format(picker.locale.format) + picker.locale.separator + picker.endDate.format(picker.locale.format);

                                //picker.element[0].children[1].textContent = val;
                                //$(picker.element[0].nextElementSibling).val(val);
                                console.log(val);
                                $('#useralertslogsearch-alert_created').val(val).trigger('change');
                                //return;
                            }",

                ],
            ]),
        ],
        [
            'attribute' => 'alert_url',
            'format' => 'raw',
            'encodeLabel' => false,
            'label' => "Url of page,<br />\nwhere alert<br />is coming",
            'value' => function($data) {
                return '<a href="' . $data->alert_url . '" class="masterTooltip" title="' . strip_tags($data->alert_url) . '">' . FileSys::formatFileName(strip_tags($data->alert_url), 20, 5). '</a>';
            }
        ],
        [
            'attribute' => 'alert_message',
            'format' => 'raw',
            'encodeLabel' => false,
            'label' => "Alert<br />message",
            'filter' => false,
            'value' => function($data) {
                return '<a href="javascript:void(0)" data-record-id="' . $data->record_id . '" class="masterTooltip show-alert-message-text" title="' . strip_tags($data->alert_message) . '">' . FileSys::formatFileName(strip_tags($data->alert_message), 20, 5). '</a>';
            }
        ],
        [
            'attribute' => 'alert_view_type',
            'format' => 'raw',
            'encodeLabel' => false,
            'label' => "Type of<br />alert window",
            'filter' => UserAlertsLogSearch::getViewTypes()
        ],
        [
            'attribute' => 'alert_type',
            'format' => 'raw',
            'encodeLabel' => false,
            'label' => "Type of<br />alert",
            'filter' => UserAlertsLogSearch::getTypes()
        ],
        [
            'attribute' => 'alert_action',
            'format' => 'raw',
            'encodeLabel' => false,
            'label' => "Action which<br />caused alert",
            //'filter' => UserAlertsLogSearch::getTypes()
            'value' => function($data) {
                if ($data->alert_action) {
                    return '<a href="javascript:void(0)" class="masterTooltip" title="' . strip_tags($data->alert_action) . '">' . FileSys::formatFileName(strip_tags($data->alert_action), 20, 5) . '</a>';
                } else {
                    return $data->alert_action;
                }
            }
        ],
        [
            'attribute' => 'alert_ttl',
            'format' => 'raw',
            'encodeLabel' => false,
            'label' => "Time<br />while alert<br />is showing<br />(milliseconds)",
            'filter' => false,
        ],
        [
            'attribute' => 'alert_close_button',
            'format' => 'raw',
            'encodeLabel' => false,
            'label' => "Close<br />button<br />is showed<br />or hidden<br />for alert",
            'filter' => false,
            'value' => function($data) {
                return $data->alert_close_button ? 'Yes' : 'No';
            }
        ],
        [
            'attribute' => 'alert_screen',
            //'format'    => 'image',
            'format' => 'raw',
            'filter' => false,
            'value' => function($data) {
                //return 'data:image/jpeg;charset=utf-8;base64,' . base64_encode($data->alert_screen);
                //return 'data:image/png;base64,' . base64_encode(stream_get_contents($data->alert_screen));
                //return base64_encode($data->alert_screen);
                if ($data->alert_screen) {
                    return Html::img('data:image/jpeg;base64,' . base64_encode(stream_get_contents($data->alert_screen)),[
                        'style' => 'width:170px; height:auto;',
                        'class' => 'masterTooltipImg',
                    ]);
                } else {
                    return '';
                }
            },

        ],
        /*
        [
            'attribute' => '_user_email',
            'label' => 'Owner',
            'encodeLabel' => false,
            'format' => 'raw',
            'value' => function ($data) {
                return '<a href="/users/view?id=' . $data->user_id . '">' . $data->_user_email . '</a>';
            },
        ],
        */
    ],

]);