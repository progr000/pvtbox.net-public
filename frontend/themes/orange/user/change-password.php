<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Users */
/* @var $form ActiveForm */
?>
<div class="payment">

    <div class="pricing__cont">

        <div class="payment__block">

                <div class="inputForm__title">
                    <span><?= Yii::t('user/change-password', 'Change_password_step_2') ?></span><b></b>
                </div>

                <?php $form = ActiveForm::begin(['id' => 'form-changePassword']); ?>

                    <?= $form->field($model, 'token')->label(false)->hiddenInput(['value' => Yii::$app->request->get('token')]); ?>

                    <div class="inputForm__cont">
                        <div class="inputForm__box">

                            <?= $form->field($model, 'old_password')
                                ->passwordInput(['placeholder' => $model->getAttributeLabel('old_password')])
                                ->label(false)
                            ?>

                            <?= $form->field($model, 'new_password')
                                ->passwordInput(['placeholder' => $model->getAttributeLabel('new_password')])
                                ->label(false)
                            ?>

                            <?= $form->field($model, 'repeat_password')
                                ->passwordInput(['placeholder' => $model->getAttributeLabel('repeat_password')])
                                ->label(false)
                            ?>

                            <div class="form-group" style="text-align: center; margin-top: 0px;">
                                <input type="submit" value="<?= Yii::t('user/change-password', 'Change_Password') ?>" class="btn-big" name="ChangePasswordStep2" style="width: 100%;  margin-top: 0px;" />
                                <div class="img-progress" title="loading..."></div>
                            </div>

                        </div>
                    </div>

                <?php ActiveForm::end(); ?>

        </div>

    </div>

</div>
<!-- END .tables -->