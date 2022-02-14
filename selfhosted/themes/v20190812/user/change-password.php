<?php
/* @var $this yii\web\View */
/* @var $model common\models\Users */
/* @var $form ActiveForm */

use yii\bootstrap\ActiveForm;

$this->title = Yii::t('user/change-password', 'Change_Password');
?>
<!-- begin Change-password-step-2-page content -->
<div class="content container">
    <h1 class="centered"><?= Yii::t('user/change-password', 'Change_password_step_2') ?></h1>

    <div class="support-block">

        <?php $form = ActiveForm::begin([
            'id' => 'form-changePassword',
            'options' => [
                'class'    => "form-box active",
            ],
        ]); ?>

            <?= $form->field($model, 'token')->label(false)->hiddenInput(['value' => Yii::$app->request->get('token')]); ?>


            <?= $form->field($model, 'old_password')
                ->passwordInput([
                    'placeholder' => $model->getAttributeLabel('old_password'),
                    'aria-label'   => $model->getAttributeLabel('old_password'),
                ])
                ->label(false)
            ?>

            <?= $form->field($model, 'new_password')
                ->passwordInput([
                    'placeholder' => $model->getAttributeLabel('new_password'),
                    'aria-label'   => $model->getAttributeLabel('new_password'),
                ])
                ->label(false)
            ?>

            <?= $form->field($model, 'repeat_password')
                ->passwordInput([
                    'placeholder' => $model->getAttributeLabel('repeat_password'),
                    'aria-label'   => $model->getAttributeLabel('repeat_password'),
                ])
                ->label(false)
            ?>

            <input type="submit"
                   name="ChangePasswordStep2"
                   value="<?= Yii::t('user/change-password', 'Change_Password') ?>"
                   class="btn primary-btn support-frm__submit wide-btn" />
            <div class="img-progress" title="loading..."></div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
<!-- end Change-password-step-2-page content -->