<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\forms\ResetPasswordForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Смена пароля';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment">

    <div class="pricing__cont">

        <span class="title-min"><?= Yii::t('forms/reset-password-form', 'Create_new_password') ?><b></b></span>

        <div class="payment__block">

            <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>

                <?= $form->field($model, 'new_password')
                    ->passwordInput(['placeholder' => $model->getAttributeLabel('new_password'), 'autocomplete' => "off"])
                    ->label(false)
                ?>

                <?= $form->field($model, 'repeat_password')
                    ->passwordInput(['placeholder' => $model->getAttributeLabel('repeat_password'), 'autocomplete' => "off"])
                    ->label(false)
                ?>

                <div class="form-group" style="text-align: center;">
                    <?= '' /*Html::submitButton(Yii::t('forms/reset-password-form', 'Change_password'), ['class' => 'btn-big'])*/ ?>
                    <input type="submit" value="<?= Yii::t('forms/reset-password-form', 'Change_password') ?>" class="btn-big" />
                    <div class="img-progress" title="loading..."></div>
                </div>

            <?php ActiveForm::end(); ?>

        </div>

    </div>

</div>
