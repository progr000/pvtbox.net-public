<?php

namespace backend\controllers;

use Yii;
use yii\helpers\Json;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\components\SController;
use common\models\Maintenance;

/**
 * PreferencesController implements the CRUD actions for Preferences model.
 */
class MaintenanceController extends SController
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
        $model = new Maintenance();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->setMaintenance()) {
                Yii::$app->session->setFlash('success', 'Saved!');
            } else {
                Yii::$app->session->setFlash('error', Json::encode($model->getErrors()));
            }
            return $this->redirect(['index']);
        }

        return $this->render('index', [
            'model' => Maintenance::getMaintenance(),
        ]);
    }
}
