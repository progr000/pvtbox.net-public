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
class profileAsset extends AppAsset
{
    public $js = [
        'themes/orange/js/modal.profile_change.js',
        'themes/orange/js/logout.and.wipe.js',
    ];

    public $depends = [
        'frontend\assets\orange\AppAsset',
    ];
}
