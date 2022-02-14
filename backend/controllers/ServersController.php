<?php

namespace backend\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\components\SController;
use backend\models\Admins;
use backend\models\search\ServersSearch;
use common\models\Servers;

/**
 * ServersController implements the CRUD actions for Servers model.
 */
class ServersController extends SController
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
        if (!$this->Admins)
            return false;

        if ($this->Admins->admin_role == Admins::ROLE_SELLER)
            return false;

        if ($this->Admins->admin_role == Admins::ROLE_ROOT)
            return true;

        if ($this->Admins->admin_role == Admins::ROLE_READER)
        {
            if (in_array($this->action->id, [
                'index',
                'view',
            ])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \yii\web\Response
     */
    protected function accessErrorRedirect()
    {
        if ($this->Admins->admin_role == Admins::ROLE_READER) {
            return $this->redirect('/servers');
        } else {
            return $this->redirect('/users');
        }
    }

    /**
     * @param string $render   render | renderPartial
     * @return mixed
     */
    protected function getIndexContent($render = 'render')
    {
        if (!in_array($render, ['render', 'renderPartial'])) { $render = 'render'; }

        $searchModel = new ServersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->$render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Servers models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->getIndexContent();
    }

    /**
     * Displays a single Servers model.
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
     * Creates a new Servers model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Servers();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Servers model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Servers model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        if (Yii::$app->getRequest()->isAjax) {
            unset($_GET['id']);
            return $this->getIndexContent('renderPartial');
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Servers model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Servers the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Servers::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
