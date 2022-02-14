<?php

namespace backend\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\components\SController;
use common\models\MailTemplates;
use common\models\Languages;
use backend\models\search\MailTemplatesSearch;

/**
 * MailTemplatesController implements the CRUD actions for MailTemplates model.
 */
class MailTemplatesController extends SController
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

        $searchModel = new MailTemplatesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->$render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all MailTemplates models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->getIndexContent();
    }

    /**
     * Displays a single MailTemplates model.
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
     * Creates a new MailTemplates model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        return false;
        $model = new MailTemplates();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->template_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing MailTemplates model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->template_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing MailTemplates model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        return false;
        /*
        $this->findModel($id)->delete();

        if (Yii::$app->getRequest()->isAjax) {
            unset($_GET['id']);
            return $this->getIndexContent('renderPartial');
        }

        return $this->redirect(['index']);
        */
    }

    public function actionInitNew()
    {
        $template_key  = MailTemplates::keyLabels();
        $template_lang = Languages::langLabels();

        $flash = "";
        if (is_array($template_key) && is_array($template_lang)) {
            foreach ($template_key as $k1 => $v1) {
                foreach ($template_lang as $k2 => $v2) {
                    if (!MailTemplates::findOne(['template_key' => $k1, 'template_lang' => $k2])) {
                        $tpl = new MailTemplates();
                        $tpl->template_key        = $k1;
                        $tpl->template_lang       = $k2;
                        $tpl->template_from_email = 'robot@null.null';
                        $tpl->template_from_name  = 'null';
                        $tpl->template_subject    = MailTemplates::keyLabel($k1);
                        $tpl->template_body_html  = MailTemplates::keyLabel($k1);
                        $tpl->template_body_text  = MailTemplates::keyLabel($k1);
                        $tpl->save(false);
                        $flash .= "Добавлен шаблон {$k1} - {$k2};<br />";
                    }
                }
            }
        }
        if ($flash) {
            Yii::$app->session->setFlash('success', $flash);
        } else {
            Yii::$app->session->setFlash('danger', 'Нет новых шаблонов для добавления.');
        }
        return $this->redirect(['index']);
    }

    /**
     * Finds the MailTemplates model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return MailTemplates the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MailTemplates::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
