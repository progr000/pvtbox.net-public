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
class modFileDownloadAsset extends MainExtendAsset
{

    public $css = [
    ];

    public $js = [
    ];

    public $depends = [
        //'yii\web\JqueryAsset',
        //'frontend\assets\v20190812\MainCssAsset',
        'frontend\assets\v20190812\AppAsset',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->css = [
            $this->compressFile('themes/v20190812/css/elfinder/progress.css', self::TYPE_CSS, 'elfinder'),
            $this->compressFile('themes/v20190812/css/elfinder/shared-file.css', self::TYPE_CSS, 'elfinder'),
            $this->compressFile('themes/v20190812/css/elfinder/preview-share.css', self::TYPE_CSS, 'elfinder'),
        ];

        $this->js = [
            $this->compressFile('themes/v20190812/js/preview-download/p2p_bundle.min.js', self::TYPE_JS, 'preview-download'),
            $this->compressFile('themes/v20190812/js/preview-download/web_tracking.min.js', self::TYPE_JS, 'preview-download'),
            $this->compressFile('themes/v20190812/js/vendors/mimetypes.min.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/vendors/base64.min.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/preview-download/share.download.js', self::TYPE_JS, 'preview-download'),
            $this->compressFile('themes/v20190812/js/preview-download/file.preview.js', self::TYPE_JS, 'preview-download'),
        ];
    }
}
