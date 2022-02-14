<?php

/* @var $this \yii\web\View */
/* @var $content string */
/* @var $user \common\models\Users */

use yii\web\View;
use common\models\Licenses;
//use cakebake\bootstrap\select\BootstrapSelectAsset;
use frontend\assets\orange\BootstrapSelectAsset;
use frontend\assets\orange\MainCssAsset;
use frontend\assets\orange\AppAsset;
use frontend\assets\orange\dateFormatAsset;
use frontend\assets\orange\guestAsset;
use frontend\assets\orange\registeredAsset;

/* assets */
MainCssAsset::register($this);
AppAsset::register($this);

$str_js = "\n";
//Откоментировать эти строку, если вдруг захотим нормальное время юзеру показывать его локальное не зависимо от выбранной им таймзоны
/*
$str_js = "
var d = new Date();
var loc = Date.UTC(d.getFullYear(), d.getMonth(), d.getDate(), d.getHours(), d.getMinutes(), d.getSeconds());
var TIMEZONE_OFFSET_SECONDS = parseInt( ((loc/1000 - " .time(). ")/60).toFixed(0) ) * 60;
";
*/
if (Yii::$app->user->isGuest) {
    /* Assets для гостей */
    $str_js .= "var IS_GUEST = true;\n";
    guestAsset::register($this);
} else {
    /* Генерация яваскриптовых объектов */
    /* Формат дат */
    $str_js .= "var IS_GUEST = false;\n";
    $str_js .= "var USER_STATIC_TIMEZONE = {$user->static_timezone};\n";
    $str_js .= "var formDate;
    var _GLOBAL =  {
        'now' : '" . date('d-m-Y H:i:s', time() + Yii::$app->session->get('UserTimeZoneOffset', 0)) . "',
        'UserTimeZoneOffset' : " . Yii::$app->session->get('UserTimeZoneOffset', 0) . ",
        //'today' : ". ( mktime(0,0,0, date('m'), date('d'), date('Y')) + Yii::$app->session->get('UserTimeZoneOffset', 0) ) . ",
        'today' : ". ( mktime(0,0,0, date('m'), date('d'), date('Y')) ) . ",
        'date_format' : '" . Yii::$app->params['date_format'] . "',
        'datetime_format' : '" . Yii::$app->params['datetime_format'] . "',
        'datetime_short_format' : '" . Yii::$app->params['datetime_short_format'] . "',
        'datetime_fancy_format' : '" . Yii::$app->params['datetime_fancy_format'] . "',
    };\n";

    /* типы лицензий */
    $licenseTypes = Licenses::licenseTypes();
    $str_js .= "var UserLicense = '{$user->license_type}';\n\n";
    $str_js .= "var LicenseTypes = {\n";
    foreach ($licenseTypes as $k => $v) {
        $str_js .= $k . ':"' . $v . '",'. "\n";
    }
    $str_js .= "};\n\n";

    /* Assets для зарегистрированных */
    registeredAsset::register($this);
    dateFormatAsset::register($this);
}
/* регистрируем яваскрипт */
$this->registerJs($str_js, View::POS_END);

BootstrapSelectAsset::register($this);
$this->registerCss(".bootstrap-select .dropdown-toggle:focus { outline: none !important; }");
