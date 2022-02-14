<?php
/* @var $this yii\web\View */
/* @var $user \common\models\Users */
/* @var $model_change_name \frontend\models\forms\ChangeNameForm */
/* @var $model_change_oo_address \frontend\models\forms\ChangeOoAddressForm */
/* @var $dataProviderColleagues \yii\data\ActiveDataProvider */
/* @var $ColleagueAddForm \frontend\models\forms\ShareElementForm */
/* @var $dataProviderReports \yii\data\ActiveDataProvider */
/* @var $ServerLicensesSearchModel */
/* @var $dataProviderServerLicenses */
/* @var $admin \common\models\Users */
/* @var $license_count_info array */
/* @var $server_license_count_info array */
/* @var $Server array */
/* @var $ReportsSearchModel \frontend\models\search\ColleaguesReportsSearch */
/* @var $current_count_unread_reports integer */

use yii\helpers\Url;
use frontend\assets\v20190812\adminPanelAsset;
use frontend\assets\v20190812\daterangepickerAsset;

/* assets */
adminPanelAsset::register($this);
daterangepickerAsset::register($this);

/* */
$user = Yii::$app->user->identity;

$this->title = Yii::t('user/admin-panel', 'title');

$tab = Yii::$app->request->get('tab', 1);
if (!in_array($tab, [1, 2, 3, 4])) {
    $tab = 1;
}
?>
<!-- begin Admin-panel-page content -->
<div class="content container noShowBalloon"
     id="wss-data"
     data-token="<?= $site_token ?>"
     data-wss-url="wss://<?= isset($Server[0]) ? $Server[0]->server_url : 'null' ?>/ws/webfm/<?= $site_token ?>"
     data-wss-url-echo-test-server="ws://echo.websocket.org">
    <h1><?= Yii::t('user/admin-panel', 'Admin_Panel') ?></h1>
    <div class="profile tabs-wrap">
        <ul class="tabs js-param-tabs real-tabs">
            <li class="tabs__item <?= ($tab == 1) ? "active" : "" ?>" data-tab="1"><?= Yii::t('user/admin-panel', 'Company_name') ?></li>
            <li class="tabs__item <?= ($tab == 2) ? "active" : "" ?>" data-tab="2"><?= Yii::t('user/admin-panel', 'Collaboration_settings') ?></li>
            <li id="reportTab" class="tabs__item <?= ($tab == 3) ? "active" : "" ?>" data-tab="3" data-location="<?= Url::to(['/admin-panel/index?tab=3'], CREATE_ABSOLUTE_URL) ?>" data-callback-function="reloadReportsTab"><?= Yii::t('user/admin-panel', 'Reports') ?><span class="count-new-reports"></span></li>
            <li class="tabs__item <?= ($tab == 4) ? "active" : "" ?>" data-tab="4"><?= Yii::t('user/admin-panel', 'Server_licenses') ?></li>
        </ul>
        <div class="tabs-content">

            <!-- begin CompanyName-tab -->
            <div class="box <?= ($tab == 1) ? "visible" : "" ?>">

                <?=
                $this->render("index_CompanyName", [
                    'user' => $user,
                    'model_change_name' => $model_change_name,
                    'model_change_oo_address' => $model_change_oo_address,
                ])
                ?>

            </div>
            <!-- end CompanyName-tab -->

            <!-- begin CollaborationSettings-tab -->
            <div class="box <?= ($tab == 2) ? "visible" : "" ?>">

                <?=
                $this->render("index_CollaborationSettings", [
                    'ColleagueAddForm'       => $ColleagueAddForm,
                    'dataProviderColleagues' => $dataProviderColleagues,
                    'admin'                  => $admin,
                    'license_count_info'     => $license_count_info,
                ])
                ?>

            </div>
            <!-- end CollaborationSettings-tab -->

            <!-- begin Reports-tab -->
            <div class="box <?= ($tab == 3) ? "visible" : "" ?>">

                <?php
                if ($ReportsSearchModel) {
                    echo $this->render("index_Reports", [
                        'ReportsSearchModel' => $ReportsSearchModel,
                        'dataProviderReports' => $dataProviderReports,
                        'admin' => $admin,
                        'current_count_unread_reports' => $current_count_unread_reports,
                    ]);
                }
                ?>

            </div>
            <!-- end Reports-tab -->

            <!-- begin ServerLicense-tab -->
            <div class="box <?= ($tab == 4) ? "visible" : "" ?>">

                <?=
                $this->render("index_ServerLicenses", [
                    'ServerLicensesSearchModel'  => $ServerLicensesSearchModel,
                    'dataProviderServerLicenses' => $dataProviderServerLicenses,
                    'server_license_count_info'  => $server_license_count_info,
                    'admin'                      => $admin,
                ])
                ?>

            </div>
            <!-- end ServerLicense-tab -->

        </div>
    </div>
</div>
<!-- end Admin-panel-page content -->
