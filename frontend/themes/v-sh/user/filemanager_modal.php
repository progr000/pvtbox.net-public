<?php

/* @var $this yii\web\View */
/* @var $UserNode \common\models\UserNode */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $Server \common\models\Servers */
/* @var $ServerProxy \common\models\Servers */
/* @var $site_token string */
/* @var $User \common\models\Users */
/* @var $ShareElementForm \frontend\models\forms\ShareElementForm */
/* @var $uploadModel \frontend\models\forms\UploadFilesForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use common\models\UserFiles;
use common\models\UserColleagues;
use common\models\Licenses;

?>
<!-- begin MODALS -->

<!-- begin #share-create-remove-modal -->
<div class="popup" id="share-create-remove-modal">
    <a class="hidden js-open-form" href="#" id="trigger-share-create-remove-modal" data-src="#share-create-remove-modal" data-modal="true"></a>
    <a class="btn-back link-get-active hidden void-0" href="#"></a>
    <div class="popup__inner">

        <!-- *** -->
        <div class="tab-pane-share active" id="link-get">
            <div class="modal-body">
                <div class="form-block">
                    <span class="modal-title">Get link</span>
                    <div class="form-group">
                        <a class="btn primary-btn wide-btn white-btn create-share-button void-0" href="#"><?= Yii::t('user/filemanager', 'Create_link') ?></a>
                    </div>
                    <span class="modal-title modal-title-indenting"><?= Yii::t('user/filemanager', 'Send_link_to_email') ?></span>
                    <div class="form-group">
                        <label><input type="email"
                               name="share_email"
                               class="input-notActive"
                               value=""
                               readonly="readonly"
                               disabled="disabled"
                               autocomplete="off"
                               placeholder="<?= Yii::t('user/filemanager', 'Email') ?>"
                               aria-label="<?= Yii::t('user/filemanager', 'Email') ?>"
                               aria-required="true" /></label>
                    </div>
                    <input type="button" class="btn primary-btn wide-btn btn-notActive" value="<?= Yii::t('user/filemanager', 'Send') ?>" />
                </div>
            </div>
        </div>

        <!-- *** -->
        <div class="tab-pane-share hidden" id="link-get-active">
            <div class="modal-body">
                <?php
                $form = ActiveForm::begin([
                    'id' => 'share-send-to-email-form',
                    'enableClientValidation' => true,
                    'options' => [
                        'onsubmit' => 'return false',
                    ],
                ]);
                ?>
                <div class="form-block">
                    <span class="modal-title"><?= Yii::t('user/filemanager', 'Get_link') ?></span>
                    <input type="hidden" name="filesystem_hash" id="filesystem-hash" />
                    <input type="hidden" name="share_hash" id="share-hash" />
                    <label><textarea class="form-control form-control-textarea notActive" id="share-link-field" readonly="readonly"></textarea></label>
                    <div class="link-manage-buttons">
                        <a class="btn-empty btn-link-settings copy-button void-0" href="#" data-clipboard-action="copy" data-clipboard-target="#share-link-field"><?= Yii::t('user/filemanager', 'Copy_link') ?></a>
                        <a class="btn-empty btn-link-settings remove-share-button void-0" href="#"><?= Yii::t('user/filemanager', 'Delete_link') ?></a>
                        <a class="btn-empty btn-link-settings link-settings void-0" href="#"><?= Yii::t('user/filemanager', 'Link_settings') ?></a>
                    </div>
                    <span class="modal-title modal-title-indenting"><?= Yii::t('user/filemanager', 'Send_link_to_email') ?></span>
                    <?=
                    $form->field($ShareElementForm, 'share_email',[
                        'template'=>'{label}{input}{hint}{error}',
                        'options' => [
                            'tag' => 'div',
                            'class' => 'user-name-field'
                        ],
                    ])->textInput([
                        'id' => "share-email",
                        'placeholder' => "E-mail",
                        'autocomplete' => "off",
                        'aria-label' => "E-mail",
                    ])->label(false)
                    ?>
                    <?= Html::submitButton(Yii::t('user/filemanager', 'Send'), ['class' => "btn primary-btn wide-btn", 'name' => "share-send-to-email-button"]) ?>
                </div>
                <?php
                ActiveForm::end();
                ?>
            </div>
        </div>

        <!-- *** -->
        <div class="tab-pane-share hidden" id="link-settings" style="margin-top: 10px;">
            <div class="modal-body">
                <?php
                $form = ActiveForm::begin([
                    'id' => 'share-create-remove-form',
                    'enableClientValidation' => true,
                    'options' => [
                        'onsubmit' => 'return false',
                    ],
                ]);
                ?>
                <div class="form-block">
                    <div id="info-settings-link-update-to-pro" style="display: none;"><?= Yii::t('user/filemanager', 'Settings_are_available_for') ?></div>
                    <a id="settings-link-update-to-pro" class="btn-min" href="<?= Url::to(['/pricing'], CREATE_ABSOLUTE_URL) ?>"><?=  Yii::t('user/filemanager', 'Update_to_pro_business', ['type_licenses' => Licenses::getType(Licenses::TYPE_PAYED_PROFESSIONAL) . '/' . Licenses::getType(Licenses::TYPE_PAYED_BUSINESS_ADMIN)]) ?></a>
                    <span class="modal-title"><?= Yii::t('user/filemanager', 'Set_expiry_date') ?></span>
                    <div id="share-ttl-div" class="select-wrap" data-title-payed="" data-title-unpayed="<?= Yii::t('user/filemanager', 'Available_for_PRO_Business') ?>">
                        <label>
                        <select id="share-ttl" class="js-select-" disabled="disabled" aria-label="ttl">
                            <?php
                            $ttl_variants = UserFiles::ttlLabels();
                            foreach ($ttl_variants as $k => $v) {
                                echo '<option value="' . $k . '">' . $v . '</option>';
                            }
                            ?>
                        </select>
                        </label>
                    </div>

                    <span class="modal-title modal-title-indenting"><?= Yii::t('user/filemanager', 'Set_password') ?></span>
                    <?=
                    $form->field($ShareElementForm, 'share_password', [
                        'template'=>'{label}{input}{hint}{error}',
                            'options' => [
                            'tag' => 'div',
                        ]
                    ])->passwordInput([
                            'id'            => "share-password",
                            'placeholder'   => Yii::t('user/filemanager', 'Password'),
                            'readonly'      => "readonly",
                            'data-toggle'   => "password",
                            'class'         => "form-control share-password input-notActive masterTooltip",
                            'title_payed'   => '',
                            'title_unpayed' => Yii::t('user/filemanager', 'Available_for_PRO_Business'),
                            'title'         => '',
                            'aria-label'    => Yii::t('user/filemanager', 'Password'),
                        ])->label(false)
                    ?>

                    <?= Html::submitButton(Yii::t('user/filemanager', 'Set'), [
                        'id'       => "share-settings-button",
                        'class'    => "btn primary-btn wide-btn btn-notActive masterTooltip",
                        //'name'     => "share-settings-button",
                        //'disabled' => "disabled",
                        'title_payed'   => '',
                        'title_unpayed' => Yii::t('user/filemanager', 'Available_for_PRO_Business'),
                        'title'         => '',
                    ]) ?>
                </div>
                <?php
                ActiveForm::end();
                ?>
            </div>
        </div>

    </div>
    <button id="confirm-close-x" class="button-confirm-no btn popup-close-btn js-close-popup" type="button" title="<?= Yii::t('app/common', "Close") ?>">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg>
    </button>
</div>
<!-- end #share-create-remove-modal -->

<!-- begin #collaborate-modal -->
<div class="popup collaborate-modal-popup" id="collaborate-modal" data-has-nicescroll="1">
    <a class="hidden js-open-form" href="#" id="trigger-collaborate-modal" data-src="#collaborate-modal" data-modal="true"></a>
    <div class="popup__inner">


        <div class="collaborate-modal-body">
            <?php
            $form = ActiveForm::begin([
                'id' => 'collaborate-form',
                'enableClientValidation' => true,
                'options' => [
                    'onsubmit' => 'return false',
                ],
            ]);
            ?>
            <div class="form-block">

                <input type="hidden" name="collaborate_filesystem_hash" id="collaborate-filesystem-hash" />
                <input type="hidden" name="collaborate_file_uuid" id="collaborate-file-uuid" />

                <div class="modal-title"><?= Yii::t('user/filemanager', 'Invite_colleagues_to') ?> <span id="collaborate-file-name" class="collaborate-file-name-title masterTooltip" title="">file-name</span></div>

                <div class="modal-settCont" id="collaborate-user-new">

                    <div class="modal-settCont__box">
                        <?=
                        $form->field($ShareElementForm, 'share_email')
                            ->textInput([
                                'id' => "colleague-email",
                                //'type' => "email",
                                'placeholder' => "Colleague email",
                                'autocomplete' => "off",
                                'aria-label'   =>  "Colleague email",
                            ])
                            ->label(false)
                        ?>
                    </div>

                    <div class="modal-settCont__box">
                        <div class="dropdown-actions dropdown">
                            <div class="dropdown-toggle" data-toggle="dropdown"><?= Yii::t('app/common', 'Can') ?><span id="collaborate-user-access-type-new" data-action="<?= UserColleagues::PERMISSION_VIEW ?>"><?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_VIEW) ?></span></div>
                            <ul class="dropdown-menu">
                                <li><a href="javascript:void(0)" class="ch-user-collaborate-access-new" data-tokens="new" data-action="<?= UserColleagues::PERMISSION_EDIT ?>" data-subtext="<?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_EDIT) ?>"><?= Yii::t('app/common', 'Can') ?> <?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_EDIT) ?></a></li>
                                <li><a href="javascript:void(0)" class="ch-user-collaborate-access-new" data-tokens="new" data-action="<?= UserColleagues::PERMISSION_VIEW ?>" data-subtext="<?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_VIEW) ?>"><?= Yii::t('app/common', 'Can') ?> <?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_VIEW) ?></a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="modal-settCont__box">
                        <input type="submit" name="invite-email-button" id="button-invite-email" class="btn primary-btn wide-btn btn-invite" value="<?= Yii::t('user/filemanager', 'Invite') ?>" />
                    </div>

                </div>

            </div>


            <!-- begin tpl for this modal -->
            <div id="waiting-tpl" style="display: none;">
                <table class="collaborators-tbl">
                    <tbody>
                    <tr><td colspan="5" class="no-border"></td></tr>
                    <tr><td colspan="5" class="no-border"></td></tr>
                    <tr><td colspan="5" class="no-border"></td></tr>
                    <tr><td colspan="5" class="no-border"><div class="small-loading waiting-form"><?= Yii::t('user/filemanager', 'Loading') ?> <img class="loading" src="/themes/v20190812/images/loading_v4.gif" alt="loading..." /></div></td></tr>
                    </tbody>
                </table>
            </div>
            <table style="display: none;">
                <tbody id="owner_tpl">
                <tr class="table__body is-owner" id="collaborate-user-owner">
                    <td><div class="user-short color-<?= $User->_color ?>"><?= $User->_sname ?></div></td>
                    <td><?= $User->user_email ?></td>
                    <td><?= UserColleagues::statusLabel(UserColleagues::STATUS_JOINED) ?></td>
                    <td>
                        <div class="dropdown-actions-view">
                            <div class="dropdown-toggle">
                                <span class="isorcan"><?= Yii::t('app/common', 'Is') ?></span>
                                <span class="access-name"><?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_OWNER) ?></span>
                            </div>
                        </div>
                    </td>
                    <td></td>
                </tr>
                </tbody>
            </table>
            <table style="display: none;">
                <tbody id="colleagues_tpl">
                <tr class="table__body {owner_or_colleague}" id="collaborate-user-{colleague_id}">
                    <td><div class="user-short color-{color}">{name}</div></td>
                    <td>{email}</td>
                    <td><b class="table-status">{status}</b><i>{date}</i></td>
                    <td>
                        <div class="dropdown-actions-view {isright}">
                            <div class="dropdown-toggle">
                                <span class="isorcan"><?= Yii::t('app/common', 'Is') ?></span>
                                <span id="colleague-list-user-access-type-{colleague_id}" class="access-name" data-action="{access_type}">{access_type_name}</span>
                            </div>
                        </div>

                        <div class="dropdown-actions dropdown {canright}">
                            <div class="dropdown-toggle" data-toggle="dropdown"><?= Yii::t('app/common', 'Can') ?><span id="collaborate-user-access-type-{colleague_id}" data-action="{access_type}">{access_type_name}</span></div>
                            <ul class="dropdown-menu">
                                <li><a href="javascript:void(0)" class="ch-user-collaborate-access" data-tokens="{colleague_id}" data-action="<?= UserColleagues::PERMISSION_EDIT ?>" data-subtext="<?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_EDIT) ?>"><?= Yii::t('app/common', 'Can') ?> <?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_EDIT) ?></a></li>
                                <li><a href="javascript:void(0)" class="ch-user-collaborate-access" data-tokens="{colleague_id}" data-action="<?= UserColleagues::PERMISSION_VIEW ?>" data-subtext="<?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_VIEW) ?>"><?= Yii::t('app/common', 'Can') ?> <?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_VIEW) ?></a></li>
                            </ul>
                        </div>
                    </td>
                    <td>
                        <a class="table-delete ch-user-collaborate-access void-0 {hideforowner}" href="#" data-tokens="{colleague_id}" data-action="delete"  data-subtext="Delete">Delete</a>
                    </td>
                </tr>
                </tbody>
            </table>
            <!-- end tpl for this modal -->

            <div class="table table--settingsPopUp" id="invite-message-form">
                <div class="table__body-cont" id="invite-message">
                    <span id="invite-title-message" class="modal-title modal-title-indenting3"><?= Yii::t('user/filemanager', 'Message') ?></span>
                    <textarea placeholder="<?= Yii::t('user/filemanager', 'Add_your_message_here') ?>" id="colleague-message"></textarea>
                </div>
                <div id="waiting-form-on-add" class="small-loading waiting-form-on-add">
                    <div>
                        <?= Yii::t('user/filemanager', 'Loading') ?> <img class="loading" src="/themes/v20190812/images/loading_v4.gif" alt="loading..." />
                    </div>
                </div>
            </div>


            <div class="table table--settingsPopUp" id="colleagues-list-form">
                <div class="modal-title modal-title-indenting2" onclick="reInitNiceScroll()"><?= Yii::t('user/filemanager', 'Colleagues_list') ?></div>

                <div class="table-wrap">
                    <div id="wrap-for-collaborators" class="table-wrap__inner scrollbar-program-horizontal">

                        <div class="tbl-head collaborators-tbl-head-colleagues">
                            <table class="collaborators-tbl">
                                <thead>
                                    <tr>
                                        <th><div style="width: 30px;"></div></th>
                                        <th><?= Yii::t('user/filemanager', 'User') ?></th>
                                        <th><?= Yii::t('user/filemanager', 'Status') ?></th>
                                        <th><?= Yii::t('user/filemanager', 'Permission') ?></th>
                                        <th><?= Yii::t('user/filemanager', 'Action') ?></th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="tbl-body collaborators-tbl-list-colleagues scrollbar-program-vertical">
                            <table class="collaborators-tbl">
                                <tbody id="colleagues-list">

                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div>


            <?php
            ActiveForm::end();
            ?>

            <div class="form-block cancel-collaboration-block" style="padding-top: 5px;">
                <?= Html::button(Yii::t('user/filemanager', 'Cancel_collaboration'), [
                    'class' => "btn primary-btn wide-btn cancel-collaboration btn-notActive",
                    'name' => "cancel-collaboration-button",
                    'id' => "btn-cancel-collaboration",
                ]) ?>
            </div>

        </div>


    </div>
    <button id="confirm-close-x" class="button-confirm-no btn popup-close-btn js-close-popup" type="button" title="<?= Yii::t('app/common', "Close") ?>">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg>
    </button>
</div>
<!-- end #collaborate-modal -->


<!-- begin #colleague-list-modal -->
<div class="popup collaborate-modal-popup colleague-list-modal" id="colleague-list-modal" data-has-nicescroll="1">
    <a class="hidden js-open-form" href="#" id="trigger-colleague-list-modal" data-src="#colleague-list-modal" data-modal="true"></a>
    <div class="popup__inner">


        <div class="modal-body">

            <input type="hidden" name="leave_collaborate_filesystem_hash" id="leave-collaborate-filesystem-hash" />
            <input type="hidden" name="leave_collaborate_file_uuid" id="leave-collaborate-file-uuid" />

            <!-- begin tpl for this modal -->
            <table style="display: none;">
                <tbody id="colleagues_view_tpl">
                <tr class="table__body" id="collaborate-user-{colleague_id}">
                    <td><div class="user-short color-{color}">{name}</div></td>
                    <td>{email}</td>
                    <td><b class="table-status">{status}</b><i>{date}</i></td>
                    <td>
                        <div class="dropdown-actions-view">
                            <div class="dropdown-toggle">
                                <span class="isorcan {show_can}"><?= Yii::t('app/common', 'Can') ?></span>
                                <span class="isorcan {show_is}"><?= Yii::t('app/common', 'Is') ?></span>
                                <span id="colleague-list-user-access-type-{colleague_id}" class="access-name" data-action="{access_type}">{access_type_name}</span>
                            </div>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
            <!-- end tpl for this modal -->

            <div class="table table--settingsPopUp">

                <div class="modal-title"><?= Yii::t('user/filemanager', 'Colleagues_list_on_folder') ?> <span id="colleague-list-file-name" class="collaborate-file-name-title masterTooltip" title="">file-name</span></div>

                <div class="table-wrap">
                    <div id="wrap-for-collaborators" class="table-wrap__inner scrollbar-program-horizontal">

                        <div class="tbl-head collaborators-tbl-head-colleagues">
                            <table class="collaborators-tbl">
                                <thead>
                                <tr>
                                    <th><div style="width: 30px;"></div></th>
                                    <th><?= Yii::t('user/filemanager', 'User') ?></th>
                                    <th><?= Yii::t('user/filemanager', 'Status') ?></th>
                                    <th><?= Yii::t('user/filemanager', 'Permission') ?></th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="tbl-body collaborators-tbl-list-colleagues scrollbar-program-vertical">
                            <table class="collaborators-tbl">
                                <tbody id="colleagues-list-view">

                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div>

            <div class="form-block" style="padding-top: 5px;">
                <?= Html::button(Yii::t('user/filemanager', 'Leave_collaboration'), ['class' => "btn primary-btn wide-btn leave-collaboration", 'name' => "leave-collaboration-button"]) ?>
            </div>

        </div>

    </div>
    <button id="confirm-close-x" class="button-confirm-no btn popup-close-btn js-close-popup" type="button" title="<?= Yii::t('app/common', "Close") ?>">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg>
    </button>
</div>
<!-- end #colleague-list-modal -->


<!-- begin #fileversions-modal -->
<div class="popup fileversions-modal-popup fileversions-modal" id="fileversions-modal" data-has-nicescroll="1">
    <a class="hidden js-open-form" href="#" id="trigger-fileversions-modal" data-src="#fileversions-modal" data-modal="true"></a>
    <div class="popup__inner">


        <div class="modal-body">

            <?php
            $form = ActiveForm::begin([
                'id' => 'fileversions-form',
                'enableClientValidation' => true,
                'options' => [
                    'onsubmit' => 'return false',
                ],
            ]);
            ?>
            <input type="hidden" name="fileversions_filesystem_hash" id="fileversions-filesystem-hash" />
            <input type="hidden" name="fileversions_file_id" id="fileversions-file-id" />
            <input type="hidden" name="fileversions_file_uuid" id="fileversions-file-uuid" />


            <!-- begin tpl for this modal -->
            <table style="display: none;">
                <tbody id="version_tpl">
                <tr class="table__body" id="event-{event_id}">
                    <td>{event_timestamp}</td>
                    <td>{file_size_after_event}</td>
                    <td>{event_type} by {user_email}</td>
                    <td>
                        <a class="restore-patch void-0 {disabled}" href="#" data-restore-status="{status}" data-event-id="{event_id}">
                            <span class="event-restore table-color-darkRed {disabled}"  style="display: {show_restore};"><?= Yii::t('user/filemanager', 'Restore') ?></span>
                            <span class="event-current table-color-darkBlue {disabled}" style="display: {show_current}; cursor: default;"><?= Yii::t('user/filemanager', 'Current') ?></span>
                            <span class="event-restored table-color-darkRed {disabled}" style="display: {show_restored};"><?= Yii::t('user/filemanager', 'Restored') ?><br /><span style="font-size: 8px;">({date_restored})</span></span>
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>
            <!-- end tpl for this modal -->

            <div class="table table--fileversionsPopUp" id="fileversions-list-form">

                <span class="modal-title"><?= Yii::t('user/filemanager', 'Patch_versions_rollback') ?> <span id="fileversions-file-name" class="collaborate-file-name-title masterTooltip" title="">file-name</span></span>

                <div class="table-wrap table-wrap-fileversions">
                    <div id="wrap-for-collaborators" class="table-wrap__inner scrollbar-program-horizontal">

                        <div class="tbl-head fileversions-tbl-head">
                            <table class="fileversions-tbl">
                                <thead>
                                <tr>
                                    <th><?= Yii::t('user/filemanager', 'Date') ?></th>
                                    <th><?= Yii::t('user/filemanager', 'Size') ?></th>
                                    <th><?= Yii::t('user/filemanager', 'Info') ?></th>
                                    <th><?= Yii::t('user/filemanager', 'Action') ?></th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="tbl-body fileversions-tbl-list scrollbar-program-vertical">
                            <table class="fileversions-tbl">
                                <tbody id="fileversions-list">

                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div>
            <?php
            ActiveForm::end();
            ?>

        </div>


    </div>
    <button id="confirm-close-x" class="button-confirm-no btn popup-close-btn js-close-popup" type="button" title="<?= Yii::t('app/common', "Close") ?>">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg>
    </button>
</div>
<!-- end #fileversions-modal -->


<!-- begin #file-upload-modal -->
<div id="upload-dialog-tpl" style="display: none;">
    <div id="upload-dialog"
         class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-draggable std42-dialog  elfinder-dialog elfinder-dialog-notify elfinder-dialog-active ui-front">
        <div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
            <a href="#" class="ui-dialog-titlebar-min ui-corner-all"><span class="ui-icon-- ui-icon-min"></span></a>
            &nbsp;
            <a href="#" class="ui-dialog-titlebar-close2 ui-corner-all"><span class="ui-icon-- ui-icon-close"></span></a>
        </div>

        <div id="total-progress"
             class="progress progress-striped active"
             role="progressbar"
             aria-valuemin="0"
             aria-valuemax="100"
             aria-valuenow="0">
            <div class="file-info-total">file-info</div>
            <div class="progress-bar progress-bar-success"
                 style="width:0%;"
                 data-dz-totaluploadprogress=""></div>
        </div>

        <div class="ui-dialog-content ui-widget-content" id="preview_uploads">

        </div>
    </div>
</div>
<!-- begin tr -->
<div style="display: none;">
    <div id="template_file_upload_tr" class="elfinder-notify elfinder-notify-open file-row">
        <div class="file-info">
            <span class="elfinder-notify-msg name" data-dz-name="">file_name</span>
            <span class="error text-danger" data-dz-errormessage></span>
        </div>
        <div class="progress progress-upload progress-file progress-striped active"
             role="progressbar"
             aria-valuemin="0"
             aria-valuemax="100"
             aria-valuenow="0">
            <div class="progress-text"
                 data-dz-size="">file_size</div>
            <div class="progress-bar progress-bar-success"
                 style="width:0%;"
                 data-dz-uploadprogress=""></div>
        </div>

        <button class="-btn -btn-warning btn-cancelUpload btn-cancel-upload cancel" data-dz-remove>
            <i class="glyphicon glyphicon-ban-circle"></i>
            <span><?= Yii::t('app/common', 'Cancel') ?></span>
        </button>
    </div>
</div>
<!-- end tr -->
<!-- begin #file-upload-modal -->


<!-- begin #download-dialog-tpl -->
<div id="download-dialog-tpl" style="display: none;">
    <div id="download-dialog" class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-draggable std42-dialog  elfinder-dialog elfinder-dialog-notify elfinder-dialog-active ui-front" style="width: 280px; height: auto; top: 12px; right: 12px; display: block; z-index: 1000;">
        <div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
            <a href="#" class="ui-dialog-titlebar-close3 ui-corner-all"><span class="ui-icon-- ui-icon-close-- ui-icon-close-download"></span></a>
        </div>

        <div class="prepare-download-text">
            <?= Yii::t('user/filemanager', 'Prepare_for_download') ?>
        </div>

        <div id="download-total-progress"
             class="progress -progress-upload progress-striped active"
             role="progressbar"
             aria-valuemin="0"
             aria-valuemax="100"
             aria-valuenow="0">
            <div class="progress-bar progress-bar-success"
                 style="width: 100%;"
                 data-dz-totaluploadprogress=""></div>
        </div>

    </div>
</div>
<!-- end #download-dialog-tpl -->


<!-- begin #download-dialog-rtc-tpl -->
<div id="download-dialog-rtc-tpl" style="display: none;">
    <div id="download-dialog-rtc" class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-draggable std42-dialog  elfinder-dialog elfinder-dialog-notify elfinder-dialog-active ui-front" style="width: 280px; height: auto; top: 12px; right: 12px; display: block; z-index: 1000;">
        <div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
            <a href="#" class="ui-dialog-titlebar-close4 ui-corner-all"><span class="ui-icon-- ui-icon-close"></span></a>
        </div>

        <div id="total-progress-download-rtc"
             class="progress progress-striped active"
             role="progressbar"
             aria-valuemin="0"
             aria-valuemax="100"
             aria-valuenow="0">
            <div id="total-download-sys-info"
                 style="display: none;"
                 data-total-size="0"
                 data-total-downloaded-size="0"
                 data-total-speed="0">
            </div>
            <div id="total-download-file-info" class="file-info-total">total-download-file-info</div>
            <div id="total-download-file-percent"
                 class="progress-bar progress-bar-success"
                 style="width:0%;"></div>
        </div>

        <div class="ui-dialog-content ui-widget-content" id="rows_downloads-rtc">



        </div>
    </div>
</div>

<div id="download-dialog-rtc-row-tpl" style="display: none;">
    <div id="download-task-{last_event_uuid}" data-event-uuid="{last_event_uuid}" data-file-size="{file_size}" data-file-downloaded="0" data-file-speed="0" class="elfinder-notify elfinder-notify-open file-row">
        <div class="file-info">
            <span class="elfinder-notify-msg name">{file_name}</span>
        </div>
        <div class="progress progress-upload progress-striped active"
             role="progressbar"
             aria-valuemin="0"
             aria-valuemax="100"
             aria-valuenow="0"
             style="margin-bottom: 0px;">
            <div id="download-file-info-{last_event_uuid}" class="file-info-total">{bytesSent} / {bytesTotal}, 0bps</div>
            <div id="download-file-percent-{last_event_uuid}"
                 class="progress-bar progress-bar-success"
                 style="width: 0%;"></div>
        </div>

        <button class="-btn -btn-warning btn-pauseRTCDownload btn-pause-rtc-download pause" data-event-uuid="{last_event_uuid}">
            <i class="glyphicon glyphicon-pause"></i>
            <span></span>
        </button>
        <button class="-btn -btn-warning btn-cancelRTCDownload btn-cancel-rtc-download cancel" data-event-uuid="{last_event_uuid}">
            <i class="glyphicon glyphicon-ban-circle"></i>
            <span></span>
        </button>
    </div>
</div>
<!-- end #download-dialog-rtc-tpl -->


<!-- begin #preview-modal -->
<div class="popup preview-modal-popup preview-modal" id="preview-modal" data-has-nicescroll="1" data-close-callback="afterClosePreview">
    <a class="hidden js-open-form" href="#" id="trigger-preview-modal" data-src="#preview-modal" data-modal="true"></a>
    <div class="popup__inner" id="preview-container">


        <div class="modal-content">

            <div class="modal-title modal-title-preview">
                Preview window for file <span id="preview-file-name" class="collaborate-file-name-title masterTooltip" title="">file-name</span>
            </div>

            <div id="preview-tpl" style="display: none;">
                <div class="preview-loading">
                    <div class="big-loading-img" title="loading..."></div>
                    <span>
                        <?= Yii::t('modules/download', 'Loading_fetching') ?>
                    </span>
                </div>
                <div class="try-download-it">
                    <span>
                        <?= Yii::t('modules/download', 'Try_Download_It') ?>
                    </span>
                    <br />
                    <a class="btn primary-btn wide-btn btn-try-download-it" target="_blank" href="#">Download</a>
                </div>
            </div>
            <div class="modal-body">
                <div class="table table--settingsPopUp preview-body" id="preview-body">

                    <div id="media_" class="bg-media_"></div>

                </div>
            </div>

        </div>


    </div>
    <button id="confirm-close-x" class="button-confirm-no btn popup-close-btn js-close-popup" type="button" title="<?= Yii::t('app/common', "Close") ?>">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg>
    </button>
</div>
<!-- end #preview-modal -->

<!-- end MODALS -->