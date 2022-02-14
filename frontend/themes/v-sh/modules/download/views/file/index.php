<?php
/* @var $this \yii\web\View */
/* @var \common\models\UserFiles $share */
/* @var \common\models\UserFileEvents $eventWithUuid */
/* @var array $servers */

use yii\helpers\Url;
use frontend\assets\v20190812\modFileDownloadAsset;

/* assets */
modFileDownloadAsset::register($this);

/* */
$this->title = Yii::t('modules/download', 'title_file', ['file_name' => $share->file_name]);

?>
<div class="content container filemanager"
     id="wss-data"
     data-stun-url="<?= $servers['stun'][0]['server_url'] ?>"
     data-signal-url="wss://<?= $servers['sign'][0]['server_url'] ?>/ws/webshare/<?= $share->share_hash ?>"
     data-proxy-url="<?= $servers['proxy'][0]['server_url'] ?>/file/<?= $share->share_hash ?>"
     data-app-url="pvtbox://file/<?= $share->share_hash ?>"
     data-file-name="<?= $share->file_name ?>"
     data-file-size="<?= $share->file_size ?>"
     data-share-hash="<?= $share->share_hash ?>"
     data-share-enable-pass="<?= $share->share_password ? 1 : 0 ?>"
     data-share-delete-immediately="<?= $share->share_ttl_info == -1 ? 1 : 0 ?>"
     data-event-uuid="<?= $eventWithUuid->event_uuid ?>">

    <div id="download-control-panel">
        <h2><?= Yii::t('modules/download', 'Get_shared_file') ?> <span class="shared-file-name-title masterTooltip" title="<?= $share->file_name ?>"><?= $share->file_name ?></span></h2>

        <div class="share-return-to-folder">
            <?php
            if (($share->share_group_hash) && ($share->file_parent_id)) {
                echo '<a href="' . Url::to(['/folder/' . $share->share_group_hash . '/' . $share->file_parent_id], CREATE_ABSOLUTE_URL) . '">' . Yii::t('modules/download', 'Return_to_folder') . '</a>';
            }
            ?>
        </div>

        <div class="share-main">

            <div class="share-fileinfo" id="share-fileinfo" data-needpasswd="<?= $share->share_password ? 1 : 0 ?>">
                <table class="shared-file" id="shared-file">
                    <tr><td><?= Yii::t('modules/download', 'File_name') ?></td><td id="td-file-name"><div><?= $share->file_name ?></div></td></tr>
                    <tr><td><?= Yii::t('modules/download', 'File_size') ?></td><td id="td-file-size"><?= $share->file_size ?></td></tr>
                    <tr><td><?= Yii::t('modules/download', 'File_hash') ?></td><td id="td-share-hash"><?= $share->share_hash ?></td></tr>
                    <tr class="hidden"><td><?= Yii::t('modules/download', 'Event_UUID') ?></td><td id="td-event-uuid"><?= $eventWithUuid->event_uuid ?></td></tr>
                    <tr class="hidden"><td><?= Yii::t('modules/download', 'Online_nodes') ?></td><td id="td-online-nodes">n/a</td></tr>
                </table>
            </div>

            <div class="share-progress">
                <p>
                    <input type="button"
                           class="btn primary-btn sm-btn btn-inline"
                           id="btn_download"
                           onclick="return false;"
                           data-btn-name-when-pause="<?= Yii::t('modules/download', 'Pause') ?>"
                           data-btn-name-when-resume="<?= Yii::t('modules/download', 'Resume') ?>"
                           data-btn-name-when-ready="<?= Yii::t('modules/download', 'Download_file') ?>"
                           value="<?= Yii::t('modules/download', 'Download_file') ?>" />
                    <input type="button"
                           class="btn primary-btn sm-btn btn-inline"
                           id="btn_download_by_app"
                           onclick="return false;"
                           value="<?= Yii::t('modules/download', 'Download_file_by_app') ?>" />
                </p>


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
                    <div id="total-download-file-info" class="file-info-total"></div>
                    <div id="total-download-file-percent"
                         class="progress-bar progress-bar-success"
                         style="width: 0%;"></div>
                </div>



                <p id="p_result">
                    <label class="label-result" id="label-dynamic-result"></label>
                    <label class="label-result label-starting-download"><?= Yii::t('modules/download', 'Starting_download') ?></label>
                    <label class="label-result label-downloading"><?= Yii::t('modules/download', 'Downloading') ?></label>
                    <label class="label-result label-downloaded-successfully"><?= Yii::t('modules/download', 'Downloaded_successfully') ?></label>
                    <label class="label-result label-download-failed"><?= Yii::t('modules/download', 'Download_failed') ?></label>
                    <label class="label-result label-download-should-start"><?= Yii::t('modules/download', 'Download_should_start') ?></label>
                </p>
            </div>
            <div>
                <a id="download-anchor" class="hidden"></a>
                <a id="download-anchor-app" class="hidden"></a>
                <a id="download-anchor-proxy" class="hidden"></a>
            </div>
        </div>

        <div class="message-info hidden">
            <span id="message-info-WebRTC" class="hidden"><?= Yii::t('modules/download', 'not_support_WebRTC') ?></span>
            <span id="message-info-Websocket" class="hidden"><?= Yii::t('modules/download', 'not_support_Websocket') ?></span>
        </div>

        <div class="share_state" id="state">
            <label id="label_state" class="hidden"></label>
        </div>
    </div>

    <div id="preview-tpl" style="display: none;">
        <div class="try-download-it">
            <span>
                <?= Yii::t('modules/download', 'Try_Download_It') ?>
            </span>
        </div>
        <div class="no-online-nodes">
            <span>
                Preview impossible.<br />
                Online nodes are not available at the moment.
            </span>
        </div>
        <div class="share-has-password">
            <span>
                File secured by password, <a href="#" class="enter-share-preview-password void-0">click here</a> to enter password and preview file
                <!--File cannot be previewed due to password protection.<br />Try download it instead.-->
            </span>
        </div>
        <div class="preview-loading">
            <div class="big-loading-img" title="loading..."></div>
            <span>Loading preview... <br>(Fetching content from nodes)</span>
        </div>
    </div>
    <div class="table table--settingsPopUp preview-body preview-share" id="preview-body">

        <div id="media_" class="share-media_">



        </div>

    </div>


