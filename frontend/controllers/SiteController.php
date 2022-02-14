<?php
namespace frontend\controllers;

use Yii;
use yii\web\Response;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\base\ViewNotFoundException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use common\models\Users;
use common\models\Licenses;
use common\models\Software;
use common\models\UserColleagues;
use frontend\components\SController;
use frontend\models\forms\SupportForm;
use frontend\models\PaypalPaysCheck;
use frontend\models\forms\LoginForm;
use frontend\models\forms\SignupForm;
use frontend\models\forms\SignupForm2;
use frontend\models\forms\PurchaseForm;
use frontend\models\forms\SelfHostUserForm;
use frontend\models\forms\PricingFeedbackForm;
use frontend\models\search\TrafSearch;

/**
 * Site controller
 *
 * @property \frontend\models\forms\LoginForm $model_login
 * @property \frontend\models\forms\SupportForm $model_contact
 */
class SiteController extends SController
{
    /** @var \frontend\models\forms\LoginForm $model_login */
    public $model_login;
    /** @var \frontend\models\forms\SupportForm $model_contact */
    public $model_contact;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->model_login  = new LoginForm();
        $this->model_contact = new SupportForm();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [

                    /* Акшены которые доступны всем */
                    [
                        'actions' => [
                            'error',
                            'system-fault',
                            'maintenance',
                            'status',
                            'index',
                            'support',
                            'static',
                            'pricing-feedback',
                            'get-traf-info',
                        ],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],

                    /* Акшены которые доступны только неавторизованым (гостям) */
                    [
                        'actions' => [
                            'entrance',
                            'login',
                            'signup'
                        ],
                        'allow' => true,
                        'roles' => ['?'],
                    ],

                    /* Акшены которые доступны только авторизованным и имеют дополнительные условия доступа */
                    [
                        'actions' => [
                            'purchase',
                            'set-license-type',
                            'set-renewal',
                        ],
                        'allow' => true,
                        'matchCallback' => function($rule, $action) {
                            if (Yii::$app->params['self_hosted']) {
                                return false;
                            } else {
                                return true;
                            }
                        },
                        'roles' => ['@'],
                    ],

                ],

                /* функция которая обработает запрет на доступ к акшену (если не указать будет использована стандартная) */
                'denyCallback' => function($rule, $action) {
                    if ($this->User) {
                        return $this->redirect(['/user/profile']);
                    } else {
                        return $this->redirect(['/']);
                        //throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
                    }
                },
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        Yii::$app->session->remove('after_signup_login_redirect_to');

