<?php
/* @var $this yii\web\View */
/* @var $model \frontend\models\search\SessionsSearch */
/* @var $model_changeemail \frontend\models\forms\ChangeEmailForm */
/* @var $model_changetimezone \frontend\models\forms\SetTimeZoneOffsetForm */
/* @var $form \yii\widgets\ActiveForm */
/* @var $user \common\models\Users */
/* @var $dataProviderSession \yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use common\models\Licenses;
use common\models\Users;
use common\helpers\Functions;

$list_of_timezones = Functions::get_list_of_timezones(Yii::$app->language);
?>

<!-- .inputForm -->
<div class="inputForm inputForm--name">

    <div class="inputForm__title">
        <span><?= Yii::t('user/profile', 'Account_type') ?></span><b><?= Licenses::getType($user->license_type); ?></b>
        <?php
        if (in_array($user->license_type, [Licenses::TYPE_FREE_DEFAULT, Licenses::TYPE_FREE_TRIAL])) {
            echo '<a class="btn-min" href="' . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . '">' . Yii::t('user/profile', 'Update_to_pro_business', ['type_licenses' => 'PRO/Business']) . '</a>';
        }/* elseif ($user->license_type == Licenses::TYPE_PAYED_PROFESSIONAL) {
            echo '<a class="btn-min" href="' . Url::to(['/purchase/-business?billed=' . Licenses::getBilledByPeriod($user->license_period)], CREATE_ABSOLUTE_URL) . '">' . Yii::t('user/profile', 'Update_to_pro_business', ['type_licenses' => 'Business']) . '</a>';
        }*/
        ?>
    </div>

    <!--
    <div class="inputForm__cont">

        <div class="inputForm__box">

            <div class="form-group">
                <input type="text" class="form-control form-control-notActive" value="<?= $user->user_name ?>" placeholder="<?= $user->user_name ?>" readonly="readonly" _disabled="disabled" />
            </div>

        </div>

        <div class="inputForm__box">

            <spam class="link-change profile-change-name" data-toggle="modal" data-target="#nameChanging"><?= Yii::t('user/profile', 'Change_name') ?></spam>

        </div>

    </div>
    -->

    <div class="inputForm__cont">

        <div class="inputForm__box">

            <div class="form-group">
                <input type="email" class="form-control form-control-notActive" value="<?= $user->user_email ?>" placeholder="<?= $user->user_email ?>" readonly="readonly" _disabled="disabled" />
            </div>

        </div>

        <?php
        if ($user->user_status != Users::STATUS_CONFIRMED) {
            ?>
            <div class="inputForm__box">

                <span class="glyphicon glyphicon-alert masterTooltip" title="<?= Yii::t('user/profile', 'Confirm_masterTooltip') ?>" style="color: #424242"></span>
                <span class="link-confirm-email masterTooltip" title="<?= Yii::t('user/profile', 'Confirm_masterTooltip') ?>" data-toggle="modal" data-target="#resend-confirm-modal"><?= Yii::t('user/profile', 'Confirm_email') ?></span>

            </div>
            <?php
        }
        if ($user->license_type == Licenses::TYPE_PAYED_BUSINESS_USER) {
            ?>
            <div class="inputForm__box">

                <span class="glyphicon glyphicon-user" style="color: #424242"></span>
                <span class="link-info profile-company-name"><?= Yii::t('user/profile', 'part of {user_company_name}', ['user_company_name' => $user->user_company_name]) ?></span>

            </div>
            <?php
        }
        ?>

    </div>


    <div class="inputForm__cont">

        <div class="inputForm__box">

            <div class="form-group">
                <input type="text" class="form-control form-control-notActive" value="**********" placeholder="**********" readonly="readonly" _disabled="disabled" autocomplete="off" />
            </div>

        </div>

        <div class="inputForm__box">

            <span class="link-change profile-change-password" data-toggle="modal" data-target="#changePassword"><?= Yii::t('user/profile', 'Change_password') ?></span>

        </div>

    </div>


    <div class="inputForm__cont">

        <div class="inputForm__box">

            <div class="form-group">
                <input type="text" class="form-control form-control-notActive" value="<?= isset($list_of_timezones[$user->static_timezone]) ? $list_of_timezones[$user->static_timezone] : $list_of_timezones[0]  ?>" placeholder="**********" readonly="readonly" _disabled="disabled" autocomplete="off" />
            </div>

        </div>

        <div class="inputForm__box">

            <span class="link-change profile-change-timezone" data-toggle="modal" data-target="#change-timezone-modal"><?= Yii::t('user/profile', 'Change_timezone') ?></span>

        </div>

    </div>


    <div class="inputForm__cont delete-account-div">

        <div class="inputForm__box">

            <a class="btn-deleteAccount" href="javascript:void(0)" data-href="<?= Url::to(['/user/delete-account'], CREATE_ABSOLUTE_URL) ?>" -data-method="post"><?= Yii::t('user/profile', 'Delete_account') ?></a>

            <div id="text-inform-for-delete-account" class="inform-for-delete-account">
                <?= Yii::t('user/profile', 'Delete_account_inform') ?>
            </div>
        </div>


    </div>

