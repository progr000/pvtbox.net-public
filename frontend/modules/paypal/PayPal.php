<?php

namespace frontend\modules\paypal;

use Yii;
use yii\base\Module;

/**
 * PayPal module definition class
 */
class PayPal extends Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'frontend\modules\paypal\controllers';
    public $defaultController = 'default';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Yii::$app->user->enableSession = false;
    }
}
