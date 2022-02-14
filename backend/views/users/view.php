<?php
/* @var $this yii\web\View */
/* @var $UserModel common\models\Users */

/* @var $UserNodeSearchModel backend\models\search\UserNodeSearch */
/* @var $UserNodeSearchDataProvider yii\data\ActiveDataProvider */

/* @var $UserFilesSearchModel backend\models\search\UserFilesSearch */
/* @var $UserFilesSearchDataProvider yii\data\ActiveDataProvider */

/* @var $UserFileEventsSearchModel backend\models\search\UserFileEventsSearch */
/* @var $UserFileEventsSearchDataProvider yii\data\ActiveDataProvider */

/* @var $UserLicensesSearchModel backend\models\search\UserLicensesSearch */
/* @var $UserLicensesSearchDataProvider yii\data\ActiveDataProvider */

/* @var $UserServerLicensesSearchModel backend\models\search\UserServerLicensesSearch */
/* @var $UserServerLicensesSearchDataProvider yii\data\ActiveDataProvider */

/* @var $UserCollaborationsSearchModel backend\models\search\UserCollaborationsSearch */
/* @var $UserCollaborationsSearchDataProvider yii\data\ActiveDataProvider */

/* @var $UserPaymentsSearchModel backend\models\search\UserPaymentsSearch */
/* @var $UserPaymentsSearchDataProvider yii\data\ActiveDataProvider */

/* @var $UserAlertLogSearchModel backend\models\search\UserAlertsLogSearch */
/* @var $UserAlertLogSearchDataProvider yii\data\ActiveDataProvider */

/* @var $UserActionsLogSearchModel backend\models\search\UserAlertsLogSearch */
/* @var $UserActionsLogSearchDataProvider yii\data\ActiveDataProvider */

/* @var $TrafficSearchModel backend\models\search\TrafficSearch */
/* @var $TrafficSearchDataProvider yii\data\ActiveDataProvider */

/* @var $totalFsInfo array */
/* @var $licenseCountInfo array */
/* @var $serverLicenseCountInfo array */
/* @var $NodeInfo array */

use yii\helpers\Html;
use kartik\tabs\TabsX;
use common\models\Licenses;
use backend\assets\UsersAsset;

UsersAsset::register($this);

$this->title = 'User information: ' . $UserModel->user_email . ' (ID=' . $UserModel->user_id . ')';

