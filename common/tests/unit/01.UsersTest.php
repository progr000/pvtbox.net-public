<?php
namespace common\tests\unit;

use common\models\Users;
use common\models\Licenses;

/**
 * Class UserTest
 * generate by command: clear && cept generate:test unit UserTest -c common
 * than rename file UserTest.php to 1.UserTest.php
 * run by command: clear && cept run -c common -vvv  unit 1.UserTest
 * @package common
 */
class UsersTest extends DefaultModel
{
    /** @var string  */
    private $user_password = 'qwerty';

    /** @var \common\models\Users */
    private $User;

    protected function _before()
    {
        parent::_before();

        Users::deleteAll();

        $this->User = new Users();
    }

    protected function _after()
    {
        parent::_after();

        Users::deleteAll();
    }

    protected function saveUserIntoDbForTest()
    {
        $this->User->user_email = $this->test_emails_pull[1];
        $this->User->user_name  = "Test User Name";
        $this->User->license_type = Licenses::TYPE_FREE_TRIAL;
        $this->User->setPassword($this->user_password, false);
        $this->User->generateAuthKey();

        expect('User was saved in DB', $this->User->save())->true();
    }

    // tests
    public function testValidateEmptyValues()
    {
        expect('Model is invalid', $this->User->validate())->false();                 //$this->assertFalse($User->validate(), 'Model is invalid');
        expect('user_email has error', $this->User->getErrors())->hasKey('user_email'); //$this->assertArrayHasKey()
        expect('user_name has error', $this->User->getErrors())->hasKey('user_name');
        expect('license_type has error', $this->User->getErrors())->hasKey('license_type');
    }

    public function testValidateWrongValues()
    {
        $this->User->user_email = "wrong_email";
        $this->User->user_name  = "123456789012345678901234567890123456789012345678901"; // more than 51 characters
        $this->User->license_type = 'unknown';
        $this->User->user_created = 'test';
        $this->User->user_updated = 'test';
        $this->User->previous_license_business_finish = 'test';
        $this->User->license_expire = 'test';
        $this->User->payment_init_date = 'test';
        $this->User->first_event_uuid_after_cron = 'test';
        $this->User->user_name = hash('sha512', uniqid());
        $this->User->user_company_name = hash('sha512', uniqid());
        $this->User->admin_full_name = hash('sha512', uniqid());
        $this->User->user_hash = hash('sha512', uniqid());
        $this->User->user_remote_hash = hash('sha512', uniqid()) . "1";
        $this->User->password_reset_token = hash('sha512', uniqid()) . hash('sha512', uniqid());
        $this->User->user_ref_id = 'test';
        $this->User->license_bytes_allowed = 'test';
        $this->User->license_bytes_sent = 'test';
        $this->User->license_count_available = 'test';
        $this->User->license_count_used = 'test';
        $this->User->shares_count_in24 = 'test';
        $this->User->license_business_from = 'test';
        $this->User->previous_license_business_from = 'test';
        $this->User->expired_notif_sent = 'test';
        $this->User->user_closed_confirm = 'test';
        $this->User->payment_already_initialized = 'test';
        $this->User->license_period = 'test';
        $this->User->user_status = 'test';
        $this->User->pay_type = 'test';
        $this->User->user_dop_status = 'test';
        $this->User->static_timezone = 'test';
        $this->User->dynamic_timezone = 46801;
        $this->User->user_balance = 'test';
        expect('Model is invalid', $this->User->validate())->false();
        expect('user_email has error', $this->User->getErrors())->hasKey('user_email');
        expect('user_name has error', $this->User->getErrors())->hasKey('user_name');
        expect('license_type has error', $this->User->getErrors())->hasKey('license_type');
        expect('user_created has error', $this->User->getErrors())->hasKey('user_created');
        expect('user_updated has error', $this->User->getErrors())->hasKey('user_updated');
        expect('previous_license_business_finish has error', $this->User->getErrors())->hasKey('previous_license_business_finish');
        expect('license_expire has error', $this->User->getErrors())->hasKey('license_expire');
        expect('payment_init_date has error', $this->User->getErrors())->hasKey('payment_init_date');
        expect('first_event_uuid_after_cron has error', $this->User->getErrors())->hasKey('first_event_uuid_after_cron');
        expect('user_name has error', $this->User->getErrors())->hasKey('user_name');
        expect('user_company_name has error', $this->User->getErrors())->hasKey('user_company_name');
        expect('admin_full_name has error', $this->User->getErrors())->hasKey('admin_full_name');
        expect('user_hash has error', $this->User->getErrors())->hasKey('user_hash');
        expect('user_remote_hash has error', $this->User->getErrors())->hasKey('user_remote_hash');
        expect('password_reset_token has error', $this->User->getErrors())->hasKey('password_reset_token');
        expect('user_ref_id has error', $this->User->getErrors())->hasKey('user_ref_id');
        expect('license_bytes_allowed has error', $this->User->getErrors())->hasKey('license_bytes_allowed');
        expect('license_bytes_sent has error', $this->User->getErrors())->hasKey('license_bytes_sent');
        expect('license_count_available has error', $this->User->getErrors())->hasKey('license_count_available');
        expect('license_count_used has error', $this->User->getErrors())->hasKey('license_count_used');
        expect('shares_count_in24 has error', $this->User->getErrors())->hasKey('shares_count_in24');
        expect('license_business_from has error', $this->User->getErrors())->hasKey('license_business_from');
        expect('previous_license_business_from has error', $this->User->getErrors())->hasKey('previous_license_business_from');
        expect('expired_notif_sent has error', $this->User->getErrors())->hasKey('expired_notif_sent');
        expect('user_closed_confirm has error', $this->User->getErrors())->hasKey('user_closed_confirm');
        expect('payment_already_initialized has error', $this->User->getErrors())->hasKey('payment_already_initialized');
        expect('license_period has error', $this->User->getErrors())->hasKey('license_period');
        expect('user_status has error', $this->User->getErrors())->hasKey('user_status');
        expect('pay_type has error', $this->User->getErrors())->hasKey('pay_type');
        expect('user_dop_status has error', $this->User->getErrors())->hasKey('user_dop_status');
        expect('static_timezone has error', $this->User->getErrors())->hasKey('static_timezone');
        expect('dynamic_timezone has error', $this->User->getErrors())->hasKey('dynamic_timezone');
        expect('user_balance has error', $this->User->getErrors())->hasKey('user_balance');
    }

