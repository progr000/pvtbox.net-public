<?php
namespace frontend\components;

use Yii;
use yii\web\Controller;
use yii\web\Cookie;
use yii\web\NotFoundHttpException;
use common\helpers\Functions;
use common\models\Users;
use common\models\UserNode;
use common\models\UserActionsLog;
use common\models\Licenses;
use common\models\Maintenance;
use frontend\models\NodeApi;
use frontend\models\ShApi;

/**
 * Site controller
 *
 * @property \common\models\Users $User
 *
 */
class SController extends Controller
{
    protected $User;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        /* generate site-token */
        NodeApi::generate_site_token();

        /* User */
        if (!Yii::$app->user->isGuest) {
            $this->User = $this->findUserModel(Yii::$app->user->identity->getId());
            if ($this->User) {
                UserActionsLog::saveUserActionData($this->User->user_id);
            }
        }

        /* реферальная ссылка (ставим куку и редиректим на ту же страницу но уже без реферального параметра) */
        $ref = Yii::$app->request->get('ref');
        if ($ref) {
            $cookies = Yii::$app->response->cookies;
            $cookies->add(new Cookie([
                'name'   => 'ref',
                'value'  => $ref,
                'expire' => time() + 7 * 86400,
            ]));
            return $this->redirect(['/' . Yii::$app->request->pathInfo]);
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        /* если это сх, юзер залогинен и имеет лицензию отличную от бизнес-админ или бизнес-юзер то разлогиним его */
        if (Yii::$app->params['self_hosted']) {
            if ($this->User && !in_array($this->User->license_type, [
                    Licenses::TYPE_PAYED_BUSINESS_USER,
                    Licenses::TYPE_PAYED_BUSINESS_ADMIN])) {
                Yii::$app->user->logout();
                return $this->redirect(['/']);
            }
        }

        /* проверки для Self-Hosted */
        if (Yii::$app->params['self_hosted']) {
            if (!in_array($action->id, ['system-fault', 'maintenance'])) {
                /* если это сх, то нужно проверить в БД количество бизнес-админов если больше одного то выдать ошибку системы (это хак) */
                /* если это сх, то нужно проверить в БД наличие юзеров с лицензией отличной от бизнес-админ и бизнес-юзер, если есть - ошибка*/
                //if (!Yii::$app->cache->get('sh_integrity_passed')) {
                    $test = ShApi::check_sh_system_integrity();
                    if (!$test['status']) {
                        Yii::$app->session->set('system-fault-error', $test['info']);
                        Yii::$app->cache->delete('last_license_check');
                        return $this->redirect(['/system-fault']);
                    } else {
                        Yii::$app->cache->set('sh_integrity_passed', true, ShApi::INTEGRITY_CHECK_TTL);
                    }
                //}

                /*
                 * если это сх, то нужно проверить наличие флага-ключа в мемкеше,
                 * он хранится 36 часов (задано в frontend\models\ShApi.php)
                 * если этого флага в кеше уже нет, то знчит нужно заблокировать
                 * работу системы и показать заглушку
                 */
                if (!Yii::$app->cache->get('last_license_check')) {
                    Yii::$app->session->set('system-fault-error', 'check-license-timeout');
                    Yii::$app->cache->delete('last_license_check');
                    return $this->redirect(['/system-fault']);
                }
            }
        }

        /* show maintenance if it active */
        $Maintenance = Maintenance::getMaintenance();
        $controller_suspend = $this->getUniqueId();
        if (in_array($controller_suspend, ['down/file', 'down/folder'])) {
            $maintenance_suspend_check = $Maintenance->maintenance_suspend_share;
            Yii::$app->session->set('redirected_from_share_page', true);
        } else {
            $maintenance_suspend_check = $Maintenance->maintenance_suspend_site;
        }
        $maintenance_active = ($Maintenance->maintenance_suspend_share || $Maintenance->maintenance_suspend_site);
        if ($maintenance_suspend_check) {

            if ($Maintenance->maintenance_show_empty_page) {
                if (Yii::$app->controller->id != 'site' || $action->id != 'maintenance') {
                    return $this->redirect(['/maintenance']);
                }
            }

            if (!$Maintenance->maintenance_can_login && !Yii::$app->user->isGuest) {
                Yii::$app->user->logout();
                return $this->redirect(['/']);
            }

            Maintenance::maintenanceFlash($Maintenance);

        } else {
            Yii::$app->session->removeFlash($Maintenance->maintenance_type . '-maintenance');
        }

        if ($action->id == 'maintenance') {
            //if (!$maintenance_active || !$Maintenance->maintenance_show_empty_page) {
            //    return $this->redirect(['/']);
            //}
            $redirected_from_share_page = Yii::$app->session->get('redirected_from_share_page', false);
            if (!$redirected_from_share_page) {

                if (!$Maintenance->maintenance_suspend_site) {
                    return $this->redirect(['/']);
                }

            } else {
                Maintenance::maintenanceFlash($Maintenance);
                Yii::$app->session->remove('redirected_from_share_page');
            }
        }

        return parent::beforeAction($action);
    }

    /**
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {
        if ($this->User) {
            $cache_web_fm_key = 'web_fm_online_user_id_' . $this->User->user_id;
            //var_dump(Yii::$app->cache->get($cache_web_fm_key));exit;
            $previous_access = Yii::$app->cache->get($cache_web_fm_key);
            if (!$previous_access) { $previous_access = 0; }
            //var_dump($previous_access); var_dump(time()); exit;
            if ($previous_access + UserNode::WebFMOnlineTimeout < time()) {
                $nodeFM = NodeApi::registerNodeFM($this->User);
                $nodeFM->node_status = UserNode::STATUS_ACTIVE;
                $nodeFM->node_online = UserNode::ONLINE_ON;
                $nodeFM->node_updated = date(SQL_DATE_FORMAT);
                $nodeFM->save();
                Yii::$app->cache->set($cache_web_fm_key, time(), UserNode::WebFMOnlineTimeout);
            }
        }
        return parent::afterAction($action, $result);
    }

    /**
     * Finds the Users model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return \common\models\Users $User
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findUserModel($id)
    {
        if (($User = Users::findIdentity($id)) !== null) {
            if (in_array($User->user_status, [Users::STATUS_ACTIVE, Users::STATUS_CONFIRMED])) {
                return $User;
            }
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

