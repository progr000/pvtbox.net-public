<?php
/* @var $user \common\models\Users */
/* @var $model_change_name \frontend\models\forms\ChangeNameForm */
/* @var $model_change_oo_address \frontend\models\forms\ChangeOoAddressForm */

use yii\bootstrap\ActiveForm;

?>
<form class="company-frm">
    <div class="form-group">
        <input type="text"
               value="<?= $user->user_company_name ?>"
               placeholder="<?= Yii::t('forms/change-name-form', 'Company_name') ?>"
               aria-label="<?= Yii::t('forms/change-name-form', 'Company_name') ?>"
               readonly="readonly"
               disabled="disabled" />
        <button class="btn edit-value js-open-form" type="button" data-src="#change-name-popup">
            <svg class="icon icon-edit">
                <use xlink:href="#edit"></use>
            </svg><span><?= Yii::t('user/admin-panel', 'Change_company_name') ?></span>
        </button>
    </div>
    <div class="form-group">
        <input type="text"
               value="<?= $user->user_oo_address ?>"
               placeholder="<?= Yii::t('forms/change-name-form', 'oo_address') ?>"
               aria-label="<?= Yii::t('forms/change-name-form', 'oo_address') ?>"
               readonly="readonly"
               disabled="disabled" />
        <button class="btn edit-value js-open-form" type="button" data-src="#change-oo-address-popup">
            <svg class="icon icon-edit">
                <use xlink:href="#edit"></use>
            </svg><span><?= Yii::t('user/admin-panel', 'Change_oo_address') ?></span>
        </button>
    </div>
</form>
<!-- begin .popups-->
<div class="popup top-popup" id="change-name-popup">
    <div class="popup__inner">
        <?php $form = ActiveForm::begin([
            'id' => 'form-change-name',
            'action'=>['index'],
            'options' => [
                'class'    => "img-progress-form",
            ],
        ]); ?>
            <div class="popup-form-title"><?= Yii::t('user/admin-panel', 'Enter_new_Company_name') ?></div>

            <?=
            $form->field($model_change_name, 'user_name', [
                'template'=>'{label}{input}{hint}{error}',
                'options' => [
                    'tag' => 'div',
                    'class' => 'user-name-field'
                ],
            ])->textInput([
                'placeholder' => $model_change_name->getAttributeLabel('user_name'),
                'autocomplete' => "off",
                'aria-label'  => $model_change_name->getAttributeLabel('user_name'),
            ])->label(false)
            ?>

            <input class="btn primary-btn wide-btn"
                   type="submit" value="<?= Yii::t('forms/login-signup-form', 'Save') ?>" />
            <div class="img-progress" title="loading..."></div>
        <?php ActiveForm::end(); ?>
    </div>
    <button class="btn popup-close-btn js-close-popup" type="button" title="<?= Yii::t('app/common', "Close") ?>">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg>
    </button>
</div>

<div class="popup top-popup" id="change-oo-address-popup">
    <div class="popup__inner">
        <?php $form = ActiveForm::begin([
            'id' => 'form-change-oo-address',
            'action'=>['index'],
            'options' => [
                'class'    => "img-progress-form",
            ],
        ]); ?>
        <div class="popup-form-title"><?= Yii::t('user/admin-panel', 'Enter_new_oo_address') ?></div>

        <?=
        $form->field($model_change_oo_address, 'user_oo_address', [
            'template'=>'{label}{input}{hint}{error}',
            'options' => [
                'tag' => 'div',
                'class' => 'user-name-field'
            ],
        ])->textInput([
            'placeholder'  => Yii::t('forms/change-name-form', 'oo_address'),
            'autocomplete' => "off",
            'aria-label'   => Yii::t('forms/change-name-form', 'oo_address'),
        ])->label(false)
        ?>

        <input class="btn primary-btn wide-btn"
               type="submit" value="<?= Yii::t('forms/login-signup-form', 'Save') ?>" />
        <div class="img-progress" title="loading..."></div>
        <?php ActiveForm::end(); ?>
    </div>
    <button class="btn popup-close-btn js-close-popup" type="button" title="<?= Yii::t('app/common', "Close") ?>">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg>
    </button>
</div>
<!-- end .popups-->
