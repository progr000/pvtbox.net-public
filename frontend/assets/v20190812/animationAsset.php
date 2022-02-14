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
class animationAsset extends MainExtendAsset
{

    public $css = [
    ];

    public $js = [
    ];

    public $depends = [
        'frontend\assets\v20190812\AppAsset',
        'frontend\assets\v20190812\downloadAsset',
        'frontend\assets\v20190812\guestAsset',
        'yii\widgets\ActiveFormAsset',
        'yii\validators\ValidationAsset',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->js = [
            $this->compressFile('themes/v20190812/js/animation/createjs.min.js', self::TYPE_JS, 'animation'),
            $this->compressFile('themes/v20190812/js/animation/privateBox_v3.js', self::TYPE_JS, 'animation'),
            $this->compressFile('themes/v20190812/js/animation/animation.js', self::TYPE_JS, 'animation'),
        ];
    }
}
