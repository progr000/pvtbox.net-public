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
    'id' => 'app-self-hosted-panel',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'selfhosted\controllers',
    'bootstrap' => [
        'log',
    ],
    'sourceLanguage' => 'en',
    'language' => 'en', //'en-US',
    'timeZone' => 'UTC',
    'components' => [
        'user' => [
            'identityClass' => 'common\models\SelfHostUsers',
            'enableAutoLogin' => true,
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
                    'pattern' => '<action:login|support|entrance|purchase|maintenance|self-hosted>/<id:[\w\-]*>',
                    'route' => 'site/<action>', 'defaults' => ['id' => 1]
                ],

                [
                    'pattern' => '<action:status>/<code_error:\w*>/<val:\w*>',
                    'route' => 'site/<action>', 'defaults' => ['code_error' => 'unknown', 'val' => '']
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
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'request' => [
            'parsers' => [
                'text/xml' => 'light\yii2\XmlParser',
                'application/xml' => 'light\yii2\XmlParser',
            ],
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
                    'basePath' => '@frontend/messages/' . DESIGN_THEME,
                    'sourceLanguage' => 'en-US',
                ],
                'search*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@frontend/messages/' . DESIGN_THEME,
                    'sourceLanguage' => 'en-US',
                ],
                'user*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@frontend/messages/' . DESIGN_THEME,
                    'sourceLanguage' => 'en-US',
                ],
                'modules*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@frontend/messages/' . DESIGN_THEME,
                    'sourceLanguage' => 'en-US',
                ],
                'mail*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@frontend/messages/' . DESIGN_THEME,
                    'sourceLanguage' => 'en-US',
                ],
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@frontend/messages/' . DESIGN_THEME,
                    'sourceLanguage' => 'en-US',
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
            'theme' => [
                'basePath' => '@app/themes/' . DESIGN_THEME,
                'baseUrl' => '@web/themes/' . DESIGN_THEME,
                'pathMap' => [
                    '@app/views'   => [
                        '@app/themes/holidays',        //Сначала будет искать файлы виевов тут, и если их нет то
                        '@app/themes/' . DESIGN_THEME, // тогда уже тут, таким образом можно подменять на праздники основной виев на праздничный
                    ],
                ],
            ],
        ],
    ],
    'params' => $params,
];
