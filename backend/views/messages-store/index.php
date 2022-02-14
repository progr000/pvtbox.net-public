<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use backend\models\search\MessagesStoreSearch;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\MessagesStoreSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Messages Store';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="messages-store-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'ms_created',
                'width' => '20%',

                'filterType' => GridView::FILTER_DATE_RANGE,
                'filterWidgetOptions' =>([
                    'model' => $searchModel,
                    //'attribute' => 'created_at_range',
                    'presetDropdown' => false,
                    'defaultPresetValueOptions' => ['style'=>'display:none'],
                    'convertFormat' => true,
                    'initRangeExpr' => false,
                    'options' => [
                        'autocomplete' => "off",
                    ],
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
                                        $('#messagesstoresearch-ms_created').val('').trigger('change');
                                    }",
                        /*
                        "hide.daterangepicker" => "function(ev, picker) {
                            if(picker.startDate._isValid==false){
                                $('#messagesstoresearch-ms_created').val('').trigger('change');
                                return;
                            }
                            if(picker.endDate._isValid==false){
                                $('#messagesstoresearch-ms_created').val('').trigger('change');
                                return;
                            }
                        }",
                        */

                        "apply.daterangepicker" => "function(ev, picker) {
                                console.log(picker.startDate._isValid);
                                if(picker.startDate._isValid==false){
                                    $('#messagesstoresearch-ms_created').val('').trigger('change');
                                    return false;
                                }
                                if(picker.endDate._isValid==false){
                                    $('#messagesstoresearch-ms_created').val('').trigger('change');
                                    return false;
                                }
                                var val = picker.startDate.format(picker.locale.format) + picker.locale.separator + picker.endDate.format(picker.locale.format);

                                //picker.element[0].children[1].textContent = val;
                                //$(picker.element[0].nextElementSibling).val(val);
                                console.log(val);
                                $('#messagesstoresearch-ms_created').val(val).trigger('change');
                                //return;
                            }",

                    ],
                ]),
            ],

            [
                'attribute' => 'ms_type',
                'filter'=> MessagesStoreSearch::getTypes(),
                //'header' => "Letter status<br />on mailer system",
                'width' => '10%',
            ],


            [
                'attribute' => 'ms_data',
                'format' => 'raw',
                'value' => function($model) {
                    return nl2br($model->ms_data);
                }
            ],

            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>


</div>
