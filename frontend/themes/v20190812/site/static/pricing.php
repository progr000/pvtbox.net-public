<?php
/* @var $this yii\web\View */
/* @var $User common\models\Users */

use yii\web\View;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use common\models\Licenses;
use common\models\Preferences;
use common\models\Users;
use frontend\assets\v20190812\pricingAsset;
use frontend\assets\v20190812\downloadAsset;
use frontend\models\forms\PurchaseForm;
use frontend\models\forms\PricingFeedbackForm;

Yii::$app->session->set('after_signup_login_redirect_to', ['/pricing']);
$this->title = Yii::t('app/pricing', 'title');

pricingAsset::register($this);
downloadAsset::register($this);

/* Подготовка сумм */
$sum_professional_one_time = number_format(Preferences::getValueByKey('PriceOneTimeForLicenseProfessional', 99.99, 'float'), 2, '.', '');
if ($User && $User->license_period == Licenses::PERIOD_ANNUALLY) {
    $USER_BILLED_PERIOD = Licenses::getBilledByPeriod(Licenses::PERIOD_ANNUALLY);
    $sum_professional = number_format(Preferences::getValueByKey('PricePerYearForLicenseProfessional', 99.99, 'float'), 2, '.', '');
    $sum_business     = number_format(Preferences::getValueByKey('PricePerYearUserForLicenseBusiness', 99.99, 'float'), 2, '.', '');
    $data_period_pro = Yii::t('app/pricing', 'head_time_PRO_year');
    $data_period_business = Yii::t('app/pricing', 'head_time_Business_year');
} else {
    $USER_BILLED_PERIOD =  Licenses::getBilledByPeriod(Licenses::PERIOD_MONTHLY);
    $sum_professional = number_format(Preferences::getValueByKey('PricePerMonthForLicenseProfessional', 99.99, 'float'), 2, '.', '');
    $sum_business     = number_format(Preferences::getValueByKey('PricePerMonthUserForLicenseBusiness', 99.99, 'float'), 2, '.', '');
    $data_period_pro = Yii::t('app/pricing', 'head_time_PRO_month');
    $data_period_business = Yii::t('app/pricing', 'head_time_Business_month');
}

$BILLED_MONTHLY  = Licenses::getBilledByPeriod(Licenses::PERIOD_MONTHLY);
$BILLED_ANNUALLY = Licenses::getBilledByPeriod(Licenses::PERIOD_ANNUALLY);

/* регистрируем яваскрипт */
$str  = Preferences::getJsStringForPricing();
$str .= "var USER_BILLED_PERIOD = '{$USER_BILLED_PERIOD}';\n";
$str .= "var USER_LICENSE_PERIOD = " . ($User ? $User->license_period : Licenses::PERIOD_NOT_SET) . ";\n";
$str .= "var BILLED_MONTHLY = '" . $BILLED_MONTHLY . "'\n";
$str .= "var BILLED_ANNUALLY = '" . $BILLED_ANNUALLY . "'\n";
$this->registerJs($str, View::POS_END);

$button_lock_title = "";
if ($User) {
    if (in_array($User->payment_already_initialized, [Users::PAYMENT_INITIALIZED, Users::PAYMENT_PROCESSED]) && in_array($User->license_type, [Licenses::TYPE_FREE_DEFAULT, Licenses::TYPE_FREE_TRIAL])) {
        $button_lock_title = Yii::t('app/purchase', "You_are_already_init_license_buy", ['license_type' => Licenses::getType($User->license_type)]);
    } else {
        $button_lock_title = Yii::t('app/pricing', "Contact_support_please", ['license_type' => Licenses::getType($User->license_type)]);
    }
}
?>

