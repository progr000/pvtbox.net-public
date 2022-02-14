<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\forms\SupportForm; */

use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use common\models\Preferences;
use frontend\models\forms\SupportForm;


$this->title = Yii::t('app/support', 'title');
?>
<!-- begin Support-page content -->
<div class="content container">
    <h1 class="centered"><?= Yii::t('app/support', 'Support') ?></h1>
    <div class="page-section-description page-section-description--limit">
        <?= Yii::t('app/support', 'Look_faq_before') ?>
        <br />
        <?= Yii::t('app/support', 'Ask_us') ?>
    </div>
    <div class="support-block">

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
                'class'    => "form-box active",
            ],
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
                    'aria-label'   => $model->getAttributeLabel('name'),
                ])
                ->label(false);

            echo $form->field($model, 'email')
                ->textInput([
                    'placeholder' => $model->getAttributeLabel('email'),
                    'autocomplete' => "off",
                    'aria-label'   => $model->getAttributeLabel('email'),
                ])
                ->label(false);
        }
        ?>

        <?=
        $form->field($model, 'subject', [
            'template'=>'<div class="select-wrap">{input}{hint}{error}</div>'
        ])
            ->dropDownList(['-1' => Yii::t('forms/support-form', 'SUBJECT_CHOOSE')] + SupportForm::subjectLabels(), [
                'class' => "js-select",
                'id' => "supportform-subject",
                'aria-required' => "true",
                'tabindex' => "-98",
                'aria-invalid' => "false",
                'aria-label'   => $model->getAttributeLabel('subject'),
                'placeholder' => Yii::t('forms/support-form', 'SUBJECT_CHOOSE'),
                'data-placeholder' => Yii::t('forms/support-form', 'SUBJECT_CHOOSE'),
            ])
            ->label(false)
        ?>

        <?=
        $form->field($model, 'body')
            ->textArea([
                'rows' => 6,
                'placeholder' => $model->getAttributeLabel('body'),
                'aria-label'  => $model->getAttributeLabel('body'),
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

        <input type="submit" name="contact-button" value="<?= Yii::t('app/support', 'Send') ?>" class="btn primary-btn support-frm__submit wide-btn" />
        <div class="img-progress" title="loading..."></div>

        <?php ActiveForm::end(); ?>

    </div>
</div>
<!-- end Support-page content -->
