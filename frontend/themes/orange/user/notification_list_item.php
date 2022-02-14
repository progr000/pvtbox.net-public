<?php
/* @var $model \frontend\models\search\NotificationsSearch */

$sr_data = @unserialize($model->notif_data);
if (isset($sr_data['search'], $sr_data['replace'])) {
    $notif_text = str_replace($sr_data['search'], $sr_data['replace'], Yii::t('mail/notifications', $model->notif_type));
} else {
    $notif_text = Yii::t('mail/notifications', $model->notif_type);
}

if (isset($sr_data['links_data'])) {
    foreach ($sr_data['links_data'] as $k=>$v) {
        $link_data['search'][] = "{" . $k . "}";
        $link_data['replace'][] = Yii::$app->urlManager->createAbsoluteUrl($v);
    }
    $notif_text = str_replace($link_data['search'], $link_data['replace'], $notif_text);
}
?>

<div class="events-list__box">
    <div class="events-list__box-info"><span><?= $notif_text ?></span></div>
    <div class="events-list__box-date"><span class="<?= ($model->notif_isnew ? 'isnew' : '' ) ?>"><?= date(Yii::$app->params['datetime_format'], $model->_notif_date_ts) ?></span></div>
</div>
