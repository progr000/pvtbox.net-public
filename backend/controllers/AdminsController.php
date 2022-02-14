<?php

namespace backend\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\base\DynamicModel;
use common\models\Users;
use backend\components\SController;
use backend\models\Admins;
use backend\models\search\AdminsSearch;

/**
 * AdminsController implements the CRUD actions for Admins model.
 */
class AdminsController extends SController
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
     * Lists all Admins models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AdminsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Admins model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Admins();

        $password_model = new DynamicModel(['password', 'password_repeat']);
        $password_model
            ->addRule(['password', 'password_repeat'], 'required')
            ->addRule(['password'], 'string', ['min' => 6])
            ->addRule(['password'], 'match', ['pattern' => Users::PASSWORD_PATTERN, 'message' => "For the password you can use only letters of the Latin alphabet, digits and symbols !@#$%^&*()_+-=[]{}<>;:\"'\\|?/.,"])
            ->addRule(['password_repeat'], 'compare', ['compareAttribute' => 'password']);

        if ($password_model->load(Yii::$app->request->post()) && $password_model->validate()) {
            if ($model->load(Yii::$app->request->post())) {
                $model->setPassword($password_model->password);
                $model->generateAuthKey();
                if ($model->save()) {
                    return $this->redirect(['index']);
                }
            }
        }

        return $this->render('create', [
            'model'   => $model,
            'password_model' => $password_model,
            'current' => $this->Admins,
        ]);
    }

    /**
     * Updates an existing Admins model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $password_model = new DynamicModel(['password', 'password_repeat', 'current_password']);
        $password_model
            ->addRule(['current_password', 'password'], 'string', ['min' => 6])
            ->addRule(['password'], 'match', ['pattern' => Users::PASSWORD_PATTERN, 'message' => "For the password you can use only letters of the Latin alphabet, digits and symbols !@#$%^&*()_+-=[]{}<>;:\"'\\|?/.,"])
            ->addRule(['password_repeat'], 'compare', ['compareAttribute' => 'password', 'skipOnEmpty' => false]);
        if ($this->Admins->admin_id == $id) {
            $password_model->addRule(['current_password'], 'required');
        }



        if ($model->load(Yii::$app->request->post())) {

            /* если это текущий пользователь */
            if ($this->Admins->admin_id == $id) {
                if ($this->Admins->admin_role != $model->admin_role) {
                    Yii::$app->session->setFlash('error', "You can't change your role.");
                    return $this->redirect(['update', 'id' => $id]);
                }
                if ($this->Admins->admin_status != $model->admin_status) {
                    Yii::$app->session->setFlash('error', "You can't change your status.");
                    return $this->redirect(['update', 'id' => $id]);
                }
                if ($this->Admins->admin_email != $model->admin_email) {
                    Yii::$app->session->setFlash('error', "You can't change your email.");
                    return $this->redirect(['update', 'id' => $id]);
                }

                if ($password_model->load(Yii::$app->request->post())) {
                    if ($model->validatePassword($password_model->current_password)) {
                        if (mb_strlen($password_model->password)) {
                            $model->setPassword($password_model->password);
                            $model->generateAuthKey();
                        }
                    } else {
                        Yii::$app->session->setFlash('error', "Wrong current password.");
                        return $this->redirect(['update', 'id' => $id]);
                    }
                }

            /* если это другой юзер а не текущий */
            } else {
                if ($password_model->load(Yii::$app->request->post()) && $password_model->validate()) {
                    if (mb_strlen($password_model->password)) {
                        $model->setPassword($password_model->password);
                        $model->generateAuthKey();
                    }
                }
            }

            if ($model->save()) {
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', [
            'model'   => $model,
            'password_model' => $password_model,
            'current' => $this->Admins,
        ]);
    }

    /**
     * Deletes an existing Admins model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if ($this->Admins->admin_id != $id) {
            $this->findModel($id)->delete();
        } else {
            Yii::$app->session->setFlash('error', "You can't delete yourself.");
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Admins model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Admins the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Admins::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