</div>
<!-- END .inputForm -->

<?php
// +++ Modal Email
Modal::begin([
    'options' => ['id' => 'change-email-modal'],
    //'closeButton' => ['id' => 'close-button-chemail'],
    'closeButton' => false,
    'header' => '<div type="button" class="close" data-dismiss="modal" aria-label="Close"></div>',
    'size' => '',
]);
?>
<div class="form-block">
    <?php $form = ActiveForm::begin(['id' => 'form-change-email', 'action'=>['profile']]); ?>
    <span class="modal-title"><?= Yii::t('user/profile', 'Change_email') ?></span>
    <?= $form->field($model_changeemail, 'user_email', ['enableAjaxValidation' => true])
             ->textInput([
                 'type' => "email",
                 'placeholder' => $model_changeemail->getAttributeLabel('user_email'),
                 'autocomplete' => "off",
                 //'value' => Yii::$app->user->identity->user_email
             ])
             ->label(false)
    ?>

    <?= $form->field($model_changeemail, 'password', ['enableAjaxValidation' => true])
        ->passwordInput([
            'placeholder' => $model_changeemail->getAttributeLabel('password'),
            'autocomplete' => "off",
            //'value' => Yii::$app->user->identity->user_email
        ])
        ->label(false)
    ?>


    <?= Html::submitButton(Yii::t('user/profile', 'OK'), ['class' => 'btn-big', 'name' => 'ChangeEmail']) ?>
    <?php ActiveForm::end(); ?>
</div>
<?php
Modal::end();

// Modal Password
Modal::begin([
    'options' => ['id' => 'change-password-modal'],
    //'closeButton' => ['id' => 'close-button-chpassword'],
    'closeButton' => false,
    'header' => '<div type="button" class="close" data-dismiss="modal" aria-label="Close"></div>',
    'size' => '',
]);
?>
<div class="form-block">
    <?php $form = ActiveForm::begin(['id' => 'form-profile', 'action'=>['profile']]); ?>
    <span class="modal-title" style="color: #9F9F9F;"><?= Yii::t('user/profile', 'After_click_sent_instruct') ?></span>
    <?= '' /*Html::submitButton(Yii::t('user/profile', 'OK'), ['class' => 'btn-big', 'name' => 'ChangePasswordStep1'])*/ ?>
    <input type="submit" name="ChangePasswordStep1" value="<?= Yii::t('forms/login-signup-form', 'OK') ?>" class="btn-big" />
    <div class="img-progress" title="loading..."></div>
    <?php ActiveForm::end(); ?>
</div>
<?php
Modal::end();
?>


<?php
// +++ Modal TimeZone
Modal::begin([
    'options' => ['id' => 'change-timezone-modal'],
    //'closeButton' => ['id' => 'close-button-chemail'],
    'closeButton' => false,
    'header' => '<div type="button" class="close" data-dismiss="modal" aria-label="Close"></div>',
    'size' => '',
]);
?>
    <div class="form-block">
        <?php $form = ActiveForm::begin(['id' => 'form-change-timezone', 'action'=>['profile']]); ?>
        <span class="modal-title"><?= Yii::t('user/profile', 'Change_timezone') ?></span>

        <?php
        echo $form->field($model_changetimezone, 'timezone_offset_seconds', [
        'template'=>'{label}<div class="select select-color-orange select-timezone">{input}{hint}{error}</div>'
        ])->dropDownList(Functions::get_list_of_timezones(Yii::$app->language), [
        'id'              => "timezone-vars",
        'class'           => "selectpicker",
        'data-actionsBox' => "true",
        'data-size-'       => "15",
        ])->label(false);
        ?>

        <?= Html::submitButton(Yii::t('user/profile', 'OK'), ['class' => 'btn-big', 'name' => 'ChangeTimeZone']) ?>
        <?php ActiveForm::end(); ?>
    </div>
<?php
Modal::end();