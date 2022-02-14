<?php
if (!isset($_SERVER['REMOTE_ADDR'])) { $_SERVER['REMOTE_ADDR'] = '127.0.0.1'; }
// Here you can initialize variables that will be available to your tests
$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../../common/config/main.php'),
    //require(__DIR__ . '/../../../common/config/main-local.php'),
    require(__DIR__ . '/../../../console/config/main.php'),
    require(__DIR__ . '/../../../console/config/main-local.php')//,
    //require(__DIR__ . '/../../../common/config/tests-local.php')
);
//var_dump($config['components']['queue']);
unset($config['components']['queue']);
unset($config['components']['queue2']);
$config = yii\helpers\ArrayHelper::merge(
    $config,
    require(__DIR__ . '/../../../common/config/tests-local.php')
);
//var_dump($config['components']['queue']); exit;

//$application = new yii\web\Application($config);
$application = new yii\console\Application($config);

