<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\UserActionsLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'User Actions Logs';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-actions-log-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Pjax::begin(); ?>


    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'record_id',
            [
                'attribute' => 'action_created',
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
            'action_url',
            'action_type',
            //'action_raw_data',
            //'user_id',
            //'site_url:url',
            'site_absolute_url:url',
            [
                'attribute' => '_user_email',
                'label' => 'Owner',
                'encodeLabel' => false,
                'format' => 'raw',
                'value' => function ($data) {
                    return '<a href="/users/view?id=' . $data->user_id . '">' . $data->_user_email . '</a>';
                },
            ],
            [
                'class'=>'kartik\grid\ActionColumn',
                'width' => '110px',
                'vAlign' => 'top',
                'template' => '{view}',
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
