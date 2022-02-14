<?php
return [
    'from_email' => "robot@pvtbox.net",
    'from_name'  => "{app_name}",
    'subject'    => "Password change for {app_name} account",
    'body_html'  => '
        <div class="password-reset">
            <p>Hello, {user_name}.</p>

            <p>Please change password for your {app_name} account by clicking the link below:</p>

            <p><a href="{change_password_link}">{change_password_link}</a></p>
        </div>
    ',
    'body_text'  => '
        Hi {user_name},

        Please change password for your {app_name} account by clicking the link below:

        {change_password_link}
    ',
];
