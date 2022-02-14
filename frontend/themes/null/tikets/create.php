<?php
/* @var $this yii\web\View */
/* @var $tiket common\models\Tikets */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model \frontend\models\forms\CreateTiketForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = "Создание нового тикета";
//$this->params['breadcrumbs'][] = ['label' => 'Tikets Messages', 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
//var_dump($dataProvider->allModels); exit;
?>

<div class="tikets-messages-view">
    <?= Html::a('К списку тикетов', ['/tikets'], ['class' => 'btn btn-primary']) ?>

    <div class="row">
        <div class="col-sm-6">

            <?php $form = ActiveForm::begin([
                'id' => 'create-tiket-form',
            ]); ?>

            <p></p>

            <?= $form->field($model, 'tiket_theme'); ?>

            <?= $form->field($model, 'message_text')->textArea(['rows' => 6]) ?>

            <div class="form-group">
                <?= Html::submitButton('Создать', ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
                &nbsp;
                <?= Html::a('Отмена', ['/tikets'], ['class' => 'btn btn-default']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>

</div>
