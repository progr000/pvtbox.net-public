<?php
namespace frontend\tests\unit\api\_02_fileEvents;

use yii\helpers\Json;
use \Mockery;
use Codeception\TestCase\Test;
use common\helpers\FileSys;
use common\models\Mailq;
use common\models\QueuedEvents;
use common\models\RemoteActions;
use common\models\UserCollaborations;
use common\models\UserColleagues;
use common\models\UserFileEvents;
use common\models\UserFiles;
use common\models\Users;
use common\models\UserNode;
use common\models\Licenses;
use frontend\models\CollaborationApi;
use frontend\models\forms\ShareElementForm;
use frontend\modules\api\controllers\FileEventsController;

/**
 * Class ApiFileEvents
 * @package frontend\tests\unit\api\_02_fileEvents
 * @property \yii\mutex\FileMutex $mutex
 * @property \Mockery $redisMock
 */
class ApiFileEvents extends Test
{
    /** @var  $controller \frontend\modules\api\controllers\FileEventsController */
    protected $controller;

    /** @var \frontend\UnitTester */
    protected $tester;

    /** @var \common\models\Users */
    protected $UserOwner,
              $UserColleague;

    /** @var \common\models\UserNode */
    protected $UserNodeOwner1,
              $UserNodeOwner2,
              $UserNodeColleague1,
              $UserNodeColleague2;

    /** @var \yii\mutex\FileMutex $mutex */
    protected $mutex;

    /** @var string */
    protected $mutex_name_owner,
              $mutex_name_colleague;

    /** @var array */
    protected $test_emails_pull = [
        'user0@noexist.domain',
        'user-owner@noexist.domain',
        'user-colleague@noexist.domain',
        'user3@noexist.domain',
        'user4@noexist.domain',
        'user5@noexist.domain',
        'user6@noexist.domain',
        'user7@noexist.domain',
        'user8@noexist.domain',
        'user9@noexist.domain',
    ];

    /** @var  array */
    protected $UserFolderDataRoot;
    protected $UserFolderDataInRoot;


    protected $redisMock;

    /**
     *
     */
    protected function _before()
    {
        \Yii::$app->language = 'en';

        Mailq::deleteAll();
        QueuedEvents::deleteAll();
        Users::deleteAll();
        UserNode::deleteAll();
        UserFiles::deleteAll();
        UserFileEvents::deleteAll();
        UserCollaborations::deleteAll();
        UserColleagues::deleteAll();
        RemoteActions::deleteAll();
        //var_dump(\Yii::$app->params['nodeVirtualFS']);exit;
        FileSys::rmdir(\Yii::$app->params['nodeVirtualFS'], true);

        /** mutex */
        $this->mutex = \Yii::$app->mutex;

        $module = new \yii\base\Module('test');
        $this->controller = new FileEventsController('test', $module);

        $this->redisMock = Mockery::mock('\yii\redis\Connection');
        \Yii::$app->set('redis', $this->redisMock);
        //$this->redisMock->shouldReceive('')->wi
    }

    /**
     *
     */
    protected function _after()
    {
        Mailq::deleteAll();
        QueuedEvents::deleteAll();
        Users::deleteAll();
        UserNode::deleteAll();
        UserFiles::deleteAll();
        UserFileEvents::deleteAll();
        RemoteActions::deleteAll();
        UserCollaborations::deleteAll();
        UserColleagues::deleteAll();
        FileSys::rmdir(\Yii::$app->params['nodeVirtualFS'], true);

        $this->mutex->release($this->mutex_name_owner);
        $this->mutex->release($this->mutex_name_colleague);

        Mockery::close();
    }

