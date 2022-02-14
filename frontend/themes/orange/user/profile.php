<?php
/* @var $this yii\web\View */
/* @var $model \frontend\models\search\SessionsSearch */
/* @var $model_changeemail \frontend\models\forms\ChangeEmailForm */
/* @var $model_changetimezone \frontend\models\forms\SetTimeZoneOffsetForm */
/* @var $user \common\models\Users */
/* @var $searchModelPayments \frontend\models\search\UserPaymentsSearch */
/* @var $dataProviderSession \yii\data\ActiveDataProvider */
/* @var $dataProviderPayments \yii\data\ActiveDataProvider */

use common\models\Licenses;
use frontend\assets\orange\profileAsset;

/* assets */
profileAsset::register($this);

/* */
$this->title = Yii::t('user/profile', 'title');

$user = Yii::$app->user->identity;

$tab = Yii::$app->request->get('tab', 1);
$show_billing = !in_array($user->license_type, [Licenses::TYPE_PAYED_BUSINESS_USER]);
if ($show_billing) {
    $tab_array = [1, 2];
} else {
    $tab_array = [1];
}
if (!in_array($tab, $tab_array)) {
    $tab = 1;
}

?>

<!-- .tables -->
<div class="tables">

    <div class="tables__cont">

        <div class="tabBlock">
            <ul class="tabBlock-list">
                <li class="<?= ($tab == 1) ? "active" : "" ?>" data-tab="1"><span><?= Yii::t('user/profile', 'Account') ?></span></li>
                <?php
                if ($show_billing) {
                    ?>
                    <li class="<?= ($tab == 2) ? "active" : "" ?>" data-tab="2"><span><?= Yii::t('user/profile', 'Billing') ?></span></li>
                    <?php
                }
                ?>
            </ul>
        </div>


        <div class="tabBlock-content">


            <!-- .tabBlock-content__box (profile_account) -->
            <div class="tabBlock-content__box <?= ($tab == 1) ? "active" : "" ?>">

                <?= $this->render('profile_account', [
                    'user'                 => $user,
                    'model_changeemail'    => $model_changeemail,
                    'model_changetimezone' => $model_changetimezone,
                ]) ?>

            </div>
            <!-- END .tabBlock-content__box (profile_account) -->



            <?php
            if ($show_billing) {
                ?>
                <!-- .tabBlock-content__box (profile_billing) -->
                <div class="tabBlock-content__box <?= ($tab == 2) ? "active" : "" ?>">

                    <?= $this->render('profile_billing', [
                        'user' => $user,
                        'searchModelPayments' => $searchModelPayments,
                        'dataProviderPayments' => $dataProviderPayments,
                    ]) ?>

                </div>
                <!-- END .tabBlock-content__box (profile_billing) -->
                <?php
            }
            ?>

        </div>


    </div>

</div>
<!-- END .tables -->
