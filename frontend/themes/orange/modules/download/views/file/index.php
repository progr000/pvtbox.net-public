<?php
/* @var $this \yii\web\View */
/* @var \common\models\UserFiles $share */
/* @var \common\models\UserFileEvents $eventWithUuid */
/* @var array $servers */

use yii\bootstrap\Modal;
use frontend\assets\orange\modDownloadAsset;
use yii\helpers\Url;

/* assets */
modDownloadAsset::register($this);

/* */
$this->title = Yii::t('modules/download', 'title_file', ['file_name' => $share->file_name]);

?>
<div class="features">

    <div class="features__cont">

        <div class="title"><h2><?= Yii::t('modules/download', 'Get_shared_file') ?></h2></div>

        <style type="text/css">
            .share_header{ width: 100%; }
            .share_main{ clear: both; width: 100%; height: auto; overflow:hidden }
            .share_fileinfo{ float: left; width: 100%; height: auto; }
            .share_progress{ float: left; width: 100%; height: auto; }
            .share_state{ width: 100%; }
            .share_right { float: right; }
            #progress_ {width: 70%;  }
            .hidden { display: none; }
            progress::-moz-progress-bar {
                border-radius: 5px;
                background-image: -moz-linear-gradient( -45deg, rgba(255, 255, 255, .2) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .2) 50%, rgba(255, 255, 255, .2) 75%, transparent 75%, transparent );
                width: 10%;
            }
        </style>

        <div class="share_header" id="header">
            <?php
            if (($share->share_group_hash) && ($share->file_parent_id)) {
                echo '<a href="' . Url::to(['/folder/' . $share->share_group_hash . '/' . $share->file_parent_id], CREATE_ABSOLUTE_URL) . '">' . Yii::t('modules/download', 'Return_to_folder') . '</a>';
            }
            ?>

            <!-- <a href="" class="share_right" id="a_refresh" onclick="window.location.reload(true);"><?= Yii::t('modules/download', 'Reload') ?></a> -->
        </div>

        <div class="share_main" id="main">
            <div class="share_fileinfo" id="share-fileinfo" data-needpasswd="<?= $share->share_password ? 1 : 0 ?>">
                <table class="shared_file" id="shared_file">
                    <tr><td width="100px;"><?= Yii::t('modules/download', 'File_name') ?></td><td id="file_name"><?= $share->file_name ?></td></tr>
                    <tr><td><?= Yii::t('modules/download', 'File_size') ?></td><td id="file_size"><?= $share->file_size ?></td></tr>
                    <tr><td><?= Yii::t('modules/download', 'File_hash') ?></td><td id="share_hash"><?= $share->share_hash ?></td></tr>
                    <tr class="hidden"><td><?= Yii::t('modules/download', 'Event_UUID') ?></td><td id="event_uuid"><?= $eventWithUuid->event_uuid ?></td></tr>
                    <tr class="hidden"><td><?= Yii::t('modules/download', 'Online_nodes') ?></td><td id="online_nodes"></td></tr>
                </table>
                <input id="stun_server_url" type="hidden" value="<?= $servers['stun'][0]['server_url'] ?>">
                <input id="sig_server_url" type="hidden" value="wss://<?= $servers['sign'][0]['server_url'] ?>/ws/webshare/<?= $share->share_hash ?>">
                <input id="proxy_node_url" type="hidden" value="<?= $servers['proxy'][0]['server_url'] ?>/file/<?= $share->share_hash ?>">
                <input id="app_node_url" type="hidden" value="pvtbox://file/<?= $share->share_hash ?>">
                <?php
                $fileNameJs = \Yii::getAlias('@webroot') . '/themes/orange/js/mod_download/main.js';
                if (file_exists($fileNameJs)) {
                    $appendTimestamp = '?v=' . filemtime($fileNameJs);
                } else {
                    $appendTimestamp = '';
                }
                ?>
                <select id="scripts" class="hidden">
                    <option hidden value="/themes/orange/js/mod_download/main.js<?= $appendTimestamp ?>"></option>
                </select>
            </div>
            <hr align="center" width="100%" size="3" color="#dddddd" />
            <div class="share_progress">
                <p>
                    <button class="btn-default" id="btn_download" onclick="dummy();"> <?= Yii::t('modules/download', 'Download_file') ?> </button>
                    <button class="btn-default" id="btn_download_by_app" onclick="download_by_app();" > <?= Yii::t('modules/download', 'Download_file_by_app') ?> </button>
                </p>
                <div id="p_progress" _class="hidden" style="display: none;">
                    <div id="start_progress" class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="display: none; margin-bottom: 0px;">
                        <div id="progress_" class="progress-bar progress-bar-success-share" style="position: relative; left: 0px; width: 0%"></div>
                    </div>

                    <div id="wait_progress" class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="display:none; margin-bottom: 0px;">
                        <div class="progress-bar progress-bar-success" style="position: relative; left: 0px;"></div>
                    </div>
                    <!--
                    <div style="display: none;">
                        progress indicator: <progress id="p___rogress_" max="0" value="25" style="background-color: #FC8F42;"></progress>
                    </div>
                    -->
                </div>
                <p id="p_result" class="hidden">
                    <label id="label_result"></label>
                </p>
            </div>
            <div>
                <a id="download"></a>
                <a id="download_by_app" hidden="true" ></a>
                <a id="download_by_proxy" hidden="true" ></a>
            </div>
        </div>

        <div class="message-info hidden">
            <span id="message-info-WebRTC"    style="display: none;"><?= Yii::t('modules/download', 'not_support_WebRTC') ?></span>
            <span id="message-info-Websocket" style="display: none;"><?= Yii::t('modules/download', 'not_support_Websocket') ?></span>
        </div>
        <!--<hr align="center" width="100%" size="3" color="#dddddd" />-->

        <div class="share_state" id="state">
            <label id="label-state" style="display: none;"></label>
        </div>

    </div>

</div>




<!-- BEGIN .Modal #password-required-modal -->
<?php
Modal::begin([
    'options' => [
        'id' => 'password-required-modal',
    ],
    'clientOptions' => [
        'keyboard' => false,
        'backdrop' => 'static',
    ],
    //'closeButton' => ['id' => 'close-button-sl'],
    'closeButton' => false,
    'header' => '<div class="modal-title" style="padding-top: 20px; text-align: center;">' . Yii::t('modules/download', 'Password_required') . '</div>',
    'size' => '',
]);
?>

<div class="form-block">

        <div id="group-passrequired-password" class="form-group field-passrequired-password required">
            <input id="passrequired-password" class="form-control" placeholder="Enter password" autocomplete="off" aria-required="true" type="password">
            <p id="passrequired-blank-password" class="hide help-block help-block-error"><?= Yii::t('modules/download', 'Password_cant_be_blank') ?></p>
            <p id="passrequired-wrong-password" class="hide help-block help-block-error"><?= Yii::t('modules/download', 'Wrong_password') ?></p>
            <p id="passrequired-block-ip-tries" class="hide help-block help-block-error"><?= Yii::t('modules/download', 'Too_many_wrong_tries') ?></p>
        </div>

        <button type="submit" class="btn-big" name="GetAccess" onclick="checkPass()"><?= Yii::t('modules/download', 'button_OK') ?></button>
</div>

<?php
Modal::end();
?>
<!-- END .Modal #fileversions-modal -->
