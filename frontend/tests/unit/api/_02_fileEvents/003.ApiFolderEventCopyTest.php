<?php
namespace frontend\tests\unit\api\_02_fileEvents;

use common\models\UserFileEvents;
use \Mockery;
use common\models\Licenses;
use common\models\UserColleagues;
use common\models\UserNode;
use frontend\models\NodeApi;
use yii\helpers\Json;

class ApiFolderEventCopyTest extends ApiFileEvents
{
    /** @var string */
    protected $test_action = 'folder_event_copy';

    /**
     *
     */
    public function testEmptyData()
    {
        $data = [
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_WRONG_DATA in', $ret['errcode'])->contains(NodeApi::ERROR_WRONG_DATA);
        expect('info contains', $ret['info'])->contains('target_parent_folder_uuid');

        $data = [
            'target_parent_folder_uuid' => null,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_WRONG_DATA in', $ret['errcode'])->contains(NodeApi::ERROR_WRONG_DATA);
        expect('is array', $ret['info'])->internalType('array');
        expect('node_hash in', $ret['info'])->hasKey('node_hash');
        expect('user_hash in', $ret['info'])->hasKey('user_hash');
        expect('last_event_id in', $ret['info'])->hasKey('last_event_id');
        expect('source_folder_uuid in', $ret['info'])->hasKey('source_folder_uuid');
        expect('target_folder_name in', $ret['info'])->hasKey('target_folder_name');
    }

    /**
     *
     */
    public function testWrongData()
    {
        $data = [
            'node_hash' => "test",
            'user_hash' => "test",
            'last_event_id' => "test",
            'target_parent_folder_uuid' => null,
            'source_folder_uuid' => "test",
            'target_folder_name' => "",
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
        expect('node_hash in', $ret['info'])->hasKey('node_hash');
        expect('user_hash in', $ret['info'])->hasKey('user_hash');
        expect('last_event_id in', $ret['info'])->hasKey('last_event_id');
        expect('source_folder_uuid in', $ret['info'])->hasKey('source_folder_uuid');
        expect('target_folder_name in', $ret['info'])->hasKey('target_folder_name');
    }

    /**
     * Проверяет присутствует ли проверка getUserAndUserNode() в тестируемом методе
     */
    public function testGetUserAndUserNodeIsPresent()
    {
        $this->createTestData();

        $data = [
            'node_hash' => hash('sha512', uniqid()),
            'user_hash' => $this->UserOwner->user_remote_hash,
            'last_event_id' => 1, //$this->UserFolderDataRoot['data']['event_id'],
            'target_parent_folder_uuid' => null,
            'source_folder_uuid' => md5("test"), //$this->UserFolderDataRoot['data']['folder_uuid'],
            'target_folder_name' => 'test',

        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_NODE_NOT_FOUND in', $ret['errcode'])->contains(NodeApi::ERROR_NODE_NOT_FOUND);
        expect('info in', $ret)->hasKey('info');
        expect('node_hash in', $ret['info'])->contains('node_hash');
    }

    /**
     *
     */
    public function testMutexUserIdLock()
    {
        $this->createTestData();
        $this->createTestFolders();

        $this->mutex->acquire($this->mutex_name_owner, MUTEX_WAIT_TIMEOUT);

        $data = [
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'last_event_id' => 1, //$this->UserFolderDataRoot['data']['event_id'],
            'target_parent_folder_uuid' => null,
            'source_folder_uuid' => $this->UserFolderDataRoot['data']['folder_uuid'],
            'target_folder_name' => 'test_copy',
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_FS_TRY_LATER in', $ret['errcode'])->contains(NodeApi::ERROR_FS_TRY_LATER);
        expect('info in', $ret)->hasKey('info');
    }

    /**
     *
     */
    public function testNotExistSourceFolderUuid()
    {
        $this->createTestData();

        $data = [
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'last_event_id' => 1, //$this->UserFolderDataRoot['data']['event_id'],
            'target_parent_folder_uuid' => null,
            'source_folder_uuid' => md5('test'),
            'target_folder_name' => 'test_copy',
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_FS_SYNC_PARENT_NOT_FOUND in', $ret['errcode'])->contains(NodeApi::ERROR_FS_SYNC_PARENT_NOT_FOUND);
        expect('info in', $ret)->hasKey('info');
    }

    /**
     *
     */
    public function testFolderDeleted()
    {
        $this->createTestData();
        $this->createTestFolders();

        /* delete folder first before test */
        $data = [
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_uuid' => $this->UserFolderDataRoot['data']['folder_uuid'],
            'last_event_id' => $this->UserFolderDataRoot['data']['event_id'],
        ];
        $ret = $this->controller->actionTests('folder_event_delete', $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('success in', $ret['result'])->contains('success');
        expect('data in', $ret)->hasKey('data');
        expect('event_id', $ret['data'])->hasKey('event_id');
        $last_event_id = $ret['data']['event_id'];

        unset($data, $ret);
        $data = [
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'last_event_id' => $last_event_id,
            'target_parent_folder_uuid' => null,
            'source_folder_uuid' => $this->UserFolderDataRoot['data']['folder_uuid'],
            'target_folder_name' => 'test_copy',
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_FS_SYNC_PARENT_NOT_FOUND in', $ret['errcode'])->contains(NodeApi::ERROR_FS_SYNC_PARENT_NOT_FOUND);
        expect('info in', $ret)->hasKey('info');
    }


    /**
     *
     */
    public function testDenyCopyRootFolder()
    {
        $this->createTestData();

        $data = [
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'last_event_id' => 1, //$this->UserFolderDataRoot['data']['event_id'],
            'target_parent_folder_uuid' => null,
            'source_folder_uuid' => null,
            'target_folder_name' => 'test_copy',
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_WRONG_DATA in', $ret['errcode'])->contains(NodeApi::ERROR_WRONG_DATA);
        expect('is array', $ret['info'])->internalType('array');
        expect('source_folder_uuid in', $ret['info'])->hasKey('source_folder_uuid');
    }

    /**
     *
     */
    public function testCheckLicenseAccess()
    {
        $this->createTestData();
        $this->createTestFolders();

        /* Мокаем редис и проверяем что в него паблишит */
        $this->redisMock->shouldReceive('publish')
            ->withArgs(["user:{$this->UserOwner->user_id}:license_type_changed", Licenses::TYPE_FREE_DEFAULT])
            ->once()
            ->andReturn(true);
        $this->redisMock->shouldReceive('save')->once()->andReturn(true);

        /* меняем лицензию на фри и создаем ФМ-ноду */
        $this->UserOwner->license_type = Licenses::TYPE_FREE_DEFAULT;
        $this->UserOwner->save();
        $UserOwnerNodeFM = NodeApi::registerNodeFM($this->UserOwner);
        expect('is object', $UserOwnerNodeFM)->isInstanceOf(UserNode::class);

        /* Пробуем скопировать папку с помощью ноды-ФМ - запрет при фрии лицензии */
        unset($data);
        $data = [
            'node_hash' => $UserOwnerNodeFM->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'last_event_id' => $this->UserFolderDataInRoot['data']['event_id'],
            'target_parent_folder_uuid' => null,
            'source_folder_uuid' => $this->UserFolderDataRoot['data']['folder_uuid'],
            'target_folder_name' => 'test_copy',
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('LICENSE_ACCESS in', $ret['errcode'])->contains(NodeApi::ERROR_LICENSE_ACCESS);
        expect('info in', $ret)->hasKey('info');
        expect('info contains', $ret['info'])->contains("No actions possible at Free license");

        /* Пробуем скопировать папку но уже с помощью другой ноды - запрет при фри линцензии */
        $this->mutex->release($this->mutex_name_owner);
        unset($data, $ret);
        $data = [
            'node_hash' => $this->UserNodeOwner2->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'last_event_id' => $this->UserFolderDataInRoot['data']['event_id'],
            'target_parent_folder_uuid' => null,
            'source_folder_uuid' => $this->UserFolderDataRoot['data']['folder_uuid'],
            'target_folder_name' => 'test_copy',
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('LICENSE_ACCESS in', $ret['errcode'])->contains(NodeApi::ERROR_LICENSE_ACCESS);
        expect('info in', $ret)->hasKey('info');
        expect('info contains', $ret['info'])->contains("It has another owner NodeId");
    }

    /**
     *
     */
    public function testCheckExistDestinationOnCopy()
    {
        $this->createTestData();
        $this->createTestFolders();

        $data = [
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'last_event_id' => $this->UserFolderDataRoot['data']['event_id'],
            'target_parent_folder_uuid' => md5('test_destination'),
            'source_folder_uuid' => $this->UserFolderDataRoot['data']['folder_uuid'],
            'target_folder_name' => 'test_copy',
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_FS_SYNC in', $ret['errcode'])->contains(NodeApi::ERROR_FS_SYNC);
        expect('info in', $ret)->hasKey('info');
        expect('info contains', $ret['info'])->contains("Destination folder does not exist");
    }

    //********************************************** дальше не настроено еще

    /**
     * @param array $ret
     */
    private function expectOK($ret)
    {
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('success in', $ret['result'])->contains('success');
        expect('no errcode in', $ret)->hasntKey('errcode');
        expect('info in', $ret)->hasKey('info');
        expect('data in', $ret)->hasKey('data');
        expect('is array', $ret['data'])->internalType('array');
        expect('event_id', $ret['data'])->hasKey('event_id');
        expect('is integer', $ret['data']['event_id'])->internalType('integer');
        expect('event_uuid', $ret['data'])->hasKey('event_uuid');
        expect('is string', $ret['data']['event_uuid'])->internalType('string');
        expect('length = 32', strlen($ret['data']['event_uuid']) == 32)->true();
        expect('folder_uuid', $ret['data'])->hasKey('folder_uuid');
        expect('is string', $ret['data']['folder_uuid'])->internalType('string');
        expect('length = 32', strlen($ret['data']['folder_uuid']) == 32)->true();
        expect('timestamp', $ret['data'])->hasKey('timestamp');
        expect('is integer', $ret['data']['timestamp'])->internalType('integer');
    }

    /**
     *
     */
    public function testSuccessDeleteFolder()
    {
        $this->createTestData();
        $this->createTestFolders();

        /* Мокаем редис и проверяем что в него паблишит */
        $arg = "user:{$this->UserOwner->user_id}:fs_events";
        $this->redisMock->shouldReceive('publish')
            ->withArgs(function ($argument1, $argument2) use ($arg) {
                //var_dump($argument1);exit;
                if ($argument1 != $arg) {
                    return false;
                }

                $tmp = Json::decode($argument2);
                //var_dump($tmp); exit;
                if (!isset($tmp[0]['data']['event_type_int']) || $tmp[0]['data']['event_type_int'] != UserFileEvents::TYPE_DELETE) {
                    return false;
                }

                return true;
            })
            ->atLeast()->times(2)
            ->andReturn(true);
        $this->redisMock->shouldReceive('save')->atLeast()->times(2)->andReturn(true);

        /* delete in folder*/
        $data = [
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_uuid' => $this->UserFolderDataInRoot['data']['folder_uuid'],
            'last_event_id' => $this->UserFolderDataInRoot['data']['event_id'],
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        $this->expectOK($ret);
        expect('is string', $ret['data']['folder_uuid'])->equals($this->UserFolderDataInRoot['data']['folder_uuid']);

        /* delete root folder*/
        unset($data, $ret);
        $data = [
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_uuid' => $this->UserFolderDataRoot['data']['folder_uuid'],
            'last_event_id' => $this->UserFolderDataRoot['data']['event_id'],
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        $this->expectOK($ret);
        expect('is string', $ret['data']['folder_uuid'])->equals($this->UserFolderDataRoot['data']['folder_uuid']);
        //exit;
    }

    /**
     *
     */
    public function testFolderAlreadyDeleted()
    {
        $this->testSuccessDeleteFolder();

        /* delete in folder*/
        $data = [
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_uuid' => $this->UserFolderDataInRoot['data']['folder_uuid'],
            'last_event_id' => $this->UserFolderDataInRoot['data']['event_id'],
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('FS_SYNC_NOT_FOUND in', $ret['errcode'])->contains(NodeApi::ERROR_FS_SYNC_NOT_FOUND);
        expect('info in', $ret)->hasKey('info');
        expect('info contains', $ret['info'])->contains("Folder not found.");

        /* delete root folder*/
        $this->mutex->release($this->mutex_name_owner);
        unset($data, $ret);
        $data = [
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_uuid' => $this->UserFolderDataRoot['data']['folder_uuid'],
            'last_event_id' => $this->UserFolderDataRoot['data']['event_id'],
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('FS_SYNC_NOT_FOUND in', $ret['errcode'])->contains(NodeApi::ERROR_FS_SYNC_NOT_FOUND);
        expect('info in', $ret)->hasKey('info');
        expect('info contains', $ret['info'])->contains("Folder not found.");
    }

    /**
     *
     */
    public function testSynchronizationConflictLastEventId()
    {
        $this->createTestData();
        $this->createTestFolders();

        /* delete in folder*/
        $data = [
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_uuid' => $this->UserFolderDataInRoot['data']['folder_uuid'],
            'last_event_id' => 1, //$this->UserFolderDataInRoot['data']['event_id'],
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('FS_SYNC in', $ret['errcode'])->contains(NodeApi::ERROR_FS_SYNC);
        expect('info in', $ret)->hasKey('info');
        expect('info contains', $ret['info'])->contains("Synchronization conflict");
        expect('debug in', $ret)->hasKey('debug');
        expect('debug contains', $ret['debug'])->contains("max_event_id");
        expect('debug contains', $ret['debug'])->contains("last_event_id");
    }

    /**
     *
     */
    public function testPresentEventUuidInDataAndItWrong()
    {
        $this->createTestData();
        $this->createTestFolders();

        $data = [
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_uuid' => $this->UserFolderDataRoot['data']['folder_uuid'],
            'last_event_id' => $this->UserFolderDataRoot['data']['event_id'],
            'event_uuid' => "test",
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
        expect('event_uuid in', $ret['info'])->hasKey('event_uuid');
    }

    /**
     *
     */
    public function testPresentEventUuidInDataAndItNotExist()
    {
        $this->createTestData();
        $this->createTestFolders();

        $data = [
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_uuid' => $this->UserFolderDataRoot['data']['folder_uuid'],
            'last_event_id' => $this->UserFolderDataRoot['data']['event_id'],
            'event_uuid' => md5(uniqid()),
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        $this->expectOK($ret);
    }

    /**
     *
     */
    public function testPresentEventUuidInDataAndItExist()
    {
        $this->createTestData();
        $this->createTestFolders();

        $data = [
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_uuid' => $this->UserFolderDataRoot['data']['folder_uuid'],
            'last_event_id' => $this->UserFolderDataRoot['data']['event_id'],
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        $this->expectOK($ret);
        //var_dump($ret); exit;
        if (isset($ret['result'], $ret['data']['event_uuid']) && $ret['result'] == 'success') {

            $data = [
                'node_hash' => $this->UserNodeOwner1->node_hash,
                'user_hash' => $this->UserOwner->user_remote_hash,
                'folder_uuid' => $this->UserFolderDataRoot['data']['folder_uuid'],
                'last_event_id' => $this->UserFolderDataRoot['data']['event_id'],
                'event_uuid' => $ret['data']['event_uuid'],
            ];
            $ret2 = $this->controller->actionTests($this->test_action, $data);
            //var_dump($ret2); exit;
            $this->expectOK($ret2);
            expect('event_uuid_already_exists in ', $ret2['data'])->hasKey('event_uuid_already_exists');
            expect('is true', $ret2['data']['event_uuid_already_exists'])->true();
        } else {
            $this->fail('failed previous method');
        }
    }

    /**
     *
     */
    public function testCollaborationAccess()
    {
        $this->createTestData();
        $this->createTestFolders();

        //$this->UserFolderDataRoot['data']['folder_uuid'],

        /* Для теста доступа по коллаборации нужно создать папку и потом коллабу из нее */

        /* Инициализация коллаборации с юзером UserColleague (инвайт с правами view) по созданной папке */
        /* Акцептим инвайт (Джойним) юзера в коллаборацию */
        $retAcceptCollaboration = $this->createCollaboration(
            $this->UserOwner->user_id,
            $this->UserFolderDataRoot['data']['folder_uuid'],
            $this->UserColleague->user_email,
            UserColleagues::PERMISSION_VIEW
        );

        /* нужно получить last_event_id для папок коллеги */
        $last_event_id_rootFolder = $this->getLastEventId(
            $this->UserFolderDataRoot['data']['folder_uuid'],
            $this->UserColleague->user_id
        );

        $last_event_id_folderInRootFolder = $this->getLastEventId(
            $this->UserFolderDataInRoot['data']['folder_uuid'],
            $this->UserColleague->user_id
        );

        /* Пробуем удалить папку с помощью аккаунта коллеги (уже джойнед но с правами view) в коллаборации */
        unset($data);
        $data = [
            'node_hash' => $this->UserNodeColleague1->node_hash,
            'user_hash' => $this->UserColleague->user_remote_hash,
            'folder_uuid' => $this->UserFolderDataInRoot['data']['folder_uuid'],
            'last_event_id' => $last_event_id_folderInRootFolder,
        ];
        $retInCollaboration = $this->controller->actionTests($this->test_action, $data);
        //var_dump($retInCollaboration); exit;
        expect('is array', $retInCollaboration)->internalType('array');
        expect('result in', $retInCollaboration)->hasKey('result');
        expect('error in', $retInCollaboration['result'])->contains('error');
        expect('errcode in', $retInCollaboration)->hasKey('errcode');
        expect('ERROR_COLLABORATION_ACCESS in', $retInCollaboration['errcode'])->contains(NodeApi::ERROR_COLLABORATION_ACCESS);
        expect('info in', $retInCollaboration)->hasKey('info');

        /* Теперь меняем права коллеге на edit */
        $retChangeAccessInCollaboration = $this->changeColleagueAccessToCollaboration(
            $this->UserOwner->user_id,
            $retAcceptCollaboration['data']['colleague_id'],
            $this->UserFolderDataRoot['data']['folder_uuid'],
            UserColleagues::PERMISSION_EDIT
        );

        /* Мокаем редис и проверяем что в него паблишит */
        $arg = "user:{$this->UserOwner->user_id}:fs_events";
        $this->redisMock->shouldReceive('publish')
            ->withArgs(function ($argument1, $argument2) use ($arg) {
                //var_dump($argument1);exit;
                if ($argument1 != $arg) {
                    return false;
                }

                $tmp = Json::decode($argument2);
                //var_dump($tmp); exit;
                if (!isset($tmp[0]['data']['event_type_int']) || $tmp[0]['data']['event_type_int'] != UserFileEvents::TYPE_DELETE) {
                    return false;
                }

                return true;
            })
            ->atLeast()->times(1)
            ->andReturn(true);

        $arg2 = "collaboration:{$retAcceptCollaboration['data']['collaboration_id']}:fsevent";
        $this->redisMock->shouldReceive('publish')
            ->withArgs(function ($argument1, $argument2) use ($arg2) {
                //var_dump($argument1);exit;
                if ($argument1 != $arg2) {
                    return false;
                }

                $tmp = Json::decode($argument2);
                //var_dump($tmp); exit;
                if (!isset($tmp[0]['data']['event_type_int']) || $tmp[0]['data']['event_type_int'] != UserFileEvents::TYPE_DELETE) {
                    return false;
                }

                return true;
            })
            ->atLeast()->times(1)
            ->andReturn(true);

        $this->redisMock->shouldReceive('save')->atLeast()->times(2)->andReturn(true);

        /* Снова пробуем удалить папку с помощью аккаунта коллеги (уже джойнед и с правами edit) в коллаборации */
        $this->mutex->release($this->mutex_name_owner);
        $this->mutex->release($this->mutex_name_colleague);
        unset($data);
        $data = [
            'node_hash' => $this->UserNodeColleague1->node_hash,
            'user_hash' => $this->UserColleague->user_remote_hash,
            'folder_uuid' => $this->UserFolderDataInRoot['data']['folder_uuid'],
            'last_event_id' => $last_event_id_folderInRootFolder,
        ];
        $retInCollaboration = $this->controller->actionTests($this->test_action, $data);
        //var_dump($retInCollaboration); exit;
        $this->expectOK($retInCollaboration);
    }

    /**
     *
     */
    public function testMutexCollaborationIdLock()
    {
        $this->createTestData();
        $this->createTestFolders();

        /* Инициализация коллаборации с юзером UserColleague (инвайт с правами edit) по созданной папке */
        /* Акцептим инвайт (Джойним) юзера в коллаборацию */
        $retAcceptCollaboration = $this->createCollaboration(
            $this->UserOwner->user_id,
            $this->UserFolderDataRoot['data']['folder_uuid'],
            $this->UserColleague->user_email,
            UserColleagues::PERMISSION_EDIT
        );

        /* нужно получить last_event_id для папок коллеги */
        $last_event_id_rootFolder = $this->getLastEventId(
            $this->UserFolderDataRoot['data']['folder_uuid'],
            $this->UserColleague->user_id
        );

        $last_event_id_folderInRootFolder = $this->getLastEventId(
            $this->UserFolderDataInRoot['data']['folder_uuid'],
            $this->UserColleague->user_id
        );

        /* Создаем иссскуственно блокировку mutex по коллаборации */
        $this->mutex->acquire('collaboration_id_' . $retAcceptCollaboration['data']['collaboration_id'] , MUTEX_WAIT_TIMEOUT);

        /* Пробуем удалить папку с помощью аккаунта коллеги (уже джойнед и с правами edit) в коллаборации */
        unset($data);
        $data = [
            'node_hash' => $this->UserNodeColleague1->node_hash,
            'user_hash' => $this->UserColleague->user_remote_hash,
            'folder_uuid' => $this->UserFolderDataInRoot['data']['folder_uuid'],
            'last_event_id' => $last_event_id_folderInRootFolder,
        ];
        $retInCollaboration = $this->controller->actionTests($this->test_action, $data);
        //var_dump($retInCollaboration); exit;
        expect('is array', $retInCollaboration)->internalType('array');
        expect('result in', $retInCollaboration)->hasKey('result');
        expect('error in', $retInCollaboration['result'])->contains('error');
        expect('errcode in', $retInCollaboration)->hasKey('errcode');
        expect('FS_TRY_LATER in', $retInCollaboration['errcode'])->contains(NodeApi::ERROR_FS_TRY_LATER);
        expect('info in', $retInCollaboration)->hasKey('info');
        expect('info contains', $retInCollaboration['info'])->contains("Collaboration is locked now");

        /* Пробуем удалить папку с помощью аккаунта ОВНЕРА */
        //$this->mutex->release($this->mutex_name_owner);
        //$this->mutex->release($this->mutex_name_colleague);
        unset($data);
        $data = [
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_uuid' => $this->UserFolderDataInRoot['data']['folder_uuid'],
            'last_event_id' => $this->UserFolderDataInRoot['data']['event_id'],
        ];
        $retInCollaboration = $this->controller->actionTests($this->test_action, $data);
        //var_dump($retInCollaboration); exit;
        expect('is array', $retInCollaboration)->internalType('array');
        expect('result in', $retInCollaboration)->hasKey('result');
        expect('error in', $retInCollaboration['result'])->contains('error');
        expect('errcode in', $retInCollaboration)->hasKey('errcode');
        expect('FS_TRY_LATER in', $retInCollaboration['errcode'])->contains(NodeApi::ERROR_FS_TRY_LATER);
        expect('info in', $retInCollaboration)->hasKey('info');
        expect('info contains', $retInCollaboration['info'])->contains("Collaboration is locked now");
    }

    /**
     *
     */
    public function testDeleteMainCollaborationFolderByOwner()
    {
        $this->createTestData();
        $this->createTestFolders();

        /* Инициализация коллаборации с юзером UserColleague (инвайт с правами edit) по созданной папке */
        /* Акцептим инвайт (Джойним) юзера в коллаборацию */
        $retAcceptCollaboration = $this->createCollaboration(
            $this->UserOwner->user_id,
            $this->UserFolderDataRoot['data']['folder_uuid'],
            $this->UserColleague->user_email,
            UserColleagues::PERMISSION_EDIT
        );

        /* Мокаем редис и проверяем что в него паблишит */
        $arg = "user:{$this->UserOwner->user_id}:fs_events";
        $this->redisMock->shouldReceive('publish')
            ->withArgs(function ($argument1, $argument2) use ($arg) {
                //var_dump($arg);
                if ($argument1 != $arg) {
                    return false;
                }

                $tmp = Json::decode($argument2);
                //var_dump($tmp);
                if (!isset($tmp[0]['data']['event_type_int']) || $tmp[0]['data']['event_type_int'] != UserFileEvents::TYPE_DELETE) {
                    return false;
                }

                return true;
            })
            ->atLeast()->times(1)
            ->andReturn(true);


        $arg2 = "collaboration:{$retAcceptCollaboration['data']['collaboration_id']}:userdel";
        $this->redisMock->shouldReceive('publish')
            ->withArgs(function ($argument1, $argument2) use ($arg2, $retAcceptCollaboration) {
                //var_dump($argument1);
                if ($argument1 != $arg2) {
                    return false;
                }

                $tmp = Json::decode($argument2);
                //var_dump($argument2);
                if ($argument2 != $retAcceptCollaboration['data']['user_id']) {
                    return false;
                }

                return true;
            })
            ->atLeast()->times(1)
            ->andReturn(true);


        $this->redisMock->shouldReceive('save')->atLeast()->times(1)->andReturn(true);

        /* Пробуем удалить папку с помощью аккаунта ОВНЕРА */
        unset($data);
        $data = [
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_uuid' => $this->UserFolderDataRoot['data']['folder_uuid'],
            'last_event_id' => $this->UserFolderDataRoot['data']['event_id'],
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit
        //exit;
        $this->expectOK($ret);
    }
}