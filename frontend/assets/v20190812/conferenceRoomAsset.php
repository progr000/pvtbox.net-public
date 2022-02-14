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
class conferenceRoomAsset extends MainExtendAsset
{

    public $css = [
    ];

    public $js = [
    ];

    public $depends = [
        'frontend\assets\v20190812\AppAsset',
        //'frontend\assets\v20190812\dateFormatAsset',
        //'yii\bootstrap\BootstrapPluginAsset',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->css = [
            //$this->compressFile('themes/v20190812/css/vendors/jquery-ui.min.css', self::TYPE_CSS, 'vendors'),
            //$this->compressFile('themes/v20190812/css/conferences/conference.list.css', self::TYPE_CSS, 'conferences'),
            $this->compressFile('themes/v20190812/css/conferences/conference.common.css', self::TYPE_CSS, 'conferences'),
            $this->compressFile('themes/v20190812/css/conferences/conference.room.css', self::TYPE_CSS, 'conferences'),
            $this->compressFile('themes/v20190812/css/vendors/slick.css', self::TYPE_CSS, 'vendors', true),
            $this->compressFile('themes/v20190812/css/vendors/slick-theme.css', self::TYPE_CSS, 'vendors', true),
        ];

        $this->js = [
            //$this->compressFile('themes/v20190812/js/jquery/jquery-ui.min.js', self::TYPE_JS, 'jquery'),
            $this->compressFile('themes/v20190812/js/jquery/jquery.nicescroll.min.js', self::TYPE_JS, 'jquery'),
            $this->compressFile('themes/v20190812/js/vendors/slick.min.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/conferences/conference.room.js', self::TYPE_JS, 'conferences'),
            $this->compressFile('themes/v20190812/js/conferences/conference.common.js', self::TYPE_JS, 'conferences'),
            'https://installer.pvtbox.net/cdn/client-bundle.js?v=' . uniqid(),
        ];
    }
}
