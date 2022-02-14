<?php
/* @var $model \frontend\models\search\ColleaguesSearch | array */
/* @var $admin \common\models\Users */

use common\models\UserColleagues;
use common\models\Licenses;

$colleague = UserColleagues::prepareColleagueDataFromArray($model);

if ($model['awaiting_permissions']) { $colleague['status'] = Yii::t('user/admin-panel', 'awaiting_permissions'); }
if (in_array($model['license_type'], [Licenses::TYPE_PAYED_PROFESSIONAL, Licenses::TYPE_PAYED_BUSINESS_ADMIN])) {
    $colleague['external'] = Yii::t('user/admin-panel', "external_license");
    $colleague['remove_link'] = Yii::t('user/admin-panel', "Exclude_user");
} elseif ($model['license_type'] == Licenses::TYPE_PAYED_BUSINESS_USER && $model['owner_collaboration_user_id'] != $admin->user_id) {
    $colleague['external'] = Yii::t('user/admin-panel', "external_license");
    $colleague['remove_link'] = Yii::t('user/admin-panel', "Exclude_user");
} else {
    $colleague['external'] = '';
    $colleague['remove_link'] = Yii::t('user/admin-panel', 'Remove');
}
//var_dump($model);
if ($model['colleague_permission'] == UserColleagues::PERMISSION_OWNER) {
    $user_created_ts = strtotime($admin->user_created) + Yii::$app->session->get('UserTimeZoneOffset', 0);
    ?>
    <tr>
        <td><div class="user-short color-<?= $admin->_color ?>"><?= $admin->_sname ?></div></td>
        <td><?= $admin->user_email ?></td>
        <td>
            <div class="reg-info">
                <span class="table-status"><?= Yii::t('user/admin-panel', 'Registered') ?></span>
                <br />
                <span class="format-date-js" data-ts="<?= $user_created_ts ?>"><?= date(Yii::$app->params['datetime_format'], $user_created_ts) ?></span>
            </div>
        </td>
        <td><span class="masterTooltip has-tooltip admin-of-panel" title="<?= Yii::t('user/admin-panel', 'You_are_the_Admin') ?>"><?= Yii::t('user/admin-panel', 'All') ?></span></td>
        <td></td>
    </tr>
    <?php
} else {
    ?>
    <tr>
        <td><div class="user-short color-<?= $colleague['color'] ?>"><?= $colleague['name'] ?></div></td>
        <td><?= $colleague['email'] ?> <span class="status-external" style="margin-top: 1px;"><?= $colleague['external'] ?></span></td>
        <td>
            <div class="reg-info">
                <span class="table-status"><?= $colleague['status'] ?></span>
                <br />
                <span class="format-date-js" data-ts="<?= $colleague['ts'] ?>"><?= $colleague['date'] ?></span>
            </div>
        </td>
        <td><a class="manage-colleague-folder" data-email="<?= $colleague['email'] ?>" href="#" data-pjax="0"><?= Yii::t('user/admin-panel', 'Set') ?></a></td>
        <td><a class="delete-colleague-folder" data-email="<?= $colleague['email'] ?>" href="#" data-confirm-text="<?= Yii::t('user/admin-panel', 'Are_you_sure_to_remove_colleague') ?>" data-confirm-yes="<?= Yii::t('app/common', 'OK') ?>" data-confirm-no="<?= Yii::t('app/common', 'Cancel') ?>"><?= $colleague['remove_link'] ?></a></td>
    </tr>
    <?php
}
?>