<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content-vw">
 *
 * @package Pvtbox
 */
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../../../../../../vendor/autoload.php');
require(__DIR__ . '/../../../../../../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../../../../../../common/config/bootstrap.php');
require(__DIR__ . '/../../../../../config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../../../../../common/config/main.php'),
    require(__DIR__ . '/../../../../../../common/config/main-local.php'),
    require(__DIR__ . '/../../../../../config/main.php'),
    require(__DIR__ . '/../../../../../config/main-local.php')
);

//unset($config['components']['urlManager']);
$application = new yii\web\Application($config);
Yii::$app->controller = new \yii\base\Controller('blog', new yii\base\Module('test'));
defined( 'THEME_PATH' ) or define( 'THEME_PATH', realpath(__DIR__ . "/../../../../../themes/" . DESIGN_THEME) );
//echo $application->view->renderPhpFile(__DIR__ . "/../../../../../themes/" . DESIGN_THEME . "/layouts/cookie_and_badge.php");

/** @var \common\models\Users $user */
$user = Yii::$app->user->identity;
$static_action = Yii::$app->request->get('action', null);

use yii\helpers\Html;
use yii\web\JqueryAsset;
use common\models\Preferences;
use common\models\Maintenance;
use frontend\assets\v20190812\AppAsset;
use frontend\assets\v20190812\guestAsset;

$view = new yii\web\View();
JqueryAsset::register($view);
AppAsset::register($view);
if (!$user) {
    guestAsset::register($view);
}

$jsAssets = [];
$cssAssets = [];
$cssAssets[] = '/blog/wp-content/themes/v20190812/blog.css';
//$bundles = $view->assetBundles['yii\web\JqueryAsset'];
foreach ($view->assetBundles as $bundles) {
    foreach ($bundles->js as $j) {
        $jsAssets[] = str_replace('/blog', '', $bundles->baseUrl) . "/" . $j;
    }
    foreach ($bundles->css as $c) {
        $cssAssets[] = str_replace('/blog', '', $bundles->baseUrl) . "/" . $c;
    }
}
Yii::$app->params['jsAssets'] = $jsAssets;
//$js[] = $view->assetBundles['yii\web\JqueryAsset']->baseUrl . "/" . $view->assetBundles['yii\web\JqueryAsset']->js;
//var_dump($jsAssets);
//var_dump($cssAssets);
//var_dump($view->assetBundles);exit;

/** Maintenance */
$Maintenance = Maintenance::getMaintenance();
if ($Maintenance->maintenance_suspend_blog) {

    if ($Maintenance->maintenance_show_empty_page) {
        Maintenance::maintenanceFlash($Maintenance);
        //var_dump(THEME_PATH);
        //Yii::$app->controller->setViewPath(THEME_PATH . '/../../');
        Yii::$app->controller->layout = '/../../themes/' . DESIGN_THEME . '/layouts/maintenance';
        //var_dump(Yii::$app->controller->getViewPath());
        echo str_replace(
            '/blog/assets/',
            '/assets/',
            Yii::$app->controller->render('/../../../../../frontend/themes/' . DESIGN_THEME . '/site/maintenance')
        );
        exit;
    }

    if ($Maintenance->maintenance_suspend_site &&
        !$Maintenance->maintenance_can_login &&
        !Yii::$app->user->isGuest)
    {
        $cookies = Yii::$app->response->cookies;
        $cookies->remove('_identity');
        setcookie('_identity', null, null, '/');
        Yii::$app->user->logout();
        Yii::$app->response->redirect(['/blog']);
    }

    Maintenance::maintenanceFlash($Maintenance);

} else {
    Yii::$app->session->removeFlash($Maintenance->maintenance_type . '-maintenance');
}

/**
 * @param $file_name
 * @param array $_params_
 */
function render($file_name, $_params_ = [])
{
    extract($_params_, EXTR_OVERWRITE);

    $inc = THEME_PATH . "/" . $file_name;
    if (file_exists($inc)) {
        require_once($inc);
        return;
    }

    $inc = THEME_PATH . "/" . $file_name . ".php";
    if (file_exists($inc)) {
        require_once($inc);
        return;
    }

    $inc = THEME_PATH . "/" . $file_name . ".html";
    if (file_exists($inc)) {
        require_once($inc);
        return;
    }

}

/**
 * @param $file_name
 * @return string
 */
function scriptsNoCache($file_name)
{
    $test = $_SERVER['DOCUMENT_ROOT'] . $file_name;
    if (file_exists($test)) {
        return $file_name . "?v=" . filemtime($test);
    } else {
        return $file_name . "?v=" . uniqid();
    }
}

/** load some params for site */
$no_show_loader_for_site = (isset(Yii::$app->params['no_show_loader_for_site']) && Yii::$app->params['no_show_loader_for_site']);

?><!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>

    <!-- begin meta block -->
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, initial-scale=1.0">

    <?= Preferences::getValueByKey('seoAdditionalMetaTagsAll') ?>

    <?= (Yii::$app->user->isGuest) ? Preferences::getValueByKey('seoAdditionalMetaTagsGuest') : Preferences::getValueByKey('seoAdditionalMetaTagsMember') ?>

    <?= Html::csrfMetaTags() ?>
    <!-- end meta block -->

    <?php
    if (!$no_show_loader_for_site) {
        render('layouts/loader_css', ['no_show_loader_for_site' => $no_show_loader_for_site]);
    }
    ?>

    <!-- begin css block -->
    <?php foreach ($cssAssets as $c) {
        echo '<link href="' . scriptsNoCache($c) . '" rel="stylesheet">' . "\n";
    }?>
    <style id="fileNameStyle"></style>
    <!-- end css block -->

    <!-- begin wp-meta-block -->
    <?php wp_head(); ?>
    <!-- end wp-meta-block -->


    <!-- begin favicon block -->
    <?php render('layouts/favicon'); ?>
    <!-- end favicon block -->

    <!-- begin #critical-css (Put here most critical css code. It will be loaded firstly) -->
    <style id="critical-css"></style>
    <!-- end #critical-css -->

</head>
<body class="loaded"
      lang="<?= Yii::$app->language ?>"
      data-is-debug="<?= YII_DEBUG ? 1 : 0 ?>"
      data-default-lang="<?= Yii::$app->sourceLanguage ?>"
      data-cloua="<?= Preferences::getValueByKey('createLogOfUserAlerts', 1, 'integer') ?>"
      data-uid="<?= $user ? $user->user_id : "null" ?>">


<?php
if (!$no_show_loader_for_site) {
    render('layouts/loader_div');
}
?>

<!-- begin .page #total-container-id-->
<div class="page" id="total-container-id">

<?php
Yii::$app->user->isGuest
    ? render('layouts/header_guest', ['user' => $user, 'static_action' => $static_action])
    : render('layouts/header_logged', ['user' => $user, 'static_action' => $static_action]);

render('layouts/alert_dialogs', ['user' => $user]);
