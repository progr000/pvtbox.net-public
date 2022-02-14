<?php
return [
    'from_email' => "robot@pvtbox.net",
    'from_name'  => "{app_name}",
    'subject'    => "Password recovery",
    'body_html'  => '
        <div class="password-reset">
            <p>Hi, {user_name}.</p>

            <p>Please reset password for your {app_name} account by clicking the link below:</p>

            <p><a href="{reset_password_link}">{reset_password_link}</a></p>
        </div>
    ',
    'body_text'  => '
        Hi, {user_name}.

        Please reset password for your {app_name} account by clicking the link below:

        {reset_password_link}
    ',
];
