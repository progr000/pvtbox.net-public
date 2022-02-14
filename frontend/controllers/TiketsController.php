<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\Tikets;
use frontend\components\SController;
use frontend\models\search\TiketsSearch;
use frontend\models\forms\AnswerTiketForm;
use frontend\models\forms\CreateTiketForm;

/**
 * TiketsController implements the CRUD actions for Tikets model.
 */
class TiketsController extends SController
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

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
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Tikets models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TiketsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
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
        if (($model = Tikets::findOne(['tiket_id' => $id, 'user_id' => Yii::$app->user->identity->getId()])) !== null) {
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
     * @return mixed
     */
    public function actionAnswer()
    {
        $model = new AnswerTiketForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($this->findModel($model->tiket_id)) {
                if ($model->sendEmail()) {
                    Yii::$app->session->setFlash('success', Yii::t('app/flash-messages', 'Answer_success'));
                } else {
                    Yii::$app->session->setFlash('error', Yii::t('app/flash-messages', 'Answer_error'));
                }
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app/flash-messages', 'Answer_secure_error'));
            }
        }
        return $this->redirect(['/tikets/view', 'id' => $model->tiket_id]);
    }

    /**
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CreateTiketForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if (($tiket_id = $model->sendEmail()) !== false) {
                Yii::$app->session->setFlash('success', Yii::t('app/flash-messages', 'Create_success'));
                return $this->redirect(['/tikets/view', 'id' => $tiket_id]);
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app/flash-messages', 'Create_error'));
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);

    }
}
