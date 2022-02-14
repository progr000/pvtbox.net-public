<?php
/* @var $this yii\web\View */
/* @var $dataProviderReports \yii\data\ActiveDataProvider */
/* @var $model \frontend\models\search\ColleaguesReportsSearch */
/* @var $ColleagueAddForm \frontend\models\forms\ShareElementForm */
/* @var $ReportsSearchModel \frontend\models\search\ColleaguesReportsSearch */
/* @var $admin \common\models\Users */
/* @var $current_count_unread_reports integer */

use yii\widgets\ListView;
use yii\bootstrap\ActiveForm;
use common\models\UserFileEvents;
use frontend\models\search\ColleaguesReportsSearch;

Yii::$app->params['page_without_loader'] = true;
?>

<?php $form = ActiveForm::begin([
    'id' => 'form-reports-filter',
    'method' => 'get',
    'action'  => ['/admin-panel'],
    'options' => [
        'class' => 'reporst-frm',
        'role' => 'form',
    ],
]); ?>

    <input type="hidden" name="tab" value="3">

    <div class="table-wrap" id="events-list-content" data-current-count-unread-reports="<?= $current_count_unread_reports ?>">
        <div class="table-wrap__inner">

            <?php
            $minPageSize = 8;
            $count = $dataProviderReports->count;
            $lost = $minPageSize - $count;

            /* */
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

            $field['colleague_user_email'] = $form->field($ReportsSearchModel, 'colleague_user_email', [
                //'template'=>'{input}{hint}{error}',
                'template'=>'{input}',
                'options' => ['tag' => false],
            ])->dropDownList($drop_list_array, [
                'class' => "js-select sm-select form-reports-filter",
                'aria-label' => $ReportsSearchModel->getAttributeLabel('colleague_user_email'),
            ])->label(false);

            /* */
            $field['event_type'] = $form->field($ReportsSearchModel, 'event_type', [
                'template'=>'{input}',
                'options' => ['tag' => false],
            ])->dropDownList([
                '' => ($ReportsSearchModel->event_type == ""
                    ? Yii::t('user/admin-panel', 'select_filter')
                    : Yii::t('user/admin-panel', 'clear_filter')
                ),
                UserFileEvents::TYPE_CREATE  => Yii::t('user/admin-panel', 'Added_new'),
                UserFileEvents::TYPE_RESTORE => Yii::t('user/admin-panel', 'Restored'),
                UserFileEvents::TYPE_UPDATE  => Yii::t('user/admin-panel', 'Modified_or_Restore_patch'),
                UserFileEvents::TYPE_DELETE  => Yii::t('user/admin-panel', 'Removed'),
                UserFileEvents::TYPE_MOVE    => Yii::t('user/admin-panel', 'Moved_or_Renamed'),
                ColleaguesReportsSearch::EXT_RPT_TYPE_COLLABORATION_CREATED => Yii::t('user/admin-panel', 'Collaboration_created'),
                ColleaguesReportsSearch::EXT_RPT_TYPE_COLLABORATION_DELETED => Yii::t('user/admin-panel', 'Collaboration_deleted'),
            ], [
                'class' => "js-select sm-select form-reports-filter",
                'aria-label' => $ReportsSearchModel->getAttributeLabel('event_type'),
            ])->label(false);

            /* */
            $field['created_at_range'] = $form->field($ReportsSearchModel, 'created_at_range', [
                'template'=>'{input}',
                'options' => ['tag' => false],
            ])->textInput([
                'id'              => "date-range-reports",
                'class'           => "date-input js-datepicker-range sm-input form-reports-filter",
                'data-start-date' => "",
                'data-position'   => "bottom right",
                'readonly'        => "readonly",
                'placeholder'     => Yii::t('user/admin-panel', 'Click_for_date_filter'),
                'aria-label'      => $ReportsSearchModel->getAttributeLabel('created_at_range'),
            ])->label(false);
            ?>
            <?=
            ListView::widget([
                'dataProvider' => $dataProviderReports,
                'itemOptions' => [
                    'tag' => false,
                    'class' => '',
                ],
                'layout' => '
                <table class="reports-tbl">
                    <thead>
                        <tr>
                            <th></th>
                            <th>
                                <span>' . Yii::t('user/admin-panel', 'Reports_User') . '</span>
                                <div class="select-wrap">
                                    ' . $field['colleague_user_email'] . '
                                </div>
                            </th>
                            <th>
                                <span>' . Yii::t('user/admin-panel', 'Reports_Activity') . '</span>
                                <div class="select-wrap">
                                    ' . $field['event_type'] . '
                                </div>
                            </th>
                            <th>
                                <span>' . Yii::t('user/admin-panel', 'Reports_Date') . '</span>
                                <div class="datepicker-wrap">
                                    <button class="btn datepicker-reset-btn js-datepicker-reset" type="button" title="Clear">
                                        <svg class="icon icon-close">
                                            <use xlink:href="#close"></use>
                                        </svg>
                                    </button>
                                    ' . $field['created_at_range'] . '
                                </div>

                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {items}
                    </tbody>
                </table>
                {pager}',
                'emptyText' => $this->render('index_Reports_ListItemNoData', ['field' => $field]),
                'emptyTextOptions' => ['tag' => false],
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

        </div>
    </div>

<?php ActiveForm::end(); ?>

