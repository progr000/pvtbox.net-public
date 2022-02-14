<?php
namespace backend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Response;
use backend\components\SController;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\base\InvalidParamException;
use yii\filters\VerbFilter;
use common\models\CronInfo;
use backend\models\forms\LoginForm;
use backend\models\search\UsersSearch;
use backend\models\search\UserPaymentsSearch;
use backend\models\forms\ResetPasswordForm;
use backend\models\forms\ResetPasswordRequestForm;
use backend\models\forms\RegisterUserBySellerForm;
use backend\models\search\UserFilesSearch;
use backend\models\search\MailqSearch;
use backend\models\search\QueuedEventsSearch;
use backend\models\search\TestsLogSearch;
use backend\models\search\UserNodeSearch;

/**
 * Site controller
 */
class SiteController extends SController
{
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
                            'login',
                            'error',
                            'reset-password',
                            'reset-password-request',
                            'register-user-by-seller',
                        ],
                        'allow' => true,
                    ],
                    [
                        'actions' => [
                            'logout',
                            'index',
                            'cron-info-task-log',
                            'view-tests-log',
                            'view-tests-image',
                            'exec-test-manually',
                            'check-is-test-ready-to-start',
                            'search-share',
                            'clear-php-log',
                            'view-php-log',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //'logout' => ['post'],
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
     *
     */
    public function actionRegisterUserBySeller()
    {
        $model = new RegisterUserBySellerForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->createUser()) {
                Yii::$app->session->setFlash('success', 'User successfully created.');
            } else {
                Yii::$app->session->setFlash('danger', 'There was an error on creating new User.');
            }
            return $this->redirect(['site/register-user-by-seller']);
        }

        return $this->render('register-user-by-seller', [
            'model' => $model,
        ]);
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModelUsers = new UsersSearch();
        $searchModelPayments = new UserPaymentsSearch();
        $searchModelCronInfo = new CronInfo();
        $searchTestsLog = new TestsLogSearch();
        $ret = QueuedEventsSearch::getQueuesStatuses();
        return $this->render('index', [
            'Admin'                 => $this->Admins,
            'dataProviderUsers'     => $searchModelUsers->totalStatistic(),
            'totalUsersInfo'        => UsersSearch::totalUsersInfo(),
            'totalUserNodeInfo'     => UserNodeSearch::totalUserNodeInfo(),
            'dataProviderPayments'  => $searchModelPayments->totalStatistic(),
            'dataProviderActivity'  => $searchModelUsers->activityStatistic(),
            'dataProviderSAC'       => $searchModelUsers->sharesAndCollaborationsStatistic(),
            'dataProviderCronInfo'  => $searchModelCronInfo->search(),
            'dataMailqTotal'        => MailqSearch::getTotal(),
            'dataTestsLogList'      => $searchTestsLog->listReports(),
            //'QueuesStatusesDataProvider' => QueuedEventsSearch::getQueuesStatusesDataProvider(),
            'QueuesStatuses' => $ret['v2'],
            'PhpLogs' => [
                'blog'       => file_exists(Yii::$app->params['log_blog_path'])       ? filesize(Yii::$app->params['log_blog_path'])     : null,
                'frontend'   => file_exists(Yii::$app->params['log_frontend_path'])   ? filesize(Yii::$app->params['log_frontend_path']) : null,
                'selfhosted' => file_exists(Yii::$app->params['log_selfhosted_path']) ? filesize(Yii::$app->params['log_selfhosted_path']) : null,
                'console'    => file_exists(Yii::$app->params['log_console_path'])    ? filesize(Yii::$app->params['log_console_path'])  : null,
                'backend'    => file_exists(Yii::$app->params['log_backend_path'])    ? filesize(Yii::$app->params['log_backend_path'])  : null,
            ]
        ]);
    }

    /**
     * @return array|bool
     */
    public function actionExecTestManually()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        set_time_limit(600);
        $model = new TestsLogSearch();
        return $model->ExecManually();
    }

    /**
     * @return array
     */
    public function actionCheckIsTestReadyToStart()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new TestsLogSearch();
        return [
            'status' => (!$model->CheckTestInProgress()),
        ];
    }

    /**
     * @param string $report
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionViewTestsLog($report)
    {
        $model = new TestsLogSearch();
        if ($model->load(['TestsLogSearch' => ['report' => $report]]) && $model->validate()) {

            $ret = $model->getReport();
            if ($ret['status']) {
                if (trim($ret['content'])) {
                    $this->layout = 'empty';
                    return $ret['content'];
                } else {
                    throw new BadRequestHttpException('Report is Empty', 300);
                }
            } else {
                throw new NotFoundHttpException('Report not found.');
            }
        } else {
            throw new BadRequestHttpException(Json::encode($model->getErrors()));
        }
    }

    /**
     * @param string $image
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionViewTestsImage($image, $image_dir)
    {
        $model = new TestsLogSearch();
        if ($model->load(['TestsLogSearch' => ['image' => $image, 'image_dir' => $image_dir]]) && $model->validate()) {

            $ret = $model->getImage();
            if ($ret['status']) {
                $this->layout = 'empty';
                Yii::$app->response->format = Response::FORMAT_RAW;

                $headers = Yii::$app->response->headers;
                $headers->removeAll();
                $headers->add('Content-Type', 'image/png');
                $headers->add('Content-Disposition', "inline; filename=\"{$model->image}.png\"");
                return file_get_contents($ret['path']);
            } else {
                throw new NotFoundHttpException('Image not found.');
                //return $ret['info'];
            }

        } else {
            throw new BadRequestHttpException(Json::encode($model->getErrors()));
        }
    }

    /**
     * @param $task_id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionCronInfoTaskLog($task_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $task_id = intval($task_id);

        $task = CronInfo::findOne(['task_id' => $task_id]);
        if ($task) {
            return [
                'status' => true,
                'data'   => nl2br($task->task_log),
            ];
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @return \yii\web\Response
     */
    public function actionSearchShare()
    {
        $share_val = Yii::$app->request->post('share_val');
        if (!$share_val) {
            Yii::$app->session->setFlash('danger', 'Bad share_link or share_hash.');
            return $this->redirect(['index']);
        }
        preg_match("/[a-z0-9]{32}/", $share_val, $ma);
        if (!isset($ma[0])) {
            Yii::$app->session->setFlash('danger', 'Bad share_link or share_hash.');
            return $this->redirect(['index']);
        }

        $searchModel = new UserFilesSearch();
        $UserFile = $searchModel->findFileByShareHash($ma[0]);
        if ($UserFile) {
            //id=5420&tab=file-info&sort-p2=share_hash#file-info
            return $this->redirect([
                'users/view',
                'id' => $UserFile->user_id,
                'UserFilesSearch[share_hash]' => $UserFile->share_hash,
                '#' => 'file-info'
            ]);
        } else {
            Yii::$app->session->setFlash('danger', 'Share not found.');
            return $this->redirect(['index']);
        }
        //exit;
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'Password was changed successfully.');

            return $this->goHome();
        }

        return $this->render('reset-password', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPasswordRequest()
    {
        $model = new ResetPasswordRequestForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->sendEmail()) {
            Yii::$app->session->setFlash('success', 'Check your E-Mail for instructions');

            return $this->goHome();
        }

        return $this->render('reset-password-request', [
            'model' => $model,
        ]);
    }

    /**
     * @param string $target
     * @return Response
     */
    public function actionClearPhpLog($target)
    {
        $target = 'log_' . strip_tags($target) . '_path';
        if (isset(Yii::$app->params[$target])) {
            //var_dump(Yii::$app->params[$target]);exit;
            @unlink(Yii::$app->params[$target]);
        }
        return $this->goHome();
    }

    /**
     * @param string $target
     * @return Response
     */
    public function actionViewPhpLog($target)
    {
        Yii::$app->response->format = Response::FORMAT_RAW;
        $target = 'log_' . strip_tags($target) . '_path';

        if (isset(Yii::$app->params[$target]) && file_exists(Yii::$app->params[$target])) {
            return file_get_contents(Yii::$app->params[$target]);
            return [
                'status' => true,
                'data' => file_get_contents(Yii::$app->params[$target]),
            ];
        } else {
            return 'empty';
            return [
                'status' => false,
                'data' => '',
            ];
        }
    }
}
