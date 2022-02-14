<?php

namespace frontend\modules\download\controllers;

use Yii;
use frontend\components\SController;
use yii\base\DynamicModel;
use yii\data\ArrayDataProvider;
use common\models\UserFiles;
use common\models\UserFileEvents;
use frontend\models\forms\LoginForm;

/**
 * Default controller for the download module
 */
class FolderController extends SController
{
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
     * @inheritdoc
     */
    /*
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
        ];
    }
    */

    /**
     * Позволяет приходить запросам на этот скрипт и акшены перечисленные в массиве
     * с других доменов а не только с домена где стоит этот скрипт
     *
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        /** @var array $share */
        //var_dump($_GET); exit;

        $model = new DynamicModel(['share_group_hash', 'file_id']);
        $model->addRule(['share_group_hash'], 'required');
        $model->addRule(['share_group_hash'], 'string', ['length' => 32]);
        $model->addRule(['file_id'], 'integer', ['min' => 0]);
        //$model->addRule(['file_id'], 'default', ['value' => 0]);

        $data[$model->formName()] = Yii::$app->request->get();
        if ($model->load($data) && $model->validate()) {

            if (!$model->file_id) {
                $where = ['share_hash' => $model->share_group_hash];
            } else {
                $where = ['share_group_hash' => $model->share_group_hash, 'file_id' => $model->file_id];
            }
            /** @var \common\models\UserFiles $shareParent */
            $shareParent = UserFiles::find()
                ->where($where)
                ->andWhere("(share_lifetime > :share_lifetime) OR (share_lifetime IS NULL)", ['share_lifetime'  => date(SQL_DATE_FORMAT)])
                ->andWhere("last_event_type <> :last_event_type", ['last_event_type' => UserFileEvents::TYPE_DELETE])
                ->one();
            //var_dump($shareParent); exit;
            if ($shareParent) {
                $shareChildren = UserFiles::find()
                    ->asArray()
                    ->select([
                        'file_id',
                        'share_hash',
                        'share_group_hash',
                        'file_name',
                        'file_size',
                        'file_parent_id',
                        'is_folder',
                    ])
                    ->where([
                        'share_group_hash' => $shareParent->share_group_hash,
                        'file_parent_id' => $shareParent->file_id,
                    ])
                    ->andWhere("(share_lifetime > :share_lifetime) OR (share_lifetime IS NULL)", ['share_lifetime'  => date(SQL_DATE_FORMAT)])
                    /*
                    ->andWhere("(share_lifetime > :share_lifetime) OR ((share_lifetime IS NULL) AND (share_ttl_info != :TTL_IMMEDIATELY_DOWNLOADED))", [
                        'share_lifetime'             => date(SQL_DATE_FORMAT),
                        'TTL_IMMEDIATELY_DOWNLOADED' => UserFiles::TTL_IMMEDIATELY_DOWNLOADED,
                    ])
                    */
                    ->andWhere("last_event_type <> :last_event_type", ['last_event_type' => UserFileEvents::TYPE_DELETE])
                    ->orderBy(['is_folder' => SORT_DESC, 'file_name' => SORT_ASC])
                    ->all();

                    //var_dump($shareChildren);exit;
                    if (!$shareChildren) {
                        $shareChildren = [];
                    }
                    //var_dump($shareChildren);
                    //if (sizeof($shareChildren) > 0 || $model->file_id) {
                    //var_dump($shareParent->share_group_hash);
                    //var_dump($model->share_group_hash);
                    //exit;
                    if ($shareParent->file_parent_id) {
                        $parentShareParent = UserFiles::findOne([
                            'file_id'          => $shareParent->file_parent_id,
                            'share_group_hash' => $model->share_group_hash,
                        ]);
                        if ($parentShareParent) {
                            $share_top = ['file_id' => null, 'share_hash' => '', 'share_group_hash' => $model->share_group_hash, 'file_name' => '.', 'is_folder' => UserFiles::TYPE_TOP_FOLDER, 'file_size' => 0, 'file_parent_id' => UserFiles::ROOT_PARENT_ID];
                            $share_up = ['file_id' => null, 'share_hash' => '', 'share_group_hash' => $model->share_group_hash, 'file_name' => '..', 'is_folder' => UserFiles::TYPE_UP_FOLDER, 'file_size' => 0, 'file_parent_id' => $shareParent->file_id];
                            array_unshift($shareChildren, $share_top, $share_up);
                            //var_dump($shareChildren); exit;
                        }
                    }
                    $dataProvider = new ArrayDataProvider([
                        'allModels'  => $shareChildren,
                        'pagination' => false,
                    ]);

                    return $this->render('index', [
                        'share_group_hash' => $model->share_group_hash,
                        'shareParent'      => $shareParent,
                        'dataProvider'     => $dataProvider,
                    ]);
                //}
            }
        }
        //var_dump($model->getErrors());
        return "404 Share Not Found";

    }

}
