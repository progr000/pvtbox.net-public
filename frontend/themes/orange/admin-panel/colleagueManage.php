<?php

/* @var $this yii\web\View */
/* @var $colleague \common\models\UserColleagues */
/* @var $colleague_user \common\models\Users */
/* @var $dataProviderFolderList \yii\data\ActiveDataProvider*/

use yii\bootstrap\Modal;
use yii\helpers\Url;
use common\models\Licenses;
use common\models\UserColleagues;
use frontend\assets\orange\adminPanelAsset;
use frontend\assets\orange\dateFormatAsset;
//use frontend\assets\jstreeAsset;

/* assets */
//dateFormatAsset::register($this);
adminPanelAsset::register($this);
//jstreeAsset::register($this);

/* */
$this->title = Yii::t('user/colleague-manage', 'title');

$user = Yii::$app->user->identity;
$MIN_COUNT_ROW = 10;

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

<!-- .manager -->
<div class="manager admin-panel-manager noShowBalloon">

    <div class="manager__cont">
        <div style="display: none" id="SignUrl" data-token="<?= $site_token ?>">wss://<?= $Server[0]->server_url ?>/ws/webfm/<?= $site_token ?></div>


        <div class="manager__content">

            <div class="manager__content-row">

                <div class="manager__content-user">

                    <div class="manager__content-user-tr">
                        <div class="userId color-<?= $colleague['color'] ?>"><strong><?= $colleague['name'] ?></strong></div>
                        <div class="user-email" id="colleague-email"><?= $colleague['email'] ?></div>
                        <span class="table-status-external" style="margin-top: 1px;"><?= $colleague['external'] ?></span>
                        <div class="user-status"><?= Yii::t('user/colleague-manage', 'Status') ?>: <b id="user-status-folder"><?= $colleague['status'] ?></b></div>
                        <div class="user-time format-date-js" id="user-status-date" data-ts="<?= $colleague['ts'] ?>"><?= $colleague['date'] ?></div>
                    </div>

                    <div class="manager__content-user-tr">
                        <div class="user-back-btn">
                            <a class="btn-back" href="<?= Url::to(['/admin-panel/index?tab=2'], CREATE_ABSOLUTE_URL) ?>" _href="javascript:void(0)" _onclick="window.history.back();"><?= Yii::t('user/colleague-manage', 'Back') ?></a>
                        </div>
                    </div>

                    <div class="user-remove-link">
                        <a class="delete-colleague-folder btn-deleteColleague" data-email="<?= $colleague['email'] ?>" href="#" data-confirm-text="<?= Yii::t('user/admin-panel', 'Are_you_sure_to_remove_colleague') ?>" data-confirm-yes="<?= Yii::t('app/common', 'OK') ?>" data-confirm-no="<?= Yii::t('app/common', 'Cancel') ?>"><?= $colleague['remove_link'] ?></a>
                    </div>

                </div>

                <div class="manager__content-folder">

                    <div class="manager__button">
                        <div class="manager__button-td">
                            <div class="inform inform-folder"><p><?= Yii::t('user/colleague-manage', 'User_has_access_to_folders') ?></p></div>
                        </div>
                        <div class="manager__button-td">
                            <a class="btn-createFolder admin-panel-select-folder" href="javascript:void(0)"><?= Yii::t('user/colleague-manage', 'Add_new_folder') ?></a>
                        </div>
                    </div>

                    <!-- +++ TPL of row -->
                    <div id="manager-list-empty-row" style="display: none;">
                        <div class="manager-list__row row-empty">
                            <div class="manager-list__col"></div>
                        </div>
                    </div>
                    <div id="manager-list-not-empty-row" style="display: none;">
                        <div class="manager-list__row"
                             id="row-colleague-id-{colleague_id}"
                             data-colleague-status="{status}"
                             data-colleague-date="{date}"
                             data-colleague-ts="{ts}">
                            <div class="manager-list__col">

                                <span class="file file-catalogFull admin-panel-file-catalogFull">{file_name}</span>

                                <div class="dropdown-actions dropdown">
                                    <div class="dropdown-toggle" data-toggle="dropdown">
                                        <?= Yii::t('app/common', 'Can') ?>
                                            <span
                                                id="access-colleague-id-{colleague_id}"
                                                data-access-type={access_type}"
                                            >
                                                {access_type_name}
                                            </span>
                                    </div>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="javascript:void(0)"
                                               class="admin-panel-change-collaboration-access"
                                               data-colleague-id="{colleague_id}"
                                               data-collaboration-id="{collaboration_id}"
                                               data-file-uuid="{file_uuid}"
                                               data-access-type="<?= UserColleagues::PERMISSION_EDIT ?>"
                                               data-subtext="<?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_EDIT) ?>"
                                            >
                                                <?= Yii::t('app/common', 'Can') ?> <?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_EDIT) ?>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)"
                                               class="admin-panel-change-collaboration-access"
                                               data-colleague-id="{colleague_id}"
                                               data-collaboration-id="{collaboration_id}"
                                               data-file-uuid="{file_uuid}"
                                               data-access-type="<?= UserColleagues::PERMISSION_VIEW ?>"
                                               data-subtext="<?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_VIEW) ?>"
                                            >
                                                <?= Yii::t('app/common', 'Can') ?> <?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_VIEW) ?>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)"
                                               class="admin-panel-change-collaboration-access"
                                               data-colleague-id="{colleague_id}"
                                               data-collaboration-id="{collaboration_id}"
                                               data-file-uuid="{file_uuid}"
                                               data-access-type="<?= UserColleagues::PERMISSION_DELETE ?>"
                                               data-subtext="<?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_DELETE) ?>"
                                            >
                                                <?= Yii::t('user/colleague-manage', 'Remove_from_user') ?>
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- --- TPL of row -->

                    <div class="manager-list" id="manager-list-folder" data-min-count-row="<?= $MIN_COUNT_ROW ?>" -style="height: 400px; overflow: hidden;">
                        <?php
                        foreach ($dataProviderFolderList->allModels as $v) {
                        ?>
                            <div class="manager-list__row"
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
                                <div class="manager-list__col">

                                    <span class="file file-catalogFull admin-panel-file-catalogFull"><?= $v['file_name'] ?></span>

                                    <div class="dropdown-actions dropdown">
                                        <?php
                                        if ($v['is_owner']) {
                                            ?>

                                            <?php
                                            if (in_array($v['colleague_status'], [UserColleagues::STATUS_JOINED, UserColleagues::STATUS_INVITED])) {
                                                ?>
                                                <div class="dropdown-toggle" data-toggle="dropdown">
                                                    <?= Yii::t('app/common', 'Can') ?>
                                                    <span
                                                        id="access-colleague-id-<?= $v['colleague_id'] ?>"
                                                        data-access-type="<?= $v['colleague_permission'] ?>"
                                                    >
                                                        <?= UserColleagues::permissionLabel($v['colleague_permission']) ?>
                                                    </span>
                                                </div>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a href="javascript:void(0)"
                                                           class="admin-panel-change-collaboration-access"
                                                           data-colleague-id="<?= $v['colleague_id'] ?>"
                                                           data-collaboration-id="<?= $v['collaboration_id'] ?>"
                                                           data-file-uuid="<?= $v['file_uuid'] ?>"
                                                           data-access-type="<?= UserColleagues::PERMISSION_EDIT ?>"
                                                           data-subtext="<?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_EDIT) ?>"
                                                        >
                                                            <?= Yii::t('app/common', 'Can') ?> <?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_EDIT) ?>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)"
                                                           class="admin-panel-change-collaboration-access"
                                                           data-colleague-id="<?= $v['colleague_id'] ?>"
                                                           data-collaboration-id="<?= $v['collaboration_id'] ?>"
                                                           data-file-uuid="<?= $v['file_uuid'] ?>"
                                                           data-access-type="<?= UserColleagues::PERMISSION_VIEW ?>"
                                                           data-subtext="<?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_VIEW) ?>"
                                                        >
                                                            <?= Yii::t('app/common', 'Can') ?> <?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_VIEW) ?>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)"
                                                           class="admin-panel-change-collaboration-access"
                                                           data-colleague-id="<?= $v['colleague_id'] ?>"
                                                           data-collaboration-id="<?= $v['collaboration_id'] ?>"
                                                           data-file-uuid="<?= $v['file_uuid'] ?>"
                                                           data-access-type="<?= UserColleagues::PERMISSION_DELETE ?>"
                                                           data-subtext="<?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_DELETE) ?>"
                                                        >
                                                            <?= Yii::t('user/colleague-manage', 'Remove_from_user') ?>
                                                        </a>
                                                    </li>
                                                </ul>
                                                <?php
                                            } else {
                                                ?>
                                                <div class="--dropdown-toggle" data-toggle="--dropdown">
                                                    <?= UserColleagues::statusLabel($v['colleague_status']) ?>
                                                </div>
                                                <?php
                                            }
                                            ?>

                                            <?php
                                        } else {
                                            ?>
                                            <div class="dropdown-toggle-no-sel">
                                                <?= Yii::t('app/common', 'Owner') ?>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </div>

                                </div>
                            </div>
                        <?php
                        }
                        $lost = $MIN_COUNT_ROW - sizeof($dataProviderFolderList->allModels);
                        if ($lost > 0) {
                            for ($i=1; $i<=$lost; $i++) {
                                echo '
                                    <div class="manager-list__row row-empty">
                                        <div class="manager-list__col"></div>
                                    </div>
                                ';
                            }
                        }
                        ?>
                    </div>

                </div>

            </div>

        </div>


        <div class="manager__button-bottom" style="display: none;">
            <a class="btn-back" href="<?= Url::to(['/admin-panel/index?tab=2'], CREATE_ABSOLUTE_URL) ?>" _href="javascript:void(0)" _onclick="window.history.back();"><?= Yii::t('user/colleague-manage', 'Back') ?></a>
            <a class="btn-big" href="<?= Url::to(['/admin-panel/index?tab=2'], CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('user/colleague-manage', 'Save') ?></a>
        </div>


    </div>

