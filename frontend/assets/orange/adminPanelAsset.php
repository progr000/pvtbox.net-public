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
class adminPanelAsset extends AppAsset
{
    public $css = [
        'themes/orange/css/jquery-ui.min.css',
    ];

    public $js = [
        'themes/orange/js/websocket.js',
        'themes/orange/js/check.nodes.online.js',
        'themes/orange/js/modal.profile_change.js',
        'themes/orange/js/admin.panel.js',
        'themes/orange/js/jquery-ui.min.js',
        'themes/orange/js/nicescroll/jquery.nicescroll.js',
    ];

    public $depends = [
        'frontend\assets\orange\AppAsset',
        'frontend\assets\orange\dateFormatAsset',
    ];
}
