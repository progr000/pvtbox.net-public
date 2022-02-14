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
class purchaseAsset extends AppAsset
{
    public $js = [
        'themes/orange/js/purchase_summary.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        //'frontend\assets\orange\JqueryAsset',
    ];
}
