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
class devicesAsset extends MainExtendAsset
{

    public $css = [
    ];

    public $js = [
    ];

    public $depends = [
        'frontend\assets\v20190812\AppAsset',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->js = [
            $this->compressFile('themes/v20190812/js/vendors/websocket.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/vendors/devices.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/vendors/logout.and.wipe.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/jquery/jquery.nicescroll.js', self::TYPE_JS, 'jquery'),
            $this->compressFile('themes/v20190812/js/vendors/client.detection.js', self::TYPE_JS, 'vendors'),
        ];
    }
}
