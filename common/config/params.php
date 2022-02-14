<?php
return [
    'userHashSalt' => 'vifewiCD32FD32568cdsd',

    // Node Virtual FS
    'nodeVirtualFS' => str_replace('/', DIRECTORY_SEPARATOR, Yii::getAlias('@frontend'))
        . DIRECTORY_SEPARATOR
        . 'runtime'
        . DIRECTORY_SEPARATOR
        . 'NodeFS',

    'userUploadsDir' => str_replace('/', DIRECTORY_SEPARATOR, Yii::getAlias('@frontend'))
        . DIRECTORY_SEPARATOR
        . 'runtime'
        . DIRECTORY_SEPARATOR
        . 'NodeFS'
        . DIRECTORY_SEPARATOR
        . '_userUploads',

    'userUploadsDir_for_XAccelRedirect' => 'NodeFS' . DIRECTORY_SEPARATOR . '_userUploads',

    'logUploadsDir' => str_replace('/', DIRECTORY_SEPARATOR, Yii::getAlias('@frontend'))
        . DIRECTORY_SEPARATOR
        . 'runtime'
        . DIRECTORY_SEPARATOR
        . 'logUploads',

    'date_format' => "d.m.Y",
    'datetime_format' => "d.m.Y H:i:s",
    'datetime_short_format' => "d.m.Y H:i",
    'datetime_fancy_format' => "\$1 H:i",

    // Приостановить работу с АПИ и ФМ например в случае проведения технических работ
    'Stop_NodeApi_and_FM' => false,

    'timeout_resend_confirm' => 300,

    // Указывает что это self-hosted версия сайта. (Если SH то в апи и контроллерах будут всяческие ограничения)
    'self_hosted' => false,

    'online_office_ext' => [
        'doc', 'docm', 'docx', 'dot', 'dotm', 'dotx', 'epub', 'fodt', 'htm', 'html', 'mht', 'odt', 'ott',
        'pdf', 'rtf', 'txt', 'djvu', 'xps', 'csv', 'fods', 'ods', 'ots', 'xls', 'xlsm', 'xlsx', 'xlt', 'xltm', 'xltx',
        'fodp', 'odp', 'otp', 'pot', 'potm', 'potx', 'pps', 'ppsm', 'ppsx', 'ppt', 'pptm', 'pptx',
    ],

    // email and name that the system uses to substitute in the from field when sending letters
    'robot_email_from' => 'robot-test@pvtbox.net',
    'robot_name_from'  => 'Mailer-robot on {app_name}'
];
