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
class daterangepickerAsset extends MainExtendAsset
{

    public $css = [
    ];

    public $js = [
    ];

    public $depends = [
        'frontend\assets\v20190812\AppAsset',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->js = [
            $this->compressFile('themes/v20190812/js/datepicker/datepicker.min.js', self::TYPE_JS, 'datepicker'),
        ];

        $filenameLang    = 'themes/v20190812/js/datepicker/i18n/datepicker.' . (Yii::$app->language) . '.js';
        $filenameDefault = 'themes/v20190812/js/datepicker/i18n/datepicker.en.js';

        if (file_exists(\Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . $filenameLang)) {
            $this->js[] = $this->compressFile($filenameLang, self::TYPE_JS, 'datepicker/i18n');
        } elseif (file_exists(\Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . $filenameDefault)) {
            $this->js[] = $this->compressFile($filenameDefault, self::TYPE_JS, 'datepicker/i18n');
        }

        $this->js[] = $this->compressFile('themes/v20190812/js/datepicker/datepicker.init.js', self::TYPE_JS, 'datepicker');
    }
}
