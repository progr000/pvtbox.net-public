<?php
namespace frontend\tests\unit\api\_02_fileEvents;

use yii\helpers\Json;
use common\models\Licenses;
use common\models\UserColleagues;
use common\models\UserFileEvents;
use common\models\UserFiles;
use common\models\UserNode;
use frontend\models\NodeApi;

class ApiFolderEventCreateTest extends ApiFileEvents
{
    /** @var string */
    protected $test_action = 'folder_event_create';

    /**
     *
     */
    public function testNotExistKeyParentFolderUuid()
    {
        $this->createTestData();

        $data = [
            'node_hash'   => $this->UserNodeOwner1->node_hash,
            'user_hash'   => $this->UserOwner->user_remote_hash,
            'folder_name' => 'FolderCreateTest',
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_WRONG_DATA in', $ret['errcode'])->contains(NodeApi::ERROR_WRONG_DATA);
        expect('info in', $ret)->hasKey('info');
        expect('parent_folder_uuid in', $ret['info'])->contains('parent_folder_uuid');
    }

    /**
     *
     */
    public function testEmptyData()
    {
        $data = [
            'parent_folder_uuid' => "",
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
        expect('folder_name in', $ret['info'])->hasKey('folder_name');
    }

    /**
     *
     */
    public function testWrongData()
    {
        $data = [
            'parent_folder_uuid' => "test",
            'node_hash' => "test",
            'user_hash' => "test",
            'folder_name' => hash('sha512', uniqid()).hash('sha512', uniqid()),
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
        expect('parent_folder_uuid in', $ret['info'])->hasKey('parent_folder_uuid');
        expect('node_hash in', $ret['info'])->hasKey('node_hash');
        expect('user_hash in', $ret['info'])->hasKey('user_hash');
        expect('folder_name in', $ret['info'])->hasKey('folder_name');
    }

    /**
     * Проверяет присутствует ли проверка getUserAndUserNode() в тестируемом методе
     */
    public function testGetUserAndUserNodeIsPresent()
    {
        $this->createTestData();

        $data = [
            'parent_folder_uuid' => "",
            'node_hash' => hash('sha512', uniqid()),
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_name' => 'FolderCreateTest',
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

        $this->mutex->acquire($this->mutex_name_owner, MUTEX_WAIT_TIMEOUT);

        $data = [
            'parent_folder_uuid' => "",
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_name' => 'FolderCreateTest',
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
    public function testNotExistParentFolderUuid()
    {
        $this->createTestData();

        $data = [
            'parent_folder_uuid' => md5(uniqid()),
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_name' => 'FolderCreateTest',
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
    public function testSuccessCreateFolder()
    {
        $this->createTestData();

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
                if (!isset($tmp[0]['data']['event_type_int']) || $tmp[0]['data']['event_type_int'] != UserFileEvents::TYPE_CREATE) {
                    return false;
                }

                return true;
            })
            ->atLeast()->times(1)
            ->andReturn(true);
        $this->redisMock->shouldReceive('save')->atLeast()->times(1)->andReturn(true);

        $data = [
            'parent_folder_uuid' => "",
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_name' => 'FolderCreateTest',
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        $this->expectOK($ret);
    }

    /**
     *
     */
    public function testCheckLicenseAccess()
    {
        $this->createTestData();

        $data = [
            'parent_folder_uuid' => "",
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_name' => 'FolderCreateTest',
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        $this->expectOK($ret);

        //var_dump($ret);exit;
        if (isset($ret['result'], $ret['data']['folder_uuid']) && $ret['result'] == 'success') {

            /* Мокаем редис и проверяем что в него паблишит */
            $this->redisMock->shouldReceive('publish')
                ->withArgs(["user:{$this->UserOwner->user_id}:license_type_changed", Licenses::TYPE_FREE_DEFAULT])
                ->once()
                ->andReturn(true);
            $this->redisMock->shouldReceive('publish')
                ->withArgs(["user:{$this->UserOwner->user_id}:new_notifications_count", 1])
                ->once()
                ->andReturn(true);
            $this->redisMock->shouldReceive('save')->twice()->andReturn(true);

            /* меняем лицензию на фри и создаем ФМ-ноду */
            $this->UserOwner->license_type = Licenses::TYPE_FREE_DEFAULT;
            $this->UserOwner->save();
            $UserOwnerNodeFM = NodeApi::registerNodeFM($this->UserOwner);
            expect('is object', $UserOwnerNodeFM)->isInstanceOf(UserNode::class);

            /* Пробуем создать папку с помощью ноды-ФМ - запрет при фрии лицензии */
            unset($data);
            $data = [
                'parent_folder_uuid' => $ret['data']['folder_uuid'],
                'node_hash' => $UserOwnerNodeFM->node_hash,
                'user_hash' => $this->UserOwner->user_remote_hash,
                'folder_name' => 'FolderCreateInParent',
            ];
            $retInParent = $this->controller->actionTests($this->test_action, $data);
            //var_dump($retInParent); exit;
            expect('is array', $retInParent)->internalType('array');
            expect('result in', $retInParent)->hasKey('result');
            expect('error in', $retInParent['result'])->contains('error');
            expect('errcode in', $retInParent)->hasKey('errcode');
            expect('LICENSE_ACCESS in', $retInParent['errcode'])->contains(NodeApi::ERROR_LICENSE_ACCESS);
            expect('info in', $retInParent)->hasKey('info');
            expect('info contains', $retInParent['info'])->contains("No actions possible at Free license");

            /* Пробуем создать папку в папке но уже с помощью другой ноды - запрет при фри линцензии */
            $this->mutex->release($this->mutex_name_owner);
            unset($data, $retInParent);
            $data = [
                'parent_folder_uuid' => $ret['data']['folder_uuid'],
                'node_hash' => $this->UserNodeOwner2->node_hash,
                'user_hash' => $this->UserOwner->user_remote_hash,
                'folder_name' => 'FolderCreateInParent',
            ];
            $retInParent = $this->controller->actionTests($this->test_action, $data);
            //var_dump($retInParent); exit;
            expect('is array', $retInParent)->internalType('array');
            expect('result in', $retInParent)->hasKey('result');
            expect('error in', $retInParent['result'])->contains('error');
            expect('errcode in', $retInParent)->hasKey('errcode');
            expect('LICENSE_ACCESS in', $retInParent['errcode'])->contains(NodeApi::ERROR_LICENSE_ACCESS);
            expect('info in', $retInParent)->hasKey('info');
            expect('info contains', $retInParent['info'])->contains("It has another owner NodeId");

        }
    }

    /**
     *
     */
    public function testFolderAlreadyExist()
    {
        $this->createTestData();

        $data = [
            'parent_folder_uuid' => "",
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_name' => 'FolderCreateTest',
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        if (isset($ret['result']) && $ret['result'] == 'success') {

            $data = [
                'parent_folder_uuid' => "",
                'node_hash' => $this->UserNodeOwner1->node_hash,
                'user_hash' => $this->UserOwner->user_remote_hash,
                'folder_name' => 'FolderCreateTest',
            ];
            $ret = $this->controller->actionTests($this->test_action, $data);
            //var_dump($ret); exit;
            expect('is array', $ret)->internalType('array');
            expect('result in', $ret)->hasKey('result');
            expect('error in', $ret['result'])->contains('error');
            expect('errcode in', $ret)->hasKey('errcode');
            expect('ERROR_FS_SYNC in', $ret['errcode'])->contains(NodeApi::ERROR_FS_SYNC);
            expect('info in', $ret)->hasKey('info');
            expect('error_data in', $ret)->hasKey('error_data');
            expect('is array', $ret['error_data'])->internalType('array');
            expect('file_hash in', $ret['error_data'])->hasKey('file_hash');
            expect('file_md5 in', $ret['error_data'])->hasKey('file_md5');
            expect('file_size in', $ret['error_data'])->hasKey('file_size');
            expect('file_name in', $ret['error_data'])->hasKey('file_name');

        } else {
            $this->fail("Failed create folder before test.");
        }
    }

    /**
     *
     */
    public function testPresentEventUuidInDataAndItWrong()
    {
        $this->createTestData();

        $data = [
            'parent_folder_uuid' => "",
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_name' => 'FolderCreateTest',
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

        $data = [
            'parent_folder_uuid' => "",
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_name' => 'FolderCreateTest',
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

        $data = [
            'parent_folder_uuid' => "",
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_name' => 'FolderCreateTest',
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        $this->expectOK($ret);
        //var_dump($ret);
        if (isset($ret['result'], $ret['data']['event_uuid']) && $ret['result'] == 'success') {

            $data = [
                'parent_folder_uuid' => "",
                'node_hash' => $this->UserNodeOwner1->node_hash,
                'user_hash' => $this->UserOwner->user_remote_hash,
                'folder_name' => 'FolderCreateTest',
                'event_uuid' => $ret['data']['event_uuid'],
            ];
            $ret2 = $this->controller->actionTests($this->test_action, $data);
            //var_dump($ret); exit;
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
    public function testSuccessCreateFolderInParent()
    {
        $this->createTestData();

        $data = [
            'parent_folder_uuid' => "",
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_name' => 'FolderCreateTest',
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        $this->expectOK($ret);
        //var_dump($ret); exit;
        if (isset($ret['result'], $ret['data']['folder_uuid']) && $ret['result'] == 'success') {

            $data = [
                'parent_folder_uuid' => $ret['data']['folder_uuid'],
                'node_hash' => $this->UserNodeOwner1->node_hash,
                'user_hash' => $this->UserOwner->user_remote_hash,
                'folder_name' => 'FolderCreateTest2',
            ];
            $ret = $this->controller->actionTests($this->test_action, $data);
            //var_dump($ret); exit;
            $this->expectOK($ret);
        } else {
            $this->fail('failed previous method');
        }
    }

    /**
     *
     */
    public function testFilePathMaxLength()
    {
        $this->createTestData();
        $this->UserOwner->generatePathForUser();

        /* Высчитываем максимальный допустимй path length */
        $max_path_length = UserFiles::FILE_PATH_MAX_LENGTH;
        $folder_name = "Тестовая_папка_Тестовая_папка_Тестовая_папка_Тестовая_папка_Тестовая_папка_Тестовая_папка_Тестовая_папка_Тестовая_папка_Тестовая_папка";
        $full_path_length = mb_strlen($this->UserOwner->_full_path, '8bit');
        //var_dump($full_path_length);
        $max_path_length = $max_path_length - $full_path_length;
        $folder_name_length = mb_strlen($folder_name, '8bit');
        $cnt = intval(ceil($max_path_length / $folder_name_length));
        $max_path_length = $max_path_length - ($cnt - 1);
        $cnt = intval(ceil($max_path_length / $folder_name_length));
        //var_dump($max_path_length);
        //var_dump($folder_name_length);
        //var_dump($cnt); exit;

        /* в цикле создаем папку в папке, пока не достигнем желаемой для тестов ошибки */
        $parent_folder_uuid = "";
        for ($i=1; $i<$cnt; $i++) {

            $data = [
                'parent_folder_uuid' => $parent_folder_uuid,
                'node_hash' => $this->UserNodeOwner1->node_hash,
                'user_hash' => $this->UserOwner->user_remote_hash,
                'folder_name' => $folder_name,
            ];
            $ret = $this->controller->actionTests($this->test_action, $data);
            //var_dump($ret);
            if ($i == $cnt) {
                expect('is array', $ret)->internalType('array');
                expect('result in', $ret)->hasKey('result');
                expect('error in', $ret['result'])->contains('error');
                expect('errcode in', $ret)->hasKey('errcode');
                expect('ERROR_FILE_PATH_MAX_LENGTH in', $ret['errcode'])->contains(NodeApi::ERROR_FILE_PATH_MAX_LENGTH);
                expect('info in', $ret)->hasKey('info');
            } else {
                $this->expectOK($ret);
            }
            if (isset($ret['result'], $ret['data']['folder_uuid']) && $ret['result'] == 'success') {
                $parent_folder_uuid = $ret['data']['folder_uuid'];
            } else {
                //$this->fail('failed recursive create folder');
                //break;
            }
        }
        //var_dump($i); exit;
    }

    /**
     *
     */
    public function testCollaborationAccess()
    {
        $this->createTestData();

        /* Для теста доступа по коллаборации нужно создать папку и потом коллабу из нее */
        $data = [
            'parent_folder_uuid' => "",
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_name' => 'FolderCreateTest',
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        $this->expectOK($ret);

        //var_dump($ret);exit;
        if (isset($ret['result'], $ret['data']['folder_uuid']) && $ret['result'] == 'success') {

            /* Инициализация коллаборации с юзером UserColleague (инвайт с правами view) по созданной папке */
            /* Акцептим инвайт (Джойним) юзера в коллаборацию */
            $retAcceptCollaboration = $this->createCollaboration(
                $this->UserOwner->user_id,
                $ret['data']['folder_uuid'],
                $this->UserColleague->user_email,
                UserColleagues::PERMISSION_VIEW
            );

            /* Пробуем создать папку с помощью аккаунта коллеги (уже джойнед но с правами view) в коллаборации */
            unset($data);
            $data = [
                'parent_folder_uuid' => $ret['data']['folder_uuid'],
                'node_hash' => $this->UserNodeColleague1->node_hash,
                'user_hash' => $this->UserColleague->user_remote_hash,
                'folder_name' => 'FolderCreateInCollaborationTest',
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
                $ret['data']['folder_uuid'],
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
                    if (!isset($tmp[0]['data']['event_type_int']) || $tmp[0]['data']['event_type_int'] != UserFileEvents::TYPE_CREATE) {
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
                    if (!isset($tmp[0]['data']['event_type_int']) || $tmp[0]['data']['event_type_int'] != UserFileEvents::TYPE_CREATE) {
                        return false;
                    }

                    return true;
                })
                ->atLeast()->times(1)
                ->andReturn(true);

            $this->redisMock->shouldReceive('save')->atLeast()->times(2)->andReturn(true);

            /* Снова пробуем создать папку с помощью аккаунта коллеги (уже джойнед и с правами edit) в коллаборации */
            $this->mutex->release($this->mutex_name_owner);
            $this->mutex->release($this->mutex_name_colleague);
            unset($data);
            $data = [
                'parent_folder_uuid' => $ret['data']['folder_uuid'],
                'node_hash' => $this->UserNodeColleague1->node_hash,
                'user_hash' => $this->UserColleague->user_remote_hash,
                'folder_name' => 'FolderCreateInCollaborationTest',
            ];
            $retInCollaboration = $this->controller->actionTests($this->test_action, $data);
            //var_dump($retInCollaboration); exit;
            $this->expectOK($retInCollaboration);
        }

    }

    /**
     *
     */
    public function testMutexCollaborationIdLock()
    {
        $this->createTestData();

        /* Для теста мутекс-лока по коллаборации нужно создать папку и потом коллабу из нее */
        $data = [
            'parent_folder_uuid' => "",
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_name' => 'FolderCreateTest',
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        $this->expectOK($ret);

        //var_dump($ret);exit;
        if (isset($ret['result'], $ret['data']['folder_uuid']) && $ret['result'] == 'success') {

            /* Инициализация коллаборации с юзером UserColleague (инвайт с правами view) по созданной папке */
            /* Акцептим инвайт (Джойним) юзера в коллаборацию */
            $retAcceptCollaboration = $this->createCollaboration(
                $this->UserOwner->user_id,
                $ret['data']['folder_uuid'],
                $this->UserColleague->user_email,
                UserColleagues::PERMISSION_EDIT
            );

            /* Создаем иссскуственно блокировку mutex по коллаборации */
            $this->mutex->acquire('collaboration_id_' . $retAcceptCollaboration['data']['collaboration_id'] , MUTEX_WAIT_TIMEOUT);

            /* Пробуем создать папку с помощью аккаунта коллеги (уже джойнед и с правами edit) в коллаборации */
            unset($data);
            $data = [
                'parent_folder_uuid' => $ret['data']['folder_uuid'],
                'node_hash' => $this->UserNodeColleague1->node_hash,
                'user_hash' => $this->UserColleague->user_remote_hash,
                'folder_name' => 'FolderCreateInCollaborationTest',
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
    }
}