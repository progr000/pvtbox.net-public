<?php

namespace frontend\modules\elfind;

use Yii;
use yii\base\Module;

/**
 * elfind module definition class
 */
class elFind extends Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'frontend\modules\elfind\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        //Yii::$app->user->enableSession = false;
    }
}
