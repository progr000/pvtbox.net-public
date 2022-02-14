<?php
namespace common\tests\unit;

use common\models\Users;
use common\models\UserLicenses;
use common\models\Licenses;

/**
 * Class UserLicensesTest
 * generate by command: clear && cept generate:test unit UserLicensesTest -c common
 * than rename file UserLicensesTest.php to 7.UserLicensesTest.php
 * run by command: clear && cept run -c common -vvv  unit 7.UserLicensesTest
 * @package common
 */
class UserLicensesTest extends DefaultModel
{
    /** @var \common\models\Users */
    private $User;

    /** @var \common\models\Users */
    private $ColleagueUser;

    /** @var \common\models\UserLicenses */
    private $UserLicense;

    protected function _before()
    {
        parent::_before();

        Users::deleteAll();
        UserLicenses::deleteAll();

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

        $this->UserLicense = new UserLicenses();
    }

    protected function _after()
    {
        parent::_after();

        Users::deleteAll();
        UserLicenses::deleteAll();
    }

    protected function saveUserLicenseIntoDbForTest()
    {
        $this->UserLicense->lic_start = date(SQL_DATE_FORMAT);
        $this->UserLicense->lic_end = date(SQL_DATE_FORMAT, time()+ 24*60*60*30);
        $this->UserLicense->lic_period = Licenses::PERIOD_MONTHLY;
        $this->UserLicense->lic_owner_user_id = $this->User->user_id;
        $this->UserLicense->lic_colleague_user_id = $this->ColleagueUser->user_id;
        $this->UserLicense->lic_colleague_email = $this->ColleagueUser->user_email;
        $this->UserLicense->lic_lastpay_timestamp = time();
        $this->UserLicense->lic_group_id = time();

        expect('UserLicense was saved in DB', $this->UserLicense->save())->true();
    }

    // tests
    public function testValidateEmptyValues()
    {
        expect('Model is invalid', $this->UserLicense->validate())->false();
        expect('lic_start has not error (OK)', $this->UserLicense->getErrors())->hasntKey('lic_start');
        expect('lic_end has not error (OK)', $this->UserLicense->getErrors())->hasntKey('lic_end');
        expect('lic_period has error (required)', $this->UserLicense->getErrors())->hasKey('lic_period');
        expect('lic_owner_user_id has error (required)', $this->UserLicense->getErrors())->hasKey('lic_owner_user_id');
        expect('lic_colleague_user_id has not error (OK)', $this->UserLicense->getErrors())->hasntKey('lic_colleague_user_id');
        expect('lic_colleague_email has not error (OK)', $this->UserLicense->getErrors())->hasntKey('lic_colleague_email');
        expect('lic_lastpay_timestamp has not error (OK)', $this->UserLicense->getErrors())->hasntKey('lic_lastpay_timestamp');
        expect('lic_group_id has not error (OK)', $this->UserLicense->getErrors())->hasntKey('lic_group_id');
    }

    public function testValidateWrongValues()
    {
        $this->UserLicense->lic_period            = "test"; // not integer & out of range
        $this->UserLicense->lic_owner_user_id     = "test"; // not integer
        $this->UserLicense->lic_colleague_user_id = "test"; // not integer
        $this->UserLicense->lic_colleague_email   = "test"; // wrong email format
        $this->UserLicense->lic_lastpay_timestamp = "test"; // not integer
        $this->UserLicense->lic_group_id          = "test"; // not integer
        $this->UserLicense->lic_start             = "test";
        $this->UserLicense->lic_end               = "11.05.215";
        expect('Model is invalid', $this->UserLicense->validate())->false();
        expect('lic_period has error (not integer & out of range)', $this->UserLicense->getErrors())->hasKey('lic_period');
        expect('lic_owner_user_id has error (not integer)', $this->UserLicense->getErrors())->hasKey('lic_owner_user_id');
        expect('lic_colleague_user_id has error (not integer)', $this->UserLicense->getErrors())->hasKey('lic_colleague_user_id');
        expect('lic_colleague_email has error (wrong email format)', $this->UserLicense->getErrors())->hasKey('lic_colleague_email');
        expect('lic_lastpay_timestamp has error (not integer)', $this->UserLicense->getErrors())->hasKey('lic_lastpay_timestamp');
        expect('lic_group_id has error (not integer)', $this->UserLicense->getErrors())->hasKey('lic_group_id');
        expect('lic_start has error', $this->UserLicense->getErrors())->hasKey('lic_start');
        expect('lic_end has error', $this->UserLicense->getErrors())->hasKey('lic_end');

        $this->UserLicense = new UserLicenses();
        $this->UserLicense->lic_period            = 222; // out of range
        $this->UserLicense->lic_owner_user_id     = 1;   // not exists (foreign key)
        $this->UserLicense->lic_colleague_user_id = 1;   // not exists (foreign key)
        $this->UserLicense->lic_colleague_email   = $this->ColleagueUser->user_email; // ok
        $this->UserLicense->lic_lastpay_timestamp = 1; // ok
        $this->UserLicense->lic_group_id          = 1; // ok
        expect('Model is invalid', $this->UserLicense->validate())->false();
        expect('lic_start has not error (OK)', $this->UserLicense->getErrors())->hasntKey('lic_start');
        expect('lic_end has not error (OK)', $this->UserLicense->getErrors())->hasntKey('lic_end');
        expect('lic_period has error (out of range)', $this->UserLicense->getErrors())->hasKey('lic_period');
        expect('lic_owner_user_id has error (not exists {foreign key})', $this->UserLicense->getErrors())->hasKey('lic_owner_user_id');
        expect('lic_colleague_user_id has error (not exists {foreign key})', $this->UserLicense->getErrors())->hasKey('lic_colleague_user_id');
        expect('lic_colleague_email has not error (OK)', $this->UserLicense->getErrors())->hasntKey('lic_colleague_email');
        expect('lic_lastpay_timestamp has not error (OK)', $this->UserLicense->getErrors())->hasntKey('lic_lastpay_timestamp');
        expect('lic_group_id has not error (OK)', $this->UserLicense->getErrors())->hasntKey('lic_group_id');
    }

