<?php
defined('FLASH_MESSAGES_TTL') or define('FLASH_MESSAGES_TTL', 6000);
defined('CREATE_ABSOLUTE_URL') or define('CREATE_ABSOLUTE_URL', false);

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/../../common/config/params-emails.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        'log',
    ],
    'controllerNamespace' => 'frontend\controllers',
    'sourceLanguage' => 'en',
    'language' => 'en', //'en-US',
    'timeZone' => 'UTC',
    'modules' => [
        'api' => [
            'class' => 'frontend\modules\api\Api',
        ],
        'paypal' => [
            'class' => 'frontend\modules\paypal\PayPal',
        ],
        'cryptonator' => [
            'class' => 'frontend\modules\cryptonator\Api',
        ],
        'elfind' => [
            'class' => 'frontend\modules\elfind\elFind',
        ],
        'down' => [
            'class' => 'frontend\modules\download\Download',
        ],
    ],
    'components' => [
        'reCaptcha' => [
            'name' => 'reCaptcha',
            'class' => 'himiklab\yii2\recaptcha\ReCaptcha',
            //'siteKey' => '6LdtRlcUAAAAAJF568JUc1NOKM2BCDCcFUeZj9GO',
            //'secret' => '6LdtRlcUAAAAABBlgn1EAYiEMMLoKredruh0tngs',
        ],

        'user' => [
            'identityClass' => 'common\models\Users',
            'enableAutoLogin' => true,
            //http://developer.uz/blog/%D1%81%D0%BF%D0%BE%D1%81%D0%BE%D0%B1%D1%8B-%D0%BF%D0%BE%D0%B4%D0%BA%D0%BB%D1%8E%D1%87%D0%B5%D0%BD%D0%B8%D1%8F-%D0%BF%D0%BE%D0%B2%D0%B5%D0%B4%D0%B5%D0%BD%D0%B8%D0%B9-behavior-%D0%B2-yii2/
            /*
            'on ' . \yii\web\User::EVENT_AFTER_LOGIN => function ( $event ) {
                var_dump($event);
                exit;
            }
            */
            'on ' . \yii\web\User::EVENT_AFTER_LOGIN => ['common\models\Users', 'afterLogin'],
        ],

        'urlManager' => [

            //https://github.com/codemix/yii2-localeurls
            'class' => 'codemix\localeurls\UrlManager',
            'languages' => ['en'],
            //'languages' => ['en', 'de', 'es', 'ru'],
            'enableDefaultLanguageUrlCode' => false,
            'enableLanguagePersistence' => true,
            'enableLanguageDetection' => true,
            'ignoreLanguageUrlPatterns' => [
                // route pattern => url pattern
                //'#^user/count-new-notifications#' => '#^user/count-new-notifications#',
                '#^blog/*#' => '#^blog/*#',
                '#^api/*#' => '#^api/*#',
                '#^paypal/*#' => '#^paypal/*#',
                '#^cryptonator/*#' => '#^cryptonator/*#',
                // Добавить исключения для модулей пейпал, криптонатор и др.
                //'#^elfind/*#' => '#^elfind/*#',
            ],

            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // Страницы для которых жестко прописаны акшены в SiteController (просто что бы заменить /site/action на /action)
                [
                    'pattern' => '<action:login|support|entrance|purchase|maintenance|system-fault|self-hosted>/<id:[\w\-]*>',
                    'route' => 'site/<action>', 'defaults' => ['id' => 1]
                ],

                [
                    'pattern' => '<action:status>/<code_error:\w*>/<val:\w*>',
                    'route' => 'site/<action>', 'defaults' => ['code_error' => 'unknown', 'val' => '']
                ],

                // Реврайты для PayPal модуля
                ['pattern' => 'paypal',                              'route' => 'paypal/default/index'],
                ['pattern' => 'paypal/<action>',                     'route' => 'paypal/default/<action>'],

                // Реврайты для АПИ
                ['pattern' => 'api/conferences',                     'route' => 'api/conferences/index'],
                ['pattern' => 'api/conferences/<action>',            'route' => 'api/conferences/index'],
                ['pattern' => 'api/self-hosted',                     'route' => 'api/self-hosted/index'],
                ['pattern' => 'api/self-hosted/<action>',            'route' => 'api/self-hosted/index'],
                ['pattern' => 'api/signal',                          'route' => 'api/signal/index'],
                ['pattern' => 'api/signal/<action>',                 'route' => 'api/signal/index'],
                ['pattern' => 'api/events',                          'route' => 'api/file-events/index'],
                ['pattern' => 'api/events/<action>',                 'route' => 'api/file-events/index'],
                ['pattern' => 'api/sharing',                         'route' => 'api/sharing/index'],
                ['pattern' => 'api/sharing/<action>',                'route' => 'api/sharing/index'],
                ['pattern' => 'api',                                 'route' => 'api/default/index'],
                ['pattern' => 'api/upload',                          'route' => 'api/default/upload'],
                ['pattern' => 'api/<action>',                        'route' => 'api/default/index'],
                ['pattern' => 'cryptonator',                         'route' => 'cryptonator/default/index'],
                ['pattern' => 'cryptonator/<action>',                'route' => 'cryptonator/default/index'],

                // Реврайты для модулей
                ['pattern' => 'file/<share_hash>',                   'route' => 'down/file/'],
                ['pattern' => 'folder/<share_group_hash>',           'route' => 'down/folder/'],
                ['pattern' => 'folder/<share_group_hash>/<file_id>', 'route' => 'down/folder/'],

                // Страницы которые прописаны в БД, контроллер PageController
                ['class'   => 'frontend\components\UrlRouter'],

                // Все остальное отправляем на контроллер SiteController акшен static
                [
                    'pattern' => '<action:features|pricing|faq|terms|privacy|sla|about|cookie-polices|third-party-licenses|affiliate>/<id:\w*>',
                    //'pattern' => '<action:\w+>/<id:\w*>',
                    'route' => 'site/static', 'defaults' => ['id' => 1]
                ],
            ],
        ],
        'log' => [
            //'traceLevel' => YII_DEBUG ? 3 : 0,
            'traceLevel' => YII_DEBUG ? 3 : 3,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\FileTarget', //в файл
                    'categories' => ['payment_fail'], //категория логов
                    'logFile' => '@runtime/logs/pay-fail.log', //куда сохранять
                    'logVars' => [] //не добавлять в лог глобальные переменные ($_SERVER, $_SESSION...)
                ],
                [
                    'class' => 'yii\log\FileTarget', //в файл
                    'categories' => ['payment_success'], //категория логов
                    'logFile' => '@runtime/logs/pay-success.log', //куда сохранять
                    'logVars' => [] //не добавлять в лог глобальные переменные ($_SERVER, $_SESSION...)
                ],
                [
                    'class' => 'yii\log\FileTarget', //в файл
                    'categories' => ['payment_created'], //категория логов
                    'logFile' => '@runtime/logs/pay-created.log', //куда сохранять
                    'logVars' => [] //не добавлять в лог глобальные переменные ($_SERVER, $_SESSION...)
                ],
                [
                    'class' => 'yii\log\EmailTarget', //шлет на e-mail
                    'categories' => ['payment_success', 'payment_fail', 'payment_created'],
                    //'mailer' => 'yii\swiftmailer\Mailer',
                    'logVars' => [],
                    'message' => [
                        'from' => ['robot@pvtbox.net' => 'Pvtbox-Mail-Bot'], //от кого
                        'to' => ['progr000@gmail.com'], //кому
                        'subject' => 'Информация по платежу. Лог в теле сообщения.', //тема
                    ],
                ],
                // пример настройки логирования
                // https://klisl.com/yii2-logs.html
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'i18n' => [
            'translations' => [
                'models*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages',
                    'sourceLanguage' => 'en-US',
                ],
                'forms*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages/' . DESIGN_THEME,
                    'sourceLanguage' => 'en-US',
                ],
                'search*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages/' . DESIGN_THEME,
                    'sourceLanguage' => 'en-US',
                ],
                'user*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages/' . DESIGN_THEME,
                    'sourceLanguage' => 'en-US',
                ],
                'modules*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages/' . DESIGN_THEME,
                    'sourceLanguage' => 'en-US',
                ],
                'mail*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages/' . DESIGN_THEME,
                    'sourceLanguage' => 'en-US',
                ],
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages/' . DESIGN_THEME,
                    'sourceLanguage' => 'en-US',
                    /*
                    'fileMap' => [
                        'app'                => 'app.php',
                        'app/bd_models'      => 'bd_models.php',
                        'app/header'         => 'header.php',
                        'app/footer'         => 'footer.php',
                        'app/flash-messages' => 'flash-messages.php',
                        'app/pages'          => 'pages.php',
                        'app/error'          => 'error.php',
                    ],
                    */
                ],

            ],
        ],
        'assetManager' => [
            'appendTimestamp' => APPEND_TIMESTAMP_FOR_CSS_JS,
            'forceCopy' => APPEND_TIMESTAMP_FOR_CSS_JS,
            'bundles' => [
                'yii\bootstrap\BootstrapAsset' => [
                    'css' => (DISABLE_BOOTSTRAP_CSS
                        ? []
                        : ['css/bootstrap.css']),
                ],
                'yii\bootstrap\BootstrapPluginAsset' => [
                    'js' => (DISABLE_BOOTSTRAP_PLUGIN_JS
                        ? []                       // для v20190812 дизайна
                        : ['js/bootstrap.min.js']), // для orange дизайна
                ],
//                'yii\web\JqueryAsset' => [
//                    'sourcePath' => null,   // не опубликовывать комплект
//                    'js' => [
//                        'themes/orange/js/jquery.2.x/jquery.min.js',
//                    ]
//                ],
                'yii\web\JqueryAsset' => [
                    'js' => [
                        'jquery.min.js',
                    ],
                    'jsOptions' => [
                        //'async' => true,
                    ],
                ],
                'yii\web\YiiAsset' => [
                    'jsOptions' => [
                        'defer' => true,
                    ],
                ],
                'yii\widgets\ActiveFormAsset' => [
                    'jsOptions' => [
                        'defer' => true,
                    ],
                ],
                'yii\validators\ValidationAsset' => [
                    'jsOptions' => [
                        'defer' => true,
                    ],
                ],
            ],
        ],
        'view' => [
            //https://lan143.ru/blog/posts/raspolozenie-css-fajlov-v-konce-body-v-yii-2
            //'class' => 'lan143\advanced_view\View',
            //https://yiiframework.com.ua/ru/doc/guide/2/output-theming/
            'theme' => [
                'basePath' => '@app/themes/' . DESIGN_THEME,
                'baseUrl' => '@web/themes/' . DESIGN_THEME,
                'pathMap' => [
                    '@app/views'   => [
                        '@app/themes/holidays',        //Сначала будет искать файлы виевов тут, и если их нет то
                        '@app/themes/' . DESIGN_THEME, // тогда уже тут, таким образом можно подменять на праздники основной виев на праздничный
                    ],
                    //'@app/views/layouts' => '@app/themes/' . DESIGN_THEME . '/layouts',
                    '@app/modules'       => '@app/themes/' . DESIGN_THEME . '/modules',
                    '@app/widgets'       => '@app/themes/' . DESIGN_THEME . '/widgets',
                    '@app/page'          => '@app/themes/' . DESIGN_THEME . '/page',
                ],
            ],
        ],
    ],
    'params' => $params,
];
