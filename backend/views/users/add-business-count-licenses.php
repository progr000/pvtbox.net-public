<?php

use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */

/* @var $UserModel common\models\Users */

$model = new \backend\models\forms\AddLicenseCount();
?>

<?php $form = ActiveForm::begin(['action' => '/users/add-license-count?id=' . $UserModel->user_id]); ?>

    <?= $form->field($model, 'license_count')
        ->textInput([
            'maxlength' => true,
            'placeholder' => $model->getAttributeLabel('license_count')
        ])->label(false)
    ?>

    <input type="submit" value="Add" name="add_license_count">

<?php ActiveForm::end(); ?>