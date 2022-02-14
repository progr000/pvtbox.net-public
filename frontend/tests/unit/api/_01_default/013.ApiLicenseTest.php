<?php
namespace frontend\tests\unit\api\_01_default;

use frontend\models\NodeApi;

class ApiLicenseTest extends ApiDefault
{
    protected $test_action = "license";

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

    }

    /**
     *
     */
    public function testWrongData()
    {
        $data = [
            'user_hash' => "test",
            'node_hash' => "test",
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
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('success in', $ret['result'])->contains('success');
        expect('no errcode in', $ret)->hasntKey('errcode');
        expect('info in', $ret)->hasKey('info');
        expect('info has license_type', $ret['info'])->equals($this->User->license_type);
    }
}