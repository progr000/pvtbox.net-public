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
class modFolderDownloadAsset extends AppAsset
{
    public $js = [
        'themes/orange/js/folder.download.js',
    ];

    public $css = [
        'themes/orange/css/elfinder/base.css',
    ];

    public $depends = [
        'frontend\assets\orange\AppAsset',
    ];
}
