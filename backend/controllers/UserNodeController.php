<?php

namespace backend\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\components\SController;
use common\models\UserNode;
use backend\models\search\UserNodeSearch;

/**
 * UserNodeController implements the CRUD actions for UserAlertsLog model.
 */
class UserNodeController extends SController
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
     * Lists all UserNode models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserNodeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Finds the UserAlertsLog model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return UserAlertsLog the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = UserNode::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
