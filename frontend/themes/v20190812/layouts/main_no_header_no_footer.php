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
$no_show_loader_for_site = (isset(Yii::$app->params['no_show_loader_for_site']) && Yii::$app->params['no_show_loader_for_site']);

/** start template */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <?= $this->render('head', [
        'user' => $user,
        'no_show_loader_for_site' => $no_show_loader_for_site
    ]); ?>
</head>
<body class="loaded"
      lang="<?= Yii::$app->language ?>"
      data-is-debug="<?= YII_DEBUG ? 1 : 0 ?>"
      data-default-lang="<?= Yii::$app->sourceLanguage ?>"
      data-cloua="<?= Preferences::getValueByKey('createLogOfUserAlerts', 1, 'integer') ?>"
      data-uid="<?= $user ? $user->user_id : "null" ?>">

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

        <div class="hFooter"></div>

</div>

<!-- begin service (javascript and to-top-button) -->
<?= $this->render('service', [
    'no_show_loader_for_site' => $no_show_loader_for_site,
    'user' => $user,
]); ?>
<!-- end service (javascript and to-top-button) -->

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
