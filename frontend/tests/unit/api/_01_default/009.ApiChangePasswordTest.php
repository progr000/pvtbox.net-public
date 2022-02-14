<?php
namespace frontend\tests\unit\api\_01_default;

use frontend\models\NodeApi;

class ApiChangePasswordTest extends ApiDefault
{
    protected $test_action = 'changepassword';

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
        expect('old_password in', $ret['info'])->hasKey('old_password');
        expect('new_password in', $ret['info'])->hasKey('new_password');
    }

    /**
     *
     */
    public function testWrongData()
    {
        $data = [
            'user_hash'    => "test",
            'node_hash'    => "test",
            'old_password' => "test",
            'new_password' => "test",
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
        expect('old_password in', $ret['info'])->hasKey('old_password');
        expect('new_password in', $ret['info'])->hasKey('new_password');
    }

    /**
     *
     */
    public function testWrongOldPassword()
    {
        $this->createTestData();

        $data = [
            'user_hash'    => $this->User->user_remote_hash,
            'node_hash'    => $this->UserNode->node_hash,
            'old_password' => hash("sha512", "qwerty1"),
            'new_password' => hash("sha512", "qwerty2"),
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_WRONG_OLDPASSWD in', $ret['errcode'])->contains(NodeApi::ERROR_WRONG_OLDPASSWD);
        expect('info in', $ret)->hasKey('info');
    }

    /**
     *
     */
    public function testSuccess()
    {
        $this->createTestData();

        $data = [
            'user_hash'    => $this->User->user_remote_hash,
            'node_hash'    => $this->UserNode->node_hash,
            'old_password' => hash("sha512", "qwerty"),
            'new_password' => hash("sha512", "qwerty1"),
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