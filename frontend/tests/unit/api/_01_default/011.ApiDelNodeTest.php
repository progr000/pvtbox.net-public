<?php
namespace frontend\tests\unit\api\_01_default;

use common\models\UserNode;
use frontend\models\NodeApi;

class ApiDelNodeTest extends ApiDefault
{
    protected $test_action = "delNode";

    /** @var \common\models\UserNode */
    protected $UserNode2;

    protected function createTestData()
    {
        parent::createTestData();

        /** UserNode */
        $this->UserNode2 = new UserNode();
        $this->UserNode2->node_hash = hash("sha512", uniqid('', true) . microtime());
        $this->UserNode2->user_id = $this->User->user_id;
        $this->UserNode2->node_name = "TestNode2";
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
        expect('error in', $ret)->hasKey('info');
        expect('is array', $ret['info'])->internalType('array');
        expect('user_hash in', $ret['info'])->hasKey('user_hash');
        expect('node_hash in', $ret['info'])->hasKey('node_hash');
        expect('node_id in', $ret['info'])->hasKey('node_id');

    }

    /**
     *
     */
    public function testWrongData()
    {
        $data = [
            'user_hash' => "test",
            'node_hash' => "test",
            'node_id'   => "test"
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
        expect('node_id in', $ret['info'])->hasKey('node_id');
    }

    /**
     *
     */
    public function testSelfDeletionDenied()
    {
        $this->createTestData();

        $data = [
            'user_hash' => $this->User->user_remote_hash,
            'node_hash' => $this->UserNode->node_hash,
            'node_id'   => $this->UserNode->node_id,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_CANT_SELF_DELETE in', $ret['errcode'])->contains(NodeApi::ERROR_CANT_SELF_DELETE);
        expect('info in', $ret)->hasKey('info');
    }

    /**
     *
     */
    public function testDeletionNotExistNode()
    {
        $this->createTestData();

        $data = [
            'user_hash' => $this->User->user_remote_hash,
            'node_hash' => $this->UserNode->node_hash,
            'node_id'   => 1,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_NODE_NOT_FOUND in', $ret['errcode'])->contains(NodeApi::ERROR_NODE_NOT_FOUND);
        expect('info in', $ret)->hasKey('info');
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
            'node_id'   => $this->UserNode2->node_id,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('success in', $ret['result'])->contains('success');
        expect('no errcode in', $ret)->hasntKey('errcode');
        expect('info in', $ret)->hasKey('info');
    }
}