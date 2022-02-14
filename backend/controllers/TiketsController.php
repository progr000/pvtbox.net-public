<?php
/****DELETE-IT-FILE-IN-SH****/
namespace backend\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use backend\components\SController;
use common\models\Tikets;
use backend\models\search\TiketsSearch;
use backend\models\forms\AnswerTiketForm;

/**
 * TiketsController implements the CRUD actions for Tikets model.
 */
class TiketsController extends SController
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
     * @param string $render   render | renderPartial
     * @return mixed
     */
    protected function getIndexContent($render = 'render')
    {
        if (!in_array($render, ['render', 'renderPartial'])) { $render = 'render'; }

        $searchModel = new TiketsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->$render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Tikets models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->getIndexContent();
    }

    /**
     * Deletes an existing Tikets model.
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
     * Finds the Tikets model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Tikets the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Tikets::findOne(['tiket_id' => $id])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Displays a list TiketsMessages model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($model) {

            $searchModel = new TiketsSearch();
            $dataProvider = $searchModel->viewTiketsMessages($model);

            $AnswerTiketForm = new AnswerTiketForm();
            $AnswerTiketForm->tiket_id = $id;

            return $this->render('view', [
                'tiket' => $model,
                'dataProvider' => $dataProvider,
                'AnswerTiketForm' => $AnswerTiketForm,
            ]);
        }
    }

    /**
     * @return \yii\web\Response
     */
    public function actionAnswer()
    {
        $model = new AnswerTiketForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($this->findModel($model->tiket_id)) {
                if ($model->sendEmail()) {
                    Yii::$app->session->setFlash('success', 'Ответ успешно отправлен.');
                } else {
                    Yii::$app->session->setFlash('danger', 'There was an error sending email.');
                }
            }
        }
        return $this->redirect(['/tikets/view', 'id' => $model->tiket_id]);
    }

    /**
     * @return string
     */
    public function actionCountUnreadTikets()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return TiketsSearch::countUnreadTikets();
    }
}
