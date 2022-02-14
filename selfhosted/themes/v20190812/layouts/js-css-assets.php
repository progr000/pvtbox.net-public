<?php

/* @var $this \yii\web\View */
/* @var $content string */
/* @var $user \common\models\Users */

use yii\web\View;
use selfhosted\assets\v20190812\MainCssAsset;
use selfhosted\assets\v20190812\AppAsset;
use selfhosted\assets\v20190812\guestAsset;

/* assets */
MainCssAsset::register($this);
AppAsset::register($this);

$str_js = "\n";
if (Yii::$app->user->isGuest) {
    /* Assets для гостей */
    $str_js .= "var IS_GUEST = true;\n";
    guestAsset::register($this);
} else {
    /* Генерация яваскриптовых объектов */
    /* Формат дат */
    $str_js .= "var IS_GUEST = false;\n";
}
/* регистрируем яваскрипт */
$this->registerJs($str_js, View::POS_END);

