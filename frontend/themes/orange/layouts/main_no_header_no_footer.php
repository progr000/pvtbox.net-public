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

/** Register al assets (js + css) */
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
      data-default-lang="<?= Yii::$app->sourceLanguage ?>"
      data-cloua="<?= Preferences::getValueByKey('createLogOfUserAlerts', 1, 'integer') ?>">

<?php $this->beginBody() ?>

    <div class="total-container <?= Yii::$app->user->isGuest ? "total-container--indent" : "" ?>" id="total-container-id">


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



<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
