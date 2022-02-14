<?php
namespace backend\components;

use Yii;
use yii\web\Controller;
use backend\models\Admins;

/**
 * Site controller
 *
 * @property \backend\models\Admins $Admins
 *
 */
class SController extends Controller
{
    protected $Admins;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        /* Admins */
        if (!Yii::$app->user->isGuest) {
            $this->Admins = Yii::$app->user->identity;
        }
    }

    /**
     * @return bool
     */
    protected function checkAccess()
    {
        return ($this->Admins && $this->Admins->admin_role == Admins::ROLE_ROOT);
    }

    /**
     * @return \yii\web\Response
     */
    protected function accessErrorRedirect()
    {
        return $this->redirect('/users');
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

    /**
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {
        return parent::afterAction($action, $result);
    }
}

