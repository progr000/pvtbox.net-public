<?php
/**
 * File Paypal.php.
 *
 * @author Marcio Camello <marciocamello@outlook.com>
 * @see https://github.com/paypal/rest-api-sdk-php/blob/master/sample/
 * @see https://developer.paypal.com/webapps/developer/applications/accounts
 */

namespace frontend\components;

//define('PP_CONFIG_PATH', __DIR__);

use Yii;
use yii\base\ErrorException;
use yii\helpers\ArrayHelper;
use yii\base\Component;

use PayPal\Api\Address;
use PayPal\Api\CreditCard;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
use PayPal\Api\FundingInstrument;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\RedirectUrls;
use PayPal\Rest\ApiContext;
use PayPal\Transport\PayPalRestCall;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Plan;

use common\models\Preferences;

class PayPal extends Component
{
    //region Mode (production/development)
    const MODE_SANDBOX = 'sandbox';
    const MODE_LIVE    = 'live';
    //endregion

    //region Log levels
    /*
     * Logging level can be one of FINE, INFO, WARN or ERROR.
     * Logging is most verbose in the 'FINE' level and decreases as you proceed towards ERROR.
     */
    const LOG_LEVEL_FINE  = 'FINE';
    const LOG_LEVEL_INFO  = 'INFO';
    const LOG_LEVEL_WARN  = 'WARN';
    const LOG_LEVEL_ERROR = 'ERROR';
    //endregion

    //region API settings
    public $clientId;
    public $clientSecret;
    public $isProduction = false;
    public $currency = 'USD';
    public $config = [];

    /** @var ApiContext */
    private $_apiContext = null;

    /**
     * @setConfig 
     * _apiContext in init() method
     */
    public function init()
    {
        $this->setConfig();
    }

    /**
     * @inheritdoc
     */
    private function setConfig()
    {
        // ### Api context
        // Use an ApiContext object to authenticate
        // API calls. The clientId and clientSecret for the
        // OAuthTokenCredential class can be retrieved from
        // developer.paypal.com

        $this->_apiContext = new ApiContext(
            new OAuthTokenCredential(
                $this->clientId,
                $this->clientSecret
            )
        );

        // #### SDK configuration

        // Comment this line out and uncomment the PP_CONFIG_PATH
        // 'define' block if you want to use static file
        // based configuration
        $this->_apiContext->setConfig(ArrayHelper::merge(
            [
                'mode'                      => self::MODE_SANDBOX, // development (sandbox) or production (live) mode
                'http.ConnectionTimeOut'    => 30,
                'http.Retry'                => 1,
                'log.LogEnabled'            => YII_DEBUG ? 1 : 0,
                'log.FileName'              => Yii::getAlias('@runtime/logs/paypal.log'),
                'log.LogLevel'              => self::LOG_LEVEL_FINE,
                'validation.level'          => 'log',
                'cache.enabled'             => 'true'
            ],$this->config)
        );
        //var_dump($this->config); exit;

        // Set file name of the log if present
        if (isset($this->config['log.FileName'])
            && isset($this->config['log.LogEnabled'])
            && ((bool)$this->config['log.LogEnabled'] == true)
        ) {
            $logFileName = \Yii::getAlias($this->config['log.FileName']);

            if ($logFileName) {
                if (!file_exists($logFileName)) {
                    if (!touch($logFileName)) {
                        throw new ErrorException('Can\'t create paypal.log file at: ' . $logFileName);
                    }
                }
            }

            $this->config['log.FileName'] = $logFileName;
        }

        return $this->_apiContext;
    }

    //Demo
    public function payDemo()
    {
        set_time_limit(120);

        $addr = new Address();
        $addr->setLine1('52 N Main ST');
        $addr->setCity('Johnstown');
        $addr->setCountryCode('US');
        $addr->setPostalCode('43210');
        $addr->setState('OH');

        $card = new CreditCard();
        $card->setNumber('4417119669820331');
        $card->setType('visa');
        $card->setExpireMonth('11');
        $card->setExpireYear('2018');
        $card->setCvv2('874');
        $card->setFirstName('Joe');
        $card->setLastName('Shopper');
        $card->setBillingAddress($addr);

        $fi = new FundingInstrument();
        $fi->setCreditCard($card);

        $payer = new Payer();
        $payer->setPaymentMethod('credit_card');
        $payer->setFundingInstruments(array($fi));

        $amountDetails = new Details();
        $amountDetails->setSubtotal('7.41');
        $amountDetails->setTax('0.03');
        $amountDetails->setShipping('0.03');

        $amount = new Amount();
        $amount->setCurrency('USD');
        $amount->setTotal('7.47');
        $amount->setDetails($amountDetails);

        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDescription('This is the payment transaction description.');

        $payment = new Payment();
        $payment->setIntent('sale');
        $payment->setPayer($payer);
        $payment->setTransactions(array($transaction));

        return $payment->create($this->_apiContext);
    }

