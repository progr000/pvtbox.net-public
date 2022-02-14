<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace selfhosted\assets\v20190812;

use Yii;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MainCssAsset extends MainExtendAsset
{
    /**
     * Сжать файлы стилей можно тут:
     * https://cssresizer.com/
     * http://refresh-sf.com/  (вроде тут лучше)
     */

    public $css = [
    ];

    public $js = [
    ];

    public $jsOptions = [
        'defer' => true,
    ];

    public $depends = [
    ];

    public $cssOptions = [
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->css = [
            $this->compressFile("themes/v20190812/css/vendors/vendors.min.css", self::TYPE_CSS, 'vendors'),
            $this->compressFile("themes/v20190812/css/common.css", self::TYPE_CSS),
            $this->compressFile("themes/v20190812/css/common2.css", self::TYPE_CSS),
            $this->compressFile("themes/v20190812/css/common3.css", self::TYPE_CSS, null, '@selfhosted/web/'),
        ];
    }
}
