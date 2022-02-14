<?php
namespace frontend\tests\unit\api\_02_fileEvents;

use frontend\models\NodeApi;

class ApiUniqUuidTest extends ApiFileEvents
{
    protected $test_action = 'uniq_uuid';

    /**
     *
     */
    public function testSuccess()
    {
        $ret = NodeApi::uniq_uuid();
        //var_dump($ret); exit;
        expect('is string', $ret)->internalType('string');
        expect('length = 32', strlen($ret) == 32)->true();
    }
}