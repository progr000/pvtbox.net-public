<?php
/* @var $this yii\web\View */
/* @var $model \frontend\models\search\SessionsSearch */
/* @var $model_changeemail \frontend\models\forms\ChangeEmailForm */
/* @var $model_changetimezone \frontend\models\forms\SetTimeZoneOffsetForm */
/* @var $form \yii\widgets\ActiveForm */
/* @var $user \common\models\Users */
/* @var $dataProviderSession \yii\data\ActiveDataProvider */

use yii\helpers\Url;
use common\models\Licenses;
use common\models\Users;
use common\helpers\Functions;

$list_of_timezones = Functions::get_list_of_timezones(Yii::$app->language);
?>
<form class="profile-frm">
    <div class="form-title">
        <?= Yii::t('user/profile', 'Account_type') ?> <span><?= Licenses::getType($user->license_type); ?></span>
        <?php
        if (in_array($user->license_type, [Licenses::TYPE_FREE_DEFAULT, Licenses::TYPE_FREE_TRIAL])) {
            echo '<a class="btn-min" href="' . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . '">' . Yii::t('user/profile', 'Update_to_pro_business', ['type_licenses' => 'PRO/Business']) . '</a>';
        }
        ?>
    </div>
    <div class="form-group">
        <input type="email"
               value="<?= $user->user_email ?>"
               placeholder="<?= $user->user_email ?>"
               readonly="readonly"
               disabled="disabled" />
        <?php
        if ($user->user_status != Users::STATUS_CONFIRMED) {
            $cache_key = 'user-lock-resend-confirm-' . $user->user_id;
            $lock = Yii::$app->cache->get($cache_key);
            if (!$lock) {
                ?>
                <button class="btn confirm-value js-open-form masterTooltip"
                        type="button"
                        data-src="#resend-confirm-modal" title="<?= Yii::t('user/profile', 'Confirm_masterTooltip') ?>">
                    <svg class="icon icon-warning">
                        <use xlink:href="#warning"></use>
                    </svg><span><?= Yii::t('user/profile', 'Confirm_email') ?></span>
                </button>
                <?php
            } else {
                ?>
                <button class="btn confirm-value masterTooltip void-0" type="button"
                        title="<?= Yii::t('user/profile', 'Confirm_masterTooltip') ?>">
                    <svg class="icon icon-warning">
                        <use xlink:href="#warning"></use>
                    </svg>
                    <span><?= Yii::t('user/profile', 'Confirm_email_wait', ['timeout' => Yii::$app->params['timeout_resend_confirm'] - (time() - $lock)]) ?></span>
                </button>
                <?php
            }
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
    <div class="form-group">
        <input type="password"
               value=""
               placeholder="*******"
               readonly="readonly"
               disabled="disabled" />
        <button class="btn edit-value js-open-form" type="button" data-src="#change-password-modal">
            <svg class="icon icon-edit">
                <use xlink:href="#edit"></use>
            </svg><span><?= Yii::t('user/profile', 'Change_password') ?></span>
        </button>
    </div>
    <div class="form-group">
        <input type="text"
               value="<?= isset($list_of_timezones[$user->static_timezone]) ? $list_of_timezones[$user->static_timezone] : $list_of_timezones[0]  ?>"
               placeholder=""
               readonly="readonly" disabled="disabled" />
        <button class="btn edit-value js-open-form" type="button" data-src="#change-timezone-modal">
            <svg class="icon icon-edit">
                <use xlink:href="#edit"></use>
            </svg><span><?= Yii::t('user/profile', 'Change_timezone') ?></span>
        </button>
    </div>
</form>
<div class="delete-profile">
    <button class="btn primary-btn xs-btn accent-btn btn-deleteAccount" type="button" data-href="<?= Url::to(['/user/delete-account'], CREATE_ABSOLUTE_URL) ?>" data-method-off="post">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg><span><?= Yii::t('user/profile', 'Delete_account') ?></span>
    </button>
    <p class="footnote" id="text-inform-for-delete-account"><?= Yii::t('user/profile', 'Delete_account_inform') ?></p>
</div>
