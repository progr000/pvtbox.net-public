<?php
namespace frontend\tests\unit\api\_01_default;

use common\models\Licenses;
use common\models\UserNode;
use frontend\models\NodeApi;

class ApiAddNodeTest extends ApiDefault
{
    protected $test_action = "addNode";

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
        expect('user_email in', $ret['info'])->hasKey('user_email');
        expect('user_password in', $ret['info'])->hasKey('user_password');
        expect('node_hash in', $ret['info'])->hasKey('node_hash');
        expect('node_name in', $ret['info'])->hasKey('node_name');
        expect('node_osname in', $ret['info'])->hasKey('node_osname');
        expect('node_ostype in', $ret['info'])->hasKey('node_ostype');
        expect('node_devicetype in', $ret['info'])->hasKey('node_devicetype');
    }

    /**
     *
     */
    public function testWrongData()
    {
        $data = [
            'user_email'      => "test",
            'user_password'   => "test",
            'node_hash'       => "test",
            'node_name'       => md5("test"),
            'node_osname'     => hash('sha512', "test") . hash('sha512', "test"),
            'node_ostype'     => "test",
            'node_devicetype' => "test",
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
        expect('user_email in', $ret['info'])->hasKey('user_email');
        expect('user_password in', $ret['info'])->hasKey('user_password');
        expect('node_hash in', $ret['info'])->hasKey('node_hash');
        expect('node_name in', $ret['info'])->hasKey('node_name');
        expect('node_osname in', $ret['info'])->hasKey('node_osname');
        expect('node_ostype in', $ret['info'])->hasKey('node_ostype');
        expect('node_devicetype in', $ret['info'])->hasKey('node_devicetype');
    }

    /**
     *
     */
    public function testNotExistsEmail()
    {
        $this->createTestData();

        $data = [
            'user_email'      => $this->test_emails_pull[9],
            'user_password'   => hash('sha512', "qwerty"),
            'node_hash'       => hash("sha512", uniqid('', true) . microtime()),
            'node_name'       => "TestNode2",
            'node_osname'     => "Linux Ubuntu",
            'node_ostype'     => UserNode::OSTYPE_LINUX,
            'node_devicetype' => UserNode::DEVICE_DESKTOP,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_USER_NOT_FOUND in', $ret['errcode'])->contains(NodeApi::ERROR_USER_NOT_FOUND);
        expect('debug in', $ret)->hasKey('debug');
        expect('user_email in', $ret['debug'])->contains('user_email');
    }

    /**
     *
     */
    public function testWrongPassword()
    {
        $this->createTestData();

        $data = [
            'user_email'      => $this->test_emails_pull[1],
            'user_password'   => hash('sha512', "qwerty1"),
            'node_hash'       => hash("sha512", uniqid('', true) . microtime()),
            'node_name'       => "TestNode2",
            'node_osname'     => "Linux Ubuntu",
            'node_ostype'     => UserNode::OSTYPE_LINUX,
            'node_devicetype' => UserNode::DEVICE_DESKTOP,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_USER_NOT_FOUND in', $ret['errcode'])->contains(NodeApi::ERROR_USER_NOT_FOUND);
        expect('debug in', $ret)->hasKey('debug');
        expect('user_email in', $ret['debug'])->contains('user_email');
    }

    /**
     *
     */
    public function testExistsNodeHash()
    {
        $this->createTestData();

        $data = [
            'user_email'      => $this->test_emails_pull[1],
            'user_password'   => hash('sha512', "qwerty"),
            'node_hash'       => $this->UserNode->node_hash,
            'node_name'       => "TestNode2",
            'node_osname'     => "Linux Ubuntu",
            'node_ostype'     => UserNode::OSTYPE_LINUX,
            'node_devicetype' => UserNode::DEVICE_DESKTOP,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_NODE_EXIST in', $ret['errcode'])->contains(NodeApi::ERROR_NODE_EXIST);
        expect('info in', $ret)->hasKey('info');
        expect('node_hash in', $ret['info'])->contains('node_hash');
    }

    /**
     *
     */
    public function testLicenseLimitNodes()
    {
        $this->createTestData();
        $this->User->license_type = Licenses::TYPE_FREE_DEFAULT;
        $this->User->save();

        $limit = Licenses::getCountLicenseLimitNodes($this->User->license_type);
        for ($i = 0; $i < $limit; $i++) {
            $UserNode = new UserNode();
            $UserNode->node_hash = hash("sha512", uniqid('', true) . microtime());
            $UserNode->user_id = $this->User->user_id;
            $UserNode->node_name = "TestNode{$i}";
            $UserNode->node_osname = "Linux Ubuntu";
            $UserNode->node_ostype = UserNode::OSTYPE_LINUX;
            $UserNode->node_devicetype = UserNode::DEVICE_DESKTOP;
            $UserNode->node_status = UserNode::STATUS_ACTIVE;
            $UserNode->save();
        }

        $data = [
            'user_email'      => $this->test_emails_pull[1],
            'user_password'   => hash('sha512', "qwerty"),
            'node_hash'       => hash("sha512", uniqid('', true) . microtime()),
            'node_name'       => "TestNode2",
            'node_osname'     => "Linux Ubuntu",
            'node_ostype'     => UserNode::OSTYPE_LINUX,
            'node_devicetype' => UserNode::DEVICE_DESKTOP,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_LICENSE_LIMIT in', $ret['errcode'])->contains(NodeApi::ERROR_LICENSE_LIMIT);
        expect('info in', $ret)->hasKey('info');
    }

    /**
     *
     */
    public function testSuccess()
    {
        $this->createTestData();

        $data = [
            'user_email'      => $this->test_emails_pull[1],
            'user_password'   => hash('sha512', "qwerty"),
            'node_hash'       => hash("sha512", uniqid('', true) . microtime()),
            'node_name'       => "TestNode2",
            'node_osname'     => "Linux Ubuntu",
            'node_ostype'     => UserNode::OSTYPE_LINUX,
            'node_devicetype' => UserNode::DEVICE_DESKTOP,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('success in', $ret['result'])->contains('success');
        expect('no errcode in', $ret)->hasntKey('errcode');
        expect('user_hash in', $ret)->hasKey('user_hash');
        expect('user_hash is string', $ret['user_hash'])->internalType('string');
        expect('user_hash length = 128', strlen($ret['user_hash']) == 128)->true();
        expect('info in', $ret)->hasKey('info');
    }
}