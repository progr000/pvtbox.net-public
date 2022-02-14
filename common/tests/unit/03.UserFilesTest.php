<?php
namespace common\tests\unit;

use common\models\Users;
use common\models\UserNode;
use common\models\UserFiles;
use common\models\UserFileEvents;
use common\models\Licenses;

/**
 * Class UserFilesTest
 * generate by command: clear && cept generate:test unit UserFilesTest -c common
 * than rename file UserFilesTest.php to 3.UserFilesTest.php
 * run by command: clear && cept run -c common -vvv  unit 3.UserFilesTest
 * @package common
 */
class UserFilesTest extends DefaultModel
{
    /** @var \common\models\Users */
    private $User;

    /** @var \common\models\UserNode */
    private $UserNode;

    /** @var \common\models\UserFiles */
    private $UserFile;

    protected function _before()
    {
        parent::_before();

        Users::deleteAll();
        UserNode::deleteAll();
        UserFiles::deleteAll();

        $this->User = new Users();
        $this->User->user_email = $this->test_emails_pull[1];
        $this->User->user_name = "Test User Name";
        $this->User->license_type = Licenses::TYPE_FREE_TRIAL;
        $this->User->setPassword(uniqid(), false);
        $this->User->generateAuthKey();
        //$this->User->user_last_ip  = Yii::$app->request->getUserIP();
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
    }

    protected function _after()
    {
        parent::_after();

        Users::deleteAll();
        UserNode::deleteAll();
        UserFiles::deleteAll();
    }

    protected function saveUserFileIntoDbForTest()
    {
        $this->UserFile->file_uuid = md5(uniqid('', true) . microtime());
        $this->UserFile->user_id = $this->User->user_id;
        $this->UserFile->file_name = "TestFileName.txt";
        $this->UserFile->file_parent_id = 0;
        $this->UserFile->is_deleted = UserFiles::FILE_UNDELETED;

        expect('UserFile was saved in DB', $this->UserFile->save())->true();
    }

    public function testValidateEmptyValues()
    {
        expect('Model is invalid', $this->UserFile->validate())->false();
        expect('file_uuid has error (required)', $this->UserFile->getErrors())->hasKey('file_uuid');
        expect('user_id has error (required)', $this->UserFile->getErrors())->hasKey('user_id');
        expect('file_name has error (required)', $this->UserFile->getErrors())->hasKey('file_name');
        expect('file_parent_id has error (required)', $this->UserFile->getErrors())->hasKey('file_parent_id');
        expect('is_deleted has error (required)', $this->UserFile->getErrors())->hasKey('is_deleted');
    }

