<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\models\forms\RegisterUserBySellerForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\Users;

$this->title = 'Create new User by Seller';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'seller_id')
                ->textInput([
                    'type'         => "hidden",
                ])
                ->label(false)
            ?>

            <?= $form->field($model, 'user_email')
                ->textInput([
                    'type'         => "email",
                    'placeholder'  => $model->getAttributeLabel('user_email'),
                    'autocomplete' => "off",
                ])
                ->label(false)
            ?>

            <?= $form->field($model, 'password')
                ->passwordInput([
                    'placeholder'  => $model->getAttributeLabel('password'),
                    'autocomplete' => "off"
                ])
                ->label(false)
            ?>

            <?= $form->field($model, 'password_repeat')
                ->passwordInput([
                    'placeholder'  => $model->getAttributeLabel('password_repeat'),
                    'autocomplete' => "off"
                ])
                ->label(false)
            ?>

            <?= $form->field($model, 'has_personal_seller')
                ->checkbox([
                    'value' => Users::YES,
                    'checked' => true
                ])
                ->label(true)
            ?>

            <?= $form->field($model, 'send_email_about_registration')
                ->checkbox([
                    'value' => Users::YES,
                    'checked' => true
                ])
                ->label(true)
            ?>

            <?= $form->field($model, 'create_business_account')
                ->checkbox([
                    'id' => 'create-business-account',
                    'value' => Users::YES,
                    'checked' => false
                ])
                ->label(true)
            ?>

            <div id="user-license-count" style="display: none;">
                <?= $form->field($model, 'license_count')
                    ->textInput([
                        'type'         => "text",
                        'placeholder'  => $model->getAttributeLabel('license_count'),
                        'autocomplete' => "off",
                        'value'        => "1",
                        'disabled'     => true,
                    ])
                    ->label(false)
                ?>
            </div>

            <br />

            <div class="form-group">
                <?= Html::submitButton('Create new User', ['class' => 'btn btn-primary', 'name' => 'Create_new_User']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
