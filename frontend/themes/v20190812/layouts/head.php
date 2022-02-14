<?php

/* @var $this \yii\web\View */
/* @var $no_show_loader_for_site bool */

use yii\helpers\Html;
use common\models\Preferences;
?>

<!-- begin meta block -->
<meta charset="<?= Yii::$app->charset ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, initial-scale=1.0">
<meta name="description" content="<?= Html::encode($this->title) . " | Private cloud software by Pvtbox" ?>">
<?= Preferences::getValueByKey('seoAdditionalMetaTagsAll') ?>

<?= (Yii::$app->user->isGuest) ? Preferences::getValueByKey('seoAdditionalMetaTagsGuest') : Preferences::getValueByKey('seoAdditionalMetaTagsMember') ?>

<?= Html::csrfMetaTags() ?>
<!-- end meta block -->

<title><?= Html::encode($this->title) . " | Private cloud software by Pvtbox" ?></title>
<?php
$str_path_canonical = trim(htmlspecialchars(strip_tags(Yii::$app->request->getPathInfo())));
$str_path_canonical = $str_path_canonical == "" ? "" : "/" . $str_path_canonical;
?>

<!-- begin favicon block -->
<link rel="canonical" href="<?= Yii::getAlias('@frontendWeb') ?><?= $str_path_canonical ?>" />
<?= $this->render('favicon') ?>
<!-- end favicon block -->

<?php
if (!$no_show_loader_for_site) {
    echo $this->render('loader_css', ['no_show_loader_for_site' => $no_show_loader_for_site]);
}
?>

<?= $this->render('critical_css') ?>


<!-- begin css block -->
<?php $this->head() ?>

<style id="fileNameStyle"></style>
<!-- end css block -->
