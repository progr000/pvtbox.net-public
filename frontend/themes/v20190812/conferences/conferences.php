<?php
/** @var $this yii\web\View */
/** @var $dataProviderConferences \yii\data\ActiveDataProvider */
/** @var $ConferenceAddForm \frontend\models\forms\ConferenceAddForm */
/** @var $ParticipantAddForm \frontend\models\forms\ParticipantAddForm */
/** @var $User \common\models\Users */
/** @var $license_count_info array */
/** @var $is_business_admin boolean */

use yii\bootstrap\ActiveForm;
use yii\widgets\ListView;
use yii\widgets\Pjax;
use frontend\assets\v20190812\conferenceListAsset;

/* assets */
conferenceListAsset::register($this);

/* */
$this->title = Yii::t('user/conferences', 'title');

?>

<!-- begin Admin-panel-page content -->
<div class="content container noShowBalloon"
     id="wss-data"
     data-token="<?= ''/*$site_token*/ ?>"
     data-wss-url="wss://<?= ''/*isset($Server[0]) ? $Server[0]->server_url : 'null' ?>/ws/webfm/<?= $site_token*/ ?>"
     data-wss-url-echo-test-server="ws://echo.websocket.org"
     data-license-available="<?= $is_business_admin ? $license_count_info['unused'] : 1000000 ?>"
     data-error_no_more_license="<?= Yii::t('app/flash-messages', 'license_restriction_businessAdmin_invite_non_registered_but_no_available_licenses') ?>"
     data-cant_add_self_into_the_list="<?= Yii::t('app/flash-messages', 'Cant_add_self_into_the_list') ?>"
     data-participant_already_exist="<?= Yii::t('app/flash-messages', 'Participant_already_exist') ?>">
    <h1><?= Yii::t('user/conferences', 'Conferences') ?></h1>

    <!-- begin form-conference-create -->
    <?php $form = ActiveForm::begin([
        'id'      => 'form-conference-create',
        //'action'  => null,
        'action' => '/conferences/check-conference-name',
        'enableClientValidation' => true,
        //'enableAjaxValidation' => true,
        'options' => [
            'class'    => "colleague-frm img-progress-form",
        ],
    ]);
    ?>
    <div class="form-title"><?= Yii::t('user/conferences', 'Create_new') ?></div>
    <div class="form-row">
        <?= $form->field($ConferenceAddForm, 'conference_name', ['enableAjaxValidation' => true])
            ->textInput([
                'type'         => "text",
                'placeholder'  => $ConferenceAddForm->getAttributeLabel('conference_name'),
                'autocomplete' => "off",
                'aria-label'   => $ConferenceAddForm->getAttributeLabel('conference_name'),
                //aria-required="true" aria-invalid="true"
            ])
            ->label(false)
        ?>
        <button class="btn primary-btn md-btn"
                type="submit"
                name="ConferenceCreate"
                value="<?= Yii::t('user/conferences', 'Create') ?>"><?= Yii::t('user/conferences', 'Create') ?></button>
        <div class="img-progress" data-add-class="img-progress-inline" title="loading..."></div>
    </div>
    <?php ActiveForm::end(); ?>
    <!-- end form-conference-create -->

    <!-- begin conferences-list -->
    <div class="form-title form-title--row"><span><?= Yii::t('user/conferences', 'Conferences_list') ?></span>
        <div class="conference-control">
            <a href="#" class="instant-call void-0">
                <svg viewBox="0 0 24 24">
                    <path fill="currentColor" d="M6.62,10.79C8.06,13.62 10.38,15.94 13.21,17.38L15.41,15.18C15.69,14.9 16.08,14.82 16.43,14.93C17.55,15.3 18.75,15.5 20,15.5A1,1 0 0,1 21,16.5V20A1,1 0 0,1 20,21A17,17 0 0,1 3,4A1,1 0 0,1 4,3H7.5A1,1 0 0,1 8.5,4C8.5,5.25 8.7,6.45 9.07,7.57C9.18,7.92 9.1,8.31 8.82,8.59L6.62,10.79Z" />
                </svg>
                <?= Yii::t('user/conferences', 'Instant_call') ?>
            </a>
            <span class="delimiter"></span>
            <a href="#"
               id="voice-device"
               class="voice-status svg-icons on void-0 masterTooltip"
               data-cookie-name="cookie_voice_status"
               data-on="1" title="turn Off"
               data-title-on="turn Off"
               data-title-off="turn On"><!--
            --><svg -viewBox="0 0 24 24">
                    <use xlink:href="/themes/v20190812/images/conferences/microphone.svg#microphone"></use>
                </svg><!--
             --><!--<svg -viewBox="0 0 24 24">
                    <use xlink:href="/themes/v20190812/images/conferences/microphone.svg#microphone"></use>
                </svg>--><!--
         --></a>
            <a href="#"
               id="video-device"
               class="video-status svg-icons on void-0 masterTooltip"
               data-cookie-name="cookie_video_status"
               data-on="1" title="turn Off"
               data-title-on="turn Off"
               data-title-off="turn On"><!--
             --><svg viewBox="0 0 24 24">
                    <path d="M17,10.5V7A1,1 0 0,0 16,6H4A1,1 0 0,0 3,7V17A1,1 0 0,0 4,18H16A1,1 0 0,0 17,17V13.5L21,17.5V6.5L17,10.5Z" />
                </svg><!--
         --></a>
        </div>
    </div>
    <div class="table-wrap">
        <div class="table-wrap__inner">

            <?php Pjax::begin(['id' => 'conferences-list-content']); ?>
            <?php
            $minCount = 7;
            //$currentPage = intval(Yii::$app->request->get($dataProviderConferences->pagination->pageParam, 1));
            $count = $dataProviderConferences->count;
            //$lost = $dataProviderConferences->pagination->pageSize - $count;
            $lost = $minCount - $count;
            ?>
            <?=
            ListView::widget([
                'dataProvider' => $dataProviderConferences,
                'itemOptions' => [
                    'tag' => false,
                    'class' => '',
                ],
                'layout' => '
                    <table class="conferences-tbl">
                        <thead>
                            <tr>
                                <th>' . Yii::t('user/conferences', 'conference_name') . '</th>
                                <th>' . Yii::t('user/conferences', 'conference_participants') . '</th>
                                <th>' . Yii::t('user/conferences', 'conference_status') . '</th>
                                <th>' . Yii::t('user/conferences', 'conference_action') . '</th>
                            </tr>
                        </thead>
                        <tbody>
                            {items}
                        </tbody>
                    </table>
                    {pager}',
                'emptyText' => $this->render('conferences_list_nodata', []),
                'emptyTextOptions' => ['tag' => false],
                'itemView' => function ($model, $key, $index, $widget) use ($lost, $count, $User) {
                    $lost_row = '';
                    if ($lost>0 && ($index == $count - 1)) {
                        for ($i=1; $i<=$lost; $i++) {
                            $lost_row .= $this->render('conferences_list_item_empty');
                        }
                    }
                    /* @var $model \frontend\models\search\ConferencesSearch */
                    return $this->render('conferences_list_item', [
                        'model' => $model,
                        'User' => $User,
                    ]) . $lost_row;
                },
            ]);
            ?>
            <?php Pjax::end(); ?>

        </div>
    </div>
    <!-- end conferences-list -->

</div>
<!-- end Admin-panel-page content -->


<!-- begin MODALS -->
<?= $this->render('conferences_modal', [
    'ParticipantAddForm' => $ParticipantAddForm,
]) ?>
<!-- end MODALS -->