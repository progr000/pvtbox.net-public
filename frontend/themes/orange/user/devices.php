<?php

/** @var $this yii\web\View */
/** @var $dataProvider yii\data\ActiveDataProvider */
/** @var $model \common\models\UserNode */
/** @var $Server \common\models\Servers */
/** @var $User \common\models\Users */

//https://esimakin.github.io/twbs-pagination/#options-and-events

use yii\widgets\ListView;
use yii\widgets\Pjax;
use yii\web\View;
use common\models\UserNode;
use common\models\Licenses;
use frontend\assets\orange\devicesAsset;

/*
echo '<pre>';
$ua = Yii::$app->request->getUserAgent();
$ua = "Mozilla/5.0 (iPhone; CPU iPhone OS 11_4_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/11.0 Mobile/15E148 Safari/604.1";
var_dump($ua);
var_dump(\common\helpers\Functions::clientDetection($ua));
exit;
*/

/* assets */
devicesAsset::register($this);

/* */
$this->title = Yii::t('user/devices', 'title');
$statusesHtmlClass = [
    UserNode::STATUS_DEACTIVATED => "table-color-gray",
    UserNode::STATUS_ACTIVE      => "table-color-gray",
    UserNode::STATUS_DELETED     => "table-color-gray",
    UserNode::STATUS_SYNCING     => "table-color-orange",
    UserNode::STATUS_SYNCED      => "table-color-green",
    UserNode::STATUS_LOGGEDOUT   => "table-color-gray",
    UserNode::STATUS_WIPED       => "table-color-gray",
    UserNode::STATUS_POWEROFF    => "table-color-gray",
    UserNode::STATUS_PAUSED      => "table-color-gray",
    UserNode::STATUS_INDEXING    => "table-color-orange",
];
/* Генерация яваскриптовых объектов для отображения статусов */
/* статусы состояния нод */
$statuses = UserNode::statusLabels();
$str = "var countRows = 10;\n\n";
$str .= "var statuses = {\n";
foreach ($statuses as $k=>$v) {
    $str .= $k . ':"' . $v . '",'. "\n";
}
$str .= "};\n\n";

/* Цвета для статусов нод */
$str .= "var statusesHtmlClass = {\n";
foreach ($statusesHtmlClass as $k=>$v) {
    $str .= $k . ':"' . $v . '",'. "\n";
}
$str .= "};\n\n";

/* статусы логаута нод */
$logoutStatuses = UserNode::logoutStatuses();
$str .= "var logoutStatuses = {\n";
foreach ($logoutStatuses as $k=>$v) {
    $str .= $k . ':"' . $v . '",'. "\n";
}
$str .= "};\n\n";

/* статусы вайпа нод */
$wipeStatuses = UserNode::wipeStatuses();
$str .= "var wipeStatuses = {\n";
foreach ($wipeStatuses as $k=>$v) {
    $str .= $k . ':"' . $v . '",'. "\n";
}
$str .= "};\n\n";

/* статусы онлайн-офлайн нод */
$onlineLabels = UserNode::onlineLabels();
$str .= "var onlineLabels = {\n";
foreach ($onlineLabels as $k=>$v) {
    $str .= $k . ':"' . $v . '",'. "\n";
}
$str .= "};\n\n";

//$str .= "var nodesList = " . $allNodes;


/* devicesLabels нод */
$devicesLabels = UserNode::devicesLabels();
$str .= "var devicesLabels = {\n";
foreach ($devicesLabels as $k=>$v) {
    $str .= $k . ':"' . $v . '",'. "\n";
}
$str .= "};\n\n";

/* регистрируем яваскрипт */
$this->registerJs($str, View::POS_END);
?>

<?php
if (Yii::$app->session->get('alert_sync_devices_restriction', true)) {
    if ($User->license_type == Licenses::TYPE_FREE_DEFAULT) {
            Yii::$app->session->setFlash('alert_sync_devices_restriction', [
                'message' => Yii::t('app/flash-messages', 'Sync_Devices_restriction'),
                'ttl' => 0,
                'showClose' => true,
                'alert_id' => 'alert-sync-devices-restriction',
                'type' => 'error',
                //'alert_action' => 'alert_sync_devices_restriction',
                //'class' => 'alert-error',
                //'auto_close_callback' => 'alert(1)',
            ]);
    }
}
?>

