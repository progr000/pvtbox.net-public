<?php
/* @var $model array */

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

$node_name = ($model['node_devicetype'] == UserNode::DEVICE_BROWSER)
    ? Yii::t('models/user-node', 'WEBFM_NODE_NAME')
    : $model['node_name'];
?>
<tr id="row-node-id-<?= $model['node_id'] ?>">
    <td>
        <div id="node-<?= $model['node_id'] ?>-online" class="masterTooltip lock <?= ($model['node_online'] ? "active" : "") ?>" title="<?= UserNode::onlineLabel($model['node_online']) ?>">
            <svg class="icon icon-lock">
                <use xlink:href="#lock"></use>
            </svg>
        </div>
    </td>
    <td class="device-type-td">
        <div class="browser masterTooltip"
             title="<?= UserNode::deviceLabel($model['node_devicetype']) ?>">
            <svg class="icon icon-device-<?= isset($devices_array[$model['node_devicetype']]) ? $devices_array[$model['node_devicetype']] : $model['node_devicetype'] ?>">
                <use xlink:href="#device-<?= isset($devices_array[$model['node_devicetype']]) ? $devices_array[$model['node_devicetype']] : $model['node_devicetype'] ?>"></use>
            </svg><span><?= UserNode::deviceLabel($model['node_devicetype']) ?></span>
        </div>
    </td>
    <td class="device-os-type-td">
        <div class="system masterTooltip"
             title="<?= $model['node_osname'] ?>">
            <svg class="icon icon-system-<?= isset($system_array[$model['node_ostype']]) ? $system_array[$model['node_ostype']] : $model['node_ostype'] ?>">
                <use xlink:href="#system-<?= isset($system_array[$model['node_ostype']]) ? $system_array[$model['node_ostype']] : $model['node_ostype'] ?>"></use>
            </svg><span><?= Functions::concatString($model['node_osname'], 20) ?></span>
        </div>
    </td>
    <td class="device-node-name-td"><span
            class="masterTooltip"
            title="<?= $node_name ?>"><?= Functions::concatString($node_name, 30) ?></span></td>
    <td><span class="masterTooltip" title="<?= $model['user_email'] ?>"><?= $model['user_email'] ?></span></td>
    <td><?= $model['lic_srv_id'] ? Yii::t('user/devices', 'ServerNodeLicensed') : Yii::t('user/devices', 'ServerNodeNotLicensed') ?></td>
    <td class="device-actions-buttons">

            <?php
            if ($model['lic_srv_id']) {
                ?>
                <a href="#"
                   class="release-server-license hide-node close -masterTooltip"
                   data-title="<?= Yii::t('user/devices', 'ConfirmServerNodeLicensed') ?>"
                   data-confirm-text="<?= Yii::t('user/devices', 'ConfirmServerNodeLicensed') ?>"
                   data-node-id="<?= $model['node_id'] ?>"><?= Yii::t('user/devices', 'Release_license') ?></a>
                <?php
            } else {
                ?>
                <a href="#"
                   class="release-server-license hide-node close -masterTooltip"
                   data-title="<?= Yii::t('user/devices', 'ConfirmServerNodeNotLicensed') ?>"
                   data-confirm-text="<?= Yii::t('user/devices', 'ConfirmServerNodeNotLicensed') ?>"
                   data-node-id="<?= $model['node_id'] ?>"><?= Yii::t('user/devices', 'Remove_from_list') ?></a>
                <?php
            }
            ?>

    </td>
</tr>
