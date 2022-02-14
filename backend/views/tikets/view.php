<?php
/****DELETE-IT-FILE-IN-SH****/
/* @var $this yii\web\View */
/* @var $tiket common\models\Tikets */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $AnswerTiketForm \backend\models\forms\AnswerTiketForm */

use yii\helpers\Html;
use yii\widgets\ListView;
use yii\bootstrap\ActiveForm;

$this->title = $tiket->tiket_theme;
//$this->params['breadcrumbs'][] = ['label' => 'Tikets Messages', 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
//var_dump($dataProvider->allModels); exit;
?>

<div class="tikets-messages-view">
    <?= Html::a('К списку тикетов', ['/tikets'], ['class' => 'btn btn-primary']) ?>
    <br />
    <br />

    <div class="row">
        <div class="col-sm-1">Тема:</div>
        <div class="col-sm-8"><?= $tiket->tiket_theme ?></div>
    </div>
    <div class="row">
        <div class="col-sm-1">Создан:</div>
        <div class="col-sm-8"><?= $tiket->tiket_created ?></div>
    </div>
    <div class="row">
        <div class="col-sm-1">Автор:</div>
        <div class="col-sm-8">
            <?=
            ($dataProvider->allModels[0]['user_email'])
                ? $dataProvider->allModels[0]['user_name'] . " &lt;" . $dataProvider->allModels[0]['user_email'] . "&gt;"
                : $tiket->tiket_name . " &lt;" . $tiket->tiket_email . "&gt;" ?></div>
    </div>

    <br />

    <div class="row">
        <div class="col-sm-10">
            <?=
            ListView::widget([
                'dataProvider' => $dataProvider,
                'itemOptions' => ['class' => 'item'],
                'layout' => "{items}\n{pager}",
                //'layout' => "{pager}\n{summary}\n{items}\n{pager}",
                //'summary' => 'Показано {count} из {totalCount}',
                'itemView' => function ($model, $key, $index, $widget) {
                    if ($model['admin_id'] > 0) { $bgcolor_au = "#FFD3D5"; } else { $bgcolor_au = "#9acfea"; }
                    if (($model['message_read_admin'] == 0) && $model['admin_id'] == 0) { $bgcolor_r = "#E7E7E7"; } else { $bgcolor_r = "#FFFFFF"; }
                    return
                        "<div style='background-color: {$bgcolor_au}; padding: 2px; color: #0000CC; font-weight: bold;'>" .
                            (
                                ($model['admin_id'] > 0)
                                    ? "Вы"
                                    : (
                                        ($model['user_email'])
                                            ? Html::a(
                                                '<span class="glyphicon glyphicon-user"></span>',
                                                ['/users/view', 'id' => $model['user_id']],
                                                [
                                                    'title' => 'Профиль пользователя',
                                                    'data-pjax' => '0',
                                                    'target' => '_blank',
                                                ]
                                            ) . "&nbsp;" . $model['user_name']  . " &lt;" . $model['user_email'] ."&gt;"
                                            : $model['tiket_name'] . " &lt;" . $model['tiket_email'] ."&gt;"
                                    )
                            ) .
                            " ({$model['message_created']})" .
                        "</div>" .
                        "<div style='background-color: {$bgcolor_r}; border: 1px dotted #000000; padding: 5px; margin-bottom: 5px;'>" .
                            nl2br($model['message_text']) .
                        "</div>";
                },
            ]);
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-10">

            <?php $form = ActiveForm::begin([
                'id'     => 'answer-tiket-form',
                'action' => '/tikets/answer'
            ]); ?>

            <?= $form->field($AnswerTiketForm, 'tiket_id')->hiddenInput()->label(false); ?>

            <?= $form->field($AnswerTiketForm, 'message_text')->textArea(['rows' => 6]) ?>

            <div class="form-group">
                <?= Html::submitButton('Ответить', ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
                &nbsp;
                <?= Html::a('Отмена', ['/tikets'], ['class' => 'btn btn-default']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>

</div>