    public function testValidateCorrectValues()
    {
        $this->UserLicense->lic_period            = Licenses::PERIOD_MONTHLY;
        $this->UserLicense->lic_owner_user_id     = $this->User->user_id;
        $this->UserLicense->lic_colleague_user_id = $this->ColleagueUser->user_id;
        $this->UserLicense->lic_colleague_email   = $this->ColleagueUser->user_email;
        $this->UserLicense->lic_lastpay_timestamp = time();
        $this->UserLicense->lic_group_id          = time();
        expect('Model is valid', $this->UserLicense->validate())->true();
        expect('lic_start has not error (OK)', $this->UserLicense->getErrors())->hasntKey('lic_start');
        expect('lic_end has not error (OK)', $this->UserLicense->getErrors())->hasntKey('lic_end');
        expect('lic_period has not error (OK)', $this->UserLicense->getErrors())->hasntKey('lic_period');
        expect('lic_owner_user_id has not error (OK)', $this->UserLicense->getErrors())->hasntKey('lic_owner_user_id');
        expect('lic_colleague_user_id has not error (OK)', $this->UserLicense->getErrors())->hasntKey('lic_colleague_user_id');
        expect('lic_colleague_email has not error (OK)', $this->UserLicense->getErrors())->hasntKey('lic_colleague_email');
        expect('lic_lastpay_timestamp has not error (OK)', $this->UserLicense->getErrors())->hasntKey('lic_lastpay_timestamp');
        expect('lic_group_id has not error (OK)', $this->UserLicense->getErrors())->hasntKey('lic_group_id');
    }

    public function testSaveIntoDatabase()
    {
        $this->saveUserLicenseIntoDbForTest();

        $UserLicense = new UserLicenses();
        $UserLicense->lic_start = date(SQL_DATE_FORMAT);
        $UserLicense->lic_end = date(SQL_DATE_FORMAT, time()+ 24*60*60*30);
        $UserLicense->lic_period = Licenses::PERIOD_MONTHLY;
        $UserLicense->lic_owner_user_id = $this->User->user_id;
        $UserLicense->lic_colleague_user_id = $this->User->user_id;
        $UserLicense->lic_colleague_email = $this->User->user_email;
        $UserLicense->lic_lastpay_timestamp = time();
        $UserLicense->lic_group_id = time();
        expect('UserLicense was saved in DB', $UserLicense->save())->true();

        $UserLicense = new UserLicenses();
        $UserLicense->lic_start = date(SQL_DATE_FORMAT);
        $UserLicense->lic_end = date(SQL_DATE_FORMAT, time()+ 24*60*60*30);
        $UserLicense->lic_period = Licenses::PERIOD_MONTHLY;
        $UserLicense->lic_owner_user_id = $this->User->user_id;
        $UserLicense->lic_colleague_user_id = null;
        $UserLicense->lic_colleague_email = $this->test_emails_pull[8];
        $UserLicense->lic_lastpay_timestamp = time();
        $UserLicense->lic_group_id = time();
        expect('UserLicense was saved in DB', $UserLicense->save())->true();
    }