<!-- .tables -->
<!--
<a href="javascript:void(0)" onclick="ws.send('{d:1}')">Send</a>
<a href="javascript:void(0)" onclick="ws.close()">Close</a>
-->
<div class="tables tables--devices">

    <div class="tables__cont">

        <div class="table table--devices" id="notif-show-for" data-show-for="-for-devices">

            <div style="display: none" id="SignUrl" data-token="<?= $site_token ?>">wss://<?= $Server[0]->server_url ?>/ws/webfm/<?= $site_token ?>?mode=node_info</div>
            <div style="display: none" id="SignUrl_">ws://echo.websocket.org</div>

            <div class="table__head-cont">

                <div class="table__head">
                    <div class="table__head-box"></div>
                    <div class="table__head-box"><span><?= Yii::t('user/devices', 'Device_type') ?></span></div>
                    <div class="table__head-box"><span><?= Yii::t('user/devices', 'Operating_system') ?></span></div>
                    <div class="table__head-box"><span><?= Yii::t('user/devices', 'Name') ?></span></div>
                    <div class="table__head-box"><span><?= Yii::t('user/devices', 'In_use') ?></span></div>
                    <div class="table__head-box"><span><?= Yii::t('user/devices', 'Status') ?></span></div>
                    <div class="table__head-box"><span><?= Yii::t('user/devices', 'Current_speed') ?></span></div>
                    <div class="table__head-box"><span><?= Yii::t('user/devices', 'Action') ?></span></div>
                </div>

            </div>


            <?php Pjax::begin(); ?>
            <?php
            $minPageSize = 8;
            $count = $dataProvider->count;
            $lost = isset($dataProvider->pagination->pageSize) ? $dataProvider->pagination->pageSize - $count : $minPageSize - $count;
            ?>
            <?=
            ListView::widget([
                'dataProvider' => $dataProvider,
                //'itemOptions' => ['class' => 'item'],
                'itemOptions' => [
                    'tag' => false,
                    'class' => '',
                ],
                'layout' => '<div class="scrollbar-box"><div class="table__body-cont" id="list-items-node" data-min-page-size="' . $minPageSize . '" style="display: none;">' . "{items}" . '</div></div>' . "\n{pager}",
                'emptyText' => $this->render('devices_list_nodata'),
                'emptyTextOptions' => ['tag' => false],
                //'layout' => "{pager}\n{summary}\n{items}\n{pager}",
                //'summary' => 'Показано {count} из {totalCount}',
                'itemView' => function ($model, $key, $index, $widget) use ($lost, $count, $statusesHtmlClass) {
                    $lost_row = '';
                    if ($lost>0 && ($index == $count - 1)) {
                        for ($i=1; $i<=$lost; $i++) {
                            $lost_row .= $this->render('devices_list_item_empty');
                        }
                    }
                    /** @var $model \frontend\models\search\UserNodeSearch */
                    return $this->render('devices_list_item', ['model' => $model, 'statusesHtmlClass' => $statusesHtmlClass]) . $lost_row;
                },
            ]);
            ?>
            <?php Pjax::end(); ?>


        </div>
        <span class="table-color-gray">* Actual content + File versions</span>

    </div>

</div>
<div id="small-loading" style="display: none;">
    <div style="width: 100%; height: 160px; text-align: center; padding-top: 70px;">
        <div class="small-loading">Loading <img class="loading" src="/themes/orange/images/loading_v4.gif" alt="loading..." /></div>
    </div>
</div>
<div style="display: none">
    <span id="confirm-hide-node"><?= Yii::t('user/devices', 'confirm_hide_node') ?></span>
    <span id="confirm-hide-node-wiped"><?= Yii::t('user/devices', 'confirm_hide_node_wiped') ?></span>
</div>
<div id="tpl-item-node" style="display: none;">
    <div class="item-node-not-empty">
        <div class="item-node" id="main-tr-node-{node_id}" data-node-status="{node_status_int}">
            <div class="table__body" id="tr-node-{node_id}" data-node-id="{node_id}">
                <div class="table__body-box"><div class="icon icon-circle-lock {active}" id="node-{node_id}-online" title="{onlineLabel}"></div></div>
                <div class="table__body-box"><div class="icon icon-{node_devicetype}"><span style="padding-left: 30px;">{node_devicetype_label}</span></div></div>
                <div class="table__body-box"><div class="icon icon-{node_ostype_lower}-os"></div><span>{node_osname}</span></div>
                <div class="table__body-box"><span>{node_name}</span></div>
                <div class="table__body-box"><span class="table-color-gray" id="node-{node_id}-disk-usage">{node_disk_usage}</span></div>
                <div class="table__body-box"><span class="{node_status_html_class}" id="node-{node_id}-status">{node_status}</span></div>
                <div class="table__body-box">
                    <span class="table-color-gray" id="node-{node_id}-upload-speed">{node_upload_speed}</span>
                    <span class="table-color-gray"> / </span>
                    <span class="table-color-gray" id="node-{node_id}-download-speed">{node_download_speed}</span>
                </div>
                <div class="table__body-box">
                    <span>
                        <a href="javascript:void(0)" class="show-node-log" data-node-id="{node_id}"><?= Yii::t('user/devices', 'Manage') ?></a>
                        &nbsp;
                        <a href="javascript:void(0)" class="hide-node hide" data-node-id="{node_id}" data-node-status="{node_status_int}"><?= Yii::t('user/devices', 'Hide') ?></a>
                    </span>
                </div>
            </div>
            <div class="item-node-log" id="tr-node-log-{node_id}">
            </div>
        </div>
    </div>

    <div class="item-node item-node-empty">
        <div class="table__body">
            <div class="table__body-box"></div>
            <div class="table__body-box"></div>
            <div class="table__body-box"><span></span></div>
            <div class="table__body-box"><span></span></div>
            <div class="table__body-box"><span></span></div>
            <div class="table__body-box"><span></span></div>
            <div class="table__body-box"><span></span></div>
            <div class="table__body-box"><span></span></div>
        </div>
        <div class="item-node-log">
        </div>
    </div>
</div>
<!-- END .tables -->

