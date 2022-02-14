<?php
return [
    'from_email' => "robot@pvtbox.net",
    'from_name'  => "{app_name}",
    'subject'    => "Your license is about to expire",
    'body_html'  => '
        <div class="password-reset">
            <p>Hi {user_name},</p>

            <p>Your license ({license_type}) is due to expire on (till {license_expire}). Please follow the <a href="{pay_link}">link</a> to renew the subscription.</p>
        </div>
    ',
    'body_text'  => '
        Hi {user_name},

        Your license ({license_type}) is due to expire on (till {license_expire}). Please follow the link {pay_link} to renew the subscription.
    ',
];
