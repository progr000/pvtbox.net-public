<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use common\helpers\FileSys;
use backend\models\search\UserAlertsLogSearch;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\UserAlertsLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'User Alerts Logs';
//$this->params['breadcrumbs'][] = $this->title;

$this->registerJs(<<<JS
$(document).on('pjax:success', function() {
    initToolTip();
    initToolTipImg();
});
JS
, \yii\web\View::POS_END);
?>
<div class="user-alerts-log-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            //'record_id',
            [
                'attribute' => 'alert_created',
                'label' => 'Date',

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
                'filter' => UserAlertsLogSearch::getActions(),
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
                        $content_from_stream =  stream_get_contents($data->alert_screen);
                        if (strrpos($content_from_stream, 'Error:') === false) {
                            return Html::img('data:image/jpeg;base64,' . base64_encode($content_from_stream), [
                                'style' => 'width:170px; height:auto;',
                                'class' => 'masterTooltipImg',
                            ]);
                        } else {
                            return ($content_from_stream);
                        }
                    } else {
                        return '';
                    }
                },

            ],
            [
                'attribute' => '_user_email',
                'label' => 'Owner',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($data) {
                    return '<a href="/users/view?id=' . $data->user_id . '">' . $data->_user_email . '</a>';
                },
            ],

            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>

<?= $this->render('/layouts/modal') ?>

