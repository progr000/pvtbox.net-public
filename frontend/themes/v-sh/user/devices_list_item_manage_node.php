<?php

/** @var $browser string */
/** @var $status array */
/** @var $dataProvider */
/** @var $SessionsSearch \frontend\models\search\SessionsSearch */
/** @var $UserNode \common\models\UserNode */
/** @var $User \common\models\Users */

use yii\widgets\ListView;
use yii\helpers\Url;
use common\models\UserNode;
use common\models\RemoteActions;
use common\models\Licenses;
use frontend\models\search\SessionsSearch;

?>
<table class="simple-tbl log-info-node-table">
    <thead>
        <tr>
            <th><?= Yii::t('user/devices', 'Country') ?></th>
            <th><?= Yii::t('user/devices', 'City') ?></th>
            <th><?= Yii::t('user/devices', 'IP_Address') ?></th>
            <th><?= Yii::t('user/devices', 'Last_activity') ?></th>
            <th><?= Yii::t('user/devices', 'Action') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $minPageSize = 5;
        $count = $dataProvider->count;
        $lost = isset($dataProvider->pagination->pageSize)
            ? $dataProvider->pagination->pageSize - $count
            : $minPageSize - $count;
        ?>
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
            'itemView' => function ($model, $key, $index, $widget) use ($lost, $count) {
                $lost_row = '';
                if ($lost>0 && ($index == $count - 1)) {
                    for ($i=1; $i<=$lost; $i++) {
                        $lost_row .= '
                            <tr>
                                <td></td>
                                <td></td>
                                <td>&nbsp;</td>
                                <td></td>
                                <td></td>
                            </tr>
                        ';
                    }
                }
                /** @var $model \frontend\models\search\SessionsSearch */
                return '
                    <tr>
                        <td>' . $model->sess_country . '</td>
                        <td>' . $model->sess_city . '</td>
                        <td>' . $model->sess_ip . '</td>
                        <td class="replaceDateByJs" data-ts="' . $model->sess_created_ts . '">' . date(Yii::$app->params['datetime_format'], $model->sess_created_ts) . '</td>
                        <td><span class="highlight-green">' . SessionsSearch::actionLabel($model->sess_action) . '</span></td>
                    </tr>' . $lost_row;
            },
        ]);
        ?>
    </tbody>
</table>
<?php
if ($UserNode->node_devicetype == UserNode::DEVICE_BROWSER) {
    echo '<a class="btn primary-btn xs-btn device-manage-btn" href="' . Url::to(['/user/logout'], CREATE_ABSOLUTE_URL) . '" data-method="post">' . Yii::t('user/devices', 'Log_out') . '</a>';
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
        echo "<a class=\"btn primary-btn xs-btn device-manage-btn {$logout_class}\" id=\"node-logout-button-{$UserNode->node_id}\" data-node-id=\"{$UserNode->node_id}\" data-action=\"" . RemoteActions::TYPE_LOGOUT . "\" href=\"#\">" . UserNode::logoutStatus($UserNode->node_logout_status) . "</a>";
        echo "&nbsp";
        echo "<a class=\"btn primary-btn xs-btn device-manage-btn {$wipe_class}\" id=\"node-wipe-button-{$UserNode->node_id}\" data-node-id=\"{$UserNode->node_id}\" data-action=\"" . RemoteActions::TYPE_WIPE . "\" href=\"#\">" . UserNode::wipeStatus($UserNode->node_wipe_status) . "</a>";
    } else {
        echo "<a class=\"btn primary-btn xs-btn device-manage-btn logout-button disabled masterTooltip\" id=\"node-logout-button-{$UserNode->node_id}\" data-node-id=\"{$UserNode->node_id}\" data-action=\"" . RemoteActions::TYPE_LOGOUT . "\" href=\"#\" title=\"Available for PRO/Business licenses only\">" . UserNode::logoutStatus($UserNode->node_logout_status) . "</a>";
        echo "&nbsp";
        echo "<a class=\"btn primary-btn xs-btn device-manage-btn wipe-button disabled masterTooltip\" id=\"node-wipe-button-{$UserNode->node_id}\" data-node-id=\"{$UserNode->node_id}\" data-action=\"" . RemoteActions::TYPE_WIPE . "\" href=\"#\" title=\"Available for PRO/Business licenses only\">" . UserNode::wipeStatus($UserNode->node_wipe_status) . "</a>";
    }
    echo "&nbsp;&nbsp;";
    echo "<span class=\"device-manage-note-small\">" . Yii::t('user/devices', 'LogOut_RemoteWipe_means', ['APP_NAME' => Yii::$app->name]) . "</span>";
}
?>
