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
class daterangepickerAsset extends AppAsset
{
    public $css = [
        'themes/orange/css/daterangepicker.css',
    ];

    public $js = [
    ];

    public $depends = [
        'kartik\daterange\MomentAsset',
        'kartik\daterange\DateRangePickerAsset',
    ];
}
