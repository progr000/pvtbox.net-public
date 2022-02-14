<?php

namespace frontend\modules\api;

use Yii;
use yii\base\Module;

class Api extends Module
{
    public $controllerNamespace = 'frontend\modules\api\controllers';
    public $defaultController = 'default';

    public function init()
    {
        parent::init();
        
        Yii::$app->user->enableSession = false;
    }
}
