/**
 *
 */
function checkSignupOrLogin()
{
    if ($('#radio-login').is(':checked')) {
        showLogin();
    } else {
        showSignup();
    }
}

/**
 * show Signup
 */
function showSignup()
{
    $('#radio-login').prop('checked', false);
    $('#radio-login').parent().removeClass('active');
    $('#radio-signup').prop('checked', true);
    $('#radio-signup').parent().addClass('active');
    $('#rules-tab').hide();
    $('#login-tab').hide();
    $('#signup-tab').show();
    if ($('#loginform-user_email').val().length) { $('#signupform-user_email').val($('#loginform-user_email').val()); }
    if ($('#loginform-password').val().length) { $('#signupform-password').val($('#loginform-password').val()); }
    //$('#signup-login-tabs a[href="#signup-tab"]').tab('show');
    if ($('#signup-login-modal').length) {
        $('#signup-login-modal').modal({"show": true});
        getCaptcha('signup');
    }
    if (window.location.href.indexOf('entrance') > 0) {
    //if ($('#signup-tab').length) {
        getCaptcha('signup');
    }
}

/**
 * show Login
 */
function showLogin()
{
    $('#radio-signup').prop('checked', false);
    $('#radio-signup').parent().removeClass('active');
    $('#radio-login').prop('checked', true);
    $('#radio-login').parent().addClass('active');
    $('#rules-tab').hide();
    $('#signup-tab').hide();
    $('#login-tab').show();
    if ($('#signupform-user_email').val().length) { $('#loginform-user_email').val($('#signupform-user_email').val()); }
    if ($('#signupform-password').val().length) { $('#loginform-password').val($('#signupform-password').val()); }
    //$('#signup-login-tabs a[href="#login-tab"]').tab('show');
    if ($('#signup-login-modal').length) {
        $('#signup-login-modal').modal({"show": true});
        getCaptcha('login');
    }
    if (window.location.href.indexOf('entrance') > 0) {
    //if ($('#login-tab').length ) {
        getCaptcha('login');
    }
}

/**
 * show Rules
 */
function showRules()
{
    $('#radio-login').prop('checked', false);
    $('#radio-login').parent().removeClass('active');
    $('#radio-signup').prop('checked', true);
    $('#radio-signup').parent().addClass('active');
    $('#login-tab').hide();
    $('#signup-tab').hide();
    $('#rules-tab').show();
    //$('#signup-login-tabs a[href="#rules-tab"]').tab('show');
    if ($('#signup-login-modal').length) {
        $('#signup-login-modal').modal({"show": true});
    }
}

/**
 * show Reset form
 */
function showResetPassword()
{
    if ($('#signup-login-modal').length) {
        $('#signup-login-modal').modal('hide');
    }

    if ($('#reset-password-modal').length) {
        $('#reset-password-modal').modal({"show": true});
        getCaptcha('reset');
    }
}

/**
 * show Reset form
 */
function showRequestResetSended()
{
    $('#signup-login-modal').modal('hide');
    $('#reset-password-modal').modal('hide');
    if ($('#request-reset-sent-modal').length) {
        $('#request-reset-sent-modal').modal({"show": true});
    }
}

/**
 * It's deprecated function
 * It's deprecated function
 * It's deprecated function
 * It's deprecated function
 * Gets captcha for Login or Register form
 *
 * @param string action
 * @returns {boolean}
 */
function getCaptcha(action)
{
    return false;
}
function __getCaptcha(action)
{
    /** It's deprecated function */
    return false;
    /** It's deprecated function */

    //if (action != 'login' &&
    //    action != 'signup' &&
    //    action != 'signup2' &&
    //    action != 'contact' &&
    //    action != 'reset') {
    //    return false;
    //}
    ////console_log(action);
    //$('#form-' + action).on('onsubmit', function() { return false; });
    //$('.captcha-container').html('');
    //
    ////compact
    //var compact = "";
    //if (window.location.href.indexOf('compact') > 0) {
    //    compact = "&compact";
    //}
    //
    //$.ajax({
    //    type: 'POST',
    //    url:  _LANG_URL + '/user/get-captcha',
    //    data: 'action=' + action + compact,
    //    dataType: 'json',
    //    statusCode: {
    //        // OK
    //        200: function(response) {
    //            //console_log(response); return false;
    //            if ($.trim(response.html).length) {
    //                //console_log($.base64.decode(response.html));
    //                if ($('#' + action + '-captcha-container').length) {
    //                    $('#' + action + '-captcha-container').html($.base64.decode(response.html));
    //                    //eval("(function() {if (!window['___grecaptcha_cfg']) { window['___grecaptcha_cfg'] = {}; };if (!window['___grecaptcha_cfg']['render']) { window['___grecaptcha_cfg']['render'] = 'onload'; };window['__google_recaptcha_client'] = true;var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;po.src = 'https://www.gstatic.com/recaptcha/api2/r20160531110558/recaptcha__ru.js';var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);})();");
    //                    eval($.base64.decode(response.jseval));
    //                }
    //            }
    //            //$(document).ready(function () {
    //                $('.modal:visible').each(reposition);
    //                //$('#form-' + action).on('onsubmit', function() { return true; }).removeAttr('onsubmit');
    //                $('#form-' + action).on('onsubmit', function() { return true; });
    //                $('#form-' + action).attr('onsubmit', 'return true');
    //
    //            //});
    //        },
    //        // Bad request
    //        400: function(response) { console_log(response); },
    //        // Not found
    //        404: function(response) { console_log(response); },
    //        500: function(response) {
    //            console_log(response);
    //            alert('An internal server error occurred.');
    //        }
    //    }
    //});
    //
    //return true;
}

