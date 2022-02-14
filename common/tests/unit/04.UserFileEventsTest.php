<?php
namespace common\tests\unit;

use common\models\Users;
use common\models\UserNode;
use common\models\UserFiles;
use common\models\UserFileEvents;
use common\models\Licenses;

/**
 * Class UserFileEventsTest
 * generate by command: clear && cept generate:test unit UserFileEventsTest -c common
 * than rename file UserFileEventsTest.php to 4.UserFileEventsTest.php
 * run by command: clear && cept run -c common -vvv  unit 3.UserFileEventsTest
 * @package common
 */
class UserFileEventsTest extends DefaultModel
{
    /** @var \common\models\Users */
    private $User;

    /** @var \common\models\UserNode */
    private $UserNode;

    /** @var \common\models\UserFiles */
    private $UserFile;

    /** @var \common\models\UserFileEvents */
    private $UserFileEvent;

    protected function _before()
    {
        parent::_before();

        Users::deleteAll();
        UserNode::deleteAll();
        UserFiles::deleteAll();
        UserFileEvents::deleteAll();

        $this->User = new Users();
        $this->User->user_email = $this->test_emails_pull[1];
        $this->User->user_name = "Test User Name";
        $this->User->license_type = Licenses::TYPE_FREE_TRIAL;
        $this->User->setPassword(uniqid(), false);
        $this->User->generateAuthKey();
        $this->User->save();

        $this->UserNode = new UserNode();
        $node_hash = hash("sha512", uniqid('', true) . microtime());
        $this->UserNode->node_hash = $node_hash;
        $this->UserNode->user_id = $this->User->user_id;
        $this->UserNode->node_name = "TestNode";
        $this->UserNode->node_ostype = UserNode::OSTYPE_LINUX;
        $this->UserNode->node_devicetype = UserNode::DEVICE_DESKTOP;
        $this->UserNode->save();

        $this->UserFile = new UserFiles();
        $this->UserFile->file_uuid = md5(uniqid('', true) . microtime());
        $this->UserFile->user_id = $this->User->user_id;
        $this->UserFile->file_name = "TestFileName.txt";
        $this->UserFile->file_parent_id = 0;
        $this->UserFile->is_deleted = UserFiles::FILE_UNDELETED;
        $this->UserFile->save();

        $this->UserFileEvent = new UserFileEvents();
    }

    protected function _after()
    {
        parent::_after();

        Users::deleteAll();
        UserNode::deleteAll();
        UserFiles::deleteAll();
        UserFileEvents::deleteAll();
    }

    protected function saveUserFileEventIntoDbForTest()
    {
        $this->UserFileEvent->event_uuid = md5(uniqid('', true) . microtime());
        $this->UserFileEvent->event_type = UserFileEvents::TYPE_CREATE;
        $this->UserFileEvent->user_id = $this->User->user_id;
        $this->UserFileEvent->file_id = $this->UserFile->file_id;
        $this->UserFileEvent->last_event_id = 0;
        $this->UserFileEvent->event_timestamp = time();

        expect('UserFile was saved in DB', $this->UserFileEvent->save())->true();
    }

    // tests
    public function testValidateEmptyValues()
    {
        expect('Model is invalid', $this->UserFileEvent->validate())->false();
        expect('event_uuid has error (required)', $this->UserFileEvent->getErrors())->hasKey('event_uuid');
        expect('event_type has error (required)', $this->UserFileEvent->getErrors())->hasKey('event_type');
        expect('user_id has error (required)', $this->UserFileEvent->getErrors())->hasKey('user_id');
        expect('file_id has error (required)', $this->UserFileEvent->getErrors())->hasKey('file_id');
        expect('last_event_id has error (required)', $this->UserFileEvent->getErrors())->hasKey('last_event_id');
        expect('event_timestamp has error (required)', $this->UserFileEvent->getErrors())->hasKey('event_timestamp');
    }

