<?php

/* @var $this yii\web\View */
/* @var $colleague \common\models\UserColleagues */
/* @var $colleague_user \common\models\Users */
/* @var $dataProviderFolderList \yii\data\ActiveDataProvider*/
/* @var $Server array */

use yii\helpers\Url;
use common\models\Licenses;
use common\models\UserColleagues;
use frontend\assets\v20190812\adminPanelAsset;

/* assets */
adminPanelAsset::register($this);

/* */
$this->title = Yii::t('user/colleague-manage', 'title');

$user = Yii::$app->user->identity;
$MIN_COUNT_ROW = 7;

if (!sizeof($dataProviderFolderList->allModels)) {
    $colleague['status'] = Yii::t('user/admin-panel', 'awaiting_permissions');
}

if ($colleague_user && in_array($colleague_user->license_type, [Licenses::TYPE_PAYED_PROFESSIONAL, Licenses::TYPE_PAYED_BUSINESS_ADMIN])) {
    $colleague['external'] = Yii::t('user/admin-panel', "external_license");
    $colleague['remove_link'] = Yii::t('user/colleague-manage', "Exclude_user");
} else {
    $colleague['external'] = '';
    $colleague['remove_link'] = Yii::t('user/colleague-manage', 'Remove_User');
}
?>
<div class="content container"
     id="wss-data"
     data-token="<?= $site_token ?>"
     data-wss-url="wss://<?= $Server[0]->server_url ?>/ws/webfm/<?= $site_token ?>"
     data-wss-url-echo-test-server="ws://echo.websocket.org">

    <h1><?= Yii::t('user/admin-panel', 'Admin_Panel') ?></h1>
    <div class="manager-content">
        <div class="manager-content__sidebar">
            <div class="user-label user-label--lg">
                <div class="user-short color-<?= $colleague['color'] ?>"><?= $colleague['name'] ?></div>
                <div class="user-email" id="colleague-email" data-colleague-email="<?= $colleague['email'] ?>"><?= $colleague['email'] ?></div>
                <span class="status-external table-status-external" style="margin-top: 1px;">&nbsp;<?= $colleague['external'] ?></span>
            </div>
            <div class="user-status"><?= Yii::t('user/colleague-manage', 'Status') ?>:<span class="highlight" id="user-status-folder"><?= $colleague['status'] ?></span></div>
            <div class="user-time format-date-js" id="user-status-date" data-ts="<?= $colleague['ts'] ?>"><?= $colleague['date'] ?></div>
            <a class="btn xs-btn back-link" href="<?= Url::to(['/admin-panel/index?tab=2'], CREATE_ABSOLUTE_URL) ?>">
                <svg class="icon icon-back-arrow">
                    <use xlink:href="#back-arrow"></use>
                </svg><span><?= Yii::t('user/colleague-manage', 'Back') ?></span>
            </a>
            <div class="btns">
                <button class="btn primary-btn xs-btn accent-btn delete-colleague-folder btn-deleteColleague"
                        type="button"
                        data-email="<?= $colleague['email'] ?>"
                        data-confirm-text="<?= Yii::t('user/admin-panel', 'Are_you_sure_to_remove_colleague') ?>"
                        data-confirm-yes="<?= Yii::t('app/common', 'OK') ?>"
                        data-confirm-no="<?= Yii::t('app/common', 'Cancel') ?>">
                    <svg class="icon icon-close">
                        <use xlink:href="#close"></use>
                    </svg><span><?= $colleague['remove_link'] ?></span>
                </button>
            </div>
        </div>
        <div class="manager-content__main">
            <div class="btns">
                <div class="inform-folder"><span class="inform"><?= Yii::t('user/colleague-manage', 'User_has_access_to_folders') ?></span></div>
                <div class="btn-add-folder">
                    <a class="btn-createFolder admin-panel-select-folder -js-open-form void-0" data-src="#folder-select-modal" href="#"><?= Yii::t('user/colleague-manage', 'Add_new_folder') ?></a>
                </div>
            </div>
            <div class="manager-list scrollbar-program-vertical" id="manager-list-scroll">
                <table class="-manager-list">
                    <tbody id="manager-list-folder" data-min-count-row="<?= $MIN_COUNT_ROW ?>">
                    <?php
                    foreach ($dataProviderFolderList->allModels as $v) {
                        ?>

                        <tr class="manager-list__row"
                            id="row-colleague-id-<?= $v['colleague_id'] ?>"
                            data-colleague-status="<?= UserColleagues::statusLabel($v['colleague_status']) ?>"
                            data-colleague-date="<?= ($v['colleague_status'] == UserColleagues::STATUS_JOINED)
                                ? date(Yii::$app->params['datetime_format'], strtotime($v['colleague_joined_date']) + Yii::$app->session->get('UserTimeZoneOffset', 0))
                                : date(Yii::$app->params['datetime_format'], strtotime($v['colleague_invite_date']) + Yii::$app->session->get('UserTimeZoneOffset', 0))
                            ?>"
                            data-colleague-ts="<?= ($v['colleague_status'] == UserColleagues::STATUS_JOINED)
                                ? strtotime($v['colleague_joined_date']) + Yii::$app->session->get('UserTimeZoneOffset', 0)
                                : strtotime($v['colleague_invite_date']) + Yii::$app->session->get('UserTimeZoneOffset', 0)
                            ?>">
                            <td class="manager-list__col"><span class="file file-catalog file-catalogFull admin-panel-file-catalogFull"><?= $v['file_name'] ?></span></td>
                            <td>

                                <div class="dropdown-actions dropdown" id="dropdown-trigger-<?= $v['colleague_id'] ?>">

                                    <?php
                                    if ($v['is_owner']) {
                                        if (in_array($v['colleague_status'], [UserColleagues::STATUS_JOINED, UserColleagues::STATUS_INVITED])) {
                                            ?>

                                            <div class="dropdown-toggle"
                                                 data-toggle="dropdown"><!--
                                                  --><?= Yii::t('app/common', 'Can') ?><!--
                                                  --><span id="access-colleague-id-<?= $v['colleague_id'] ?>"
                                                           data-access-type="<?= $v['colleague_permission'] ?>"><!--
                                                           --><?= UserColleagues::permissionLabel($v['colleague_permission']) ?><!--
                                                  --></span><!--
                                          --></div>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"
                                                       class="admin-panel-change-collaboration-access"
                                                       data-colleague-id="<?= $v['colleague_id'] ?>"
                                                       data-collaboration-id="<?= $v['collaboration_id'] ?>"
                                                       data-file-uuid="<?= $v['file_uuid'] ?>"
                                                       data-access-type="<?= UserColleagues::PERMISSION_EDIT ?>"
                                                       data-subtext="<?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_EDIT) ?>"><!--
                                                  --><?= Yii::t('app/common', 'Can') ?> <?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_EDIT) ?><!--
                                              --></a></li>
                                                <li><a href="#"
                                                       class="admin-panel-change-collaboration-access"
                                                       data-colleague-id="<?= $v['colleague_id'] ?>"
                                                       data-collaboration-id="<?= $v['collaboration_id'] ?>"
                                                       data-file-uuid="<?= $v['file_uuid'] ?>"
                                                       data-access-type="<?= UserColleagues::PERMISSION_VIEW ?>"
                                                       data-subtext="<?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_VIEW) ?>"><!--
                                                  --><?= Yii::t('app/common', 'Can') ?> <?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_VIEW) ?><!--
                                              --></a></li>
                                                <li><a href="#"
                                                       class="admin-panel-change-collaboration-access"
                                                       data-colleague-id="<?= $v['colleague_id'] ?>"
                                                       data-collaboration-id="<?= $v['collaboration_id'] ?>"
                                                       data-file-uuid="<?= $v['file_uuid'] ?>"
                                                       data-access-type="<?= UserColleagues::PERMISSION_DELETE ?>"
                                                       data-subtext="<?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_DELETE) ?>"><!--
                                                  --><?= Yii::t('user/colleague-manage', 'Remove_from_user') ?><!--
                                              --></a></li>
                                            </ul>

                                            <?php
                                        } else {
                                            ?>
                                            <div class="dropdown-toggle-no-sel">
                                                <a href="#" class="void-0"><?= UserColleagues::statusLabel($v['colleague_status']) ?></a>
                                            </div>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <div class="dropdown-toggle-no-sel">
                                            Is <a href="#" class="void-0"><?= Yii::t('app/common', 'Owner') ?></a>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>

                            </td>
                        </tr>
                        <?php
                    }
                    $lost = $MIN_COUNT_ROW - sizeof($dataProviderFolderList->allModels);
                    if ($lost > 0) {
                        for ($i=1; $i<=$lost; $i++) {
                            echo '
                                <tr class="manager-list__row row-empty">
                                    <td class="manager-list__col"></td>
                                    <td>
                                        <br />
                                    </td>
                                </tr>
                            ';
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- begin TPL-row -->
<table style="display: none;">
    <tbody id="manager-list-empty-row">
    <tr class="manager-list__row row-empty">
        <td class="manager-list__col"></td>
        <td>
            <br />
        </td>
    </tr>
    </tbody>
</table>
<table style="display: none;">
    <tbody id="manager-list-not-empty-row">
    <tr class="manager-list__row"
        id="row-colleague-id-{colleague_id}"
        data-colleague-status="{status}"
        data-colleague-date="{date}"
        data-colleague-ts="{ts}">
        <td class="manager-list__col"><span class="file file-catalog file-catalogFull admin-panel-file-catalogFull">{file_name}</span></td>
        <td>

            <div class="dropdown-actions dropdown" id="dropdown-trigger-{colleague_id}">
                <div class="dropdown-toggle"
                     data-toggle="dropdown"><!--
                      --><?= Yii::t('app/common', 'Can') ?><!--
                      --><span id="access-colleague-id-{colleague_id}"
                               data-access-type="{access_type}"><!--
                               -->{access_type_name}<!--
                      --></span><!--
                 --></div>
                <ul class="dropdown-menu">
                    <li><a href="#"
                           class="admin-panel-change-collaboration-access"
                           data-colleague-id="{colleague_id}"
                           data-collaboration-id="{collaboration_id}"
                           data-file-uuid="{file_uuid}"
                           data-access-type="<?= UserColleagues::PERMISSION_EDIT ?>"
                           data-subtext="<?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_EDIT) ?>"><!--
                         --><?= Yii::t('app/common', 'Can') ?> <?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_EDIT) ?><!--
                     --></a></li>
                    <li><a href="#"
                           class="admin-panel-change-collaboration-access"
                           data-colleague-id="{colleague_id}"
                           data-collaboration-id="{collaboration_id}"
                           data-file-uuid="{file_uuid}"
                           data-access-type="<?= UserColleagues::PERMISSION_VIEW ?>"
                           data-subtext="<?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_VIEW) ?>"><!--
                         --><?= Yii::t('app/common', 'Can') ?> <?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_VIEW) ?><!--
                     --></a></li>
                    <li><a href="#"
                           class="admin-panel-change-collaboration-access"
                           data-colleague-id="{colleague_id}"
                           data-collaboration-id="{collaboration_id}"
                           data-file-uuid="{file_uuid}"
                           data-access-type="<?= UserColleagues::PERMISSION_DELETE ?>"
                           data-subtext="<?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_DELETE) ?>"><!--
                         --><?= Yii::t('user/colleague-manage', 'Remove_from_user') ?><!--
                     --></a></li>
                </ul>

            </div>
        </td>
    </tr>
    </tbody>
