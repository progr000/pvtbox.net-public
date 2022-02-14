<?php

namespace backend\controllers;

use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use backend\components\SController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\base\DynamicModel;
use common\helpers\FileSys;
use common\models\Users;
use common\models\UserFiles;
use common\models\UserLicenses;
use common\models\UserServerLicenses;
use common\models\Licenses;
use common\models\UserAlertsLog;
use common\models\MailTemplatesStatic;
use backend\models\Admins;
use backend\models\search\UsersSearch;
use backend\models\search\UserNodeSearch;
use backend\models\search\UserFilesSearch;
use backend\models\search\UserFileEventsSearch;
use backend\models\search\UserLicensesSearch;
use backend\models\search\UserCollaborationsSearch;
use backend\models\search\TrafficSearch;
use backend\models\search\NodesWithFileSearch;
use backend\models\search\EventsForFileSearch;
use backend\models\search\ColleaguesForCollaborationSearch;
use backend\models\search\UserAlertsLogSearch;
use backend\models\search\UserActionsLogSearch;
use backend\models\forms\AddLicenseCount;
use backend\models\forms\AddServerLicenseCount;
use backend\models\forms\DeleteOldPatches;
use backend\models\search\UserPaymentsSearch;
use backend\models\search\UserServerLicensesSearch;
use frontend\models\forms\ShareElementForm;

/**
 * UsersController implements the CRUD actions for Users model.
 */
