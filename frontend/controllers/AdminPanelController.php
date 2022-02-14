<?php

namespace frontend\controllers;

use Yii;
use yii\web\Response;
use yii\filters\AccessControl;
use common\models\Users;
use common\models\UserNode;
use common\models\UserColleagues;
use common\models\UserLicenses;
use common\models\UserServerLicenses;
use common\models\Servers;
use common\models\Licenses;
use common\models\RemoteActions;
use frontend\components\SController;
use frontend\models\forms\ChangeNameForm;
use frontend\models\forms\ChangeOoAddressForm;
use frontend\models\search\ColleaguesSearch;
use frontend\models\search\ColleaguesReportsSearch;
use frontend\models\search\ServerLicensesSearch;
use frontend\models\forms\ShareElementForm;
use frontend\models\JsTreeMy;
use frontend\models\NodeApi;

/**
 * AdminPanel controller
 */
class AdminPanelController extends SController
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
                        'allow' => $this->checkAccess(),
                        'roles' => ['@'],
                        'denyCallback' => function ($rule, $action) {
                            return $this->redirect(['/user/files']);
                        }
                    ],
                ],
            ],
        ];
    }

    /**
     * Проверяет доступна ли админ панель этому юзеру
     * @return bool
     */
    public function checkAccess()
    {
        return ($this->User && $this->User->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN && $this->User->enable_admin_panel);
    }

    /**
     * Отображает главную страницу с закладками (разделами) админ панели
     * @return string
     */
    public function actionIndex()
    {
        /** Смена имени */
        $model_change_name = new ChangeNameForm();
        if ($model_change_name->load(Yii::$app->request->post()) && $model_change_name->validate()) {
            if ($this->User->user_name !== $model_change_name->user_name) {
                if ($model_change_name->changeName()) {
                    Yii::$app->getSession()->setFlash('success', [
                        'message'   => Yii::t('app/flash-messages', 'Index_ChangeNameForm_success'),
                        'ttl'       => FLASH_MESSAGES_TTL,
                        'showClose' => false,
                        'alert_action' => 'admin-panel.ChangeNameForm',
                    ]);
                } else {
                    Yii::$app->getSession()->setFlash('error', [
                        'message' => Yii::t('app/flash-messages', 'Index_ChangeNameForm_error'),
                        'ttl'       => FLASH_MESSAGES_TTL,
                        'showClose' => false,
                        'alert_action' => 'admin-panel.ChangeNameForm',
                    ]);
                }
            }
            return $this->redirect(['index'/*, 'tab'=>'1'*/]);
        }

        /** Смена oo-адреса */
        $model_change_oo_address = new ChangeOoAddressForm();
        if ($model_change_oo_address->load(Yii::$app->request->post()) && $model_change_oo_address->validate()) {

                if ($model_change_oo_address->changeOoAddress($this->User)) {
                    Yii::$app->getSession()->setFlash('success', [
                        'message'   => Yii::t('app/flash-messages', 'Index_ChangeOoAddressForm_success'),
                        'ttl'       => FLASH_MESSAGES_TTL,
                        'showClose' => false,
                        //'alert_action' => 'admin-panel.ChangeNameForm',
                    ]);
                } else {
                    Yii::$app->getSession()->setFlash('error', [
                        'message' => Yii::t('app/flash-messages', 'Index_ChangeOoAddressForm__error'),
                        'ttl'       => FLASH_MESSAGES_TTL,
                        'showClose' => false,
                        //'alert_action' => 'admin-panel.ChangeNameForm',
                    ]);
                }
            return $this->redirect(['index'/*, 'tab'=>'1'*/]);
        }

        /** */
        $ColleagueAddForm       = new ShareElementForm(['colleague_email']);

        $ColleaguesSearchModel  = new ColleaguesSearch();
        $ColleaguesSearchModel->owner_user_id = $this->User->user_id;
        $dataProviderColleagues = $ColleaguesSearchModel->getListColleagues();

        if (isset($_GET['tab']) && $_GET['tab'] == 3) {
            $ReportsSearchModel = new ColleaguesReportsSearch();
            $dataProviderReports = $ReportsSearchModel->search(Yii::$app->request->queryParams);
            $current_count_unread_reports = ColleaguesReportsSearch::countNewReports();
        } else {
            $ReportsSearchModel = false;
            $dataProviderReports = false;
            $current_count_unread_reports = 0;
        }

        $ServerLicensesSearchModel = new ServerLicensesSearch();
        $dataProviderServerLicenses = $ServerLicensesSearchModel->getListServerNodes($this->User->user_id);

        return $this->render("index", [
            'model_change_name'          => $model_change_name,
            'model_change_oo_address'    => $model_change_oo_address,
            'ColleaguesSearchModel'      => $ColleaguesSearchModel,
            'dataProviderColleagues'     => $dataProviderColleagues,
            'ColleagueAddForm'           => $ColleagueAddForm,
            'ReportsSearchModel'         => $ReportsSearchModel,
            'dataProviderReports'        => $dataProviderReports,
            'current_count_unread_reports' => $current_count_unread_reports,
            'ServerLicensesSearchModel'  => $ServerLicensesSearchModel,
            'dataProviderServerLicenses' => $dataProviderServerLicenses,
            'Server'                     => Servers::getSignal(),
            'site_token'                 => NodeApi::site_token_key(),
            'license_count_info'         => UserLicenses::getLicenseCountInfoForUser($this->User->user_id),
            'server_license_count_info'  => UserServerLicenses::getLicenseCountInfoForUser($this->User->user_id),
            'admin'                      => $this->User,
        ]);
    }

    /**
     * Добавляет нового юзера в список коллег и редиректит
     * на страницу с настройками этого нового коллеги
     * @return \yii\web\Response
     */
    public function actionColleagueAdd()
    {
        $model = new ShareElementForm(['colleague_email']);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $ret = $model->adminPanelCreateNullCollaboration($this->User);

            Yii::$app->session->setFlash($ret['type'], [
                'message' => Yii::t('app/flash-messages', $ret['info']),
                'ttl' => (isset($ret['ttl'])? $ret['ttl'] : FLASH_MESSAGES_TTL),
                'showClose' => (isset($ret['showClose']) ? true : null),
                'alert_action' => 'actionColleagueAdd',
            ]);

            if ($ret['status']) {
                return $this->redirect(['colleague-manage', 'colleague_email' => $model->colleague_email]);
            } else {
                return $this->redirect(['index', 'tab'=>'2']);
            }
        }
        return $this->redirect(['index', 'tab'=>'2']);
    }

    /**
     * Удаляет коллегу из всех коллабораций и редиректит
     * на страницу со списком оставшихся коллег
     * @return Response
     */
    public function actionColleagueDelete()
    {
        if (Yii::$app->request->get('colleague_email')) {
            $model = new ShareElementForm(['colleague_email']);
            $model->colleague_email = Yii::$app->request->get('colleague_email');
            $model->owner_user_id = $this->User->user_id;
            if ($model->validate()) {

                $model->adminPanelColleagueDelete();
                Yii::$app->session->setFlash('success', [
                    'message' => 'Successfully removed from list',
                    'ttl' => FLASH_MESSAGES_TTL,
                    'alert_action' => 'actionColleagueDelete',
                ]);
                return $this->redirect(['index', 'tab' => '2']);
            }
        }
        Yii::$app->session->setFlash('error', [
            'message' => 'Wrong params',
            'ttl' => FLASH_MESSAGES_TTL,
            'alert_action' => 'actionColleagueDelete',
        ]);
        return $this->redirect(['index', 'tab' => '2']);
    }

    /**
     * Отображает страницу управления настройками отдельного коллеги
     * @return string|\yii\web\Response
     */
    public function actionColleagueManage()
    {
        //if ( && $model->validate())
        if (Yii::$app->request->get('colleague_email')) {
            $model = new ColleaguesSearch();
            $model->colleague_email = Yii::$app->request->get('colleague_email');
            $model->owner_user_id = $this->User->user_id;
            if ($model->validate()) {
                $colleague = $model->getColleagueInfo();
                if ($colleague) {
                    $dataProviderFolderList = $model->getColleagueListFolder();
                    //$availableFolderList = $model->getAvailableFolderList($model->colleague_email);
                    if (!sizeof($dataProviderFolderList->allModels)) {
                        Yii::$app->session->setFlash('warning', [
                            'class' => 'hide-on-add-folder',
                            'message' => Yii::t('app/flash-messages', 'To_complete_invitation_select_folder'),
                            'ttl' => FLASH_MESSAGES_TTL,
                            'showClose' => true,
                            'alert_action' => 'actionColleagueManage',
                        ]);
                    }

                    return $this->render("colleagueManage", [
                        'colleague' => (is_object($colleague))
                            ? UserColleagues::prepareColleagueData($colleague)
                            : UserColleagues::prepareColleagueDataFromArray($colleague),
                        'colleague_user'         => (is_object($colleague) && $colleague->hasMethod('getUser')) ? $colleague->getUser()->one() : null,
                        'dataProviderFolderList' => $dataProviderFolderList,
                        //'availableFolderList'    => $availableFolderList,
                        'Server' => Servers::getSignal(),
                        'site_token' => NodeApi::site_token_key(),
                    ]);
                }
            }
        }

        return $this->redirect(['index', 'tab'=>'2']);
    }

    /**
     * Возвращает джсон строку со списком доступных для
     * коллаборации дирректорий (для текущего коллеги)
     * @return string|\yii\web\Response
     */
    public function actionAvailableFolders()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new ColleaguesSearch();
        $model->colleague_email = Yii::$app->request->get('colleague_email');
        $model->owner_user_id = $this->User->user_id;
        if ($model->validate()) {
            $colleague = $model->getColleagueInfo();
            if ($colleague) {
                $availableFolderList = $model->getAvailableFolderList();
                return [
                    'status' => true,
                    'data'   => $availableFolderList,
                ];
            }
        }
        return [
            'status' => false,
            'info'   => 'System error.',
            'debug'  => $model->getErrors(),
        ];
    }

    /**
     * Меняет настройки доступа для конкретного коллеги
     * возвращает строку джсон с результатом работы
     * @return array
     */
    public function actionColleagueChange()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $_POST['owner_user_id'] = Yii::$app->user->identity->getId();
        $model = new ShareElementForm(['file_uuid', 'access_type', 'action', 'owner_user_id']);
        if ($model->load(['ShareElementForm' => $_POST], 'ShareElementForm') && $model->validate()) {
            return $model->changeCollaboration();
        }
        return [
            'status' => false,
            'info'   => 'System error.',
            'debug'  => $model->getErrors(),
        ];
    }

    /**
     * @return array|null
     * @throws \yii\base\Exception
     */
    public function actionFolderSelect()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        //$user_id = Yii::$app->user->identity->getId();
        if (isset($_GET['operation'], $_GET['node_id'])) {
            $data['tree'] = $_GET;
            $data['tree']['user_id'] = $this->User->getId();
            //var_dump($data['tree']); exit;
            $model = new JsTreeMy();
            if ($model->load($data, 'tree')) {
                return $model->processTree();
            } else {
                return $model->getErrors();
            }
        }
    }

    /**
     * Освобождает серверную лицензию от ноды
     * Возвращает джсон строку с результатом
     * @param $node_id
     * @return array|null
     */
    public function actionReleaseServerLicense($node_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $node_id = intval($node_id);

        /* проверяем что такая нода (node_id) есть */
        $UserNode = UserNode::findIdentity($node_id);
        if (!$UserNode) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_NODE_NOT_FOUND,
                'info'    => "UserNode not found",
            ];
        }

        /* Проверим что нода серверная */
        if ($UserNode->is_server != UserNode::IS_SERVER) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => "UserNode is not server node",
            ];
        }

        /* если $this->User->user_id не совпадает с $UserNode->user_id это значит что нода должна принадлежать коллеге и нужно это проверить */
        if ($this->User->user_id != $UserNode->user_id) {

            $query = "SELECT user_id FROM dl_user_colleagues
                      WHERE (user_id = :node_user_id)
                      AND (user_id IS NOT NULL)
                      AND (collaboration_id IN (
                        SELECT collaboration_id
                        FROM dl_user_collaborations
                        WHERE user_id = :business_admin_user_id
                      ))
                      GROUP BY user_id";
            $res = Yii::$app->db->createCommand($query, [
                'node_user_id' => $UserNode->user_id,
                'business_admin_user_id' => $this->User->user_id,
            ])->queryOne();
            if (!is_array($res) || !sizeof($res)) {
                return [
                    'result'  => "error",
                    'errcode' => NodeApi::ERROR_WRONG_DATA,
                    'info'    => "UserNode is not owned by you or your colleagues",
                ];
            }
        }

        /* Создаем модель юзера ноду которого освобождаем от лицензии */
        $UserColleague = Users::findIdentity($UserNode->user_id);
        if (!$UserColleague) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_USER_NOT_FOUND,
                'info'    => "UserColleague not found",
            ];
        }

        /* Сначала логаутим ноду */
        $model = new NodeApi(['node_id', 'target_node_id', 'action_type']);
        if (!$model->load(['NodeApi' => [
                'node_id'        => $node_id,
                'target_node_id' => $node_id,
                'action_type'    => RemoteActions::TYPE_LOGOUT,
            ]]) || !$model->validate()) {
            return [
                'result'  => "error",
                'errcode' => NodeApi::ERROR_WRONG_DATA,
                'info'    => $model->getErrors()
            ];
        }
        $UserNodeFM = NodeApi::registerNodeFM($UserColleague);
        $model->execute_remote_action($UserNodeFM, $UserColleague);

        /* а тут хайдим ноду (в методе хайда она автоматически освобождает серверную лицензию) */
        $model->hideNode($UserColleague, $this->User->user_id);

        return [
            'result' => "success",
            'data'   => UserServerLicenses::getLicenseCountInfoForUser($this->User->user_id),
        ];
    }
}