</div>
<!-- END .manager -->

<?php
// +++ Modal Folder Select
Modal::begin([
    'options' => ['id' => 'folder-select-modal'],
    'closeButton' => false,
    'header' => '<div type="button" class="close" data-dismiss="modal" aria-label="Close"></div>',
    'size' => '',
]);
?>
    <div class="form-block" -id="-datatree">
        <span class="modal-title" style="margin-bottom: 0px;"><?= Yii::t('user/colleague-manage', 'Select_Folder', ['user' => $colleague['email']]) ?></span>

        <div id="available-row-tpl" style="display: none;">
            <div class="manager-list__row available-folder" data-file-uuid="{file_uuid}">
                <div class="manager-list__col">

                    <span class="file file-catalog{full} file-select-name">{file_name}</span>

                </div>
            </div>
        </div>

        <div class="manager-list" id="available-folder-list" style="height: 200px; overflow: hidden;">


        </div>

        <div style="margin-top: 5px; text-align: right;">
            <button type="submit" class="btn-empty confirm-yes orange  select-available-folder" name="select-folder"><?= Yii::t('user/colleague-manage', 'Select') ?></button>
            <button type="button" class="btn-empty confirm-no" data-dismiss="modal" name="close-modal"><?= Yii::t('user/colleague-manage', 'Cancel') ?></button>
        </div>

    </div>
<?php
Modal::end();
