<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use common\models\Mailq;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\MailqSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Sent mail via mailer statistic';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mailq-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            //'mail_id',
            [
                'attribute' => 'mailer_letter_id',
                //'header' => $searchModel->getAttributeLabel('mailer_letter_id'),
                'header' => "Unique letter ID<br />on mailer system",
                'format' => 'raw',
                'width' => '10%',
            ],
            [
                'attribute' => 'mail_created',
                'width' => '20%',

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
                                        $('#mailqsearch-mail_created').val('').trigger('change');
                                    }",
                        /*
                        "hide.daterangepicker" => "function(ev, picker) {
                            if(picker.startDate._isValid==false){
                                $('#mailqsearch-mail_created').val('').trigger('change');
                                return;
                            }
                            if(picker.endDate._isValid==false){
                                $('#mailqsearch-mail_created').val('').trigger('change');
                                return;
                            }
                        }",
                        */

                        "apply.daterangepicker" => "function(ev, picker) {
                                console.log(picker.startDate._isValid);
                                if(picker.startDate._isValid==false){
                                    $('#mailqsearch-mail_created').val('').trigger('change');
                                    return false;
                                }
                                if(picker.endDate._isValid==false){
                                    $('#mailqsearch-mail_created').val('').trigger('change');
                                    return false;
                                }
                                var val = picker.startDate.format(picker.locale.format) + picker.locale.separator + picker.endDate.format(picker.locale.format);

                                //picker.element[0].children[1].textContent = val;
                                //$(picker.element[0].nextElementSibling).val(val);
                                console.log(val);
                                $('#mailqsearch-mail_created').val(val).trigger('change');
                                //return;
                            }",

                    ],
                ]),
            ],
            [
                'attribute' => 'remote_ip',
                'width' => '10%',

            ],
            [
                'attribute' => 'mail_from',
                'width' => '15%',
            ],
            [
                'attribute' => 'mail_to',
                'width' => '15%',
            ],
            /*
            [
                'attribute' => 'mail_reply_to',
                'width' => '15%',
            ],
            */
            [
                'attribute' => 'template_key',
                'width' => '20%',
            ],
            [
                'attribute' => 'mailer_letter_status',
                'filter'=> Mailq::mailqStatuses(),
                //'header' => "Letter status<br />on mailer system",
                'width' => '5%',
            ],
            //'mail_subject',
            //'mail_body',
            //'mailer_answer',
            //'user_id',
            //'node_id',

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