</div>

<!-- BEGIN .Modal #password-required-modal -->
<div class="popup top-popup collaborate-modal-popup colleague-list-modal" id="password-required-modal" data-has-nicescroll="1">
    <a class="hidden js-open-form" href="#" id="trigger-password-required-modal" data-src="#password-required-modal" data-modal="true"></a>
    <div class="popup__inner">

        <div class="popup-form-title"><?= Yii::t('modules/download', 'Password required') ?></div>

        <div class="form-block">

            <div id="group-passrequired-password" class="form-group field-passrequired-password required">
                <input id="passrequired-password"
                       class="form-control"
                       placeholder="Enter password"
                       autocomplete="off"
                       aria-required="true"
                       type="password" />
                <p id="passrequired-blank-password"
                   class="hidden help-block help-block-error"><?= Yii::t('modules/download', 'Password_cant_be_blank') ?></p>
                <p id="passrequired-wrong-password"
                   class="hidden help-block help-block-error"><?= Yii::t('modules/download', 'Wrong_password') ?></p>
                <p id="passrequired-block-ip-tries"
                   class="hidden help-block help-block-error"><?= Yii::t('modules/download', 'Too_many_wrong_tries') ?></p>
            </div>

            <button type="submit"
                    id="btn-enter-password"
                    class="btn primary-btn wide-btn"
                    name="GetAccess"
                    data-off-onclick="checkPass()"><?= Yii::t('modules/download', 'button_OK') ?></button>
        </div>


    </div>
</div>
<!-- END .Modal #fileversions-modal -->
