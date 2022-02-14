<?php
namespace frontend\tests\unit\api\_01_default;

use common\models\Licenses;
use common\models\RemoteActions;
use common\models\UserNode;
use common\models\Users;
use frontend\models\NodeApi;

class ApiExecuteRemoteActionTest extends ApiDefault
{
    protected $test_action = "execute_remote_action";

    /** @var \common\models\Users */
    protected $User2;

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
        expect('user_hash in', $ret['info'])->hasKey('user_hash');
        expect('node_hash in', $ret['info'])->hasKey('node_hash');
        expect('target_node_id in', $ret['info'])->hasKey('target_node_id');
        expect('action_type in', $ret['info'])->hasKey('action_type');
    }

    /**
     *
     */
    public function testWrongData()
    {
        $data = [
            'user_hash'      => "test",
            'node_hash'      => "test",
            'target_node_id' => "test",
            'action_type'    => 'test',
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_WRONG_DATA in', $ret['errcode'])->contains(NodeApi::ERROR_WRONG_DATA);
        expect('info in', $ret)->hasKey('info');
        expect('user_hash in', $ret['info'])->hasKey('user_hash');
        expect('node_hash in', $ret['info'])->hasKey('node_hash');
        expect('target_node_id in', $ret['info'])->hasKey('target_node_id');
        expect('action_type in', $ret['info'])->hasKey('action_type');
    }

    /**
     *
     */
    public function testTargetNodeNotOwnedByUser()
    {
        $this->createTestData();

        $data = [
            'user_hash'      => $this->User->user_remote_hash,
            'node_hash'      => $this->UserNode->node_hash,
            'target_node_id' => $this->UserNode2->node_id,
            'action_type'    => RemoteActions::TYPE_LOGOUT,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_NODE_NOT_FOUND in', $ret['errcode'])->contains(NodeApi::ERROR_NODE_NOT_FOUND);
        expect('info in', $ret)->hasKey('info');
        expect('target_node_id in', $ret['info'])->contains('target_node_id');
    }

    /**
     *
     */
    public function testSuccessLogoutAction()
    {
        $this->createTestData();

        $data = [
            'user_hash'      => $this->User->user_remote_hash,
            'node_hash'      => $this->UserNode->node_hash,
            'target_node_id' => $this->UserNode->node_id,
            'action_type'    => RemoteActions::TYPE_LOGOUT,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('success in', $ret['result'])->contains('success');
        expect('no errcode in', $ret)->hasntKey('errcode');
        expect('info in', $ret)->hasKey('info');
        expect('data in', $ret)->hasKey('data');
        expect('is array ret[data]', $ret['data'])->internalType('array');
        expect('target_node_id in', $ret['data'])->hasKey('target_node_id');
        expect('action_type in', $ret['data'])->hasKey('action_type');
        expect('action_uuid in', $ret['data'])->hasKey('action_uuid');
        expect('action_data in', $ret['data'])->hasKey('action_data');
        expect('node_logout_status in', $ret['data'])->hasKey('node_logout_status');
        expect('node_logout_status_text in', $ret['data'])->hasKey('node_logout_status_text');
        expect('node_wipe_status in', $ret['data'])->hasKey('node_wipe_status');
        expect('node_wipe_status_text in', $ret['data'])->hasKey('node_wipe_status_text');
    }

    /**
     *
     */
    public function testSuccessWipeAction()
    {
        $this->createTestData();

        $data = [
            'user_hash'      => $this->User->user_remote_hash,
            'node_hash'      => $this->UserNode->node_hash,
            'target_node_id' => $this->UserNode->node_id,
            'action_type'    => RemoteActions::TYPE_WIPE,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('success in', $ret['result'])->contains('success');
        expect('no errcode in', $ret)->hasntKey('errcode');
        expect('info in', $ret)->hasKey('info');
        expect('data in', $ret)->hasKey('data');
        expect('is array ret[data]', $ret['data'])->internalType('array');
        expect('target_node_id in', $ret['data'])->hasKey('target_node_id');
        expect('action_type in', $ret['data'])->hasKey('action_type');
        expect('action_uuid in', $ret['data'])->hasKey('action_uuid');
        expect('action_data in', $ret['data'])->hasKey('action_data');
        expect('node_logout_status in', $ret['data'])->hasKey('node_logout_status');
        expect('node_logout_status_text in', $ret['data'])->hasKey('node_logout_status_text');
        expect('node_wipe_status in', $ret['data'])->hasKey('node_wipe_status');
        expect('node_wipe_status_text in', $ret['data'])->hasKey('node_wipe_status_text');
    }

    /**
     *
     */
    public function testSuccessCredentialsAction()
    {
        $this->createTestData();

        $data = [
            'user_hash'      => $this->User->user_remote_hash,
            'node_hash'      => $this->UserNode->node_hash,
            'target_node_id' => $this->UserNode->node_id,
            'action_type'    => RemoteActions::TYPE_CREDENTIALS,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('success in', $ret['result'])->contains('success');
        expect('no errcode in', $ret)->hasntKey('errcode');
        expect('info in', $ret)->hasKey('info');
        expect('data in', $ret)->hasKey('data');
        expect('is array ret[data]', $ret['data'])->internalType('array');
        expect('target_node_id in', $ret['data'])->hasKey('target_node_id');
        expect('action_type in', $ret['data'])->hasKey('action_type');
        expect('action_uuid in', $ret['data'])->hasKey('action_uuid');
        expect('action_data in', $ret['data'])->hasKey('action_data');
        expect('node_logout_status in', $ret['data'])->hasKey('node_logout_status');
        expect('node_logout_status_text in', $ret['data'])->hasKey('node_logout_status_text');
        expect('node_wipe_status in', $ret['data'])->hasKey('node_wipe_status');
        expect('node_wipe_status_text in', $ret['data'])->hasKey('node_wipe_status_text');
    }

    /**
     *
     */
    public function testTargetNodeAlreadyWiped()
    {
        $this->testSuccessWipeAction();

        $data = [
            'user_hash'      => $this->User->user_remote_hash,
            'node_hash'      => $this->UserNode->node_hash,
            'target_node_id' => $this->UserNode->node_id,
            'action_type'    => RemoteActions::TYPE_LOGOUT,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_NODE_WIPED in', $ret['errcode'])->contains(NodeApi::ERROR_NODE_WIPED);
        expect('info in', $ret)->hasKey('info');
        expect('data in', $ret)->hasKey('data');
        expect('is array ret[data]', $ret['data'])->internalType('array');
        expect('target_node_id in', $ret['data'])->hasKey('target_node_id');
        expect('action_type in', $ret['data'])->hasKey('action_type');
        expect('action_uuid in', $ret['data'])->hasKey('action_uuid');
        expect('action_data in', $ret['data'])->hasKey('action_data');
        expect('node_logout_status in', $ret['data'])->hasKey('node_logout_status');
        expect('node_logout_status_text in', $ret['data'])->hasKey('node_logout_status_text');
        expect('node_wipe_status in', $ret['data'])->hasKey('node_wipe_status');
        expect('node_wipe_status_text in', $ret['data'])->hasKey('node_wipe_status_text');
    }

    /**
     *
     */
    public function testTargetNodeAlreadyWaitLogout()
    {
        $this->testSuccessLogoutAction();

        $data = [
            'user_hash'      => $this->User->user_remote_hash,
            'node_hash'      => $this->UserNode->node_hash,
            'target_node_id' => $this->UserNode->node_id,
            'action_type'    => RemoteActions::TYPE_LOGOUT,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_NODE_LOGOUT_EXIST in', $ret['errcode'])->contains(NodeApi::ERROR_NODE_LOGOUT_EXIST);
        expect('info in', $ret)->hasKey('info');
        expect('data in', $ret)->hasKey('data');
        expect('is array ret[data]', $ret['data'])->internalType('array');
        expect('target_node_id in', $ret['data'])->hasKey('target_node_id');
        expect('action_type in', $ret['data'])->hasKey('action_type');
        expect('action_uuid in', $ret['data'])->hasKey('action_uuid');
        expect('action_data in', $ret['data'])->hasKey('action_data');
        expect('node_logout_status in', $ret['data'])->hasKey('node_logout_status');
        expect('node_logout_status_text in', $ret['data'])->hasKey('node_logout_status_text');
        expect('node_wipe_status in', $ret['data'])->hasKey('node_wipe_status');
        expect('node_wipe_status_text in', $ret['data'])->hasKey('node_wipe_status_text');
    }
}