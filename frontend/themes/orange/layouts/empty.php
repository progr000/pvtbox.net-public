<?php

/* @var $this \yii\web\View */
/* @var $content string */
/* @var $user \common\models\Users */

use yii\helpers\Html;
use common\models\Licenses;
use common\models\Preferences;

$user = Yii::$app->user->identity;
$CountDaysTrialLicense = Licenses::getCountDaysTrialLicense();

/** Register al assets (js + css) */
$this->render('js-css-assets', ['user' => $user]);

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


<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