    public function testValidateWrongValues()
    {
        $this->UserFile->file_uuid = uniqid(); // less than 32 characters
        $this->UserFile->user_id = "test"; // not integer
        $this->UserFile->file_name = " "; // empty name
        $this->UserFile->file_parent_id = "test"; // not integer
        $this->UserFile->is_deleted = "test"; // not integer
        $this->UserFile->file_md5 = "test";
        $this->UserFile->diff_file_uuid = "test";
        $this->UserFile->share_hash = "test";
        $this->UserFile->share_group_hash = "test";
        $this->UserFile->last_event_uuid = "test";
        $this->UserFile->file_name = hash('sha512', uniqid()).hash('sha512', uniqid());
        $this->UserFile->file_created = "test";
        $this->UserFile->file_updated = "test";
        $this->UserFile->share_created = "test";
        $this->UserFile->share_lifetime = "test";
        $this->UserFile->node_id = "test";
        $this->UserFile->file_parent_id = "test";
        $this->UserFile->file_size = "test";
        $this->UserFile->file_lastatime = "test";
        $this->UserFile->file_lastmtime = "test";
        $this->UserFile->collaboration_id = "test";
        $this->UserFile->share_ttl_info = "test";
        $this->UserFile->folder_children_count = "test";
        $this->UserFile->first_event_id = "test";
        $this->UserFile->last_event_id = "test";
        $this->UserFile->last_event_type = "test";
        $this->UserFile->is_folder = "test";
        $this->UserFile->is_updated = "test";
        $this->UserFile->is_outdated = "test";
        $this->UserFile->is_collaborated = "test";
        $this->UserFile->is_owner = "test";
        $this->UserFile->is_shared = "test";
        $this->UserFile->share_is_locked = "test";
        $this->UserFile->share_password = hash('sha512', uniqid());
        expect('Model is invalid', $this->UserFile->validate())->false();
        expect('file_uuid has error (less than 32 characters)', $this->UserFile->getErrors())->hasKey('file_uuid');
        expect('user_id has error (not integer)', $this->UserFile->getErrors())->hasKey('user_id');
        expect('file_name has error (empty name)', $this->UserFile->getErrors())->hasKey('file_name');
        expect('file_parent_id has error (not integer)', $this->UserFile->getErrors())->hasKey('file_parent_id');
        expect('is_deleted has error (not integer)', $this->UserFile->getErrors())->hasKey('is_deleted');
        expect('file_md5 has error', $this->UserFile->getErrors())->hasKey('file_md5');
        expect('diff_file_uuid has error', $this->UserFile->getErrors())->hasKey('diff_file_uuid');
        expect('share_hash has error', $this->UserFile->getErrors())->hasKey('share_hash');
        expect('share_group_hash has error', $this->UserFile->getErrors())->hasKey('share_group_hash');
        expect('last_event_uuid has error', $this->UserFile->getErrors())->hasKey('last_event_uuid');
        expect('file_name has error', $this->UserFile->getErrors())->hasKey('file_name');
        expect('file_created has error', $this->UserFile->getErrors())->hasKey('file_created');
        expect('file_updated has error', $this->UserFile->getErrors())->hasKey('file_updated');
        expect('share_created has error', $this->UserFile->getErrors())->hasKey('share_created');
        expect('share_lifetime has error', $this->UserFile->getErrors())->hasKey('share_lifetime');
        expect('node_id has error', $this->UserFile->getErrors())->hasKey('node_id');
        expect('file_parent_id has error', $this->UserFile->getErrors())->hasKey('file_parent_id');
        expect('file_size has error', $this->UserFile->getErrors())->hasKey('file_size');
        expect('file_lastatime has error', $this->UserFile->getErrors())->hasKey('file_lastatime');
        expect('file_lastmtime has error', $this->UserFile->getErrors())->hasKey('file_lastmtime');
        expect('collaboration_id has error', $this->UserFile->getErrors())->hasKey('is_deleted');
        expect('share_ttl_info has error', $this->UserFile->getErrors())->hasKey('share_ttl_info');
        expect('folder_children_count has error', $this->UserFile->getErrors())->hasKey('folder_children_count');
        expect('first_event_id has error', $this->UserFile->getErrors())->hasKey('first_event_id');
        expect('last_event_id has error', $this->UserFile->getErrors())->hasKey('last_event_id');
        expect('last_event_type has error', $this->UserFile->getErrors())->hasKey('last_event_type');
        expect('is_folder has error', $this->UserFile->getErrors())->hasKey('is_folder');
        expect('is_updated has error', $this->UserFile->getErrors())->hasKey('is_updated');
        expect('is_outdated has error', $this->UserFile->getErrors())->hasKey('is_outdated');
        expect('is_collaborated has error', $this->UserFile->getErrors())->hasKey('is_collaborated');
        expect('is_owner has error', $this->UserFile->getErrors())->hasKey('is_owner');
        expect('is_shared has error', $this->UserFile->getErrors())->hasKey('is_shared');
        expect('share_is_locked has error', $this->UserFile->getErrors())->hasKey('share_is_locked');
        expect('share_password has error', $this->UserFile->getErrors())->hasKey('share_password');

        $this->UserFile = new UserFiles();
        $this->UserFile->file_uuid = hash("sha512", uniqid('', true) . microtime()); // more than 32 characters
        $this->UserFile->user_id = 1; // not exists user_id (foreign key)
        $this->UserFile->node_id = 1; // not exists node_id (foreign key)
        $this->UserFile->file_name = $this->UserFile->file_uuid . $this->UserFile->file_uuid; // more than 255 characters
        $this->UserFile->file_parent_id = 0; // ok
        $this->UserFile->is_deleted = 10; // out of range
        $this->UserFile->last_event_type = 15;
        $this->UserFile->is_folder = 2;
        $this->UserFile->is_updated = 3;
        $this->UserFile->is_outdated = 4;
        $this->UserFile->is_collaborated = 5;
        $this->UserFile->is_owner = 6;
        $this->UserFile->is_shared = 7;
        $this->UserFile->share_is_locked = 8;
        expect('Model is invalid', $this->UserFile->validate())->false();
        expect('file_uuid has error (more than 32 characters)', $this->UserFile->getErrors())->hasKey('file_uuid');
        expect('user_id has error (not exists user_id {foreign key})', $this->UserFile->getErrors())->hasKey('user_id');
        expect('node_id has error (not exists user_id {foreign key})', $this->UserFile->getErrors())->hasKey('node_id');
        expect('file_name has error (more than 255 characters)', $this->UserFile->getErrors())->hasKey('file_name');
        expect('is_deleted has error (out of range)', $this->UserFile->getErrors())->hasKey('is_deleted');
        expect('last_event_type has error', $this->UserFile->getErrors())->hasKey('last_event_type');
        expect('is_folder has error', $this->UserFile->getErrors())->hasKey('is_folder');
        expect('is_updated has error', $this->UserFile->getErrors())->hasKey('is_updated');
        expect('is_outdated has error', $this->UserFile->getErrors())->hasKey('is_outdated');
        expect('is_collaborated has error', $this->UserFile->getErrors())->hasKey('is_collaborated');
        expect('is_owner has error', $this->UserFile->getErrors())->hasKey('is_owner');
        expect('is_shared has error', $this->UserFile->getErrors())->hasKey('is_shared');
        expect('share_is_locked has error', $this->UserFile->getErrors())->hasKey('share_is_locked');
    }

