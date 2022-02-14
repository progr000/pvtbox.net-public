/**
 * @param {string} action
 * @param {int} node_id
 */
function actionExec(action, node_id)
{
    $.ajax({
        type: 'post',
        url: _LANG_URL + '/user/logout-and-wipe',
        data: {
            action_type    : action,
            target_node_id : node_id,
        },
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (typeof response.data == 'object') {
                    var ret = response.data;

                    if (ret.node_logout_status > 0) {
                        $(document).find('.remote-logout-button-' + ret.target_node_id).each(function () {
                            $(this).html(ret.node_logout_status_text);
                            $(this).addClass('disabled');
                            $(this).attr('onclick', 'return false;');
                        });
                    } else {
                        $(document).find('.remote-logout-button-' + ret.target_node_id).each(function () {
                            $(this).html(ret.node_logout_status_text);
                            $(this).removeClass('disabled');
                            $(this).attr('onclick', "actionExec('logout', " + ret.target_node_id + ")");
                        });
                    }

                    if (ret.node_wipe_status > 0) {
                        $(document).find('.remote-logout-button-' + ret.target_node_id).each(function () {
                            $(this).html(ret.node_logout_status_text);
                            $(this).addClass('disabled');
                            $(this).attr('onclick', 'return false;');
                        });
                        $(document).find('.remote-wipe-button-' + ret.target_node_id).each(function () {
                            $(this).html(ret.node_wipe_status_text);
                            $(this).addClass('disabled');
                            $(this).attr('onclick', 'return false;');
                        });
                    } else {
                        $(document).find('.remote-wipe-button-' + ret.target_node_id).each(function () {
                            $(this).html(ret.node_wipe_status_text);
                            $(this).removeClass('disabled');
                            $(this).attr('onclick', "actionExec('wipe', " + ret.target_node_id + ")");
                        });
                    }
                } else {
                    //error
                    console_log(response.info);
                }
            },
            500: function(response) {
                console_log(response);
                alert('An internal server error occurred.');
            }
        }
    });
}

/**
 *
 * @param {string} action
 * @param {int} node_id
 */
function actionExecDevices(action, node_id)
{
    $.ajax({
        type: 'post',
        url: _LANG_URL + '/user/logout-and-wipe',
        data: {
            action_type    : action,
            target_node_id : node_id
        },
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (typeof response.data == 'object') {
                    var ret = response.data;
                    //console_log(response);

                    /* Логаут */
                    if (ret.node_logout_status > 0) {
                        $('#node-logout-button-' + ret.target_node_id)
                            .html(ret.node_logout_status_text)
                            .addClass('disabled')
                            .removeClass('logout-button');
                    } else {
                        $('#node-logout-button-' + ret.target_node_id)
                            .html(ret.node_logout_status_text)
                            .addClass('logout-button')
                            .removeClass('disabled');
                    }

                    /* Вайп */
                    if (ret.node_wipe_status > 0) {
                        $('#node-logout-button-' + ret.target_node_id)
                            .html(ret.node_logout_status_text)
                            .addClass('disabled')
                            .removeClass('wipe-button');
                        $('#node-wipe-button-' + ret.target_node_id)
                            .html(ret.node_wipe_status_text)
                            .addClass('disabled')
                            .removeClass('wipe-button');
                    } else {
                        $('#node-wipe-button-' + ret.target_node_id)
                            .html(ret.node_wipe_status_text)
                            .addClass('wipe-button')
                            .removeClass('disabled');
                    }
                }
                if ("status" in response) {
                    if (!response.status) {
                        flash_msg(response.info, 'error', 0, true, null, 'user.logout-and-wipe');
                    }
                }
            },
            500: function(response) {
                console_log(response);
                alert('An internal server error occurred.');
            }
        }
    });
}

/**
 *
 */
$(document).ready(function() {

    $(document).on('click', '.logout-button', function () {
        if (checkIsFreeLicense(true)) {
            return false;
        }
        actionExecDevices($(this).attr('data-action'), $(this).attr('data-node-id'));
        return false;
    });

    $(document).on('click', '.wipe-button', function () {
        if (checkIsFreeLicense(true)) {
            return false;
        }
        actionExecDevices($(this).attr('data-action'), $(this).attr('data-node-id'));
        return false;
    });

});
