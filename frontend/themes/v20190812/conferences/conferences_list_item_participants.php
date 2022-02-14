<?php
/** @var $this yii\web\View */
/** @var $conference_id integer */
/** @var $conference_name string */
/** @var $model \frontend\models\search\ConferencesSearch */
/** @var $User \common\models\Users */
/** @var $participants string */
/** @var $owner_user_id integer */

/*
(пока не делаем) - просто убираем этот статус он-офф из спсика участников
Доработать систему онлайн-оффлайн партиципантов. Нужно отслеживать активность Юзера,
ставить в кеш переменную на 10 минут что бы не дрочить базу постоянно.
Если переенная умерла в кеше или ее нет, то обновить у юзера дату активности,
затем найти в таблице партиципантов участников с таким ИД,
обновить у них дату активности, найти их конференции и для
них перезаписать поле со списком партиципантов.
*/

//$arr = Json::decode($participants, true);
$arr = json_decode($participants, true);

if ($owner_user_id == $User->user_id) {
    $str = '<a href="#"
           data-conference-id="' . $conference_id . '"
           data-conference-name="' . $conference_name . '"
           class="open-conference-participants edit-list-participants void-0 masterTooltip"
           title="' . Yii::t('user/conferences', 'Edit_list_participants') . '">
            <svg viewBox="0 0 24 24">
                <use xlink:href="/themes/v20190812/images/conferences/account-edit-outline.svg#edit"></use>
            </svg>
        </a>';
} else {
    $str = '<a href="#"
           data-conference-id="' . $conference_id . '"
           data-conference-name="' . $conference_name . '"
           class="view-list-participants void-0 masterTooltip"
           title="' . Yii::t('user/conferences', 'View_list_participants') . '">
            <svg viewBox="0 0 24 24">
                <use xlink:href="/themes/v20190812/images/conferences/account-outline.svg#list"></use>
            </svg>
        </a>';
}
if (is_array($arr)) {
    $l = sizeof($arr);
    $i = 0;
    foreach ($arr as $v) {
        $i++;

        $v['status_class'] = mb_strtolower($v['participant_status']);
        $v['online_status'] = Yii::t('user/conferences', 'Offline');


        //if (in_array($v['status_class'], ['owner', 'joined'])) {
            if (time() - $v['last_activity_timestamp'] <= 600) {
                $v['online_status'] = Yii::t('user/conferences', 'Online');
                //$v['status_class'] = mb_strtolower($v['participant_status']) . '-online';
                $v['status_class'] = mb_strtolower($v['participant_status']) . '-offline';
            } else {
                $v['status_class'] = mb_strtolower($v['participant_status']) . '-offline';
            }
        //}

        $v['participant_name'] = $v['participant_email'];
        if ($v['participant_email'] == $User->user_email) {
            $v['participant_name'] = 'you';
            $v['online_status'] = Yii::t('user/conferences', 'Online');
            //$v['status_class'] = mb_strtolower($v['participant_status']) . '-online';
        }

        $str .= '<a href="#"
                    data-off-title="' . $v['participant_status'] . ', ' . $v['online_status'] . '"
                    title="' . $v['participant_status'] . '"
                    class="void-0 masterTooltip participant-'. $v['status_class'] .'"
                    data-user_id="' . $v['user_id'] . '"
                    data-conference_id="' . $v['conference_id'] . '"
                    data-participant_id="' . $v['participant_id'] . '"
                    data-participant_status="' . $v['participant_status'] . '"
                    data-participant_last_activity="' . $v['participant_last_activity'] . '"
                    data-participant_last_activity_timestamp="' . $v['last_activity_timestamp'] . '"
                    data-participant_email="' . $v['participant_email'] . '"
                    data-participant_name="' . $v['participant_name'] . '">' . Yii::t('user/conferences', $v['participant_name']) . '</a>';
        if ($i < $l) {
            $str .= ', ';
        }

    }
    echo $str;
} else {
    echo Yii::t('user/conferences', 'Empty_participants_list');
}
