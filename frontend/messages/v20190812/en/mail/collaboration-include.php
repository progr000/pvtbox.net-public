<?php
return [
    'from_email' => "robot@pvtbox.net",
    'from_name'  => "{app_name}",
    'subject'    => "You are added as a collaborator",
    'body_html'  => '
        <div class="password-reset">
            <p>Hi {user_name},</p>
            <br />
            <p>User {UserOwner_name} added you as a collaborator.</p>
            <p>Message:</p>
            --------------------------
            <br />
            <pre>{invite_colleague_message}</pre>
            --------------------------
            <br /><br />
            <p>Please follow the link {collaboration_include_link} to view collaboration.</p>
        </div>
    ',
    'body_text'  => '
Hi {user_name}, 

User {UserOwner_name} added you as a collaborator.
 Message:
--------------------------
{invite_colleague_message}
--------------------------

Please follow the link {collaboration_include_link} to view collaboration',
];
