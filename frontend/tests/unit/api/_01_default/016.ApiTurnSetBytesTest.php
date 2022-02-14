<?php
namespace frontend\tests\unit\api\_01_default;

use frontend\models\NodeApi;

class ApiTurnSetBytesTest extends ApiDefault
{
    protected $test_action = "turn_set_bytes";

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
        expect('bytes in', $ret['info'])->hasKey('bytes');

    }

    /**
     *
     */
    public function testWrongData()
    {
        $data = [
            'user_hash' => "test",
            'node_hash' => "test",
            'bytes'     => "test"
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
        expect('bytes in', $ret['info'])->hasKey('bytes');
    }

    /**
     *
     */
    public function testLessThanRequired()
    {
        $this->createTestData();

        //$this->User->license_type = Licenses::TYPE_FREE_DEFAULT;
        //$this->User->save();

        $data = [
            'user_hash' => $this->User->user_remote_hash,
            'node_hash' => $this->UserNode->node_hash,
            'bytes'     => 104857600,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('errcode contains', $ret['errcode'])->contains('less than required');
        expect('info in', $ret)->hasKey('info');
        expect('info contains', $ret['info'])->contains('less than required');
    }

    /**
     *
     */
    public function testMoreThanRequired()
    {
        $this->createTestData();

        $this->User->license_bytes_sent = 104857600 * 2;
        $this->User->save();

        $data = [
            'user_hash' => $this->User->user_remote_hash,
            'node_hash' => $this->UserNode->node_hash,
            'bytes'     => 104857600,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('success in', $ret['result'])->contains('success');
        expect('no errcode in', $ret)->hasntKey('errcode');
        expect('info in', $ret)->hasKey('info');
        expect('is array', $ret['info'])->internalType('array');
        expect('allowed in', $ret['info'])->hasKey('allowed');
        expect('is integer', $ret['info']['allowed'])->internalType('integer');
    }
}