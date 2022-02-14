<?php
namespace common\tests\unit;

use Codeception\TestCase\Test;
use \Mockery;
use common\helpers\FileSys;

/**
 * Class DefaultModel
 * @property \Mockery $redisMock
 */
class DefaultModel extends Test
{
    /** @var \common\UnitTester */
    protected $tester;

    /** @var array */
    protected $test_emails_pull = [
        'user0@noexist.domain',
        'user1@noexist.domain',
        'user2@noexist.domain',
        'user3@noexist.domain',
        'user4@noexist.domain',
        'user5@noexist.domain',
        'user6@noexist.domain',
        'user7@noexist.domain',
        'user8@noexist.domain',
        'user9@noexist.domain',
    ];

    protected $redisMock;

    /**
     *
     */
    protected function _before()
    {
        \Yii::$app->language = 'en';

        //var_dump(\Yii::$app->params['nodeVirtualFS']);exit;
        FileSys::rmdir(\Yii::$app->params['nodeVirtualFS'], true);

        $this->redisMock = Mockery::mock('\yii\redis\Connection');
        \Yii::$app->set('redis', $this->redisMock);
    }

    /**
     *
     */
    protected function _after()
    {
        FileSys::rmdir(\Yii::$app->params['nodeVirtualFS'], true);
        Mockery::close();
    }
}