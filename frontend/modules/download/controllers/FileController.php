<?php

namespace frontend\modules\download\controllers;

use Yii;
use frontend\components\SController;
use yii\base\DynamicModel;
use common\models\Servers;
use common\models\UserNode;
use common\models\UserFiles;
use common\models\UserFileEvents;
use frontend\models\forms\LoginForm;

/**
 * Default controller for the download module
 */
class FileController extends SController
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
        /** @var \common\models\UserFiles $share */
        //var_dump($_GET); exit;
        $model = new DynamicModel(['share_hash']);
        $model->addRule(['share_hash'], 'required');
        $model->addRule(['share_hash'], 'string', ['length' => 32]);

        $data[$model->formName()] = Yii::$app->request->get();
        if ($model->load($data) && $model->validate()) {
            $share = UserFiles::find()
                //->asArray()
                ->where(['share_hash' => $model->share_hash])
                ->andWhere("(share_lifetime > :share_lifetime) OR (share_lifetime IS NULL)", ['share_lifetime'  => date(SQL_DATE_FORMAT)])
                /*
                ->andWhere("(share_lifetime > :share_lifetime) OR ((share_lifetime IS NULL) AND (share_ttl_info != :TTL_IMMEDIATELY_DOWNLOADED))", [
                    'share_lifetime'             => date(SQL_DATE_FORMAT),
                    'TTL_IMMEDIATELY_DOWNLOADED' => UserFiles::TTL_IMMEDIATELY_DOWNLOADED,
                ])
                */
                ->andWhere("last_event_type <> :last_event_type", ['last_event_type' => UserFileEvents::TYPE_DELETE])
                ->limit(1)
                ->one();

            //var_dump($share); exit;
            if ($share) {

                if ($share->share_is_locked) {
                    return "404 Share is locked";
                }

                /** @var \common\models\UserFileEvents $eventWithUuid */
                $eventWithUuid = UserFileEvents::find()
                    ->select([
                        'event_id',
                        'event_uuid',
                    ])
                    ->where(['file_id' => $share->file_id])
                    ->andWhere('event_type NOT IN (:event_delete)', ['event_delete' => UserFileEvents::TYPE_DELETE])
                    ->orderBy(['event_id' => SORT_DESC])
                    ->limit(1)
                    ->one();

                if ($eventWithUuid) {

                    $nodes = UserNode::find()
                        ->where([
                            'user_id' => $share->user_id,
                            //'node_online' => UserNode::ONLINE_ON,
                        ])
                        ->andWhere('node_status != :node_status', ['node_status' => UserNode::STATUS_DELETED])
                        ->asArray()
                        ->all();
                    //var_dump($nodes);

                    $servers['stun'] = Servers::find()
                        ->asArray()
                        ->where([
                            'server_type' => Servers::SERVER_TYPE_STUN,
                            'server_status' => Servers::SERVER_ACTIVE_YES
                        ])->limit(1)->all();
                    if (!isset($servers['stun'][0])) {
                        return "404 STUN-Server Not Found";
                    }

                    $servers['sign'] = Servers::find()
                        ->asArray()
                        ->where([
                            'server_type' => Servers::SERVER_TYPE_SIGN,
                            'server_status' => Servers::SERVER_ACTIVE_YES
                        ])->limit(1)->all();
                    if (!isset($servers['sign'][0])) {
                        return "404 SIGN-Server Not Found";
                    }

                    $servers['proxy'] = Servers::find()
                        ->asArray()
                        ->where([
                            'server_type' => Servers::SERVER_TYPE_PROXY,
                            'server_status' => Servers::SERVER_ACTIVE_YES
                        ])->limit(1)->all();
                    if (!isset($servers['proxy'][0])) {
                        return "404 PROXY-NODE-Server Not Found";
                    }
                    if (mb_strrpos($servers['proxy'][0]['server_url'], '/') == mb_strlen($servers['proxy'][0]['server_url']) - 1) {
                        $servers['proxy'][0]['server_url'] = mb_substr($servers['proxy'][0]['server_url'], 0, mb_strlen($servers['proxy'][0]['server_url']) - 1);
                    }

                    //$this->layout = "download";
                    return $this->render('index', [
                        'share' => $share,
                        'eventWithUuid' => $eventWithUuid,
                        'servers' => $servers,
                        'nodes' => $nodes
                    ]);
                }
            }
        }
        //var_dump($model->getErrors());
        return "404 Share Not Found";
    }

}