    /**
     * Создаем платеж типа paypal
     * в случае успеха возвращает массив с ид-платежа,
     * токеном и редирект-урлом куда нужно направить пользователя для оплаты
     *
     * @param double $pay_sum
     * @param string $paymentInfo
     * @param string $sku - internal UNIT ID
     *
     * @return array | null
     */
    public function payThroughPayPal($pay_sum, $paymentInfo, $sku=null)
    {
        set_time_limit(120);

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $amount = new Amount();
        $amount->setCurrency('USD');
        $amount->setTotal($pay_sum);

        $item1 = new Item();
        $item1->setName($paymentInfo)->setCurrency('USD')->setQuantity(1)->setPrice($pay_sum);
        // Ид товара/услуги на вашей стороне
        if ($sku)
            $item1->setSku($sku);

        $itemList = new ItemList();
        $itemList->setItems([$item1]);

        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDescription('Payment to DirectLink');
        $transaction->setItemList($itemList);
        $transaction->setNotifyUrl($this->config['url.notify_url']); //**

        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl($this->config['url.return_url']);
        $redirect_urls->setCancelUrl($this->config['url.cancel_url']);

        $payment = new Payment();
        $payment->setIntent('sale');
        $payment->setPayer($payer);
        $payment->setTransactions([$transaction]);
        $payment->setRedirectUrls($redirect_urls);
        //$payment->setId('123456789'); //**

        $payment->create($this->_apiContext);

        //var_dump($payment); exit;

        $links = $payment->getLinks();
        foreach ($links as $link) {
            if ($link->getMethod() == 'REDIRECT') {
                $redirect_to = $link->getHref();
                $token = time() . "_" . rand(100, 999);
                $tmp = parse_url($redirect_to);
                if (isset($tmp['query'])) {
                    parse_str($tmp['query'], $out);
                    if (isset($out['token'])) {
                        $token = $out['token'];
                    }
                }
                $paymentId = $payment->getId();
                // ++ DEBUG LOG
                $this->logging_queryes('paymentCreate_' . $paymentId . '.txt', $payment->toJSON());
                // -- DEBUG LOG

                return ['paymentId' => $paymentId, 'token'=>$token, 'redirect_to' => $redirect_to];
            }
        }

        return null;
    }

    /**
     * Подтверждаем платеж после того как пользователь нажал на кнопкку ОПЛАТЫ
     * на странице куда мы его отправили в ф-ии payThroughPayPal
     * После успешной оплаты на странице пейпел происходит редирект
     * на return_url с такими параметрами: paymentId, &token, PayerID
     * (https://return_url/?paymentId=PAY-8D646545FV855315AK4Q7XPY&token=EC-38715081XC2123944&PayerID=HPAGZWLDS7MZQ)
     * эта ф-ия обрабатывает его.
     * Возвращает txn_id
     *
     * @param string $paymentId
     * @param string $payerId
     *
     * @return string | null
     */
    public function payThroughPayPalConfirm($paymentId, $payerId)
    {
        set_time_limit(120);

        $payment = Payment::get($paymentId, $this->_apiContext);
        $paymentExecution= new PaymentExecution();
        $paymentExecution->setPayerId($payerId);
        $payment->execute($paymentExecution, $this->_apiContext);
        //var_dump($payment->getTransactions()); echo "<br />\n\n<br />>\n\n"; var_dump($payment);

        if ($payment) {
            $transactions = $payment->getTransactions();
            $resources = $transactions[0]->getRelatedResources();
            $sale = $resources[0]->getSale();

            // ++ DEBUG LOG
            $this->logging_queryes('paymentExecute_' . $paymentId . '.txt', $payment->toJSON());
            // -- DEBUG LOG

            return $sale->id;
        }

        return null;
    }

    /**
     * Проверяет реально был выполнен запрос к нашему скрипту IPN от сервера пейпел
     * или это какото хакерский запрос.
     *
     * @return bool
     */
    public function checkIPN()
    {
        $postdata = "";
        foreach ($_POST as $key => $value)
            $postdata .= $key . "=" . urlencode($value) . "&";
        $postdata .= "cmd=_notify-validate";

        $restCall = new PayPalRestCall($this->_apiContext);
        $response = $restCall->execute(['PayPal\Handler\RestHandler'], '/cgi-bin/webscr', 'POST', $postdata, []);

        // ++ DEBUG LOG
        $postdata = "";
        foreach ($_POST as $key => $value)
            $postdata .= $key . " = " . urlencode($value) . " \n";

        $this->logging_queryes('ipn_answer.txt', $postdata . "\n================\n RESPONSE = " . $response);
        // -- DEBUG LOG

        if (strtoupper(trim($response)) == 'VERIFIED') {
            return true;
        }

        return false;
    }

