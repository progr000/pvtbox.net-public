<?php
namespace frontend\tests\unit\api\_01_default;

use common\models\Licenses;
use common\models\UserNode;
use common\models\Users;
use frontend\models\NodeApi;

class ApiGetUserAndUserNodeTest extends ApiDefault
{
    protected $test_action = "getUserAndUserNode";

    /** @var \frontend\models\NodeApi */
    protected $NodeApiModel;

    /** @var \common\models\Users */
    protected $User2;

    protected function _before()
    {
        parent::_before();

        $this->NodeApiModel = new NodeApi(['user_hash', 'node_hash']);
    }

    protected function createTestData()
    {
        parent::createTestData();

        /** User */
        $this->User2 = new Users();
        $this->User2->user_email = $this->test_emails_pull[2];
        $this->User2->user_name  = "Test2 User Name";
        $this->User2->license_type = Licenses::TYPE_FREE_TRIAL;
        $this->User2->setPassword(hash('sha512', "qwerty"), false);
        $this->User2->generateAuthKey();
        $this->User2->save();
    }

    /**
     *
     */
    public function testEmptyData()
    {
        $ret = $this->controller->actionTests($this->test_action, $this->NodeApiModel);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_USERHASH_NODEHASH in', $ret['errcode'])->contains(NodeApi::ERROR_USERHASH_NODEHASH);
        expect('info in', $ret)->hasKey('info');
    }

    /**
     *
     */
    public function testUserHashNotFound()
    {
        $data = [
            'user_hash' => hash('sha512', uniqid()), //$this->User->user_remote_hash,
            'node_hash' => hash('sha512', uniqid()),
        ];

        if ($this->NodeApiModel->load(['NodeApi' => $data]) && $this->NodeApiModel->validate()) {

            $ret = $this->controller->actionTests($this->test_action, $this->NodeApiModel);
            //var_dump($ret); exit;
            expect('is array', $ret)->internalType('array');
            expect('result in', $ret)->hasKey('result');
            expect('error in', $ret['result'])->contains('error');
            expect('errcode in', $ret)->hasKey('errcode');
            expect('ERROR_USER_NOT_FOUND in', $ret['errcode'])->contains(NodeApi::ERROR_USER_NOT_FOUND);
            expect('info in', $ret)->hasKey('info');
            expect('user_hash in', $ret['info'])->contains('user_hash');

        } else {
            $this->fail('Load and validate model error');
        }
    }

    /**
     *
     */
    public function testUserNodeHashNotFound()
    {
        $this->createTestData();

        $data = [
            'user_hash' => $this->User->user_remote_hash,
            'node_hash' => hash('sha512', uniqid()),
        ];

        if ($this->NodeApiModel->load(['NodeApi' => $data]) && $this->NodeApiModel->validate()) {

            $ret = $this->controller->actionTests($this->test_action, $this->NodeApiModel);
            //var_dump($ret); exit;
            expect('is array', $ret)->internalType('array');
            expect('result in', $ret)->hasKey('result');
            expect('error in', $ret['result'])->contains('error');
            expect('errcode in', $ret)->hasKey('errcode');
            expect('ERROR_NODE_NOT_FOUND in', $ret['errcode'])->contains(NodeApi::ERROR_NODE_NOT_FOUND);
            expect('info in', $ret)->hasKey('info');
            expect('node_hash in', $ret['info'])->contains('node_hash');

        } else {
            $this->fail('Load and validate model error');
        }
    }

    /**
     *
     */
    public function testUserNodeValidStatus()
    {
        $this->createTestData();

        $this->UserNode->node_status = UserNode::STATUS_WIPED;
        $this->UserNode->save();

        $data = [
            'user_hash' => $this->User->user_remote_hash,
            'node_hash' => $this->UserNode->node_hash,
        ];

        if ($this->NodeApiModel->load(['NodeApi' => $data]) && $this->NodeApiModel->validate()) {

            $ret = $this->controller->actionTests($this->test_action, $this->NodeApiModel);
            //var_dump($ret); exit;
            expect('is array', $ret)->internalType('array');
            expect('result in', $ret)->hasKey('result');
            expect('error in', $ret['result'])->contains('error');
            expect('errcode in', $ret)->hasKey('errcode');
            expect('ERROR_BAD_NODE_STATUS in', $ret['errcode'])->contains(NodeApi::ERROR_BAD_NODE_STATUS);
            expect('info in', $ret)->hasKey('info');
            expect('node_status in', $ret['info'])->contains('node_status');

        } else {
            $this->fail('Load and validate model error');
        }
    }

    /**
     *
     */
    public function testUserNodeMismatch()
    {
        $this->createTestData();

        $data = [
            'user_hash' => $this->User2->user_remote_hash,
            'node_hash' => $this->UserNode->node_hash,
        ];

        if ($this->NodeApiModel->load(['NodeApi' => $data]) && $this->NodeApiModel->validate()) {

            $ret = $this->controller->actionTests($this->test_action, $this->NodeApiModel);
            //var_dump($ret); exit;
            expect('is array', $ret)->internalType('array');
            expect('result in', $ret)->hasKey('result');
            expect('error in', $ret['result'])->contains('error');
            expect('errcode in', $ret)->hasKey('errcode');
            expect('ERROR_USER_NODE_MISMATCH in', $ret['errcode'])->contains(NodeApi::ERROR_USER_NODE_MISMATCH);
            expect('info in', $ret)->hasKey('info');
            expect('user_hash in', $ret['info'])->contains('user_hash');
            expect('node_hash in', $ret['info'])->contains('node_hash');

        } else {
            $this->fail('Load and validate model error');
        }
    }

    /**
     *
     */
    public function testSuccess()
    {
        $this->createTestData();

        $data = [
            'user_hash' => $this->User->user_remote_hash,
            'node_hash' => $this->UserNode->node_hash,
        ];

        if ($this->NodeApiModel->load(['NodeApi' => $data]) && $this->NodeApiModel->validate()) {

            $ret = $this->controller->actionTests($this->test_action, $this->NodeApiModel);
            //var_dump($ret); exit;
            expect('is array', $ret)->internalType('array');
            expect('result in', $ret)->hasKey('result');
            expect('success in', $ret['result'])->contains('success');
            expect('no errcode in', $ret)->hasntKey('errcode');
            expect('User in', $ret)->hasKey('User');
            //expect('ret[User] its object of class Users', (is_object($ret['User']) && get_class($ret['User']) == Users::className()))->true();
            expect('ret[User] is', $ret['User'])->isInstanceOf(Users::className());
            expect('UserNode in', $ret)->hasKey('UserNode');
            //expect('ret[UserNode] its object of class UserNode', (is_object($ret['UserNode']) && get_class($ret['UserNode']) == UserNode::className()))->true();
            expect('ret[UserNode] is', $ret['UserNode'])->isInstanceOf(UserNode::className());

        } else {
            $this->fail('Load and validate model error');
        }
    }
}