<?php
/* @var $this yii\web\View */
/* @var $model \frontend\models\search\SessionsSearch */
/* @var $model_changetimezone \frontend\models\forms\SetTimeZoneOffsetForm */
/* @var $user \common\models\Users */
/* @var $searchModelPayments \frontend\models\search\UserPaymentsSearch */
/* @var $dataProviderSession \yii\data\ActiveDataProvider */
/* @var $dataProviderPayments \yii\data\ActiveDataProvider */

use selfhosted\assets\v20190812\profileAsset;

/* assets */
profileAsset::register($this);

/* */
$this->title = Yii::t('user/profile', 'title');

$user = Yii::$app->user->identity;

$tab = Yii::$app->request->get('tab', 1);
$tab_array = [1, 2];
if (!in_array($tab, $tab_array)) {
    $tab = 1;
}

?>
<!-- end Profile-page content -->
<div class="content container">
    <h1>Profile</h1>
    <div class="profile tabs-wrap">
        <ul class="tabs js-param-tabs real-tabs">
            <li class="tabs__item <?= ($tab == 1) ? "active" : "" ?>"
                data-tab="1"><?= Yii::t('user/profile', 'Account') ?></li>
            <li class="tabs__item <?= ($tab == 2) ? "active" : "" ?>"
                data-tab="2"><?= Yii::t('user/profile', 'Billing') ?></li>
        </ul>
        <div class="tabs-content">
            <!-- begin Profile-Account tab-content -->
            <div class="box <?= ($tab == 1) ? "visible" : "" ?>">

                <?= $this->render('profile_account', [
                    'user'                 => $user,
                    'model_changetimezone' => $model_changetimezone,
                ]) ?>

            </div>
            <!-- end Profile-Account tab-content -->

            <!-- begin Profile-Billing tab-content -->
            <div class="box <?= ($tab == 2) ? "visible" : "" ?>">

                <?= $this->render('profile_billing', [
                    'user' => $user,
                ]) ?>

            </div>
            <!-- end Profile-Billing tab-content -->

        </div>
    </div>
</div>
<!-- end Profile-page content -->

<!-- begin Profile-modal content -->
<?= $this->render('profile_modal', [
    'user' => $user,
    'model_changetimezone' => $model_changetimezone,
]) ?>
<!-- end Profile-modal content -->
