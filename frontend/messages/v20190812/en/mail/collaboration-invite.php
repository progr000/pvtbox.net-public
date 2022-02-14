<?php
return [
    'from_email' => "robot@pvtbox.net",
    'from_name'  => "{app_name}",
    'subject'    => "You are invited to collaborate",
    'body_html'  => '
        <div class="password-reset">
            <p>Hi {user_name},</p>
            <br />
            <p>User {UserOwner_name} invited you to collaborate.</p>
            <p>Message:</p>
            --------------------------
            <br />
            <pre>{invite_colleague_message}</pre>
            --------------------------
            <br /><br />
            <p>Please follow the link {collaboration_invite_link} to join collaboration.</p>
        </div>
    ',
    'body_text'  => '
Hi {user_name}, 

User {UserOwner_name} invited you to collaborate.
Message:
--------------------------
{invite_colleague_message}
--------------------------

Please follow the link {collaboration_invite_link} to join collaboration',
];
