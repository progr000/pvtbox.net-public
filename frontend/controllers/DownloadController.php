<?php
namespace frontend\controllers;

use Yii;
use yii\filters\AccessControl;
use common\models\Software;
use frontend\components\SController;
use frontend\models\forms\LoginForm;
use yii\web\BadRequestHttpException;

/**
 * Download controller
 *
 * @property \frontend\models\forms\LoginForm $model_login
 */
class DownloadController extends SController
{
    /** @var \frontend\models\forms\LoginForm $model_login */
    public $model_login;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (file_exists(Yii::getAlias('@frontend').'/themes/' . DESIGN_THEME . '/layouts/download.php')) {
            $this->layout = 'download';
        }
        $this->model_login  = new LoginForm();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'index',
                        ],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => [
                            'index',
                            'install',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('download', [
            'software' => Software::findOtherVersionSoftware(),
        ]);
    }

    /**
     * Display install page
     *
     * @return string
     */
    public function actionInstall()
    {
        return $this->render('install');
    }
}