    public function testValidateWrongValues()
    {
        $this->UserFileEvent->event_uuid = "test"; // less than 32 characters
        $this->UserFileEvent->event_type = "test"; // not integer
        $this->UserFileEvent->user_id = "test"; // not integer
        $this->UserFileEvent->node_id = "test"; // not integer
        $this->UserFileEvent->file_id = "test"; // not integer
        $this->UserFileEvent->last_event_id = "test"; // not integer
        $this->UserFileEvent->event_timestamp = "test"; // not integer
        $this->UserFileEvent->diff_file_uuid = "test";
        $this->UserFileEvent->rev_diff_file_uuid = "test";
        $this->UserFileEvent->file_hash = "test";
        $this->UserFileEvent->file_hash_before_event = "test";
        $this->UserFileEvent->file_name_before_event = hash('sha512', uniqid()).hash('sha512', uniqid());
        $this->UserFileEvent->file_name_after_event = hash('sha512', uniqid()).hash('sha512', uniqid());
        $this->UserFileEvent->diff_file_size = "test";
        $this->UserFileEvent->rev_diff_file_size = "test";
        $this->UserFileEvent->file_size_before_event = "test";
        $this->UserFileEvent->file_size_after_event = "test";
        $this->UserFileEvent->parent_before_event = "test";
        $this->UserFileEvent->parent_after_event = "test";
        $this->UserFileEvent->event_creator_user_id = "test";
        $this->UserFileEvent->event_creator_node_id = "test";
        $this->UserFileEvent->event_group_timestamp = "test";
        $this->UserFileEvent->event_group_id = "test";
        expect('Model is invalid', $this->UserFileEvent->validate())->false();
        expect('event_uuid has error (less than 32 characters)', $this->UserFileEvent->getErrors())->hasKey('event_uuid');
        expect('event_type has error (not integer)', $this->UserFileEvent->getErrors())->hasKey('event_type');
        expect('user_id has error (not integer)', $this->UserFileEvent->getErrors())->hasKey('user_id');
        expect('file_id has error (not integer)', $this->UserFileEvent->getErrors())->hasKey('file_id');
        expect('last_event_id has error (not integer)', $this->UserFileEvent->getErrors())->hasKey('last_event_id');
        expect('event_timestamp has error (not integer)', $this->UserFileEvent->getErrors())->hasKey('event_timestamp');
        expect('diff_file_uuid has error', $this->UserFileEvent->getErrors())->hasKey('diff_file_uuid');
        expect('rev_diff_file_uuid has error', $this->UserFileEvent->getErrors())->hasKey('rev_diff_file_uuid');
        expect('file_hash has error', $this->UserFileEvent->getErrors())->hasKey('file_hash');
        expect('file_hash_before_event has error', $this->UserFileEvent->getErrors())->hasKey('file_hash_before_event');
        expect('file_name_before_event has error', $this->UserFileEvent->getErrors())->hasKey('file_name_before_event');
        expect('file_name_after_event has error', $this->UserFileEvent->getErrors())->hasKey('file_name_after_event');
        expect('diff_file_size has error', $this->UserFileEvent->getErrors())->hasKey('diff_file_size');
        expect('rev_diff_file_size has error', $this->UserFileEvent->getErrors())->hasKey('rev_diff_file_size');
        expect('file_size_before_event has error', $this->UserFileEvent->getErrors())->hasKey('file_size_before_event');
        expect('file_size_after_event has error', $this->UserFileEvent->getErrors())->hasKey('file_size_after_event');
        expect('parent_before_event has error', $this->UserFileEvent->getErrors())->hasKey('parent_before_event');
        expect('parent_after_event has error', $this->UserFileEvent->getErrors())->hasKey('parent_after_event');
        expect('event_creator_user_id has error', $this->UserFileEvent->getErrors())->hasKey('event_creator_user_id');
        expect('event_creator_node_id has error', $this->UserFileEvent->getErrors())->hasKey('event_creator_node_id');
        expect('event_group_timestamp has error', $this->UserFileEvent->getErrors())->hasKey('event_group_timestamp');
        expect('event_group_id has error', $this->UserFileEvent->getErrors())->hasKey('event_group_id');

        $this->UserFileEvent = new UserFileEvents();
        $this->UserFileEvent->event_uuid = hash("sha512", uniqid('', true) . microtime()); // more than 32 characters
        $this->UserFileEvent->event_type = 10; // out of range
        $this->UserFileEvent->file_id = 1; // not exists file_id (foreign key)
        $this->UserFileEvent->node_id = 1; // not exists node_id (foreign key)
        $this->UserFileEvent->user_id = 1; // not exists user_id (foreign key)
        $this->UserFileEvent->event_creator_user_id = 1; // not exists user_id (foreign key)
        $this->UserFileEvent->event_creator_node_id = 1; // not exists node_id (foreign key)
        $this->UserFileEvent->last_event_id = 0; // ok
        $this->UserFileEvent->event_timestamp = time(); // ok
        expect('Model is invalid', $this->UserFileEvent->validate())->false();
        expect('event_uuid has error (more than 32 characters)', $this->UserFileEvent->getErrors())->hasKey('event_uuid');
        expect('event_type has error (out of range)', $this->UserFileEvent->getErrors())->hasKey('event_type');
        expect('file_id has error (not exists file_id {foreign key})', $this->UserFileEvent->getErrors())->hasKey('file_id');
        expect('user_id has not error (not exists user_id {foreign key})', $this->UserFileEvent->getErrors())->hasKey('user_id');
        expect('event_creator_user_id has error', $this->UserFileEvent->getErrors())->hasKey('event_creator_user_id');
        expect('event_creator_node_id has error', $this->UserFileEvent->getErrors())->hasKey('event_creator_node_id');
        expect('last_event_id has not error (OK)', $this->UserFileEvent->getErrors())->hasntKey('last_event_id');
        expect('event_timestamp has not error (OK)', $this->UserFileEvent->getErrors())->hasntKey('event_timestamp');
    }

