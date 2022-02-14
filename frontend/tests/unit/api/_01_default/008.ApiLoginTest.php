<?php
namespace frontend\tests\unit\api\_01_default;

use common\models\Users;
use common\models\UserNode;
use common\models\Licenses;
use frontend\models\NodeApi;

class ApiLoginTest extends ApiDefault
{
    protected $test_action = 'login';

    /** @var \common\models\Users */
    protected $User2;

    /**
     *
     */
    protected function createTestData()
    {
        parent::createTestData();

        /** User2 */
        $this->User2 = new Users();
        $this->User2->user_email = $this->test_emails_pull[2];
        $this->User2->user_name  = "Test2 User Name";
        $this->User2->license_type = Licenses::TYPE_FREE_TRIAL;
        $this->User2->setPassword(hash('sha512', "qwerty"), false);
        $this->User2->generateAuthKey();
        $this->User2->save();

        $this->UserNode->node_status = UserNode::STATUS_ACTIVE;
        $this->UserNode->save();
    }

    /**
     *
     */
    public function testEmptyData()
    {
        $data = [];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_WRONG_DATA in', $ret['errcode'])->contains(NodeApi::ERROR_WRONG_DATA);
        expect('info in', $ret)->hasKey('info');
        expect('is array', $ret['info'])->internalType('array');
        expect('user_email in', $ret['info'])->hasKey('user_email');
        expect('user_password in', $ret['info'])->hasKey('user_password');
        expect('node_hash in', $ret['info'])->hasKey('node_hash');
        expect('node_name in', $ret['info'])->hasKey('node_name');
        expect('node_osname in', $ret['info'])->hasKey('node_osname');
        expect('node_ostype in', $ret['info'])->hasKey('node_ostype');
        expect('node_devicetype in', $ret['info'])->hasKey('node_devicetype');
    }

