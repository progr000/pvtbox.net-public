<?php

/* @var $this \yii\web\View */
/* @var $content string */
/* @var $user \common\models\Users */

use common\models\Licenses;
use common\models\Preferences;

/** init vars */
$user = Yii::$app->user->identity;
$CountDaysTrialLicense = Licenses::getCountDaysTrialLicense();
$static_action = Yii::$app->request->get('action', null);

/** Register all assets (js + css) */
$this->render('js-css-assets', ['user' => $user]);

/** load some params for site */
$no_lock_fm_action = (isset(Yii::$app->params['no_lock_fm_action']) && Yii::$app->params['no_lock_fm_action'])
    ? "no-lock-fm-action"
    : "";
$no_show_loader_for_site = (isset(Yii::$app->params['no_show_loader_for_site']) && Yii::$app->params['no_show_loader_for_site']);

/** start template */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <?= $this->render('head', [
        'no_show_loader_for_site' => $no_show_loader_for_site,
        'user' => $user,
    ]) ?>
</head>
<body class="loaded <?= $no_show_loader_for_site ? "" : "not-loaded-body" ?> <?= $no_lock_fm_action ?>"
      lang="<?= Yii::$app->language ?>"
      data-is-debug="<?= YII_DEBUG ? 1 : 0 ?>"
      data-default-lang="<?= Yii::$app->sourceLanguage ?>"
      data-cloua="<?= Preferences::getValueByKey('createLogOfUserAlerts', 1, 'integer') ?>"
      data-uid="<?= $user ? $user->user_id : "null" ?>"
      data-user-email="<?= $user ? $user->user_email : "null" ?>"
      data-license-type="<?= $user ? $user->license_type : "null" ?>">

<?php $this->beginBody() ?>

<?php
/* begin #loader-div */
if (!$no_show_loader_for_site && !isset(Yii::$app->params['page_without_loader'])) {
    echo $this->render('loader_div');
}
/* end #loader-div */
?>

<!-- begin .page #total-container-id-->
<div class="page page--main" id="total-container-id">

<?php
/* begin header of site */
if (Yii::$app->user->isGuest) {
    echo $this->render('header_guest', ['user' => $user, 'static_action' => $static_action]);
} else {
    echo $this->render('header_logged', ['user' => $user, 'static_action' => $static_action]);
}
/* end header of site */
?>


<!-- begin .alert-messages-->
<?= $this->render('alert_dialogs', ['user' => $user]) ?>
<!-- end .alert-messages-->


<?=
str_replace(
    [
        '{CountDaysTrialLicense}',
        '{APP_NAME}',
    ],
    [
        $CountDaysTrialLicense,
        Yii::$app->name,
    ],
    $content
) ?>


<?php
/* begin footer of site */
if (Yii::$app->user->isGuest) {
    echo $this->render('footer_guest', ['user' => $user, 'static_action' => $static_action]);
} else {
    echo $this->render('footer_logged', ['user' => $user, 'static_action' => $static_action]);
}
/* end footer of site */
?>

</div>
<!-- end .page #total-container-id-->


<!-- begin .popups-->
<?=
$this->render('modal', [
    //'form_login' => $this->context->model_login,
    'form_signup' => new \frontend\models\forms\SignupForm(),
    'form_request_reset' => new \frontend\models\forms\PasswordResetRequestForm(),
]);
?>
<!-- end .popups-->


<!-- begin service (javascript and to-top-button) -->
<?= $this->render('service', [
    'no_show_loader_for_site' => $no_show_loader_for_site,
    'user' => $user,
]); ?>
<!-- end service (javascript and to-top-button) -->

<?php $this->endBody() ?>
<div id="for-capture" class="hidden" style="display: none;"></div>
</body>
</html>
<?php $this->endPage() ?>