<div class="content container">

    <!-- HEADER-DESCRIPTION -->
    <h1 class="centered"><?= Yii::t('app/pricing', 'pricing__title') ?></h1>
    <div class="page-section-description"><?= Yii::t('app/pricing', 'pricing__text') ?></div>

    <!-- TOP PRICING TABLE -->
    <div id="top-pricing-info-table">
        <table class="pricing-table">
            <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Description</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="td-bold">Saas version free</td>
                <td>Free & open source</td>
                <td>Just for file transfer, no sync. Limited functional.</td>
            </tr>
            <tr>
                <td class="td-bold">SaaS version Home</td>
                <td>$69.99/one time</td>
                <td>For home users only. No server OS support.</td>
            </tr>
            <tr>
                <td class="td-bold">SaaS version business</td>
                <td>Choose below</td>
                <td>For business. Server OS support, extended functional.</td>
            </tr>
            <tr class="sh">
                <td class="td-bold">Self-hosted version free</td>
                <td>Free & open source</td>
                <td>Deployment on your server.</td>
            </tr>
            <tr>
                <td class="td-bold">Self-hosted version business</td>
                <td>Choose below</td>
                <td>Deployment on your server. Extended functional.</td>
            </tr>
            </tbody>
        </table>
    </div>

    <!-- ANIMATED DOWN POINTER -->
    <div class="icon-down-image down-pointer animated-item visible-item full-visible">
        <svg class="icon icon-down-arrow">
            <use xlink:href="#down-arrow"></use>
        </svg>
    </div>

    <!-- RADIO BUTTONS -->
    <div class="form-row form-row-pricing-radio">
        <div class="sass-sh-radio">
            <div class="check-wrap">
                <input
                    type="radio"
                    value="saas"
                    class="js-saas-self-toggle no-color"
                    id="radio_saas"
                    autocomplete="off"
                    name="radio-saas-self-hosted" />
                <label for="radio_saas"><span></span><span><?= Yii::t('app/pricing', 'SAAS VERSION') ?><br /><span class="recommended">(recommended)</span></span></label>
            </div>
            <div class="check-wrap or-div">OR</div>
            <div class="check-wrap">
                <input
                    type="radio"
                    data-off-checked="checked"
                    value="self"
                    class="js-saas-self-toggle no-color"
                    id="radio_self"
                    autocomplete="off"
                    name="radio-saas-self-hosted" />
                <label for="radio_self"><span></span><span><?= Yii::t('app/pricing', 'SELF-HOSTED VERSION ') ?></span></label>
            </div>
        </div>
    </div>

    <!-- SAAS -->
    <div id="div-saas" class="saas-self-divs">

        <h2 class="centered"><?= Yii::t('app/pricing', 'Pricing of SaaS Business edition (annual subscription)') ?></h2>

        <table class="pricing-table pricing-table-saas">
            <thead>
            <tr>
                <th>Number of users</th>
                <th>Price/User/Year</th>
            </tr>
            <tbody>
            <tr>
                <td>1-3 users</td>
                <td>$59.99</td>
            </tr>
            <tr>
                <td>4-9 users</td>
                <td>$57.99</td>
            </tr>
            <tr>
                <td>10-25 users</td>
                <td>$54.99</td>
            </tr>
            <tr>
                <td>26-100 users</td>
                <td>$51.99</td>
            </tr>
            <tr>
                <td>101 - 250 users</td>
                <td>$49.99</td>
            </tr>
            <tr>
                <td>251-500 users</td>
                <td>$47.99</td>
            </tr>
            <tr>
                <td>501+</td>
                <td>Contact us</td>
            </tr>
            <tr>
                <td>Server seat</td>
                <td>$699/yr.</td>
            </tr>
            </tbody>
        </table>

        <div class="helper">
            E.g. You have 10 users. The price will be 10 * 54.99 = $ 549.9/year.
            <br />
            <h2 class="centered">Support:</h2>

            Standard support via e-mail included by default in SaaS Business edititon, if you need extended support (Teamviewer, phone calls, personal consultant) and shorter reaction time you may order it.
            <br />
            <table class="pricing-table pricing-table-saas2">
                <thead>
                <tr>
                    <th></th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>TV</th>
                    <th>Price</th>
                </tr>
                <tbody>
                <tr>
                    <td>Standard support<br />8×5 with 72h reaction time</td>
                    <td><span class="yes">Yes</span></td>
                    <td>---</td>
                    <td>---</td>
                    <td>---</td>
                </tr>
                <tr>
                    <td>Extended support<br />8×5 with 4h reaction time</td>
                    <td><span class="yes">Yes</span></td>
                    <td><span class="yes">Yes</span></td>
                    <td><span class="yes">Yes</span></td>
                    <td>$ 2300/yr.</td>
                </tr>
                <tr>
                    <td>Extended support<br />24×7 with 2h reaction time</td>
                    <td><span class="yes">Yes</span></td>
                    <td><span class="yes">Yes</span></td>
                    <td><span class="yes">Yes</span></td>
                    <td>on request</td>
                </tr>
                </tbody>
            </table>
            Support time: UTC +01:00

            <h2 class="centered">Steps to order:</h2>

            To order SaaS Business edition contact us via a form located below, We'll send you a login information with desired licenses seats and 14 days trial period. When you are ready to buy ask us to send invoice manually please.
            <br /><br />
            To order SaaS version Home (home use only) just click the button
            <?=
            Yii::$app->user->isGuest
                ? '<a class="get-app-btn btn primary-btn sm-btn -wide-btn js-open-form" data-off-type="button" data-src="#auth-popup" data-tab="2">' . Yii::t('app/pricing', 'Purchase_now') . '</a>'
                : (
            (in_array($User->license_type, [/*Licenses::TYPE_PAYED_BUSINESS_ADMIN,*/ Licenses::TYPE_PAYED_BUSINESS_USER]) || in_array($User->payment_already_initialized, [Users::PAYMENT_INITIALIZED, Users::PAYMENT_PROCESSED]))
                ? '<a class="get-app-btn btn primary-btn sm-btn -wide-btn btn-notActive masterTooltip void-0" href="#" title="' . $button_lock_title . '">' . Yii::t('app/pricing', 'Purchase_now') . '</a>'
                : '<a class="get-app-btn btn primary-btn sm-btn -wide-btn" id="link-professional-one-time" href="' . Url::to(['/purchase/summary?license=professional&billed=onetime'], CREATE_ABSOLUTE_URL) .'">' . Yii::t('app/pricing', 'Purchase_now') . '</a>'
            )
            ?>
        </div>

        <table class="pricing-table pricing-table-saas2">
            <thead>
            <tr>
                <th></th>
                <th>SaaS free</th>
                <th>SaaS Home</th>
                <th>Saas Business</th>
            </tr>
            </thead>
            <tbody>
                <tr><td>Open source</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Extra fast</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>End-2-end encryption</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Secure links</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Cross-device file transfer</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Available on all platforms</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Network fault tolerance feature</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Multi-platform File Sync</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Unlimited devices</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Unlimited data transfer</td><td><span class="no">No</span></td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Version Control</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>LAN syncing</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Automatic & smart sync</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Subfolder smart sync</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Folder sharing</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>File recovery and version history</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Using "torrent" technology<br />for content delivery</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Downloading only changed parts of the file</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Control Bandwidth Usage</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Secure collaboration</td><td><span class="no">No</span></td><td><span class="limited">Limited</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Real time permission changes</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Password-protected links</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Links with self-destruction timer</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Remote wipe</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Admin panel</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Online Preview (Jpg/Pdf/Doc/Excel/Video files)</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Number of users</td><td><span class="number">1</span></td><td><span class="number">1</span></td><td><span class="unlimited">Unlimited</span></td></tr>
                <tr><td>Server OS support</td><td><span class="no">No</span></td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Secure file server</td><td><span class="no">No</span></td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Full team member control</td><td><span class="no">No</span></td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Management of all members permissions</td><td><span class="no">No</span></td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Detailed event log</td><td><span class="no">No</span></td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Detailed activity log</td><td><span class="no">No</span></td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Priority email support</td><td><span class="no">No</span></td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Personal consultant via phone</td><td><span class="no">No</span></td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Remote support via TeamViewer</td><td><span class="no">No</span></td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Integrity consultant</td><td><span class="no">No</span></td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Branding</td><td><span class="no">No</span></td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Early updates</td><td><span class="no">No</span></td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Outlook plugin</td><td><span class="no">No</span></td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Collabora Office</td><td><span class="no">No</span></td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
                <tr><td>Libre Office</td><td><span class="no">No</span></td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
                <tr class="sm-buttons">
                    <td>&nbsp;</td>
                    <td>
                        <a class="get-app-btn btn primary-btn sm-btn download-link" href="#"><?= Yii::t('app/pricing', 'Download') ?></a>
                        <div style="display: none;">
                            <?= $this->render('/download/other_platforms', ['software' => $software]) ?>
                        </div>
                    </td>
                    <td>
                        <?=
                        Yii::$app->user->isGuest
                            ? '<a class="get-app-btn btn primary-btn sm-btn -wide-btn js-open-form" data-off-type="button" data-src="#auth-popup" data-tab="2">' . Yii::t('app/pricing', 'Purchase_now') . '</a>'
                            : (
                        (in_array($User->license_type, [/*Licenses::TYPE_PAYED_BUSINESS_ADMIN,*/ Licenses::TYPE_PAYED_BUSINESS_USER]) || in_array($User->payment_already_initialized, [Users::PAYMENT_INITIALIZED, Users::PAYMENT_PROCESSED]))
                            ? '<a class="get-app-btn btn primary-btn sm-btn -wide-btn btn-notActive masterTooltip void-0" href="#" title="' . $button_lock_title . '">' . Yii::t('app/pricing', 'Purchase_now') . '</a>'
                            : '<a class="get-app-btn btn primary-btn sm-btn -wide-btn" id="link-professional-one-time" href="' . Url::to(['/purchase/summary?license=professional&billed=onetime'], CREATE_ABSOLUTE_URL) .'">' . Yii::t('app/pricing', 'Purchase_now') . '</a>'
                        )
                        ?>
                    </td>
                    <td>
                        <a class="get-app-btn btn primary-btn sm-btn js-scroll-to" href="#feedback-form">Submit request</a>
                    </td>
                </tr>
            </tbody>
        </table>

    </div>

    <!-- SELF -->
    <div id="div-self" class="saas-self-divs">

        <h2 class="centered"><?= Yii::t('app/pricing', 'Pricing of Self-hosted Business edition (annual subscription)') ?></h2>

        <table class="pricing-table pricing-table-saas">
            <thead>
            <tr>
                <th>Number of users</th>
                <th>Price/User/Year</th>
            </tr>
            <tbody>
            <tr>
                <td>1-3 users</td>
                <td>Free</td>
            </tr>
            <tr>
                <td>4-9 users</td>
                <td>$90 flat rate</td>
            </tr>
            <tr>
                <td>10-25 users</td>
                <td>$45.99</td>
            </tr>
            <tr>
                <td>26-100 users</td>
                <td>$43.99</td>
            </tr>
            <tr>
                <td>101 - 250 users</td>
                <td>$41.99</td>
            </tr>
            <tr>
                <td>251-500 users</td>
                <td>$39.99</td>
            </tr>
            <tr>
                <td>501+</td>
                <td>Contact us</td>
            </tr>
            <tr>
                <td>Server seat</td>
                <td>$0/yr.</td>
            </tr>
            </tbody>
        </table>

        <div class="helper">
            E.g. You have 50 users. The price will be 50 * 43.99 = $ 2199.5/year.
            <br />
            <h2 class="centered">Support:</h2>

            Standard support via e-mail included by default in Self-hosted Business edition, if you need extended support (Teamviewer, phone calls, personal consultant) and shorter reaction time you may order it.
            <br />
            <table class="pricing-table pricing-table-saas2">
                <thead>
                <tr>
                    <th></th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>TV</th>
                    <th>Price</th>
                </tr>
                <tbody>
                <tr>
                    <td>Standard support<br />8×5 with 72h reaction time</td>
                    <td><span class="yes">Yes</span></td>
                    <td>---</td>
                    <td>---</td>
                    <td>---</td>
                </tr>
                <tr>
                    <td>Extended support<br />8×5 with 4h reaction time</td>
                    <td><span class="yes">Yes</span></td>
                    <td><span class="yes">Yes</span></td>
                    <td><span class="yes">Yes</span></td>
                    <td>$ 2300/yr.</td>
                </tr>
                <tr>
                    <td>Extended support<br />24×7 with 2h reaction time</td>
                    <td><span class="yes">Yes</span></td>
                    <td><span class="yes">Yes</span></td>
                    <td><span class="yes">Yes</span></td>
                    <td>on request</td>
                </tr>
                </tbody>
            </table>
            Support time: UTC +01:00

            <h2 class="centered">Steps to order:</h2>

            To order Self-hosted Business edition contact us via a form located below, We'll send you an installation packet for your server with desired licenses seats and 14 days (ask for more if you want) trial period. When you are ready to buy ask us to send invoice manually please.
            <br /><br />
            To download Self-hosted version free just click <a href="<?= Yii::getAlias('@selfHostedWeb') ?>">this link</a>. You'll be able to download and setup infrastructure by yourself on your server.
        </div>

        <table class="pricing-table pricing-table-saas2">
            <thead>
            <tr>
                <th></th>
                <th>Self-hosted Free</th>
                <th>Self-hosted Business</th>
            </tr>
            </thead>
            <tbody>
            <tr><td>Open source</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Extra fast</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>End-2-end encryption</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Secure links</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Cross-device file transfer</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Available on all platforms</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Network fault tolerance feature</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Multi-platform File Sync</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Unlimited devices</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Unlimited data transfer</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Version Control</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>LAN syncing</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Automatic & smart sync</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Subfolder smart sync</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Folder sharing</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>File recovery and version history</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Using "torrent" technology<br />for content delivery</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Downloading only changed parts of the file</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Control Bandwidth Usage</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Secure collaboration</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Real time permission changes</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Password-protected links</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Links with self-destruction timer</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Remote wipe</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Admin panel</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Online Preview (Jpg/Pdf/Doc/Excel/Video files)</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Number of users</td><td><span class="unlimited">Unlimited</span></td><td><span class="unlimited">Unlimited</span></td></tr>
            <tr><td>Server OS support</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Secure file server</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Full team member control</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Management of all members permissions</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Detailed event log</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Detailed activity log</td><td><span class="yes">Yes</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Priority email support</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Personal consultant via phone</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Remote support via TeamViewer</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Integrity consultant</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Branding</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Early updates</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Outlook plugin</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Collabora Office</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
            <tr><td>Libre Office</td><td><span class="no">No</span></td><td><span class="yes">Yes</span></td></tr>
            <tr class="hide-less-than-769">
                <td>&nbsp;</td>
                <td>
                    <a class="get-app-btn btn primary-btn sm-btn" href="<?= Yii::getAlias('@selfHostedWeb') ?>">Download</a>
                </td>
                <td>
                    <a class="get-app-btn btn primary-btn sm-btn js-scroll-to" href="#feedback-form">Submit request</a>
                </td>
            </tr>
            </tbody>
        </table>

    </div>

    <!-- COMMON TEXT FOR SAAS AND SELF WHEN IT SELECTED BY RADIO -->
    <div class="common-saas-self-text">
        <div class="helper">
            <p>The following services are optional and are not included in the basic Pvtbox Business subscription, you can order them separately:</p>

            <p class="p-bold">Integration of office suite Collabora Online Office and Libre Office.</p>
            <p>Collabora Online Office and Libre Office are popular office file editors with functionality similar to one of Google Docs. They allow you to edit files online for members of your team and / or your clients. Of course, entirely on your server and under your control. They are safe and reliable alternatives to Google Docs. Price: from 18 $ / user / year</p>

            <p class="p-bold">Outlook plugin</p>
            <p>You can send files to your Pvtbox private cloud directly from your Microsoft Outlook email program, thus saving mail traffic and adding extra convenience when used by your users. Price: from 6 $ / user / year</p>

            <p class="p-bold">Branding</p>
            <p>This service can be useful to those organizations which want to use their corporate identity in applications (Mobile and Desktop), as well as have the ability to pre-configure clients so that your users or system administrator do not waste time on setting up. Price: from 7000 $ / year</p>

            <p class="p-bold">Integrity consultant</p>
            <p>Integration consultant assistance. Takes a full business day. Our engineer will be in a telephone mode, as well as through Team viewer and any other communication channels to assist with the installation and deployment of Pvtbox private cloud to your organization. You will only contact highly qualified engineers who know their job and will set everything up in the most optimal way. Price: from $ 990 / One time.</p>

        </div>
    </div>

    <!-- FEEDBACK FORM-->
    <div class="pricing-feedback" id="feedback-form">
        <picture>
            <source srcset="/assets/v20190812-min/images/pricing-feedback.png" media="(max-width: 840px)"><img src="/assets/v20190812-min/images/pricing-feedback.png" alt="smart" />
        </picture>

        <div class="feedback-form clearfix">
            <h2 class="centered">Fill the form below. It will help us to understand your needs better and offer a solution suited precisely for your needs.</h2>

            <!--
            <div class="support-men">
                <img src="/assets/v20190812-min/images/support-men.png">
            </div>
            -->
            <div class="support-form-">
                <?php
                $model = new PricingFeedbackForm();
                $reCaptchaPublicKey = Preferences::getValueByKey('reCaptchaPublicKey');
                $cnt = Yii::$app->cache->get(Yii::$app->params['ContactCacheKey']);
                if (!$cnt) {
                    $cnt = 1;
                    Yii::$app->cache->set(Yii::$app->params['ContactCacheKey'], $cnt);
                }
                if (!$reCaptchaPublicKey) {
                    $cnt = 1;
                }

                $form = ActiveForm::begin([
                    'id' => 'form-pricing-feedback',
                    'action'  => Url::to(["/site/pricing-feedback"], CREATE_ABSOLUTE_URL),
                    'options' => [
                        'class'    => "form-box active img-progress-form",
                    ],
                    'enableClientValidation' => true,
                    'validateOnSubmit' => true,
                ]);
                ?>

                <div class="fl-row">
                    <div class="fl-left">
                    <?= $form->field($model, 'name')
                            ->textInput([
                                'placeholder' => $model->getAttributeLabel('name'),
                                'autocomplete' => "off",
                                'aria-label'   => $model->getAttributeLabel('name'),
                            ])
                            ->label(false) ?>
                    </div>
                    <div class="fl-right">
                    <?= $form->field($model, 'organization')
                        ->textInput([
                            'placeholder' => $model->getAttributeLabel('organization'),
                            'autocomplete' => "off",
                            'aria-label'   => $model->getAttributeLabel('organization'),
                        ])
                        ->label(false) ?>
                    </div>
                </div>
                <div class="fl-row">
                    <div class="fl-left">
                    <?= $form->field($model, 'email')
                            ->textInput([
                                'placeholder' => $model->getAttributeLabel('email'),
                                'autocomplete' => "off",
                                'aria-label'   => $model->getAttributeLabel('email'),
                            ])
                            ->label(false) ?>
                    </div>
                    <div class="fl-right">
                    <?= $form->field($model, 'phone')
                        ->textInput([
                            'placeholder' => $model->getAttributeLabel('phone'),
                            'autocomplete' => "off",
                            'aria-label'   => $model->getAttributeLabel('phone'),
                        ])
                        ->label(false) ?>
                    </div>
                </div>
                <div class="fl-center">
                    <?= $form->field($model, 'count_users')
                        ->textInput([
                            'placeholder' => $model->getAttributeLabel('count_users'),
                            'autocomplete' => "off",
                            'aria-label'   => $model->getAttributeLabel('count_users'),
                            'class' => 'small',
                        ])
                        ->label(false) ?>

                    <?=
                    $form->field($model, 'body')
                        ->textArea([
                            'rows' => 6,
                            'placeholder' => $model->getAttributeLabel('body'),
                            'aria-label'  => $model->getAttributeLabel('body'),
                            'style' => "text-align: left;",
                        ])
                        ->label(false)
                    ?>

                    <div id="contact-captcha-container" class="captcha-container">
                        <?php
                        if (Yii::$app->user->isGuest) {
                            if ($cnt > Preferences::getValueByKey('ContactCountNoCaptcha', 1, 'int')) {

                                echo $form->field($model, 'reCaptchaSupport')
                                    ->widget(\himiklab\yii2\recaptcha\ReCaptcha::className(), ['siteKey' => Preferences::getValueByKey('reCaptchaPublicKey')])
                                    ->label(false);

                            }
                        }
                        ?>
                    </div>

                    <input type="submit" name="contact-button" value="<?= Yii::t('forms/pricing-feedback-form', 'Submit request') ?>" class="btn primary-btn support-frm__submit -wide-btn" />
                    <div class="img-progress" title="loading..."></div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>

        </div>
    </div>

    <!-- PAY CARD IMAGES -->
    <div class="payments">
        <div><?= Yii::t('app/pricing', 'We_accept') ?></div>
        <ul class="payments-list">
            <li class="payment-item"><img src="/assets/v20190812-min/images/payments/visa.svg" alt=""></li>
            <li class="payment-item"><img src="/assets/v20190812-min/images/payments/mastercard.svg" alt=""></li>
            <li class="payment-item"><img src="/assets/v20190812-min/images/payments/maestro.svg" alt=""></li>
            <li class="payment-item"><img src="/assets/v20190812-min/images/payments/american-express.svg" alt=""></li>
            <li class="payment-item"><img src="/assets/v20190812-min/images/payments/paypal.svg" alt=""></li>
        </ul>
    </div>

    <!-- WARRANTY IMAGES -->
    <div class="warranty">
        <ul class="warranty-list">
            <li class="warranty-item"><img src="/assets/v20190812-min/images/comodo_secure_100x85.png" alt=""></li>
            <li class="warranty-item"><img src="/assets/v20190812-min/images/award2.jpg" alt=""></li>
            <li class="warranty-item"><img src="/assets/v20190812-min/images/award3.jpg" alt=""></li>
        </ul>
        <div class="warranty__footer">
            <p><?= Yii::t('app/pricing', 'We_guarantee_100') ?></p>
        </div>
    </div>

</div>
