<?php
namespace selfhosted\components;

use Yii;
use yii\web\Controller;
use common\models\SelfHostUsers;

/**
 * Site controller
 *
 * @property \common\models\SelfHostUsers $SelfHostUser
 *
 */
class SController extends Controller
{
    protected $SelfHostUser;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        /* SelfHostUser */
        if (!Yii::$app->user->isGuest) {
            $this->SelfHostUser = Yii::$app->user->identity;
        }
    }

    /**
     * @return bool
     */
    protected function checkAccess()
    {
        return ($this->SelfHostUser && $this->SelfHostUser->shu_role == SelfHostUsers::ROLE_ROOT);
    }

    /**
     * @return \yii\web\Response
     */
    protected function accessErrorRedirect()
    {
        return $this->redirect('/index');
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

