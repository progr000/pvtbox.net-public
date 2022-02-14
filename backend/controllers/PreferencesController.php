<?php

namespace backend\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\components\SController;
use common\models\Preferences;
use backend\models\search\PreferencesSearch;

/**
 * PreferencesController implements the CRUD actions for Preferences model.
 */
class PreferencesController extends SController
{
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
     * Lists all Preferences models.
     * @return mixed
     */
    public function actionIndex()
    {
        $pref_list = Yii::$app->request->post('PreferencesSearch');

        if (is_array($pref_list)) {
            //var_dump($pref_list);exit;
            $tab = Preferences::CATEGORY_BASE;

            foreach ($pref_list as $key=>$val) {
                $pref = Preferences::findByKey($key);

                if ($pref) {
                    if ($pref->pref_value !== $val['pref_value']) {
                        $pref->pref_value = $val['pref_value'];
                        $tab = $pref->pref_category;
                        $pref->save(false);
                    }
                }
            }

            Yii::$app->session->setFlash('success', 'Все настройки успешно сохранены!');
            return $this->redirect(['index', 'tab' => $tab]);
        }

        return $this->render('index', [
            'preferences' => PreferencesSearch::getPrefArray(),
        ]);
    }

    /**
     * Lists all Preferences models.
     * @return mixed
     */
    public function actionList()
    {
        $searchModel = new PreferencesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Preferences model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Preferences();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['list']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Preferences model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['list']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Preferences model.
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
     * Finds the Preferences model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Preferences the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Preferences::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
