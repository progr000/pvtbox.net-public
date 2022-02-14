<?php
namespace frontend\tests\unit\api\_01_default;

use Codeception\TestCase\Test;
use common\helpers\FileSys;
use common\models\Mailq;
use common\models\QueuedEvents;
use common\models\RemoteActions;
use common\models\Users;
use common\models\UserNode;
use common\models\Licenses;
use frontend\modules\api\controllers\DefaultController;

class ApiDefault extends Test
{
    /** @var  $controller \frontend\modules\api\controllers\DefaultController */
    protected $controller;

    /** @var \frontend\UnitTester */
    protected $tester;

    /** @var \common\models\Users */
    protected $User;

    /** @var \common\models\UserNode */
    protected $UserNode;

    /** @var array */
    protected $test_emails_pull = [
        'user0@noexist.domain',
        'user1@noexist.domain',
        'user2@noexist.domain',
        'user3@noexist.domain',
        'user4@noexist.domain',
        'user5@noexist.domain',
        'user6@noexist.domain',
        'user7@noexist.domain',
        'user8@noexist.domain',
        'user9@noexist.domain',
    ];

    /**
     *
     */
    protected function _before()
    {
        \Yii::$app->language = 'en';

        Mailq::deleteAll();
        QueuedEvents::deleteAll();
        Users::deleteAll();
        UserNode::deleteAll();
        RemoteActions::deleteAll();
        //var_dump(\Yii::$app->params['nodeVirtualFS']);exit;
        FileSys::rmdir(\Yii::$app->params['nodeVirtualFS'], true);

        $module = new \yii\base\Module('test');
        $this->controller = new DefaultController('test', $module);
    }

    /**
     *
     */
    protected function _after()
    {
        Mailq::deleteAll();
        QueuedEvents::deleteAll();
        Users::deleteAll();
        UserNode::deleteAll();
        RemoteActions::deleteAll();
        FileSys::rmdir(\Yii::$app->params['nodeVirtualFS'], true);
    }

    /**
     *
     */
    protected function createTestData()
    {
        /** User */
        $this->User = new Users();
        $this->User->user_email = $this->test_emails_pull[1];
        $this->User->user_name  = "Test User Name";
        $this->User->license_type = Licenses::TYPE_FREE_TRIAL;
        $this->User->setPassword(hash('sha512', "qwerty"), false);
        $this->User->generateAuthKey();
        $this->User->save();

        /** UserNode */
        $this->UserNode = new UserNode();
        $this->UserNode->node_hash = hash("sha512", uniqid('', true) . microtime());
        $this->UserNode->user_id = $this->User->user_id;
        $this->UserNode->node_name = "TestNode";
        $this->UserNode->node_osname = "Linux Ubuntu";
        $this->UserNode->node_ostype = UserNode::OSTYPE_LINUX;
        $this->UserNode->node_devicetype = UserNode::DEVICE_DESKTOP;
        $this->UserNode->node_status = UserNode::STATUS_ACTIVE;
        $this->UserNode->save();
    }
}