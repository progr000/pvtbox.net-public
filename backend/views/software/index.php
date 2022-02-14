<?php
/****DELETE-IT-FILE-IN-SH****/
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use kartik\grid\GridView;
use common\models\Software;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\SoftwareSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $modelVersion \common\models\Software */

$this->title = 'Applications Management';
//$this->params['breadcrumbs'][] = $this->title;
$modelVersion = new Software();
?>
<div class="download-links-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="row">
        <div class="col-lg-6">
                <?= Html::a('Upload application', ['create'], ['class' => 'btn btn-success']) ?>
        </div>

        <?php $form = ActiveForm::begin([
            'enableAjaxValidation' => true,
            'enableClientValidation' => true,
            'validateOnSubmit' => true,
            'action' => ['update-version-for-all'],
            'options' => ['enctype' => 'multipart/form-data'],
        ]); ?>
        <div class="col-lg-4">

                <?= $form->field($modelVersion, 'software_version', ['enableAjaxValidation' => true])
                    ->textInput([
                        'maxlength' => true,
                        'placeholder' => "Enter new software version here",
                    ])
                    ->label(false);
                ?>

        </div>
        <div class="col-lg-2">
            <input type="submit" />

        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <?php
    Pjax::begin();
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'filterModel' => null,

        'panel' => [
            'before' => false,
            'after' => "",
        ],
        //'summary' => 'Показаны записи с <b>{begin, number}</b> по <b>{end, number}</b> из <b>{totalCount, number}</b>.',

        'columns' => [
            [
                'attribute' => 'software_id',
                'width' => '80px',
            ],
            [
                'attribute' => 'software_sort',
                'width' => '80px',
            ],
            [
                'attribute' => 'software_type',
                'width' => '150px',
                'filter' => Software::linkTypes(),
                'value' => function ($data) {
                    return Software::getType($data->software_type);
                },
            ],

            'software_description',

            [
                'attribute' => 'software_version',
                'width' => '100px',
            ],

            'software_created',
            //'software_updated',

            [
                'attribute' => 'software_status',
                'width' => '150px',
                'filter' => Software::linkStatuses(),
                'value' => function ($data) {
                    return Software::getStatus($data->software_status);
                },
            ],

            [
                'class' => 'kartik\grid\ActionColumn',
                'width' => '80px;',
                'vAlign' => 'top',
                'template' => '{view} {update} {delete}',
            ],

        ],
    ]);
    Pjax::end();
    ?>

</div>
