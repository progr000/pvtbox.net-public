<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\assets\v20190812;

use Yii;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends MainExtendAsset
{

    public $css = [
    ];

    public $js = [
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        //'yii\bootstrap\BootstrapAsset',
        'frontend\assets\v20190812\MainCssAsset',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->js = [
            //'themes/v20190812/js/jquery/jquery.scrollbar.min.js',
            $this->compressFile('themes/v20190812/js/jquery/jquery.cookie.js', self::TYPE_JS, 'jquery'),
            $this->compressFile('themes/v20190812/js/jquery/jquery.fancybox.min.js', self::TYPE_JS, 'jquery'),
            $this->compressFile('themes/v20190812/js/jquery/jquery.viewportchecker.js', self::TYPE_JS, 'jquery'),

            $this->compressFile('themes/v20190812/js/vendors/log.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/vendors/alert.divs.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/vendors/detect.os.useragent.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/vendors/html2canvas.min.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/vendors/select2.min.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/vendors/slick.min.js', self::TYPE_JS, 'vendors'),

            $this->compressFile('themes/v20190812/js/common.js', self::TYPE_JS),
            $this->compressFile('themes/v20190812/js/common2.js', self::TYPE_JS),
        ];
    }
}
