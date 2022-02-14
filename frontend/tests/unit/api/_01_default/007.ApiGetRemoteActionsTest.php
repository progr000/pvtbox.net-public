<?php
namespace frontend\tests\unit\api\_01_default;

use common\models\RemoteActions;
use frontend\models\NodeApi;

class ApiGetRemoteActionsTest extends ApiDefault
{
    protected $test_action = 'getRemoteActions';

    /**
     *
     */
    public function testEmptyResultSuccess()
    {
        $this->createTestData();

        $this->tester->amGoingTo('Test static method that gets remote actions');
        $ret = NodeApi::getRemoteActions($this->UserNode->node_id);
        //var_dump($ret); exit;
        expect('ret is array', $ret)->internalType('array');
        expect('ret is empty array', $ret)->isEmpty();
    }

    /**
     *
     */
    public function testNotEmptyResultSuccess()
    {
        $this->createTestData();

        $data = [
            'node_hash'      => $this->UserNode->node_hash,
            'user_hash'      => $this->User->user_remote_hash,
            'target_node_id' => $this->UserNode->node_id,
            'action_type'    => RemoteActions::TYPE_LOGOUT,
        ];
        $ret1 = $this->controller->actionTests('execute_remote_action', $data);
        //var_dump($ret1); exit;
        if (isset($ret1['result']) && $ret1['result'] == 'success') {
            $this->tester->amGoingTo('Test static method that gets remote actions');
            $ret = NodeApi::getRemoteActions($this->UserNode->node_id);
            //var_dump($ret); exit;
            expect('ret is array', $ret)->internalType('array');
            expect('ret is not empty array', $ret)->notEmpty();
            expect('ret has key 0', $ret)->hasKey(0);
            expect('ret[0] has key', $ret[0])->hasKey('action_type');
            expect('ret[0] has key', $ret[0])->hasKey('action_uuid');
            expect('ret[0] has key', $ret[0])->hasKey('action_data');
        } else {
            $this->fail('Method execute_remote_action is fail. Check it before.');
        }
    }

}