    public function testValidateCorrectValues()
    {
        $this->User->user_email = $this->test_emails_pull[1];
        $this->User->user_name  = "Test User Name";
        $this->User->license_type = Licenses::TYPE_FREE_TRIAL;
        $this->User->user_created = date(SQL_DATE_FORMAT);
        $this->User->user_updated = date('Y.m.d H:i:s');
        $this->User->previous_license_business_finish = date('Y.m.d; H:i');
        $this->User->license_expire = date('Y.m.d');
        $this->User->payment_init_date = date('Y-m-d, His');
        $this->User->first_event_uuid_after_cron = md5(uniqid());
        $this->User->user_name = 'Test User Name';
        $this->User->user_company_name = 'Test User Company Name';
        $this->User->admin_full_name = 'Test Admin Full Name';
        $this->User->user_hash = md5(uniqid());
        $this->User->user_remote_hash = hash('sha512', uniqid()) ;
        $this->User->password_reset_token = hash('sha512', uniqid());
        $this->User->user_ref_id = null;
        $this->User->license_bytes_allowed = 0;
        $this->User->license_bytes_sent = 10;
        $this->User->license_count_available = 2;
        $this->User->license_count_used = 0;
        $this->User->shares_count_in24 = 10;
        $this->User->license_business_from = 1;
        $this->User->previous_license_business_from = 1;
        $this->User->expired_notif_sent = Users::EXPIRED_NOTIF_NOT_SENT;
        $this->User->user_closed_confirm = Users::CONFIRM_CLOSED;
        $this->User->payment_already_initialized = Users::PAYMENT_NOT_INITIALIZED;
        $this->User->license_period = Licenses::PERIOD_MONTHLY;
        $this->User->user_status = Users::STATUS_ACTIVE;
        $this->User->pay_type = Users::PAY_CRYPTO;
        $this->User->user_dop_status = Users::DOP_IN_PROGRESS;
        $this->User->static_timezone = 46800;
        $this->User->dynamic_timezone = -43200;
        $this->User->user_balance = 10.55;
        expect('Model is valid', $this->User->validate())->true();
        expect('No errors', sizeof($this->User->getErrors()))->isEmpty();
    }

    public function testSetPassword()
    {
        $this->User->user_email = $this->test_emails_pull[1];
        $this->User->setPassword(uniqid(), false);
        //expect('sha512_password - OK', mb_strlen($this->User->sha512_password) == 128)->true();
        expect('password_hash - OK', mb_strlen($this->User->password_hash) == 60)->true();
        expect('user_remote_hash - OK', mb_strlen($this->User->user_remote_hash) == 128)->true();
    }

    public function testGenerateAuthKey()
    {
        $this->User->user_email = $this->test_emails_pull[1];
        $this->User->generateAuthKey();
        expect('auth_key - OK', mb_strlen($this->User->auth_key) == 32)->true();
    }

    public function testSaveIntoDatabase()
    {
        $this->saveUserIntoDbForTest();
    }

    public function testValidateExistedValues()
    {
        /* first model */
        $this->saveUserIntoDbForTest();


        /* duplicate model */
        $User = new Users();
        $User->user_email = $this->test_emails_pull[1];
        $User->user_name  = "Test User Name";
        $User->license_type = Licenses::TYPE_FREE_TRIAL;
        $User->setPassword(uniqid(), false);
        $User->generateAuthKey();

        //var_dump($User->validate());
        //var_dump($User->getErrors()); exit;
        expect('User was not saved in DB', $User->validate())->false();
        expect('User was not saved in DB', $User->save())->false();
    }

