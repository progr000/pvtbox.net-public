$(document).ready(function() {
    $(document).on('click', '.close-alert', function() {
        $.ajax({
            type: 'post',
            url: _LANG_URL + '/user/alert-dialogs',
            data: {
                dialog: $(this)[0].hasAttribute('data-flash-dialog') ? $(this).attr('data-flash-dialog') : "unknown_dialog",
                alert_id: $(this)[0].hasAttribute('data-alert-id') ? $(this).attr('data-alert-id') : "unknown_id",
                show: 0
            },
            dataType: 'json',
            statusCode: {
                200: function(response) {
                    if (("status" in response) && ("alert_id" in response) && ("dialog" in response)) {
                        //if (response.status) {
                            $('#' + response.alert_id).hide();
                        //}
                    }
                },
                500: function(response) {
                    console_log(response);
                    alert('An internal server error occurred.');
                }
            }
        });
    });

    $(document).on('click', '.mc-snackbar-close', function() {
        //var $alert = $(this).parent().parent();
        //var $content_alert = $alert.parent();
        //console_log($content_alert.children().length);
        $(this).parent().parent().remove();
    });

    var alert_data = [];
    var i = 0;
    $('#alert-block-container').find('.alert').each(function() {
        if (!IS_GUEST && createLogOfUserAlerts) {
            if ($(this).is(':visible')) {
                alert_data[i] = {
                    message: $(this).html(),
                    closeButton: 0,
                    ttl: 0,
                    action: null,
                    viewType: 'flash',
                    type: $.trim($(this)
                        .attr('class')
                        .replace('in', '')
                        .replace('fade', '')
                        .replace('alert-', '')
                        .replace('alert', ''))
                };
                //alert-danger alert fade in

                var $but = $(this).find('button.close').first();
                if ($but.length) {
                    var repl = $but[0].outerHTML;
                    alert_data[i].closeButton = 1;
                    alert_data[i].message = alert_data[i].message.replace(repl, '');
                }
                alert_data[i].message = $.trim(alert_data[i].message);
                alert_data[i].type = $(this).data('type') ? $(this).data('type') : 'unknown';

                if ($(this)[0].hasAttribute('data-ttl')) {
                    alert_data[i].ttl = $(this).attr('data-ttl');
                }

                if ($(this)[0].hasAttribute('data-alert-action')) {
                    if ($.trim($(this).attr('data-alert-action')) != '') {
                        alert_data[i].action = $(this).attr('data-alert-action');
                    }
                }

                i++;
            }
        }
        if ($(this)[0].hasAttribute('data-ttl')) {
            if ($(this).attr('data-ttl') > 0) {
                $(this).delay($(this).attr('data-ttl')).fadeOut('slow', function () {
                    $(this).remove();
                    if ($(this)[0].hasAttribute('data-auto-close-callback')) {
                        var funct = new Function($(this).attr('data-auto-close-callback'));
                        funct();
                    }
                });
            }
        }
    });

    /* Логирование данных об алертах */
    prepareAlertData(alert_data, 'alert-block-container');
});

/**
 *
 * @param alert_data
 */
function prepareAlertData(alert_data, container_id) {
    if (!IS_GUEST && createLogOfUserAlerts) {
        //console_log(alert_data);
        if (alert_data.length) {
            var data = {
                alert_data: alert_data,
                url: window.location.href,
                screen: ''
            };
            if (typeof html2canvas !== 'undefined') {
                var container_for_capture = document.getElementById(container_id);
                if (container_for_capture !== null) {
                    html2canvas(container_for_capture).then(function (canvas) {

                        document.body.appendChild(canvas);

                        // Get base64URL
                        //var base64URL = canvas.toDataURL('image/jpeg').replace('image/jpeg', 'image/octet-stream');

                        data.screen = canvas.toDataURL('image/jpeg').replace('image/jpeg', 'image/octet-stream');
                        //alert(data.screen);
                        document.body.removeChild(canvas);
                        // AJAX request
                        logAlertData(data);
                    });
                } else {
                    logAlertData(data);
                }
            } else {
                logAlertData(data);
            }
        }
    }
}