        return parent::beforeAction($action);
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }




    /** Акшены которые доступны всем */
    /**
     * Special page for display that site is under maintenance
     */
    public function actionSystemFault()
    {
        $error_text = Yii::$app->session->get('system-fault-error', null);
        if ($error_text) {
            $this->layout = 'maintenance';
            Yii::$app->session->setFlash('error-system-fault', [
                'message' => Yii::t('app/flash-messages', $error_text),
                'type' => 'error',
                'ttl' => 0,
                'showClose' => false,
            ]);
            Yii::$app->session->set('system-fault-error', null);
            return $this->render('maintenance');
        } else {
            return $this->redirect(['/']);
        }
    }

    /**
     * Special page for display that site is under maintenance
     */
    public function actionMaintenance()
    {
        $this->layout = 'maintenance';
        return $this->render('maintenance');
    }

    /**
     * Display code_error.
     * Created for redirecting from pages for download shared files, when received code from signal or proxy
     * @return string
     */
    public function actionStatus()
    {
        return $this->render('status', [
            'code_error' => Yii::$app->request->get('code_error'),
        ]);
    }

    /**
     * Displays homepage.
     * if user are logged into member than will be redirected to /user/files
     * @return mixed
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->render('index', [
                'model_signup2' => new SignupForm2(),
                'software' => Software::findOtherVersionSoftware(),
                'traf' => Yii::$app->params['self_hosted'] ? null: TrafSearch::search(),
            ]);
        } else {
            return $this->redirect(['/user/files']);
        }
    }

    /**
     * Displays contact form page.
     * @return string|Response
     * @throws BadRequestHttpException
     */
    public function actionSupport()
    {
        if ($this->model_contact->load(Yii::$app->request->post())) {

            if ($this->model_contact->validate()) {

                $cnt = Yii::$app->cache->get(Yii::$app->params['ContactCacheKey']);
                if (!$cnt) {
                    $cnt = 1;
                } else {
                    $cnt++;
                }
                Yii::$app->cache->set(Yii::$app->params['ContactCacheKey'], $cnt);

                if ($this->model_contact->sendEmail()) {
                    Yii::$app->session->setFlash('success', [
                        'message' => Yii::t('app/flash-messages', 'Support_success'),
                        'ttl' => FLASH_MESSAGES_TTL,
                        'showClose' => true,
                        'alert_action' => 'actionSupport',
                    ]);
                } else {
                    Yii::$app->session->setFlash('error', [
                        'message' => Yii::t('app/flash-messages', 'Support_error'),
                        'ttl' => FLASH_MESSAGES_TTL,
                        'showClose' => true,
                        'alert_action' => 'actionSupport',
                    ]);
                }

                return $this->refresh();

            } else {

                throw new BadRequestHttpException('Wrong form data');

            }

        } else {

            $try = Yii::$app->request->get('try');
            if ($try == 'business-admin-functionality' && $this->User && $this->User->license_type == Licenses::TYPE_FREE_TRIAL) {
                $this->model_contact->subject = SupportForm::SUBJECT_LICENSES;
                $this->model_contact->body = Yii::t('app/support', 'want_try_business_admin');
            }
            return $this->render('support', [
                'model' => $this->model_contact,
            ]);

        }
    }

    /**
     * Этот акшен обработает страницы которые описаны в правилах
     * реврайта конфига frontend/config/main.php  urlManager/rules
     * в случае если имеется для них виевка
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionStatic()
    {
        $action = Yii::$app->request->get('action');
        $no_header = (bool) Yii::$app->request->get('header-free', 0);
        //var_dump($action);exit;
        if ($action == 'pricing' && $this->User && $this->User->has_personal_seller) {
            $action = 'personal-seller';
        }
        if ($action == 'pricing') {
            $software = Software::findOtherVersionSoftware();
        } else {
            $software = null;
        }
        if (file_exists(Yii::getAlias('@frontend').'/themes/' . DESIGN_THEME . '/site/static/' . $action . '.php')) {
            if ($no_header && file_exists(Yii::getAlias('@frontend').'/themes/' . DESIGN_THEME . '/layouts/main_no_header_no_footer.php')) {
                $this->layout = 'main_no_header_no_footer';
            }
            return $this->render('static/' . $action, [
                'User' => Yii::$app->user->isGuest ? null : Yii::$app->user->identity,
                'data' => 'null',
                'software' => $software,
            ]);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Отправка данных из формы на странице pricing
     * @return Response
     */
    public function actionPricingFeedback()
    {
        $model = new PricingFeedbackForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $cnt = Yii::$app->cache->get(Yii::$app->params['ContactCacheKey']);
            if (!$cnt) {
                $cnt = 1;
            } else {
                $cnt++;
            }
            Yii::$app->cache->set(Yii::$app->params['ContactCacheKey'], $cnt);

            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', [
                    'message' => Yii::t('app/flash-messages', 'pricing_feedback_success'),
                    'ttl' => FLASH_MESSAGES_TTL,
                    'showClose' => true,
                ]);
            } else {
                Yii::$app->session->setFlash('error', [
                    'message' => Yii::t('app/flash-messages', 'pricing_feedback_error'),
                    'ttl' => FLASH_MESSAGES_TTL,
                    'showClose' => true,
                ]);
            }

        }

        return $this->redirect(['/pricing']);
    }

    /**
     * Получение данных по трафику для счетчиков на главной
     * @return array
     */
    public function actionGetTrafInfo()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $res = TrafSearch::search();
        if (is_array($res)) {
            return [
                'result' => "success",
                'data' => $res,
            ];
        } else {
            return [
                'result' => "error",
            ];
        }
    }




    /** Акшены которые доступны только неавторизованым (гостям) */
    /**
     * Special page for display login form after user logout from member
     * If not view not exist in current design than will be redirect to index
     * @return string
     */
    public function actionEntrance()
    {
        $this->model_login = null;
        try {
            return $this->render('entrance', [
                'form_signup' => new SignupForm(),
                'form_login' => new LoginForm(),
            ]);
        } catch (ViewNotFoundException $e) {
            return $this->redirect(['/']);
        }
    }

    /**
     * Displays homepage.
     * Created specifically for javascript to analyze the address bar and call a popup window with a login form
     * @return mixed
     */
    public function actionLogin()
    {
        return $this->actionIndex();
    }

    /**
     * Displays homepage.
     * Created specifically for javascript to analyze the address bar and call a popup window with a login form
     * @return mixed
     */
    public function actionSignup()
    {
        if (Yii::$app->params['self_hosted']) {
            $colleague_id = intval(Yii::$app->request->get('colleague_id'));
            if (!$colleague_id || !UserColleagues::findOne(['colleague_id' => $colleague_id])) {
                return $this->redirect(['/']);
            }
        }
        return $this->actionIndex();
    }




    /* Акшены которые доступны только авторизованным и имеют дополнительные условия доступа */
    /**
     * Форма заказа и оплаты.
     * Сейчас доступна только для зарегистированных пользователей
     * @return string
     */
    public function actionPurchase()
    {
        if ($this->User->has_personal_seller) {
            return $this->redirect(['/pricing']);
        }

        if (in_array($this->User->payment_already_initialized, [Users::PAYMENT_INITIALIZED, Users::PAYMENT_PROCESSED]) &&
            !in_array($_GET['id'], [PurchaseForm::ID_SUCCESS, PurchaseForm::ID_CANCEL, PurchaseForm::ID_INITIALIZED]))
        {
            return $this->redirect(['/purchase/' . PurchaseForm::ID_INITIALIZED]);
        }

        if (Yii::$app->cache->get('PaymentInitialized_for_UserID_' . $this->User->user_id) &&
            !in_array($_GET['id'], [PurchaseForm::ID_SUCCESS, PurchaseForm::ID_CANCEL, PurchaseForm::ID_INITIALIZED])) {
            return $this->redirect(['/purchase/' . PurchaseForm::ID_INITIALIZED]);
        }

        if ($this->User->license_type == Licenses::TYPE_PAYED_BUSINESS_USER) {
            return $this->redirect(['/user/profile']);
        }

        /**/
        if (!isset($_GET['id'])) { $_GET['id'] = PurchaseForm::ID_BUSINESS; }
        if (!in_array($_GET['id'], [
            PurchaseForm::ID_BUSINESS,
            PurchaseForm::ID_PROFESSIONAL,
            PurchaseForm::ID_SUCCESS,
            PurchaseForm::ID_CANCEL,
            PurchaseForm::ID_SUMMARY,
            PurchaseForm::ID_INITIALIZED,
        ])) { $_GET['id'] = PurchaseForm::ID_SUMMARY; }


        if ($_GET['id'] == PurchaseForm::ID_SUCCESS) {
            //$referrer = (string) Yii::$app->request->referrer;
            //$referrer = 'https://www.paypal.com/cgi-bin/webscr';
            //if (strrpos($referrer, 'paypal')) {
                Yii::$app->cache->set('PaymentInitialized_for_UserID_' . $this->User->user_id, true, 30*60);
            //} else {
            //    return "This page can only be accessed from the PayPal service.";
            //}
            //var_dump(Yii::$app->request->referrer);
            //var_dump(Yii::$app->request->referrer);
            //Yii::$app->cache->set('UserID_' . )
        }

        /**/
        if (!isset($_GET['billed'])) { $_GET['billed'] = Licenses::getBilledByPeriod(Licenses::PERIOD_ANNUALLY); }
        if (!in_array($_GET['billed'], [
            'daily',
            Licenses::getBilledByPeriod(Licenses::PERIOD_ONETIME),
            Licenses::getBilledByPeriod(Licenses::PERIOD_MONTHLY),
            Licenses::getBilledByPeriod(Licenses::PERIOD_ANNUALLY)
        ])) { $_GET['billed'] = Licenses::getBilledByPeriod(Licenses::PERIOD_ANNUALLY); }

        /**/
        if (!isset($_GET['license'])) { $_GET['license'] = PurchaseForm::LICENSE_ID_BUSINESS; }
        if (!in_array($_GET['license'], [
            PurchaseForm::LICENSE_ID_BUSINESS,
            PurchaseForm::LICENSE_ID_PROFESSIONAL,
        ])) {
            $_GET['license'] = PurchaseForm::LICENSE_ID_BUSINESS;
        }

        if ($_GET['license'] == PurchaseForm::LICENSE_ID_PROFESSIONAL) {
            $_GET['billed'] = Licenses::getBilledByPeriod(Licenses::PERIOD_ONETIME);
        }

        if (in_array($_GET['id'], [PurchaseForm::ID_SUMMARY])) {
            /* Не даем возможности юзеру переопределить тип лицензии в случае если лицензия еще оплачена и он пытается снова оплатить */
            /*
            if ($this->User->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN && $_GET['license'] != PurchaseForm::LICENSE_ID_BUSINESS) {
                $_GET['license'] = PurchaseForm::LICENSE_ID_BUSINESS;
                $_GET['billed'] = Licenses::getBilledByPeriod($this->User->license_period);
                $this->redirect(['purchase/summary', 'license' => $_GET['license'], 'billed' => $_GET['billed']]);
            }
            if ($this->User->license_type == Licenses::TYPE_PAYED_PROFESSIONAL && $_GET['license'] != PurchaseForm::LICENSE_ID_PROFESSIONAL) {
                $_GET['license'] = PurchaseForm::LICENSE_ID_PROFESSIONAL;
                $_GET['billed'] = Licenses::getBilledByPeriod($this->User->license_period);
                $this->redirect(['purchase/summary', 'license' => $_GET['license'], 'billed' => $_GET['billed']]);
            }
            */

            /* не даем возможности юзеру переоплеелить период биллинга в случае если еще лицензия оплаченна и он пытается снова оплатить */
            /*
            if ($this->User->license_period == Licenses::PERIOD_ANNUALLY && $_GET['billed'] != Licenses::getBilledByPeriod(Licenses::PERIOD_ANNUALLY)) {
                $_GET['billed'] = Licenses::getBilledByPeriod(Licenses::PERIOD_ANNUALLY);
                $this->redirect(['purchase/summary', 'license' => $_GET['license'], 'billed' => $_GET['billed']]);
            }
            if ($this->User->license_period == Licenses::PERIOD_MONTHLY && $_GET['billed'] != Licenses::getBilledByPeriod(Licenses::PERIOD_MONTHLY)) {
                $_GET['billed'] = Licenses::getBilledByPeriod(Licenses::PERIOD_MONTHLY);
                $this->redirect(['purchase/summary', 'license' => $_GET['license'], 'billed' => $_GET['billed']]);
            }
            */
        }

        return $this->render('purchase_paypal/purchase', [
            'id'      => $_GET['id'],
            'billed'  => $_GET['billed'],
            'license' => $_GET['license'],
            'User'    => $this->User,
            'model'   => new PurchaseForm(['os1', 'os2']),
        ]);
    }

    /**
     * Акшен обработки оплаты
     * @return Response
     */
    public function actionSetLicenseType()
    {
        //$model = New PurchaseForm(['admin_full_name', 'user_company_name']);
        $model = New PurchaseForm();
        /*
        var_dump(Yii::$app->request->post());
        var_dump($model->load(Yii::$app->request->post()));
        var_dump($model->validate());
        exit;
        */
        $ret = false;
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->license_type == Licenses::TYPE_PAYED_PROFESSIONAL) {
                if ($model->checkAllowProfessional()) {
                    $ret = $model->purchaseProfessional();
                }
            } else {
                if ($model->checkAllowBusiness()) {
                    $ret = $model->purchaseBusiness();
                }
            }
        }

        if (isset($ret['status']) && $ret['status'] !== false) {
            return $this->redirect($ret['url']);
        }

        //var_dump($model->getErrors()); exit;
        Yii::$app->session->setFlash('error', [
            'message'   => Yii::t('app/flash-messages', $ret['info']),
            'ttl'       => FLASH_MESSAGES_TTL,
            'showClose' => true,
            'alert_action' => 'actionSetLicenseType',
        ]);
        return $this->redirect(['/']);
    }

    /**
     * Акшен для повторных оплат
     */
    public function actionSetRenewal()
    {
        /** @var \common\models\Users $User */
        $User = Yii::$app->user->identity;
        if (!in_array($User->license_type, [Licenses::TYPE_PAYED_PROFESSIONAL, Licenses::TYPE_PAYED_BUSINESS_ADMIN])) {
            return $this->redirect(['/']);
        }

        $model = New PurchaseForm();
        $model->load(Yii::$app->request->post());

        if ($model->admin_full_name && $model->user_company_name && !$model->validate(['admin_full_name', 'user_company_name'])) {
            return $this->redirect('purchase/renewal');
        }

        $ret = $model->renewal();
        if ($ret['status'] !== false) {
            return $this->redirect($ret['url']);
        }

        Yii::$app->session->setFlash('error', [
            'message'   => Yii::t('app/flash-messages', $ret['info']),
            'ttl'       => FLASH_MESSAGES_TTL,
            'showClose' => true,
            'alert_action' => 'actionSetRenewal',
        ]);
        return $this->redirect(['/']);
    }




    /** ********************** DEPRECATED ACTIONS ********************** */
    /**
     * Displays SHU page for create new account.
     * (depricated cause created this page on sh-domain)
     * @return string|Response
     * @throws BadRequestHttpException
     */
    public function actionSelfHosted()
    {

        $model = new SelfHostUserForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->User) {
            $model->shu_email = $this->User->user_email;
        }

        if ($model->load(Yii::$app->request->post())) {

            if ($model->validate()) {

                $cnt = Yii::$app->cache->get(Yii::$app->params['ShuCacheKey']);
                if (!$cnt) {
                    $cnt = 1;
                } else {
                    $cnt++;
                }
                Yii::$app->cache->set(Yii::$app->params['ShuCacheKey'], $cnt);

                $ret = $model->signup();
                //var_dump($ret['user']); exit;
                if ($ret['user']) {

                    return $this->redirect(Yii::getAlias('@selfHostedWeb') .
                        '/site/login-by-token?token=' . $ret['user']->password_reset_token .
                        '&free=' . $ret['free']);

                } else {
                    throw new BadRequestHttpException('Wrong form data2');
                }

            } else {
                throw new BadRequestHttpException('Wrong form data');
            }

        } else {
            return $this->render('self-hosted', [
                'model' => $model,
                'user' => $this->User,
            ]);
        }
    }

    /**
     * Processing of PayPal pays
     * Deprecated
     * @return mixed
     */
    public function actionPaypal()
    {
        //http://dlink.frontend.home/site/paypal?status=success&paymentId=PAY-266001645D153860DK4RVIHY&token=EC-5XV99650DV184864S&PayerID=uniq
        //http://dlink.frontend.home/site/paypal?status=canceled&token=EC-5XV99650DV184864S

        $model = new PaypalPaysCheck();
        $data[$model->formName()] = Yii::$app->request->get();
        if ($model->load($data)) {
            $model->checkPay();
        }
        return $this->redirect(['balance-info']);
    }
}
