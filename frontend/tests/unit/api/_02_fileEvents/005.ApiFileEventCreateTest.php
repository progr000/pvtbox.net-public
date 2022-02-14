<?php
namespace frontend\tests\unit\api\_02_fileEvents;

use frontend\models\NodeApi;

class ApiFileEventCreateTest extends ApiFileEvents
{
    /** @var string */
    protected $test_action = 'file_event_create';
//
//    /**
//     *
//     */
//    public function testNotExistKeyFolderUuid()
//    {
//        $this->createTestData();
//
//        $data = [
//            'node_hash' => $this->UserNode->node_hash,
//            'user_hash' => $this->User->user_remote_hash,
//            'file_name' => 'FileCreateTest.txt',
//            'file_size' => 256,
//            'diff_file_size' => 256,
//            'hash' => md5(uniqid()),
//        ];
//        $ret = $this->controller->actionTests($this->test_action, $data);
//        //var_dump($ret); exit;
//        expect('is array', $ret)->internalType('array');
//        expect('result in', $ret)->hasKey('result');
//        expect('error in', $ret['result'])->contains('error');
//        expect('errcode in', $ret)->hasKey('errcode');
//        expect('ERROR_WRONG_DATA in', $ret['errcode'])->contains(NodeApi::ERROR_WRONG_DATA);
//        expect('info in', $ret)->hasKey('info');
//        expect('folder_uuid in', $ret['info'])->contains('folder_uuid');
//    }
//
//    /**
//     *
//     */
//    public function testEmptyData()
//    {
//        $data = [
//            'folder_uuid' => "",
//        ];
//        $ret = $this->controller->actionTests($this->test_action, $data);
//        //var_dump($ret); exit;
//        expect('is array', $ret)->internalType('array');
//        expect('result in', $ret)->hasKey('result');
//        expect('error in', $ret['result'])->contains('error');
//        expect('errcode in', $ret)->hasKey('errcode');
//        expect('ERROR_WRONG_DATA in', $ret['errcode'])->contains(NodeApi::ERROR_WRONG_DATA);
//        expect('info in', $ret)->hasKey('info');
//        expect('is array', $ret['info'])->internalType('array');
//        expect('node_hash in', $ret['info'])->hasKey('node_hash');
//        expect('user_hash in', $ret['info'])->hasKey('user_hash');
//        expect('file_name in', $ret['info'])->hasKey('file_name');
//        expect('file_size in', $ret['info'])->hasKey('file_size');
//        expect('diff_file_size in', $ret['info'])->hasKey('diff_file_size');
//        expect('hash in', $ret['info'])->hasKey('hash');
//    }
//
//    /**
//     *
//     */
//    public function testWrongData()
//    {
//        $data = [
//            'folder_uuid' => "test",
//            'node_hash' => "test",
//            'user_hash' => "test",
//            'file_name' => hash('sha512', uniqid()).hash('sha512', uniqid()),
//            'file_size' => "test",
//            'diff_file_size' => "test",
//            'hash' => "test",
//        ];
//        $ret = $this->controller->actionTests($this->test_action, $data);
//        //var_dump($ret); exit;
//        expect('is array', $ret)->internalType('array');
//        expect('result in', $ret)->hasKey('result');
//        expect('error in', $ret['result'])->contains('error');
//        expect('errcode in', $ret)->hasKey('errcode');
//        expect('ERROR_WRONG_DATA in', $ret['errcode'])->contains(NodeApi::ERROR_WRONG_DATA);
//        expect('info in', $ret)->hasKey('info');
//        expect('is array', $ret['info'])->internalType('array');
//        expect('folder_uuid in', $ret['info'])->hasKey('folder_uuid');
//        expect('node_hash in', $ret['info'])->hasKey('node_hash');
//        expect('user_hash in', $ret['info'])->hasKey('user_hash');
//        expect('file_name in', $ret['info'])->hasKey('file_name');
//        expect('file_size in', $ret['info'])->hasKey('file_size');
//        expect('diff_file_size in', $ret['info'])->hasKey('diff_file_size');
//        expect('hash in', $ret['info'])->hasKey('hash');
//    }
//
//    /**
//     * Проверяет присутствует ли проверка getUserAndUserNode() в тестируемом методе
//     */
//    public function testGetUserAndUserNodeIsPresent()
//    {
//        $this->createTestData();
//
//        $data = [
//            'folder_uuid' => "",
//            'node_hash' => hash('sha512', uniqid()),
//            'user_hash' => $this->User->user_remote_hash,
//            'file_name' => 'FileCreateTest.txt',
//            'file_size' => 256,
//            'diff_file_size' => 256,
//            'hash' => md5(uniqid()),
//        ];
//        $ret = $this->controller->actionTests($this->test_action, $data);
//        //var_dump($ret); exit;
//        expect('is array', $ret)->internalType('array');
//        expect('result in', $ret)->hasKey('result');
//        expect('error in', $ret['result'])->contains('error');
//        expect('errcode in', $ret)->hasKey('errcode');
//        expect('ERROR_WRONG_DATA in', $ret['errcode'])->contains(NodeApi::ERROR_NODE_NOT_FOUND);
//        expect('info in', $ret)->hasKey('info');
//        expect('node_hash in', $ret['info'])->contains('node_hash');
//    }
//
//    /**
//     *
//     */
//    public function testMutexLock()
//    {
//        $this->createTestData();
//
//        $this->mutex->acquire($this->mutex_name, MUTEX_WAIT_TIMEOUT);
//
//        $data = [
//            'folder_uuid' => "",
//            'node_hash' => $this->UserNode->node_hash,
//            'user_hash' => $this->User->user_remote_hash,
//            'file_name' => 'FileCreateTest.txt',
//            'file_size' => 256,
//            'diff_file_size' => 256,
//            'hash' => md5(uniqid()),
//        ];
//        $ret = $this->controller->actionTests($this->test_action, $data);
//        //var_dump($ret); exit;
//        expect('is array', $ret)->internalType('array');
//        expect('result in', $ret)->hasKey('result');
//        expect('error in', $ret['result'])->contains('error');
//        expect('errcode in', $ret)->hasKey('errcode');
//        expect('ERROR_WRONG_DATA in', $ret['errcode'])->contains(NodeApi::ERROR_FS_TRY_LATER);
//        expect('info in', $ret)->hasKey('info');
//    }
//

}