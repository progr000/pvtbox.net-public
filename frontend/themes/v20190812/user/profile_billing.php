<?php
/* @var $this yii\web\View */
/* @var $model \frontend\models\search\SessionsSearch */
/* @var $model_changeemail \frontend\models\forms\ChangeEmailForm */
/* @var $form \yii\widgets\ActiveForm */
/* @var $user \common\models\Users */
/* @var $dataProviderPayments \yii\data\ActiveDataProvider */

use yii\helpers\Url;
use yii\widgets\ListView;
use yii\widgets\Pjax;
use common\helpers\Functions;
use common\models\Licenses;
use common\models\Users;
use common\models\UserLicenses;
use common\models\UserServerLicenses;

/* Подготовка сумм */
$lock_change_link = false;

if (!in_array($user->license_type, [Licenses::TYPE_PAYED_BUSINESS_ADMIN, Licenses::TYPE_PAYED_PROFESSIONAL])) {
    $lock_change_link = true;
}
$isPayInitialized = $user->checkPaymentInitialized();
?>
<div class="table-wrap">
    <div class="table-wrap__inner">
        <table class="billing-tbl">
            <!-- Payment_method -->
            <tr>
                <td><?= Yii::t('user/billing', 'Payment_method') ?></td>
                <td><span class="highlight"><?= Yii::t('user/billing', $user->pay_type) ?></span></td>
                <td>
                    <?php
                    if ($user->license_type != Licenses::TYPE_PAYED_PROFESSIONAL) {
                        if ($lock_change_link) {
                            ?>
                            <a class="btn edit-value -masterTooltip"
                               data-off-type="button"
                               data-title-off="<?= Yii::t('user/billing', 'Contact_support') ?>"
                               href="<?= Url::to(['/pricing'], CREATE_ABSOLUTE_URL) ?>">
                                <svg class="icon icon-edit">
                                    <use xlink:href="#edit"></use>
                                </svg>
                                <span><?= Yii::t('user/billing', 'Change') ?></span>
                            </a>
                            <?php
                        } else {
                            ?>
                            <button class="btn edit-value masterTooltip -js-open-form" type="button"
                                    title="<?= Yii::t('user/billing', 'Contact_support') ?>"
                                    data-src-off="#change-pay-type">
                                <svg class="icon icon-edit">
                                    <use xlink:href="#edit"></use>
                                </svg>
                                <span><?= Yii::t('user/billing', 'Change') ?></span>
                            </button>
                            <?php
                        }
                    }
                    ?>
                </td>
            </tr>
            <!-- Billing_period -->
            <tr>
                <td><?= Yii::t('user/billing', 'Billing_period') ?></td>
                <td><span class="highlight"><?= Licenses::getBilledByPeriod($user->license_period, true) ?></span></td>
                <td>
                    <?php
                    if ($user->license_type != Licenses::TYPE_PAYED_PROFESSIONAL) {
                        if ($lock_change_link) {
                            ?>
                            <a class="btn edit-value -masterTooltip"
                               data-off-type="button"
                               data-title-off="<?= Yii::t('user/billing', 'Contact_support') ?>"
                               href="<?= Url::to(['/pricing'], CREATE_ABSOLUTE_URL) ?>">
                                <svg class="icon icon-edit">
                                    <use xlink:href="#edit"></use>
                                </svg>
                                <span><?= Yii::t('user/billing', 'Change') ?></span>
                            </a>
                            <?php
                        } else {
                            ?>
                            <button class="btn edit-value masterTooltip -js-open-form" type="button"
                                    title="<?= Yii::t('user/billing', 'Contact_support') ?>"
                                    data-src-off="#change-billing-period">
                                <svg class="icon icon-edit">
                                    <use xlink:href="#edit"></use>
                                </svg>
                                <span><?= Yii::t('user/billing', 'Change') ?></span>
                            </button>
                            <?php
                        }
                    }
                    ?>
                </td>
            </tr>
            <!-- Next pay date -->
            <tr>
                <td><?= Yii::t('user/billing', 'Next_pay') ?></td>
                <td>
                    <span class="highlight">
                        <?= $user->license_type == Licenses::TYPE_PAYED_PROFESSIONAL
                            ? Yii::t('user/billing', 'Never_unlimited_account')
                            : (
                            $user->license_expire
                                ? Functions::formatPostgresDate(Yii::$app->params['date_format'], $user->license_expire)
                                : Yii::t('user/billing', 'not_set')
                            )
                        ?>
                    </span>
                </td>
                <td>
                    <?php
                    if (!Users::isAutoPayType($user->pay_type)) {
                        if (Licenses::checkIsExpireSoon($user->license_expire) || Licenses::checkIsExpired($user->license_expire)) {
                            //echo '<a class="btn-default ' . ($isPayInitialized ? "btn-notActive" : '') . ' masterTooltip" href="' . ($isPayInitialized ? "#" : Url::to(['/purchase/renewal'], CREATE_ABSOLUTE_URL)) . '" title="' . ($isPayInitialized ? Yii::t('app/purchase', "already_initialized") : '') . '">' . Yii::t('user/billing', 'Renew_now') . '</a>';
                        }
                    } elseif (in_array($user->license_type, [Licenses::TYPE_FREE_DEFAULT, Licenses::TYPE_FREE_TRIAL])) {
                        echo '<a class="btn-min ' . ($isPayInitialized ? "btn-notActive" : '') . ' masterTooltip" href="' . ($isPayInitialized ? "#" : Url::to(['/pricing'], CREATE_ABSOLUTE_URL)) . '" title="' . ($isPayInitialized ? Yii::t('app/purchase', "already_initialized") : '') . '">' . Yii::t('user/billing', 'Purchase_now') . '</a>';
                    }
                    ?>
                </td>
            </tr>
            <!-- Number of purchased and available licenses -->
            <?php
            if ($user->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                $license_info = UserLicenses::getLicenseCountInfoForUser($user->user_id);
                $server_license_info = UserServerLicenses::getLicenseCountInfoForUser($user->user_id);
                ?>
            <tr>
                <td><?= Yii::t('user/billing', 'License_info') ?></td>
                <td>
                    <ul class="billing-stat">
                        <li><?= Yii::t('user/billing', 'Total') ?> <a href="<?= Url::to(['/admin-panel?tab=2'], CREATE_ABSOLUTE_URL) ?>"><?= $license_info['total'] ?></a></li>
                        <li><?= Yii::t('user/billing', 'Used') ?> <a href="<?= Url::to(['/admin-panel?tab=2'], CREATE_ABSOLUTE_URL) ?>"><?= $license_info['used'] ?></a></li>
                        <li><?= Yii::t('user/billing', 'Available') ?> <a href="<?= Url::to(['/admin-panel?tab=2'], CREATE_ABSOLUTE_URL) ?>"><?= $license_info['unused'] ?></a></li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td><?= Yii::t('user/billing', 'Server_license_info') ?></td>
                <td>
                    <ul class="billing-stat">
                        <li><?= Yii::t('user/billing', 'Total') ?> <a href="<?= Url::to(['/admin-panel?tab=4'], CREATE_ABSOLUTE_URL) ?>"><?= $server_license_info['total'] ?></a></li>
                        <li><?= Yii::t('user/billing', 'Used') ?> <a href="<?= Url::to(['/admin-panel?tab=4'], CREATE_ABSOLUTE_URL) ?>"><?= $server_license_info['used'] ?></a></li>
                        <li><?= Yii::t('user/billing', 'Available') ?> <a href="<?= Url::to(['/admin-panel?tab=4'], CREATE_ABSOLUTE_URL) ?>"><?= $server_license_info['unused'] ?></a></li>
                    </ul>
                </td>
            </tr>
                <?php
            }
            ?>
        </table>
    </div>
