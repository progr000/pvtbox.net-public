<?php

/* @var $this \yii\web\View */
/* @var $content string */
/* @var $user \common\models\Users */

use yii\helpers\Html;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;
use frontend\assets\AppAsset;
use lajax\languagepicker\widgets\LanguagePicker;
use kartik\nav\NavX;
use yii\bootstrap\Modal;
use yii\bootstrap\ActiveForm;
use common\models\Users;

$user = Yii::$app->user->identity;

AppAsset::register($this);
$this->registerJsFile('/themes/null/js/modal.centered.js', ['depends' => 'yii\web\JqueryAsset']);
if (!Yii::$app->user->isGuest) {
    //$this->registerJsFile('/themes/null/js/main.js', ['depends' => 'yii\web\JqueryAsset']);
    if ($user->user_status < Users::STATUS_CONFIRMED) {
        Yii::$app->session->setFlash('danger',
            /*'<button type="button" class="close close-alert-confirm-email" data-dismiss="alert" aria-hidden="true">×</button> ' .*/
            Yii::t('app/flash-messages', 'Confirm_email')
        );
    }
}
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
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);

    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => 'DOWNLOAD_APP', 'url' => ['/download']];
        $menuItems[] = [
            'label' => 'REGISTER',
            'linkOptions' => [
                //'data-toggle' => 'modal',
                //'data-target' => '#signup-login-modal',
                'class' => 'signup-dialog',
            ],
        ];
        $menuItems[] = [
            'label' => 'LOGIN',
            'linkOptions' => [
                //'data-toggle' => 'modal',
                //'data-target' => '#signup-login-modal',
                'class' => 'login-dialog',
            ],
        ];
    } else {
        /*
        $menuItems[] = ['label' => Yii::t('app', 'BALLANCE: {balance}$', ['balance' => $user->user_balance]), 'url' => ['/site/balance-info']];
        $menuItems[] = ['label' => '|', 'url' => false];
        $menuItems[] = ['label' => Yii::t('app', 'MY_DEVICES'), 'url' => ['/site/devices']];
        $menuItems[] = ['label' => Yii::t('app', 'PROFILE'), 'url' => ['/site/profile']];
        $menuItems[] = [
            //'label' => 'Выйти (' . $user->user_name . ')',
            'label' => Yii::t('app', 'LOGOUT', ['user' => $user->user_name]),
            'url' => ['/site/logout'],
            'linkOptions' => ['data-method' => 'post']
        ];
        */

        //Имя на кого оформлено, нотификации, события, перейти на Pro/Business
        $menuItems[] = ['label' => Yii::t('app', ($user->user_name ? $user->user_name : "Ваше имя")),
            'items' => [
                ['label' => Yii::t('app', 'Тип аккаунта' . ' (Free/Pro/Business) ' . $user->user_email), 'url' => false],
                ['label' => Yii::t('app', 'MY_DEVICES'),   'url' => ['/devices']],
                ['label' => Yii::t('app', 'DOWNLOAD_APP'), 'url' => ['/download']],
                ['label' => Yii::t('app', 'Настройки'),    'url' => ['/user/profile'],
                    'items' => [
                        ['label' => Yii::t('app', 'Профиль'),      'url' => ['/user/profile']],
                        ['label' => Yii::t('app', 'Безопасность'), 'url' => ['/user/sessions'],
                            'items' => [
                                ['label' => 'Сеансы',     'url' => ['/user/sessions']],
                                ['label' => 'Устройства<span class="count-online-nodes"></span>', 'url' => ['/user/log-devices']],
                            ],
                        ],
                    ],
                ],

                ['label' => Yii::t('app', 'LOGOUT', ['user' => $user->user_name]), 'url' => ['/user/logout'], 'linkOptions' => ['data-method' => 'post']],
            ],
        ];
        //$menuItems[] = ['label' => 'Тикеты<span id="count-new-tikets"></span>',  'url' => ['/tikets']];
        $menuItems[] = ['label' => Yii::t('app', 'Нотификации')];
        $menuItems[] = ['label' => Yii::t('app', 'События')];
        $menuItems[] = ['label' => Yii::t('app', 'Перейти на Pro/Business')];

    }

    //$menuItems[] = ['label' => Yii::t('app', 'CONTACTS'), 'url' => ['/site/contact']];
    /*
    $menuItems[] = "<li>" . LanguagePicker::widget([
            'skin' => LanguagePicker::SKIN_DROPDOWN,
            'size' => LanguagePicker::SIZE_LARGE,
            'parentTemplate' => '<div class="language-picker dropdown-list {size}" style="margin-top: 18px; padding: 0px 20px 0px 20px;"><div style="">{activeItem}<ul>{items}</ul></div></div>',
    ]) . "</li>";
    */

    /*
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
    */
    echo NavX::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
        'activateParents' => true,
        'encodeLabels' => false
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?php
        if (Yii::$app->user->isGuest) {
            ?>
            <div class="row">
                <?= Html::a('Возможности', ['page/feature']); ?> /
                <?= Html::a('Pricing', ['page/pricing']); ?> /
                <?= Html::a('Помощь', ['page/help']); ?>
            </div><br/>
            <?php
        }
        ?>

        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; Direct-Link <?= date('Y') ?></p>

        <p class="pull-right">
            <!--<?= Yii::powered() ?>-->
            <?=
            LanguagePicker::widget([
                'skin' => LanguagePicker::SKIN_DROPDOWN,
                'size' => LanguagePicker::SIZE_LARGE,
                'parentTemplate' => '<div class="pull-right language-picker dropdown-list {size}" style=""><div style="">{activeItem}<ul>{items}</ul></div></div>',
            ])
            ?>
        </p>
    </div>
</footer>

<?php
if (Yii::$app->user->isGuest && isset($this->context->model_login)) {
    //echo \frontend\widgets\auth\AuthWidget::widget(['form_login' => $this->context->model_login/*new \frontend\models\forms\LoginForm()*/, 'form_signup' => new \frontend\models\forms\SignupForm()]);
}
if (!Yii::$app->user->isGuest) {
    if ($user->user_status < Users::STATUS_CONFIRMED) {
        Modal::begin([
            'options' => [
                'id' => 'resend-confirm-modal',
            ],
            'clientOptions' => [
                'keyboard' => false,
                'backdrop' => 'static',
            ],
            'closeButton' => ['id' => 'close-button-rc'],
            'header' => 'Подтверждение адреса электронной почты.',
            'size' => '',
        ]);

        $form = ActiveForm::begin(['id' => 'contact-form', 'action' => '/user/resend-confirm']);
        echo $form->field($user, 'user_email')->textInput(['readonly' => true]);
        echo Html::submitButton('Отправить сообщение повторно ', ['class' => 'btn btn-primary', 'name' => 'contact-button']);
        ActiveForm::end();

        Modal::end();
    }
}
?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