    public function testValidateExistedValues()
    {
        $this->testSaveIntoDatabase();

        // idx_owner_colleague_ids
        $UserLicense = new UserLicenses();
        $UserLicense->lic_start = date(SQL_DATE_FORMAT);
        $UserLicense->lic_end = date(SQL_DATE_FORMAT, time()+ 24*60*60*30);
        $UserLicense->lic_period = Licenses::PERIOD_MONTHLY;
        $UserLicense->lic_owner_user_id = $this->User->user_id;
        $UserLicense->lic_colleague_user_id = $this->ColleagueUser->user_id;
        $UserLicense->lic_colleague_email = null;
        $UserLicense->lic_lastpay_timestamp = time();
        $UserLicense->lic_group_id = time();

        //var_dump($UserLicense->validate());
        //var_dump($UserLicense->getErrors());exit;
        expect('Model is invalid', $UserLicense->validate())->false();
        expect('UserColleague was not saved in DB', $UserLicense->save())->false();

        // idx_owner_id_colleague_email
        $UserLicense = new UserLicenses();
        $UserLicense->lic_start = date(SQL_DATE_FORMAT);
        $UserLicense->lic_end = date(SQL_DATE_FORMAT, time()+ 24*60*60*30);
        $UserLicense->lic_period = Licenses::PERIOD_MONTHLY;
        $UserLicense->lic_owner_user_id = $this->User->user_id;
        $UserLicense->lic_colleague_user_id = null;
        $UserLicense->lic_colleague_email = $this->ColleagueUser->user_email;
        $UserLicense->lic_lastpay_timestamp = time();
        $UserLicense->lic_group_id = time();

        //var_dump($UserLicense->validate());
        //var_dump($UserLicense->getErrors());exit;
        expect('Model is invalid', $UserLicense->validate())->false();
        expect('UserColleague was not saved in DB', $UserLicense->save())->false();

        // idx_owner_id_colleague_email
        $UserLicense = new UserLicenses();
        $UserLicense->lic_start = date(SQL_DATE_FORMAT);
        $UserLicense->lic_end = date(SQL_DATE_FORMAT, time()+ 24*60*60*30);
        $UserLicense->lic_period = Licenses::PERIOD_MONTHLY;
        $UserLicense->lic_owner_user_id = $this->User->user_id;
        $UserLicense->lic_colleague_user_id = null;
        $UserLicense->lic_colleague_email = $this->test_emails_pull[8];
        $UserLicense->lic_lastpay_timestamp = time();
        $UserLicense->lic_group_id = time();

        //var_dump($UserLicense->validate());
        //var_dump($UserLicense->getErrors());exit;
        expect('Model is invalid', $UserLicense->validate())->false();
        expect('UserColleague was not saved in DB', $UserLicense->save())->false();
    }

    public function testGetLicOwnerUser()
    {
        $this->testSaveIntoDatabase();

        $User = $this->UserLicense->getLicOwnerUser();
        expect('User is NOT NULL', $User)->notNull();
        expect('User is instance of class ActiveQuery', $User)->isInstanceOf(\yii\db\ActiveQuery::className());

        $TestUser = $User->one();
        expect('TestUser is NOT NULL', $TestUser)->notNull();
        expect('TestUser is instance of class Users', $TestUser)->isInstanceOf(Users::className());
        expect('TestUser->user_id == this->UserCollaboration->user_id', $TestUser->user_id == $this->UserLicense->lic_owner_user_id)->true();
    }

    public function testGetFreeLicense()
    {
        $this->testSaveIntoDatabase();

        $Test = UserLicenses::getFreeLicense($this->User->user_id);
        expect('No free licenses available', $Test)->null();
    }

    public function testGetFreeLicenseForNonRegistered()
    {
        $this->testSaveIntoDatabase();

        $Test = UserLicenses::getFreeLicenseForNonRegistered($this->User->user_id, $this->ColleagueUser->user_email);
        expect('Test is NOT NULL', $Test)->notNull();
        expect('Test is instance of class UserLicenses', $Test)->isInstanceOf(UserLicenses::className());
        expect('Test->lic_colleague_user_id == this->ColleagueUser->user_id', $Test->lic_colleague_user_id == $this->ColleagueUser->user_id)->true();
    }

    public function testGetLicenseUsedBy()
    {
        $this->testSaveIntoDatabase();

        $Test = UserLicenses::getLicenseUsedBy($this->User->user_id, $this->ColleagueUser->user_id);
        expect('Test is NOT NULL', $Test)->notNull();
        expect('Test is instance of class UserLicenses', $Test)->isInstanceOf(UserLicenses::className());
        expect('Test->lic_colleague_email == this->ColleagueUser->user_email', $Test->lic_colleague_email == $this->ColleagueUser->user_email)->true();

        $Test = UserLicenses::getLicenseUsedBy($this->User->user_id, null, $this->test_emails_pull[8]);
        expect('Test is NOT NULL', $Test)->notNull();
        expect('Test is instance of class UserLicenses', $Test)->isInstanceOf(UserLicenses::className());
        expect('Test->lic_colleague_user_id == null', $Test->lic_colleague_user_id)->null();
    }

    public function testGetLicenseCountInfoForUser()
    {
        $this->testSaveIntoDatabase();

        $Test = UserLicenses::getLicenseCountInfoForUser($this->User->user_id);
        expect('Test is array', $Test)->internalType('array');
        expect('Test has key {used}', $Test)->hasKey('used');
        expect('Test has key {total}', $Test)->hasKey('total');
        expect('Test has key {unused}', $Test)->hasKey('unused');
    }

}