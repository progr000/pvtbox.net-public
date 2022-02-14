<?php
/* @var $model \frontend\models\search\ColleaguesReportsSearch */

use common\models\Users;
use common\models\UserFileEvents;
use common\models\ColleaguesReports;

$TYPE_RENAMED = 100;
$TYPE_MOVE_AND_RENAMED = 101;
$TYPE_RESTORE_PATCH = 102;
$TYPE_COLLABORATION_CREATED = 103;
$TYPE_COLLABORATION_DELETED = 104;

$folder_or_file = Yii::t('user/admin-panel', ($model->is_folder ? 'folder' : 'file'));

$TEMPLATES = [

    //UserFileEvents::TYPE_DELETE  => 'Removed ' . ($model->is_folder ? 'folder' : 'file') . ' <a class="table-color-orange deleted-file" data-file-id="' . $model->file_id . '" data-file-parent-id="' . $model->file_parent_id . '" href="#">' . $model->file_name_before_event . '</a>',
    UserFileEvents::TYPE_DELETE  => Yii::t('user/admin-panel', 'report_template_removed', [
        'folder_or_file'         => $folder_or_file,
        'file_id'                => $model->file_id,
        'file_parent_id'         => $model->file_parent_id,
        'file_name_before_event' => $model->file_name_before_event,
    ]),

    //UserFileEvents::TYPE_RESTORE => 'Restored ' . ($model->is_folder ? 'folder' : 'file') . ' <a class="table-color-orange restored-file" data-file-id="' . $model->file_id . '" data-file-parent-id="' . $model->file_parent_id . '" href="#">' . $model->file_name_after_event . '</a>',
    UserFileEvents::TYPE_RESTORE => Yii::t('user/admin-panel', 'report_template_restored', [
        'folder_or_file'         => $folder_or_file,
        'file_id'                => $model->file_id,
        'file_parent_id'         => $model->file_parent_id,
        'file_name_after_event'  => $model->file_name_after_event,
    ]),

    //UserFileEvents::TYPE_CREATE  => 'Added new ' . ($model->is_folder ? 'folder' : 'file') . ' <a class="table-color-orange created-file" data-file-id="' . $model->file_id . '" data-file-parent-id="' . $model->file_parent_id . '" href="#">' . $model->file_name_after_event . '</a>',
    UserFileEvents::TYPE_CREATE  => Yii::t('user/admin-panel', 'report_template_added', [
        'folder_or_file'         => $folder_or_file,
        'file_id'                => $model->file_id,
        'file_parent_id'         => $model->file_parent_id,
        'file_name_after_event'  => $model->file_name_after_event,
    ]),

    //UserFileEvents::TYPE_UPDATE  => 'Modified ' . ($model->is_folder ? 'folder' : 'file') . ' <a class="table-color-orange updated-file" data-file-id="' . $model->file_id . '" data-file-parent-id="' . $model->file_parent_id . '" href="#">' . $model->file_name_after_event . '</a>',
    UserFileEvents::TYPE_UPDATE  => Yii::t('user/admin-panel', 'report_template_modified', [
        'folder_or_file'         => $folder_or_file,
        'file_id'                => $model->file_id,
        'file_parent_id'         => $model->file_parent_id,
        'file_name_after_event'  => $model->file_name_after_event,
    ]),

    UserFileEvents::TYPE_FORK    => '',

    //UserFileEvents::TYPE_MOVE    => 'Moved ' . ($model->is_folder ? 'folder' : 'file') . ' <a class="table-color-orange moved-file" data-file-id="' . $model->file_id . '" data-file-parent-id="' . $model->file_parent_id . '" data-file-parent-id="' . $model->file_parent_id . '" href="#">' . $model->file_name_after_event . '</a> to <a class="table-color-orange moved-file" data-file-parent-id="' . $model->file_parent_id . '" href="#">' . $model->parent_folder_name_after_event . '</a>',
    UserFileEvents::TYPE_MOVE    => Yii::t('user/admin-panel', 'report_template_moved', [
        'folder_or_file'                 => $folder_or_file,
        'file_id'                        => $model->file_id,
        'file_parent_id'                 => $model->file_parent_id,
        'file_name_after_event'          => $model->file_name_after_event,
        'parent_folder_name_after_event' => $model->parent_folder_name_after_event,
    ]),

    //$TYPE_RENAMED                => 'Renamed ' . ($model->is_folder ? 'folder' : 'file') . ' <a class="table-color-orange renamed-file" data-file-id="' . $model->file_id . '" data-file-parent-id="' . $model->file_parent_id . '" data-file-parent-id="' . $model->file_parent_id . '" href="#">' . $model->file_name_before_event . '</a> to <a class="table-color-orange renamed-file" data-file-parent-id="' . $model->file_parent_id . '" href="#">' . $model->file_name_after_event . '</a>',
    ColleaguesReports::EXT_RPT_TYPE_RENAMED => Yii::t('user/admin-panel', 'report_template_renamed', [
        'folder_or_file'                 => $folder_or_file,
        'file_id'                        => $model->file_id,
        'file_parent_id'                 => $model->file_parent_id,
        'file_name_before_event'         => $model->file_name_before_event,
        'file_name_after_event'          => $model->file_name_after_event,
    ]),

    //$TYPE_MOVE_AND_RENAMED       => 'Moved and renamed ' . ($model->is_folder ? 'folder' : 'file') . ' <a class="table-color-orange moved-renamed-file" data-file-id="' . $model->file_id . '" data-file-parent-id="' . $model->file_parent_id . '" data-file-parent-id="' . $model->file_parent_id . '" href="#">' . $model->file_name_before_event . '</a> to <a class="table-color-orange moved-renamed-file" data-file-parent-id="' . $model->file_parent_id . '" href="#">' . $model->parent_folder_name_after_event . '/' . $model->file_name_after_event . '</a>',
    ColleaguesReports::EXT_RPT_TYPE_MOVE_AND_RENAMED => Yii::t('user/admin-panel', 'report_template_moved_renamed', [
        'folder_or_file'                 => $folder_or_file,
        'file_id'                        => $model->file_id,
        'file_parent_id'                 => $model->file_parent_id,
        'file_name_before_event'         => $model->file_name_before_event,
        'parent_folder_name_after_event' => $model->parent_folder_name_after_event,
        'file_name_after_event'          => $model->file_name_after_event,
    ]),

    //$TYPE_RESTORE_PATCH          => 'Restored patch for ' . ($model->is_folder ? 'folder' : 'file') . ' <a class="table-color-orange updated-file" data-file-id="' . $model->file_id . '" data-file-parent-id="' . $model->file_parent_id . '" href="#">' . $model->file_name_after_event . '</a>',
    ColleaguesReports::EXT_RPT_TYPE_RESTORE_PATCH => Yii::t('user/admin-panel', 'report_template_restored_patch', [
        'folder_or_file'                 => $folder_or_file,
        'file_id'                        => $model->file_id,
        'file_parent_id'                 => $model->file_parent_id,
        'file_name_after_event'          => $model->file_name_after_event,
    ]),

    ColleaguesReports::EXT_RPT_TYPE_COLLABORATION_CREATED => Yii::t('user/admin-panel', 'report_template_collaboration_created', [
        'folder_or_file'                 => $folder_or_file,
        'file_id'                        => $model->file_id,
        'file_parent_id'                 => $model->file_parent_id,
        'file_name_after_event'          => $model->file_name_after_event,
    ]),

    ColleaguesReports::EXT_RPT_TYPE_COLLABORATION_DELETED => Yii::t('user/admin-panel', 'report_template_collaboration_deleted', [
        'folder_or_file'                 => $folder_or_file,
        'file_id'                        => $model->file_id,
        'file_parent_id'                 => $model->file_parent_id,
        'file_name_after_event'          => $model->file_name_after_event,
    ]),
];
$icon = Users::getUserIcon($model->colleague_user_email);

$event_type = $model->event_type;
if ($model->file_renamed) {
    $event_type = $TYPE_RENAMED;
}
if ($model->file_moved && $model->file_renamed) {
    $event_type = $TYPE_MOVE_AND_RENAMED;
}
if ($model->is_rollback && $model->event_type == UserFileEvents::TYPE_UPDATE) {
    $event_type = $TYPE_RESTORE_PATCH;
}
?>
<tr class="report-row <?= ($model->report_isnew ? 'isnew' : '' ) ?>" data-report-id="<?= $model->report_id ?>">
    <td><div class="user-short color-<?= $icon['color'] ?>"><?= $icon['sname'] ?></div></td>
    <td><?= $model->colleague_user_email ?></td>
    <td><?= $TEMPLATES[$event_type] ?></td>
    <td><?= date(Yii::$app->params['datetime_format'], $model->_report_date_ts) ?></td>
</tr>

