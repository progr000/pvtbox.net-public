<?php

/** @var $model \frontend\models\search\UserNodeSearch */

use common\models\UserNode;
use common\helpers\Functions;

$system_array = [
    UserNode::OSTYPE_ANDROID => 'android',
    UserNode::OSTYPE_DARWIN  => 'darwin',
    UserNode::OSTYPE_IOS     => 'ios',
    UserNode::OSTYPE_LINUX   => 'unix',
    UserNode::OSTYPE_WINDOWS => 'windows',
    UserNode::OSTYPE_WEBFM   => 'webfm',
];

$devices_array = [
    UserNode::DEVICE_BROWSER => 'browser',
    UserNode::DEVICE_DESKTOP => 'desktop',
    UserNode::DEVICE_PHONE   => 'phone',
    UserNode::DEVICE_TABLET  => 'tablet',
];

if ($model->node_online == UserNode::ONLINE_OFF) {
    if (!in_array($model->node_status, [UserNode::STATUS_LOGGEDOUT, UserNode::STATUS_POWEROFF])) {
        //$model->node_status = UserNode::STATUS_POWEROFF;
    }
}

if ($model->node_wipe_status == UserNode::WIPE_STATUS_SUCCESS) {
    $model->node_status = UserNode::STATUS_WIPED;
}

$node_name = ($model->node_devicetype == UserNode::DEVICE_BROWSER)
    ? Yii::t('models/user-node', 'WEBFM_NODE_NAME')
    : $model->node_name;
?>
<tr id="main-tr-node-<?= $model->node_id ?>"
    class="item-node"
    data-node-id="<?= $model->node_id ?>"
    data-node-status="<?= $model->node_status ?>"
    data-node-logout-status="<?= $model->node_logout_status ?>"
    data-node-wipe-status="<?= $model->node_wipe_status ?>">
    <td>
        <div id="node-<?= $model->node_id ?>-online" class="masterTooltip lock <?= ($model->node_online ? "active" : "") ?>" title="<?= UserNode::onlineLabel($model->node_online) ?>">
            <svg class="icon icon-lock">
                <use xlink:href="#lock"></use>
            </svg>
        </div>
    </td>
    <td class="device-type-td">
        <div class="browser masterTooltip"
             title="<?= UserNode::deviceLabel($model->node_devicetype) ?>">
            <svg class="icon icon-device-<?= isset($devices_array[$model->node_devicetype]) ? $devices_array[$model->node_devicetype] : $model->node_devicetype ?>">
                <use xlink:href="#device-<?= isset($devices_array[$model->node_devicetype]) ? $devices_array[$model->node_devicetype] : $model->node_devicetype ?>"></use>
            </svg><span><?= UserNode::deviceLabel($model->node_devicetype) ?></span>
        </div>
    </td>
    <td class="device-os-type-td">
        <div class="system masterTooltip"
             title="<?= $model->node_osname ?>">
            <svg class="icon icon-system-<?= isset($system_array[$model->node_ostype]) ? $system_array[$model->node_ostype] : $model->node_ostype ?>">
                <use xlink:href="#system-<?= isset($system_array[$model->node_ostype]) ? $system_array[$model->node_ostype] : $model->node_ostype ?>"></use>
            </svg><span><?= Functions::concatString($model->node_osname, 15) ?></span>
        </div>
    </td>
    <td class="device-node-name-td"><span
            class="masterTooltip"
            title="<?= $node_name ?>"><?= Functions::concatString($node_name, 15) ?></span></td>
    <td class="device-disk-usage-td">
        <span
            class="table-color-gray"
            id="node-<?= $model->node_id ?>-disk-usage"><?= ($model->node_devicetype == UserNode::DEVICE_BROWSER) ? "-" : Functions::file_size_format($model->node_disk_usage, 1) ?></span>
    </td>
    <td class="device-node-status-td">
        <span
            class="<?= isset($statusesHtmlClass[$model->node_status]) ? $statusesHtmlClass[$model->node_status] : "table-color-orange" ?>"
            id="node-<?= $model->node_id ?>-status"
            data-status="<?= $model->node_status ?>"><?= ($model->node_devicetype == UserNode::DEVICE_BROWSER) ? "-" : UserNode::statusLabel($model->node_status) ?></span>
    </td>
    <td class="device-node-speed-td">
        <span
            class="table-color-gray"
            id="node-<?= $model->node_id ?>-download-speed"><?= ($model->node_devicetype == UserNode::DEVICE_BROWSER) ? "-" : Functions::file_size_format($model->node_download_speed, 0, 'KB') . "s" ?></span>
        <span class="table-color-gray"> / </span>
        <span
            class="table-color-gray"
            id="node-<?= $model->node_id ?>-upload-speed"><?= ($model->node_devicetype == UserNode::DEVICE_BROWSER) ? "-" : Functions::file_size_format($model->node_upload_speed, 0, 'KB') . "s" ?></span>
    </td>
    <td class="device-actions-buttons">
        <a href="#"
           class="manage-link show-node-log dashed-link"
           data-node-id="<?= $model->node_id ?>"><?= Yii::t('user/devices', 'Manage') ?></a>
        <a href="#"
           class="masterTooltip hide-node close <?= ($model->node_online ? "hidden" : "") ?>"
           title="<?= Yii::t('user/devices', 'Hide_text') ?>"
           data-node-id="<?= $model->node_id ?>"
           data-node-status="<?= $model->node_status ?>"
           data-node-wipe-status="<?= $model->node_wipe_status ?>"><?= Yii::t('user/devices', 'Hide') ?></a>
    </td>
</tr>
<tr id="tr-node-log-<?= $model->node_id ?>"
    data-node-id="<?= $model->node_id ?>"
    class="hidden-tr item-node-log">
    <td colspan="8"></td>
</tr>
