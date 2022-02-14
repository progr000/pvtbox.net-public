var onCommandsNotShowBalloonNodes = true;

$(window).on('resize', function(e) {
    resizeFileNameAdmin();
});

$(document).ready(function() {

    resizeFileNameAdmin();

    $(document).on('click', '.release-server-license', function() {
        //$(this).remove();

        var obj = $(this);
        var msg = $(this).attr('data-confirm-text');

        prettyConfirm(function () {

            //setNodeHidden(node_id);
            releaseServerLicense(obj);

        }, null, $.trim(msg), '', '');


        return false;
    });

    $(document).on('click', '.admin-panel-change-collaboration-access', function () {
        var access_type = $(this).attr('data-access-type');
        var data = {
            //colleague_email   : $('#colleague-email').val(),
            action            : access_type == 'delete' ? "delete" : "edit",
            colleague_email   : "",
            colleague_message : "",
            access_type       : access_type,
            access_type_name  : $(this).attr('data-subtext'),
            colleague_id      : $(this).attr('data-colleague-id'),
            file_uuid         : $(this).attr('data-file-uuid'),
        }
        //console_log(data);
        AdminPanelChangeUserCollaborateAccess(data);
    });

    $(document).on('click', '.manager-list__row', function() {
        selectFolder($(this));
    });

    $(document).on('change', '.form-reports-filter', function() {
        $('#form-reports-filter')[0].submit();
        return true;
    });
    $(document).on('change', '#colleaguesreportssearch-created_at_range', function() {
        $('#form-reports-filter')[0].submit();
        return true;
    });
    $(document).on('click', '.clear-filter-report-date', function(e) {
        $('.daterangepicker').hide();
        e.preventDefault();
        e.stopPropagation();
        $('#colleaguesreportssearch-created_at_range').val('');
        $('#form-reports-filter')[0].submit();
        return false;
    });

    $(document).on('click', '.clear-filter-report-search', function() {
        $('#id-colleaguesreportssearch-colleague_user_email').val('');
        $('#form-colleague-filter')[0].submit();
    });
    $(document).on('click', '.exec-filter-report-search', function() {
        $('#form-colleague-filter')[0].submit();
    });

    $(document).on('dblclick', '.available-folder', function(e) {
        e.preventDefault();
        e.stopPropagation();
        //console_log($(this).attr('data-file-uuid'));
        AdminPanelAddNewFolderForColleague($(this).attr('data-file-uuid'));
    });

    $(document).on('click', '.manage-colleague-folder', function() {
        return managerColleagueFolder($(this));
    });

    $(document).on('click', '.delete-colleague-folder', function() {

        if (checkNodesOnline(false)) {
            var data_email =($(this).attr('data-email'));

            prettyConfirm(function () {

                //alert("/admin-panel/colleague-delete?colleague_email=" + data_email);
                window.location.href = "/admin-panel/colleague-delete?colleague_email=" + encodeURIComponent(data_email);
                return true;

            }, null, $(this).attr('data-confirm-text'), $(this).attr('data-confirm-yes'), $(this).attr('data-confirm-no'));
        }
        return false;

    });

    $(document).on('click', '.select-available-folder', function(e) {
        var $avail_selected = $('#available-folder-list').find('.ui-selected').first();
        if ($avail_selected.length) {
            //console_log($avail_selected.attr('data-file-uuid'));
            AdminPanelAddNewFolderForColleague($avail_selected.attr('data-file-uuid'));
        } else {
            snackbar('Please, select the folder.', 'error', 3000, null, 'AdminPanelAddNewFolderForColleague');
        }
    });

    $(document).on('click', '.created-file, .deleted-file, .restored-file, .updated-file, .moved-file, .renamed-file, .moved-renamed-file', function() {
        goToFileFromReport($(this).attr('data-file-parent-id'));
    });

    if ($('#reportTab').hasClass('active')) {
        setReportsAsRead($('#reportTab'));
    }

    if (!checkNodesOnline(onCommandsNotShowBalloonNodes)) {
        $(document).find('.manage-colleague-folder, .delete-colleague-folder').each(function() {
            $(this).attr('href', '#');
        })
        $('.admin-panel-select-folder').addClass('notActive');
        //$('.dropdown-toggle').addClass('disabled');
    }

    $(document).find('.format-date-js').each(function() {
        $(this).html(formDate.exec($(this).attr('data-ts')));
    });

    getAvailableFolderList();
    if ($('#manager-list-folder').length) {
        selectFolder($('#manager-list-folder').find('.manager-list__row').first());
    }

    //$('#available-folder-list').niceScroll({cursorcolor:"#CDCDCD", cursorwidth:"8px"});
});

