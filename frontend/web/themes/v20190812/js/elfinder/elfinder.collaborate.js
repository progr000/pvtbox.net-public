/** ******************************************* COLLAB *********************************************** */

/**
 * Show CollaborateWindow settings or list
 * @param {object} sel
 */
function showCollaborationSettings(sel)
{

    if (sel.cbl_is_owner != 0) {
        showCollaborationDialog(sel.hash);
    } else {
        showColleagueList(sel.hash);
    }
}


/**
 * Show Colleague List popup dialog
 * @param {string} hash
 */
function showColleagueList(hash)
{
    if (!checkNodesOnline(onCommandsNotShowBalloonNodes)) {
        return false;
    }

    /** ++ Init collaborate popup */
    $('#trigger-colleague-list-modal').trigger( "click" );

    $('#collaborate-filesystem-hash').val(hash);
    $('#collaborate-file-uuid').val('');
    var fname = $('#' + hash).find('.elfinder-cwd-filename').first();
    if (fname.length) {
        $('#colleague-list-file-name').html(fname.text()).attr('title', fname.text());
    } else {
        $('#colleague-list-file-name').html('').attr('title', '');
    }

    $('#colleagues-list').html('');
    /** -- Init collaborate popup */

    $.ajax({
        type: 'get',
        url: _LANG_URL + '/elfind?cmd=collaborationDialog&target=' + hash,
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true && typeof response.data != 'undefined') {

                    var data = response.data;
                    $('#leave-collaborate-filesystem-hash').val(data.hash);
                    $('#leave-collaborate-file-uuid').val(data.file_uuid);
                    $('#colleague-list-file-name').html(data.file_name).attr('title', data.file_name);

                    var colleagues_tpl = $('#colleagues_view_tpl').html();

                    var colleagues_list = "";
                    if (typeof response.colleagues != 'undefined') {
                        for (var i = 0; i < response.colleagues.length; i++) {
                            //console_log(response.colleagues[i]);
                            if (response.colleagues[i].access_type == 'owner') {
                                response.colleagues[i].show_can = "hidden";
                                response.colleagues[i].show_is = "";
                            } else {
                                response.colleagues[i].show_can = "";
                                response.colleagues[i].show_is = "hidden";
                            }
                            response.colleagues[i]['date'] = formDate.exec(response.colleagues[i]['ts']);
                            colleagues_list += colleagues_tpl.replace(/\{([a-z\_]+)\}/g, function (s, e) {
                                //console_log(s);
                                //console_log(e);
                                //console_log(response.colleagues[i][e]);
                                return response.colleagues[i][e];
                            });
                        }
                    }
                    $('#colleagues-list-view').html(colleagues_list);

                    createNiceScroll('.scrollbar-program-vertical', false, true);
                    createNiceScroll('.scrollbar-program-horizontal', true, false);
                    setTimeout(function() { reInitNiceScroll(null); }, 100);
                    setTimeout(function() { reInitNiceScroll(null); }, 200);

                } else {
                    $.fancybox.close();
                    elfinderInstance.error(response.info);
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
 * Show collaboration popup dialog
 * @param {string} hash
 */
function showCollaborationDialog(hash)
{
    if (!checkNodesOnline(onCommandsNotShowBalloonNodes)) {
        return false;
    }

    /** ++ Init collaborate popup */
    $('#trigger-collaborate-modal').trigger( "click" );

    $('#collaborate-filesystem-hash').val(hash);
    $('#collaborate-file-uuid').val('');
    var fname = $('#' + hash).find('.elfinder-cwd-filename').first();
    if (fname.length) {
        $('#collaborate-file-name').html(fname.text()).attr('title', fname.text());
    } else {
        $('#collaborate-file-name').html('').attr('title', '');
    }

    $('#colleagues-list').html($('#waiting-tpl').html());
    $('#colleague-email').val('').parent().removeClass('has-error').find('p').first().html('');
    $('#colleague-message').val('');
    $('#invite-message-form').hide();
    $('#colleagues-list-form').show();
    $('#waiting-form').show();
    $('#button-invite-email').removeClass('btn-notActive');
    $('#btn-cancel-collaboration').addClass('btn-notActive');
    /** -- Init collaborate popup */

    $.ajax({
        type: 'get',
        url: _LANG_URL + '/elfind?cmd=collaborationDialog&target=' + hash,
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true && typeof response.data != 'undefined') {

                    var data = response.data;
                    $('#collaborate-filesystem-hash').val(data.hash);
                    $('#collaborate-file-uuid').val(data.file_uuid);
                    $('#collaborate-file-name').html(data.file_name).attr('title', data.file_name);

                    $('#colleagues-list-form').show();

                    var colleagues_tpl = $('#colleagues_tpl').html();

                    var colleagues_list = "";
                    var is_empty_list = true;
                    if (typeof response.colleagues != 'undefined') {
                        for (var i = 0; i < response.colleagues.length; i++) {
                            //console_log(response.colleagues[i]);
                            if (response.colleagues[i]['access_type'] == 'owner') {
                                response.colleagues[i]['isright'] = '';
                                response.colleagues[i]['canright'] = 'hidden';
                                response.colleagues[i]['hideforowner'] = 'hidden';
                                response.colleagues[i]['owner_or_colleague'] = 'is-owner';
                            } else {
                                response.colleagues[i]['isright'] = 'hidden';
                                response.colleagues[i]['canright'] = '';
                                response.colleagues[i]['hideforowner'] = '';
                                response.colleagues[i]['owner_or_colleague'] = 'is-colleague';
                                is_empty_list = false;
                            }
                            if (!(response.colleagues[i].user_id === null)) {
                                response.colleagues[i].button_resend = "hidden";
                            }
                            response.colleagues[i]['date'] = formDate.exec(response.colleagues[i]['ts']);
                            colleagues_list += colleagues_tpl.replace(/\{([a-z\_]+)\}/g, function (s, e) {
                                //console_log(s);
                                //console_log(e);
                                //console_log(response.colleagues[i][e]);
                                return response.colleagues[i][e];
                            });
                        }
                        if (is_empty_list) {
                            colleagues_list = '';
                        }

                        var el_id = $('#collaborate-filesystem-hash').val();
                        if (is_empty_list) {
                            $('#' + el_id).removeClass('collaborated_element');
                            $('#nav-' + el_id).removeClass("collaborated_element");
                            $('#btn-cancel-collaboration').addClass('btn-notActive');
                        } else {
                            $('#' + el_id).addClass('collaborated_element');
                            $('#nav-' + el_id).addClass("collaborated_element");
                            $('#btn-cancel-collaboration').removeClass('btn-notActive');
                        }

                    }

                    $('#colleagues-list').html(colleagues_list);

                    createNiceScroll('.scrollbar-program-vertical', false, true);
                    createNiceScroll('.scrollbar-program-horizontal', true, false);
                    setTimeout(function() { reInitNiceScroll(null); }, 100);
                    setTimeout(function() { reInitNiceScroll(null); }, 200);

                } else {
                    $.fancybox.close();
                    elfinderInstance.error(response.info);
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
 * Allow for user to leave collaboration
 */
function leaveCollaboration()
{
    var file_uuid = $('#leave-collaborate-file-uuid').val();
    if (!file_uuid.length) {
        return false;
    }

    $.ajax({
        type: 'post',
        url: _LANG_URL + '/user/leave-collaboration',
        data: {
            file_uuid : file_uuid,
        },
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true) {

                    var el_id = $('#leave-collaborate-filesystem-hash').val();
                    $.fancybox.close();
                    $('#' + el_id).removeClass('collaborated_element');
                    $('#nav-' + el_id).removeClass("collaborated_element");
                    setTimeout(function() {
                        elfinderInstance.exec('reload');
                    }, 3000);

                } else {
                    //error
                    //console_log(response.info);
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
 * Cancel collaboration for folder (delete all colleagues)
 */
function cancelCollaboration()
{
    var file_uuid = $('#collaborate-file-uuid').val();
    if (!file_uuid.length) {
        return false;
    }

    $('#btn-cancel-collaboration').addClass('btn-notActive');
    $.ajax({
        type: 'post',
        url: _LANG_URL + '/user/cancel-collaboration',
        data: {
            file_uuid : file_uuid,
        },
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true) {

                    $('#colleagues-list').html('');

                    reInitNiceScroll(null);

                } else {
                    snackbar(response.info, 'error', 3000, null, 'cancelCollaboration');
                }

                var el_id = $('#collaborate-filesystem-hash').val();
                if ($('#colleagues-list').find('.is-colleague').length > 0) {
                    $('#' + el_id).addClass('collaborated_element');
                    $('#nav-' + el_id).addClass("collaborated_element");
                } else {
                    $('#' + el_id).removeClass('collaborated_element');
                    $('#nav-' + el_id).removeClass("collaborated_element");
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
 * changeUserCollaborateAccess
 * @param {object} data
 */
function changeUserCollaborateAccess(data)
{
    if (data.access_type && data.colleague_id && data.file_uuid) {
        $('#invite-message').hide();
        $('#waiting-form-on-add').show();
        $('#button-invite-email').addClass('btn-notActive');

        var change_acton = data.colleague_id == 'new' ? "add" : (data.access_type == 'delete' ? "delete" : "edit");
        $.ajax({
            type: 'post',
            url: _LANG_URL + '/user/change-collaboration-access',
            data: {
                action            : change_acton,
                colleague_email   : data.colleague_id == 'new' ? data.colleague_email : "",
                colleague_message : data.colleague_id == 'new' ? data.colleague_message : "",
                file_uuid         : data.file_uuid,
                access_type       : data.access_type,
                access_type_name  : data.access_type_name,
                colleague_id      : data.colleague_id == 'new' ? "" : data.colleague_id,
            },
            dataType: 'json',
            statusCode: {
                200: function(response) {
                    if (response.status == true) {

                        if (response.action == 'add') {
                            response.data['date'] = formDate.exec(response.data['ts']);
                            var colleagues_tpl = $('#colleagues_tpl').html();
                            //console_log(response.data);
                            if (response.data['access_type'] == 'owner') {
                                response.data['show_can'] = "hide";
                                response.data['show_is'] = "";
                                response.data['isright'] = '';
                                response.data['canright'] = 'hidden';
                                response.data['owner_or_colleague'] = 'is-owner';
                            } else {
                                response.data['show_can'] = "";
                                response.data['show_is'] = "hide";
                                response.data['isright'] = 'hidden';
                                response.data['canright'] = '';
                                response.data['owner_or_colleague'] = 'is-colleague';
                            }
                            var colleague_new = colleagues_tpl.replace(/\{([a-z\_]+)\}/g, function(s, e) {
                                //console_log(s);
                                //console_log(e);
                                //console_log(colleagues[i][e]);
                                return response.data[e];
                            });

                            var $owner = $('#colleagues-list').find('.is-owner').first();
                            if (!$owner.length) {
                                var app_owner = $('#owner_tpl').html();

                                app_owner = app_owner.replace(/\{([a-z\_]+)\}/g, function(s, e) {
                                    return response.data[e];
                                });

                                $('#colleagues-list').prepend(app_owner);
                                $owner = $('#colleagues-list').find('.is-owner').first();
                            }

                            if ($owner.length) {
                                $owner.after(colleague_new);
                            } else {
                                $('#colleagues-list').prepend(colleague_new);
                            }

                            $('#colleague-email').val('');
                            $('#colleague-message').val('');
                            $('#invite-message-form').hide();
                            $('#colleagues-list-form').show();
                            $('#button-invite-email').removeClass('btn-notActive');
                        } else if (response.action == 'delete') {
                            if ("data" in response) {
                                if (response.status) {
                                    $('#collaborate-user-' + response.data.colleague_id).remove();
                                }
                                if (!$('#colleagues-list').find('.is-colleague').length) {
                                    $('#colleagues-list').html('');
                                }
                            }

                        } else {
                            $('#collaborate-user-access-type-' + response.data.colleague_id)
                                .attr('data-action', response.data.access_type)
                                .html(response.data.access_type_name);
                        }

                        if ("license_restriction" in response.data) {
                            var restriction_type = 'error';
                            if ("license_restriction_type" in response.data) {
                                restriction_type = response.data.license_restriction_type;
                            }
                            snackbar(response.data.license_restriction, restriction_type, 10000, null, 'changeUserCollaborateAccess.' + change_acton);
                        }

                    } else {
                        if (!("hidden_info" in response) && ("info" in response) && (response.info.length) > 0) {
                            snackbar(response.info, 'error', 10000, null, 'changeUserCollaborateAccess.' + change_acton);
                        }
                    }

                    var el_id = $('#collaborate-filesystem-hash').val();
                    if ($('#colleagues-list').find('.is-colleague').length > 0) {
                        $('#' + el_id).addClass('collaborated_element');
                        $('#nav-' + el_id).addClass("collaborated_element");
                        $('#btn-cancel-collaboration').removeClass('btn-notActive');
                    } else {
                        $('#' + el_id).removeClass('collaborated_element');
                        $('#nav-' + el_id).removeClass("collaborated_element");
                        $('#btn-cancel-collaboration').addClass('btn-notActive');
                    }


                    reInitNiceScroll(null);

                    $('#invite-message').show();
                    $('#waiting-form-on-add').hide();
                    $('#button-invite-email').removeClass('btn-notActive');
                },
                403: function(response) {
                    snackbar(response.responseText, 'error', 3000, 'changeUserCollaborateAccess.' + change_acton);

                    //$('#invite-title-message').show();
                    $('#invite-message').show();
                    $('#waiting-on-add').hide();
                    $('#button-invite-email').removeClass('btn-notActive');
                },
                500: function(response) {
                    console_log(response);
                    alert('An internal server error occurred.');
                }
            }
        });
    }
}


/** ******************************************* DOCUMENT READY *********************************************** */
$(document).ready(function() {

    if (elfinderInstance) {

        $(document).on('beforeSubmit', '#collaborate-form', function () {
            if ($('#button-invite-email').hasClass('btn-notActive')) {
                return false;
            }

            var form = $(this);
            if (form.find('.has-error').length) {
                return false;
            }

            if ($('#colleague-email').val() == '') {
                $('#colleague-email').parent().removeClass('has-success').addClass('has-error').find('p').first().html('E-mail can\'t be empty');
                return false;
            }

            var data = {
                colleague_email: $('#colleague-email').val(),
                colleague_message: $('#colleague-message').val(),
                access_type: $('#collaborate-user-access-type-new').attr('data-action'),
                access_type_name: $('#collaborate-user-access-type-new').text(),
                colleague_id: "new",
                file_uuid: $('#collaborate-file-uuid').val(),
            };
            changeUserCollaborateAccess(data)

            return false;
        });

        $(document).on('input', '#colleague-email', function () {
            if ($(this).val() != '') {
                $('#invite-message-form').show();
                $('#invite-message').show();
                //$('#waiting-form-on-add').hide();
                $('#colleagues-list-form').hide();
            } else {
                $('#invite-message-form').hide();
                $('#colleagues-list-form').show();
            }
        });

        $(document).on('click', '.cancel-collaboration', function () {
            if (!$(this).hasClass('btn-notActive')) {
                cancelCollaboration();
            }
        });

        $(document).on('click', '.leave-collaboration', function () {
            leaveCollaboration();
        });

        $(document).on('click', '.ch-user-collaborate-access', function() {
            var data = {
                colleague_email   : $('#colleague-email').val(),
                colleague_message : $('#colleague-message').val(),
                access_type       : $(this).attr('data-action'),
                access_type_name  : $(this).attr('data-subtext'),
                colleague_id      : $(this).attr('data-tokens'),
                file_uuid         : $('#collaborate-file-uuid').val(),
            };
            console_log(data);
            changeUserCollaborateAccess(data)
        });

        $(document).on('click', '.ch-user-collaborate-access-new', function() {
            $('#collaborate-user-access-type-new')
                .attr('data-action', $(this).attr('data-action'))
                .html($(this).attr('data-subtext'));
        });

    }

});