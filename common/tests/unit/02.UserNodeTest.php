<?php
namespace common\tests\unit;

use common\models\Users;
use common\models\UserNode;
use common\models\Licenses;
use frontend\models\NodeApi;

/**
 * Class UserNodeTest
 * generate by command: clear && cept generate:test unit UserNodeTest -c common
 * than rename file UserNodeTest.php to 2.UserNodeTest.php
 * run by command: clear && cept run -c common -vvv  unit 2.UserNodeTest
 * @package common
 */
class UserNodeTest extends DefaultModel
{
    /** @var string  */
    private $node_hash;

    /** @var \common\models\Users */
    private $User;

    /** @var \common\models\UserNode */
    private $UserNode;

    protected function _before()
    {
        parent::_before();

        Users::deleteAll();
        UserNode::deleteAll();

        $this->User = new Users();
        $this->User->user_email = $this->test_emails_pull[1];
        $this->User->user_name  = "Test User Name";
        $this->User->license_type = Licenses::TYPE_FREE_TRIAL;
        $this->User->setPassword(uniqid(), false);
        $this->User->generateAuthKey();
        //$this->User->user_last_ip  = Yii::$app->request->getUserIP();
        $this->User->save();

        $this->UserNode = new UserNode();
    }

    protected function _after()
    {
        parent::_after();

        Users::deleteAll();
        UserNode::deleteAll();
    }

    protected function saveUserNodeIntoDbForTest()
    {
        $this->node_hash = hash("sha512", uniqid('', true) . microtime());
        $this->UserNode->node_hash = $this->node_hash;
        $this->UserNode->user_id = $this->User->user_id;

        expect('UserNode was saved in DB', $this->UserNode->save())->true();
    }

    // tests
    public function testValidateEmptyValues()
    {
        expect('Model is invalid', $this->UserNode->validate())->false();
        expect('node_hash has error (required)', $this->UserNode->getErrors())->hasKey('node_hash');
        expect('user_id has error (required)', $this->UserNode->getErrors())->hasKey('user_id');
    }

    public function testValidateWrongValues()
    {
        $this->UserNode->node_hash = uniqid('', true); // less than 128 characters
        $this->UserNode->user_id = "test"; // not integer
        $this->UserNode->node_name = md5("test");
        $this->UserNode->node_created = "test";
        $this->UserNode->node_updated = "test";
        $this->UserNode->node_countrycode = "test";
        $this->UserNode->node_country = hash('sha512', uniqid());
        $this->UserNode->node_city = hash('sha512', uniqid());
        $this->UserNode->node_useragent = hash('sha512', uniqid()).hash('sha512', uniqid());
        $this->UserNode->node_osname = hash('sha512', uniqid()).hash('sha512', uniqid());
        $this->UserNode->node_ostype = "test";
        $this->UserNode->node_devicetype = "test";
        $this->UserNode->node_online = "test";
        $this->UserNode->node_status = "test";
        $this->UserNode->node_upload_speed = "test";
        $this->UserNode->node_download_speed = "test";
        $this->UserNode->node_disk_usage = "test";
        $this->UserNode->node_logout_status = "test";
        $this->UserNode->node_wipe_status = "test";
        expect('Model is invalid', $this->UserNode->validate())->false();
        expect('node_hash has error (less than 128 characters)', $this->UserNode->getErrors())->hasKey('node_hash');
        expect('user_id has error (not integer)', $this->UserNode->getErrors())->hasKey('user_id');
        expect('node_name has error', $this->UserNode->getErrors())->hasKey('node_name');
        expect('node_created has error', $this->UserNode->getErrors())->hasKey('node_created');
        expect('node_updated has error', $this->UserNode->getErrors())->hasKey('node_updated');
        expect('node_countrycode has error', $this->UserNode->getErrors())->hasKey('node_countrycode');
        expect('node_country has error', $this->UserNode->getErrors())->hasKey('node_country');
        expect('node_city has error', $this->UserNode->getErrors())->hasKey('node_city');
        expect('node_useragent has error', $this->UserNode->getErrors())->hasKey('node_useragent');
        expect('node_osname has error', $this->UserNode->getErrors())->hasKey('node_osname');
        expect('node_ostype has error', $this->UserNode->getErrors())->hasKey('node_ostype');
        expect('node_devicetype has error', $this->UserNode->getErrors())->hasKey('node_devicetype');
        expect('node_online has error', $this->UserNode->getErrors())->hasKey('node_online');
        expect('node_status has error', $this->UserNode->getErrors())->hasKey('node_status');
        expect('node_upload_speed has error', $this->UserNode->getErrors())->hasKey('node_upload_speed');
        expect('node_download_speed has error', $this->UserNode->getErrors())->hasKey('node_download_speed');
        expect('node_disk_usage has error', $this->UserNode->getErrors())->hasKey('node_disk_usage');
        expect('node_logout_status has error', $this->UserNode->getErrors())->hasKey('node_logout_status');
        expect('node_wipe_status has error', $this->UserNode->getErrors())->hasKey('node_wipe_status');


        $this->UserNode = new UserNode();
        $this->UserNode->node_hash = hash("sha512", uniqid('', true) . microtime()) . "1"; // more than 128 characters
        $this->UserNode->user_id = 1; // not exists user_id (foreign key)
        expect('Model is invalid', $this->UserNode->validate())->false();
        expect('node_hash has error (more than 128 characters)', $this->UserNode->getErrors())->hasKey('node_hash');
        expect('user_id has error (not exists user_id {foreign key})', $this->UserNode->getErrors())->hasKey('user_id');
    }

