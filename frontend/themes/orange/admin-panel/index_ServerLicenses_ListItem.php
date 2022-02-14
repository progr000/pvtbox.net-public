<?php
/* @var $model array */
//var_dump($model); exit;

use common\models\UserNode;
use common\helpers\Functions;

$node_name = ($model['node_devicetype'] == UserNode::DEVICE_BROWSER)
    ? Yii::t('models/user-node', 'WEBFM_NODE_NAME')
    : $model['node_name'];
?>

<div class="table__body" id="row-node-id-<?= $model['node_id'] ?>">
    <div class="table__body-box">
        <div class="icon icon-circle-lock <?= ($model['node_online'] ? "active" : "") ?>"
             id="node-<?= $model['node_id'] ?>-online"
             title="<?= UserNode::onlineLabel($model['node_online']) ?>"></div>
    </div>
    <div class="table__body-box">
        <div class="icon icon-<?= $model['node_devicetype'] ?>"></div>
        <span><?= UserNode::deviceLabel($model['node_devicetype']) ?></span>
    </div>
    <div class="table__body-box">
        <div class="icon icon-<?= mb_strtolower($model['node_ostype']) ?>-os"></div>
        <span class="masterTooltip" title="<?= $model['node_osname'] ?>"><?= Functions::concatString($model['node_osname'], 15) ?></span>
    </div>
    <div class="table__body-box">
        <span class="masterTooltip" title="<?= $node_name ?>"><?= Functions::concatString($node_name, 20) ?></span>
    </div>
    <div class="table__body-box">
        <span class="masterTooltip" title="<?= $model['user_email'] ?>"><?= $model['user_email'] ?></span>
    </div>
    <div class="table__body-box">
        <?= $model['lic_srv_id'] ? Yii::t('user/devices', 'ServerNodeLicensed') : Yii::t('user/devices', 'ServerNodeNotLicensed') ?>
    </div>
    <div class="table__body-box">
            <span>
                <?php
                if ($model['lic_srv_id']) {
                    ?>
                    <a href="javascript:void(0)" class="release-server-license" data-confirm-text="<?= Yii::t('user/devices', 'ConfirmServerNodeLicensed') ?>"
                       data-node-id="<?= $model['node_id'] ?>"><?= Yii::t('user/devices', 'Release_license') ?></a>
                    <?php
                } else {
                    ?>
                    <a href="javascript:void(0)" class="release-server-license" data-confirm-text="<?= Yii::t('user/devices', 'ConfirmServerNodeNotLicensed') ?>"
                       data-node-id="<?= $model['node_id'] ?>"><?= Yii::t('user/devices', 'Remove_from_list') ?></a>
                    <?php
                }
                ?>
            </span>
    </div>
</div>

