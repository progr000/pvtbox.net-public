<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\assets\orange;

use Yii;
use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MaintenanceAsset extends AssetBundle
{
    //public $sourcePath = '@frontend/themes/orange/assets';
    public $basePath = '@webroot';
    public $baseUrl = '@web';

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

        $minimized = (isset(Yii::$app->params['use_minimized_css']) && Yii::$app->params['use_minimized_css'])
            ? ".min"
            : "";

        $this->css = [
            "themes/orange/css/guest.css",
            "themes/orange/css/maintenance.css",
        ];
    }
}
