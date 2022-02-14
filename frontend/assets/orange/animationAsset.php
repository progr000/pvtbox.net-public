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
class animationAsset extends AppAsset
{
    public $css = [

    ];
    public $js = [
        'themes/orange/js/animation/createjs-2015.11.26.min.js',
        'themes/orange/js/animation/privateBox_v3.js',
        'themes/orange/js/animation/animation.js',
    ];
    public $depends = [
        'frontend\assets\orange\AppAsset',
    ];
}
