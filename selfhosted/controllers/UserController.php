<?php

namespace selfhosted\controllers;

use Yii;
use yii\base\DynamicModel;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use common\models\Sessions;
use common\models\SelfHostUsers;
use common\models\MailTemplatesStatic;
use selfhosted\components\SController;
use selfhosted\models\forms\ChangePasswordForm;
use selfhosted\models\forms\SetTimeZoneOffsetForm;

/**
 * UserController
 */
class UserController extends SController
{
    public $model_login;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),

                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'confirm-registration',
                            'register-alert-data',
                        ],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => [
                            'index',
                            'logout',
                            'profile',
                            'resend-confirm',
                            'confirm-registration',
                            'change-password',
                            'delete-account',
                            'request-support',
                            'request-brand',
                            'register-alert-data',
                            'resend-download',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post', 'get'],
                    'delete-account' => ['post'],
                ],
            ],
        ];
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
        ];
    }

    /**
     * @return array
     */
    public function actionRegisterAlertData()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        //$model = new LogAlertDataForm();
        //if ($model->load(['LogAlertDataForm' => $_POST]) && $model->validate()) {
        //    return $model->saveAlertData($this->User);
        //}

        return [
            'status' => true,
            //'info'   => $model->getErrors(),
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        if ($this->SelfHostUser) {
            return $this->actionProfile();
        } else {
            return $this->redirect(['site/login']);
        }
    }

    /**
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->redirect(['/']);
    }

    /**
     * Displays profile page.
     *
     * @return mixed
     */
    public function actionProfile()
    {
        /** @var \common\models\SelfHostUsers $User */
        $User = SelfHostUsers::findIdentity($this->SelfHostUser->getId());

        /** Смена пароля */
        $model_changepassword = new ChangePasswordForm();
        if (isset($_POST['ChangePasswordStep1'])) {
            if ($model_changepassword->changePasswordStep1()) {
                Yii::$app->getSession()->setFlash('success', [
                    'message'   => Yii::t('app/flash-messages', 'Profile_ChangePasswordForm_success'),
                    'ttl'       => FLASH_MESSAGES_TTL,
                    'showClose' => true,
                    'alert_action' => 'actionProfile.ChangePasswordForm',
                ]);
            } else {
                Yii::$app->getSession()->setFlash('error', [
                    'message'   => Yii::t('app/flash-messages', 'Profile_ChangePasswordForm_error'),
                    'ttl'       => FLASH_MESSAGES_TTL,
                    'showClose' => true,
                    'alert_action' => 'actionProfile.ChangePasswordForm',
                ]);
            }
            return $this->redirect(['profile']);
        }

        /** Смена таймзоны */
        $model_changetimezone = new SetTimeZoneOffsetForm();
        $model_changetimezone->timezone_offset_seconds = $User->static_timezone;
        //var_dump($model_changetimezone->timezone_offset_seconds);
        if ($model_changetimezone->load(Yii::$app->request->post()) && $model_changetimezone->validate()) {
            if ($model_changetimezone->setStaticTimeZone($User)) {
                Yii::$app->getSession()->setFlash('success', [
                    'message'   => Yii::t('app/flash-messages', 'Profile_ChangeTimeZone_success'),
                    'ttl'       => FLASH_MESSAGES_TTL,
                    'showClose' => true,
                    'alert_action' => 'actionProfile.SetTimeZoneOffsetForm',
                ]);
            } else {
                Yii::$app->getSession()->setFlash('error', [
                    'message'   => Yii::t('app/flash-messages', 'Profile_ChangeTimeZone_error'),
                    'ttl'       => FLASH_MESSAGES_TTL,
                    'showClose' => true,
                    'alert_action' => 'actionProfile.SetTimeZoneOffsetForm',
                ]);
            }
            return $this->redirect(['profile']);
        }

        /** Вывод страницы настроек профиля */
        return $this->render('profile', [
            'model_changepassword' => $model_changepassword,
            //'model_changename'     => $model_changename,
            'model_changetimezone' => $model_changetimezone,
            //'searchModelPayments'  => $searchModelPayments,
            //'dataProviderPayments' => $dataProviderPayments,
        ]);
    }

    /**
     * @return Response
     */
    public function actionResendConfirm()
    {
        /** @var \common\models\SelfHostUsers $User */
        $User = SelfHostUsers::findIdentity($this->SelfHostUser->getId());

        $cache_key = 'shu-lock-resend-confirm-' . $User->shu_id;

        if (!Yii::$app->cache->get($cache_key)) {
            Yii::$app->cache->set($cache_key, time(), Yii::$app->params['timeout_resend_confirm']);

            if ($User->shu_status == SelfHostUsers::STATUS_ACTIVE) {

                $User->generatePasswordResetToken();
                if ($User->save() && MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_newShuRegister, $User->shu_email, [
                        'user_name' => $User->shu_name,
                        'user_email' => $User->shu_email,
                        'confirm_registration_link' => Yii::$app->urlManager->createAbsoluteUrl(['user/confirm-registration', 'token' => $User->password_reset_token]),
                    ])
                ) {
                    Yii::$app->session->setFlash('success', [
                        'message' => Yii::t('app/flash-messages', 'ResendConfirm_success'),
                        'ttl' => FLASH_MESSAGES_TTL,
                        'showClose' => true,
                        'alert_action' => 'actionResendConfirm',
                    ]);
                } else {
                    Yii::$app->session->setFlash('error', [
                        'message' => Yii::t('app/flash-messages', 'ResendConfirm_error'),
                        'ttl' => FLASH_MESSAGES_TTL,
                        'showClose' => true,
                        'alert_action' => 'actionResendConfirm',
                    ]);
                }

            }
        } else {
            Yii::$app->session->setFlash('error', [
                'message' => Yii::t('app/flash-messages', 'ResendConfirm_wait_error'),
                'ttl' => FLASH_MESSAGES_TTL,
                'showClose' => true,
                'alert_action' => 'actionResendConfirm',
            ]);
        }
        return $this->goBack((!empty(Yii::$app->request->referrer)
            ? Yii::$app->request->referrer
            : null
        ));
        //return $this->goBack();
    }

    /**
     * Display Confirm registration page
     * @return mixed
     */
    public function actionConfirmRegistration()
    {
        if (!Yii::$app->user->isGuest) {
            /** @var \common\models\SelfHostUsers $testUser */
            $testUser = Yii::$app->user->identity;
            if ($testUser->shu_status == SelfHostUsers::STATUS_CONFIRMED) {
                Yii::$app->session->setFlash('error', [
                    'message'   => Yii::t('app/flash-messages', 'ConfirmRegistrationAlreadyExist'),
                    'ttl'       => FLASH_MESSAGES_TTL,
                    'showClose' => true,
                    'alert_action' => 'actionConfirmRegistration',
                ]);
                return $this->redirect(['user/profile']);
            }
        }

        $model = new DynamicModel(['token']);
        $model->addRule('token', 'required');
        $model->addRule('token', 'string', ['min'=>6, 'max'=>255]);
        if ($model->load(['ConfirmRegistration' => $_GET], 'ConfirmRegistration') && $model->validate()) {
            $user = SelfHostUsers::findByPasswordResetToken($model->token, true);
            //var_dump($user); exit;
            if ($user) {
                $user->shu_status = SelfHostUsers::STATUS_CONFIRMED;
                $user->removePasswordResetToken();
                if ($user->save()) {
                    Yii::$app->session->setFlash('success', [
                        'message'   => Yii::t('app/flash-messages', 'ConfirmRegistration_success'),
                        'ttl'       => FLASH_MESSAGES_TTL,
                        'showClose' => true,
                        'alert_action' => 'actionConfirmRegistration',
                    ]);

                    if (Yii::$app->user->isGuest) {
                        return $this->redirect(['/']);
                    } else {
                        return $this->redirect(['user/profile']);
                    }
                }
            }
        }
        Yii::$app->session->setFlash('error', [
            'message'   => Yii::t('app/flash-messages', 'ConfirmRegistration_error'),
            'ttl'       => FLASH_MESSAGES_TTL,
            'showClose' => true,
            'alert_action' => 'actionConfirmRegistration',
        ]);

        return $this->goHome();
    }

    /**
     * Change password form
     * @return mixed
     */
    public function actionChangePassword()
    {
        $model = new ChangePasswordForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->changePasswordStep2()) {
                if ($user)
                    Yii::$app->session->setFlash('success', [
                        'message'   => Yii::t('app/flash-messages', 'ChangePassword_success'),
                        'ttl'       => FLASH_MESSAGES_TTL,
                        'showClose' => true,
                        'alert_action' => 'actionChangePassword',
                    ]);
                else
                    Yii::$app->session->setFlash('success', [
                        'message'   => Yii::t('app/flash-messages', 'ChangePassword_error'),
                        'ttl'       => FLASH_MESSAGES_TTL,
                        'showClose' => true,
                        'alert_action' => 'actionChangePassword',
                    ]);

                return $this->redirect(['profile']);
            }
        }

        if ($model->findChangeToken(Yii::$app->request->get('token'))) {
            return $this->render('change-password', [
                'model' => $model,
            ]);
        }

        return $this->redirect(['profile']);
    }

    /**
     * @throws \yii\db\Exception
     */
    public function actionDeleteAccount()
    {
        $transaction = Yii::$app->db->beginTransaction();

        $shu_id = $this->SelfHostUser->shu_id;
        if ($shu_id) {
            $session = new Sessions();
            $session->user_id = $shu_id;
            $session->sess_action = Sessions::ACTION_DELETE_SHU;
            $session->save();
        }

        $this->SelfHostUser->markUserAsDeleted();
        if ($this->SelfHostUser->save()) {

            $transaction->commit();

            Yii::$app->user->logout();

            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => true,
                'redirect' => Yii::$app->urlManager->createAbsoluteUrl(['/']),
            ];

        } else {

            $transaction->rollBack();
            return [
                'status' => false,
                'redirect' => Yii::$app->urlManager->createAbsoluteUrl(['/']),
            ];

        }
    }

    /**
     * @return Response
     */
    public function actionRequestSupport()
    {
        if (!$this->SelfHostUser->shu_support_requested) {
            $this->SelfHostUser->shu_support_requested = SelfHostUsers::YES;
            $this->SelfHostUser->requestSupportOrBrand();
            $this->SelfHostUser->save();

            Yii::$app->session->setFlash('success', [
                'message'   => Yii::t('app/flash-messages', 'Request_was_sent'),
                'ttl'       => FLASH_MESSAGES_TTL,
                'showClose' => true,
                'alert_action' => 'actionChangePassword',
            ]);
        }

        return $this->redirect(['profile', 'tab' => 2]);
    }

    /**
     * @return Response
     */
    public function actionRequestBrand()
    {
        if (!$this->SelfHostUser->shu_brand_requested) {
            $this->SelfHostUser->shu_brand_requested = SelfHostUsers::YES;
            $this->SelfHostUser->requestSupportOrBrand();
            $this->SelfHostUser->save();

            Yii::$app->session->setFlash('success', [
                'message'   => Yii::t('app/flash-messages', 'Request_was_sent'),
                'ttl'       => FLASH_MESSAGES_TTL,
                'showClose' => true,
                'alert_action' => 'actionChangePassword',
            ]);
        }

        return $this->redirect(['profile', 'tab' => 2]);
    }

    /**
     * @return Response
     */
    public function actionResendDownload()
    {
        $cache_key = 'shu-email-with-link-sent-' . $this->SelfHostUser->shu_id;

        if (!Yii::$app->cache->get($cache_key)) {
            Yii::$app->cache->set($cache_key, time(), Yii::$app->params['timeout_resend_confirm']);

            MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_newShuDownload, $this->SelfHostUser->shu_email, [
                'user_name' => $this->SelfHostUser->shu_name,
                'user_email' => $this->SelfHostUser->shu_email,
                'download_shu_link' => Yii::$app->urlManager->createAbsoluteUrl(['/download_shu_link']),
                'user_key' => $this->SelfHostUser->shu_user_hash,
            ]);

        }

        return $this->redirect(['profile', 'tab' => 1]);
    }
}
