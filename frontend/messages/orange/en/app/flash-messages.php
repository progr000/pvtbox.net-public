<?php
use yii\helpers\Url;

return [
    /* button */
    'Close'                                 => "Close",

    /* /themes/orange/layouts/alert_dialogs */
    'Confirm_email'                         => "Please confirm your email address to always get the latest updates from us and take advantage of all the benefits your account provides. <a href=\"#\" data-toggle=\"modal\" data-target=\"#resend-confirm-modal\">Confirm e-mail</a>",
    'Confirm_email_plus_trial'              => "Please confirm your email address to always get the latest updates from us and take advantage of all the benefits your account provides. <a href=\"#\" data-toggle=\"modal\" data-target=\"#resend-confirm-modal\">Confirm e-mail</a>",
    'No_Nodes_Online'                       => "You don't have any nodes online. No actions possible. Mapping files are shown based on previous metadata.",
    'Sync_Devices_restriction'              => "Sync across devices isn't available for free license. Please upgrade to <a href=\"" . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . "\">PRO/Business</a>",

    /* /frontend/models/PaypalPaysCheck */
    'YouCanceledPay'                        => "You are canceled the payment.",
    'ErrorWrongParametersGiven'             => "PayPal payments failed. Error: wrong parameters given.",
    'ErrorWrongPayerID'                     => "PayPal payments failed. Error: wrong PayerID.",
    'ErrorWrongUser'                        => "PayPal payments failed. Error: wrong user.",
    'ErrorWrongToken'                       => "PayPal payments failed. Error: wrong token.",
    'PaymentsAlreadyProcessed'              => "PayPal payments already processed.",
    'PaymentsSuccess'                       => "PayPal payments success.",
    'ErrorPyaPalSavingFail'                 => "PayPal payments failed. Error: PyaPal saving fail.",
    'ErrorTransferSavingFail'               => "PayPal payments failed. Error: transfer saving fail.",
    'ValidationFail'                        => "PayPal payments failed. Error: validation fail.",

    /* /frontend/controllers/UserController */
    'ResendConfirm_success'                 => "Further instruction has been sent to your email.",
    'ResendConfirm_error'                   => "Failed generate token. Try again later.",
    'ConfirmRegistration_success'           => "E-mail successfully confirmed.",
    'ConfirmRegistration_error'             => "Confirmation of E-mail is failed. Try again.",
    'LoginByToken_error'                    => "Login failed. Wrong token.",
    'RequestPasswordReset_success'          => "Check your email for further instructions.",
    'RequestPasswordReset_error'            => "Sorry, we are unable to reset password for email provided.",
    'ResetChangePassword_success'           => "Password changed successfully.",
    'Profile_ChangePasswordForm_success'    => "Check your email for further instructions.",
    'Profile_ChangePasswordForm_error'      => "Sorry, we are unable to send email.",
    'Profile_ChangeNameForm_success'        => "Your name was changed.",
    'Profile_ChangeNameForm_error'          => "Change name failed.",
    'Profile_ChangeEmailForm_success'       => "Your E-Mail was changed.",
    'Profile_ChangeEmailForm_error'         => "Change E-Mail failed.",
    'Profile_ChangeTimeZone_success'        => "Time zone successfully changed",
    'Profile_ChangeTimeZone_error'          => "Some error on time zone change",
    'ChangePassword_success'                => "Password changed successfully.",
    'ChangePassword_error'                  => "Password not changed",
    'BalanceInfo_success'                   => "Successful",
    'BalanceInfo_error'                     => "Fail",
    'ShareSendToEmail_success'              => "Successfully sent to E-Mail.",

    /* /frontend/controllers/TiketsController */
    'Answer_success'                        => "Answer was successfully sent.",
    'Answer_error'                          => "There was an error on sending email.",
    'Answer_secure_error'                   => "Secure error.",
    'Create_success'                        => "The ticket has been successfully created.",
    'Create_error'                          => "There was an error on ticket create.",

    /* /frontend/controllers/SiteController */
    'Support_success'                       => "Thank you for contacting us. We will respond to you as soon as possible.",
    'Support_error'                         => "There was an error sending email.",

    /* /frontend/controllers/DownloadController */
    'Download_success'                      => "Message with download-link was successfully sent to E-Mail.",
    'Download_error'                        => "There was an error on sending email.",

    /* /frontend/controllers/AdminPanelController */
    'Index_ChangeNameForm_success'          => "<div style=\"padding: 5px 0 5px 0;\">Company name was changed.</div>",
    'Index_ChangeNameForm_error'            => "Change name failed.",

    /* ---- */
    'flash_copied_ok'                                       => "Successfully copied to clipboard",
    'flash_share_sent_ok'                                   => "Share-link successfully sent to <b>{email}</b>.",
    'flash_success_changed_for_collaborate_folder'          => "Successfully changed access for user in collaboration",
    'flash_success_deleted_from_collaborate_folder'         => "User successfully removed from collaboration",
    'flash_success_added_to_collaborate_folder'             => "User successfully added into collaboration",
    'flash_success_restored_patch'                          => "File version restored successfully",

    /* ---- */
    'license_minus_businessAdmin_invite_free_or_trial'      => "One license is taken off",
    'license_minus_businessAdmin_invite_non_registered'     => "One license is taken off",

    /* ---- */
    'license_restriction'                                   => "Your license doesn't allow this action.<br />Please upgrade to <a href=\"" . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . "\">PRO/Business</a>",
    'license_restriction_share_dir'                         => "Your license doesn't allow to share folder.<br />Please upgrade to <a href=\"" . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . "\">PRO/Business</a>",
    'license_restriction_3_in24'                            => "Your license has restrictions on count shared files. Allow {license_shares_count_in24} files in 24 hours.<br />Please upgrade to <a href=\"" . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . "\">PRO/Business</a>",
    'license_restriction_share_max_size'                    => "Your license has restrictions on max size of shared file. Allow {license_max_shares_size} for file.<br />Please upgrade to <a href=\"" . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . "\">PRO/Business</a>",
    'license_restriction_free_invite_any'                   => "Your license not allow invite into collaborations any user. <br />Please upgrade to <a href=\"" . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . "\">PRO/Business</a>",
    'license_restriction_trial_invite_free'                 => "Your license not allow invite users with Free license. <br />Please upgrade to <a href=\"" . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . "\">Business</a>",
    'license_restriction_pro_invite_free'                   => "Your license not allow invite users with Free license. <br />Please upgrade to <a href=\"" . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . "\">Business</a>",
    'license_restriction_businessUser_invite_free'          => "Your license doesn't allow this action. Please contact your Business Admin to invite this user",
    'license_restriction_free_invite_non_registered'        => "Your license doesn't allow this action.<br />Please upgrade to <a href=\"" . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . "\">PRO/Business</a>",
    'license_restriction_any_try_join_from_free'            => "license_restriction_any_try_join_from_free",
    'license_restriction_free_add_any'                      => "Your license not allow add any users to collaboration.<br />Please upgrade to <a href=\"" . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . "\">PRO/Business</a>",
    'license_restriction_pro_add_free'                      => "Your license not allow add users with Free license. <br />Please upgrade to <a href=\"" . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . "\">Business</a>",

    'license_restriction_businessAdmin_invite_free_or_trial_but_no_available_licenses'        => "No more license available.",
    'license_restriction_businessAdmin_invite_non_registered_but_no_available_licenses'       => "No more license available.",
    'license_restriction_businessAdmin_invites_the_user_repeatedly'                           => "For security reasons you can't reinvite the same user more than once in 24 hours.",
    'license_restriction_free_invites_businessAdmin_repeatedly'                               => "For security reasons you can't reinvite the same user more than once in 24 hours.",
    'license_restriction_businessAdmin_with_0_available_licenses_try_join_from_free'          => "license_restriction_businessAdmin_with_0_available_licenses_try_join_from_free",

    'license_restriction_free_try_join_from_trial'                                            => "You can't join collaborations with free license.<br />Please upgrade to <a href=\"" . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . "\">PRO/Business</a>",
    'license_restriction_free_try_join_from_pro'                                              => "You can't join collaborations with free license.<br />Please upgrade to <a href=\"" . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . "\">PRO/Business</a>",
    'license_restriction_free_try_join_from_businessUser'                                     => "You can't join collaborations with free license.<br />Please upgrade to <a href=\"" . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . "\">PRO/Business</a>",
    'license_restriction_free_try_join_from_business_but_license_is_expire'                   => "You can't join collaborations with free license.<br />Please upgrade to <a href=\"" . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . "\">PRO/Business</a>",
    'license_restriction_free_try_join_from_business_but_business_has_not_available_licenses' => "You can't join collaborations with free license.<br />Please upgrade to <a href=\"" . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . "\">PRO/Business</a>",

    /* ---- */
    'YourJoinIsAcceptedYouAreQueuedToAdd'                   => "You successfully joined collaboration.",
    'Your_license_expire_soon_payed'                        => "Your license will expire in {days} day(s).<br /><a href=\"{link}\">Renew now</a>",
    'Your_license_expire_soon_free'                         => "Your license will expire in {days} day(s).<br /><a href=\"{link}\">Purchase now</a>",
    'Your_license_is_expired'                               => "Your license has expired.<br /><a href=\"{link}\">Billing</a>",

    /* ---- */
    'Cant_add_self_into_the_list'           => "Can't add self into the list",
    'This_user_already_added_into_the_list' => "This user (email) already added into the list",
    'Successfully_added_into_the_list'      => "Successfully added into the list",
    'To_complete_invitation_select_folder'  => "To complete the invitation, select the folder(s) where the invited user will have access.",
];