    public function testValidateCorrectValues()
    {
        $this->UserNode->node_hash = hash("sha512", uniqid('', true) . microtime());
        $this->UserNode->user_id = $this->User->user_id;
        $this->UserNode->node_name = "Test Node Name";
        $this->UserNode->node_created = date(SQL_DATE_FORMAT);
        $this->UserNode->node_updated = date('Y.m.d');
        $this->UserNode->node_countrycode = "US";
        $this->UserNode->node_country = "United States of America";
        $this->UserNode->node_city = "Dakota";
        $this->UserNode->node_useragent = "Chromium Browser 2.5.5";
        $this->UserNode->node_osname = "Windows";
        $this->UserNode->node_ostype = UserNode::OSTYPE_WINDOWS;
        $this->UserNode->node_devicetype = UserNode::DEVICE_DESKTOP;
        $this->UserNode->node_online = UserNode::ONLINE_ON;
        $this->UserNode->node_status = UserNode::STATUS_ACTIVE;
        $this->UserNode->node_upload_speed = 100;
        $this->UserNode->node_download_speed = 200;
        $this->UserNode->node_disk_usage = 300;
        $this->UserNode->node_logout_status = UserNode::LOGOUT_STATUS_IN_PROGRESS;
        $this->UserNode->node_wipe_status = UserNode::WIPE_STATUS_SUCCESS;
        expect('Model is valid', $this->UserNode->validate())->true();
        expect('No errors', sizeof($this->UserNode->getErrors()))->isEmpty();
    }

    public function testSaveIntoDatabase()
    {
        $this->saveUserNodeIntoDbForTest();
    }

    public function testValidateExistedValues()
    {
        /* first model */
        $this->saveUserNodeIntoDbForTest();

        /* duplicate model */
        $UserNode = new UserNode();
        $UserNode->node_hash = $this->node_hash;
        $UserNode->user_id = $this->User->user_id;

        //var_dump($UserNode->validate());
        //var_dump($UserNode->getErrors()); exit;
        expect('UserNode was not saved in DB', $UserNode->validate())->false();
        expect('UserNode was not saved in DB', $UserNode->save())->false();
    }

    public function testGetUser()
    {
        $this->saveUserNodeIntoDbForTest();

        $User = $this->UserNode->getUser();
        expect('UserNode is NOT NULL', $User)->notNull();
        expect('UserNode is instance of class UserNode', $User)->isInstanceOf(\yii\db\ActiveQuery::className());

        $TestUser = $User->one();
        expect('TestUser is NOT NULL', $TestUser)->notNull();
        expect('TestUser is instance of class Users', $TestUser)->isInstanceOf(Users::className());
        expect('TestUser->user_id == this->UserNode->user_id', $TestUser->user_id == $this->UserNode->user_id)->true();
    }

    public function testFindIdentity()
    {
        $this->saveUserNodeIntoDbForTest();

        $UserNode = UserNode::findIdentity($this->UserNode->node_id);
        expect('UserNode is NOT NULL', $UserNode)->notNull();
        expect('UserNode is instance of class UserNode', $UserNode)->isInstanceOf(UserNode::className());
    }

    public function testFindByHash()
    {
        $this->saveUserNodeIntoDbForTest();

        $UserNode = UserNode::findByHash($this->UserNode->node_hash);
        expect('UserNode is NOT NULL', $UserNode)->notNull();
        expect('UserNode is instance of class UserNode', $UserNode)->isInstanceOf(UserNode::className());
    }

    public function testFindNodeWebFM()
    {
        $NodeFM = NodeApi::registerNodeFM($this->User);
        expect('NodeFM is NOT NULL', $NodeFM)->notNull();
        expect('NodeFM is instance of class UserNode', $NodeFM)->isInstanceOf(UserNode::className());

        $testNodeFM = UserNode::findNodeWebFM($this->User->user_id);
        expect('testNodeFM is NOT NULL', $testNodeFM)->notNull();
        expect('testNodeFM is instance of class UserNode', $testNodeFM)->isInstanceOf(UserNode::className());
        expect('testNodeFM->node_id == NodeFM->node_id', $NodeFM->node_id == $testNodeFM->node_id)->true();
    }


}