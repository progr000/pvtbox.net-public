<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\assets\orange;

use Yii;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class downloadAsset extends AppAsset
{
    public $css = [
        //'themes/orange/css/download.min.css',
    ];

    public $js = [
        'themes/orange/js/detect.os.useragent.js',
        'themes/orange/js/download.init.js',
    ];

    public $depends = [
        'frontend\assets\orange\AppAsset',
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
            "themes/orange/css/download{$minimized}.css",
        ];

        if (Yii::$app->user->isGuest && Yii::$app->controller->id == "site" && Yii::$app->controller->action->id == "index") {
            $this->cssOptions['rel']    = "preload";
            $this->cssOptions['as']     = "style";
            $this->cssOptions['onload'] = "this.onload=null;this.rel='stylesheet'";

            $this->js[] = 'themes/orange/js/loadCSS/cssrelpreload.js';
        }
    }
}
