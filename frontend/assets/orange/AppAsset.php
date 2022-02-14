<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\assets\orange;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    //public $sourcePath = '@frontend/themes/orange/assets';
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    /**
     * Сжать файлы стилей можно тут:
     * https://cssresizer.com/
     * http://refresh-sf.com/  (вроде тут лучше)
     */

    public $css = [

    ];

    public $js = [
        //'themes/orange/js/bootstrap.min.js',
        'themes/orange/js/log.js',
        'themes/orange/js/modal.centered.js',
        'themes/orange/js/main_dizaynerskiy.js',
        'themes/orange/js/jquery.scrollbar.min.js',
        'themes/orange/js/jquery.cookie.js',
        'themes/orange/js/alert.divs.js',
        'themes/orange/js/html2canvas.min.js',
        'themes/orange/js/detect.os.useragent.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        //'frontend\assets\orange\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'frontend\assets\orange\MainCssAsset',
    ];
}
