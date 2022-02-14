<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\forms\SupportForm */
/* @var $software array */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\Software;

$this->title = 'Скачать приложение';
//$this->params['breadcrumbs'][] = $this->title;
$this->registerJsFile('/js/jsclient/jquery.client.js', ['depends' => 'yii\web\JqueryAsset']);
$this->registerJsFile('/js/jsclient/download.init.js', ['depends' => 'yii\web\JqueryAsset']);
?>
<div class="site-contact">

    <div class="row">
        <div class="col-xs-8 col-sm-8 col-lg-8">

            <div class="row" id="download-descktop">
                <h2><?= Yii::t('app', 'sd_YDSSA') ?></h2>
                <br />
                <?=Yii::t('app', 'sd_IIDNS') ?> <?= Html::a(Yii::t('app', 'sd_CH'), '', ['class' => 'download-link']) ?>.
                <br />
                <?=Yii::t('app', 'sd_LMTST')?>
            </div>
            <div class="row" id="download-mobile" style="display: none;">
                <h2><?=Yii::t('app', 'sd_S_{APP_NAME}_TYMD', ['APP_NAME' => Yii::$app->name])?></h2>
                <br />
                <div class="col-xs-12 col-sm-12 col-lg-12">
                    <div class="row">
                        <div class="col-xs-4 col-sm-2 col-lg-2" style="padding: 30px 2px 2px 2px;">
                            <img src="/images/AppStore.jpg" border="0" width="100px" id="img-iphone" class="device-img" />
                            <img src="/images/GooglePlay.jpg" border="0" width="100px" id="img-android" class="device-img" style="display: none;" />
                        </div>
                        <div class="col-xs-8 col-sm-10 col-lg-10">
                            <?php $form = ActiveForm::begin(['id' => 'contact-form']); ?>

                            <?= $form->field($model, 'os')
                                ->hiddenInput(['value' => 'android', 'id' => 'os-type'])
                                ->label(false) ?>

                            <?= $form->field($model, 'subject')
                                ->hiddenInput(['value' => Yii::t('app', 'sd_LTDTA')])
                                ->label(false) ?>

                            <?= $form->field($model, 'email') ?>

                            <div class="form-group">
                                <?= Html::submitButton(Yii::t('app', 'sd_SADLOE'), ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
                            </div>

                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>

            <br />
            <br />

            <div class="row">
                <?= Html::a(Yii::t('app', 'sd_{APP_NAME}_FOP', ['APP_NAME' => Yii::$app->name]), '#', ['class' => 'download-other-platforms']) ?>:
            </div>

            <div id="other-platforms"  class="row" style="display: none;">
                <div class="col-lg-1"></div>
                <div class="col-lg-7">
                    <?php
                    foreach ($software as $k => $v) {
                        if ($v['software_program_type'] == Software::PROGRAM_TYPE_FILE) {
                            $v['software_url'] = Yii::$app->urlManager->createAbsoluteUrl(Yii::$app->params['downloadSoftwareDir'] . $v['software_file_name']);
                        }
                        echo '<div class="row"><div class="col-sm-4">' . Software::getType($v['software_type']) . '</div><div class="col-sm-4">' .
                            Html::a(Yii::t('app', 'sd_D'), $v['software_url'], ['id' => 'download-'.$v['software_type']]) .
                            "</div></div>\n";
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>

</div>
