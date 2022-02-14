<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\forms\SupportForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\Preferences;
//use yii\captcha\Captcha;

$this->title = 'Обратная связь';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-contact">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        Задайте нам вопрос через форму обратной связи.
    </p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'contact-form']); ?>


                <?php
                if (Yii::$app->user->isGuest) {
                    echo $form->field($model, 'name');

                    echo $form->field($model, 'email');
                }
                ?>

                <?= $form->field($model, 'subject') ?>

                <?= $form->field($model, 'body')->textArea(['rows' => 6]) ?>

                <?php
                if (Yii::$app->user->isGuest) {
                    echo $form->field($model, 'reCaptcha')->widget(
                        \himiklab\yii2\recaptcha\ReCaptcha::className(),
                        ['siteKey' => Preferences::getValueByKey('reCaptchaPublicKey')]
                    );
                }
                ?>

                <div class="form-group">
                    <?= Html::submitButton('Отправить', ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>