    /**
     *
     */
    public function testWrongData1()
    {
        $data = [
            'user_email'      => "test",
            'user_password'   => "test",
            'node_hash'       => "test",
            'node_name'       => md5("test"),
            'node_osname'     => hash('sha512', "test") . hash('sha512', "test"),
            'node_ostype'     => "test",
            'node_devicetype' => "test",
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
        expect('no user_hash in', $ret['info'])->hasntKey('user_hash');
        expect('user_email in', $ret['info'])->hasKey('user_email');
        expect('user_password in', $ret['info'])->hasKey('user_password');
        expect('node_hash in', $ret['info'])->hasKey('node_hash');
        expect('node_name in', $ret['info'])->hasKey('node_name');
        expect('node_osname in', $ret['info'])->hasKey('node_osname');
        expect('node_ostype in', $ret['info'])->hasKey('node_ostype');
        expect('node_devicetype in', $ret['info'])->hasKey('node_devicetype');
    }

    /**
     *
     */
    public function testWrongData2()
    {
        $data = [
            'user_hash'       => "test",
            'user_email'      => "test",
            'user_password'   => "test",
            'node_hash'       => "test",
            'node_name'       => md5("test"),
            'node_osname'     => hash('sha512', "test") . hash('sha512', "test"),
            'node_ostype'     => "test",
            'node_devicetype' => "test",
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
        expect('user_hash in', $ret['info'])->hasKey('user_hash');
        expect('no user_email in data or ignored if present cause user_hash is priority', $ret['info'])->hasntKey('user_email');
        expect('no user_password in data or ignored if present cause user_hash is priority', $ret['info'])->hasntKey('user_password');
        expect('node_hash in', $ret['info'])->hasKey('node_hash');
        expect('node_name in', $ret['info'])->hasKey('node_name');
        expect('node_osname in', $ret['info'])->hasKey('node_osname');
        expect('node_ostype in', $ret['info'])->hasKey('node_ostype');
        expect('node_devicetype in', $ret['info'])->hasKey('node_devicetype');
    }

    /**
     *
     */
    public function testIfNodeWipedOrDeleted()
    {
        $this->createTestData();
        $this->UserNode->node_status = UserNode::STATUS_WIPED;
        $this->UserNode->save();

        $data = [
            'user_hash'       => $this->User->user_remote_hash,
            //'user_email'      => "test",
            //'user_password'   => "test",
            'node_hash'       => $this->UserNode->node_hash,
            'node_name'       => $this->UserNode->node_name,
            'node_osname'     => $this->UserNode->node_osname,
            'node_ostype'     => $this->UserNode->node_ostype,
            'node_devicetype' => $this->UserNode->node_devicetype,
        ];
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_BAD_NODE_STATUS in', $ret['errcode'])->contains(NodeApi::ERROR_BAD_NODE_STATUS);
        expect('info in', $ret)->hasKey('info');
        expect('STATUS_WIPED in', $ret['info'])->contains(UserNode::statusLabel(UserNode::STATUS_WIPED));


        $this->UserNode->node_status = UserNode::STATUS_DELETED;
        $this->UserNode->save();
        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_BAD_NODE_STATUS in', $ret['errcode'])->contains(NodeApi::ERROR_BAD_NODE_STATUS);
        expect('info in', $ret)->hasKey('info');
        expect('STATUS_DELETED in', $ret['info'])->contains(UserNode::statusLabel(UserNode::STATUS_DELETED));
    }

    /**
     *
     */
    public function testIfUserNotFoundByHash()
    {
        $this->createTestData();

        $data = [
            'user_hash'       =>  hash('sha512', "any_not_exists_hash"),
            //'user_email'      => "test",
            //'user_password'   => "test",
            'node_hash'       => $this->UserNode->node_hash,
            'node_name'       => $this->UserNode->node_name,
            'node_osname'     => $this->UserNode->node_osname,
            'node_ostype'     => $this->UserNode->node_ostype,
            'node_devicetype' => $this->UserNode->node_devicetype,
        ];

        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_USER_NOT_FOUND in', $ret['errcode'])->contains(NodeApi::ERROR_USER_NOT_FOUND);
        expect('debug in', $ret)->hasKey('debug');
        expect('user_hash in', $ret['debug'])->contains('user_hash');
        expect('remote_actions in', $ret)->hasKey('remote_actions');
    }

    /**
     *
     */
    public function testIfUserNotFoundCauseNotExistEmail()
    {
        $this->createTestData();

        $data = [
            //'user_hash'       =>  hash('sha512', "any_not_exists_hash"),
            'user_email'      => $this->test_emails_pull[9],
            'user_password'   => hash('sha512', "qwerty"),
            'node_hash'       => $this->UserNode->node_hash,
            'node_name'       => $this->UserNode->node_name,
            'node_osname'     => $this->UserNode->node_osname,
            'node_ostype'     => $this->UserNode->node_ostype,
            'node_devicetype' => $this->UserNode->node_devicetype,
        ];

        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_USER_NOT_FOUND in', $ret['errcode'])->contains(NodeApi::ERROR_USER_NOT_FOUND);
        expect('debug in', $ret)->hasKey('debug');
        expect('user_hash in', $ret['debug'])->contains('user_hash');
        expect('remote_actions in', $ret)->hasKey('remote_actions');
    }

    /**
     *
     */
    public function testIfUserNotFoundCauseWrongPassword()
    {
        $this->createTestData();

        $data = [
            //'user_hash'       =>  hash('sha512', "any_not_exists_hash"),
            'user_email'      => $this->test_emails_pull[1],
            'user_password'   => hash('sha512', "qwerty1"),
            'node_hash'       => $this->UserNode->node_hash,
            'node_name'       => $this->UserNode->node_name,
            'node_osname'     => $this->UserNode->node_osname,
            'node_ostype'     => $this->UserNode->node_ostype,
            'node_devicetype' => $this->UserNode->node_devicetype,
        ];

        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_USER_NOT_FOUND in', $ret['errcode'])->contains(NodeApi::ERROR_USER_NOT_FOUND);
        expect('debug in', $ret)->hasKey('debug');
        expect('user_hash in', $ret['debug'])->contains('user_hash');
        expect('remote_actions in', $ret)->hasKey('remote_actions');
    }

    /**
     *
     */
    public function testIfUserNodeNotFoundAndNoEmailPresent()
    {
        $this->createTestData();

        $data = [
            'user_hash'       => $this->User->user_remote_hash,
            //'user_email'      => $this->test_emails_pull[1],
            //'user_password'   => hash('sha512', "qwerty1"),
            'node_hash'       => hash('sha512', 'not_exists_node'),
            'node_name'       => $this->UserNode->node_name . "2",
            'node_osname'     => $this->UserNode->node_osname,
            'node_ostype'     => $this->UserNode->node_ostype,
            'node_devicetype' => $this->UserNode->node_devicetype,
        ];

        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_NODE_NOT_FOUND in', $ret['errcode'])->contains(NodeApi::ERROR_NODE_NOT_FOUND);
        expect('debug in', $ret)->hasKey('debug');
        expect('node_hash in', $ret['debug'])->contains('node_hash');
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
        expect('user_id in', $ret)->hasKey('user_id');
        //expect('user_id is integer', $ret['user_id'])->internalType('integer');
        expect('user_hash in', $ret)->hasKey('user_hash');
        expect('user_hash is string', $ret['user_hash'])->internalType('string');
        expect('user_hash length = 128', strlen($ret['user_hash']) == 128)->true();
        expect('license_type in', $ret)->hasKey('license_type');
        expect('license_type in range', in_array($ret['license_type'], [
            Licenses::TYPE_FREE_DEFAULT,
            Licenses::TYPE_FREE_TRIAL,
            Licenses::TYPE_PAYED_BUSINESS_ADMIN,
            Licenses::TYPE_PAYED_BUSINESS_USER,
            Licenses::TYPE_PAYED_PROFESSIONAL,
        ]))->true();
        expect('servers in', $ret)->hasKey('servers');
        expect('nodes in', $ret)->hasKey('nodes');
        expect('nodes is array', $ret['nodes'])->internalType('array');
        expect('max_path_length in', $ret)->hasKey('max_path_length');
        expect('max_file_name_length in', $ret)->hasKey('max_file_name_length');
        expect('remote_actions in', $ret)->hasKey('remote_actions');
        expect('last_event_uuid in', $ret)->hasKey('last_event_uuid');
    }

    /**
     *
     */
    public function testSuccessIfUserNodeNotFoundButEmailPasswordPresentedAndValid()
    {
        $this->createTestData();

        $data = [
            //'user_hash'       => $this->User->user_remote_hash,
            'user_email'      => $this->test_emails_pull[1],
            'user_password'   => hash('sha512', "qwerty"),
            'node_hash'       => hash('sha512', 'not_exists_node'),
            'node_name'       => $this->UserNode->node_name . "2",
            'node_osname'     => $this->UserNode->node_osname,
            'node_ostype'     => $this->UserNode->node_ostype,
            'node_devicetype' => $this->UserNode->node_devicetype,
        ];

        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        $this->expectOK($ret);
    }

    /**
     *
     */
    public function testUserNodeMismatch()
    {
        $this->createTestData();

        $data = [
            //'user_hash'       => $this->User->user_remote_hash,
            'user_email'      => $this->test_emails_pull[2],
            'user_password'   => hash('sha512', "qwerty"),
            'node_hash'       => $this->UserNode->node_hash,
            'node_name'       => $this->UserNode->node_name,
            'node_osname'     => $this->UserNode->node_osname,
            'node_ostype'     => $this->UserNode->node_ostype,
            'node_devicetype' => $this->UserNode->node_devicetype,
        ];

        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_USER_NODE_MISMATCH in', $ret['errcode'])->contains(NodeApi::ERROR_USER_NODE_MISMATCH);
        expect('info in', $ret)->hasKey('info');
    }

    /**
     *
     */
    public function testLicenseLimitNodes()
    {
        $this->createTestData();
        $this->User->license_type = Licenses::TYPE_FREE_DEFAULT;
        $this->User->save();

        $limit = Licenses::getCountLicenseLimitNodes($this->User->license_type);
        for ($i = 0; $i < $limit; $i++) {
            $UserNode = new UserNode();
            $UserNode->node_hash = hash("sha512", uniqid('', true) . microtime());
            $UserNode->user_id = $this->User->user_id;
            $UserNode->node_name = "TestNode{$i}";
            $UserNode->node_osname = "Linux Ubuntu";
            $UserNode->node_ostype = UserNode::OSTYPE_LINUX;
            $UserNode->node_devicetype = UserNode::DEVICE_DESKTOP;
            $UserNode->node_status = UserNode::STATUS_ACTIVE;
            $UserNode->save();
        }

        $data = [
            'user_hash'       => $this->User->user_remote_hash,
            //'user_email'      => $this->test_emails_pull[1],
            //'user_password'   => hash('sha512', "qwerty"),
            'node_hash'       => $this->UserNode->node_hash,
            'node_name'       => $this->UserNode->node_name,
            'node_osname'     => $this->UserNode->node_osname,
            'node_ostype'     => $this->UserNode->node_ostype,
            'node_devicetype' => $this->UserNode->node_devicetype,
        ];

        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        expect('is array', $ret)->internalType('array');
        expect('result in', $ret)->hasKey('result');
        expect('error in', $ret['result'])->contains('error');
        expect('errcode in', $ret)->hasKey('errcode');
        expect('ERROR_LICENSE_LIMIT in', $ret['errcode'])->contains(NodeApi::ERROR_LICENSE_LIMIT);
        expect('info in', $ret)->hasKey('info');
    }

    /**
     *
     */
    public function testSuccessByHash()
    {
        $this->createTestData();

        $data = [
            'user_hash'       => $this->User->user_remote_hash,
            //'user_email'      => $this->test_emails_pull[1],
            //'user_password'   => hash('sha512', "qwerty"),
            'node_hash'       => $this->UserNode->node_hash,
            'node_name'       => $this->UserNode->node_name,
            'node_osname'     => $this->UserNode->node_osname,
            'node_ostype'     => $this->UserNode->node_ostype,
            'node_devicetype' => $this->UserNode->node_devicetype,
        ];

        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        $this->expectOK($ret);
    }

    /**
     *
     */
    public function testSuccessByEmailPassword()
    {
        $this->createTestData();

        $data = [
            //'user_hash'       => $this->User->user_remote_hash,
            'user_email'      => $this->test_emails_pull[1],
            'user_password'   => hash('sha512', "qwerty"),
            'node_hash'       => $this->UserNode->node_hash,
            'node_name'       => $this->UserNode->node_name,
            'node_osname'     => $this->UserNode->node_osname,
            'node_ostype'     => $this->UserNode->node_ostype,
            'node_devicetype' => $this->UserNode->node_devicetype,
        ];

        $ret = $this->controller->actionTests($this->test_action, $data);
        //var_dump($ret); exit;
        $this->expectOK($ret);
    }
}