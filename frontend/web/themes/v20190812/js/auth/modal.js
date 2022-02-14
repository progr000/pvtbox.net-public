/**
 * show Signup
 */
function showSignup()
{
    $( "#trigger-signup-dialog" ).trigger( "click" );
}

/**
 * show Login
 */
function showLogin()
{
    $( "#trigger-login-dialog" ).trigger( "click" );
}


/**
 * show Reset form
 */
function showResetPassword()
{
    $( "#trigger-reset-password-dialog" ).trigger( "click" );
}

/**
 * show Reset form
 */
function showRequestResetOk()
{
    //$( "#trigger-reset-password-ok-dialog" ).trigger( "click" );
}

/** ************** */
$(document).ready(function() {

    $(document).on('click', '.signup-dialog', function() {
        showSignup();
    });

    $(document).on('click', '.login-dialog', function() {
        showLogin()
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
        showRequestResetOk();
    }

    $('#reset-password-modal').on('hidden.bs.modal', function (e) {
        if (window.location.href.indexOf('entrance') > 0) {
            showLogin();
        }
    });

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