<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\assets;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RegisteredAsset extends AppAsset
{
    public $js = [
        'js/main.js',
        'js/tikets.main.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
