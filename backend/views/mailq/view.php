<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Mailq */

$this->title = $model->mailer_letter_id;
//$this->params['breadcrumbs'][] = ['label' => 'Mailqs', 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mailq-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('To the list', ['index'], ['class' => 'btn btn-default']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'mailer_letter_id',
            'template_key',
            'mail_created',
            'remote_ip',
            'mailer_letter_status',
            [
                'attribute' => 'mailer_description',
                'format' => 'raw',
                'value' => nl2br($model->mailer_description),
            ],
            'mail_from',
            'mail_to',
            'mail_reply_to',
            'mail_subject',
            [
                'attribute' => 'mail_body',
                'format' => 'raw',
                'value' => nl2br($model->mail_body),
            ],
            [
                'attribute' => 'mailer_answer',
                'format' => 'raw',
                'value' => nl2br($model->mailer_answer),
            ],
            'user_id',
            'node_id',
        ],
    ]) ?>

</div>
