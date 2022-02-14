<?php
namespace frontend;

use Yii;
use frontend\FunctionalTester;

class LoginSignCest
{
    public $SCREEN_DIR;
    public $SCREEN_NAME_DIR;

    public function __construct()
    {
        $tmp = 'tests' . DIRECTORY_SEPARATOR .
            '_reports' . DIRECTORY_SEPARATOR .
            date('Y-m-d');
        $this->SCREEN_NAME_DIR = '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            $tmp . DIRECTORY_SEPARATOR;
        $this->SCREEN_DIR = dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . $tmp;
        //1var_dump($this->SCREEN_DIR); exit;
        if (!file_exists($this->SCREEN_DIR)) {
            @mkdir($this->SCREEN_DIR, 0777, true);
            @chmod($this->SCREEN_DIR, 0777);
        }
    }

//    public function _before(FunctionalTester $I)
//    {
//        //$I->haveHttpHeader("Accept-Language", "en-US");
//        $I->amOnPage('/');
//        $I->see(Yii::$app->name . ' video presentation');
//        $I->see('What are our benefits compared to others?');
//    }
//
//    // tests
//    public function loginCheck(FunctionalTester $I)
//    {
//        $I->wantTo('Check availability Login method');
//        $I->click('#btn-login-dialog');
//        $I->wait(1);
//        $I->makeScreenshot($this->SCREEN_NAME_DIR . 'screen-loginCheckStart-' . date('His') . '-screen');
//        $I->seeElement('#signup-login-modal');
//        $I->fillField('LoginForm[user_email]', 'user222@mail.ru');
//        $I->fillField('LoginForm[password]','qwerty');
//        $I->click('login-button');
//        $I->wait(5);
//        $I->seeElement('#member-main-menu');
//        $I->makeScreenshot($this->SCREEN_NAME_DIR . 'screen-loginCheckFinish-' . date('His') . '-screen');
//    }
//
//    public function signupCheck(FunctionalTester $I)
//    {
//        $I->wantTo('Check availability Signup method');
//        $I->click('.signup-dialog');
//        $I->wait(1);
//        $I->makeScreenshot($this->SCREEN_NAME_DIR . 'screen-signupCheckStart-' . date('His') . '-screen');
//        $I->seeElement('#signup-login-modal');
//        $I->fillField('SignupForm[user_email]', 'user222@mail.ru');
//        $I->fillField('SignupForm[password]','qwerty');
//        $I->fillField('SignupForm[password_repeat]','qwerty');
//        $I->click('#label-accept-rules');
//        $I->click('#signup-button-form1');
//        $I->wait(5);
//        $I->see('This E-Mail address has already been taken.');
//        $I->makeScreenshot($this->SCREEN_NAME_DIR . 'screen-signupCheckFinish-' . date('His') . '-screen');
//    }
}