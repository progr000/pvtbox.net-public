<?php

/** @var $this yii\web\View */
/** @var $dataProvider yii\data\ActiveDataProvider */
/** @var $model \common\models\UserNode */
/** @var $Server \common\models\Servers */
/** @var $User \common\models\Users */

use yii\widgets\ListView;
use yii\web\View;
use common\models\UserNode;
use common\models\Licenses;
use frontend\assets\v20190812\devicesAsset;

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
            ]);
    }
}
?>
<!-- begin Devices-page content -->
<div class="content container editor-area"
     id="wss-data"
     data-token="<?= $site_token ?>"
     data-wss-url="wss://<?= isset($Server[0]) ? $Server[0]->server_url : 'null' ?>/ws/webfm/<?= $site_token ?>?mode=node_info"
     data-wss-url-echo-test-server="ws://echo.websocket.org">


    <?php
    $minPageSize = 8;
    $count = $dataProvider->count;
    $lost = isset($dataProvider->pagination->pageSize)
        ? $dataProvider->pagination->pageSize - $count
        : $minPageSize - $count;
    ?>
    <?=
    ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions' => [
            'tag' => false,
            'class' => '',
        ],
        'layout' => '
            <table class="devices-tbl">
                <thead>
                    <tr>
                        <th></th>
                        <th class="-test-add-empty-tr">' . Yii::t('user/devices', 'Device_type') . '</th>
                        <th class="-test-add-node-tr">' . Yii::t('user/devices', 'Operating_system') . '</th>
                        <th>' . Yii::t('user/devices', 'Name') . '</th>
                        <th>' . Yii::t('user/devices', 'In_use') . '</th>
                        <th>' . Yii::t('user/devices', 'Status') . '</th>
                        <th>' . Yii::t('user/devices', 'Current_speed') . '</th>
                        <th>' . Yii::t('user/devices', 'Action') . '</th>
                    </tr>
                </thead>
                <tbody id="list-items-node" data-min-page-size="' . $minPageSize . '" style="display: none;">
                    {items}
                </tbody>
            </table>
            <span class="devices-note-gray">* Actual content + File versions</span>
            {pager}',
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


</div>
<!-- end Devices-page content -->

<!-- begin js-tpl-for-devices -->
<div id="small-loading" style="display: none;">
    <div class="small-loading">Loading <img class="loading" src="/themes/v20190812/images/loading_v4.gif" alt="loading..." /></div>
</div>

<div style="display: none">
    <span id="confirm-hide-node"><?= Yii::t('user/devices', 'confirm_hide_node') ?></span>
    <span id="confirm-hide-node-wiped"><?= Yii::t('user/devices', 'confirm_hide_node_wiped') ?></span>
</div>

<div id="tpl-item-node" style="display: none;">
    <table class="hidden">
        <tbody class="item-node-not-empty">
            <tr id="main-tr-node-{node_id}"
                class="item-node"
                data-node-id="{node_id}"
                data-node-status="{node_status_int}"
                data-node-logout-status="{node_logout_status}"
                data-node-wipe-status="{node_wipe_status}">
                <td>
                    <div id="node-{node_id}-online" class="lock {active}" title="{onlineLabel}">
                        <svg class="icon icon-lock">
                            <use xlink:href="#lock"></use>
                        </svg>
                    </div>
                </td>
                <td class="device-type-td">
                    <div class="browser masterTooltip"
                         title="{node_devicetype_label}">
                        <svg class="icon icon-device-{node_devicetype}">
                            <use xlink:href="#device-{node_devicetype}"></use>
                        </svg><span>{node_devicetype_label}</span>
                    </div>
                </td>
                <td class="device-os-type-td">
                    <div class="system masterTooltip"
                         title="{node_osname}">
                        <svg class="icon icon-system-{node_ostype_lower}">
                            <use xlink:href="#system-{node_ostype_lower}"></use>
                        </svg><span>{node_osname}</span>
                    </div>
                </td>
                <td class="device-node-name-td"><span
                        class="masterTooltip"
                        title="{node_name}">{node_name}</span>
                </td>
                <td class="device-disk-usage-td">
                    <span
                        class="table-color-gray"
                        id="node-{node_id}-disk-usage">{node_disk_usage}</span>
                </td>
                <td class="device-node-status-td">
                    <span
                        class="{node_status_html_class}"
                        id="node-{node_id}-status"
                        data-status="{node_status_int}">{node_status}</span>
                </td>
                <td class="device-node-speed-td">
                    <span
                        class="table-color-gray"
                        id="node-{node_id}-download-speed">{node_download_speed}</span>
                    <span class="table-color-gray"> / </span>
                    <span
                        class="table-color-gray"
                        id="node-{node_id}-upload-speed">{node_upload_speed}</span>
                </td>
                <td class="device-actions-buttons">
                    <a href="#"
                       class="manage-link show-node-log dashed-link"
                       data-node-id="{node_id}"><?= Yii::t('user/devices', 'Manage') ?></a>
                    <a href="#"
                       class="masterTooltip hide-node close hidden"
                       title="<?= Yii::t('user/devices', 'Hide_text') ?>"
                       data-node-id="{node_id}"
                       data-node-status="{node_status_int}"
                       data-node-wipe-status="{node_wipe_status}"><?= Yii::t('user/devices', 'Hide') ?></a>
                </td>
            </tr>
            <tr id="tr-node-log-{node_id}"
                data-node-id="{node_id}"
                class="hidden-tr item-node-log">
                <td colspan="8"></td>
            </tr>
        </tbody>
    </table>
    <table class="hidden">
        <?= $this->render('devices_list_item_empty') ?>
    </table>
</div>
<!-- end js-tpl-for-devices -->

