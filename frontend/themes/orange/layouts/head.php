<?php

/* @var $this \yii\web\View */

use yii\helpers\Html;
use common\models\Preferences;
?>

<!-- begin meta block -->
<meta charset="<?= Yii::$app->charset ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=0, maximum-scale=5" />
<meta name="description" content="<?= Yii::$app->name . " - " . Html::encode($this->title) ?>">
<?= Preferences::getValueByKey('seoAdditionalMetaTagsAll') ?>

<?= (Yii::$app->user->isGuest) ? Preferences::getValueByKey('seoAdditionalMetaTagsGuest') : Preferences::getValueByKey('seoAdditionalMetaTagsMember') ?>

<?= Html::csrfMetaTags() ?>
<!-- end meta block -->

<title><?= Yii::$app->name . " - " . Html::encode($this->title) ?></title>
<?php
$str_path_canonical = trim(htmlspecialchars(strip_tags(Yii::$app->request->getPathInfo())));
$str_path_canonical = $str_path_canonical == "" ? "" : "/" . $str_path_canonical;
?>

<!-- begin favicon block -->
<link rel="canonical" href="https://pvtbox.net<?= $str_path_canonical ?>" />
<?= $this->render('favicon') ?>
<!-- end favicon block -->

<?= (Yii::$app->user->isGuest) ? $this->render('critical_css') : "" ?>


<!-- begin css block -->
<?php $this->head() ?>

<style id="fileNameStyle"></style>
<!-- end css block -->
