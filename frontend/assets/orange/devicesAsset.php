<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\assets\orange;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class devicesAsset extends AppAsset
{
    public $css = [
        'themes/orange/css/jquery-ui.min.css',
    ];

    public $js = [
        'themes/orange/js/websocket.js',
        'themes/orange/js/devices.js',
        'themes/orange/js/logout.and.wipe.js',
        'themes/orange/js/nicescroll/jquery.nicescroll.js',
        'themes/orange/js/client.detection.js',
    ];

    public $depends = [
        'frontend\assets\orange\AppAsset',
    ];
}
