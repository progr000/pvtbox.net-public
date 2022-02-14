<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\assets\orange;

use yii;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class dateFormatAsset extends AppAsset
{
    public $js = [
        'themes/orange/js/dateformat/format.date.js',
    ];

    public static function register($view)
    {
        parent::register($view);

        $appendTimestamp = '';
        $filenameLang    = '/themes/orange/js/dateformat/i18n/format.date.' . (Yii::$app->language) . '.js';
        $filenameDefault = '/themes/orange/js/dateformat/i18n/format.date.en.js';

        if (file_exists(\Yii::getAlias('@webroot') . $filenameLang)) {
            if (isset(yii::$app->components['assetManager']['appendTimestamp']) && yii::$app->components['assetManager']['appendTimestamp']) {
                $appendTimestamp = '?v=' . filemtime(\Yii::getAlias('@webroot') . $filenameLang);
            }
            $view->registerJsFile($filenameLang.$appendTimestamp, ['depends' => 'yii\web\JqueryAsset']);
        } elseif (file_exists(\Yii::getAlias('@webroot') . $filenameDefault)) {
            if (isset(yii::$app->components['assetManager']['appendTimestamp']) && yii::$app->components['assetManager']['appendTimestamp']) {
                $appendTimestamp = '?v=' . filemtime(\Yii::getAlias('@webroot') . $filenameDefault);
            }
            $view->registerJsFile($filenameDefault.$appendTimestamp, ['depends' => 'yii\web\JqueryAsset']);
        }

        //return parent::register($view);
    }
}
