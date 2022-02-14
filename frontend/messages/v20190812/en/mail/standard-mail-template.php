<?php
use yii\helpers\Url;

return [
    'from_email' => "support@pvtbox.net", // can override in $data['from_email']
    'from_name'  => "{app_name}",      // can override in $data['from_name']
    'subject'    => "empty",            // can override in $data['subject']
    'body_html'  => '
        <div class="message-body">
            <p>
                <pre><code>{body}</code></pre>
            </p>
            <br /><br />
            <p style="font-size: 10px; color: #cccccc;">This message was sent from a contact form on the site <a href="' . Url::to(['/'], CREATE_ABSOLUTE_URL) . '">' . Url::to(['/'], true) . '</a></p>
        </div>
    ',
    'body_text'  => '

--------------------------------------------------------
{body}
--------------------------------------------------------


This message was sent from a contact form on the site ' . Url::to(['/'], true) . '
',
];
