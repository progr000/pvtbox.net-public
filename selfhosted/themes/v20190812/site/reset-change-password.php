<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\forms\ResetPasswordForm */

use yii\bootstrap\ActiveForm;

$this->title = Yii::t('user/change-password', 'Change_Password');
?>
<div class="content container">
    <h1 class="centered"><?= Yii::t('forms/reset-password-form', 'Create_new_password') ?></h1>

    <div class="support-block">

        <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>

            <?= $form->field($model, 'new_password')
                ->passwordInput([
                    'placeholder' => $model->getAttributeLabel('new_password'),
                    'autocomplete' => "off",
                    'aria-label'   => $model->getAttributeLabel('new_password'),
                ])
                ->label(false)
            ?>

            <?= $form->field($model, 'repeat_password')
                ->passwordInput([
                    'placeholder' => $model->getAttributeLabel('repeat_password'),
                    'autocomplete' => "off",
                    'aria-label'   => $model->getAttributeLabel('repeat_password'),
                ])
                ->label(false)
            ?>

            <input type="submit"
                   name="ChangePasswordStep2"
                   value="<?= Yii::t('forms/reset-password-form', 'Change_password') ?>"
                   class="btn primary-btn support-frm__submit wide-btn" />
            <div class="img-progress" title="loading..."></div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
<!-- end Change-password-step-2-page content -->