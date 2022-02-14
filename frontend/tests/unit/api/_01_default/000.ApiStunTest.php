<?php
namespace frontend\tests\unit\api\_01_default;

use frontend\models\NodeApi;

class ApiStunTest extends ApiDefault
{
    protected $test_action = "stun";

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
        expect('get in', $ret['info'])->hasKey('get');
    }

    /**
     *
     */
    public function testWrongData()
    {
        $data = [
            'get' => "test",
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_WRONG_DATA in', $ret['errcode'])->contains(NodeApi::ERROR_WRONG_DATA);
        expect('info in', $ret)->hasKey('info');
        expect('get in', $ret['info'])->hasKey('get');
    }

    /**
     *
     */
    public function testSuccess()
    {
        $data = [
            'get' => "candidate",
        ];

        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('success in', $ret['result'])->contains('success');
        expect('no errcode in', $ret)->hasntKey('errcode');
        expect('info in', $ret)->hasKey('info');
        expect('info is integer', $ret['info'])->internalType('integer');
    }
}