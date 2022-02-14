<?php
/** @var $this yii\web\View */
/** @var $ParticipantAddForm \frontend\models\forms\ParticipantAddForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

?>

<!-- begin #participants-select-modal -->
<div class="popup" id="participants-select-modal">
    <a class="hidden js-open-form" href="#" id="trigger-participants-select-modal" data-src="#participants-select-modal" data-modal="true"></a>
    <div class="popup__inner">

        <div class="popup-form-title"><?= Yii::t('user/conferences', 'Select_Participants') ?></div>

        <!-- begin form-colleague-add -->
        <?php $form = ActiveForm::begin([
            'id'      => 'form-participant-add',
            'action'  => null,
            //'action' => '/conferences/check-participant',
            'enableClientValidation' => true,
            'options' => [
                'class'    => "colleague-frm",
            ],
        ]);
        ?>
        <div class="form-row">
            <?= $form->field($ParticipantAddForm, 'participant_email')
                ->textInput([
                    'id'           => 'participant-email',
                    'type'         => "email",
                    'placeholder'  => $ParticipantAddForm->getAttributeLabel('participant_email'),
                    'autocomplete' => "off",
                    'aria-label'   => $ParticipantAddForm->getAttributeLabel('participant_email'),
                    //aria-required="true" aria-invalid="true"
                ])
                ->label(false)
            ?>
            <button class="btn primary-btn md-btn"
                    id="participant-add-button"
                    type="submit"
                    name="ColleagueAdd"
                    value="<?= Yii::t('user/conferences', 'Add') ?>"><?= Yii::t('user/conferences', 'Add') ?></button>
        </div>
        <?php ActiveForm::end(); ?>
        <!-- end form-colleague-add -->

        <div id="participant-row-tpl" style="display: none;">
            <div class="manager-list__row available-participant"
                 id="enc-{enc_participant_email}"
                 data-num-pp="{num_pp}">
                <div class="manager-list__col">
                    <input type="checkbox"
                           {checked}
                           class="participant-checkbox"
                           data-participant-email="{participant_email}"
                           data-user-id="{user_id}"
                           data-enabled="{user_enabled}" />
                    <span class="participant-email">{participant_email}</span>
                </div>
            </div>
        </div>

        <div class="available-list progress-loading" id="progress-tpl">
            <div class="img-progress" title="loading..."></div>
        </div>

        <div class="available-list" id="available-participants-list">
        </div>

        <div style="margin-top: 5px; text-align: right;">
            <input type="hidden" name="conference_id" id="conference-id" />
            <input type="hidden" name="conference_name" id="conference-name" />
            <button type="submit"
                    id="set-participants-for-conference"
                    class="-button-confirm-yes btn primary-btn sm-btn orange-btn -js-close-popup confirm-yes set-participants"
                    name="select-folder"><?= Yii::t('user/colleague-manage', 'Select') ?></button>
            <button type="button"
                    class="-button-confirm-no btn primary-btn sm-btn white-btn js-close-popup confirm-no close-popup-participants" data-dismiss="modal"
                    name="close-modal"><?= Yii::t('user/colleague-manage', 'Cancel') ?></button>
        </div>


    </div>
    <button class="btn popup-close-btn js-close-popup" type="button" title="<?= Yii::t('app/common', "Close") ?>">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg>
    </button>
</div>
<!-- end #participants-select-modal -->

<!-- begin #guest-link-modal -->
<div class="popup" id="guest-link-modal">
    <a class="hidden js-open-form" href="#" id="trigger-guest-link-modal" data-src="#guest-link-modal" data-modal="true"></a>
    <div class="popup__inner">

        <!-- *** -->
        <div class="modal-body">
            <?php
            $form = ActiveForm::begin([
                'id' => 'guest-link-send-to-email-form',
                'enableClientValidation' => true,
                'options' => [
                    'onsubmit' => 'return false',
                ],
            ]);
            ?>
            <div class="form-block">
                <span class="modal-title"><?= Yii::t('user/conferences', 'Guest_link_for_conference_room') ?></span>
                <input type="hidden" name="conference_id" id="for-send-conference-id" />
                <input type="hidden" name="conference_name" id="for-send-conference-name" />
                <input type="hidden" name="conference_guest_hash" id="for-send-conference-guest-hash" />
                <label><textarea class="form-control form-control-textarea notActive" id="guest-link-field" readonly="readonly">cdscsdcsd</textarea></label>
                <div class="link-manage-buttons">
                    <a class="btn-empty btn-link-settings copy-button void-0" href="#" data-clipboard-action="copy" data-clipboard-target="#guest-link-field"><?= Yii::t('user/filemanager', 'Copy_link') ?></a>
                    <a class="btn-empty btn-link-settings generate-new-guest-link void-0" href="#" data-confirm-text="<?= Yii::t('user/conferences', 'confirm_generate_new_link') ?>"><?= Yii::t('user/conferences', 'Generate_new_link') ?></a>
                </div>
                <span class="modal-title modal-title-indenting"><?= Yii::t('user/filemanager', 'Send_link_to_email') ?></span>
                <?=
                $form->field($ParticipantAddForm, 'participant_email',[
                    'template'=>'{label}{input}{hint}{error}',
                    'options' => [
                        'tag' => 'div',
                        'class' => 'user-name-field'
                    ],
                ])->textInput([
                    'id' => "guest-email",
                    'placeholder' => "E-mail",
                    'autocomplete' => "off",
                    'aria-label' => "E-mail",
                ])->label(false)
                ?>
                <?= Html::submitButton(Yii::t('user/filemanager', 'Send'), ['class' => "btn primary-btn wide-btn", 'name' => "guest-link-send-to-email-button"]) ?>
            </div>
            <?php
            ActiveForm::end();
            ?>
        </div>

    </div>
    <button class="btn popup-close-btn js-close-popup" type="button" title="<?= Yii::t('app/common', "Close") ?>">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg>
    </button>
</div>
<!-- end #guest-link-modal -->
