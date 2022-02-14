<?php

namespace backend\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\components\SController;
use common\models\QueuedEvents;
use backend\models\search\QueuedEventsSearch;

/**
 * QueuedController implements the CRUD actions for QueuedEvents model.
 */
class QueuedController extends SController
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
     * Lists all QueuedEvents models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new QueuedEventsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Finds the QueuedEvents model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $event_uuid
     * @param integer $user_id
     * @return QueuedEvents the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($event_uuid, $user_id)
    {
        if (($model = QueuedEvents::findOne(['event_uuid' => $event_uuid, 'user_id' => $user_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
