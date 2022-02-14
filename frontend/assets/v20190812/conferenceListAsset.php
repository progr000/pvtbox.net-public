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
class conferenceListAsset extends MainExtendAsset
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

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->css = [
            $this->compressFile('themes/v20190812/css/vendors/jquery-ui.min.css', self::TYPE_CSS, 'vendors'),
            $this->compressFile('themes/v20190812/css/conferences/conference.common.css', self::TYPE_CSS, 'conferences'),
            $this->compressFile('themes/v20190812/css/conferences/conference.list.css', self::TYPE_CSS, 'conferences'),
        ];

        $this->js = [
            $this->compressFile('themes/v20190812/js/clipboard/clipboard.min.js', self::TYPE_JS, 'clipboard'),
            $this->compressFile('themes/v20190812/js/vendors/md5.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/conferences/conference.list.js', self::TYPE_JS, 'conferences'),
            $this->compressFile('themes/v20190812/js/conferences/conference.common.js', self::TYPE_JS, 'conferences'),
            $this->compressFile('themes/v20190812/js/jquery/jquery-ui.min.js', self::TYPE_JS, 'jquery'),
            $this->compressFile('themes/v20190812/js/jquery/jquery.nicescroll.js', self::TYPE_JS, 'jquery'),
        ];
    }
}
