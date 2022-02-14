<?php
return [
    'from_email' => "robot@pvtbox.net",
    'from_name'  => "{app_name}",
    'subject'    => "Share link for {app_name}",
    'body_html'  => '
        <div class="password-reset">
            <p>Hi {user_name},</p>

            <p>Share link: {share_link}</p>
        </div>
    ',
    'body_text'  => '
        Hi {user_name},

        Share link: {share_link}
    ',
];
