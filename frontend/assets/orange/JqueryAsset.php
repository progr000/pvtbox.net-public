<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\assets\orange;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class JqueryAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css =[

    ];

    public $js = [
        'themes/orange/js/jquery.2.x/jquery.min.js',
    ];

    public function init()
    {
        parent::init();
        // делаем этот ассет замещающим.
        \Yii::$app->assetManager->bundles['yii\web\JqueryAsset'] = $this;

    }
}
