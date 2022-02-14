<?php
/* @var $model \common\models\Pages */

$this->title = $model->page_title;

Yii::$app->view->registerMetaTag(
    [
        'name' => 'keywords',
        'content' => $model->page_keywords
    ]
);

Yii::$app->view->registerMetaTag(
    [
        'name' => 'description',
        'content' => $model->page_description
    ]
);
?>

<?= $model->page_text ?>
