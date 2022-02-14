<?php
return [
    'from_email' => "robot@pvtbox.net",
    'from_name'  => "{app_name}",
    'subject'    => "Installation instructions for Self-Hosted Pvtbox.",
    'body_html'  => '
      <div class="password-reset">
          <p>Hi, {user_name}.</p>
          <br />
          <p>Thank you for creating a Self-Hosted Pvtbox account on ' . Yii::getAlias('@selfHostedWeb') . '.</p>
          <br />
          <p>Your license key is:.</p>
          <p><code>{user_key}</code></p>
          <p>You will need to enter that key during self-hosted installation process.</p>
          <br />
          <p>To install Self-Hosted Pvtbox on your linux (Debian, Ubuntu, CentOS or similar) server:</p>
          <p>run </p>
          <p><code>wget https://installer.pvtbox.net/self-hosted/release/pvtbox-install.sh -O pvtbox-install.sh && bash pvtbox-install.sh</code></p>
          <p>and follow instructions.</p>
          <br />
          <br />
          <p>You can find Self-Hosted user guide on <a href=' . Yii::getAlias('@docsWeb') . '/pvtbox-user-guide-self-hosted-variant>https://docs.pvtbox.net</a></p>
      </div>
  ',
    'body_text'  => '
      Hi, {user_name}.

      Thank you for creating a Self-Hosted Pvtbox account on ' . Yii::getAlias('@selfHostedWeb') . '.

      Your license key is:
      {user_key}.
      You will need to enter that key during self-hosted installation process.

      To install Self-Hosted Pvtbox on your linux (Debian, Ubuntu, CentOS or similar) server:
      run
      `wget https://installer.pvtbox.net/self-hosted/release/pvtbox-install.sh -O pvtbox-install.sh && bash pvtbox-install.sh`
      and follow instructions.


      You can find Self-Hosted user guide on ' . Yii::getAlias('@docsWeb') . '/pvtbox-user-guide-self-hosted-variant
  ',
];