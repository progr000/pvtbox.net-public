<?php
namespace frontend;

use Yii;
use frontend\AcceptanceTester;

class PagesAcceptanceCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->setHeader("Accept-Language", "en-US");
        //$I->haveHttpHeader("Accept-Language", "en-US");
        //$I->amOnPage('/');
        //$I->see(Yii::$app->name . ' video presentation');
        //$I->see('What are our benefits from others?');
    }

    public function _after(AcceptanceTester $I)
    {
    }

    public function checkIndex(AcceptanceTester $I)
    {
        $I->wantTo('Check availability Index');
        $I->amOnPage('/');
        //$I->see(Yii::$app->name . ' video presentation');
        $I->see('Pvtbox private cloud');
        $I->see('The best at its best');
        //$I->makeScreenshot('checkIndex');
    }

    public function checkDownload(AcceptanceTester $I)
    {
        $I->wantTo('Check availability Download');
        $I->setHeader("User-Agent", "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/80.0.3987.87 Chrome/80.0.3987.87 Safari/537.36");
        $I->amOnPage('/download');
        $I->see('installer download should start automatically...');
        $I->see('for other platforms');
        //$I->makeScreenshot('checkDownload');
    }

    public function checkFeatures(AcceptanceTester $I)
    {
        $I->wantTo('Check availability Features');
        $I->amOnPage('/features');
        $I->see('Our Features');
        $I->see('Each version has the following capabilities');
        //$I->makeScreenshot('checkFeatures');
    }

    public function checkPricing(AcceptanceTester $I)
    {
        $I->wantTo('Check availability Pricing');
        $I->amOnPage('/pricing');
        $I->see('Pricing Guide');
        $I->see('We accept:');
        //$I->makeScreenshot('checkPricing');
    }

    public function checkBog(AcceptanceTester $I)
    {
        $I->wantTo('Check availability Blog');
        $I->amOnPage('/blog');
        $I->see('Blog');
        $I->see('Categories');
        $I->see('<form role="search" method="get" class="search-form"');
        //$I->makeScreenshot('checkSupport');
    }

    public function checkSupport(AcceptanceTester $I)
    {
        $I->wantTo('Check availability Support');
        $I->amOnPage('/support');
        $I->see('If you have any questions, please ask us via the');
        //$I->makeScreenshot('checkSupport');
    }

    public function checkUserGuide(AcceptanceTester $I)
    {
        $I->wantTo('Check availability Support');
        $I->amOnPage('https://docs.pvtbox.net/');
        $I->see('Pvtbox User Guide');
        //$I->makeScreenshot('checkSupport');
    }

    public function checkTerms(AcceptanceTester $I)
    {
        $I->wantTo('Check availability Terms');
        $I->amOnPage('/terms');
        $I->see('Terms and Conditions');
        $I->see('Third-party Licenses');
        //$I->makeScreenshot('checkTerms');
    }

    public function checkPrivacy(AcceptanceTester $I)
    {
        $I->wantTo('Check availability Privacy');
        $I->amOnPage('/privacy');
        $I->see('Privacy Policy');
        $I->see('Changes to This Privacy Policy');
        //$I->makeScreenshot('checkPrivacy');
    }

    public function checkSla(AcceptanceTester $I)
    {
        $I->wantTo('Check availability Sla');
        $I->amOnPage('/sla');
        $I->see('Service Level Agreement (SLA)');
        $I->see('Recommendations');
        //$I->makeScreenshot('checkSla');
    }

    public function checkFaq(AcceptanceTester $I)
    {
        $I->wantTo('Check availability Faq');
        $I->amOnPage('/faq');
        $I->see('Frequently Asked Questions');
        $I->see('Business user questions');
        //$I->makeScreenshot('checkFaq');
    }

    public function checkAbout(AcceptanceTester $I)
    {
        $I->wantTo('Check availability About');
        $I->amOnPage('/about');
        $I->see('About Us');
        $I->see('85 Great Portland Street');
        //$I->makeScreenshot('checkAbout');
    }

    public function checkAffiliate(AcceptanceTester $I)
    {
        $I->wantTo('Check availability Entrance');
        $I->amOnPage('/affiliate');
        $I->see('Our affiliate program');
        $I->see('Start to earn with us NOW!');
        //$I->makeScreenshot('checkEntrance');
    }

    public function checkThirdPartyLicenses(AcceptanceTester $I)
    {
        $I->wantTo('Check availability third-party-licenses');
        $I->amOnPage('/third-party-licenses');
        $I->see('Third-party licenses used in Pvtbox desktop application');
        $I->see('elFinder');
        //$I->makeScreenshot('checkEntrance');
    }
}