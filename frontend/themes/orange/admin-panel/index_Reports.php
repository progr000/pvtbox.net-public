<?php
/* @var $this yii\web\View */
/* @var $dataProviderReports \yii\data\ActiveDataProvider */
/* @var $model \frontend\models\search\ColleaguesReportsSearch */
/* @var $ColleagueAddForm \frontend\models\forms\ShareElementForm */
/* @var $ReportsSearchModel \frontend\models\search\ColleaguesReportsSearch */
/* @var $admin \common\models\Users */

use yii\web\View;
use yii\widgets\Pjax;
use yii\widgets\ListView;
use kartik\form\ActiveForm;
use kartik\daterange\DateRangePicker;
use common\models\UserFileEvents;
use frontend\models\search\ColleaguesReportsSearch;

?>


<div class="inputForm admin-panel-inputForm">

    <div class="inputForm__cont">

    </div>

</div>


<div class="table table--reports">

    <?php $form = ActiveForm::begin([
        'id' => 'form-reports-filter',
        'method' => 'get',
        'action'  => ['/admin-panel', 'tab' => 3],
    ]); ?>
    <div class="table__head-cont">

        <div class="table__head">
            <div class="table__head-box"></div>
            <div class="table__head-box"><span><?= Yii::t('user/admin-panel', 'Reports_User') ?></span></div>
            <div class="table__head-box"><span><?= Yii::t('user/admin-panel', 'Reports_Activity') ?></span></div>
            <!--<div class="table__head-box"><span><?= Yii::t('user/admin-panel', 'Reports_Status') ?></span></div>-->
            <div class="table__head-box"><span><?= Yii::t('user/admin-panel', 'Reports_Date') ?></span></div>
        </div>

    </div>

    <div class="table__head-cont">

        <div class="table__head">
            <div class="table__head-box"></div>
            <div class="table__head-box">
                <?php
                $array_list = ColleaguesReportsSearch::getAllColleaguesDropDownList($admin->user_id);
                $drop_list_array = [
                    '' => ($ReportsSearchModel->colleague_user_email == ""
                        ? Yii::t('user/admin-panel', 'select_filter')
                        : Yii::t('user/admin-panel', 'clear_filter')
                    ),
                ];
                foreach ($array_list as $v) {
                    if ($v['is_owner']) { $owner = Yii::t('user/admin-panel', 'list_owner'); } else { $owner = ""; }
                    $drop_list_array[$v['colleague_email']] = $v['colleague_email'] . $owner;
                }

                echo $form->field($ReportsSearchModel, 'colleague_user_email', [
                    'template'=>'{label}<div id="select-filter-report-colleague-user-email" class="select select-color-orange select-filter-report">{input}{hint}{error}</div>'
                ])
                    ->dropDownList($drop_list_array, [
                        //'class' => "form-control form-reports-filter",
                        'class'           => "selectpicker form-reports-filter",
                        'data-actionsBox' => "true",
                        //'data-size-'       => "15",
                    ])
                    ->label(false)
                ?>
            </div>
            <div class="table__head-box">
                <?=
                $form->field($ReportsSearchModel, 'event_type', [
                        'template'=>'{label}<div id="select-filter-report-event-type" class="select select-color-orange select-filter-report">{input}{hint}{error}</div>'
                    ])
                    ->dropDownList([
                        '' => ($ReportsSearchModel->event_type == ""
                            ? Yii::t('user/admin-panel', 'select_filter')
                            : Yii::t('user/admin-panel', 'clear_filter')
                        ),
                        UserFileEvents::TYPE_CREATE  => Yii::t('user/admin-panel', 'Added_new'),
                        UserFileEvents::TYPE_RESTORE => Yii::t('user/admin-panel', 'Restored'),
                        UserFileEvents::TYPE_UPDATE  => Yii::t('user/admin-panel', 'Modified_or_Restore_patch'),
                        UserFileEvents::TYPE_DELETE  => Yii::t('user/admin-panel', 'Removed'),
                        UserFileEvents::TYPE_MOVE    => Yii::t('user/admin-panel', 'Moved_or_Renamed'),
                    ], [
                        //'class' => "form-control form-reports-filter",
                        'class'           => "selectpicker form-reports-filter",
                        'data-actionsBox' => "true",
                        //'data-size-'       => "15",
                    ])
                    ->label(false)
                ?>
            </div>
            <!--<div class="table__head-box"><span><?= Yii::t('user/admin-panel', 'Reports_Status') ?></span></div>-->
            <div class="table__head-box">
                <div class="form-group highlight-addon field-colleaguesreportssearch-date-filter">
                    <div class="input-append input-prepend input-group drp-container">
                        <?php
                        $prepend = '<div class="add-on input-group-addon clear-filter-report-date"><span class="input-group-text"><i class="glyphicon glyphicon-remove"></i></span></div>';
                        $addon = '<div class="add-on input-group-addon"><span class="input-group-text"><i class="glyphicon glyphicon-calendar"></i></span></div>';
                        echo $prepend . DateRangePicker::widget([
                                //'name'=>'kvdate2',
                                'model' => $ReportsSearchModel,
                                'attribute' => 'created_at_range',
                                'useWithAddon'=>true,
                                'convertFormat'=>true,
                                //'startAttribute' => 'from_date',
                                //'endAttribute' => 'to_date',
                                'startInputOptions' => ['value' => date('01.m.Y', time())],
                                'endInputOptions' => ['value' => date('d.m.Y', time())],

                                //'presetDropdown' => true,
                                //'defaultPresetValueOptions' => ['style'=>'display:none'],
                                //'initRangeExpr' => false,

                                'options' => [
                                    'autocomplete' => "off",
                                    'class' => "form-control",
                                    'placeholder' => 'Click for date filter',
                                ],

                                'pluginOptions'=>[
                                    //'locale'=>['format' => 'Y-m-d'],
                                    'alwaysShowCalendars' => true,
                                    'locale' => [
                                        'format' => 'd.m.Y',
                                        'cancelLabel' => 'Clear',
                                    ],

                                    'ranges' => [
                                        //"Clear" => ["", ""],
                                        "Today" => ["moment().startOf('day')", "moment()"],
                                        "Yesterday" => ["moment().startOf('day').subtract(1,'days')", "moment().endOf('day').subtract(1,'days')"],
                                        "Last 7 Days" => ["moment().startOf('day').subtract(7, 'days')", "moment()"],
                                        "Last 30 Days" => ["moment().startOf('day').subtract(30, 'days')", "moment()"],
                                        "This Week" => ["moment().startOf('week')","moment()"],
                                        "This Month" => ["moment().startOf('month')","moment()"],
                                        "Prev Week" => ["moment().startOf('week').subtract(1,'week')","moment().endOf('week').subtract(1,'week')"],
                                        "Prev Month" => ["moment().startOf('month').subtract(1,'month')","moment().endOf('month').subtract(1,'month')"],
                                    ],

                                ],
                                'pluginEvents' => [
                                    //"show.daterangepicker" => "function(ev, picker) { console.log(ev); return false; }",
                                    "cancel.daterangepicker" => "function(ev, picker) {
                                                $('#colleaguesreportssearch-created_at_range').val('').trigger('change');
                                            }",
                                    "apply.daterangepicker" => "function(ev, picker) {
                                        console.log(picker.startDate._isValid);
                                        if(picker.startDate._isValid==false){
                                            $('#userfileeventssearch-created_at_range').val('').trigger('change');
                                            return false;
                                        }
                                        if(picker.endDate._isValid==false){
                                            $('#userfileeventssearch-created_at_range').val('').trigger('change');
                                            return false;
                                        }
                                        var val = picker.startDate.format(picker.locale.format) + picker.locale.separator + picker.endDate.format(picker.locale.format);

                                        //picker.element[0].children[1].textContent = val;
                                        //$(picker.element[0].nextElementSibling).val(val);
                                        console.log(val);
                                        $('#colleaguesreportssearch-created_at_range').val(val).trigger('change');
                                        //return;
                                    }",

                                ],
                            ]) . $addon;
                        ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php ActiveForm::end(); ?>

    <?php Pjax::begin(['id' => 'events-list-content']); ?>
    <?php
    $minPageSize = 8;
    $count = $dataProviderReports->count;
    //$lost = $dataProviderReports->pagination->pageSize - $count;
    $lost = $minPageSize - $count;
    //var_dump($count);
    ?>
    <?=
    ListView::widget([
        'dataProvider' => $dataProviderReports,
        'itemOptions' => [
            'tag' => false,
            'class' => '',
        ],
        'layout' => '<div class="scrollbar-box"><div class="table__body-cont">' . "{items}" . '</div></div>' . "\n{pager}",
        'emptyText' => $this->render('index_Reports_ListItemNoData'),
        'emptyTextOptions' => ['tag' => false],
        //'layout' => "{pager}\n{summary}\n{items}\n{pager}",
        //'summary' => 'Показано {count} из {totalCount}',
        'itemView' => function ($model, $key, $index, $widget) use ($lost, $count) {
            $lost_row = '';
            if ($lost>0 && ($index == $count - 1)) {
                for ($i=1; $i<=$lost; $i++) {
                    $lost_row .= $this->render('index_Reports_ListItemEmpty');
                }
            }
            /* @var $model \frontend\models\search\ColleaguesSearch */
            return $this->render('index_Reports_ListItem', ['model' => $model]) . $lost_row;
        },
    ]);
    ?>
    <?php Pjax::end(); ?>

</div>
