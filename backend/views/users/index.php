<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use common\helpers\Functions;
use common\models\Users;
use common\models\Licenses;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\UsersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users Management';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="users-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>


    <p>
        <?= Html::a('Create New User', ['create'], ['class' => 'btn btn-success']) ?>
    </p>


    <?php
    Pjax::begin();
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'pjax'=>true,
        'pjaxSettings' => [],
        'panel' => [
            'before' => false,
            'after' => Functions::getLegend(Users::statusParams()),
        ],
        //'summary' => 'Показаны записи с <b>{begin, number}</b> по <b>{end, number}</b> из <b>{totalCount, number}</b>.',
        'summary' => "Showing {begin, number}-{end, number} of {totalCount, number} users.",

        'columns' => [
            [
                'attribute' => 'user_id',
                'width' => '40px',
            ],
            /*
            [
                'attribute' => 'user_created',
                //'format' => ['date', 'php:d/m/Y H:i:s'],
            ],
            */
            [
                'attribute' => 'user_created',
                'hAlign'=>'center',
                'width' => '100px;',
                //'format' => ['date', 'php:d/m/Y H:i:s'],
                'encodeLabel' => false,
                'label' => 'Reg<br />date',
                /*
                'value' => function ($model) use ($searchModel) {
                    return date(SQL_DATE_FORMAT, $model->user_created);
                },
                */

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
                                        $('#userssearch-user_created').val('').trigger('change');
                                    }",
                        /*
                        "hide.daterangepicker" => "function(ev, picker) {
                            if(picker.startDate._isValid==false){
                                $('#userssearch-user_created').val('').trigger('change');
                                return;
                            }
                            if(picker.endDate._isValid==false){
                                $('#userssearch-user_created').val('').trigger('change');
                                return;
                            }
                        }",
                        */

                        "apply.daterangepicker" => "function(ev, picker) {
                                console.log(picker.startDate._isValid);
                                if(picker.startDate._isValid==false){
                                    $('#userssearch-user_created').val('').trigger('change');
                                    return false;
                                }
                                if(picker.endDate._isValid==false){
                                    $('#userssearch-user_created').val('').trigger('change');
                                    return false;
                                }
                                var val = picker.startDate.format(picker.locale.format) + picker.locale.separator + picker.endDate.format(picker.locale.format);

                                //picker.element[0].children[1].textContent = val;
                                //$(picker.element[0].nextElementSibling).val(val);
                                console.log(val);
                                $('#userssearch-user_created').val(val).trigger('change');
                                //return;
                            }",

                    ],
                ]),
            ],
            [
                'attribute' => 'user_updated',
                'hAlign'=>'center',
                'width' => '100px;',
                //'format' => ['date', 'php:d/m/Y H:i:s'],
                'encodeLabel' => false,
                'label' => 'Last<br />activity',

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
                                        $('#userssearch-user_updated').val('').trigger('change');
                                    }",
                        /*
                        "hide.daterangepicker" => "function(ev, picker) {
                            if(picker.startDate._isValid==false){
                                $('#userssearch-user_updated').val('').trigger('change');
                                return;
                            }
                            if(picker.endDate._isValid==false){
                                $('#userssearch-user_updated').val('').trigger('change');
                                return;
                            }
                        }",
                        */

                        "apply.daterangepicker" => "function(ev, picker) {
                                console.log(picker.startDate._isValid);
                                if(picker.startDate._isValid==false){
                                    $('#userssearch-user_updated').val('').trigger('change');
                                    return false;
                                }
                                if(picker.endDate._isValid==false){
                                    $('#userssearch-user_updated').val('').trigger('change');
                                    return false;
                                }
                                var val = picker.startDate.format(picker.locale.format) + picker.locale.separator + picker.endDate.format(picker.locale.format);

                                //picker.element[0].children[1].textContent = val;
                                //$(picker.element[0].nextElementSibling).val(val);
                                console.log(val);
                                $('#userssearch-user_updated').val(val).trigger('change');
                                //return;
                            }",

                    ],
                ]),
            ],
            //'user_name',
            [
                'attribute' => 'user_email',
                'format' => 'raw',
                'value' => function($model) {
                    $str = '<a class="masterTooltip" href="javascript:void(0)" title="' . $model->user_email . '">' . Functions::concatString($model->user_email, 30) . '</a>';
                    return $str;
                },
            ],

            [
                'attribute' => 'user_promo_code',
                'format' => 'raw',
                'label' => 'Promo<br /><a href="#" class="show-all-with-promo">Show not null</a>',
                'encodeLabel' => false,
                'width' => '80px',
                'value' => function($model) {
                    return $model->user_promo_code;
                },
            ],

            [
                'attribute' => 'user_ref_id',
                'visible' => !Yii::$app->params['self_hosted'],
                'format' => 'raw',
                'width' => '70px',
                'encodeLabel' => false,
                'label' => 'SellerId<br /><a href="' . \yii\helpers\Url::to(['/admins']) . '">(admin_id)</a>',
                'value' => function($model) {
                    if ($model->user_ref_id)
                        return '<a href="/admins?AdminsSearch[admin_id]=' . $model->user_ref_id . '">' . $model->user_ref_id . '</a>';
                    else {
                        return $model->user_ref_id;
                    }
                }
            ],

            [
                'attribute' => 'user_last_ip',
                'hAlign'=>'right',
                'width' => '110px',
            ],
            [
                'attribute' => 'license_type',
                'format' => 'raw',
                'label' => 'License',
                'hAlign'=>'center',
                'filter' => Yii::$app->params['self_hosted'] ? false : Licenses::licenseTypes(true),
                'value' => function ($model) {
                    return Licenses::getType($model->license_type, true);
                },
                'width' => '80px',
            ],
            [
                'attribute' => 'user_status',
                'format' => 'raw',
                'hAlign'=>'center',
                'filter' => Users::statusLabels(),
                'value' => function ($model) {
                    $color = "#000000";
                    $color = Users::statusColor($model->user_status);
                    return '<span class="badge" style="background-color: '.$color.'">&nbsp;</span>';
                },
                'width' => '60px',
            ],
            //'user_updated',
            [
                //'class' => 'yii\grid\ActionColumn',
                'class'=>'kartik\grid\ActionColumn',
                'width' => '60px',
                'vAlign' => 'top',
                //'template' => '{change-status} {profile} {view} {update} {delete}',
                //'template' => '{change-status} {view} {update} {delete}',
                //'template' => '{change-status} {profile} {view} {update}',
                'template' => '{change-status} {view} {update}',
                'buttons' => [

                    'profile' => function ($url) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-user"></span>',
                            $url,
                            [
                                'title' => 'Login to User Account',
                                'data-pjax' => '0',
                                'target' => '_blank',
                                //'class' => 'masterTooltip',
                            ]
                        );
                    },

                    'change-status' => function ($url, $model) {
                        return Html::a(
                            '<span class="glyphicon '.($model->user_status == 0 ? "glyphicon-ok-circle" : "glyphicon-ban-circle").'"></span>',
                            //$url.'&qs='.base64_encode(Yii::$app->request->queryString),
                            $url.Functions::prepareQS(['id', '_pjax']),
                            //$url,
                            [
                                'title' => ($model->user_status == 0 ? "UnBlock" : "Block"),
                                'data-pjax' => "w0",
                                'data-method' => "post",
                                //'class' => 'masterTooltip',
                            ]
                        );
                    }
                ],
            ],

        ],
    ]);
    Pjax::end();
    ?>

</div>