function resizeFileNameAdmin()
{
    var w_td = $('#manager-list-folder').find('.manager-list__col').first().width() - 100;
    //alert(w_td);
    $('.admin-panel-file-catalogFull').css({ 'width': w_td + 'px' });
}

/**
 *
 * @param obj
 * @returns {boolean}
 */
function managerColleagueFolder(obj)
{
    //if (checkNodesOnline(false)) {
        window.location.href = "/admin-panel/colleague-manage?colleague_email=" + encodeURIComponent(obj.attr('data-email'));
        return true;
    //} else {
    //    return false;
    //}
}

/**
 *
 */
function getAvailableFolderList()
{
    if ($('#colleague-email').length) {
        $.ajax({
            type: 'get',
            url: _LANG_URL + '/admin-panel/available-folders?colleague_email=' + encodeURIComponent($('#colleague-email').text()),
            dataType: 'json',
            statusCode: {
                200: function(response) {
                    if (response.status == true) {
                        var tpl = $('#available-row-tpl').html();
                        var avail = response.data;

                        var avail_list = "";
                        for (var i = 0; i < avail.length; i++) {
                            if (avail[i]['is_collaborated'] == 1) {
                                avail[i]['full'] = 'Full';
                            } else {
                                avail[i]['full'] = '';
                            }
                            avail_list += tpl.replace(/\{([a-z\_]+)\}/g, function (s, e) {
                                return avail[i][e];
                            });
                        }
                        $('#available-folder-list').html(avail_list);

                    } else {
                        snackbar(response.info, 'error', 3000, null, 'admin-panel.available-folders');
                    }
                },
                500: function(response) {
                    console_log(response);
                    alert('An internal server error occurred.');
                }
            }
        });
    }
}

/**
 *
 * @param row
 */
function selectFolder(row)
{
    //console_log(row);
    if (!row.hasClass('row-empty')) {
        $('#manager-list-folder').find('.manager-list__row').each(function() {
            $(this).removeClass('ui-selected');
        });
        $('#available-folder-list').find('.manager-list__row').each(function() {
            $(this).removeClass('ui-selected');
        });
        row.addClass('ui-selected');
        $('#user-status-folder').html(row.attr('data-colleague-status'));
        //$('#user-status-date').html(row.attr('data-colleague-date'));
        $('#user-status-date').html(formDate.exec(row.attr('data-colleague-ts')));
        $('#user-status-date').attr('data-ts', row.attr('data-colleague-ts'));
    }
}

/**
 *
 * @param file_parent_id integer
 */
function goToFileFromReport(file_parent_id)
{
    $.ajax({
        type: 'get',
        url: _LANG_URL + '/elfind?cmd=getLink&target=' + file_parent_id,
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true) {
                    console_log(response.url);
                    window.location.href = response.url;
                    //$(location).attr('href', response.url);
                }
            },
            500: function(response) {
                console_log(response);
                alert('An internal server error occurred.');
            }
        }
    });
    return false;
}

/**
 * @param data object
 */