?>
<div class="users-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Change', ['update', 'id' => $UserModel->user_id], ['class' => 'btn btn-primary']) ?>
        <?php /* Html::a('Удалить', ['delete', 'id' => $UserModel->user_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) */ ?>
        <?= Html::a('To the list', ['index'], ['class' => 'btn btn-default']) ?>
        <!-- <a href="javascript:void(0)" onclick="window.history.back();" class="btn btn-default">К списку</a>-->
    </p>

    <?php /*echo DetailView::widget([
        'model' => $UserModel,
        'attributes' => [
            'user_id',
            'user_name',
            'user_email:email',
            //'auth_key',
            //'password_hash',
            //'password_reset_token',
            'user_balance',
            [
                'attribute' => 'user_status',
                'value' => Users::statusLabel($UserModel->user_status),
            ],
            [
                'attribute' => 'license_type',
                'value' => Licenses::getType($UserModel->license_type),
            ],
            'user_created',
            'user_updated',
        ],
    ])*/ ?>
    <?php
    $availableTabs = ['base-info', 'node-info', 'file-info', 'event-info', 'licenses-info', 'collaborations-info', 'traffic-info', 'alert-logs', 'payments-info', 'action-logs'];
    //var_dump($_GET['tab']);
    $tab = Yii::$app->request->get('tab', $availableTabs[0]);
    if (!in_array($tab, $availableTabs)) { $tab = $availableTabs[0]; }


    /** Основная информация о пользователе */
    $items[] = [
        'label'   => 'Base info',
        'active'  => ($tab == $availableTabs[0]),
        'options' => ['id' => $availableTabs[0]],
        'content' => $this->render('view-base', [
            'UserModel'        => $UserModel,
            'NodeInfo'         => $NodeInfo,
            'licenseCountInfo' => $licenseCountInfo,
            'serverLicenseCountInfo' => $serverLicenseCountInfo,
        ]),
    ];


    /** Информация о нодах пользователя */
    $items[] = [
        'label'   => 'User nodes',
        'active'  => ($tab == $availableTabs[1]),
        'options' => ['id' => $availableTabs[1]],
        'content' => $this->render('view-nodes', [
            'UserNodeSearchModel'        => $UserNodeSearchModel,
            'UserNodeSearchDataProvider' => $UserNodeSearchDataProvider,
        ]),
    ];


    /** Информация о файлах пользователя */
    $items[] = [
        'label'   => 'User files',
        'active'  => ($tab == $availableTabs[2]),
        'options' => ['id' => $availableTabs[2]],
        'content' => $this->render('view-files', [
            'UserModel'                   => $UserModel,
            'UserFilesSearchModel'        => $UserFilesSearchModel,
            'UserFilesSearchDataProvider' => $UserFilesSearchDataProvider,
            'totalFsInfo'                 => $totalFsInfo,
        ]),
    ];


    /** Информация об евентах пользователя */
    $items[] = [
        'label'   => 'User Events',
        'active'  => ($tab == $availableTabs[3]),
        'options' => ['id' => $availableTabs[3]],
        'content' => $this->render('view-events', [
            'UserModel'                   => $UserModel,
            'UserFileEventsSearchModel'        => $UserFileEventsSearchModel,
            'UserFileEventsSearchDataProvider' => $UserFileEventsSearchDataProvider,
        ]),
    ];


    /** Информация о лицензиях пользователя */
    if ($UserModel->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
        $items[] = [
            'label' => 'User Licenses',
            'active' => ($tab == $availableTabs[4]),
            'options' => ['id' => $availableTabs[4]],
            'content' => $this->render('view-licenses', [
                'UserLicensesSearchModel'        => $UserLicensesSearchModel,
                'UserLicensesSearchDataProvider' => $UserLicensesSearchDataProvider,
                'UserServerLicensesSearchModel'        => $UserServerLicensesSearchModel,
                'UserServerLicensesSearchDataProvider' => $UserServerLicensesSearchDataProvider,
            ]),
        ];
    }


    /** Информация о коллаборация пользователя */
    $items[] = [
        'label' => 'User Collaborations',
        'active' => ($tab == $availableTabs[5]),
        'options' => ['id' => $availableTabs[5]],
        'content' => $this->render('view-collaborations', [
            'UserModel'                            => $UserModel,
            'UserCollaborationsSearchModel'        => $UserCollaborationsSearchModel,
            'UserCollaborationsSearchDataProvider' => $UserCollaborationsSearchDataProvider,
        ]),
    ];


    /** Информация о платежах пользователя */
    /****BEGIN-CUT-IT-IN-SH****/
    if (!Yii::$app->params['self_hosted']) {
        $items[] = [
            'label' => 'User Payments',
            'active' => ($tab == $availableTabs[8]),
            'options' => ['id' => $availableTabs[8]],
            'content' => $this->render('view-payments', [
                'UserPaymentsSearchModel'        => $UserPaymentsSearchModel,
                'UserPaymentsSearchDataProvider' => $UserPaymentsSearchDataProvider,
            ]),
        ];
    }
    /****END-CUT-IT-IN-SH****/


    /** Информация об алертах(флеш и снек сообщениях) пользователя */
    $items[] = [
        'label' => 'Alerts log',
        'active' => ($tab == $availableTabs[7]),
        'options' => ['id' => $availableTabs[7]],
        'content' => $this->render('view-alerts', [
            'UserAlertLogSearchModel'        => $UserAlertLogSearchModel,
            'UserAlertLogSearchDataProvider' => $UserAlertLogSearchDataProvider,
        ]),
    ];


    /** Информация об акшенах (действиях пост и гет запросах) пользователя */
    $items[] = [
        'label' => 'Actions log',
        'active' => ($tab == $availableTabs[9]),
        'options' => ['id' => $availableTabs[9]],
        'content' => $this->render('view-actions', [
            'UserActionsLogSearchModel'        => $UserActionsLogSearchModel,
            'UserActionsLogSearchDataProvider' => $UserActionsLogSearchDataProvider,
        ]),
    ];


    /** Информация из логов стороннего сервера по трафику турн, стун, п2п */
    $items[] = [
        'label'   => 'Traffic log',
        'active'  => ($tab == $availableTabs[6]),
        'options' => ['id' => $availableTabs[6]],
        'content' => $this->render('view-traffic', [
            'UserModel'                 => $UserModel,
            'TrafficSearchDataProvider' => $TrafficSearchDataProvider,
        ]),
    ];
    ?>

    <?= TabsX::widget([
        'items'=>$items,
        'position'=>TabsX::POS_ABOVE,
        'encodeLabels'=>false,
        'enableStickyTabs' => true,
        /*
        'stickyTabsOptions' => [
            'selectorAttribute' => 'data-target',
            'backToTop' => false,
        ],
        */

        'pluginEvents' => [
            "tabsX.click" => "function(event) {
                history.pushState({}, '', '". \yii\helpers\Url::to(['users/view', 'id' =>  $UserModel->user_id]) ."');
            }",
            //"tabsX.beforeSend" => "function(event, data, status, jqXHR) { console.log(event); }",
            //"tabsX.success" => "function(event, data, status, jqXHR) { console.log(event); }",
            //"tabsX.error" => "function(event, data, status, jqXHR) { console.log(event); }",
        ],

    ]) ?>


</div>

<?= $this->render('/layouts/modal') ?>

