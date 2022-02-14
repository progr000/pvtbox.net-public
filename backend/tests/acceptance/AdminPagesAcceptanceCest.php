<?php
namespace frontend;
use backend\AcceptanceTester;

class AdminPagesAcceptanceCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->setHeader("Accept-Language", "en-US");
        //$I->haveHttpHeader("Accept-Language", "en-US");
        //$I->amOnPage('/');
    }

    public function _after(AcceptanceTester $I)
    {
    }

    public function checkIndex(AcceptanceTester $I)
    {
        $I->wantTo('Check availability Admin-Index');
        $I->amOnPage('/');
        $I->see('No required SSL certificate was sent');
        /*
        $I->see('<h1>Login</h1>');
        $I->see('<p>Please fill out the following fields to login:</p>');
        $I->see('Remind password');
        */
    }

}