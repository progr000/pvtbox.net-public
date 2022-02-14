<?php

/** @var $model \frontend\models\search\UserNodeSearch */

use common\models\UserNode;
use common\helpers\Functions;

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

<div class="item-node node_devicetype-<?= mb_strtolower($model->node_devicetype) ?> node_ostype-<?= mb_strtolower($model->node_ostype) ?>" id="main-tr-node-<?= $model->node_id ?>" data-node-status="<?= $model->node_status ?>" data-node-wipe-status="<?= $model->node_wipe_status ?>">
    <div class="table__body" id="tr-node-<?= $model->node_id ?>" data-node-id="<?= $model->node_id ?>">
        <div class="table__body-box">
            <div class="icon icon-circle-lock <?= ($model->node_online ? "active" : "") ?>" id="node-<?= $model->node_id ?>-online" title="<?= UserNode::onlineLabel($model->node_online) ?>">
                <!--
                <svg style="width:15px;height:15px" viewBox="0 0 24 24">
                    <path fill="#000000" d="M12,17A2,2 0 0,0 14,15C14,13.89 13.1,13 12,13A2,2 0 0,0 10,15A2,2 0 0,0 12,17M18,8A2,2 0 0,1 20,10V20A2,2 0 0,1 18,22H6A2,2 0 0,1 4,20V10C4,8.89 4.9,8 6,8H7V6A5,5 0 0,1 12,1A5,5 0 0,1 17,6V8H18M12,3A3,3 0 0,0 9,6V8H15V6A3,3 0 0,0 12,3Z" />
                </svg>
                -->
            </div></div>
        <div class="table__body-box"><div class="icon icon-<?= $model->node_devicetype ?>"></div><span><?= UserNode::deviceLabel($model->node_devicetype) ?></span></div>
        <div class="table__body-box device-os-type-td"><div class="icon icon-<?= mb_strtolower($model->node_ostype) ?>-os"></div><span class="masterTooltip" title="<?= $model->node_osname ?>"><?= Functions::concatString($model->node_osname, 15) ?></span></div>
        <div class="table__body-box devices-node-name-td"><span class="masterTooltip" title="<?= $node_name ?>"><?= Functions::concatString($node_name, 32) ?></span></div>
        <div class="table__body-box"><span class="table-color-gray" id="node-<?= $model->node_id ?>-disk-usage"><?= ($model->node_devicetype == UserNode::DEVICE_BROWSER) ? "-" : Functions::file_size_format($model->node_disk_usage, 1) ?></span></div>
        <div class="table__body-box"><span class="<?= isset($statusesHtmlClass[$model->node_status]) ? $statusesHtmlClass[$model->node_status] : "table-color-orange" ?>" id="node-<?= $model->node_id ?>-status" data-status="<?= $model->node_status ?>"><?= ($model->node_devicetype == UserNode::DEVICE_BROWSER) ? "-" : UserNode::statusLabel($model->node_status) ?></span></div>
        <div class="table__body-box">
            <span class="table-color-gray" id="node-<?= $model->node_id ?>-download-speed"><?= ($model->node_devicetype == UserNode::DEVICE_BROWSER) ? "-" : Functions::file_size_format($model->node_download_speed, 0, 'KB') . "s" ?></span>
            <span class="table-color-gray"> / </span>
            <span class="table-color-gray" id="node-<?= $model->node_id ?>-upload-speed"><?= ($model->node_devicetype == UserNode::DEVICE_BROWSER) ? "-" : Functions::file_size_format($model->node_upload_speed, 0, 'KB') . "s" ?></span>
        </div>
        <div class="table__body-box">
            <span>
                <a href="javascript:void(0)" class="show-node-log" data-node-id="<?= $model->node_id ?>"><?= Yii::t('user/devices', 'Manage') ?></a>
                &nbsp;
                <a href="javascript:void(0)" class="hide-node <?= ($model->node_online ? "hide" : "") ?>" data-node-id="<?= $model->node_id ?>" data-node-status="<?= $model->node_status ?>" data-node-wipe-status="<?= $model->node_wipe_status ?>"><?= Yii::t('user/devices', 'Hide') ?></a>
            </span>
        </div>
    </div>
    <div class="item-node-log" id="tr-node-log-<?= $model->node_id ?>">
    </div>
</div>