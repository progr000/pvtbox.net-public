<?php
/* @var $this yii\web\View */
/* @var $user \common\models\Users */
/* @var $model_change_name \frontend\models\forms\ChangeNameForm */
/* @var $dataProviderColleagues \yii\data\ActiveDataProvider */
/* @var $ColleagueAddForm \frontend\models\forms\ShareElementForm */
/* @var $dataProviderReports \yii\data\ActiveDataProvider */
/* @var $ServerLicensesSearchModel */
/* @var $dataProviderServerLicenses */
/* @var $admin \common\models\Users */
/* @var $license_count_info array */
/* @var $server_license_count_info array */
/* @var $ReportsSearchModel \frontend\models\search\ColleaguesReportsSearch */

use frontend\assets\orange\adminPanelAsset;
use frontend\assets\orange\daterangepickerAsset;

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

<!-- .tables -->
<div class="tables noShowBalloon">

    <div class="tables__cont">
        <div style="display: none" id="SignUrl" data-token="<?= $site_token ?>">wss://<?= $Server[0]->server_url ?>/ws/webfm/<?= $site_token ?></div>

        <div class="tabBlock">
            <ul class="tabBlock-list">
                <li class="<?= ($tab == 1) ? "active" : "" ?>" data-tab="1"><span><?= Yii::t('user/admin-panel', 'Company_name') ?></span></li>
                <li class="<?= ($tab == 2) ? "active" : "" ?>" data-tab="2"><span><?= Yii::t('user/admin-panel', 'Collaboration_settings') ?></span></li>
                <li id="reportTab" class="<?= ($tab == 3) ? "active" : "" ?>" data-tab="3" data-function="setReportsAsRead"><span><?= Yii::t('user/admin-panel', 'Reports') ?></span></li>
                <li class="<?= ($tab == 4) ? "active" : "" ?>" data-tab="4"><span><?= Yii::t('user/admin-panel', 'Server_licenses') ?></span></li>
            </ul>
        </div>


        <div class="tabBlock-content">


            <!-- .tabBlock-content__box #tab-change-name -->
            <div id="tab-change-name" class="tabBlock-content__box <?= ($tab == 1) ? "active" : "" ?>">

                <?=
                $this->render("index_CompanyName", [
                    'user' => $user,
                    'model_change_name' => $model_change_name,
                ])
                ?>

            </div>
            <!-- END .tabBlock-content__box -->



            <!-- .tabBlock-content__box #tab-list-colleagues -->
            <div id="tab-list-colleagues" class="tabBlock-content__box <?= ($tab == 2) ? "active" : "" ?>">

                <?=
                $this->render("index_CollaborationSettings", [
                    'ColleagueAddForm'       => $ColleagueAddForm,
                    'dataProviderColleagues' => $dataProviderColleagues,
                    'admin'                  => $admin,
                    'license_count_info'     => $license_count_info,
                ])
                ?>

            </div>
            <!-- END .tabBlock-content__box -->



            <!-- .tabBlock-content__box #tab-colleagues-actions-log -->
            <div id="tab-colleagues-actions-log" class="tabBlock-content__box <?= ($tab == 3) ? "active" : "" ?>">

                <?=
                $this->render("index_Reports", [
                    'ReportsSearchModel'  => $ReportsSearchModel,
                    'dataProviderReports' => $dataProviderReports,
                    'admin'               => $admin,
                ])
                ?>

            </div>
            <!-- END .tabBlock-content__box -->



            <!-- .tabBlock-content__box #tab-colleagues-actions-log -->
            <div id="tab-server-licenses" class="tabBlock-content__box <?= ($tab == 4) ? "active" : "" ?>">

                <?=
                $this->render("index_ServerLicenses", [
                    'ServerLicensesSearchModel'  => $ServerLicensesSearchModel,
                    'dataProviderServerLicenses' => $dataProviderServerLicenses,
                    'server_license_count_info'  => $server_license_count_info,
                    'admin'                      => $admin,
                ])
                ?>

            </div>
            <!-- END .tabBlock-content__box -->

        </div>


    </div>

</div>
<!-- END .tables -->
