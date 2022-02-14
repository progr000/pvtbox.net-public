<?php
return [
    'from_email' => "robot@pvtbox.net",
    'from_name'  => "{app_name}",
    'subject'    => "Add new devices",
    'body_html'  => '
        <div class="password-reset">
            <p>
                You have successfully registered!<br />
                Please install {app_name} application in order to start using our service by clicking the link below.
            </p>

            <p>{download_app_link}</p>

            <p>The more devices you connect to your {app_name} account, the faster your private cloud runs.</p>
        </div>
    ',
    'body_text'  => '
        You have successfully registered!
        Please install {app_name} application in order to start using our service by clicking the link below.

        {download_app_link}

        The more devices you connect to your {app_name} account, the faster your private cloud runs.
    ',
];
