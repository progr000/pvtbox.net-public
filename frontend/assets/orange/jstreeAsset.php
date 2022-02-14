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
class jstreeAsset extends AppAsset
{
    public $css = [
        'themes/orange/css/jstree/style.css',
        'themes/orange/css/jstree/jstree.css',
    ];

    public $js = [
        'themes/orange/js/jstree/jstree.my.js',
        'themes/orange/js/jstree/init.jstree.my.js',
    ];
}
