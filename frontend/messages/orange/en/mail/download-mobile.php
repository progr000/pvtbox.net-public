<?php
return [
    'from_email' => "robot@pvtbox.net",
    'from_name'  => "{app_name}",
    'subject'    => "Download and install the {app_name} application",
    'body_html'  => '
        <div class="password-reset">
            <p>Hi {user_name},</p>

            <p>Please follow the link below to download and install our application:</p>

            <p><a href="{download_app_link}">{download_app_link}</a></p>
        </div>
    ',
    'body_text'  => '
        Hi {user_name},

        Please follow the link below to download and install our application:

        {download_app_link}
    ',
];
