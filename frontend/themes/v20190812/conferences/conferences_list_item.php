<?php
/** @var $this yii\web\View */
/** @var $model \frontend\models\search\ConferencesSearch */
/** @var $User \common\models\Users */

use yii\helpers\Url;
use common\models\UserConferences;

$enc_name = md5($model->conference_name);
if ($model->conference_status == UserConferences::STATUS_IDLE) {
    $action_room = Yii::t('user/conferences', 'Open_room');
    $action_class = 'open-room';
} else {
    $action_room = Yii::t('user/conferences', 'Join_room');
    $action_class = 'join-room';
}
if ($model->your_user_id == $model->user_id) {
    $title_close = Yii::t('user/conferences', 'Close_conference');
    $confirm_text = Yii::t('user/conferences', 'Are_you_sure_delete');
} else {
    $title_close = Yii::t('user/conferences', 'Leave_conference');
    $confirm_text = Yii::t('user/conferences', 'Are_you_sure_leave');
}
?>

<tr id="tr-conference-<?= $model->conference_id ?>" class="manager-list__row-conf conf-name-md5-<?= $enc_name ?>">
    <td><a href="#"
           class="conference-name void-0"
           data-conference-id="<?= $model->conference_id ?>"
           data-conference-name="<?= $model->conference_name ?>"><?= $model->conference_name ?></a></td>
    <td id="td-participants-<?= $model->conference_id ?>"
        class="participants-list"><?= $this->render('conferences_list_item_participants', [
            'User'            => $User,
            'owner_user_id'   => $model->user_id,
            'conference_id'   => $model->conference_id,
            'conference_name' => $model->conference_name,
            'participants'    => $model->conference_participants,
        ]) ?></td>
    <td class="conference-status-<?= strtolower(UserConferences::getStatus($model->conference_status)) ?>"><?= UserConferences::getStatus($model->conference_status) ?></td>
    <td class="device-actions-buttons">
        <a href="<?= Url::to(['conferences/open-conference', 'conference_id' => $model->conference_id], CREATE_ABSOLUTE_URL) ?>"
           class="conference-open-join-link masterTooltip <?= $action_class ?>"
           title="<?= $action_room ?>"
           data-room-uuid=""
           data-pjax="0"
           data-conference-id="<?= $model->conference_id ?>"><!--
         --><svg viewBox="0 0 24 24">
                <use xlink:href="/themes/v20190812/images/conferences/Vector.svg#vector"></use>
            </svg><!--
         --><!--
     --></a>
        <a href="#"
           class="conference-manage-guest-link masterTooltip void-0"
           title="<?= Yii::t('user/conferences', 'Guest_link') ?>"
           data-room-uuid=""
           data-conference-guest-link="<?= $model->conference_guest_link ?>"
           data-conference-guest-hash="<?= $model->conference_guest_hash ?>"
           data-conference-name="<?= $model->conference_name ?>"
           data-conference-id="<?= $model->conference_id ?>"><!--
         --><svg viewBox="0 0 24 24">
                <use xlink:href="/themes/v20190812/images/conferences/share-variant.svg#share"></use>
            </svg><!--
         --><!--
     --></a>
        <a href="#"
           class="masterTooltip close cancel-conference void-0"
           title="<?= $title_close ?>"
           data-confirm-message="<?= $confirm_text ?>"
           data-conference-id="<?= $model->conference_id ?>"><?= Yii::t('user/devices', 'Hide') ?></a>
    </td>
</tr>