    public function testFindOne()
    {
        $this->saveUserIntoDbForTest();

        $User = Users::findOne(['user_id' => $this->User->user_id]);
        expect('User is NOT NULL', $User)->notNull();
        expect('User is instance of class Users', $User)->isInstanceOf(Users::className());
        expect('User->_relative_path IS NOT NULL', $User->_relative_path)->notNull();
        expect('User->_full_path IS NOT NULL', $User->_full_path)->notNull();
    }

    public function testFindIdentity()
    {
        $this->saveUserIntoDbForTest();

        $User = Users::findIdentity($this->User->user_id);
        expect('User is NOT NULL', $User)->notNull();
        expect('User is instance of class Users', $User)->isInstanceOf(Users::className());
        expect('User->_relative_path IS NOT NULL', $User->_relative_path)->notNull();
        expect('User->_full_path IS NOT NULL', $User->_full_path)->notNull();
    }

    public function testFindByUsername()
    {
        $this->saveUserIntoDbForTest();

        $User = Users::findByUsername($this->User->user_name);
        expect('User is NOT NULL', $User)->notNull();
        expect('User is instance of class Users', $User)->isInstanceOf(Users::className());
        expect('User->_relative_path IS NOT NULL', $User->_relative_path)->notNull();
        expect('User->_full_path IS NOT NULL', $User->_full_path)->notNull();
    }

    public function testFindByEmail()
    {
        $this->saveUserIntoDbForTest();

        $User = Users::findByEmail($this->User->user_email);
        expect('User is NOT NULL', $User)->notNull();
        expect('User is instance of class Users', $User)->isInstanceOf(Users::className());
        expect('User->_relative_path IS NOT NULL', $User->_relative_path)->notNull();
        expect('User->_full_path IS NOT NULL', $User->_full_path)->notNull();
    }

    public function testFindByUserHash()
    {
        $this->saveUserIntoDbForTest();

        $User = Users::findByUserHash($this->User->user_hash);
        expect('User is NOT NULL', $User)->notNull();
        expect('User is instance of class Users', $User)->isInstanceOf(Users::className());
        expect('User->_relative_path IS NOT NULL', $User->_relative_path)->notNull();
        expect('User->_full_path IS NOT NULL', $User->_full_path)->notNull();
    }

    public function testFindByUserRemoteHash()
    {
        $this->saveUserIntoDbForTest();

        $User = Users::findByUserRemoteHash($this->User->user_remote_hash);
        expect('User is NOT NULL', $User)->notNull();
        expect('User is instance of class Users', $User)->isInstanceOf(Users::className());
        expect('User->_relative_path IS NOT NULL', $User->_relative_path)->notNull();
        expect('User->_full_path IS NOT NULL', $User->_full_path)->notNull();
    }

    public function testIsPasswordResetTokenValid()
    {
        $this->saveUserIntoDbForTest();
        $this->User->generatePasswordResetToken();

        expect('Token is valid', Users::isPasswordResetTokenValid($this->User->password_reset_token))->true();

        expect('Token is invalid', Users::isPasswordResetTokenValid('ddsdsdsd'))->false();
    }

    public function testFindByPasswordResetToken()
    {
        $this->saveUserIntoDbForTest();

        $User = Users::findByPasswordResetToken('dsdsdfvfd');
        expect('User is NULL', $User)->null();

        $this->User->generatePasswordResetToken();
        $this->User->save();
        $User = Users::findByPasswordResetToken($this->User->password_reset_token);
        expect('User is NOT NULL', $User)->notNull();
        expect('User is instance of class Users', $User)->isInstanceOf(Users::className());
        expect('User->_relative_path IS NOT NULL', $User->_relative_path)->notNull();
        expect('User->_full_path IS NOT NULL', $User->_full_path)->notNull();
    }

    public function testValidatePassword()
    {
        $this->saveUserIntoDbForTest();
        expect('Password is valid', $this->User->validatePassword($this->user_password, false))->true();

        $this->saveUserIntoDbForTest();
        expect('Password is invalid', $this->User->validatePassword('qwerty1', false))->false();
    }

    public function testGetPathNodeFS()
    {
        $this->saveUserIntoDbForTest();
        $User = Users::findIdentity($this->User->user_id);
        expect('User is NOT NULL', $User)->notNull();
        expect('User is instance of class Users', $User)->isInstanceOf(Users::className());
        expect('User->_relative_path IS NOT NULL', $User->_relative_path)->notNull();
        expect('User->_full_path IS NOT NULL', $User->_full_path)->notNull();
    }

    public function testGetUserIcon()
    {
        $this->saveUserIntoDbForTest();
        $ret = Users::getUserIcon($this->User->user_email);
        //var_dump($ret); exit;
        expect('ret["color"] = T', $ret["color"] == "U")->true();
        expect('ret["sname"] = TE', $ret["sname"] == "US")->true();
    }

    public function testGetCountEvents()
    {
        $this->saveUserIntoDbForTest();
        $count = $this->User->getCountEvents();
        expect('Is int $count', is_int($count))->true();
    }
}