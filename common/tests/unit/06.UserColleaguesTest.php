<?php
namespace common\tests\unit;

use common\models\Users;
use common\models\UserFiles;
use common\models\UserCollaborations;
use common\models\UserColleagues;
use common\models\Licenses;

/**
 * Class UserColleaguesTest
 * generate by command: clear && cept generate:test unit UserColleaguesTest -c common
 * than rename file UserColleaguesTest.php to 6.UserColleaguesTest.php
 * run by command: clear && cept run -c common -vvv  unit 6.UserColleaguesTest
 * @package common
 */
class UserColleaguesTest extends DefaultModel
{
    /** @var \common\models\Users */
    private $User;

    /** @var \common\models\Users */
    private $ColleagueUser;

    /** @var \common\models\UserFiles */
    private $UserFile;

    /** @var \common\models\UserCollaborations */
    private $UserCollaboration;

    /** @var \common\models\UserColleagues */
    private $UserColleague;

    protected function _before()
    {
        parent::_before();

        Users::deleteAll();
        UserFiles::deleteAll();
        UserCollaborations::deleteAll();
        UserColleagues::deleteAll();

        $this->User = new Users();
        $this->User->user_email = $this->test_emails_pull[1];
        $this->User->user_name = "Test User Name";
        $this->User->license_type = Licenses::TYPE_FREE_TRIAL;
        $this->User->setPassword(uniqid(), false);
        $this->User->generateAuthKey();
        $this->User->save();

        $this->ColleagueUser = new Users();
        $this->ColleagueUser->user_email = $this->test_emails_pull[9];
        $this->ColleagueUser->user_name = "Colleague User Name";
        $this->ColleagueUser->license_type = Licenses::TYPE_FREE_TRIAL;
        $this->ColleagueUser->setPassword(uniqid(), false);
        $this->ColleagueUser->generateAuthKey();
        $this->ColleagueUser->save();

        $this->UserFile = new UserFiles();
        $this->UserFile->file_uuid = md5(uniqid('', true) . microtime());
        $this->UserFile->user_id = $this->User->user_id;
        $this->UserFile->file_name = "TestFileName.txt";
        $this->UserFile->file_parent_id = 0;
        $this->UserFile->is_deleted = UserFiles::FILE_UNDELETED;
        $this->UserFile->is_folder = UserFiles::TYPE_FOLDER;
        $this->UserFile->save();

        $this->UserCollaboration = new UserCollaborations();
        $this->UserCollaboration->file_uuid = $this->UserFile->file_uuid;
        $this->UserCollaboration->user_id = $this->User->user_id;
        $this->UserCollaboration->collaboration_status = UserCollaborations::STATUS_ACTIVE;
        $this->UserCollaboration->save();

        $this->UserColleague = new UserColleagues();
    }

    protected function _after()
    {
        parent::_after();

        Users::deleteAll();
        UserFiles::deleteAll();
        UserCollaborations::deleteAll();
        UserColleagues::deleteAll();
    }

    protected function saveUserColleagueIntoDbForTest()
    {
        $this->UserColleague->colleague_status = UserColleagues::STATUS_INVITED;
        $this->UserColleague->colleague_permission = UserColleagues::PERMISSION_VIEW;
        $this->UserColleague->colleague_invite_date = date(SQL_DATE_FORMAT);
        $this->UserColleague->colleague_invite_date = null;
        $this->UserColleague->colleague_email = $this->ColleagueUser->user_email;
        $this->UserColleague->user_id = $this->ColleagueUser->user_id;
        $this->UserColleague->collaboration_id = $this->UserCollaboration->collaboration_id;

        expect('UserColleague was saved in DB', $this->UserColleague->save())->true();
    }

    // tests
    public function testValidateEmptyValues()
    {
        expect('Model is invalid', $this->UserColleague->validate())->false();
        //expect('colleague_status has error (required)', $this->UserColleague->getErrors())->hasKey('colleague_status');
        //expect('colleague_permission has error (required)', $this->UserColleague->getErrors())->hasKey('colleague_permission');
        //expect('user_id has error (required)', $this->UserColleague->getErrors())->hasKey('user_id');
        expect('colleague_email has error (required)', $this->UserColleague->getErrors())->hasKey('colleague_email');
        expect('collaboration_id has error (required)', $this->UserColleague->getErrors())->hasKey('collaboration_id');
    }