    public function testValidateCorrectValues()
    {
        $this->UserFileEvent->event_uuid = md5(uniqid('', true) . microtime());
        $this->UserFileEvent->event_type = UserFileEvents::TYPE_CREATE;
        $this->UserFileEvent->file_id = $this->UserFile->file_id;
        $this->UserFileEvent->node_id = $this->UserNode->node_id;
        $this->UserFileEvent->user_id = $this->User->user_id;
        $this->UserFileEvent->event_creator_user_id = $this->User->user_id;
        $this->UserFileEvent->event_creator_node_id = $this->UserNode->node_id;
        $this->UserFileEvent->last_event_id = 0;
        $this->UserFileEvent->event_timestamp = time();
        $this->UserFileEvent->event_invisible = UserFileEvents::EVENT_VISIBLE;
        $this->UserFileEvent->erase_nested = UserFileEvents::ERASE_NESTED_FALSE;
        $this->UserFileEvent->is_rollback = UserFileEvents::NOT_ROLLBACK;
        $this->UserFileEvent->file_name_before_event = "";
        $this->UserFileEvent->file_name_after_event = $this->UserFile->file_name;
        expect('Model is valid', $this->UserFileEvent->validate())->true();
        expect('No errors', sizeof($this->UserFileEvent->getErrors()))->isEmpty();
    }

    public function testSaveIntoDatabase()
    {
        $this->saveUserFileEventIntoDbForTest();
    }

    public function testValidateExistedValues()
    {
        /* first model */
        $this->saveUserFileEventIntoDbForTest();


        /* duplicate model */
        $UserFileEvent = new UserFileEvents();
        $UserFileEvent->event_uuid = $this->UserFileEvent->event_uuid; // unique key [event_uuid + user_id]
        $UserFileEvent->user_id    = $this->UserFileEvent->user_id;    //
        $UserFileEvent->file_id = $this->UserFile->file_id;            // unique key [file_id + last_event_id]
        $UserFileEvent->last_event_id = 0;                             //
        $UserFileEvent->event_type = UserFileEvents::TYPE_UPDATE;
        $UserFileEvent->event_timestamp = time();

        //var_dump($UserFileEvent->validate());
        //var_dump($UserFileEvent->getErrors());exit;
        expect('Model is invalid', $UserFileEvent->validate())->false();
        expect('UserNode was not saved in DB', $UserFileEvent->save())->false();
    }

    public function testGetFile()
    {
        $this->saveUserFileEventIntoDbForTest();

        $UserFile = $this->UserFileEvent->getFile();
        expect('UserFile is NOT NULL', $UserFile)->notNull();
        expect('UserFile is instance of class ActiveQuery', $UserFile)->isInstanceOf(\yii\db\ActiveQuery::className());

        $TestFile = $UserFile->one();
        expect('TestFile is NOT NULL', $TestFile)->notNull();
        expect('TestFile is instance of class UserFiles', $TestFile)->isInstanceOf(UserFiles::className());
        expect('TestFile->file_id == this->UserFileEvent->file_id', $TestFile->file_id == $this->UserFileEvent->file_id)->true();
    }
}