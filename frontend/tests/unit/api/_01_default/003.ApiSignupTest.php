<?php
namespace frontend\tests\unit\api\_01_default;

use common\models\UserNode;
use frontend\models\NodeApi;

class ApiSignupTest extends ApiDefault
{
    protected $test_action = "signup";

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
    public function testExistUser()
    {
        $this->createTestData();

        $data = [
            'user_email'      => $this->User->user_email,
            'user_password'   => hash('sha512', "test"),
            'node_hash'       => hash('sha512', "test"),
            'node_name'       => $this->UserNode->node_name,
            'node_osname'     => "Linux Ubuntu",
            'node_ostype'     => $this->UserNode->node_ostype,
            'node_devicetype' => $this->UserNode->node_devicetype,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_EMAIL_EXIST in', $ret['errcode'])->contains(NodeApi::ERROR_EMAIL_EXIST);
        expect('info in', $ret)->hasKey('info');
    }

    /**
     *
     */
    public function testExistUserNode()
    {
        $this->createTestData();

        $data = [
            'user_email'      => $this->test_emails_pull[2],
            'user_password'   => hash('sha512', "test"),
            'node_hash'       => $this->UserNode->node_hash,
            'node_name'       => "Test2NodeName",
            'node_osname'     => "Linux Ubuntu",
            'node_ostype'     => UserNode::OSTYPE_ANDROID,
            'node_devicetype' => UserNode::DEVICE_PHONE,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_NODEHASH_EXIST in', $ret['errcode'])->contains(NodeApi::ERROR_NODEHASH_EXIST);
        expect('info in', $ret)->hasKey('info');
    }

    /**
     *
     */
    public function testSuccess()
    {
        $data = [
            'user_email'      => $this->test_emails_pull[1],
            'user_password'   => hash('sha512', "test"),
            'node_hash'       =>  hash('sha512', uniqid()),
            'node_name'       => "TestNodeName",
            'node_osname'     => "Linux Ubuntu",
            'node_ostype'     => UserNode::OSTYPE_ANDROID,
            'node_devicetype' => UserNode::DEVICE_PHONE,
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