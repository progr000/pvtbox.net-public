<?php
/* @var $this yii\web\View */
/* @var $model \frontend\models\search\SessionsSearch */
/* @var $model_changeemail \frontend\models\forms\ChangeEmailForm */
/* @var $form \yii\widgets\ActiveForm */
/* @var $user \common\models\SelfHostUsers */
/* @var $dataProviderPayments \yii\data\ActiveDataProvider */

use yii\helpers\Url;
use common\helpers\Functions;
use common\models\Licenses;
use common\models\Users;
use common\models\UserLicenses;
use common\models\UserServerLicenses;

?>
<div class="table-wrap">
    <div class="table-wrap__inner">
        <table class="billing-tbl">
            <!-- Payment_method -->
            <tr>
                <td><?= Yii::t('user/billing', 'Payment_method') ?></td>
                <td><span class="highlight"><?= Yii::t('user/billing', $user->pay_type) ?></span></td>
                <td>
                    <button class="btn edit-value masterTooltip -js-open-form" type="button"
                            title="<?= Yii::t('user/billing', 'Contact_support') ?>"
                            data-src-off="#change-pay-type">
                        <svg class="icon icon-edit">
                            <use xlink:href="#edit"></use>
                        </svg>
                        <span><?= Yii::t('user/billing', 'Change') ?></span>
                    </button>
                </td>
            </tr>
            <!-- Billing_period -->
            <tr>
                <td><?= Yii::t('user/billing', 'Billing_period') ?></td>
                <td><span class="highlight"><?= Licenses::getBilledByPeriod($user->license_period, true) ?></span></td>
                <td>
                    <button class="btn edit-value masterTooltip -js-open-form" type="button"
                            title="<?= Yii::t('user/billing', 'Contact_support') ?>"
                            data-src-off="#change-billing-period">
                        <svg class="icon icon-edit">
                            <use xlink:href="#edit"></use>
                        </svg>
                        <span><?= Yii::t('user/billing', 'Change') ?></span>
                    </button>
                </td>
            </tr>
            <!-- Next pay date -->
            <tr>
                <td><?= Yii::t('user/billing', 'Next_pay') ?></td>
                <td>
                    <span class="highlight">
                        <?=
                            $user->license_expire
                                ? Functions::formatPostgresDate(Yii::$app->params['date_format'], $user->license_expire)
                                : Yii::t('user/billing', 'not_set')

                        ?>
                    </span>
                </td>
                <td>
                </td>
            </tr>
            <!-- Support status pay date -->
            <tr>
                <td><?= Yii::t('user/billing', 'Support_status') ?></td>
                <td>
                    <span class="highlight">
                        <?=
                        $user->shu_support_status
                            ? Yii::t('user/billing', 'Yes')
                            : Yii::t('user/billing', 'No')
                        ?>
                    </span>
                </td>
                <td>
                    <?php
                    if (!$user->shu_support_requested && !$user->shu_support_status) {
                        ?>
                        <a class="btn edit-value masterTooltip"
                           title="<?= Yii::t('forms/login-signup-form', 'shu_support_status') ?>"
                           href="<?= Url::to(['/user/request-support'], CREATE_ABSOLUTE_URL)?>">
                            <svg class="icon icon-edit">
                                <use xlink:href="#edit-"></use>
                            </svg>
                            <span><?= Yii::t('forms/login-signup-form', 'shu_support_status') ?></span>
                        </a>
                        <?php
                    } elseif ($user->shu_support_requested && !$user->shu_support_status) {
                        ?>
                        <a class="btn edit-value masterTooltip void-0"
                           title="<?= Yii::t('forms/login-signup-form', 'Request_was_sent') ?>"
                           href="#">
                            <svg class="icon icon-edit">
                                <use xlink:href="#edit-"></use>
                            </svg>
                            <span><?= Yii::t('forms/login-signup-form', 'Request_was_sent') ?></span>
                        </a>
                        <?php
                    } else {
                        ?>

                        <?php
                    }
                    ?>
                </td>
            </tr>
            <!-- Brand status pay date -->
            <tr>
                <td><?= Yii::t('user/billing', 'Brand_status') ?></td>
                <td>
                    <span class="highlight">
                        <?=
                        $user->shu_brand_status
                            ? Yii::t('user/billing', 'Yes')
                            : Yii::t('user/billing', 'No')
                        ?>
                    </span>
                </td>
                <td>
                    <?php
                    if (!$user->shu_brand_requested && !$user->shu_brand_status) {
                        ?>
                        <a class="btn edit-value masterTooltip"
                           title="<?= Yii::t('forms/login-signup-form', 'shu_brand_status') ?>"
                           href="<?= Url::to(['/user/request-brand'], CREATE_ABSOLUTE_URL)?>">
                            <svg class="icon icon-edit">
                                <use xlink:href="#edit-"></use>
                            </svg>
                            <span><?= Yii::t('forms/login-signup-form', 'shu_brand_status') ?></span>
                        </a>
                        <?php
                    } elseif ($user->shu_brand_requested && !$user->shu_brand_status) {
                        ?>
                        <a class="btn edit-value masterTooltip void-0"
                           title="<?= Yii::t('forms/login-signup-form', 'Request_was_sent') ?>"
                           href="#">
                            <svg class="icon icon-edit">
                                <use xlink:href="#edit-"></use>
                            </svg>
                            <span><?= Yii::t('forms/login-signup-form', 'Request_was_sent') ?></span>
                        </a>
                        <?php
                    } else {
                        ?>

                        <?php
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>
</div>