    public function checkIpnForPpButton($postdata)
    {
        // https://developer.paypal.com/docs/classic/ipn/integration-guide/IPNImplementation/#specs
        $postdata['cmd'] = "_notify-validate";

        $url = "https://ipnpb.paypal.com/cgi-bin/webscr";

        $headers = ["Accept-Language: en"];
        $ch = curl_init();    // initialize curl handle
        curl_setopt($ch, CURLOPT_URL, $url); // set url to post to
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // times out after 40s
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, "PHP-IPN-VerificationScript");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata); // add POST fields
        $answer = curl_exec($ch);// run the whole process
        curl_close($ch);

        //var_dump($answer); exit;
        if (strtoupper(trim($answer)) == 'VERIFIED') {
            return true;
        }

        return false;
    }

    /**
     * Логирование запросов к ПейПал в отдельные файлы
     *
     * @param string $fname                    Имя файла лога
     * @param string $data                     Данные для записи в лог
     */
    protected function logging_queryes($fname, $data)
    {
        if (isset($this->config['log.LogEnabled'], $this->config['log.LogHttpQueryDir']) && ((bool)$this->config['log.LogEnabled'] == true)) {

            if (!file_exists($this->config['log.LogHttpQueryDir'])) {
                @mkdir($this->config['log.LogHttpQueryDir'], 0777);
                @chmod($this->config['log.LogHttpQueryDir'], 0777);
            }

            $fname = date('Ymd-His') . "_" . $fname;
            file_put_contents($this->config['log.LogHttpQueryDir'].'/'.$fname, $data);
        }
    }

    /**
     * @param string $level
     * @param string $data
     * @param mixed $var_data
     */
    public function logging($level, $data, $var_data=null)
    {
        if (isset($this->config['log.LogExecution'])) {

            $f = fopen($this->config['log.LogExecution'], 'ab');
            fwrite($f, "[" . date('Y-m-d H:i:s') . "] [" . $level . "] "  . $data . "\n");
            if ($var_data !== null) {
                fwrite($f, "[Данные] "  . serialize($var_data) . "\n");
            }
            fflush($f);
            fclose($f);
        }
    }

    /**
     * @param string $data
     */
    public function debugging($data)
    {
        if (isset($this->config['log.Debugging'])) {

            $f = fopen($this->config['log.Debugging'], 'ab');
            fwrite($f, "+++++++++++++++++++++++++++++++++ [" . date('Y-m-d H:i:s') . "] +++++++++++++++++++++++++++++++++ \n");
            fwrite($f, $data . "\n");
            fwrite($f, "--------------------------------- ----------- --------------------------------- \n\n");
            fflush($f);
            fclose($f);
        }
    }

    /**
     * @param string $name
     * @param string $description
     * @param string $cost
     * @param string $currency example USD|EUR|RUR etc.
     * @param string $period allowed YEAR|MONTH
     * @return Plan
     */
    public function createPlan($name, $description, $cost, $currency, $period)
    {
        /** Технические параметры и настройки для плана */
        $MerchantPreferences = new MerchantPreferences();
        //$MerchantPreferences->setSetupFee(0);
        $MerchantPreferences->setAutoBillAmount("NO");
        $MerchantPreferences->setInitialFailAmountAction("CONTINUE");
        $MerchantPreferences->setNotifyUrl($this->config['url.notify_url']);
        $MerchantPreferences->setReturnUrl($this->config['url.return_url']); // Retrieve from config
        $MerchantPreferences->setCancelUrl($this->config['url.cancel_url']); // Retrieve from config
        $MerchantPreferences->setMaxFailAttempts("0");

        /** Определение вариантов оплат для нашего плана */
        /* Проф на месяц */
        $PaymentDefinitionProMonth = new PaymentDefinition();
        $amount = new Currency();
        $amount->setCurrency($currency);
        $amount->setValue($cost/*Preferences::getValueByKey('PricePerMonthForLicenseProfessional')*/);
        $PaymentDefinitionProMonth->setAmount($amount);
        $PaymentDefinitionProMonth->setType("REGULAR");
        $PaymentDefinitionProMonth->setName($name/*"ProMonth"*/);
        $PaymentDefinitionProMonth->setFrequencyInterval("1");
        $PaymentDefinitionProMonth->setFrequency($period/*"MONTH"*/);
        //$PaymentDefinitionProMonth->setCycles("12"); // If payment definition type is REGULAR, cycles can only be null or 0
        //$PaymentDefinitionProMonth->setChargeModels()


        $PaymentDefinitions[] = $PaymentDefinitionProMonth;
        //$restCall = new PayPalRestCall($this->_apiContext);

        /** Создание плана */
        $Plan = new Plan();
        $Plan->setName($name);
        $Plan->setDescription($description);
        $Plan->setType("INFINITE");
        $Plan->setMerchantPreferences($MerchantPreferences);
        $Plan->setPaymentDefinitions($PaymentDefinitions);
        $returnPlan = $Plan->create($this->_apiContext);

        return $returnPlan;
    }
}