/**
 *
 * @param data
 */
function logAlertData(data)
{
    console_log(data);
    $.ajax({
        type: 'post',
        url: _LANG_URL + '/user/register-alert-data',
        data: data,
        dataType: 'json',
        statusCode: {
            200: function (response) {
                if ("status" in response && "data" in response && response.status) {
                    console_log(response.status);
                }
                /*
                $(document).find('canvas').each(function() {
                    $(this).remove();
                });
                */
            },
            404: function (response) {
                console_log('Error: 404 Not Found.');
            },
            500: function (response) {
                console_log(response);
                //alert('An internal server error occurred.');
            }
        }
    });
}

/**
 *
 * @param message
 * @param type
 * @param timeout
 * @param replace
 */
function snackbar(message, type, timeout, replace, action)
{
    if (!action) { action = null; }
    var message_text;
    var el = document.getElementById('flash-' + message);
    var m_class = ('class_' + message.hashCode()).replace('-', '');
    //console_log(m_class);
    if (!(el === null)) {
        message_text = $('#flash-' + message).html();
    } else {
        message_text = message;
    }
    if (!(replace === null)) {
        if (typeof replace == 'object') {
            message_text = message_text.replace(/\{([a-zA-Z0-9\_]+)\}/g, function (s, e) {
                return replace[e];
            });
        }
    }

    var $mcsnackbar = $('#alert-template').find('.mc-snackbar').first().clone();
    $mcsnackbar.addClass(m_class);

    $mcsnackbar.find('.mc-snackbar-icon').first()
        .removeClass()
        .addClass('mc-snackbar-icon ' + type);
    var $test = $('#alert-snackbar-container').find('.' + m_class).first();
    if ($test.length) {
        $test.remove();
    }

    $mcsnackbar.find('.mc-snackbar-title').first().html(message_text);
    $mcsnackbar.show();
    $('#alert-snackbar-container').append($mcsnackbar);

    if (timeout) {
        $mcsnackbar.delay(timeout).fadeOut(300, function () {
            $(this).remove();
            //$mcsnackbar.remove();
        });
    }

    /* Логирование данных об алертах */
    prepareAlertData([{
        message: message_text,
        closeButton: 1,
        ttl: timeout,
        viewType: 'snack',
        type: type,
        action: action
    }], 'alert-snackbar-container');
}

/**
 *
 * @param message
 * @param type
 * @param timeout
 * @param showClose
 * @param replace
 */
function flash_msg(message, type, timeout, showClose, replace, action)
{
    if (!action) { action = null; }
    //console_log(message);
    var message_text;
    var el = document.getElementById('flash-' + message);
    var m_class = ('class_flash_' + message.hashCode()).replace('-', '');
    //console_log(m_class);
    if (!(el === null)) {
        message_text = $('#flash-' + message).html();
    } else {
        message_text = message;
    }
    if (!(replace === null)) {
        if (typeof replace == 'object') {
            message_text = message_text.replace(/\{([a-zA-Z0-9\_]+)\}/g, function (s, e) {
                return replace[e];
            });
        }
    }

    var $flash = $('#flash-tpl').find('.alert').first().clone();
    $flash.addClass(m_class);
    $flash.addClass('alert-' + type);

    var $test = $('#alert-block-container').find('.' + m_class).first();
    if ($test.length) {
        $test.fadeOut(100, function () {
            $(this).remove();
        })
    }

    if (!showClose) {
        $flash.find('button').first().remove();
    }

    $flash.find('.flash-message').first().html(message_text);
    $flash.show();
    if (timeout) {
        $flash.delay(timeout).fadeOut(300, function () {
            $(this).remove();
        });
    }
    //console_log($flash);
    $('#alert-block-container').append($flash);

    /* Логирование данных об алертах */
    prepareAlertData([{
        message: message_text,
        closeButton: showClose ? 1 : 0,
        ttl: timeout,
        viewType: 'flash',
        type: type,
        action: action
    }], 'alert-block-container');
}