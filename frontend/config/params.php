<?php
return [
    // Системные параметры не стоит менять не разобравшись
    'RegisterCacheKey'      => md5('register' . $_SERVER['REMOTE_ADDR'] . 'register'),
    'LoginCacheKey'         => md5('login' . $_SERVER['REMOTE_ADDR'] . 'login'),
    'ContactCacheKey'       => md5('contact' . $_SERVER['REMOTE_ADDR'] . 'contact'),
    'ResetPasswordCacheKey' => md5('reset' . $_SERVER['REMOTE_ADDR'] . 'reset'),
    'ShuCacheKey'           => md5('shu' . $_SERVER['REMOTE_ADDR'] . 'shu'),
    'reCaptchaApiLink'      => 'http://www.google.com/recaptcha/api.js?hl=',

    // DownloadLinks
    'downloadSoftwareDir'  => "/uploads/software/",

    // Нужно ли отправлять информацию по событиям event-ам из файлового менеджера на сигнальный сервер
    'sendEventToSignal' => true,

    // Использовать сжатые или несжатые файлы стилей (css) и файлы яваскрипов (js)
    'use_minimized_css' => true,
];
