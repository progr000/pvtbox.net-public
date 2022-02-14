<?php

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\SelfHostUsersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model common\models\SelfHostUsers */

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use common\helpers\Functions;
use common\models\SelfHostUsers;
use backend\assets\ShUsersAsset;

ShUsersAsset::register($this);

$this->title = 'Self Host Users';
?>
<div class="self-host-users-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= '' /*Html::a('Create Self Host Users', ['create'], ['class' => 'btn btn-success'])*/ ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'pjax'=>true,
        'pjaxSettings' => [],
        'panel' => [
            'before' => false,
            'after' => false,
        ],
        //'summary' => 'Показаны записи с <b>{begin, number}</b> по <b>{end, number}</b> из <b>{totalCount, number}</b>.',
        'summary' => "Showing {begin, number}-{end, number} of {totalCount, number} users.",

        'columns' => [

            [
                'attribute' => 'shu_id',
                'width' => '40px',
            ],

            [
                'attribute' => 'shu_created',
                'hAlign'=>'center',
                'width' => '100px;',
                'encodeLabel' => false,
                'label' => 'Reg<br />date',

                'filterType' => GridView::FILTER_DATE_RANGE,
                'filterWidgetOptions' =>([
                    'model' => $searchModel,
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
                            "Today" => [date('Y-m-d'), date('Y-m-d')],
                            "Yesterday" => [date('Y-m-d', time()-86400), date('Y-m-d', time()-86400)],
                            "Last 7 Days" => [date('Y-m-d', time()-7*86400), date('Y-m-d', time())],
                            "Last 30 Days" => [date('Y-m-d', time()-30*86400), date('Y-m-d', time())],
                            "This Month" => [date('Y-m-01', time()), date('Y-m-d', time())],
                        ],

                    ],
                    'pluginEvents' => [
                        "cancel.daterangepicker" => "function(ev, picker) {
                                        //alert(1);
                                        //picker.element[0].children[1].textContent = '';
                                        //$(picker.element[0].nextElementSibling).val('').trigger('change');
                                        $('#selfhostuserssearch-shu_created').val('').trigger('change');
                                    }",

                        "apply.daterangepicker" => "function(ev, picker) {
                                console.log(picker.startDate._isValid);
                                if(picker.startDate._isValid==false){
                                    $('#selfhostuserssearch-shu_created').val('').trigger('change');
                                    return false;
                                }
                                if(picker.endDate._isValid==false){
                                    $('#selfhostuserssearch-shu_created').val('').trigger('change');
                                    return false;
                                }
                                var val = picker.startDate.format(picker.locale.format) + picker.locale.separator + picker.endDate.format(picker.locale.format);

                                //picker.element[0].children[1].textContent = val;
                                //$(picker.element[0].nextElementSibling).val(val);
                                console.log(val);
                                $('#selfhostuserssearch-shu_created').val(val).trigger('change');
                                //return;
                            }",

                    ],
                ]),
            ],

            'shu_email:email',

            [
                'attribute' => 'shu_promo_code',
                'format' => 'raw',
                'label' => 'Promo<br /><a href="#" class="show-all-with-promo">Show not null</a>',
                'encodeLabel' => false,
                'width' => '80px',
                'value' => function($model) {
                    return $model->shu_promo_code;
                },
            ],

            [
                'attribute' => 'shu_business_status',
                'label' => 'Business<br />status',
                'encodeLabel' => false,
                'width' => '40px',
                'filter' => SelfHostUsers::getBusinessStatuses(),
                'value' => function($model) {
                    return SelfHostUsers::getBusinessStatus($model->shu_business_status);
                }
            ],

            [
                'attribute' => 'shu_support_status',
                'label' => 'Support<br />status',
                'encodeLabel' => false,
                'width' => '40px',
                'filter' => SelfHostUsers::getSupportStatuses(),
                'value' => function($model) {
                    return SelfHostUsers::getSupportStatus($model->shu_support_status);
                }
            ],

            [
                'attribute' => 'shu_brand_status',
                'label' => 'Brand<br />status',
                'encodeLabel' => false,
                'width' => '40px',
                'filter' => SelfHostUsers::getBrandStatuses(),
                'value' => function($model) {
                    return SelfHostUsers::getBrandStatus($model->shu_brand_status);
                }
            ],

            [
                'width' => '40px',
                'attribute' => 'license_count_available',
                'label' => 'License<br />available',
                'encodeLabel' => false,
            ],

            [
                'width' => '40px',
                'attribute' => 'license_count_used',
                'label' => 'License<br />used',
                'encodeLabel' => false,
            ],

            [
                'attribute' => 'shu_status',
                'label' => 'User<br />status',
                'encodeLabel' => false,
                'width' => '40px',
                'filter' => SelfHostUsers::getStatuses(),
                'value' => function($model) {
                    return SelfHostUsers::getStatus($model->shu_status);
                }
            ],

            [
                'attribute' => 'shu_license_last_check',
                'hAlign'=>'center',
                'width' => '100px;',
                'encodeLabel' => false,
                'label' => 'Last<br />license<br />check',

                'filterType' => GridView::FILTER_DATE_RANGE,
                'filterWidgetOptions' =>([
                    'model' => $searchModel,
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
                            "Today" => [date('Y-m-d'), date('Y-m-d')],
                            "Yesterday" => [date('Y-m-d', time()-86400), date('Y-m-d', time()-86400)],
                            "Last 7 Days" => [date('Y-m-d', time()-7*86400), date('Y-m-d', time())],
                            "Last 30 Days" => [date('Y-m-d', time()-30*86400), date('Y-m-d', time())],
                            "This Month" => [date('Y-m-01', time()), date('Y-m-d', time())],
                        ],

                    ],
                    'pluginEvents' => [
                        "cancel.daterangepicker" => "function(ev, picker) {
                                        //alert(1);
                                        //picker.element[0].children[1].textContent = '';
                                        //$(picker.element[0].nextElementSibling).val('').trigger('change');
                                        $('#selfhostuserssearch-shu_license_last_check').val('').trigger('change');
                                    }",

                        "apply.daterangepicker" => "function(ev, picker) {
                                console.log(picker.startDate._isValid);
                                if(picker.startDate._isValid==false){
                                    $('#selfhostuserssearch-shu_license_last_check').val('').trigger('change');
                                    return false;
                                }
                                if(picker.endDate._isValid==false){
                                    $('#selfhostuserssearch-shu_license_last_check').val('').trigger('change');
                                    return false;
                                }
                                var val = picker.startDate.format(picker.locale.format) + picker.locale.separator + picker.endDate.format(picker.locale.format);

                                //picker.element[0].children[1].textContent = val;
                                //$(picker.element[0].nextElementSibling).val(val);
                                console.log(val);
                                $('#selfhostuserssearch-shu_license_last_check').val(val).trigger('change');
                                //return;
                            }",

                    ],
                ]),
            ],

            //'shu_company',
            //'shu_name',


            //'shu_status',
            //'shu_role',
            //'shu_support_status',
            //'shu_support_cost',
            //'shu_brand_status',
            //'shu_brand_cost',
            //'user_id',
            //'static_timezone:datetime',
            //'dynamic_timezone:datetime',
            //'pay_type',
            //'license_period',
            //'license_expire',
            //'shu_support_requested',
            //'shu_brand_requested',
            //'shu_user_hash',
            //'',
            //'license_count_available',
            //'license_mismatch',

            [
                //'class' => 'yii\grid\ActionColumn',
                'class'=>'kartik\grid\ActionColumn',
                'width' => '100px',
                'vAlign' => 'top',
                //'template' => '{change-status} {profile} {view} {update} {delete}',
                //'template' => '{change-status} {view} {update} {delete}',
                //'template' => '{change-status} {profile} {view} {update}',
                'template' => '{check-log} {change-status} {view} {update}',
                'buttons' => [

                    'check-log' => function ($url, $model) {
                        $buttons = Html::a(
                            '<span class="glyphicon glyphicon-check-log glyphicon-list-alt" data-shu-id="' . $model->shu_id . '"></span>',
                            '#',
                            [
                                'title' => 'Check log',
                                'data-pjax' => '0',
                                'target' => '_blank',
                                //'class' => 'masterTooltip',
                            ]
                        );
                        return $buttons;
                    },

                    'change-status' => function ($url, $model) {
                        return Html::a(
                            '<span class="glyphicon '.($model->shu_status == SelfHostUsers::STATUS_LOCKED ? "glyphicon-ok-circle" : "glyphicon-ban-circle").'"></span>',
                            //$url.'&qs='.base64_encode(Yii::$app->request->queryString),
                            $url.Functions::prepareQS(['id', '_pjax']),
                            //$url,
                            [
                                'title' => ($model->shu_status == 0 ? "UnBlock" : "Block"),
                                'data-pjax' => "w0",
                                'data-method' => "post",
                                //'class' => 'masterTooltip',
                            ]
                        );
                    }
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

<?= $this->render('/layouts/modal') ?>

