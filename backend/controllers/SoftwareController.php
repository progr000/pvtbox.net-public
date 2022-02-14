<?php
/****DELETE-IT-FILE-IN-SH****/
namespace backend\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\bootstrap\ActiveForm;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\helpers\Json;
use backend\components\SController;
use common\models\Software;
use backend\models\search\SoftwareSearch;

/**
 * SoftwareController implements the CRUD actions for Software model.
 */
class SoftwareController extends SController
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
        if (!in_array($render, ['render', 'renderPartial'])) { $render = 'render'; }

        $searchModel = new SoftwareSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->$render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modelVersion' => new Software(),
        ]);
    }

    /**
     * Lists all Software models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->getIndexContent();
    }

    /**
     * Displays a single Software model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Software model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Software();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->software_file = UploadedFile::getInstance($model, 'software_file');
            if ($model->validate()) {
                    if ($model->save()) {
                        //return $this->redirect(['view', 'id' => $model->software_id]);
                        return $this->redirect(['index']);
                    } else {
                        Yii::$app->session->setFlash('danger', 'Failed save model.');
                    }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Software model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $model->software_file = UploadedFile::getInstance($model, 'software_file');
            if ($model->validate()) {
                    //var_dump($model->software_status); exit;
                    if ($model->save()) {
                        //return $this->redirect(['view', 'id' => $model->software_id]);
                        return $this->redirect(['index']);
                    } else {
                        Yii::$app->session->setFlash('danger', 'Failed save model.');
                    }
            }
        }

        return $this->render('update', [
                'model' => $model,
        ]);
    }

    /**
     *
     */
    public function actionUpdateVersionForAll()
    {
        $model = new Software();
        
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model, ['software_version']);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate(['software_version'])) {
            Software::updateAll(['software_version' => $model->software_version]);
            Software::invalidateCache();
            Yii::$app->session->setFlash('success', 'Successfully updated.');
        } else {
            Yii::$app->session->setFlash('danger', Json::encode($model->getErrors()));
        }
        return $this->redirect(['index']);
    }

    /**
     * Deletes an existing Software model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $software = $this->findModel($id);
        $software->delete();

        if (Yii::$app->getRequest()->isAjax) {
            unset($_GET['id']);
            return $this->getIndexContent('renderPartial');
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Software model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Software the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Software::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
