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
class adminPanelAsset extends MainExtendAsset
{

    public $css = [
    ];

    public $js = [
    ];

    public $depends = [
        'frontend\assets\v20190812\AppAsset',
        'frontend\assets\v20190812\dateFormatAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->css = [
            $this->compressFile('themes/v20190812/css/vendors/jquery-ui.min.css', self::TYPE_CSS, 'vendors'),
        ];

        $this->js = [
            $this->compressFile('themes/v20190812/js/vendors/websocket.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/vendors/check.nodes.online.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/vendors/admin.panel.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/vendors/resend.invite.js', self::TYPE_JS, 'vendors'),
            $this->compressFile('themes/v20190812/js/jquery/jquery-ui.min.js', self::TYPE_JS, 'jquery'),
            $this->compressFile('themes/v20190812/js/jquery/jquery.nicescroll.js', self::TYPE_JS, 'jquery'),
        ];
    }
}
