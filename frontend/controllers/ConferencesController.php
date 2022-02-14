<?php

namespace frontend\controllers;

use common\models\UserConferences;
use frontend\models\forms\ParticipantAddForm;
use Yii;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\bootstrap\ActiveForm;
use common\models\Users;
use common\models\Licenses;
use common\models\UserLicenses;
use frontend\components\SController;
use frontend\models\ConferenceApi;
use frontend\models\forms\ConferenceAddForm;

/**
 * ConferencesController
 */
class ConferencesController extends SController
{

    public $special_access_actions = [
        'get-available-participants',
        'set-participants',
        'check-conference-name',
        'check-participant',
        'cancel-conference',
        'open-conference',
        'guest-link-send-to-email',
        'generate-new-guest-link',
    ];

    public $open_conference_access_token;
    public $guest_hash;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
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
                            'accept-invitation',
                        ],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],

                    /* Акшены которые доступны только неавторизованым (гостям) */
                    [
                        'actions' => [
                            'guest-actions',
                        ],
                        'allow' => true,
                        'roles' => ['?'],
                    ],

                    /* Акшены которые доступны только авторизованным */
                    [
                        'actions' => [
                            'index',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],

                    /* Акшены которые доступны только неавторизованым (гостям) и имеют дополнительные условия доступа */
                    [
                        'actions' => [
                            'open-conference',
                        ],
                        'allow' => true,
                        'matchCallback' => function($rule, $action) {
                            /**/
                            $access_token = Yii::$app->request->get('access_token', false);
                            if ($access_token && Yii::$app->cache->get($access_token)) {
                                $this->open_conference_access_token = Yii::$app->cache->get($access_token);
                                return true;
                            }

                            /**/
                            $this->guest_hash = Yii::$app->request->get('hash', false);
                            if ($this->guest_hash) {
                                return true;
                            }

                            /**/
                            return false;
                        },
                        'roles' => ['?'],
                    ],

                    /* Акшены которые доступны только авторизованным и имеют дополнительные условия доступа */
                    [
                        'actions' => $this->special_access_actions,
                        'allow' => true,
                        'matchCallback' => function($rule, $action) {
                            if ($this->User->license_type == Licenses::TYPE_FREE_DEFAULT) {
                                //$rule->allow = false;
                                return false;
                            }
                            return true;
                        },
                        'roles' => ['@'],
                    ],
                ],
                'denyCallback' => function($rule, $action) {
                    if (in_array($action->id, $this->special_access_actions)) {
                        if (!$this->User) {
                            return $this->redirect(['/']);
                        } else {
                            return $this->redirect(['/user/license-restriction']);
                        }
                    } else {
                        if (!$this->User) {
                            return $this->redirect(['/']);
                        } else {
                            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
                        }
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

    /**
     * @return Response
     */
    public function actionIndex()
    {
        return $this->redirect(['user/conferences']);
    }

    /**
     * @return array
     */
    public function actionCheckConferenceName()
    {
        $model = new ConferenceAddForm();
        $model->user_id = $this->User->user_id;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        return [];
    }

    /**
     * @param string $participant_email
     * @return array
     */
    public function actionCheckParticipant($participant_email)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $plus = 0;
        if ($this->User->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
            $User = Users::findByEmail($participant_email);
            if ($User && in_array($User->license_type, [
                    Licenses::TYPE_PAYED_PROFESSIONAL,
                    Licenses::TYPE_PAYED_BUSINESS_USER,
                    Licenses::TYPE_PAYED_BUSINESS_ADMIN,
                ])
            ) {
                $plus = 1;
            }
        } else {
            $plus = 1;
        }
        return [
            'status' => true,
            'data' => $plus,
        ];
    }

    /**
     * @param integer $conference_id
     * @return array
     */
    public function actionGetAvailableParticipants($conference_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $conference_id = intval($conference_id);

        $model = new ConferenceApi(['conference_id']);
        if ($model->load(['ConferenceApi' => ['conference_id' => $conference_id]]) && $model->validate()) {

            $ret = $model->getListAvailableParticipants($this->User);
            if ($this->User->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                $test = UserLicenses::getLicenseCountInfoForUser($this->User->user_id);
                $ret['cnt_license_available'] = $test['unused'];
            }
            return $ret;

        } else {
            return [
                'status' => false,
                'info'   => 'Some errors on get participants list',
                'debug'  => $model->getErrors(),
            ];
        }
    }

    /**
     * @return array
     */
    public function actionSetParticipants()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new ConferenceApi(['conference_id', 'conference_name']);
        if ($model->load(['ConferenceApi' => $_POST]) && $model->validate()) {

            $ret = $model->setListParticipants($this->User);
            if ($ret['status']) {
                $participants_html = $this->renderPartial('/conferences/conferences_list_item_participants', [
                    'User'            => $this->User,
                    'owner_user_id'   => $this->User->user_id,
                    'conference_id' => $ret['data']['conference_id'],
                    'conference_name' => $ret['data']['conference_name'],
                    'participants' => json_encode($ret['data']['participants'])
                ]);
                $ret['data']['participants_html'] = $participants_html;
                return $ret;
            } else {
                return $ret;
            }
        } else {
            return [
                'status' => false,
                'info'   => 'Some errors on set participants list',
                'debug'  => $model->getErrors(),
            ];
        }
    }

    /**
     * Акшен для вступления в конференцию по инвайт приглашению
     * @return Response
     */
    public function actionAcceptInvitation()
    {
        $model = new ConferenceApi(['participant_id']);
        if ($model->load(['ConferenceApi' => $_GET]) && $model->validate()) {
            $ret = $model->acceptInvitation($this->User);
            if ($ret['status']) {
                Yii::$app->session->setFlash('success2', [
                    'message'   => Yii::t('app/flash-messages', isset($ret['info']) ? $ret['info'] : 'YourJoinToConferenceIsAccepted'),
                    'ttl'       => FLASH_MESSAGES_TTL,
                    'showClose' => true,
                    'alert_action' => 'actionAcceptInvitation',
                    'type' => isset($ret['type']) ? $ret['type'] : 'success',
                ]);
                if (isset($ret['redirect'])) {
                    return $this->redirect($ret['redirect']);
                } else {
                    return $this->redirect([
                        '/user/conferences',
                        'mark' => (isset($ret['data']['conference_id']) ? $ret['data']['conference_id'] : 0),
                    ]);
                }
            } else {
                Yii::$app->session->setFlash('error', [
                    'message' => Yii::t('app/flash-messages', (isset($ret['info']) ? $ret['info'] : 'SomeErrorOnJoinConference')),
                    'ttl' => FLASH_MESSAGES_TTL,
                    'showClose' => true,
                    'alert_action' => 'actionAcceptInvitation',
                    'type'      => 'error',
                ]);
                if (isset($ret['redirect'])) {
                    return $this->redirect($ret['redirect']);
                } else {
                    return $this->redirect(['/']);
                }
            }
        } else {
            //var_dump($model->getErrors());
            Yii::$app->session->setFlash('error', [
                'message'   => Yii::t('app/flash-messages', 'SomeErrorOnJoinConference'),
                'ttl'       => FLASH_MESSAGES_TTL,
                'showClose' => true,
                'alert_action' => 'actionAcceptInvitation',
                'type'      => 'error',
            ]);
            return $this->redirect(['/']);
        }

    }

    /**
     * Акшен для выхода из конференцию или ее закрытия если это овнер
     * @param integer $conference_id
     * @return array
     */
    public function actionCancelConference($conference_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $conference_id = intval($conference_id);

        $model = new ConferenceApi(['conference_id']);
        if ($model->load(['ConferenceApi' => ['conference_id' => $conference_id]]) && $model->validate()) {

            return $model->cancelConference($this->User);

        } else {
            return [
                'status' => false,
                'info'   => 'Some errors on get participants list',
                'debug'  => $model->getErrors(),
            ];
        }
    }

    /**
     * @return string
     */
    public function actionOpenConference()
    {
        if (isset($this->User)) {
            $fail_redirect = ['user/conferences'];
        } else {
            $fail_redirect = ['/'];
        }

        /**/
        $_GET['conference_guest_hash'] = $this->guest_hash;

        /**/
        $model = new ConferenceApi();
        if ($model->load(['ConferenceApi' => $_GET]) && $model->validate()) {

            /**/
            if (isset($_GET['view'])) {
                if ($_GET['view'] == UserConferences::VIEW_GALLERY) {
                    Yii::$app->session->set('conference_view_mode', UserConferences::VIEW_GALLERY);
                } else {
                    Yii::$app->session->set('conference_view_mode', UserConferences::VIEW_SINGLE);
                }
            }

            /**/
            if ($this->open_conference_access_token && !$this->User) {
                $currentUser = Users::findIdentity($this->open_conference_access_token);
            } else {
                $currentUser = $this->User;
            }

            //var_dump($currentUser); exit;
            $ret = $model->openConference($currentUser);

            if ($ret['status'] && isset($ret['data'])) {
                $no_header = (bool) Yii::$app->request->get('header-free', 0);
                if ($no_header && file_exists(Yii::getAlias('@frontend').'/themes/' . DESIGN_THEME . '/layouts/main_no_header_no_footer.php')) {
                    $this->layout = 'main_no_header_no_footer';
                }
                $ret['data']['ParticipantAddForm'] = new ParticipantAddForm($this->User);
                return $this->render('/conferences/conference_room', $ret['data']);
            } else {
                if (isset($ret['redirect'])) {
                    Yii::$app->session->setFlash('error', [
                        'message' => Yii::t('app/flash-messages', (isset($ret['info']) ? $ret['info'] : 'SomeErrorOnOpenConferenceRoom')),
                        'ttl' => FLASH_MESSAGES_TTL,
                        'showClose' => true,
                        'alert_action' => 'actionOpenConference',
                        'type'      => 'error',
                    ]);
                    return $this->redirect($ret['redirect']);
                }
                Yii::$app->response->format = Response::FORMAT_JSON;
                return $ret;
            }

        } else {
            return $this->redirect($fail_redirect);
        }
    }

    /**
     * Send share link to email
     * @return mixed
     */
    public function actionGuestLinkSendToEmail()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new ParticipantAddForm($this->User);
        if ($model->load(['ParticipantAddForm' => $_POST], 'ParticipantAddForm') && $model->validate()) {

            return $model->guestLinkSendToEmail();

        }
        return ['status' => false, 'info' => 'Validation error.'];
    }

    /**
     *
     */
    public function actionGenerateNewGuestLink()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new ConferenceApi(['conference_id']);
        if ($model->load(['ConferenceApi' => Yii::$app->request->post()]) && $model->validate()) {

            return $model->generateGuestLink($this->User);

        } else {
            return [
                'status' => false,
                'info'   => 'Some errors on generate new link',
                'debug'  => $model->getErrors(),
            ];
        }
    }
}