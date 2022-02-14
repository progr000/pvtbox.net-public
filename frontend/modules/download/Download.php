<?php

namespace frontend\modules\download;

use Yii;
use yii\base\Module;

/**
 * download module definition class
 */
class Download extends Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'frontend\modules\download\controllers';
    public $defaultController = 'file';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
        //Yii::$app->user->enableSession = false;
    }
}