$(document).ready(function() {

    if (window.location.href.indexOf('login') <= 0 && window.location.href.indexOf('support') <= 0) {
        getCaptcha('signup2');
    }
    if (window.location.href.indexOf('support') > 0) {
        getCaptcha('contact');
    }

    //checkSignupOrLogin();

    $('#accept-rules').prop('checked', false);
    $('#label-accept-rules').removeClass('active');
    $('#accept-rules2').prop('checked', false);
    $('#label-accept-rules2').removeClass('active');
    $(document).on('click', '#label-accept-rules', function () {
        if ($('#accept-rules').prop('checked')) {
            $('#accept-rules').prop('checked', false);
            $(this).removeClass('active');
        } else {
            $('#accept-rules').prop('checked', true);
            $(this).addClass('active');
        }
    });
    $(document).on('click', '#label-accept-rules2', function () {
        if ($('#accept-rules2').prop('checked')) {
            $('#accept-rules2').prop('checked', false);
            $(this).removeClass('active');
        } else {
            $('#accept-rules2').prop('checked', true);
            $(this).addClass('active');
        }
    });
    $(document).on('change', '#radio-signup', function() {
        checkSignupOrLogin();
    });
    $(document).on('change', '#radio-login', function() {
        checkSignupOrLogin();
    });
    $(document).on('click', '.signup-dialog', function() {
        showSignup();
    });
    $(document).on('click', '.login-dialog', function() {
        showLogin()
    });
    $(document).on('click', '.rules-dialod', function() {
        showRules();
    });
    $(document).on('click', '.reset-dialod', function() {
        showResetPassword();
    });
    if (window.location.href.indexOf('signup') > 0) {
        showSignup();
    }
    if (window.location.href.indexOf('login') > 0) {
        showLogin();
    }
    if (window.location.href.indexOf('entrance') > 0) {
        if (window.location.href.indexOf('signup') > 0) {
            showSignup();
        } else {
            showLogin();
        }
    }
    if (window.location.href.indexOf('reset-password-error') > 0) {
        var $passwordresetrequestform = $('.field-passwordresetrequestform-user_email');
        $passwordresetrequestform.addClass('has-error');
        $passwordresetrequestform.find('.help-block-error').first().html($('#flash-request-password-reset-error').text());
        showResetPassword();
    }
    if (window.location.href.indexOf('reset-password') > 0) {
        showResetPassword();
    }
    if (window.location.href.indexOf('request-reset-sent') > 0) {
        showRequestResetSended();
    }
    $(document).on('blur', '#signupform2-user_email', function() {
        getCaptcha('signup2');
    });
    $('#reset-password-modal').on('hidden.bs.modal', function (e) {
        if (window.location.href.indexOf('entrance') > 0) {
            showLogin();
        } else if (window.location.href.indexOf('support') > 0) {
            getCaptcha('contact');
        } else {
            getCaptcha('signup2');
        }
    });
    $('#signup-login-modal').on('hidden.bs.modal', function (e) {
        if (!$('#reset-password-modal').data('bs.modal').isShown) {

            if (window.location.href.indexOf('support') > 0) {
                getCaptcha('contact');
            } else {
                getCaptcha('signup2');
            }
        }
    });

    // Центровка модальных окон при регистрации и авторизации (при переключении табов)
    $('#signup-login-tabs').find('a[data-toggle="tab"]').each(function(){
        $(this).on('shown.bs.tab', function(e) {

            var target = $(e.target).attr("href") // activated tab
            //console_log($(target).attr('data-name'));
            getCaptcha($(target).attr('data-name'));

            $('.modal:visible').each(reposition);
        });
    });

    /*
     $("#form-login").on("afterValidate", function (event, messages) {
     if ($(this).find('.has-error').length) {
     //console_log($.trim($('#login-captcha-container').html()));
     if ($('#login-captcha-container').html() == "") {
     getCaptcha('login');
     }
     }
     });
     */

    $('#loginform-user_email').focusout(function(e) {
        if ($.trim($('#loginform-password').val()) == '') {
            $('#loginform-password')[0].focus();
        }
    });

    $('#loginform-password').focusout(function(e) {
        if ($.trim($('#loginform-user_email').val()) == '') {
            $('#loginform-user_email')[0].focus();
        }
    });
});

function onSubmitLogin()
{
    if ($.trim($('#loginform-user_email').val()) == '') {
        $('#loginform-user_email')[0].focus();
        return false;
    }
    if ($.trim($('#loginform-password').val()) == '') {
        $('#loginform-password')[0].focus();
        return false;
    }
    return true;
}