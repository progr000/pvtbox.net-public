<?php
/** @var $this yii\web\View */
/** @var $conference_name string */
/** @var $conference_id integer */
/** @var $conference_guest_hash string */
/** @var $conference_guest_link string */
/** @var $users_ids array */
/** @var $user_hash string */
/** @var $room_uuid string */
/** @var $ParticipantAddForm \frontend\models\forms\ParticipantAddForm */

use yii\helpers\Url;
use frontend\assets\v20190812\conferenceRoomAsset;
use common\models\UserConferences;

/* assets */
conferenceRoomAsset::register($this);

/* */
$this->title = Yii::t('user/conferences', 'title');

$DEFAULT_VIEW_MODE = UserConferences::VIEW_GALLERY;
$CURRENT_VIEW_MODE = Yii::$app->session->get('conference_view_mode', $DEFAULT_VIEW_MODE);
?>

<!-- begin conference-room-page content -->
<div class="content container noShowBalloon"
     id="wss-data"
     data-token="<?= ''/*$site_token*/ ?>"
     data-off-wss-url="wss://<?= ''/*isset($Server[0]) ? $Server[0]->server_url : 'null' ?>/ws/webfm/<?= $site_token*/ ?>"
     data-wss-url="wss://signalserver.pvtdev.net:4483"
     data-user-hash="<?= $user_hash ?>"
     data-room-uuid="<?= $room_uuid ?>">


    <div id="system-control" style="display: none;">
        <div id="local-control">
        <div id="join-control">
            <button id="join-button" onclick="Client.joinRoom()">
                join room
            </button>
            <span class="arrow"> &#x21E2; </span>
        </div>

        <div id="camera-control">
            <button id="send-camera" onclick="Client.sendCameraStreams()">
                send camera streams
            </button>
            <button id="stop-streams" onclick="Client.stopStreams()">
                stop streams
            </button>
            <span id="camera-info"></span>
            <button id="share-screen" onclick="Client.startScreenshare()">
                share screen
            </button>
            <div id="outgoing-cam-streams-ctrl">
                <div><input id="local-cam-checkbox" type="checkbox" checked
                            onchange="Client.changeCamPaused()" />
                    <label id="local-cam-label">camera</label>
                    <span id="camera-producer-stats" class="track-ctrl"></span>
                </div>
                <div><input id="local-mic-checkbox" type="checkbox" checked
                            onchange="Client.changeMicPaused()" />
                    <label id="local-mic-label">mic</label></div>
                <div id="local-screen-pause-ctrl">
                    <input id="local-screen-checkbox" type="checkbox" checked
                           onchange="Client.changeScreenPaused()" />
                    <label id="local-screen-label">screen</label>
                    <span id="screen-producer-stats" class="track-ctrl"></span>
                </div>
                <div id="local-screen-audio-pause-ctrl">
                    <input id="local-screen-audio-checkbox" type="checkbox" checked
                           onchange="Client.changeScreenAudioPaused()" />
                    <label id="local-screen-audio-label">screen audio</label>
                    <span id="screen-audio-producer-stats" class="track-ctrl"></span>
                </div>
            </div>
        </div>


        <button id="leave-room" onclick="Client.leaveRoom()">
            leave room
        </button>

        <div>
            <input id="auto-set-layers-checkbox" type="checkbox" checked
                   onchange="Client.changeAutoSetLayers()" />
            <label id="auto-set-layers-label">AutoSet Layers</label>
        </div>

        <div>
            <input id="auto-subscribe-checkbox" type="checkbox" checked
                   onchange="Client.changeAutoSubscribe()" />
            <label id="auto-subscribe-label">AutoSubscribe</label>
        </div>

    </div>
        <div id="available-tracks"></div>
        <div id="remote-audio"></div>
    </div>


    <div id="main-conference-container-div" class="main-conference-container">

        <!-- conference name -->
        <div class="conference-name delta-height-div">
            <?= $conference_name ?>
            <a href="<?= Url::to(['user/conferences'], CREATE_ABSOLUTE_URL) ?>"
               class="masterTooltip svg-icons exit-room on" style="float: right"
               title="<?= Yii::t('app/common', 'Exit') ?>">&nbsp;</a>
        </div>

        <?php
        if ($CURRENT_VIEW_MODE == UserConferences::VIEW_SINGLE) {
            echo $this->render('conference_room_single');
        } else {
            echo $this->render('conference_room_gallery');
        }
        ?>

        <!-- main control panel -->
        <div id="owner-stream-controls" class="conference-control delta-height-div">
            <div class="control-left">

                <a href="#"
                   id="voice-device"
                   class="void-0 masterTooltip svg-icons voice-status on"
                   data-cookie-name="cookie_voice_status"
                   data-on="1"
                   title="<?= Yii::t('user/conferences', 'Mute') ?>"
                   data-title-off="<?= Yii::t('user/conferences', 'Unmute') ?>"
                   data-title-on="<?= Yii::t('user/conferences', 'Mute') ?>">&nbsp;</a>

                <a href="#"
                   id="video-device"
                   class="void-0 masterTooltip svg-icons video-status on"
                   data-cookie-name="cookie_video_status"
                   data-on="1"
                   title="<?= Yii::t('user/conferences', 'Stop_Video') ?>"
                   data-title-off="<?= Yii::t('user/conferences', 'Start_Video') ?>"
                   data-title-on="<?= Yii::t('user/conferences', 'Stop_Video') ?>">&nbsp;</a>

                <a href="#"
                   id="share-screen-btn"
                   class="void-0 only-for-desktop masterTooltip svg-icons share-screen on"
                   title="<?= Yii::t('user/conferences', 'Share_screen') ?>"
                   data-title-on="<?= Yii::t('user/conferences', 'Share_screen') ?>"
                   data-title-off="<?= Yii::t('user/conferences', 'Screen_already_shared') ?>">&nbsp;</a>

                <a href="#"
                   class="void-0 masterTooltip svg-icons full-screen off"
                   title="<?= Yii::t('user/conferences', 'Full_screen_on') ?>"
                   data-title-on="<?= Yii::t('user/conferences', 'Full_screen_off') ?>"
                   data-title-off="<?= Yii::t('user/conferences', 'Full_screen_on') ?>">&nbsp;</a>

                <?php if ($CURRENT_VIEW_MODE == UserConferences::VIEW_SINGLE) { ?>
                <a href="<?= Url::to(['conferences/open-conference', 'conference_id' => $conference_id, 'view' => 'gallery'], CREATE_ABSOLUTE_URL) ?>"
                   class="masterTooltip svg-icons gallery-mode-link on"
                   title="<?= Yii::t('user/conferences', 'Gallery_mode') ?>">&nbsp;</a>
                <?php } else { ?>
                <a href="<?= Url::to(['conferences/open-conference', 'conference_id' => $conference_id, 'view' => 'single'], CREATE_ABSOLUTE_URL) ?>"
                   class="masterTooltip svg-icons single-mode-link on"
                   title="<?= Yii::t('user/conferences', 'Single_mode') ?>">&nbsp;</a>
                <?php } ?>
            </div>

            <div class="control-right">
                <a href="#"
                   class="conference-manage-guest-link masterTooltip void-0 svg-icons on"
                   title="<?= Yii::t('user/conferences', 'Guest_link') ?>"
                   data-room-uuid=""
                   data-conference-guest-link="<?= $conference_guest_link ?>"
                   data-conference-guest-hash="<?= $conference_guest_hash ?>"
                   data-conference-name="<?= $conference_name ?>"
                   data-conference-id="<?= $conference_id ?>">&nbsp;</a>
            </div>
        </div>

    </div>

</div>
<!-- end conference-room-page content -->

<!-- begin MODALS -->
<?= $this->render('conferences_modal', [
    'ParticipantAddForm' => $ParticipantAddForm,
]) ?>
<!-- end MODALS -->