<?php

namespace backend\controllers;

use Yii;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use backend\components\SController;
use common\models\Pages;
use backend\models\search\PagesSearch;

/**
 * PagesController implements the CRUD actions for Pages model.
 */
class PagesController extends SController
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, ['upload'])) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
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

        $searchModel = new PagesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->$render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Pages models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->getIndexContent();
    }

    /**
     * Displays a single Pages model.
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
     * Creates a new Pages model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Pages();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            //return $this->redirect(['view', 'id' => $model->page_id]);
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Pages model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            //return $this->redirect(['view', 'id' => $model->page_id]);
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Pages model.
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
     * Finds the Pages model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Pages the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Pages::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpload()
    {
        if (Yii::$app->request->isPost) {
            $uploadedFile = UploadedFile::getInstanceByName('upload');

            $mime = FileHelper::getMimeType($uploadedFile->tempName);
            $file = time()."_".$uploadedFile->name;

            $url = Yii::getAlias('@frontendWeb').'/uploads/pages/'.$file;
            $uploadPath = Yii::getAlias('@webroot').'/uploads/pages/';
            if (!file_exists($uploadPath)) { FileHelper::createDirectory($uploadPath, 0777, true); }
            //extensive suitability check before doing anything with the file…
            if ($uploadedFile==null)
            {
                $message = "No file uploaded.";
            }
            else if ($uploadedFile->size == 0)
            {
                $message = "The file is of zero length.";
            }
            else if ($mime!="image/jpeg" && $mime!="image/png")
            {
                $message = "The image must be in either JPG or PNG format. Please upload a JPG or PNG instead.";
            }
            else if ($uploadedFile->tempName==null)
            {
                $message = "You may be attempting to hack our server. We're on to you; expect a knock on the door sometime soon.";
            }
            else {
                $message = "";
                $move = $uploadedFile->saveAs($uploadPath.$file);
                if(!$move)
                {
                    $message = "Error moving uploaded file. Check the script is granted Read/Write/Modify permissions.";
                }
            }
            $funcNum = $_GET['CKEditorFuncNum'] ;
            echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";
        }
    }
}
