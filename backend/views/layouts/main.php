<?php

/* @var $this \yii\web\View */
/* @var $content string */
/* @var $admin \backend\models\Admins */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use kartik\nav\NavX;
use common\widgets\Alert;
use backend\assets\AppAsset;
use backend\assets\RegisteredAsset;
use backend\models\Admins;

AppAsset::register($this);

if (!Yii::$app->user->isGuest) {
    RegisteredAsset::register($this);
}

/** @var \backend\models\Admins $admin */
$admin = Yii::$app->user->identity;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body data-admin-role="<?= $admin ? $admin->admin_role : 100 ?>">
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'brandOptions' => [
            'style' => (Yii::$app->controller->id == "site" ? "color: #FFFFFF" : ""),
        ],
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);

    if (Yii::$app->user->isGuest) {

        $menuItems[] = ['label' => 'Signup', 'url' => Url::to(['/site/login'])];

    } else {

        $menuItems[] = ['label' => 'Users', 'url' => Url::to(['/users']), 'active' => (Yii::$app->controller->id == 'users')];

        if (in_array($admin->admin_role, [Admins::ROLE_ROOT, Admins::ROLE_READER])) {

            if (!Yii::$app->params['self_hosted']) {
                $menuItems[] = ['label' => 'Sh-Users', 'url' => Url::to(['/self-host-users']), 'active' => (Yii::$app->controller->id == 'self-host-users')];
            }

            $menuItems[] = [
                'label' => 'General Statistics',
                'items' => [
                    'messages-store' => ['label' => 'Messages Store', 'url' => Url::to(['/messages-store'], CREATE_ABSOLUTE_URL), 'active' => (Yii::$app->controller->id == 'messages-store')],
                    'bad-logins'     => ['label' => 'Blocked IPs', 'url' => Url::to(['/bad-logins'], CREATE_ABSOLUTE_URL), 'active' => (Yii::$app->controller->id == 'bad-logins')],
                    'alerts-log'     => ['label' => 'Alerts Log', 'url' => Url::to(['/alerts-log']), 'active' => (Yii::$app->controller->id == 'alerts-log')],
                    'actions-log'    => ['label' => 'Actions Log', 'url' => Url::to(['/actions-log']), 'active' => (Yii::$app->controller->id == 'actions-log')],
                    'shares'         => ['label' => 'All Shares', 'url' => Url::to(['/shares']), 'active' => (Yii::$app->controller->id == 'shares')],
                    'collaborations' => ['label' => 'All Collaborations', 'url' => Url::to(['/collaborations']), 'active' => (Yii::$app->controller->id == 'collaborations')],
                    'mailq'          => ['label' => 'Sent mail', 'url' => Url::to(['/mailq']), 'active' => (Yii::$app->controller->id == 'mailq')],
                    'queued'         => ['label' => 'Queued Events', 'url' => Url::to(['/queued']), 'active' => (Yii::$app->controller->id == 'queued')],
                    /****BEGIN-CUT-IT-IN-SH****/
                    'payments'       => Yii::$app->params['self_hosted']
                        ? ['label' => false]
                        : ['label' => 'Payments', 'url' => Url::to(['/payments', 'UserPaymentsSearch[pay_status]' => 'paid']), 'active' => (Yii::$app->controller->id == 'payments')],
                    /****END-CUT-IT-IN-SH****/
                ]
            ];

            $menuItems[] = ['label' => 'Servers', 'url' => Url::to(['/servers']), 'active' => (Yii::$app->controller->id == 'servers')];
            //$menuItems[] = ['label' => 'Tickets<span id="count-new-tikets"></span>',  'url' => Url::to(['/tikets'])];

            $menuItems[] = [
                'label' => 'Content',
                'active' => (in_array(Yii::$app->controller->id, ['news', 'pages'])),
                'items' => [
                    /****BEGIN-CUT-IT-IN-SH****/
                    'news'  => Yii::$app->params['self_hosted']
                        ? ['label' => false]
                        : ['label' => 'News', 'url' => Url::to(['/news']), 'active' => (Yii::$app->controller->id == 'news')],
                    /****END-CUT-IT-IN-SH****/
                    'pages' =>['label' => 'Static pages', 'url' => Url::to(['/pages']), 'active' => (Yii::$app->controller->id == 'pages')],
                ]
            ];

            $menuItems[] = [
                'label' => 'Preferences',
                'url' => Url::to(['/preferences']),
                'active' => (in_array(Yii::$app->controller->id, ['maintenance', 'preferences', 'software', 'licenses', 'mail-templates'])),
                'items' => [
                    'admins'      => ['label' => 'Admins Management', 'url' => Url::to(['/admins']), 'active' => (Yii::$app->controller->id == 'admins')],
                    'maintenance' => ['label' => 'Site Maintenance', 'url' => Url::to(['/maintenance']), 'active' => (Yii::$app->controller->id == 'maintenance')],
                    'preferences' => ['label' => 'Settings Management', 'url' => Url::to(['/preferences']), 'active' => (Yii::$app->controller->id == 'preferences')],
                    /****BEGIN-CUT-IT-IN-SH****/
                    'software'    => Yii::$app->params['self_hosted']
                        ? ['label' => false]
                        : ['label' => 'Applications Management', 'url' => Url::to(['/software']), 'active' => (Yii::$app->controller->id == 'software')],
                    'licenses'    => Yii::$app->params['self_hosted']
                        ? ['label' => false]
                        : ['label' => 'Licenses Management', 'url' => Url::to(['/licenses']), 'active' => (Yii::$app->controller->id == 'licenses')],
                    /****END-CUT-IT-IN-SH****/
                    //'mail-templates' => ['label' => 'Letter Templates', 'url' => Url::to(['/mail-templates']), 'active' => (Yii::$app->controller->id == 'mail-templates')],
                ]
            ];


        }

        $menuItems[] = [
            'label' => 'Log Out (' . $admin->admin_name . ')',
            'url' => Url::to(['/site/logout']),
            'linkOptions' => [
                //'data-method' => 'post',
                'onclick' => "return confirm('Are you sure you want to log out?');"
            ],
        ];

        $menuItems[] = [
            'label' => date(Yii::$app->params['datetime_format']) . 'GMT',
        ];

    }
    echo NavX::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
        'activateParents' => true,
        'encodeLabels' => false
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; My Company <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
