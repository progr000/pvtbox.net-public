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
class modFolderDownloadAsset extends MainExtendAsset
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

        $this->css = [
            $this->compressFile('themes/v20190812/css/elfinder/base.css', self::TYPE_CSS, 'elfinder'),
            $this->compressFile('themes/v20190812/css/elfinder/shared-folder.css', self::TYPE_CSS, 'elfinder'),
        ];

        $this->js = [
            $this->compressFile('themes/v20190812/js/preview-download/folder.download.js', self::TYPE_JS, 'preview-download'),
        ];
    }
}
