<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\forms\SelfHostUserForm */
/* @var $user \common\models\Users|null */

use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use common\models\Preferences;

$this->title = Yii::t('app/support', 'title');
?>
<!-- begin Support-page content -->
<div class="content container">
    <h1 class="centered"><?= Yii::t('app/shu', 'New_user_registration') ?></h1>
    <div class="page-section-description page-section-description--limit">
        <?= /*Yii::t('app/shu', 'New_user_registration')*/ '' ?>
    </div>
    <div class="support-block">

        <?php
        $reCaptchaPublicKey = Preferences::getValueByKey('reCaptchaPublicKey');
        $cnt = Yii::$app->cache->get(Yii::$app->params['ShuCacheKey']);
        if (!$cnt) {
            $cnt = 1;
            Yii::$app->cache->set(Yii::$app->params['ShuCacheKey'], $cnt);
        }
        if (!$reCaptchaPublicKey) {
            $cnt = 1;
        }

        $form = ActiveForm::begin([
            'id' => 'form-shu',
            //'action'  => Url::to(Yii::getAlias('@selfHostedWeb') . "/signup", CREATE_ABSOLUTE_URL),
            'action'  => Url::to(['/self-hosted'], CREATE_ABSOLUTE_URL),
            'options' => [
                'class'    => "form-box active img-progress-form",
            ],
            //'enableClientValidation' => true,
            //'enableAjaxValidation' => true,
            //'validateOnSubmit' => false,
        ]);
        ?>

        <?= $form->field($model, 'shu_company')
            ->textInput([
                'placeholder' => $model->getAttributeLabel('shu_company'),
                'autocomplete' => "off",
                'aria-label'   => $model->getAttributeLabel('shu_company'),
            ])
            ->label(false)
        ?>

        <?= $form->field($model, 'shu_name')
                ->textInput([
                    'placeholder' => $model->getAttributeLabel('shu_name'),
                    'autocomplete' => "off",
                    'aria-label'   => $model->getAttributeLabel('shu_name'),
                ])
                ->label(false)
        ?>

        <?= $form->field($model, 'shu_email', ['enableAjaxValidation' => true])
                ->textInput([
                    'placeholder' => $model->getAttributeLabel('shu_email'),
                    'autocomplete' => "off",
                    'aria-label'   => $model->getAttributeLabel('shu_email'),
                    'readonly' => (isset($user) && isset($user->user_email)),
                    'disabled' => (isset($user) && isset($user->user_email)),
                ])
                ->label(false)
        ?>

        <?= $form->field($model, 'password')
            ->passwordInput([
                'placeholder'  => $model->getAttributeLabel('password'),
                'autocomplete' => "off",
                'aria-label'   => $model->getAttributeLabel('password'),
            ])
            ->label(false)
        ?>

        <?= $form->field($model, 'password_repeat')
            ->passwordInput([
                'placeholder'  => $model->getAttributeLabel('password_repeat'),
                'autocomplete' => "off",
                'aria-label'   => $model->getAttributeLabel('password_repeat'),
            ])
            ->label(false)
        ?>

        <?=
        $form->field($model, 'shu_support_status', [
            'template' => "",
            'inputTemplate' => "",
            'options' => ['class' => "check-wrap private form-group"],
            'checkboxTemplate'=>'
                {input}
                <label for="shu-support-status" id="label-support-status"><span></span><span>' . Yii::t('forms/login-signup-form', 'shu_support_status') . '</span></label>
                <div class="form-group-hint">{error}{hint}</div>',
        ])
            ->checkbox(['id' => "shu-support-status", 'value' => true, 'autocomplete' => "off", 'inputTemplate' => ""])
            ->label(false)
        ?>

        <?=
        $form->field($model, 'shu_brand_status', [
            'template' => "",
            'inputTemplate' => "",
            'options' => ['class' => "check-wrap private form-group"],
            'checkboxTemplate'=>'
                {input}
                <label for="shu-brand-status" id="label-brand-status"><span></span><span>' . Yii::t('forms/login-signup-form', 'shu_brand_status') . '</span></label>
                <div class="form-group-hint">{error}{hint}</div>',
        ])
            ->checkbox(['id' => "shu-brand-status", 'value' => true, 'autocomplete' => "off", 'inputTemplate' => ""])
            ->label(false)
        ?>

        <div id="contact-captcha-container" class="captcha-container" style="margin-top: 20px;">
            <?php
            if (Yii::$app->user->isGuest) {
                $cnt = intval(Yii::$app->cache->get(Yii::$app->params['ShuCacheKey']));
                if ($cnt > Preferences::getValueByKey('RegisterCountNoCaptcha', 1, 'int')) {

                    echo $form->field($model, 'reCaptchaShu')
                        ->widget(\himiklab\yii2\recaptcha\ReCaptcha::className(), ['siteKey' => Preferences::getValueByKey('reCaptchaPublicKey')])
                        ->label(false);

                }
            }
            ?>
        </div>

        <input type="submit" name="shu-button" value="<?= Yii::t('app/shu', 'Create_account') ?>" class="btn primary-btn wide-btn" />
        <div class="img-progress" title="loading..."></div>

        <?php ActiveForm::end(); ?>

    </div>
</div>
<!-- end Support-page content -->
