<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\assets\orange;

use common\helpers\Functions;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class modPreviewAsset extends AppAsset
{
    public $css = [
        'themes/orange/css/jquery-ui.min.css',
        'themes/orange/css/registered.min.css',
    ];

    public $js = [
        'themes/orange/js/jquery-ui.min.js',
        'themes/orange/js/log.js',
        'themes/orange/js/p2p_bundle.min.js',
        'themes/orange/js/web_tracking.min.js',
        'themes/orange/js/mimetypes.min.js',
        'themes/orange/js/get_file_start.js',
        'themes/orange/js/preview.init.js',
        'themes/orange/js/nicescroll/jquery.nicescroll.js',
    ];

    public $depends = [
        'frontend\assets\orange\AppAsset',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!Functions::isIE()) {
            $this->js[] = 'themes/orange/js/get_file_main.js';
        }
    }
}
