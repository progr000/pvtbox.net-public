<?php
defined('SQL_DATE_FORMAT') or define('SQL_DATE_FORMAT', "Y-m-d H:i:s");
defined('MUTEX_WAIT_TIMEOUT') or define('MUTEX_WAIT_TIMEOUT', 5);

return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'name' => 'Pvtbox',
    'sourceLanguage' => 'en',
    'language' => 'en', //'en-US',
    'timeZone' => 'UTC',
    'aliases' => [
        '@frontendDomain' => 'pvtbox.net',
        '@frontendWeb' => 'https://pvtbox.net',
        '@selfHostedDomain' => 'self-hosted.pvtbox.net',
        '@selfHostedWeb' => 'https://self-hosted.pvtbox.net',
        '@docsDomain' => 'docs.pvtbox.net',
        '@docsWeb' => 'https://docs.pvtbox.net',
        //'@backend_uploads_fs' => '@backend' . DIRECTORY_SEPARATOR. 'web' . DIRECTORY_SEPARATOR . 'uploads',
        //'@backend_uploads_web' => 'uploads/',
    ],
    'modules' => [
        'gridview' =>  [
            'class' => '\kartik\grid\Module'
            // enter optional module parameters below - only if you need to
            // use your own export download action or custom translation
            // message source
            // 'downloadAction' => 'gridview/export/download',
            // 'i18n' => []
        ],
    ],
    'bootstrap' => [
        'queue',  // Главная очередь для копирования папок и коллабораций
        'queue2', // Дополнительная очередь для админ задач
    ],
    'components' => [
        /*
        'queue' => [
            // Такой вариант драйвера запускает задание очереди
            // сразу синхронно в этом же процессе, где и создается задание
            // Возможно удобно иногда использовать для отладки
            'class' => \yii\queue\sync\Queue::class,
            'handle' => true,
        ],
        */
        'queue' => [
            'class' => \yii\queue\file\Queue::class,
            'path' => '@console/runtime/queue',
            'as log' => \yii\queue\LogBehavior::class,
            'ttr' => 60 * 60, // Максимальное время выполнения задания
            'attempts' => 1, // Максимальное кол-во попыток

            //This command obtains and executes tasks in a loop until the queue is empty:
            //yii queue/run
            //This command launches a daemon which infinitely queries the queue:
            //yii queue/listen

            //'class' => \yii\queue\sync\Queue::class,
            //'handle' => true, // Флаг необходимости выполнять поставленные в очередь задания
            // /usr/bin/php /var/www/Direct-link/yii queue/listen --verbose=1
            // https://github.com/yiisoft/yii2-queue/blob/master/docs/guide-ru/worker.md
            // https://github.com/yiisoft/yii2-queue/blob/2.0.1/docs/guide/usage.md#multiple-queues
        ],
        /*
        'queue2' => [
            // Такой вариант драйвера запускает задание очереди
            // сразу синхронно в этом же процессе, где и создается задание
            // Возможно удобно иногда использовать для отладки
            'class' => \yii\queue\sync\Queue::class,
            'handle' => true,
        ],
        */
        'queue2' => [
            'class' => \yii\queue\file\Queue::class,
            'path' => '@console/runtime/queue2',
            'as log' => \yii\queue\LogBehavior::class,
            'ttr' => 60 * 60, // Максимальное время выполнения задания
            'attempts' => 1, // Максимальное кол-во попыток
        ],
        'mutex' => [
            'class'     => 'yii\mutex\FileMutex',
            'mutexPath' => '@common/runtime/mutex',
            'dirMode'   => 0777,
            'fileMode'  => 0666,
        ],
    ],
];
