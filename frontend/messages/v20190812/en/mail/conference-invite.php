<?php
return [
    'from_email' => "robot@pvtbox.net",
    'from_name'  => "{app_name}",
    'subject'    => "You are invited to conference",
    'body_html'  => '
        <div class="password-reset">
            <p>Hi {user_name},</p>
            <br />
            <p>User {UserOwner_name} invited you to conference &lt;&lt;{conference_name}&gt;&gt;.</p>
            <br />
            <p>Please follow the link {conference_invite_link} to join conference.</p>
        </div>
    ',
    'body_text'  => '
Hi {user_name}, 

User {UserOwner_name} invited you to conference <<{conference_name}>>.

Please follow the link {conference_invite_link} to join conference',
];
