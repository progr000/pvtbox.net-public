<?php
return [
    'from_email' => "robot@pvtbox.net",
    'from_name'  => "{app_name}",
    'subject'    => "Guest room link",
    'body_html'  => '
        <div class="password-reset">
            <p>Hi {user_name},</p>

            <p>Guest room link: {guest_room_link}</p>
        </div>
    ',
    'body_text'  => '
        Hi {user_name},

        Guest room link: {guest_room_link}
    ',
];
