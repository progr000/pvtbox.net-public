<?php

/* @var $this yii\web\View */
/* @var $UserNode \common\models\UserNode */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $Server \common\models\Servers */
/* @var $site_token string */
/* @var $ShareElementForm \frontend\models\forms\ShareElementForm */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\bootstrap\ActiveForm;
use kartik\datetime\DateTimePicker;
//use kartik\grid\GridView;
//use kartik\editable\Editable;
//use yii\web\JsExpression;
//use limion\jqueryfileupload\JQueryFileUpload;
//use common\helpers\WebSockets;

$this->title = 'Мои устройства';
?>
<div class="site-devices">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php
    $this->registerCssFile('//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css');
    $this->registerCssFile('/elfinder/css/elfinder.min.css');
    $this->registerCssFile('/elfinder/css/theme.css');
    $this->registerCssFile('/elfinder/themes/windows-10/css/theme.css');
    $this->registerJsFile('//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js', ['depends' => 'yii\web\JqueryAsset']);
    $this->registerJsFile('/elfinder/js/elfinder.full.js', ['depends' => 'yii\web\JqueryAsset']);
    if (file_exists(\Yii::getAlias('@webroot') . '/elfinder/js/i18n/elfinder.' . (Yii::$app->language) . '.js')) {
        $this->registerJsFile('/elfinder/js/i18n/elfinder.' . (Yii::$app->language) . '.js', ['depends' => 'yii\web\JqueryAsset']);
    } else {
        $this->registerJsFile('/elfinder/js/i18n/elfinder.LANG.js', ['depends' => 'yii\web\JqueryAsset']);
    }
    $this->registerJsFile('/elfinder/js/init.elfinder.js', ['depends' => 'yii\web\JqueryAsset']);
    ?>

    <div class="col-lg-12">
        <div>Просмотр ФС устройств:</div>

        <div style="display: none" id="SignUrl">wss://<?= $Server[0]->server_url ?>/ws/webfm/<?= $site_token ?></div>
        <!--  -->

        <div class="state" id="label_state">Тут статус подключения</div>

        <?php
        $this->registerJs("initElFinder('".(Yii::$app->language)."');");

        /*
        $form = ActiveForm::begin();
        echo JQueryFileUpload::widget([
            'model' => $uploadModel,
            'attribute' => 'image',
            'url' => ['add-files', 'node_id' => $UserNode->node_id],
            'gallery' => false,
            'formId'=>$form->id,

            //'fieldOptions' => [
            //    'accept' => 'image/*'
            //],

            'clientOptions' => [
                'maxFileSize' => 2000000
            ],
            // ...
            'clientEvents' => [
                'fileuploaddone' => 'function(e, data) {
                                    console.log(e);
                                    console.log(data);
                                }',
                'fileuploadfail' => 'function(e, data) {
                                    console.log(e);
                                    console.log(data);
                                }',
            ],
        ]);
        ActiveForm::end();
        */

        ?>
        <div id="elfinder"></div>

    </div>

</div>

<?php
Modal::begin([
    'options' => [
        'id' => 'share-create-remove-modal',
    ],
    'clientOptions' => [
        'keyboard' => false,
        'backdrop' => 'static',
    ],
    'closeButton' => ['id' => 'close-button-rc'],
    'header' => '<div id="share-popup-link-main-header">Get Link</div><div id="share-popup-link-settings-header" style="display: none;">Link settings</div>',
    'size' => '',
]);
$form = ActiveForm::begin([
    'id' => 'sare-create-remove-form',
    'enableClientValidation' => true,
    'options' => [
        'onsubmit' => 'return false',
    ],
]);
echo $form->field($ShareElementForm, 'hash')->hiddenInput(['id' => 'filesystem_hash'])->label(false);
?>
<div id="share-popup-link-main">
    <div class="row">
        <div class="col-xs-10">
            <?= $form->field($ShareElementForm, 'share_link')->textInput(['readonly' => true, 'id' => 'share-link-field'])->label(false) ?>
        </div>
        <div class="col-xs-2">
            <?= Html::button('Settings', ['class' => 'btn btn-primary', 'id' => 'share-show-settings-button', 'disabled' => 'disabled']) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <?= Html::button('Create Link', ['class' => 'btn btn-success', 'id' => 'create-share-button']) ?>
            <?= Html::button('Remove Link', ['class' => 'btn btn-danger', 'id' => 'remove-share-button']) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <hr />
        </div>
    </div>
    <div class="row">
        <div class="col-xs-10">
            <?= $form->field($ShareElementForm, 'share_email')->textInput() ?>
        </div>
        <div class="col-xs-2">
            <label>&nbsp;</label>
            <?= Html::button('Send', ['class' => 'btn btn-default show', 'name' => 'send-share-button', 'id' => 'share-send-button']) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            Here email colleguas<br />
            Here email colleguas<br />
            Here email colleguas<br />
            Here email colleguas<br />
            Here email colleguas<br />
        </div>
    </div>
</div>
<div id="share-popup-link-settings" style="display: none;">
    <div class="row">
        <div class="col-xs-12">
            <?= $form->field($ShareElementForm, 'share_lifetime')->widget(
                trntv\yii\datetime\DateTimeWidget::className(),
                [
                    //'phpDatetimeFormat' => 'yyyy.MM.dd, HH:mm:ss',
                    //'containerOptions' => ['id' => 'dddddddd'],
                    'clientOptions' => [
                        'format' => 'YYYY-MM-DD HH:mm:ss UTC',
                        'minDate' => date('Y-m-d H:i:s', time()),
                        'defaultDate' => null,
                        'allowInputToggle' => false,
                        'sideBySide' => true,
                        'keepOpen' => true,
                        'showClear' => true,
                        'showClose' => true,
                        'showTodayButton' => true,
                        'toolbarPlacement' => 'top',
                        //'locale' => 'zh-cn',
                        'widgetPositioning' => [
                            'horizontal' => 'auto',
                            'vertical' => 'auto'
                        ]
                    ]
                ]
            );
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <?= $form->field($ShareElementForm, 'share_password')->textInput() ?>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <?= Html::button('&lt;&lt;Back', ['class' => 'btn btn-default pull-left',  'id' => 'share-show-main-button'])    ?>
            <?= Html::button('Set',          ['class' => 'btn btn-success pull-right', 'id' => 'share-set-settings-button']) ?>
        </div>
    </div>
</div>
<?php
ActiveForm::end();
Modal::end();
