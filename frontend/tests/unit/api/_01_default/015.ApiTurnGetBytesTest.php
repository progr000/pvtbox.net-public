<?php
namespace frontend\tests\unit\api\_01_default;

use common\models\Licenses;
use frontend\models\NodeApi;

class ApiTurnGetBytesTest extends ApiDefault
{
    protected $test_action = "turn_get_bytes";

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
    public function testDisabledLimit()
    {
        $this->createTestData();

        $data = [
            'user_hash' => $this->User->user_remote_hash,
            'node_hash' => $this->UserNode->node_hash,
            'bytes'     => 104857600 * 2,
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

    /**
     *
     */
    public function testLimitIsExceeded()
    {
        $this->createTestData();

        $this->User->license_type = Licenses::TYPE_FREE_DEFAULT;
        $this->User->save();

        $License = Licenses::findByType($this->User->license_type);

        $data = [
            'user_hash' => $this->User->user_remote_hash,
            'node_hash' => $this->UserNode->node_hash,
            'bytes'     => $License->license_limit_bytes * 2,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('is array', $ret['errcode'])->internalType('array');
        expect('allowed in', $ret['errcode'])->hasKey('allowed');
        expect('allowed is integer', $ret['errcode']['allowed'])->internalType('integer');
        expect('info in', $ret)->hasKey('info');
        expect('is array', $ret['info'])->internalType('array');
        expect('allowed in', $ret['info'])->hasKey('allowed');
        expect('is integer', $ret['info']['allowed'])->internalType('integer');
    }

    /**
     *
     */
    public function testLimitIsNotExceeded()
    {
        $this->createTestData();

        $this->User->license_type = Licenses::TYPE_FREE_DEFAULT;
        $this->User->save();

        $License = Licenses::findByType($this->User->license_type);

        $data = [
            'user_hash' => $this->User->user_remote_hash,
            'node_hash' => $this->UserNode->node_hash,
            'bytes'     => intval($License->license_limit_bytes / 2),
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('success in', $ret['result'])->contains('success');
        expect('no errcode in', $ret)->hasntKey('errcode');
        expect('info', $ret)->hasKey('info');
        expect('is array', $ret['info'])->internalType('array');
        expect('allowed in', $ret['info'])->hasKey('allowed');
        expect('is integer', $ret['info']['allowed'])->internalType('integer');
    }
}