    public function testValidateCorrectValues()
    {
        $this->UserFile = new UserFiles();
        $this->UserFile->file_uuid = md5(uniqid('', true) . microtime());
        $this->UserFile->user_id = $this->User->user_id;
        $this->UserFile->file_name = "TestFileName.txt";
        $this->UserFile->file_parent_id = 0;
        $this->UserFile->is_deleted = UserFiles::FILE_UNDELETED;
        $this->UserFile->last_event_type = UserFileEvents::TYPE_CREATE;
        $this->UserFile->is_folder = UserFiles::TYPE_FOLDER;
        $this->UserFile->is_updated = UserFiles::FILE_UPDATED;
        $this->UserFile->is_outdated = UserFiles::FILE_OUTDATED;
        $this->UserFile->is_collaborated = UserFiles::FILE_COLLABORATED;
        $this->UserFile->is_owner = UserFiles::IS_OWNER;
        $this->UserFile->is_shared = UserFiles::FILE_SHARED;
        $this->UserFile->share_is_locked = UserFiles::SHARE_LOCKED;
        expect('Model is valid', $this->UserFile->validate())->true();
        expect('No errors', sizeof($this->UserFile->getErrors()))->isEmpty();

        $this->UserFile = new UserFiles();
        $this->UserFile->file_uuid = md5(uniqid('', true) . microtime());
        $this->UserFile->user_id = $this->User->user_id;
        $this->UserFile->file_name = "TestFileName.txt";
        $this->UserFile->file_parent_id = 0;
        $this->UserFile->is_deleted = UserFiles::FILE_DELETED;
        $this->UserFile->last_event_type = UserFileEvents::TYPE_UPDATE;
        $this->UserFile->is_folder = UserFiles::TYPE_FILE;
        $this->UserFile->is_updated = UserFiles::FILE_UNUPDATED;
        $this->UserFile->is_outdated = UserFiles::FILE_UNOUTDATED;
        $this->UserFile->is_collaborated = UserFiles::FILE_UNCOLLABORATED;
        $this->UserFile->is_owner = UserFiles::IS_COLLEAGUE;
        $this->UserFile->is_shared = UserFiles::FILE_UNSHARED;
        $this->UserFile->share_is_locked = UserFiles::SHARE_UNLOCKED;
        expect('Model is valid', $this->UserFile->validate())->true();
        expect('No errors', sizeof($this->UserFile->getErrors()))->isEmpty();
    }

    public function testSaveIntoDatabase()
    {
        $this->saveUserFileIntoDbForTest();
    }