</table>
<!-- end TPL-row -->


<!-- begin MODALS -->
<div class="popup" id="folder-select-modal">
    <a class="hidden js-open-form" href="#" id="trigger-folder-select-modal" data-src="#folder-select-modal" data-modal="true"></a>
    <div class="popup__inner">

        <div class="popup-form-title"><?= Yii::t('user/colleague-manage', 'Select_Folder', ['user' => $colleague['email']]) ?></div>
        <div id="available-row-tpl" style="display: none;">
            <div class="manager-list__row available-folder" data-file-uuid="{file_uuid}" id="enc-{enc_file_name}" data-num-pp="{num_pp}">
                <div class="manager-list__col">
                    <span class="file file-catalog{full} file-select-name">{file_name}</span>
                </div>
            </div>
        </div>
        <div id="create-new-folder-row-tpl" style="display: none;">
            <div class="create-new-folder manager-list__row available-folder" style="display: none;">
                <div class="manager-list__col">
                    <span class="file file-catalog file-select-name">
                        <input id={input_create_new_folder} type="text" value="" style="height: 25px; width: 90%"/>
                    </span>
                </div>
            </div>
        </div>

        <div class="available-list" id="available-folder-list">
        </div>

        <div style="margin-top: 5px; text-align: right;">
            <a class="create-new-folder-for-collaboration btn-createFolder void-0" href="#" style="float: left;">Create new folder</a>
            <button type="submit"
                    class="button-confirm-yes btn primary-btn sm-btn orange-btn js-close-popup confirm-yes select-available-folder"
                    name="select-folder"><?= Yii::t('user/colleague-manage', 'Select') ?></button>
            <button type="button"
                    class="button-confirm-no btn primary-btn sm-btn white-btn js-close-popup confirm-no" data-dismiss="modal"
                    name="close-modal"><?= Yii::t('user/colleague-manage', 'Cancel') ?></button>
        </div>


    </div>
    <button class="btn popup-close-btn js-close-popup" type="button" title="<?= Yii::t('app/common', "Close") ?>">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg>
    </button>
</div>
<!-- end MODALS -->