<?php
namespace common\tests\unit;

use common\models\Users;
use common\models\UserFiles;
use common\models\UserCollaborations;
use common\models\Licenses;

/**
 * Class UserCollaborationsTest
 * generate by command: clear && cept generate:test unit UserCollaborationsTest -c common
 * than rename file UserCollaborationsTest.php to 5.UserCollaborationsTest.php
 * run by command: clear && cept run -c common -vvv  unit 5.UserCollaborationsTest
 * @package common
 */
class UserCollaborationsTest extends DefaultModel
{
    /** @var \common\models\Users */
    private $User;

    /** @var \common\models\UserFiles */
    private $UserFile;

    /** @var \common\models\UserCollaborations */
    private $UserCollaboration;

    protected function _before()
    {
        parent::_before();

        Users::deleteAll();
        UserFiles::deleteAll();
        UserCollaborations::deleteAll();

        $this->User = new Users();
        $this->User->user_email = $this->test_emails_pull[1];
        $this->User->user_name = "Test User Name";
        $this->User->license_type = Licenses::TYPE_FREE_TRIAL;
        $this->User->setPassword(uniqid(), false);
        $this->User->generateAuthKey();
        $this->User->save();

        $this->UserFile = new UserFiles();
        $this->UserFile->file_uuid = md5(uniqid('', true) . microtime());
        $this->UserFile->user_id = $this->User->user_id;
        $this->UserFile->file_name = "TestFileName.txt";
        $this->UserFile->file_parent_id = 0;
        $this->UserFile->is_deleted = UserFiles::FILE_UNDELETED;
        $this->UserFile->is_folder = UserFiles::TYPE_FOLDER;
        $this->UserFile->save();

        $this->UserCollaboration = new UserCollaborations();
    }

    protected function _after()
    {
        parent::_after();

        Users::deleteAll();
        UserFiles::deleteAll();
        UserCollaborations::deleteAll();
    }

    protected function saveUserCollaborationIntoDbForTest()
    {
        $this->UserCollaboration->file_uuid = $this->UserFile->file_uuid;
        $this->UserCollaboration->user_id = $this->User->user_id;
        $this->UserCollaboration->collaboration_status = UserCollaborations::STATUS_ACTIVE;

        expect('UserFile was saved in DB', $this->UserCollaboration->save())->true();
    }

    // tests
    public function testValidateEmptyValues()
    {
        expect('Model is invalid', $this->UserCollaboration->validate())->false();
        expect('user_id has error (required)', $this->UserCollaboration->getErrors())->hasKey('user_id');
        expect('file_uuid has not error (OK)', $this->UserCollaboration->getErrors())->hasntKey('file_uuid');
        expect('collaboration_status has not error (required)', $this->UserCollaboration->getErrors())->hasntKey('collaboration_status');
    }

    public function testValidateWrongValues()
    {
        $this->UserCollaboration->file_uuid = "test"; // not integer
        $this->UserCollaboration->user_id = "test"; // not integer
        $this->UserCollaboration->collaboration_status = "test"; // not integer
        $this->UserCollaboration->collaboration_created = "test";
        expect('Model is invalid', $this->UserCollaboration->validate())->false();
        expect('file_uuid has error (less than 32 characters)', $this->UserCollaboration->getErrors())->hasKey('file_uuid');
        expect('user_id has error (not integer)', $this->UserCollaboration->getErrors())->hasKey('user_id');
        expect('collaboration_status has error (not integer)', $this->UserCollaboration->getErrors())->hasKey('collaboration_status');
        expect('collaboration_created has error', $this->UserCollaboration->getErrors())->hasKey('collaboration_created');

        $this->UserCollaboration = new UserCollaborations();
        $this->UserCollaboration->file_uuid = hash("sha512", uniqid('', true) . microtime()); // more than 32 characters
        $this->UserCollaboration->user_id = 1; // not exists user_id (foreign key)
        $this->UserCollaboration->collaboration_status = 10; // out of range
        expect('Model is invalid', $this->UserCollaboration->validate())->false();
        expect('file_uuid has error (more than 32 characters)', $this->UserCollaboration->getErrors())->hasKey('file_uuid');
        expect('user_id has error (not exists user_id {foreign key})', $this->UserCollaboration->getErrors())->hasKey('user_id');
        expect('collaboration_status has error (out of range)', $this->UserCollaboration->getErrors())->hasKey('collaboration_status');
    }

    public function testValidateCorrectValues()
    {
        $this->UserCollaboration->file_uuid = $this->UserFile->file_uuid;
        $this->UserCollaboration->user_id = $this->User->user_id;
        $this->UserCollaboration->collaboration_status = UserCollaborations::STATUS_ACTIVE;
        $this->UserCollaboration->collaboration_created = date(SQL_DATE_FORMAT);
        expect('Model is valid', $this->UserCollaboration->validate())->true();
        expect('No errors', sizeof($this->UserCollaboration->getErrors()))->isEmpty();

        $this->UserCollaboration = new UserCollaborations();
        $this->UserCollaboration->file_uuid = null;
        $this->UserCollaboration->user_id = $this->User->user_id;
        $this->UserCollaboration->collaboration_status = UserCollaborations::STATUS_DEACTIVATED;
        $this->UserCollaboration->collaboration_created = date('Y.m.d H:i');
        expect('Model is valid', $this->UserCollaboration->validate())->true();
        expect('No errors', sizeof($this->UserCollaboration->getErrors()))->isEmpty();
    }

    public function testSaveIntoDatabase()
    {
        $this->saveUserCollaborationIntoDbForTest();
    }

    public function testValidateExistedValues()
    {
        /* first model */
        $this->saveUserCollaborationIntoDbForTest();


        /* duplicate model */
        $UserCollaboration = new UserCollaborations();
        $UserCollaboration->file_uuid = $this->UserFile->file_uuid;
        $UserCollaboration->user_id = $this->User->user_id;
        $UserCollaboration->collaboration_status = UserCollaborations::STATUS_ACTIVE;

        //var_dump($UserCollaboration->validate());
        //var_dump($UserCollaboration->getErrors());exit;
        expect('Model is invalid', $UserCollaboration->validate())->false();
        expect('UserCollaboration was not saved in DB', $UserCollaboration->save())->false();
    }

    public function testGetFile()
    {
        $this->saveUserCollaborationIntoDbForTest();

        $UserFile = $this->UserCollaboration->getFile();
        expect('UserFile is NOT NULL', $UserFile)->notNull();
        expect('UserFile is instance of class ActiveQuery', $UserFile)->isInstanceOf(\yii\db\ActiveQuery::className());

        $TestFile = $UserFile->one();
        expect('TestFile is NOT NULL', $TestFile)->notNull();
        expect('TestFile is instance of class UserFiles', $TestFile)->isInstanceOf(UserFiles::className());
        expect('TestFile->file_uuid == this->UserCollaboration->file_uuid', $TestFile->file_uuid == $this->UserCollaboration->file_uuid)->true();
    }

    public function testGetUser()
    {
        $this->saveUserCollaborationIntoDbForTest();

        $User = $this->UserCollaboration->getUser();
        expect('User is NOT NULL', $User)->notNull();
        expect('User is instance of class ActiveQuery', $User)->isInstanceOf(\yii\db\ActiveQuery::className());

        $TestUser = $User->one();
        expect('TestUser is NOT NULL', $TestUser)->notNull();
        expect('TestUser is instance of class Users', $TestUser)->isInstanceOf(Users::className());
        expect('TestUser->user_id == this->UserCollaboration->user_id', $TestUser->user_id == $this->UserCollaboration->user_id)->true();
    }
}