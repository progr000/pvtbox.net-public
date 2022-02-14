<?php

/** @var $browser string */
/** @var $status array */
/** @var $model \frontend\models\search\SessionsSearch */

use common\models\UserNode;
use common\models\RemoteActions;
use common\helpers\Functions;
use frontend\models\search\SessionsSearch;

$browser = Functions::getBrowserByUserAgent($model->sess_useragent);
if (!$browser) {
    $browser = $model->node_devicetype;
}
$status = SessionsSearch::getStatusSessAction($model->sess_action);
if ($model->node_devicetype == UserNode::DEVICE_BROWSER) {
    $status['text']  = 'Current';
    $status['class'] = 'table-color-darkBlue';
}
?>

<div class="table__body">
    <div class="table__body-box" style="padding-left: 10px; padding-right: 0px;">
        <div class="icon icon-browser-<?= $browser ?>-small" style="padding-left: 0px; padding-right: 0px; margin-left: 0px; margin-right: 0px;"></div>
        <div class="icon icon-<?= mb_strtolower($model->node_ostype) ?>-os" style="padding-left: 0px; padding-right: 0px; margin-left: 0px; margin-right: 0px;"></div>
        <span><?= $model->node_name ?></span>
    </div>
    <div class="table__body-box"><span style="color: #CCCCCC;"><?= $model->sess_country ?>, <?= $model->sess_city ?></span></div>
    <div class="table__body-box"><span><?= $model->sess_ip ?></span></div>
    <div class="table__body-box"><span style="color: #CCCCCC;"><?= $model->sess_created ?></span></div>
    <div class="table__body-box"><b class="<?= $status['class'] ?>"><?= $status['text'] ?></b><!--<b class="table-color-darkBlue">/Current</b>--></div>
    <div class="table__body-box">
        <?= (
        $model->node_devicetype == UserNode::DEVICE_BROWSER
            ? '<a class="remote-logout-button-' . $model->node_id. ' table-color-orange disabled" href="javascript:void(0)" onclick="return false;">Log out</a>'
            : (
                $model->node_logout_status != UserNode::LOGOUT_STATUS_READY_TO
                    ? '<a class="remote-logout-button-' . $model->node_id. ' table-color-orange disabled" href="javascript:void(0)" onclick="return false;">' . UserNode::logoutStatus($model->node_logout_status). '</a>'
                    : (
                        $model->node_wipe_status != UserNode::WIPE_STATUS_READY_TO
                            ? '<a class="remote-logout-button-' . $model->node_id. ' table-color-orange disabled" href="javascript:void(0)" onclick="return false;">' . UserNode::logoutStatus($model->node_logout_status). '</a>'
                            : '<a class="remote-logout-button-' . $model->node_id. ' table-color-orange" href="javascript:void(0)" onclick="actionExec(\'' . RemoteActions::TYPE_LOGOUT . '\', ' . $model->node_id . ')">' . UserNode::logoutStatus($model->node_logout_status). '</a>'
                    )
                )
            )
        ?>
    </div>
    <div class="table__body-box">
        <?= (
        $model->node_devicetype == UserNode::DEVICE_BROWSER
            ? '<a class="remote-wipe-button-' . $model->node_id. ' table-color-darkRed disabled" href="javascript:void(0)" onclick="return false;">Log out & Remote wipe*</a>'
            : (
                $model->node_wipe_status != UserNode::WIPE_STATUS_READY_TO
                    ? '<a class="remote-wipe-button-' . $model->node_id. ' table-color-darkRed disabled" href="javascript:void(0)" onclick="return false;">' . UserNode::wipeStatus($model->node_wipe_status). '</a>'
                    : '<a class="remote-wipe-button-' . $model->node_id. ' table-color-darkRed" href="javascript:void(0)" onclick="actionExec(\'' . RemoteActions::TYPE_WIPE . '\', ' . $model->node_id . ')">' . UserNode::wipeStatus($model->node_wipe_status). '</a>'
                )
            )
        ?>
    </div>
</div>