    public function testValidateExistedValues()
    {
        /* first model */
        $this->saveUserFileIntoDbForTest();


        /* duplicate model */
        $UserFile = new UserFiles();
        $UserFile->file_uuid = $this->UserFile->file_uuid;
        $UserFile->user_id = $this->User->user_id;
        $UserFile->file_name = $this->UserFile->file_name;
        $UserFile->file_parent_id = 0;
        $UserFile->is_deleted = UserFiles::FILE_UNDELETED;

        //var_dump($UserFile->validate());
        //var_dump($UserFile->getErrors());exit;
        expect('Model is invalid', $UserFile->validate())->false();
        expect('UserNode was not saved in DB', $UserFile->save())->false();
    }

    public function testValidateCheckSystemReservedFilename()
    {
        $this->UserFile->file_uuid = md5(uniqid('', true) . microtime());
        $this->UserFile->user_id = $this->User->user_id;
        $this->UserFile->file_parent_id = 0;
        $this->UserFile->is_deleted = UserFiles::FILE_UNDELETED;

        $this->UserFile->file_name = "TestFil>eName.txt";
        expect('Model is invalid', $this->UserFile->validate())->false();
        $errors = $this->UserFile->getErrors();
        expect('file_name has error', $errors)->hasKey('file_name');
        expect('Error text equal', $errors['file_name'])->contains(['Illegal characters in the file name']);

        $this->UserFile->file_name = "aux";
        expect('Model is invalid', $this->UserFile->validate())->false();
        $errors = $this->UserFile->getErrors();
        expect('file_name has error', $errors)->hasKey('file_name');
        expect('Error text equal', $errors['file_name'])->contains(['Not allowed reserved filename']);

        $this->UserFile->file_name = "test.prn";
        expect('Model is invalid', $this->UserFile->validate())->false();
        $errors = $this->UserFile->getErrors();
        expect('file_name has error', $errors)->hasKey('file_name');
        expect('Error text equal', $errors['file_name'])->contains(['Not allowed reserved extension']);

        $this->UserFile->file_name = "test.";
        expect('Model is invalid', $this->UserFile->validate())->false();
        $errors = $this->UserFile->getErrors();
        expect('file_name has error', $errors)->hasKey('file_name');
        expect('Error text equal', $errors['file_name'])->contains(['Not allowed dot at end of name']);
    }

    public function testGetUser()
    {
        $this->saveUserFileIntoDbForTest();

        $User = $this->UserFile->getUser();
        expect('UserNode is NOT NULL', $User)->notNull();
        expect('UserNode is instance of class ActiveQuery', $User)->isInstanceOf(\yii\db\ActiveQuery::className());

        $TestUser = $User->one();
        expect('TestUser is NOT NULL', $TestUser)->notNull();
        expect('TestUser is instance of class Users', $TestUser)->isInstanceOf(Users::className());
        expect('TestUser->user_id == this->UserFile->user_id', $TestUser->user_id == $this->UserFile->user_id)->true();
    }

    public function testGenerate_share_hash()
    {
        $this->saveUserFileIntoDbForTest();
        $share_hash = $this->UserFile->generate_share_hash();
        expect('$share_hash is NOT EMPTY', $share_hash)->notEmpty();
        expect('$share_hash is string', $share_hash)->internalType('string');
        expect('$share_hash is string length 32', mb_strlen($share_hash) == 32)->true();
    }

    public function testExt_to_mime()
    {
        $ret = UserFiles::ext_to_mime();
        expect('$ret is not empty array', $ret)->notEmpty();
        expect('$ret is not empty array', $ret)->internalType('array');
        expect('$ret is not empty array', $ret)->contains('text/plain');
    }

    public function testFileMime()
    {
        $ret = UserFiles::fileMime('image.jpg');
        expect('$ret is NOT EMPTY', $ret)->notEmpty();
        expect('$ret is string', $ret)->internalType('string');
        expect('$ret contains image/jpeg', $ret)->contains('image/jpeg');

        $ret = UserFiles::fileMime('image.unknown');
        expect('$ret is NOT EMPTY', $ret)->notEmpty();
        expect('$ret is string', $ret)->internalType('string');
        expect('$ret contains image/jpeg', $ret)->contains('application/octet-stream');
    }

    public function testGetFullPath()
    {
        $this->saveUserFileIntoDbForTest();

        $ret = UserFiles::getFullPath($this->UserFile);
        expect('is string', $ret)->internalType('string');
        expect('ret = this->UserFile->file_name ', $ret)->equals($this->UserFile->file_name);
    }
}