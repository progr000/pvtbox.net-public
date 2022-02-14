<?php
/* @var $model \frontend\models\search\ColleaguesSearch | array */
/* @var $admin \common\models\Users */

use common\models\UserColleagues;
use common\models\Licenses;

$colleague = UserColleagues::prepareColleagueDataFromArray($model);
//var_dump($model);
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
    <div class="table__body">
        <div class="table__body-box"><div class="userId color-<?= $admin->_color ?>"><strong><?= $admin->_sname ?></strong></div></div>
        <div class="table__body-box"><b><?= $admin->user_email ?></b></div>
        <div class="table__body-box"><span class="table-status">Registered</span><i class="format-date-js" data-ts="<?= $user_created_ts ?>"><?= date(Yii::$app->params['datetime_format'], $user_created_ts) ?></i></div>
        <div class="table__body-box"><b class="table-color-dark manage-colleague-folder- masterTooltip admin-of-panel" title="<?= Yii::t('user/admin-panel', 'You_are_the_Admin') ?>"><?= Yii::t('user/admin-panel', 'All') ?></b></div>
        <div class="table__body-box"></div>
    </div>
    <?php
} else {
    ?>
    <div class="table__body">
        <div class="table__body-box"><div class="userId color-<?= $colleague['color'] ?>"><strong><?= $colleague['name'] ?></strong></div></div>
        <div class="table__body-box"><b><?= $colleague['email'] ?></b> <span class="table-status-external" style="margin-top: 1px;"><?= $colleague['external'] ?></span></div>
        <div class="table__body-box"><span class="table-status"><?= $colleague['status'] ?></span><i class="format-date-js" data-ts="<?= $colleague['ts'] ?>"><?= $colleague['date'] ?></i></div>
        <div class="table__body-box"><a class="table-color-dark manage-colleague-folder" data-email="<?= $colleague['email'] ?>" href="#" data-pjax="0"><?= Yii::t('user/admin-panel', 'Set') ?></a></div>
        <div class="table__body-box"><a class="table-delete delete-colleague-folder" data-email="<?= $colleague['email'] ?>" href="#" data-confirm-text="<?= Yii::t('user/admin-panel', 'Are_you_sure_to_remove_colleague') ?>" data-confirm-yes="<?= Yii::t('app/common', 'OK') ?>" data-confirm-no="<?= Yii::t('app/common', 'Cancel') ?>"><?= $colleague['remove_link'] ?></a></div>
    </div>
    <?php
}
?>