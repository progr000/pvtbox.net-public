<?php

/* @var $this \yii\web\View */
/* @var $content string */
/* @var $user \common\models\Users */

use yii\helpers\Html;
use common\models\Licenses;
use common\models\Preferences;

/** init vars */
$user = Yii::$app->user->identity;
$CountDaysTrialLicense = Licenses::getCountDaysTrialLicense();
$static_action = Yii::$app->request->get('action', "null");

/** Register all assets (js + css) */
$this->render('js-css-assets', ['user' => $user]);

/** start template */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <?= $this->render('head'); ?>
</head>
<body lang="<?= Yii::$app->language ?>"
      data-is-debug="<?= YII_DEBUG ? 1 : 0 ?>"
      data-default-lang="<?= Yii::$app->sourceLanguage ?>"
      data-cloua="<?= Preferences::getValueByKey('createLogOfUserAlerts', 1, 'integer') ?>">

<?php $this->beginBody() ?>

    <div class="total-container <?= Yii::$app->user->isGuest ? "total-container--indent" : "" ?>" id="total-container-id">

        <!-- .header -->
        <?php
        if (Yii::$app->user->isGuest) {
            echo $this->render('header_guest', ['user' => $user, 'static_action' => $static_action]);
            echo $this->render('alert_dialogs', ['user' => $user]);
        } else {
            echo $this->render('header_logged', ['user' => $user, 'static_action' => $static_action]);
        }
        ?>
        <!-- END .header -->


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


    <!-- .footer -->
    <?php
    if (Yii::$app->user->isGuest) {
        echo $this->render('footer_guest', ['user' => $user, 'static_action' => $static_action]);
    } else {
        echo $this->render('footer_logged', ['user' => $user, 'static_action' => $static_action]);
    }
    ?>
    <!-- END .footer -->


<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