class UsersController extends SController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
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
     * Finds the Users model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Users the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     * @throws ForbiddenHttpException if the model cannot be accessed
     */
    protected function findModel($id)
    {
        if (($model = Users::findOne(['user_id' => $id])) !== null) {

            if ($this->Admins->admin_role != Admins::ROLE_ROOT && $this->Admins->admin_id != $model->user_ref_id) {
                throw new ForbiddenHttpException("You don't have permissions for this action.");
            }

            return $model;

        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Finds the UserFiles model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $file_id
     * @return UserFiles the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     * @throws ForbiddenHttpException if the model cannot be accessed
     */
    protected function findFile($file_id)
    {
        if (($model = UserFiles::findOne(['file_id' => $file_id])) !== null) {

            if ($this->Admins->admin_role != Admins::ROLE_ROOT) {
                /** @var \common\models\Users $userModel */
                $userModel = $model->getUser()->one();
                if ($this->Admins->admin_id != $userModel->user_ref_id) {
                    throw new ForbiddenHttpException("You don't have permissions for this action.");
                }
            }

            return $model;

        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Finds the UserFiles model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $collaboration_id
     * @return \common\models\UserCollaborations the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     * @throws ForbiddenHttpException if the model cannot be accessed
     */
    protected function findCollaboration($collaboration_id)
    {
        if (($model = UserCollaborationsSearch::findOne(['collaboration_id' => $collaboration_id])) !== null) {

            return $model;

        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param string $render   render | renderPartial
     * @return mixed
     */
    protected function getIndexContent($render = 'render')
    {
        //var_dump(Yii::$app->request->queryParams);
        if (!in_array($render, ['render', 'renderPartial'])) { $render = 'render'; }

        $searchModel = new UsersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $this->Admins);
        return $this->$render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Users models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->getIndexContent();
    }

    /**
     * Displays a single Users model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        if (isset($_GET['UserNodeSearch']['tab'])) { $_GET['tab'] = 'node-info'; unset($_GET['UserNodeSearch']['tab']); }
        if (isset($_GET['UserFilesSearch']['tab'])) { $_GET['tab'] = 'file-info'; unset($_GET['UserFilesSearch']['tab']); }
        if (isset($_GET['UserFileEventsSearch']['tab'])) { $_GET['tab'] = 'event-info'; unset($_GET['UserFilesSearch']['tab']); }
        if (isset($_GET['UserLicensesSearch']['tab'])) { $_GET['tab'] = 'licenses-info'; unset($_GET['UserLicensesSearch']['tab']); }
        if (isset($_GET['UserCollaborationsSearch']['tab'])) { $_GET['tab'] = 'collaborations-info'; unset($_GET['UserCollaborationsSearch']['tab']); }
        if (isset($_GET['TrafficSearch']['tab'])) { $_GET['tab'] = 'traffic-info'; unset($_GET['TrafficSearch']['tab']); }
        if (isset($_GET['UserAlertsLogSearch']['tab'])) { $_GET['tab'] = 'alert-logs'; unset($_GET['UserAlertsLogSearch']['tab']); }
        if (isset($_GET['UserActionsLogSearch']['tab'])) { $_GET['tab'] = 'action-logs'; unset($_GET['UserActionsLogSearch']['tab']); }
        if (isset($_GET['UserPaymentsSearch']['tab'])) { $_GET['tab'] = 'payments-info'; unset($_GET['UserPaymentsSearch']['tab']); }

        $logType = Yii::$app->request->get('log', TrafficSearch::$logType[0]);
        if (!in_array($logType, TrafficSearch::$logType)) {
            $logType = TrafficSearch::$logType[0];
        }

        $UserModel = $this->findModel($id);

        $TrafficSearchModel = new TrafficSearch();
        $UserNodeSearchModel = new UserNodeSearch();
        $UserFilesSearchModel = new UserFilesSearch();
        $UserFileEventsSearchModel = new UserFileEventsSearch();
        $UserLicensesSearchModel = new UserLicensesSearch();
        $UserServerLicensesSearchModel = new UserServerLicensesSearch();
        $UserCollaborationsSearchModel = new UserCollaborationsSearch();
        $UserAlertLogSearchModel = new UserAlertsLogSearch();
        $UserActionsLogSearchModel = new UserActionsLogSearch();

        /****BEGIN-CUT-IT-IN-SH****/
        if (!Yii::$app->params['self_hosted']) {
            $UserPaymentsSearchModel = new UserPaymentsSearch();
        } else {
            $UserPaymentsSearchModel = null;
        }
        /****END-CUT-IT-IN-SH****/

        return $this->render('view', [
            'UserModel' => $UserModel,
            //'TrafficSearchModel' => $TrafficSearchModel,
            //'TrafficSearchDataProvider' => $TrafficSearchModel->search($UserModel->user_remote_hash),
            'TrafficSearchModel' => $TrafficSearchModel,
            'TrafficSearchDataProvider' => $TrafficSearchModel->searchBD($UserModel->user_id),
            'UserNodeSearchModel' => $UserNodeSearchModel,
            'UserNodeSearchDataProvider' => $UserNodeSearchModel->search(Yii::$app->request->queryParams, $UserModel->user_id),
            'NodeInfo' => $UserNodeSearchModel->selectCountNodes($UserModel->user_id),
            'UserFilesSearchModel' => $UserFilesSearchModel,
            'UserFilesSearchDataProvider' => $UserFilesSearchModel->search($UserModel->user_id, Yii::$app->request->queryParams),
            'UserFileEventsSearchModel' => $UserFileEventsSearchModel,
            'UserFileEventsSearchDataProvider' => $UserFileEventsSearchModel->search($UserModel->user_id, Yii::$app->request->queryParams),
            'UserLicensesSearchModel' => $UserLicensesSearchModel,
            'UserLicensesSearchDataProvider' => $UserLicensesSearchModel->search($UserModel->user_id, Yii::$app->request->queryParams),
            'UserServerLicensesSearchModel' => $UserServerLicensesSearchModel,
            'UserServerLicensesSearchDataProvider' => $UserServerLicensesSearchModel->search($UserModel->user_id, Yii::$app->request->queryParams),
            'UserCollaborationsSearchModel' => $UserCollaborationsSearchModel,
            'UserCollaborationsSearchDataProvider' => $UserCollaborationsSearchModel->search($UserModel->user_id, Yii::$app->request->queryParams),
            'UserAlertLogSearchModel' => $UserAlertLogSearchModel,
            'UserAlertLogSearchDataProvider' => $UserAlertLogSearchModel->search(Yii::$app->request->queryParams, $UserModel->user_id),
            'UserActionsLogSearchModel' => $UserActionsLogSearchModel,
            'UserActionsLogSearchDataProvider' => $UserActionsLogSearchModel->search(Yii::$app->request->queryParams, $UserModel->user_id),
            /****BEGIN-CUT-IT-IN-SH****/
            'UserPaymentsSearchModel' => $UserPaymentsSearchModel,
            'UserPaymentsSearchDataProvider' => $UserPaymentsSearchModel
                ? $UserPaymentsSearchModel->search(Yii::$app->request->queryParams, $UserModel->user_id)
                : null,
            /****END-CUT-IT-IN-SH****/
            'totalFsInfo' => $UserFilesSearchModel->getCountFoldersAndFiles($UserModel->user_id),
            'licenseCountInfo' => UserLicenses::getLicenseCountInfoForUser($UserModel->user_id),
            'serverLicenseCountInfo' => UserServerLicenses::getLicenseCountInfoForUser($UserModel->user_id),
        ]);
    }

    /**
     * @param string $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionAddLicenseCount($id)
    {
        /****BEGIN-CUT-IT-IN-SH****/
        if (!Yii::$app->params['self_hosted']) {
            $user = $this->findModel($id);

            if ($user->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                $model = new AddLicenseCount();
                if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                    $model->add($user);
                }
            }
        }
        /****END-CUT-IT-IN-SH****/
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * @param string $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionAddServerLicenseCount($id)
    {
        /****BEGIN-CUT-IT-IN-SH****/
        if (!Yii::$app->params['self_hosted']) {
            $user = $this->findModel($id);

            if ($user->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                $model = new AddServerLicenseCount();
                if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                    $model->add($user);
                }
            }
        }
        /****END-CUT-IT-IN-SH****/
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * @param mixed $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionCheckIsDopReadyToStart($id)
    {
        $User = $this->findModel($id);

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'status' => ($User->user_dop_status == Users::DOP_IS_COMPLETE),
        ];
    }

    /**
     * @param mixed $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionGetLogDopForUser($id)
    {
        $User = $this->findModel($id);

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'status' => true,
            'data'   => nl2br($User->user_dop_log),
        ];
    }

    /**
     * @param mixed $id
     * @param mixed $restorePatchTTL
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionStartDopForUser($id, $restorePatchTTL) {

        $User = $this->findModel($id);

        $restorePatchTTL = intval($restorePatchTTL);
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new DeleteOldPatches();
        if ($model->load(['DeleteOldPatches' => [
                'userId'   => $id,
                'restorePatchTTL' => $restorePatchTTL,
            ]
            ]) && $model->validate()) {

            return $model->startDop($User);

        }
        /*
        $yii_path = realpath(Yii::getAlias('@app') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . "yii");
        $cmd = $yii_path . "yii console/delete-old-patches --userId={$user->user_id} --restorePatchTTL={$restorePatchTTL}";
        exec($cmd, $output);
        var_dump($output); exit;
        */

        return [
            'status' => false,
            'info'   => $model->getErrors(),
        ];
    }

    /**
     * Creates a new Users model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        //return $this->redirect('index');

        $user = new Users();
        $user->user_balance = '0.00';

        $password = new DynamicModel(['password']);
        $password->addRule(['password'], 'required')
                 ->addRule(['password'], 'string', ['min' => 6]);

        if ($user->load(Yii::$app->request->post()) && $password->load(Yii::$app->request->post())) {

            /* Автоназначение админа для юзеров в случае СХ */
            if (Yii::$app->params['self_hosted']) {
                $user->license_type = Licenses::TYPE_PAYED_BUSINESS_USER;
                /** @var \common\models\Users $businessAdminAuto */
                $businessAdminAuto = Users::find()
                    ->where([
                        'license_type' => Licenses::TYPE_PAYED_BUSINESS_ADMIN,
                    ])
                    ->orderBy(['user_id' => SORT_ASC])
                    ->limit(1)
                    ->one();
                if ($businessAdminAuto) {
                    $user->license_business_from = $businessAdminAuto->user_id;
                }
            }

            /* если бизнес-юзер лицензия, то должно быть задано поле license_business_from иначе на даем сохранять такое в БД */
            if ($user->license_type == Licenses::TYPE_PAYED_BUSINESS_USER && !$user->license_business_from) {
                $user->addError('license_business_from', 'Field license_business_from must be set for license_type = BUSINESS_USER');
                $user->addError('license_type', 'Field license_business_from must be set for license_type = BUSINESS_USER');
                $user->addError('user_email', 'Field license_business_from must be set for license_type = BUSINESS_USER');
                return $this->render('create', [
                    'user' => $user,
                    'password' => $password,
                    'Admin' => $this->Admins,
                ]);
            }

            /* Если же лицензия не бизнес-юзер, то установим license_business_from = null */
            if ($user->license_type != Licenses::TYPE_PAYED_BUSINESS_USER) {
                $user->license_business_from = null;
            }

            /* Если задан license_business_from то проверим что он существует в БД
             * А так же что у бизнес-админа есть свободная лицензия для этого юзера
             */
            if ($user->license_business_from) {
                $businessAdmin = Users::findOne([
                    'user_id'      => $user->license_business_from,
                    'license_type' => Licenses::TYPE_PAYED_BUSINESS_ADMIN,
                ]);
                if (!$businessAdmin) {
                    $user->addError('license_business_from', "Not exist BusinessAdmin with UserID = {$user->license_business_from}");
                    $user->addError('user_email', "Not exist BusinessAdmin with UserID = {$user->license_business_from}");
                    return $this->render('create', [
                        'user' => $user,
                        'password' => $password,
                        'Admin' => $this->Admins,
                    ]);
                }
                $checkLicenseAvailable = UserLicenses::getFreeLicense($businessAdmin->user_id);
                if (!$checkLicenseAvailable) {
                    $user->addError('license_business_from', "this business-admin (UserID = {$businessAdmin->user_id}) has no enough licenses to create a new user");
                    $user->addError('user_email', "this business-admin (UserID = {$businessAdmin->user_id}) has no enough licenses to create a new user");
                    return $this->render('create', [
                        'user' => $user,
                        'password' => $password,
                        'Admin' => $this->Admins,
                    ]);
                }

                $user->license_expire = $businessAdmin->license_expire;
                $user->license_period = $businessAdmin->license_period;
            }

            $user->user_name = $user->user_email;
            $user->setPassword($password->password);
            $user->generateAuthKey();

            if ($this->Admins->admin_role != Admins::ROLE_ROOT) {
                $user->user_ref_id = $this->Admins->admin_id;
            }

            /*
             * тут специально ставим лицензию ФРИТРИАЛ что бы сработала
             * вот эта хуйня ниже adminPanelCreateNullCollaboration
             * она списывает лицуху с бизнес-админа только если лицуха у колеги
             * фри или фритриал
             */
            if (isset($businessAdmin) && $businessAdmin) {
                $user->license_type = Licenses::TYPE_FREE_DEFAULT;
            }

            /* сохраняем юзера */
            if ($user->save()) {

                /* Если это бизнес-юзер, нужно создать нулл-колаборацию что бы сняло лицензию у бизнес-админа и показало его в списке коллег */
                if (isset($businessAdmin) && $businessAdmin) {
                    $modelShare = new ShareElementForm(['colleague_email']);
                    $modelShare->colleague_email = $user->user_email;
                    $ret = $modelShare->adminPanelCreateNullCollaboration($businessAdmin);
                    //var_dump($ret); exit;
                }

                /* отправка письма юзеру, если установлена галка что нужно отправить */
                if (isset($_POST['send_email_about_registration'])) {
                    MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_newRegister, $user->user_email, ['UserObject' => $user]);
                    //exit;
                }

                /* флеш-сообщение */
                Yii::$app->session->setFlash('success', 'User successfully created.');
                return $this->redirect(['view', 'id' => $user->user_id]);
            } else {
                Yii::$app->session->setFlash('danger', 'There was an error on creating new User.');
            }
        }

        $user->user_status = Users::STATUS_ACTIVE;
        return $this->render('create', [
            'user' => $user,
            'password' => $password,
            'Admin' => $this->Admins,
        ]);

    }

    /**
     * Updates an existing Users model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $user = $this->findModel($id);
        $current_license_business_from = $user->license_business_from;
        $current_license_type = $user->license_type;
        $password = new DynamicModel(['password']);

        $old_user_ref_id = $user->user_ref_id;
        //var_dump($old_user_ref_id); exit;
        if ($user->load(Yii::$app->request->post())) {

            /* Автоназначение админа для юзеров в случае СХ */
            if (Yii::$app->params['self_hosted']) {
                $user->license_type = Licenses::TYPE_PAYED_BUSINESS_USER;
                /** @var \common\models\Users $businessAdminAuto */
                $businessAdminAuto = Users::find()
                    ->where([
                        'license_type' => Licenses::TYPE_PAYED_BUSINESS_ADMIN,
                    ])
                    ->orderBy(['user_id' => SORT_ASC])
                    ->limit(1)
                    ->one();
                if ($businessAdminAuto) {
                    $user->license_business_from = $businessAdminAuto->user_id;
                }
            }

            /* если бизнес-юзер лицензия, то должно быть задано поле license_business_from иначе на даем сохранять такое в БД */
            if ($user->license_type == Licenses::TYPE_PAYED_BUSINESS_USER && !$user->license_business_from) {
                $user->addError('license_business_from', 'Field license_business_from must be set for license_type = BUSINESS_USER');
                $user->addError('license_type', 'Field license_business_from must be set for license_type = BUSINESS_USER');
                $user->addError('user_email', 'Field license_business_from must be set for license_type = BUSINESS_USER');
                return $this->render('update', [
                    'user' => $user,
                    'password' => $password,
                    'Admin' => $this->Admins,
                ]);
            }

            /* Если же лицензия не бизнес-юзер, то установим license_business_from = null */
            if ($user->license_type != Licenses::TYPE_PAYED_BUSINESS_USER) {
                $user->license_business_from = null;
            }

            /* Если задан license_business_from то проверим что он существует в БД
             * А так же что у бизнес-админа есть свободная лицензия для этого юзера
             */
            if ($user->license_business_from) {
                $businessAdmin = Users::findOne([
                    'user_id'      => $user->license_business_from,
                    'license_type' => Licenses::TYPE_PAYED_BUSINESS_ADMIN,
                ]);
                if (!$businessAdmin) {
                    $user->addError('license_business_from', "Not exist BusinessAdmin with UserID = {$user->license_business_from}");
                    $user->addError('user_email', "Not exist BusinessAdmin with UserID = {$user->license_business_from}");
                    return $this->render('update', [
                        'user' => $user,
                        'password' => $password,
                        'Admin' => $this->Admins,
                    ]);
                }
                if ($current_license_business_from != $user->license_business_from) {
                    $checkLicenseAvailable = UserLicenses::getFreeLicense($businessAdmin->user_id);
                    if (!$checkLicenseAvailable) {
                        $user->addError('license_business_from', "this business-admin (UserID = {$businessAdmin->user_id}) has no enough licenses to create a new user");
                        $user->addError('user_email', "this business-admin (UserID = {$businessAdmin->user_id}) has no enough licenses to create a new user");
                        return $this->render('create', [
                            'user' => $user,
                            'password' => $password,
                            'Admin' => $this->Admins,
                        ]);
                    }
                }
                $user->license_expire = $businessAdmin->license_expire;
                $user->license_period = $businessAdmin->license_period;
            }

            /**/
            if ($this->Admins->admin_role != Admins::ROLE_ROOT) {
                $user->user_ref_id = $old_user_ref_id;
            }

            /* если сменился бизнес-админ */
            if (!empty($user->license_business_from) &&
                !empty($current_license_business_from) &&
                $user->license_business_from != $current_license_business_from)
            {
                $business_admin_changed = true;
            }

            /* если перестал быть бизнес-юзером или сменился бизнес админ освободить лицензии и колабы */
            if (($current_license_type == Licenses::TYPE_PAYED_BUSINESS_USER && $user->license_type != Licenses::TYPE_PAYED_BUSINESS_USER) || isset($business_admin_changed))
            {
                $modelShareDel = new ShareElementForm(['colleague_email']);
                $modelShareDel->colleague_email = $user->user_email;
                $modelShareDel->owner_user_id = $current_license_business_from;
                $modelShareDel->adminPanelColleagueDelete();
            }

            if ($user->save()) {


                /* если сменился бизнес-админ для этого бизнес-юзера нужно удалить предыдущие колабы и лицензии освободить */
//                if ($user->license_type == Licenses::TYPE_PAYED_BUSINESS_USER &&
//                    isset($businessAdmin) && $businessAdmin && !empty($current_license_business_from) &&
//                    $user->license_business_from != $current_license_business_from) {
//
//                    $modelShareDel = new ShareElementForm(['colleague_email']);
//                    $modelShareDel->colleague_email = $user->user_email;
//                    $modelShareDel->owner_user_id = $current_license_business_from;
//                    $modelShareDel->adminPanelColleagueDelete();
//
//                }

                /* а тут назначить новую лицензию от нового бизнес-юзера */
                if ($user->license_type == Licenses::TYPE_PAYED_BUSINESS_USER && isset($businessAdmin) && $businessAdmin) {
                    if ($current_license_business_from != $user->license_business_from) {
                        $user->license_type = Licenses::TYPE_FREE_DEFAULT;
                        $user->save();
                        $modelShareAdd = new ShareElementForm(['colleague_email']);
                        $modelShareAdd->colleague_email = $user->user_email;
                        $modelShareAdd->adminPanelCreateNullCollaboration($businessAdmin);
                    }
                }

                return $this->redirect(['view', 'id' => $user->user_id]);
            }

        }

        return $this->render('update', [
            'user' => $user,
            'password' => $password,
            'Admin' => $this->Admins,
        ]);
    }

    /**
     * Deletes an existing Users model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        return $this->redirect('index');
        /*
        $this->findModel($id)->delete();

        if (Yii::$app->getRequest()->isAjax) {
            unset($_GET['id']);
            return $this->getIndexContent('renderPartial');
        }

        $qs = Yii::$app->request->get('qs');
        if ($qs) { $qs = "?".base64_decode($qs); } else { $qs = ""; }
        return $this->redirect(['index'.$qs]);
        */
    }

    /**
     * Opens user profile in frontend
     * @param string $id
     * @return mixed
     */
    public function actionProfile($id)
    {
        $user = $this->findModel($id);
        $fu_hs = hash("sha512", $user->user_email . $user->password_hash);
        return $this->redirect(Yii::getAlias('@frontendWeb')."/user/fictive-login?fu_id={$id}&fu_hs={$fu_hs}");
    }

    /**
     * Changes user_status in an Users model/
     * @param string $id
     * @return mixed
     */
    public function actionChangeStatus($id)
    {
        $model = $this->findModel($id);
        $model->user_status = $model->user_status == Users::STATUS_ACTIVE ? Users::STATUS_BLOCKED : Users::STATUS_ACTIVE;
        $model->save();

        if (Yii::$app->getRequest()->isAjax) {
            unset($_GET['id']);
            return $this->getIndexContent('renderPartial');
        }

        $qs = Yii::$app->request->get('qs');
        if ($qs) { $qs = "?".base64_decode($qs); } else { $qs = ""; }
        return $this->redirect(['index'.$qs]);
    }

    /**
     * @param integer $file_id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionNodesWithFile($file_id)
    {
        $file_id = intval($file_id);

        $UserFileModel = $this->findFile($file_id);

        $model = $model = new NodesWithFileSearch();
        $dataProvider = $model->search($UserFileModel);

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'status' => true,
            'data'   => $this->renderPartial('nodes-with-file', [
                'model' => $model,
                'dataProvider' => $dataProvider,
            ]),
        ];
    }

    /**
     * @param integer $file_id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionEventsForFile($file_id)
    {
        $file_id = intval($file_id);

        $UserFileModel = $this->findFile($file_id);

        $model = new EventsForFileSearch();
        $dataProvider = $model->search($UserFileModel);

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'status' => true,
            'data'   => $this->renderPartial('events-for-file', [
                'model' => $model,
                'dataProvider' => $dataProvider,
            ]),
        ];
    }

    /**
     * @param integer $collaboration_id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionColleaguesForCollaboration($collaboration_id)
    {
        $collaboration_id = intval($collaboration_id);

        $UserCollaboration = $this->findCollaboration($collaboration_id);

        $model = new ColleaguesForCollaborationSearch();
        $dataProvider = $model->search($UserCollaboration);

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'status' => true,
            'data'   => $this->renderPartial('colleagues-for-collaboration', [
                'model' => $model,
                'dataProvider' => $dataProvider,
            ]),
        ];
    }

    /**
     * @param $file_id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionShowFullPath($file_id)
    {
        $file_id = intval($file_id);

        $UserFileModel = $this->findFile($file_id);

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'status' => true,
            'data'   => 'All files'. DIRECTORY_SEPARATOR . UserFiles::getFullPath($UserFileModel),
        ];
    }

    /**
     * @param $file_id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionShowOnlyPath($file_id)
    {
        $file_id = intval($file_id);

        $UserFileModel = $this->findFile($file_id);

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'status' => true,
            'data'   => dirname('All files'. DIRECTORY_SEPARATOR . UserFiles::getFullPath($UserFileModel)) . DIRECTORY_SEPARATOR,
        ];
    }

    /**
     * @param integer $user_id
     * @param string $date
     * @param string $type
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionShowFilesForTrafficInfo($user_id, $date, $type)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $user_id = intval($user_id);

        $UserFileModel = $this->findModel($user_id);

        $model = new TrafficSearch();
        $dataProvider = $model->showFilesForTraffic($UserFileModel->user_id, $date, $type);

        return [
            'status' => true,
            'data'   => $this->renderPartial('files-for-traffic-info', [
                'model' => $model,
                'dataProvider' => $dataProvider,
                'type' => $type,
            ]),
        ];
    }

    /**
     * @param $record_id
     * @return array
     */
    public function actionShowAlertMessageText($record_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $record_id = intval($record_id);

        $al = UserAlertsLog::findIdentity($record_id);
        if ($al) {
            return [
                'status' => true,
                'data'   => $al->alert_message,
            ];
        }

        return [
            'status' => false,
        ];
    }

    /**
     * @param $file_id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionLockUnlockShareLink($file_id, $share_is_locked)
    {
        $file_id = intval($file_id);

        $UserFileModel = $this->findFile($file_id);

        Yii::$app->response->format = Response::FORMAT_JSON;
        $UserFileModel->share_is_locked = $share_is_locked;
        if ($UserFileModel->save()) {
            return [
                'status' => true,
            ];
        } else {
            return [
                'status' => false,
                'info' => $UserFileModel->getErrors(),
            ];
        }
    }

    /**
     * @return string
     */
    public function actionRefactorDirNodeFs()
    {
        if (!Yii::$app->params['Stop_NodeApi_and_FM']) {
            return "You must suspend NodeApi and FM before. Set param Stop_NodeApi_and_FM = true in params.php";
        }
        $last_user_id = 6956;
        $Users = Users::find()
            ->where("user_id >= :last_user_id", ['last_user_id' => $last_user_id])
            ->orderBy(['user_id' => SORT_ASC])
            ->limit(2000)
            ->all();
        $str = "Start<br />\n";
        foreach ($Users as $User) {
            /** @var \common\models\Users $User*/
            //$User->generatePathForUser();

            $last_user_id = $User->user_id;
            $tmp = intval(floor($User->user_id / 100)) * 100;

            $old_path = Yii::$app->params['nodeVirtualFS'] . DIRECTORY_SEPARATOR . 'UserID-' . $User->user_id;
            $new_path_dst = Yii::$app->params['nodeVirtualFS'] . DIRECTORY_SEPARATOR . $tmp;
            $new_path = Yii::$app->params['nodeVirtualFS'] . DIRECTORY_SEPARATOR . $tmp . DIRECTORY_SEPARATOR . 'UserID-' . $User->user_id;

            if (!file_exists($new_path_dst)) {
                FileSys::mkdir($new_path_dst);
            }

            if (file_exists($old_path) && file_exists($new_path_dst) && is_dir($old_path) && is_dir($new_path_dst)) {
                FileSys::move($old_path, $new_path);
            }
        }
        $str .= "Finish<br />\n";
        $str .= "Last user_id = {$last_user_id}";
        return $str;
    }
}
