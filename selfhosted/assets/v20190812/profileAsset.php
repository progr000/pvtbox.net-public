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
class profileAsset extends MainExtendAsset
{

    public $css = [
    ];

    public $js = [
    ];

    public $jsOptions = [
        'defer' => true,
    ];

    public $depends = [
        'selfhosted\assets\v20190812\AppAsset',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->js = [
            $this->compressFile('themes/v20190812/js/vendors/modal.profile.js', self::TYPE_JS, 'vendors', null, true),
        ];
    }
}
