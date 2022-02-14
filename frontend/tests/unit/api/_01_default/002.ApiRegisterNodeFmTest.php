<?php
namespace frontend\tests\unit\api\_01_default;

use common\models\UserNode;
use frontend\models\NodeApi;

class ApiRegisterNodeFmTest extends ApiDefault
{
    protected $test_action = "registerNodeFM";

    /**
     *
     */
    public function testSuccess()
    {
        $this->createTestData();

        $this->tester->amGoingTo('Test static method that create node-fm');
        $ret = NodeApi::registerNodeFM($this->User);
        expect('ret its object of class UserNode', (is_object($ret) && get_class($ret) == UserNode::className()))->true();
    }
}