    /**
     *
     */
    protected function createTestData()
    {
        /** UserOwner */
        $this->UserOwner = Users::findByEmail($this->test_emails_pull[1]);
        $this->UserOwner = new Users();
        $this->UserOwner->user_email = $this->test_emails_pull[1];
        $this->UserOwner->user_name = "Owner User Name";
        $this->UserOwner->license_type = Licenses::TYPE_FREE_TRIAL;
        $this->UserOwner->setPassword(hash('sha512', "qwerty"), false);
        $this->UserOwner->generateAuthKey();
        $this->UserOwner->save();

        /** UserNodeOwner1 */
        $this->UserNodeOwner1 = new UserNode();
        $this->UserNodeOwner1->node_hash = hash("sha512", uniqid('', true) . microtime());
        $this->UserNodeOwner1->user_id = $this->UserOwner->user_id;
        $this->UserNodeOwner1->node_name = "TestNodeOwner1";
        $this->UserNodeOwner1->node_osname = "Linux Ubuntu";
        $this->UserNodeOwner1->node_ostype = UserNode::OSTYPE_LINUX;
        $this->UserNodeOwner1->node_devicetype = UserNode::DEVICE_DESKTOP;
        $this->UserNodeOwner1->node_status = UserNode::STATUS_ACTIVE;
        $this->UserNodeOwner1->save();

        /** UserNodeOwner2 */
        $this->UserNodeOwner2 = new UserNode();
        $this->UserNodeOwner2->node_hash = hash("sha512", uniqid('', true) . microtime());
        $this->UserNodeOwner2->user_id = $this->UserOwner->user_id;
        $this->UserNodeOwner2->node_name = "TestNodeOwner2";
        $this->UserNodeOwner2->node_osname = "Linux Ubuntu";
        $this->UserNodeOwner2->node_ostype = UserNode::OSTYPE_LINUX;
        $this->UserNodeOwner2->node_devicetype = UserNode::DEVICE_DESKTOP;
        $this->UserNodeOwner2->node_status = UserNode::STATUS_ACTIVE;
        $this->UserNodeOwner2->save();

        /** UserColleague */
        $this->UserColleague = Users::findByEmail($this->test_emails_pull[1]);
        $this->UserColleague = new Users();
        $this->UserColleague->user_email = $this->test_emails_pull[2];
        $this->UserColleague->user_name = "Colleague User Name";
        $this->UserColleague->license_type = Licenses::TYPE_FREE_TRIAL;
        $this->UserColleague->setPassword(hash('sha512', "qwerty"), false);
        $this->UserColleague->generateAuthKey();
        $this->UserColleague->save();

        /** UserNodeOwner1 */
        $this->UserNodeColleague1 = new UserNode();
        $this->UserNodeColleague1->node_hash = hash("sha512", uniqid('', true) . microtime());
        $this->UserNodeColleague1->user_id = $this->UserColleague->user_id;
        $this->UserNodeColleague1->node_name = "TestNodeColleague1";
        $this->UserNodeColleague1->node_osname = "Linux Ubuntu";
        $this->UserNodeColleague1->node_ostype = UserNode::OSTYPE_LINUX;
        $this->UserNodeColleague1->node_devicetype = UserNode::DEVICE_DESKTOP;
        $this->UserNodeColleague1->node_status = UserNode::STATUS_ACTIVE;
        $this->UserNodeColleague1->save();

        /** UserNodeOwner2 */
        $this->UserNodeColleague2 = new UserNode();
        $this->UserNodeColleague2->node_hash = hash("sha512", uniqid('', true) . microtime());
        $this->UserNodeColleague2->user_id = $this->UserColleague->user_id;
        $this->UserNodeColleague2->node_name = "TestNodeColleague2";
        $this->UserNodeColleague2->node_osname = "Linux Ubuntu";
        $this->UserNodeColleague2->node_ostype = UserNode::OSTYPE_LINUX;
        $this->UserNodeColleague2->node_devicetype = UserNode::DEVICE_DESKTOP;
        $this->UserNodeColleague2->node_status = UserNode::STATUS_ACTIVE;
        $this->UserNodeColleague2->save();

        /** mutex */
        $this->mutex_name_owner     = 'user_id_' . $this->UserOwner->user_id;
        $this->mutex_name_colleague = 'user_id_' . $this->UserColleague->user_id;
    }

