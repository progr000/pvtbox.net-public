<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\assets\v20190812;

use yii;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class elfinderAsset extends MainExtendAsset
{

    public $css = [
    ];

    public $js = [
    ];

    public $depends = [
        'frontend\assets\v20190812\AppAsset',
        'frontend\assets\v20190812\dateFormatAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];

    public function init()
    {
        parent::init();

        $this->css = [
            $this->compressFile('themes/v20190812/css/vendors/jquery-ui.min.css', self::TYPE_CSS, 'vendors'),
            $this->compressFile('themes/v20190812/css/elfinder/base.css', self::TYPE_CSS, 'elfinder'),
            $this->compressFile('themes/v20190812/css/elfinder/reset.css', self::TYPE_CSS, 'elfinder'),
            $this->compressFile('themes/v20190812/css/elfinder/fonts.googleapis.com.css', self::TYPE_CSS, 'elfinder'),
            $this->compressFile('themes/v20190812/css/elfinder/main.css', self::TYPE_CSS, 'elfinder'),
            $this->compressFile('themes/v20190812/css/elfinder/icons.css', self::TYPE_CSS, 'elfinder'),
            $this->compressFile('themes/v20190812/css/elfinder/toolbar.css', self::TYPE_CSS, 'elfinder'),
            $this->compressFile('themes/v20190812/css/elfinder/navbar.css', self::TYPE_CSS, 'elfinder'),
            $this->compressFile('themes/v20190812/css/elfinder/view-list.css', self::TYPE_CSS, 'elfinder'),
            $this->compressFile('themes/v20190812/css/elfinder/view-thumbnail.css', self::TYPE_CSS, 'elfinder'),
            $this->compressFile('themes/v20190812/css/elfinder/contextmenu.css', self::TYPE_CSS, 'elfinder'),
            $this->compressFile('themes/v20190812/css/elfinder/dialog.css', self::TYPE_CSS, 'elfinder'),
            $this->compressFile('themes/v20190812/css/elfinder/statusbar.css', self::TYPE_CSS, 'elfinder'),
            $this->compressFile('themes/v20190812/css/elfinder/manager.css', self::TYPE_CSS, 'elfinder'),
            $this->compressFile('themes/v20190812/css/elfinder/progress.css', self::TYPE_CSS, 'elfinder'),
            $this->compressFile('themes/v20190812/css/elfinder/preview-popup.css', self::TYPE_CSS, 'elfinder'),
        ];

        $this->js = [
            $this->compressFile('themes/v20190812/js/jquery/jquery-ui.min.js', self::TYPE_JS, 'jquery'),
            $this->compressFile('themes/v20190812/js/jquery/jquery.nicescroll.min.js', self::TYPE_JS, 'jquery'),
            $this->compressFile('themes/v20190812/js/vendors/websocket.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/vendors/check.nodes.online.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/vendors/detect.os.useragent.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/vendors/bootstrap-show-password.min.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/vendors/mimetypes.min.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/vendors/resend.invite.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/clipboard/clipboard.min.js', self::TYPE_JS, 'clipboard'),
            $this->compressFile('themes/v20190812/js/preview-download/p2p_bundle.min.js', self::TYPE_JS, 'preview-download'),
            $this->compressFile('themes/v20190812/js/elfinder/dropzone.js', self::TYPE_JS, 'elfinder'),
            $this->compressFile('themes/v20190812/js/elfinder/dropzone.init.js', self::TYPE_JS, 'elfinder'),
            $this->compressFile('themes/v20190812/js/elfinder/elfinder.full.js', self::TYPE_JS, 'elfinder'),
            $this->compressFile('themes/v20190812/js/elfinder/elfinder.init.js', self::TYPE_JS, 'elfinder'),
            $this->compressFile('themes/v20190812/js/elfinder/elfinder.share.js', self::TYPE_JS, 'elfinder'),
            $this->compressFile('themes/v20190812/js/elfinder/elfinder.collaborate.js', self::TYPE_JS, 'elfinder'),
            $this->compressFile('themes/v20190812/js/elfinder/elfinder.fileversions.js', self::TYPE_JS, 'elfinder'),
            $this->compressFile('themes/v20190812/js/preview-download/file.download.js', self::TYPE_JS, 'preview-download'),
            $this->compressFile('themes/v20190812/js/preview-download/file.preview.js', self::TYPE_JS, 'preview-download'),
        ];

        $filenameLang    = 'themes/v20190812/js/elfinder/i18n/elfinder.' . (Yii::$app->language) . '.js';
        $filenameDefault = 'themes/v20190812/js/elfinder/i18n/elfinder.LANG.js';

        if (file_exists(\Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . $filenameLang)) {
            $this->js[] = $this->compressFile($filenameLang, self::TYPE_JS, 'elfinder/i18n');
        } elseif (file_exists(\Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . $filenameDefault)) {
            $this->js[] = $this->compressFile($filenameDefault, self::TYPE_JS, 'elfinder/i18n');
        }
    }
}
