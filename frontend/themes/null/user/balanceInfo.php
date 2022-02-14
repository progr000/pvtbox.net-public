<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';

use yii\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\Transfers;

/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div class="site-index">
    <div class="body-content">

        <div class="row">
            <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    //'filterModel' => $searchModel,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'transfer_sum',
                            'value' => function ($data) {
                                return number_format($data->transfer_sum, 2, '.', "'");
                            },
                        ],
                        [
                            'attribute' => 'transfer_type',
                            'value' => function ($data) {
                                return Transfers::typeLabel($data->transfer_type);
                            },
                        ],
                        [
                            'attribute' => 'transfer_status',
                            'value' => function ($data) {
                                return Transfers::statusLabel($data->transfer_status);
                            },
                        ],
                        'transfer_created',
                        'transfer_updated',
                        //['class' => 'yii\grid\ActionColumn'],
                    ],
            ]); ?>
        </div>

        <div class="row">
            <div class="col-lg-9">

                <?php $form = ActiveForm::begin(['id' => 'form-signup', 'layout' => 'horizontal']); ?>

                <?= $form->field($model, 'transfer_params')
                         ->hiddenInput(['value' => "user_id=" . Yii::$app->user->identity->getId(),])
                         ->label(false) ?>

                <?= $form->field($model, 'transfer_sum',
                                    [
                                        'inputTemplate' => '<div class="input-group"><span class="input-group-addon">$</span>{input}</div>',
                                        'horizontalCssClasses' => ['wrapper' => 'col-sm-3'],
                                    ]) ?>

                <?= $form->field($model, 'transfer_type')->dropDownList(Transfers::typeLabels()) ?>

                <div class="form-group">
                    <label class="control-label col-sm-3" for="transferform-transfer_type"></label>
                    <div class="col-sm-6">
                        <?= Html::submitButton('Пополнить баланс', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
                        <div class="help-block help-block-error "></div>
                    </div>
                </div>

                <?php ActiveForm::end(); ?>

            </div>
        </div>


    </div>
</div>
