<?php
/* @var $user \common\models\Users */
/* @var $model_change_name \frontend\models\forms\ChangeNameForm */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\bootstrap\ActiveForm;

?>

<!-- .inputForm -->
<div class="inputForm inputForm--name">

    <div class="inputForm__cont">

        <div class="inputForm__box">

            <div class="form-group">
                <input type="email" class="form-control form-control-notActive" value="<?= $user->user_company_name ?>" placeholder="<?= $user->user_company_name ?>" readonly="readonly" _disabled="disabled" />
            </div>

        </div>

        <div class="inputForm__box">

            <span class="link-change profile-change-name" data-toggle="modal" data-target="#textName"><?= Yii::t('user/admin-panel', 'Change_company_name') ?></span>

        </div>

    </div>

</div>
<!-- END .inputForm -->

<?php
// +++ Modal Company name
Modal::begin([
    'options' => ['id' => 'change-name-modal'],
    'closeButton' => false,
    'header' => '<div type="button" class="close" data-dismiss="modal" aria-label="Close"></div>',
    'size' => '',
]);
?>
    <div class="form-block">
        <?php $form = ActiveForm::begin(['id' => 'form-change-name', 'action'=>['index']]); ?>
        <span class="modal-title"><?= Yii::t('user/admin-panel', 'Enter_new_Company_name') ?></span>
        <?= $form->field($model_change_name, 'user_name'/*, ['enableAjaxValidation' => true]*/)
            ->textInput([
                'placeholder' => $model_change_name->getAttributeLabel('user_name'),
                'autocomplete' => "off",
                //'value' => Yii::$app->user->identity->user_email
            ])
            ->label(false)
        ?>

        <?= '' /*Html::submitButton(Yii::t('user/admin-panel', 'Save'), ['class' => 'btn-big', 'name' => 'ChangeCompanyName'])*/ ?>
        <input type="submit" name="ChangeCompanyName" value="<?= Yii::t('forms/login-signup-form', 'Save') ?>" class="btn-big" />
        <div class="img-progress" title="loading..."></div>

        <?php ActiveForm::end(); ?>
    </div>
<?php
Modal::end();
