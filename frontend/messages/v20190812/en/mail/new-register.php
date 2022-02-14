<?php
return [
    'from_email' => "robot@pvtbox.net",
    'from_name'  => "{app_name}",
    'subject'    => "Confirm your registration on {app_name}",
    'body_html'  => '
        <div class="password-reset">
            <p>Hi, {user_name}.</p>
            <br />
            <p>Thanks for creating a {app_name} account. To continue, please confirm your email address by clicking the link below:</p>
            <br />
            <p><a href="{confirm_registration_link}">{confirm_registration_link}</a></p>
            <br />
            <br />
            <p>You can find SaaS user guide on <a href=' . Yii::getAlias('@docsWeb') . '/pvtbox-user-guide-saas-variant>https://docs.pvtbox.net</a></p>
        </div>
    ',
    'body_text'  => '
        Hi, {user_name}.

        Thanks for creating a {app_name} account. To continue, please confirm your email address by clicking the link below:

        {confirm_registration_link}


        You can find SaaS user guide on ' . Yii::getAlias('@docsWeb') . '/pvtbox-user-guide-saas-variant
    ',
];