    /**
     * @param array $ret
     */
    protected function expectCreateFolderOK($ret)
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
    protected function createTestFolders()
    {
        $data = [
            'parent_folder_uuid' => "",
            'node_hash' => $this->UserNodeOwner1->node_hash,
            'user_hash' => $this->UserOwner->user_remote_hash,
            'folder_name' => 'FolderCreateTest',
        ];
        $this->UserFolderDataRoot = $this->controller->actionTests('folder_event_create', $data);
        $this->expectCreateFolderOK($this->UserFolderDataRoot);
        //var_dump($this->UserFolderDataRoot); exit;
        if (isset($this->UserFolderDataRoot['result'],
                  $this->UserFolderDataRoot['data']['folder_uuid']) && $this->UserFolderDataRoot['result'] == 'success') {

            unset($data);
            $data = [
                'parent_folder_uuid' => $this->UserFolderDataRoot['data']['folder_uuid'],
                'node_hash' => $this->UserNodeOwner1->node_hash,
                'user_hash' => $this->UserOwner->user_remote_hash,
                'folder_name' => 'FolderCreateTest2',
            ];
            $this->UserFolderDataInRoot = $this->controller->actionTests('folder_event_create', $data);
            //var_dump($this->UserFolderDataInRoot); exit;
            $this->expectCreateFolderOK($this->UserFolderDataInRoot);
        } else {
            $this->fail('failed previous method');
        }
    }

    /**
     * @param $owner_user_id
     * @param $folder_uuid
     * @param $colleague_email
     * @param $permission
     * @return array
     */
    protected function createCollaboration($owner_user_id, $folder_uuid, $colleague_email, $permission)
    {
        /* Инициализация коллаборации с юзером UserColleague (инвайт с правами view) по созданной папке */
        unset($data);
        $data['access_type']     = $permission;
        $data['file_uuid']       = $folder_uuid;
        $data['action']          = CollaborationApi::ACTION_ADD;
        $data['owner_user_id']   = $owner_user_id;
        $data['colleague_email'] = $colleague_email;
        unset($model);
        $model = new ShareElementForm(['file_uuid', 'access_type', 'action', 'owner_user_id', 'colleague_email']);
        if (!$model->load(['ShareElementForm' => $data], 'ShareElementForm') || !$model->validate()) {
            $this->fail("Failed Init Collaboration " . Json::encode($model->getErrors()));
        }
        $retInitCollaboration = $model->changeCollaboration();
        //var_dump($retInitCollaboration); exit;
        expect('is array', $retInitCollaboration)->internalType('array');
        expect('status in', $retInitCollaboration)->hasKey('status');
        expect('is true', $retInitCollaboration['status'])->true();
        expect('data in', $retInitCollaboration)->hasKey('data');
        expect('is array', $retInitCollaboration['data'])->internalType('array');
        expect('colleague_id in', $retInitCollaboration['data'])->hasKey('colleague_id');
        expect('collaboration_id in', $retInitCollaboration['data'])->hasKey('collaboration_id');
        expect('access_type in', $retInitCollaboration['data'])->hasKey('access_type');
        expect('file_uuid in', $retInitCollaboration['data'])->hasKey('file_uuid');
        expect('file_uuid equals', $retInitCollaboration['data']['file_uuid'])->equals($folder_uuid);

        /* Акцептим инвайт (Джойним) юзера в коллаборацию */
        unset($data);
        $data['colleague_message'] = '';
        $data['action'] = CollaborationApi::ACTION_EDIT;
        $data['access_type'] = $retInitCollaboration['data']['access_type'];
        $data['colleague_id'] = $retInitCollaboration['data']['colleague_id'];
        $data['collaboration_id'] = $retInitCollaboration['data']['collaboration_id'];
        $data['owner_user_id'] = $owner_user_id;
        $data['uuid'] = $retInitCollaboration['data']['file_uuid'];
        unset($model);
        $model = new CollaborationApi(['action', 'access_type', 'colleague_id', 'collaboration_id', /*'owner_user_id', 'uuid'*/]);
        if (!$model->load(['CollaborationApi' => $data]) || !$model->validate()) {
            $this->fail("Failed Accept Collaboration " . Json::encode($model->getErrors()));
        }
        $retAcceptCollaboration = $model->colleagueJoin();
        //var_dump($retAcceptCollaboration); exit;
        expect('is array', $retAcceptCollaboration)->internalType('array');
        expect('status in', $retAcceptCollaboration)->hasKey('status');
        expect('is true', $retAcceptCollaboration['status'])->true();
        expect('data in', $retAcceptCollaboration)->hasKey('data');
        expect('is array', $retAcceptCollaboration['data'])->internalType('array');
        expect('colleague_id in', $retAcceptCollaboration['data'])->hasKey('colleague_id');
        expect('collaboration_id in', $retAcceptCollaboration['data'])->hasKey('collaboration_id');
        expect('access_type in', $retAcceptCollaboration['data'])->hasKey('access_type');
        expect('access_type equals', $retAcceptCollaboration['data']['access_type'])->equals($permission);
        expect('file_uuid in', $retAcceptCollaboration['data'])->hasKey('file_uuid');
        expect('file_uuid equals', $retAcceptCollaboration['data']['file_uuid'])->equals($folder_uuid);
        expect('status in', $retAcceptCollaboration['data'])->hasKey('status');
        expect('status equals', $retAcceptCollaboration['data']['status'])->equals(UserColleagues::statusLabel(UserColleagues::STATUS_QUEUED_ADD));

        return $retAcceptCollaboration;
    }

