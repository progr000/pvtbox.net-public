<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\assets\orange;

use yii;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class elfinderAsset extends AppAsset
{
    public $css = [
        'themes/orange/css/jquery-ui.min.css',
        'themes/orange/css/elfinder/base.css',
        'themes/orange/css/elfinder/theme.css',
    ];

    public $js = [
        'themes/orange/js/jquery-ui.min.js',
        'themes/orange/js/elfinder/elfinder.full.js',
        'themes/orange/js/websocket.js',
        'themes/orange/js/nicescroll/jquery.nicescroll.js',
        'themes/orange/js/check.nodes.online.js',
        'themes/orange/js/elfinder.init.js',
        'themes/orange/js/clipboard/clipboard.min.js',
        'themes/orange/js/dropzone.js',
        'themes/orange/js/dropzone.init.js',
        'themes/orange/js/detect.os.useragent.js',
        'themes/orange/js/bootstrap-show-password.min.js',
        'themes/orange/js/mimetypes.min.js',
        'themes/orange/js/p2p_bundle.min.js',
        'themes/orange/js/file.download.js',
    ];

    public $depends = [
        'frontend\assets\orange\AppAsset',
        'frontend\assets\orange\dateFormatAsset',
    ];

    public function init()
    {
        parent::init();

        $minimized = (isset(Yii::$app->params['use_minimized_css']) && Yii::$app->params['use_minimized_css'])
            ? ".min"
            : "";

        $filenameLang    = 'themes/orange/js/elfinder/i18n/elfinder.' . (Yii::$app->language) . '.js';
        $filenameDefault = 'themes/orange/js/elfinder/i18n/elfinder.LANG.js';

        if (file_exists(\Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . $filenameLang)) {
            $this->js[] = $filenameLang;
        } elseif (file_exists(\Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . $filenameDefault)) {
            $this->js[] = $filenameDefault;
        }
    }
}
