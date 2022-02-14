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
class UsersAsset extends AppAsset
{
    public $js = [
        'js/users.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
