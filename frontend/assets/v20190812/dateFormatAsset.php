<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\assets\v20190812;

use yii;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class dateFormatAsset extends MainExtendAsset
{

    public $css = [
    ];

    public $js = [
    ];

    public $depends = [
        'frontend\assets\v20190812\AppAsset',
    ];

    public function init()
    {
        parent::init();

        $this->js = [
            $this->compressFile('themes/v20190812/js/dateformat/format.date.js', self::TYPE_JS, 'dateformat'),
        ];

        $filenameLang    = 'themes/v20190812/js/dateformat/i18n/format.date.' . (Yii::$app->language) . '.js';
        $filenameDefault = 'themes/v20190812/js/dateformat/i18n/format.date.en.js';
        if (file_exists(\Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . $filenameLang)) {
            $this->js[] = $this->compressFile($filenameLang, self::TYPE_JS, 'dateformat/i18n');
        } elseif (file_exists(\Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . $filenameDefault)) {
            $this->js[] = $this->compressFile($filenameDefault, self::TYPE_JS, 'dateformat/i18n');
        }
    }
}
