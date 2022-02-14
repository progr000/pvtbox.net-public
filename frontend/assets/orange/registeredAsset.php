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
class registeredAsset extends AppAsset
{
    public $css = [
        //'themes/orange/css/registered.min.css',
        //'themes/orange/css/snackbar.min.css',
    ];

    public $js = [
        'themes/orange/js/check.license.access.js',
        'themes/orange/js/notifications.js',
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
            "themes/orange/css/registered{$minimized}.css",
            "themes/orange/css/snackbar{$minimized}.css",
        ];
    }
}
