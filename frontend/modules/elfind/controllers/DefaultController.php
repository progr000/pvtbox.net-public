<?php

namespace frontend\modules\elfind\controllers;

use Yii;
use yii\web\Controller;
use common\models\Maintenance;
use common\models\Users;
use frontend\models\NodeApi;

/**
 * Default controller for the elfind module
 */
class DefaultController extends Controller
{

    /**
     * Позволяет приходить запросам на этот скрипт и акшены перечисленные в массиве
     * с других доменов а не только с домена где стоит этот скрипт
     *
     * @inheritdoc
     */
    public function beforeAction($action)
    {

        //if (in_array($action->id, ['index'])) {
        //
        //}
        $this->enableCsrfValidation = false;
        set_time_limit(0);
        ini_set('memory_limit', '1G');

        $language = Yii::$app->session->get('_language');
        if ($language) {
            Yii::$app->language = $language;
        }

        return parent::beforeAction($action);
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        // если система на обслуживании
        if (Yii::$app->params['Stop_NodeApi_and_FM']) {
            return '{"error":["errNodeApiSuspended"]}';
        }

        // если система на обслуживании
        $Maintenance = Maintenance::getMaintenance();
        if ($Maintenance->maintenance_suspend_fm) {
            return '{"error":["errNodeApiSuspended"]}';
        }

        if (!Yii::$app->user->isGuest) {
            $User = Users::getPathNodeFS(Yii::$app->user->identity->getId());
            if ($User) {
                //sleep(5);
                //var_dump($User); exit;
                //Yii::$app->session->open();
                //var_dump(NodeApi::site_token_key()); exit;
                $User->user_last_ip = Yii::$app->request->getUserIP();
                $User->save();
                NodeApi::generate_site_token();

                $opts = array(
                    //'debug' => true,
                    'roots' => array(
                        array(
                            'driver' => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                            'path' => $User->_full_path,        // path to files (REQUIRED)
                            //'URL'           => dirname($_SERVER['PHP_SELF']) . '/../files/', // URL to files (REQUIRED)
                            'uploadAllow' => array('all'),                // All Mimetypes not allowed to upload
                            'uploadMaxSize' => '0',
                            //'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                            //'uploadAllow'   => array('image', 'text/plain'),// Mimetype image and text/plain allowed to upload
                            'uploadOrder' => array('deny', 'allow'),      // allowed Mimetype image and text/plain only
                            'accessControl' => 'access'                     // disable and hide dot starting files (OPTIONAL)
                        )
                    )
                );
                require_once(__DIR__ . '/../src/connector.php');

            }
        }
    }
}
