<?php

/** @var $browser string */
/** @var $status array */
/** @var $SessionsSearch \frontend\models\search\SessionsSearch */
/** @var $UserNode \common\models\UserNode */
/** @var $User \common\models\Users */

use yii\widgets\ListView;
use yii\helpers\Url;
use common\models\UserNode;
use common\models\RemoteActions;
use common\models\Licenses;
use common\helpers\Functions;
use frontend\models\search\SessionsSearch;

?>

<div class="table table--settings-small">
    <div class="table__head-cont">
        <div class="table__head-small">
            <div class="table__head-box-small"><span><?= Yii::t('user/devices', 'Country') ?></span></div>
            <div class="table__head-box-small"><span><?= Yii::t('user/devices', 'City') ?></span></div>
            <div class="table__head-box-small"><span><?= Yii::t('user/devices', 'IP_Address') ?></span></div>
            <div class="table__head-box-small"><span><?= Yii::t('user/devices', 'Last_activity') ?></span></div>
            <div class="table__head-box-small"><span><?= Yii::t('user/devices', 'Action') ?></span></div>
        </div>
    </div>
    <div class="scrollbar-box" -style="height: 100px; overflow: hidden;">
        <div class="table__body-cont">

            <?=
            ListView::widget([
                'options' => [
                    'tag' => false,
                ],
                'dataProvider' => $dataProvider,
                'itemOptions' => [
                    'tag' => false,
                    'class' => '',
                ],
                'layout' => '{items}',
                'emptyText' => '',
                'emptyTextOptions' => ['tag' => false],
                'itemView' => function ($model, $key, $index, $widget) {
                    /** @var $model \frontend\models\search\SessionsSearch */
                    return '
                            <div class="table__body-small">
                                <div class="table__body-box-small"><span>' . $model->sess_country . '</span></div>
                                <div class="table__body-box-small"><span>' . $model->sess_city . '</span></div>
                                <div class="table__body-box-small"><span>' . $model->sess_ip . '</span></div>
                                <div class="table__body-box-small"><span class="replaceDateByJs" data-ts="' . $model->sess_created_ts. '">' . date(Yii::$app->params['datetime_format'], $model->sess_created_ts) . '</span></div>
                                <div class="table__body-box-small"><span class="table-color-green">' . SessionsSearch::actionLabel($model->sess_action) . '</span></div>
                            </div>';
                },
            ]);
            ?>

        </div>
    </div>
</div>

<?php
if ($UserNode->node_devicetype == UserNode::DEVICE_BROWSER) {
    echo '<a href="' . Url::to(['/user/logout'], CREATE_ABSOLUTE_URL) . '" data-method="post" class="btn-min">' . Yii::t('user/devices', 'Log_out') . '</a>';
} else {
    $logout_class = "logout-button";
    if ($UserNode->node_logout_status != UserNode::LOGOUT_STATUS_READY_TO || $UserNode->node_wipe_status != UserNode::WIPE_STATUS_READY_TO) {
        $logout_class = "disabled";
    }
    $wipe_class = "wipe-button";
    if ($UserNode->node_wipe_status != UserNode::WIPE_STATUS_READY_TO) {
        $wipe_class = "disabled";
    }

    if ($User->license_type != Licenses::TYPE_FREE_DEFAULT) {
        echo "<a id=\"node-logout-button-{$UserNode->node_id}\" data-node-id=\"{$UserNode->node_id}\" data-action=\"" . RemoteActions::TYPE_LOGOUT . "\" class=\"btn-min {$logout_class}\" href=\"javascript:void(0)\">" . UserNode::logoutStatus($UserNode->node_logout_status) . "</a>";
        echo "&nbsp";
        echo "<a id=\"node-wipe-button-{$UserNode->node_id}\" data-node-id=\"{$UserNode->node_id}\" data-action=\"" . RemoteActions::TYPE_WIPE . "\" class=\"btn-min {$wipe_class}\" href=\"javascript:void(0)\">" . UserNode::wipeStatus($UserNode->node_wipe_status) . "</a>";
    } else {
        echo "<a id=\"node-logout-button-{$UserNode->node_id}\" data-node-id=\"{$UserNode->node_id}\" data-action=\"" . RemoteActions::TYPE_LOGOUT . "\" class=\"btn-min logout-button disabled masterTooltip\" href=\"javascript:void(0)\" title=\"Available for PRO/Business licenses only\">" . UserNode::logoutStatus($UserNode->node_logout_status) . "</a>";
        echo "&nbsp";
        echo "<a id=\"node-wipe-button-{$UserNode->node_id}\" data-node-id=\"{$UserNode->node_id}\" data-action=\"" . RemoteActions::TYPE_WIPE . "\" class=\"btn-min wipe-button disabled masterTooltip\" href=\"javascript:void(0)\" title=\"Available for PRO/Business licenses only\">" . UserNode::wipeStatus($UserNode->node_wipe_status) . "</a>";
    }
    echo "&nbsp;&nbsp;";
    echo "<span class=\"small\">" . Yii::t('user/devices', 'LogOut_RemoteWipe_means', ['APP_NAME' => Yii::$app->name]) . "</span>";
}
?>