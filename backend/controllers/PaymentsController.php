<?php
/****DELETE-IT-FILE-IN-SH****/
namespace backend\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\components\SController;
use common\models\UserPayments;
use backend\models\search\UserPaymentsSearch;

/**
 * PaymentsController implements the CRUD actions for UserPayments model.
 */
class PaymentsController extends SController
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

        $searchModel = new UserPaymentsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->$render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all UserPayments models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->getIndexContent();
    }

    /**
     * Displays a single UserPayments model.
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
     * Finds the UserPayments model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return UserPayments the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = UserPayments::find()->with('user')->where(['pay_id' => $id])->one();
        //var_dump($model);exit;
        //if (($model = UserPayments::findOne($id)) !== null) {
        if ($model) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
