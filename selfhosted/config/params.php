<?php
return [
    // Системные параметры не стоит менять не разобравшись
    'RegisterCacheKey'      => md5('register' . $_SERVER['REMOTE_ADDR'] . 'register'),
    'LoginCacheKey'         => md5('login' . $_SERVER['REMOTE_ADDR'] . 'login'),
    'ContactCacheKey'       => md5('contact' . $_SERVER['REMOTE_ADDR'] . 'contact'),
    'ResetPasswordCacheKey' => md5('reset' . $_SERVER['REMOTE_ADDR'] . 'reset'),
    'ShuCacheKey'           => md5('shu' . $_SERVER['REMOTE_ADDR'] . 'shu'),
    'reCaptchaApiLink'      => 'http://www.google.com/recaptcha/api.js?hl=',

    // Использовать сжатые или несжатые файлы стилей {styleName}.min.css или {styleName}.css
    'use_minimized_css' => true,
];