</div>
<!-- begin Payments list-table -->
<p class="billing-title"><?= Yii::t('user/billing', 'Payment_history') ?></p>
<div class="table-wrap">
    <div class="table-wrap__inner">
        <?php Pjax::begin(); ?>
        <?php
        $count = $dataProviderPayments->count;
        $lost = isset($dataProviderPayments->pagination->pageSize)
            ? $dataProviderPayments->pagination->pageSize - $count
            : 0;
        ?>
        <?=
        ListView::widget([
            'dataProvider' => $dataProviderPayments,
            'itemOptions' => [
                'tag' => false,
                'class' => '',
            ],
            'layout' => '
                <table class="history-tbl">
                    <thead>
                        <tr>
                            <th>' . Yii::t('user/billing', 'tblDate') . '</th>
                            <th>' . Yii::t('user/billing', 'tblSum') . '</th>
                            <th>' . Yii::t('user/billing', 'tblFor') . '</th>
                            <th>' . Yii::t('user/billing', 'tblType') . '</th>
                            <th>' . Yii::t('user/billing', 'tblStatus') . '</th>
                        </tr>
                    </thead>
                    <tbody>
                        {items}
                    </tbody>
                </table>
                {pager}',
            'emptyText' => $this->render('profile_billing_list_nodata'),
            'emptyTextOptions' => ['tag' => false],
            //'layout' => "{pager}\n{summary}\n{items}\n{pager}",
            //'summary' => 'Показано {count} из {totalCount}',
            'itemView' => function ($searchModelPayments, $key, $index, $widget) use ($lost, $count) {
                $lost_row = '';
                if ($lost>0 && ($index == $count - 1)) {
                    for ($i=1; $i<=$lost; $i++) {
                        $lost_row .= $this->render('profile_billing_list_item_empty');
                    }
                }
                /** @var $model \frontend\models\search\UserNodeSearch */
                return $this->render('profile_billing_list_item', ['searchModelPayments' => $searchModelPayments]) . $lost_row;
            },
        ]);
        ?>
        <?php Pjax::end(); ?>
    </div>
</div>
<!-- end Payments list-table -->
