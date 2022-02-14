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
class trafAsset extends MainExtendAsset
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

        $this->css = [
            $this->compressFile('themes/v20190812/js/odometer/themes/odometer-theme-car.css', self::TYPE_CSS, 'odometer/themes'),
            //$this->compressFile('themes/v20190812/js/odometer/themes/odometer-theme-default.css', self::TYPE_CSS, 'odometer/themes'),
            //$this->compressFile('themes/v20190812/js/odometer/themes/odometer-theme-train-station.css', self::TYPE_CSS, 'odometer/themes'),
            $this->compressFile('themes/v20190812/js/odometer/themes/odometer-repair.css', self::TYPE_CSS, 'odometer/themes'),
        ];

        $this->js = [
            $this->compressFile('themes/v20190812/js/vendors/traf.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/odometer/odometer_v1.min.js', self::TYPE_JS, 'odometer'),
            //$this->compressFile('themes/v20190812/js/odometer/odometer_v2.min.js', self::TYPE_JS, 'odometer'),
        ];
    }
}