function AdminPanelChangeUserCollaborateAccess(data)
{
    if (!checkNodesOnline(false)) {
        return false;
    }
    if (data.access_type && data.file_uuid && (data.colleague_email || data.colleague_id)) {
        $.ajax({
            type: 'post',
            url: _LANG_URL + '/admin-panel/colleague-change',
            data: {
                action            : data.action,
                colleague_email   : data.colleague_email,
                colleague_message : data.colleague_message,
                file_uuid         : data.file_uuid,
                access_type       : data.access_type,
                access_type_name  : data.access_type_name,
                colleague_id      : data.colleague_id,
            },
            dataType: 'json',
            statusCode: {
                200: function(response) {
                    if (response.status == true) {
                        console_log(response);
                        if (response.action == 'add') {

                            var not_empty_row_tpl = $('#manager-list-not-empty-row').html();
                            var not_empty_row_new = not_empty_row_tpl.replace(/\{([a-z\_]+)\}/g, function(s, e) {
                                //console_log(s);
                                //console_log(e);
                                //console_log(colleagues[i][e]);
                                return response.data[e];
                            });

                            $('#manager-list-folder')
                                .prepend(not_empty_row_new)
                                .find('.row-empty:first').remove();

                            selectFolder($('#row-colleague-id-' + response.data.colleague_id));

                            $('#folder-select-modal').modal("hide");
                            //console_log(colleague_new);
                            flash_msg('success-added-to-collaborate-folder', 'success', 3000, false, null, 'admin-panel.colleague-change.add-to-folder');

                            $('#alert-block-container').find('.hide-on-add-folder').each(function() {
                                $(this).remove();
                            });

                        } else if (response.action == 'delete') {

                            $('#row-colleague-id-' + response.data.colleague_id).remove();
                            var empty_row = $('#manager-list-empty-row').html();
                            var cur_l = $('#manager-list-folder').find('.manager-list__row').length;
                            var need_l = $('#manager-list-folder').attr('data-min-count-row');
                            if (cur_l < need_l) {
                                $('#manager-list-folder').append(empty_row);
                            }
                            flash_msg('success-deleted-from-collaborate-folder', 'success', 3000, false, null, 'admin-panel.colleague-change.del-from-folder');

                        } else {

                            $('#access-colleague-id-' + response.data.colleague_id)
                                .attr('data-access-type', response.data.access_type)
                                .html(response.data.access_type_name);

                            flash_msg('success-changed-for-collaborate-folder', 'success', 3000, false, null, 'admin-panel.colleague-change.changed-access-for-folder');
                        }

                        if ((typeof ws != "undefined") && ("event_data" in response) && (response.event_data)) {
                            for (var ii=0; ii<response.event_data.length; ii++) {
                                //console_log(JSON.stringify(response.event_data[ii]));
                                //ws.send(JSON.stringify(response.event_data[ii]));
                            }
                        }

                        //console_log(response.info);

                    } else {
                        $('#folder-select-modal').modal("hide");
                        flash_msg(response.info, 'error', 3000, false, null, 'admin-panel.colleague-change');
                        //error
                        //console_log(response.info);
                    }

                    getAvailableFolderList();
                },
                500: function(response) {
                    console_log(response);
                    alert('An internal server error occurred.');
                }
            }
        });
    }
}

/**
 *
 * @param file_uuid
 * @constructor
 */
function AdminPanelAddNewFolderForColleague(file_uuid)
{
    //console_log(file_uuid);

    var data = {
        action            : "add",
        colleague_email   : $.trim($('#colleague-email').text()),
        colleague_message : "",
        access_type       : "view",
        access_type_name  : "View",
        colleague_id      : "",
        file_uuid         : file_uuid,
    }

    //console_log(data);
    AdminPanelChangeUserCollaborateAccess(data);
}

/**
 *
 * @param obj
 */
function setReportsAsRead(obj)
{
    //console_log(obj.attr('data-tab'));
    console_log($('#menu-count-new-events').find('b:first').length);
    //if ($('#menu-count-new-events').find('b:first').length) {
        $.ajax({
            type: 'get',
            url: _LANG_URL + '/user/set-reports-as-read',
            dataType: 'json',
            statusCode: {
                200: function(response) {

                    var count = response.count;

                },
                500: function(response) {
                    console_log(response);
                    alert('An internal server error occurred.');
                }
            }
        });
    //}
}

/**
 *
 * @param node_id
 */
function releaseServerLicense(obj)
{
    var node_id = parseInt(obj.attr('data-node-id'));
    $.ajax({
        type: 'get',
        url: _LANG_URL + '/admin-panel/release-server-license?node_id=' + node_id,
        dataType: 'json',
        statusCode: {
            200: function(response) {

                //console_log(response);
                //var count = response.count;
                //setTimeout(function() { obj.remove(); }, 2000);
                //obj.remove();
                if ("data" in response) {
                    $('#used-server-license-count').html(response.data.used);
                    $('#total-server-license-count').html(response.data.total);
                    $('#row-node-id-' + node_id).remove();
                }
                $.pjax.reload({container: '#server-license-list-content', async: true});
            },
            400: function() {
                alert('An internal server error occurred.');
            },
            500: function(response) {
                console_log(response);
                alert('An internal server error occurred.');
            }
        }
    });
}
