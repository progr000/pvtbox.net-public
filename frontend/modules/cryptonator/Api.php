<?php

namespace frontend\modules\cryptonator;

use Yii;
use yii\base\Module;

class Api extends Module
{
    public $controllerNamespace = 'frontend\modules\cryptonator\controllers';
    public $defaultController = 'default';

    public function init()
    {
        parent::init();
        
        Yii::$app->user->enableSession = false;
    }
}
