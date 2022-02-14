<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\forms\SupportForm; */

use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use common\models\Preferences;

$this->title = Yii::t('app/support', 'title');
?>

<!-- .anything -->
<div class="anything support-form">

    <div class="anything__cont">

        <span class="title-min">
            <?= Yii::t('app/support', 'Look_faq_before') ?>
            <br />
            <?= Yii::t('app/support', 'Ask_us') ?>
        </span>


        <div class="anything__block">

            <?php
            $cnt = Yii::$app->cache->get(Yii::$app->params['ContactCacheKey']);
            if (!$cnt) {
                $cnt = 1;
                Yii::$app->cache->set(Yii::$app->params['ContactCacheKey'], $cnt);
            }

            $form = ActiveForm::begin([
                'id' => 'form-contact',
                'action'  => Url::to(["/support"], CREATE_ABSOLUTE_URL),
                'options' => [
                    //'onsubmit' => (Yii::$app->user->isGuest && $cnt > Preferences::getValueByKey('ContactCountNoCaptcha', 1, 'int')) ? "return false" : "return true;",
                    'class'    => "form-box active",
                ],
                //'enableAjaxValidation' => true,
                'enableClientValidation' => true,
                'validateOnSubmit' => true,
            ]);
            ?>

                <?php
                if (Yii::$app->user->isGuest) {
                    echo $form->field($model, 'name')
                        ->textInput([
                            'placeholder' => $model->getAttributeLabel('name'),
                            'autocomplete' => "off",
                        ])
                        ->label(false);

                    echo $form->field($model, 'email')
                        ->textInput([
                            'placeholder' => $model->getAttributeLabel('email'),
                            'autocomplete' => "off",
                        ])
                        ->label(false);
                }
                ?>

                <?=
                    $form->field($model, 'subject', [
                        'template'=>'{label}<div class="select-simple select-support-subject">{input}{hint}{error}</div>'
                    ])
                         ->dropDownList(\frontend\models\forms\SupportForm::subjectLabels())
                         ->label(false)
                ?>

                <?=
                    $form->field($model, 'body')
                         ->textArea([
                             'rows' => 6,
                             'placeholder' => $model->getAttributeLabel('body'),
                             'style' => "text-align: left;",
                         ])
                         ->label(false)
                ?>

                <div id="contact-captcha-container" class="captcha-container">
                    <?php
                    if (Yii::$app->user->isGuest) {
                        if ($cnt > Preferences::getValueByKey('ContactCountNoCaptcha', 1, 'int')) {

                            echo $form->field($model, 'reCaptchaSupport')
                                ->widget(\himiklab\yii2\recaptcha\ReCaptcha::className(), ['siteKey' => Preferences::getValueByKey('reCaptchaPublicKey')])
                                ->label(false);

                        }
                    }
                    ?>
                </div>

                <div class="form-group">
                    <input type="submit" name="contact-button" value="<?= Yii::t('app/support', 'Send') ?>" class="btn-big" />
                    <div class="img-progress" title="loading..."></div>
                </div>

            <?php ActiveForm::end(); ?>

        </div>



    </div>

</div>
<!-- END .anything -->
