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
class MaintenanceAsset extends MainExtendAsset
{

    public $css = [

    ];

    public $js = [
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->css = [
            $this->compressFile("themes/v20190812/css/common2.css", self::TYPE_CSS),
            $this->compressFile("themes/v20190812/css/maintenance.css", self::TYPE_CSS),
        ];
    }
}