    public function testValidateWrongValues()
    {
        $this->UserColleague->colleague_status      = "test"; // out of range
        $this->UserColleague->colleague_permission  = "test"; // out of range
        $this->UserColleague->colleague_email       = "test"; // wrong email format
        $this->UserColleague->user_id               = "test"; // not integer
        $this->UserColleague->collaboration_id      = "test"; // not integer
        $this->UserColleague->colleague_invite_date = "test";
        $this->UserColleague->colleague_joined_date = "test";
        expect('Model is invalid', $this->UserColleague->validate())->false();
        expect('colleague_status has error (out of range)', $this->UserColleague->getErrors())->hasKey('colleague_status');
        expect('colleague_permission has error (out of range)', $this->UserColleague->getErrors())->hasKey('colleague_permission');
        expect('colleague_email has error (wrong email format)', $this->UserColleague->getErrors())->hasKey('colleague_email');
        expect('user_id has error (not integer)', $this->UserColleague->getErrors())->hasKey('user_id');
        expect('collaboration_id has error (not integer)', $this->UserColleague->getErrors())->hasKey('collaboration_id');
        expect('colleague_invite_date has error', $this->UserColleague->getErrors())->hasKey('colleague_invite_date');
        expect('colleague_joined_date has error', $this->UserColleague->getErrors())->hasKey('colleague_joined_date');

        $this->UserColleague = new UserColleagues();
        $this->UserColleague->colleague_status      = UserColleagues::STATUS_INVITED;   // ok
        $this->UserColleague->colleague_permission  = UserColleagues::PERMISSION_VIEW;  // ok
        $this->UserColleague->colleague_email       = $this->ColleagueUser->user_email; // ok
        $this->UserColleague->user_id               = 1; // not exists (foreign key)
        $this->UserColleague->collaboration_id      = 1; // not exists (foreign key)
        expect('Model is invalid', $this->UserColleague->validate())->false();
        expect('colleague_status has not error (OK)', $this->UserColleague->getErrors())->hasntKey('colleague_status');
        expect('colleague_permission has not error (OK)', $this->UserColleague->getErrors())->hasntKey('colleague_permission');
        expect('colleague_email has not error (OK)', $this->UserColleague->getErrors())->hasntKey('colleague_email');
        expect('user_id has error (not exists {foreign key})', $this->UserColleague->getErrors())->hasKey('user_id');
        expect('collaboration_id has error (not exists {foreign key})', $this->UserColleague->getErrors())->hasKey('collaboration_id');
    }

    public function testValidateCorrectValues()
    {
        $this->UserColleague->colleague_status = UserColleagues::STATUS_INVITED;
        $this->UserColleague->colleague_permission = UserColleagues::PERMISSION_VIEW;
        $this->UserColleague->colleague_email = $this->ColleagueUser->user_email;
        $this->UserColleague->user_id = $this->ColleagueUser->user_id;
        $this->UserColleague->collaboration_id = $this->UserCollaboration->collaboration_id;
        $this->UserColleague->colleague_invite_date = date(SQL_DATE_FORMAT);
        $this->UserColleague->colleague_joined_date = date('Y-m-d H:i:s+01');
        expect('Model is valid', $this->UserColleague->validate())->true();
        expect('No errors', sizeof($this->UserColleague->getErrors()))->isEmpty();

        $this->UserColleague = new UserColleagues();
        $this->UserColleague->colleague_status = UserColleagues::STATUS_INVITED;
        $this->UserColleague->colleague_permission = UserColleagues::PERMISSION_VIEW;
        $this->UserColleague->colleague_email = $this->test_emails_pull[8];
        $this->UserColleague->user_id = null;
        $this->UserColleague->collaboration_id = $this->UserCollaboration->collaboration_id;
        $this->UserColleague->colleague_invite_date = date(SQL_DATE_FORMAT);
        $this->UserColleague->colleague_joined_date = date('Y-m-d H:i:s-01');
        expect('Model is valid', $this->UserColleague->validate())->true();
        expect('No errors', sizeof($this->UserColleague->getErrors()))->isEmpty();
    }

    public function testSaveIntoDatabase()
    {
        $this->saveUserColleagueIntoDbForTest();

        $UserColleague = new UserColleagues();
        $UserColleague->colleague_invite_date = date(SQL_DATE_FORMAT);
        $UserColleague->colleague_status = UserColleagues::STATUS_JOINED;
        $UserColleague->colleague_permission = UserColleagues::PERMISSION_OWNER;
        $UserColleague->colleague_email = $this->test_emails_pull[8];
        $UserColleague->user_id = null;
        $UserColleague->collaboration_id = $this->UserCollaboration->collaboration_id;

        expect('UserColleague was saved in DB', $UserColleague->save())->true();
    }

