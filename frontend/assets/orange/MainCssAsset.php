<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\assets\orange;

use Yii;
//use yii\web\View;
use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MainCssAsset extends AssetBundle
{
    //public $sourcePath = '@frontend/themes/orange/assets';
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    /**
     * Сжать файлы стилей можно тут:
     * https://cssresizer.com/
     * http://refresh-sf.com/  (вроде тут лучше)
     */

    /**
     * Чтобы отключить прелоад нужно закоментировать строку подключения яваскрипта cssrelpreload.js
     * Так же закоментировать строки $cssOptions ['rel', 'as', 'onload']
     * Но теперь это все находится в функции init() и эти моменты нужно будет делать там
     */

    public $css = [

    ];

    public $js = [
        //https://webformyself.com/sovremennaya-asinxronnaya-zagruzka-css/
        //https://github.com/filamentgroup/loadCSS/blob/v2.0.1/src/onloadCSS.js
        //https://master-origin-loadcss.fgview.com/test/preload.html
        //'themes/orange/js/loadCSS/cssrelpreload.js',
    ];

    public $depends = [

    ];

    public $cssOptions = [
        //'position' => View::POS_END,
        //'rel' => "preload",
        //'as' => "style",
        //'onload' => "this.onload=null;this.rel='stylesheet'",
        //'noscript' => true,
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
            "themes/orange/css/style_00{$minimized}.css",
            "themes/orange/css/style_01{$minimized}.css",
            "themes/orange/css/style_02{$minimized}.css",
            "themes/orange/css/style_03{$minimized}.css",
            "themes/orange/css/style_04{$minimized}.css",
            "themes/orange/css/style_05{$minimized}.css",
            "themes/orange/css/style_06{$minimized}.css",
            "themes/orange/css/style_07{$minimized}.css",
            "themes/orange/css/style_08{$minimized}.css",
            "themes/orange/css/style_09{$minimized}.css",
            "themes/orange/css/style_10{$minimized}.css",
        ];

        if (Yii::$app->user->isGuest && Yii::$app->controller->id == "site" && Yii::$app->controller->action->id == "index") {
            $this->cssOptions['rel']    = "preload";
            $this->cssOptions['as']     = "style";
            $this->cssOptions['onload'] = "this.onload=null;this.rel='stylesheet'";

            $this->js[] = 'themes/orange/js/loadCSS/cssrelpreload.js';
        }
    }
}
