<?php
/* @var $content string */

use frontend\assets\orange\MaintenanceAsset;

MaintenanceAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->render('head'); ?>
</head>
<body>

<?php $this->beginBody() ?>

    <?= $content; ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>