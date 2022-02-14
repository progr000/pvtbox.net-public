<?php
/****DELETE-IT-FILE-IN-SH****/
namespace backend\controllers;

use Yii;
use yii\web\Response;
use backend\components\SController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\SelfHostUsers;
use backend\models\search\SelfHostUsersSearch;

/**
 * SelfHostUsersController implements the CRUD actions for SelfHostUsers model.
 */
class SelfHostUsersController extends SController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => $this->checkAccess(),
                        'roles' => ['@'],
                        'denyCallback' => function ($rule, $action) {
                            return $this->accessErrorRedirect();
                        }
                    ],
                ],
            ],
        ];
    }

    /**
     * @return bool
     */
    protected function checkAccess()
    {
        if (Yii::$app->params['self_hosted']) {
            return false;
        }
        return parent::checkAccess();
    }

    /**
     * @param string $render   render | renderPartial
     * @return mixed
     */
    protected function getIndexContent($render = 'render')
    {
        //var_dump(Yii::$app->request->queryParams);
        if (!in_array($render, ['render', 'renderPartial'])) { $render = 'render'; }

        $searchModel = new SelfHostUsersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->$render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all SelfHostUsers models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->getIndexContent();
    }

    /**
     * Displays a single SelfHostUsers model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new SelfHostUsers model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
//    public function actionCreate()
//    {
//        $model = new SelfHostUsers();
//
//        if ($model->load(Yii::$app->request->post()) && $model->save()) {
//            return $this->redirect(['view', 'id' => $model->shu_id]);
//        }
//
//        return $this->render('create', [
//            'model' => $model,
//        ]);
//    }

    /**
     * Updates an existing SelfHostUsers model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->shu_id]);
        }

        return $this->render('update', [
            'user' => $model,
        ]);
    }

    /**
     * Deletes an existing SelfHostUsers model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Changes user_status in an Users model/
     * @param string $id
     * @return mixed
     */
    public function actionChangeStatus($id)
    {
        $model = $this->findModel($id);
        $model->shu_status = $model->shu_status == SelfHostUsers::STATUS_ACTIVE ? SelfHostUsers::STATUS_LOCKED : SelfHostUsers::STATUS_ACTIVE;
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
     * Changes user_status in an Users model/
     * @param string $shu_id
     * @return mixed
     */
    public function actionCheckLog($shu_id)
    {
        $model = $this->findModel($shu_id);

        $searchModel = new SelfHostUsersSearch();
        $dataProvider = $searchModel->searchShuCheckLog($model->shu_id);

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'status' => true,
            'data'   => $this->renderPartial('shu-check-log', [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]),
        ];
    }

    /**
     * Finds the SelfHostUsers model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SelfHostUsers the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SelfHostUsers::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
