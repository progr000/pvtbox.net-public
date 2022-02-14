<?php
namespace frontend\tests\unit\api\_01_default;

use common\models\Licenses;
use common\models\RemoteActions;
use common\models\UserNode;
use common\models\Users;
use frontend\models\NodeApi;

class ApiRemoteActionDoneTest extends ApiDefault
{
    protected $test_action = "remote_action_done";

    /** @var \common\models\Users */
    protected $User2;

    /** @var  string */
    protected $action_uuid_forUser;
    protected $action_uuid_forUser2;

    /** @var \common\models\UserNode */
    protected $UserNode2;

    protected function createTestData()
    {
        parent::createTestData();

        /** User */
        $this->User2 = new Users();
        $this->User2->user_email = $this->test_emails_pull[2];
        $this->User2->user_name  = "Test User Name";
        $this->User2->license_type = Licenses::TYPE_FREE_TRIAL;
        $this->User2->setPassword(hash('sha512', "qwerty"), false);
        $this->User2->generateAuthKey();
        $this->User2->save();

        /** UserNode */
        $this->UserNode2 = new UserNode();
        $this->UserNode2->node_hash = hash("sha512", uniqid('', true) . microtime());
        $this->UserNode2->user_id = $this->User2->user_id;
        $this->UserNode2->node_name = "TestNode";
        $this->UserNode2->node_osname = "Linux Ubuntu";
        $this->UserNode2->node_ostype = UserNode::OSTYPE_LINUX;
        $this->UserNode2->node_devicetype = UserNode::DEVICE_DESKTOP;
        $this->UserNode2->node_status = UserNode::STATUS_ACTIVE;
        $this->UserNode2->save();

        /** Create remote_action for User (UserNode) before tests it done */
        $data = [
            'node_hash'      => $this->UserNode->node_hash,
            'user_hash'      => $this->User->user_remote_hash,
            'target_node_id' => $this->UserNode->node_id,
            'action_type'    => RemoteActions::TYPE_LOGOUT,
        ];
        $ret1 = $this->controller->actionTests('execute_remote_action', $data);
        //var_dump($ret1); exit;
        if (isset($ret1['result']) && $ret1['result'] == 'success') {
            $this->action_uuid_forUser = $ret1['data']['action_uuid'];
        } else {
            $this->fail('Method execute_remote_action is fail. Check it before.');
        }

        /** Create remote_action for User2 (UserNode2) before tests that action is not owned by User */
        $data = [
            'node_hash'      => $this->UserNode2->node_hash,
            'user_hash'      => $this->User2->user_remote_hash,
            'target_node_id' => $this->UserNode2->node_id,
            'action_type'    => RemoteActions::TYPE_LOGOUT,
        ];
        $ret2 = $this->controller->actionTests('execute_remote_action', $data);
        //var_dump($ret1); exit;
        if (isset($ret2['result']) && $ret2['result'] == 'success') {
            $this->action_uuid_forUser2 = $ret2['data']['action_uuid'];
        } else {
            $this->fail('Method execute_remote_action is fail. Check it before.');
        }
    }

    /**
     *
     */
    public function testEmptyData()
    {
        $data = [];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_WRONG_DATA in', $ret['errcode'])->contains(NodeApi::ERROR_WRONG_DATA);
        expect('info in', $ret)->hasKey('info');
        expect('is array', $ret['info'])->internalType('array');
        expect('user_hash in', $ret['info'])->hasKey('user_hash');
        expect('node_hash in', $ret['info'])->hasKey('node_hash');
        expect('action_uuid in', $ret['info'])->hasKey('action_uuid');
    }

    /**
     *
     */
    public function testWrongData()
    {
        $data = [
            'user_hash'   => "test",
            'node_hash'   => "test",
            'action_uuid' => "test",
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_WRONG_DATA in', $ret['errcode'])->contains(NodeApi::ERROR_WRONG_DATA);
        expect('info in', $ret)->hasKey('info');
        expect('is array', $ret['info'])->internalType('array');
        expect('user_hash in', $ret['info'])->hasKey('user_hash');
        expect('node_hash in', $ret['info'])->hasKey('node_hash');
        expect('action_uuid in', $ret['info'])->hasKey('action_uuid');
    }

    /**
     *
     */
    public function testRemoteActionNotFound()
    {
        $this->createTestData();

        $data = [
            'user_hash'   => $this->User->user_remote_hash,
            'node_hash'   => $this->UserNode->node_hash,
            'action_uuid' => md5(uniqid()),
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_WRONG_DATA in', $ret['errcode'])->contains(NodeApi::ERROR_WRONG_DATA);
        expect('info in', $ret)->hasKey('info');
        expect('action_uuid in', $ret['info'])->contains('action_uuid not found');
    }

    /**
     *
     */
    public function testTargetNodeNotOwnedByUser()
    {
        $this->createTestData();

        $data = [
            'user_hash'   => $this->User->user_remote_hash,
            'node_hash'   => $this->UserNode->node_hash,
            'action_uuid' => $this->action_uuid_forUser2,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_USER_NODE_MISMATCH in', $ret['errcode'])->contains(NodeApi::ERROR_USER_NODE_MISMATCH);
        expect('info in', $ret)->hasKey('info');
        expect('user_node.user_id in', $ret['info'])->contains('user_node.user_id');
        expect('remote_actions.user_id in', $ret['info'])->contains('remote_actions.user_id');
    }

    /**
     *
     */
    public function testSuccess()
    {
        $this->createTestData();

        $data = [
            'user_hash'   => $this->User->user_remote_hash,
            'node_hash'   => $this->UserNode->node_hash,
            'action_uuid' => $this->action_uuid_forUser,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('success in', $ret['result'])->contains('success');
        expect('no errcode in', $ret)->hasntKey('errcode');
    }
}