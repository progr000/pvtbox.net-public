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
class vshCssAsset extends MainExtendAsset
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
            $this->compressFile("themes/v-sh/css/common3.css", self::TYPE_CSS),
        ];
    }
}
