<?php
if (!isset($_SERVER['REMOTE_ADDR'])) { $_SERVER['REMOTE_ADDR'] = '127.0.0.1'; }
// Here you can initialize variables that will be available to your tests
$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../../common/config/main.php'),
    //require(__DIR__ . '/../../../common/config/main-local.php'),
    require(__DIR__ . '/../../../frontend/config/main.php'),
    require(__DIR__ . '/../../../frontend/config/main-local.php')//,
    //require(__DIR__ . '/../../../frontend/config/tests-local.php')
);
//var_dump($config['components']['queue']);
unset($config['components']['queue']);
unset($config['components']['queue2']);
/*
foreach ($config['bootstrap'] as $k=>$v) {
    if (in_array($v, ['queue', 'queue2'])) {
        //array_splice($config['bootstrap'], $k, 1);
        unset($config['bootstrap'][$k]);
    }
}
$config['bootstrap'] = array_values($config['bootstrap']);
*/
//var_dump($config['bootstrap']);exit;
$config = yii\helpers\ArrayHelper::merge(
    $config,
    require(__DIR__ . '/../../../frontend/config/tests-local.php')
);
//var_dump($config['components']['queue']); exit;

$application = new yii\web\Application($config);
//$application = new yii\console\Application($config);

