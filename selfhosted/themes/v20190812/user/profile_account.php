<?php
/* @var $this yii\web\View */
/* @var $model \frontend\models\search\SessionsSearch */
/* @var $model_changetimezone \frontend\models\forms\SetTimeZoneOffsetForm */
/* @var $form \yii\widgets\ActiveForm */
/* @var $user \common\models\SelfHostUsers */
/* @var $dataProviderSession \yii\data\ActiveDataProvider */

use yii\helpers\Url;
use common\models\SelfHostUsers;
use common\helpers\Functions;

$list_of_timezones = Functions::get_list_of_timezones(Yii::$app->language);
?>
<form class="profile-frm">
    <div class="form-title">
        <?= Yii::t('user/profile', 'Account_type') ?> <span class="highlight"><?= Yii::t('user/profile', 'Self_hosted') ?></span>
    </div>
    <div class="form-title">
        <?= Yii::t('user/profile', 'License_key:') ?> <span class="highlight"><?= $user->shu_user_hash ?></span>
    </div>
    <div class="form-title">
        <?= Yii::t('user/profile', 'Account_status') ?>
        <span class="highlight"><?php
            if ($user->shu_brand_status || $user->shu_support_status) {
                echo Yii::t('app/flash-messages', 'Please_wait_Support_will_contact_you_soon');
            } else {
                if ($user->shu_status == SelfHostUsers::STATUS_ACTIVE) {
                    $cache_key = 'shu-email-with-link-sent-' . $user->shu_id;
                    if (!Yii::$app->cache->get($cache_key)) {
                        echo Yii::t('app/flash-messages', 'Email sent.') .
                            " <a href=\"/user/resend-download\">" . Yii::t('user/profile', 'Resend_email') . "</a>";
                    } else {
                        echo Yii::t('user/profile', 'Please_wait_for_email');
                    }
                } else {
                    if ($user->shu_status == SelfHostUsers::STATUS_SH_LOCKED) {
                        echo "<span class=\"\">" . Yii::t('user/profile', 'Server blocked') . "</span>" .
                             " <a href=\"/support\">" . Yii::t('user/profile', 'Contact_support') . "</a>";;
                    } elseif ($user->shu_status == SelfHostUsers::STATUS_CONFIRMED) {
                        echo "<span class=\"\">" . Yii::t('user/profile', 'Server working') . "</span>";
                    }
                }
            }
        ?></span>
    </div>
    <div class="form-group">
        <input type="email"
               value="<?= $user->shu_email ?>"
               placeholder="<?= $user->shu_email ?>"
               readonly="readonly"
               disabled="disabled" />
        <?php
        if ($user->shu_status != SelfHostUsers::STATUS_CONFIRMED) {
            $cache_key = 'shu-lock-resend-confirm-' . $user->shu_id;
            $lock = Yii::$app->cache->get($cache_key);
            if (!$lock) {
                ?>
                <button class="btn confirm-value js-open-form masterTooltip"
                        type="button"
                        data-src="#resend-confirm-modal" title="<?= Yii::t('user/profile', 'Confirm_masterTooltip') ?>">
                    <svg class="icon icon-warning">
                        <use xlink:href="#warning"></use>
                    </svg>
                    <span><?= Yii::t('user/profile', 'Confirm_email') ?></span>
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
    <p class="footnote" id="text-inform-for-delete-account"><?= Yii::t('user/profile', 'Delete_sh_account_inform') ?></p>
</div>
