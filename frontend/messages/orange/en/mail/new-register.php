<?php
return [
    'from_email' => "robot@pvtbox.net",
    'from_name'  => "{app_name}",
    'subject'    => "Confirm your registration on {app_name}",
    'body_html'  => '
        <div class="password-reset">
            <p>Hi, {user_name}.</p>

            <p>Thanks for creating a {app_name} account. To continue, please confirm your email address by clicking the link below:</p>

            <p><a href="{confirm_registration_link}">{confirm_registration_link}</a></p>
        </div>
    ',
    'body_text'  => '
        Hi, {user_name}.

        Thanks for creating a {app_name} account. To continue, please confirm your email address by clicking the link below:

        {confirm_registration_link}
    ',
];
