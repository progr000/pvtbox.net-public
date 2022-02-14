<?php
namespace frontend\controllers;

use Yii;
use yii\caching\TagDependency;
use yii\web\NotFoundHttpException;
use common\models\Pages;
use frontend\components\SController;
use frontend\models\forms\LoginForm;

/**
 * Page controller
 *
 * @property \frontend\models\forms\LoginForm $model_login
 */
class PageController extends SController
{
    /** @var \frontend\models\forms\LoginForm $model_login */
    public $model_login;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->model_login  = new LoginForm();
    }

    /**
     * Display pages from DataBase if its exists.
     * @param null $alias
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionIndex($alias = null)
    {
        $model = $this->loadModel($alias);
        return $this->render('index', [
            'model' => $model
        ]);
    }

    /**
     * Find model for page
     * @param $alias
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\web\NotFoundHttpException
     */
    public function loadModel($alias)
    {

        $model = Pages::getDb()->cache(
            function($db) use($alias) {
                $Page = Pages::find()->where('(page_alias = :name) AND (page_lang = :lang) AND (page_status = :status)', [
                    ':name'   => $alias,
                    ':status' => Pages::STATUS_ACTIVE,
                    ':lang'   => Yii::$app->language
                ])->one();
                if (!$Page) {
                    $Page = Pages::find()->where('(page_alias = :name) AND (page_lang = :lang) AND (page_status = :status)', [
                        ':name'   => $alias,
                        ':status' => Pages::STATUS_ACTIVE,
                        ':lang'   => 'en'
                    ])->one();
                }
                return $Page;
                //return Pages::find()->where('(page_alias = :name) AND (page_lang = :lang)', [':name' => $alias, ':lang' => Yii::$app->language])->one();
            },
            null,
            new TagDependency(['tags'  => md5( 'page' . $alias . Yii::$app->language )])
        );

        /*
        $model = Pages::find()->where('(page_alias = :name) AND (page_lang = :lang)', [':name' => $alias, ':lang' => Yii::$app->language])->one();
        if (!$model) {
            $model = Pages::find()->where('(page_alias = :name) AND (page_lang = :lang)', [':name' => $alias, ':lang' => 'en'])->one();
        }
        */
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

