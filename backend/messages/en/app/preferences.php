<?php
return [
    'adminEmail' => "Admin Email",
    'RestorePatchTTL' => "Time to store patches for rollback changes (in seconds)",
    'user.passwordResetTokenExpire' => "Password token lifetime (in seconds)",

    'ContactCountNoCaptcha' => "The number of requests in the support from the same IP before the appearance of ReCaptcha",
    'LoginCountNoCaptcha' => "The number of allowed invalid logins from the same IP before the appearance of ReCaptcha",
    'RegisterCountNoCaptcha' => "The number of allowed registrations from the same IP before the appearance of ReCaptcha",
    'ResetPasswordCountNoCaptcha' =>"The number of password recovery from the same IP to the appearance of ReCaptcha",
    'reCaptchaGoogleAcc' => "Google Account Information (e.g. login and password)",
    'reCaptchaPublicKey' => "ReCaptcha Public Key",
    'reCaptchaSecretKey' => "ReCaptcha Secret Key",

    'SignalAccessKey' => "Access key to API for signaling server",

    'BonusPeriodLicense' => "The bonus period of the license upon payment expiration (in hours)",
    'BonusTrialForEmailConfirm' => "Bonus to trial license for Email confirmation (in days)",
    'InviteLockPeriod' => "Invites blocking period for repeated invitation for user from business (in hours)",
    'PriceOneTimeForLicenseProfessional'  => "Professional license price One Time <br /><span class=\"small\" style=\"color: #FF0000; font-weight: bold;\">(set cost for unlimited period)</span>",
    'PricePerMonthForLicenseProfessional' => "Professional license price per month  <br /><span class=\"small\" style=\"color: #FF0000; font-weight: bold;\">(set cost for 1 month)</span>",
    'PricePerMonthUserForLicenseBusiness' => "Business license price per user/month  <br /><span class=\"small\" style=\"color: #FF0000; font-weight: bold;\">(set cost for 1 month)</span>",
    'PricePerYearForLicenseProfessional'  => "Professional license price per year  <br /><span class=\"small\" style=\"color: #FF0000; font-weight: bold;\">(set cost for 1 month)</span>",
    'PricePerYearUserForLicenseBusiness'  => "Business license price per user/year <br /><span class=\"small\" style=\"color: #FF0000; font-weight: bold;\">(set cost for 1 month)</span>",

    'paypalSellerEmail' => "E-mail PayPal account (seller)",
    'createLogOfUserActions' => "Create log of users actions <br /><span class=\"small\" style=\"color: #FF0000; font-weight: bold;\">(3::only get; 2::only post; 1:: all; 0::disable)</span>",
    'createLogOfUserAlerts'  => "Create log of users Alerts <br /><span class=\"small\" style=\"color: #FF0000; font-weight: bold;\">(1:: enable; 0::disable)</span>",
];