    /**
     * @param $owner_user_id
     * @param $colleague_id
     * @param $folder_uuid
     * @param $permission
     * @return array
     */
    protected function changeColleagueAccessToCollaboration($owner_user_id, $colleague_id, $folder_uuid, $permission)
    {
        unset($data);
        $data['access_type']     = $permission;
        $data['file_uuid']       = $folder_uuid;
        $data['action']          = CollaborationApi::ACTION_EDIT;
        $data['owner_user_id']   = $owner_user_id;
        $data['colleague_id']    = $colleague_id;
        unset($model);
        $model = new ShareElementForm(['file_uuid', 'access_type', 'action', 'owner_user_id', 'colleague_id']);
        if (!$model->load(['ShareElementForm' => $data], 'ShareElementForm') || !$model->validate()) {
            $this->fail("Failed Change Access for Colleague in Collaboration " . Json::encode($model->getErrors()));
        }
        $retChangeAccessInCollaboration = $model->changeCollaboration();
        //var_dump($retChangeAccessInCollaboration); exit;
        expect('is array', $retChangeAccessInCollaboration)->internalType('array');
        expect('status in', $retChangeAccessInCollaboration)->hasKey('status');
        expect('is true', $retChangeAccessInCollaboration['status'])->true();
        expect('data in', $retChangeAccessInCollaboration)->hasKey('data');
        expect('is array', $retChangeAccessInCollaboration['data'])->internalType('array');
        expect('colleague_id in', $retChangeAccessInCollaboration['data'])->hasKey('colleague_id');
        expect('collaboration_id in', $retChangeAccessInCollaboration['data'])->hasKey('collaboration_id');
        expect('access_type in', $retChangeAccessInCollaboration['data'])->hasKey('access_type');
        expect('access_type equals', $retChangeAccessInCollaboration['data']['access_type'])->equals($permission);
        expect('file_uuid in', $retChangeAccessInCollaboration['data'])->hasKey('file_uuid');
        expect('file_uuid equals', $retChangeAccessInCollaboration['data']['file_uuid'])->equals($folder_uuid);
        expect('status in', $retChangeAccessInCollaboration['data'])->hasKey('status');
        expect('status equals', $retChangeAccessInCollaboration['data']['status'])->equals(UserColleagues::statusLabel(UserColleagues::STATUS_JOINED));

        return $retChangeAccessInCollaboration;
    }

    /**
     * @param $file_uuid
     * @param $user_id
     * @return int
     */
    protected function getLastEventId($file_uuid, $user_id)
    {
        /** @var \common\models\UserFiles $UserFile */
        $UserFile = UserFiles::find()
            ->andWhere([
                'file_uuid' => $file_uuid,
                'user_id'   => $user_id,
            ])
            ->one();
        expect('is object', $UserFile)->isInstanceOf(UserFiles::class);
        return $UserFile->last_event_id;
    }
}