    public function testValidateExistedValues()
    {
        /* first model */
        $this->saveUserColleagueIntoDbForTest();

        /* duplicate model v1 */
        $UserColleague = new UserColleagues();
        $UserColleague->colleague_invite_date = date(SQL_DATE_FORMAT);
        $UserColleague->colleague_status = UserColleagues::STATUS_JOINED;
        $UserColleague->colleague_permission = UserColleagues::PERMISSION_OWNER;
        $UserColleague->colleague_email = $this->ColleagueUser->user_email;
        $UserColleague->user_id = $this->ColleagueUser->user_id;
        $UserColleague->collaboration_id = $this->UserCollaboration->collaboration_id;

        //var_dump($UserColleague->validate());
        //var_dump($UserColleague->getErrors());exit;
        expect('Model is invalid', $UserColleague->validate())->false();
        expect('UserColleague was not saved in DB', $UserColleague->save())->false();

        /* duplicate model v2 */
        $UserColleague = new UserColleagues();
        $UserColleague->colleague_invite_date = date(SQL_DATE_FORMAT);
        $UserColleague->colleague_status = UserColleagues::STATUS_JOINED;
        $UserColleague->colleague_permission = UserColleagues::PERMISSION_OWNER;
        $UserColleague->colleague_email = $this->ColleagueUser->user_email;
        $UserColleague->user_id = null;
        $UserColleague->collaboration_id = $this->UserCollaboration->collaboration_id;

        //var_dump($UserColleague->validate());
        //var_dump($UserColleague->getErrors());exit;
        expect('Model is invalid', $UserColleague->validate())->false();
        expect('UserColleague was not saved in DB', $UserColleague->save())->false();

        /* duplicate model v3 */
        /*
        $UserColleague = new UserColleagues();
        $UserColleague->colleague_invite_date = date(SQL_DATE_FORMAT);
        $UserColleague->colleague_status = UserColleagues::STATUS_JOINED;
        $UserColleague->colleague_permission = UserColleagues::PERMISSION_OWNER;
        $UserColleague->colleague_email = null;
        $UserColleague->user_id = $this->ColleagueUser->user_id;
        $UserColleague->collaboration_id = $this->UserCollaboration->collaboration_id;

        var_dump($UserColleague->validate());
        var_dump($UserColleague->getErrors());exit;
        expect('Model is invalid', $UserColleague->validate())->false();
        expect('UserColleague was not saved in DB', $UserColleague->save())->false();
        */
    }

    public function testGetCollaboration()
    {
        $this->saveUserColleagueIntoDbForTest();

        $UserCollaboration = $this->UserColleague->getCollaboration();
        expect('UserCollaboration is NOT NULL', $UserCollaboration)->notNull();
        expect('UserCollaboration is instance of class ActiveQuery', $UserCollaboration)->isInstanceOf(\yii\db\ActiveQuery::className());

        $TestUserCollaboration = $UserCollaboration->one();
        expect('TestFile is NOT NULL', $TestUserCollaboration)->notNull();
        expect('TestFile is instance of class UserCollaborations', $TestUserCollaboration)->isInstanceOf(UserCollaborations::className());
        expect('TestUserCollaboration->file_uuid == this->UserCollaboration->file_uuid', $TestUserCollaboration->collaboration_id == $this->UserColleague->collaboration_id)->true();
    }

    public function testGetUser()
    {
        $this->saveUserColleagueIntoDbForTest();

        $User = $this->UserColleague->getUser();
        expect('User is NOT NULL', $User)->notNull();
        expect('User is instance of class ActiveQuery', $User)->isInstanceOf(\yii\db\ActiveQuery::className());

        $TestUser = $User->one();
        expect('TestUser is NOT NULL', $TestUser)->notNull();
        expect('TestUser is instance of class Users', $TestUser)->isInstanceOf(Users::className());
        expect('TestUser->user_id == this->UserCollaboration->user_id', $TestUser->user_id == $this->UserColleague->user_id)->true();
    }

    public function testPrepareColleagueData()
    {
        $this->saveUserColleagueIntoDbForTest();

        $Test = UserColleagues::prepareColleagueData($this->UserColleague);
        expect('Array with keys', $Test)->hasKey('color');
        expect('Array with keys', $Test)->hasKey('name');
        expect('Array with keys', $Test)->hasKey('email');
        expect('Array with keys', $Test)->hasKey('status');
        expect('Array with keys', $Test)->hasKey('date_utc');
        expect('Array with keys', $Test)->hasKey('date');
        expect('Array with keys', $Test)->hasKey('ts');
        expect('Array with keys', $Test)->hasKey('access_type');
        expect('Array with keys', $Test)->hasKey('access_type_name');
        expect('Array with keys', $Test)->hasKey('colleague_id');
        expect('Array with keys', $Test)->hasKey('user_id');
    }

    public function testPrepareColleagueDataFromArray()
    {
        $this->saveUserColleagueIntoDbForTest();
        $tmp = UserColleagues::find()
            ->where(['colleague_id' => $this->UserColleague->colleague_id])
            ->asArray()
            ->one();

        $Test = UserColleagues::prepareColleagueDataFromArray($tmp);
        expect('Array with keys', $Test)->hasKey('color');
        expect('Array with keys', $Test)->hasKey('name');
        expect('Array with keys', $Test)->hasKey('email');
        expect('Array with keys', $Test)->hasKey('status');
        expect('Array with keys', $Test)->hasKey('date_utc');
        expect('Array with keys', $Test)->hasKey('date');
        expect('Array with keys', $Test)->hasKey('ts');
        expect('Array with keys', $Test)->hasKey('access_type');
        expect('Array with keys', $Test)->hasKey('access_type_name');
        expect('Array with keys', $Test)->hasKey('colleague_id');
        expect('Array with keys', $Test)->hasKey('user_id');
    }

}