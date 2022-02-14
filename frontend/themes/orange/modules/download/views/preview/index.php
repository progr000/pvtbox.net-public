<?php
/* @var $this \yii\web\View */
/* @var $Server_Sign \common\models\Servers */
/* @var $Server_Stun \common\models\Servers */
/* @var $Server_Proxy \common\models\Servers */
/* @var $site_token string */
/* @var $UserFile \common\models\UserFiles */
/* @var $lastEvent \common\models\UserFileEvents */
/* @var $encoded_file_name string */

use frontend\assets\orange\modPreviewAsset;
use frontend\assets\orange\modPreviewDownloadAsset;
use frontend\assets\orange\MainCssAsset;

/* assets */
MainCssAsset::register($this);
if (!isset($_GET['download'])) {
    modPreviewAsset::register($this);
} else {
    modPreviewDownloadAsset::register($this);
}

$encoded_file_name = urlencode($UserFile->file_name);

$this->title = Yii::t('modules/download', 'title_preview', ['file_name' => $UserFile->file_name]);
?>

<!--
<div class="features">

    <div class="features__cont">
-->
        <!--<div class="title"><h2>Preview file</h2></div>-->


        <input type="hidden" name="file_name" value="<?= $UserFile->file_name ?>" />
        <input type="hidden" name="file_size" value="<?= $UserFile->file_size ?>" />
        <input type="hidden" name="event_uuid" value="<?= $lastEvent->event_uuid ?>" />


        <style type="text/css">
            body {
                text-align: center;
                text-align: -moz-center;
                text-align: -webkit-center;
                overflow: hidden;
            }
            .main{ clear: both; overflow: hidden; margin: 3px; }
            .fileinfo{ float: left; width: 100%; height: auto; }
            .controls{ float: left; width: 100%; height: auto; }
            .state{ width: 100%; }
            .hidden { display: none; }
            .media_ {
                text-align: center;
                text-align: -moz-center;
                text-align: -webkit-center;
                overflow-x: auto;
                overflow-y: auto;
                /*height: 500px;*/
            }
            .media_ pre {
                /*width: -moz-min-content !important;*/
                text-align: left !important;
            }
            /*
            .header{ width: 100%; }
            .right { float: right; }
            #progress {width: 70%;  }
            #img_ { display: block; width: 96%; margin-left: auto; margin-right: auto; }
            #pre_ { word-wrap: break-word; white-space: pre-wrap; border:#a0a0c0 1px solid; float: left; width: 99%; }
            #video_ { width: 100%; }
            #canvas_ { width: 100%; }
            .canvas_ { width: 100%; }
            .canvas_ { width: auto; width: 100%; }
            */
        </style>

        <!--
        <div class="header" id="header">
            <a href="#" class="right" id="a_refresh" onclick="window.location.reload(true);">Reload</a>
        </div>
        -->

        <div class="main" id="main">
            <div class="fileinfo" style="display: none;">
                <table class="file_" id="file_">
                    <tr><td><?= Yii::t('modules/download', 'File_name') ?></td><td id="file_name"><?= $UserFile->file_name ?></td></tr>
                    <tr><td><?= Yii::t('modules/download', 'File_size') ?></td><td id="file_size"><?= $UserFile->file_size ?></td></tr>
                    <tr class="hidden"><td><?= Yii::t('modules/download', 'Event_UUID') ?></td><td id="event_uuid"><?= $lastEvent->event_uuid ?></td></tr>
                    <tr><td><?= Yii::t('modules/download', 'Online_nodes') ?></td><td id="online_nodes"></td></tr>
                </table>
                <input id="stun_server_url" type="hidden" value="<?= $Server_Stun[0]->server_url ?>">
                <input id="sig_server_url" type="hidden" value="wss://<?= $Server_Sign[0]->server_url ?>/ws/webfm/<?= $site_token ?>?mode=get_file">
                <input id="proxy_node_url" type="hidden" value="<?= $Server_Proxy[0]->server_url ?>token/<?= $site_token ?>?file_name=<?= $encoded_file_name ?>&file_size=<?= $UserFile->file_size ?>&event_uuid=<?= $lastEvent->event_uuid ?>">
                <?php
                $fileNameJs = \Yii::getAlias('@webroot') . '/themes/orange/js/get_file_main.min.js';
                if (file_exists($fileNameJs)) {
                    $appendTimestamp = '?v=' . filemtime($fileNameJs);
                } else {
                    $appendTimestamp = '';
                }
                ?>
                <select id="scripts" class="hidden">
                    <!--<option hidden value="/themes/orange/js/--------get_file_main.min.js<?= $appendTimestamp ?>"></option>-->
                </select>
            </div>
            <!-- <hr align="center" width="100%" size="3" color="#dddddd" /> -->
            <div class="media_" id="media_">
                <div id="preview-loading" style="display: none; text-align: center;">
                    <div class="big-loading-img" title="loading..."></div>
                    <span style="color: #aaaaaa; font-weight: 600; font-size: 14px;">
                        <?= Yii::t('modules/download', 'Loading_fetching') ?>
                    </span>
                </div>
                <div id="download-loading" style="display: none; text-align: center;">
                    <!-- <div class="big-loading-img" title="loading..."></div> -->
                    <br />
                    <span style="color: #aaaaaa; font-weight: 600; font-size: 14px;">
                        <?= Yii::t('modules/download', 'Downloading_fetching') ?>
                    </span>
                </div>
            </div>
            <!-- <hr align="center" width="100%" size="3" color="#dddddd" /> -->

            <div class="media_" id="info-and-controls">
                <div id="preview-fail-download-start" style="display: none;">
                    <div id="preview-loading-" style="text-align: center;">
                        <div class="big-loading-img" title="loading..."></div>
                        <span style="color: #aaaaaa; font-weight: 600; font-size: 14px;">
                            <?= Yii::t('modules/download', 'Preview_fail_download_start') ?>
                        </span>
                    </div>
                </div>

                <div id="btn-controls" class="controls" style="display: none; text-align: center;">
                    <p id="p_progress" class="hidden">
                        <div class="progress progress-striped active hidden" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="margin-bottom: 0px; height: 10px;">
                            <div id="progress_" class="progress-bar progress-bar-success" style="width: 0%;"></div>
                        </div>
                    </p>
                    <p id="p_result" class="hidden">
                        <label id="label_result"></label>
                    </p>
                    <div class="noPreview">
                        <?= Yii::t('modules/download', 'Cant_previewed') ?>
                    </div>
                    <p>
                        <a class="btn-default" id="btn_download" onclick="" style="display: none;"><?= Yii::t('modules/download', 'Download_by_proxy') ?></a>
                        <a class="btn-default" id="download-from-node" ><?= Yii::t('modules/download', 'Download_by_p2p') ?></a>
                        <!--
                        <button class="btn-default" id="btn_preview" onclick="" > <?= Yii::t('modules/download', 'Preview_by_p2p') ?> </button>
                        <button class="hidden" id="btn_preview_by_proxy" onclick="" > <?= Yii::t('modules/download', 'Preview_by_proxy') ?> </button>
                        -->
                    </p>
                </div>
            </div>

            <div style="display: none;">

                <a id="download_by_proxy_node" hidden="true" ></a>
            </div>
        </div>

        <div class="message-info" style="display: none;">
            <span id="message-info-WebRTC"    style="display: none;"><?= Yii::t('modules/download', 'not_support_WebRTC') ?></span>
            <span id="message-info-Websocket" style="display: none;"><?= Yii::t('modules/download', 'not_support_Websocket') ?></span>
        </div>

        <!-- <hr align="center" width="100%" size="3" color="#dddddd" /> -->

        <div class="state" id="state" style="display: none;">
            <label id="label_state"></label>
        </div>

<!--
    </div>

</div>
-->