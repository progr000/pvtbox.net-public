<?php
/* @var $content string */

use frontend\assets\v20190812\MaintenanceAsset;

MaintenanceAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->render('head', [
        'user' => Yii::$app->user->identity,
        'no_show_loader_for_site' => true,
    ]); ?>
</head>
<body>

<?php $this->beginBody() ?>

    <?= $content; ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>