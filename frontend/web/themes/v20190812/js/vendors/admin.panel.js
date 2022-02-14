var onCommandsNotShowBalloonNodes = true;

$(document).ready(function() {
    //$('#trigger-folder-select-modal').trigger('click');+

    $(document).on('click', '.release-server-license', function() {
        //$(this).remove();

        var obj = $(this);
        var msg = $(this).attr('data-confirm-text');

        prettyConfirm(function () {

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
        };

        //console_log(data);
        AdminPanelChangeUserCollaborateAccess(data);

        return false;
    });

    $(document).on('click', '.admin-panel-select-folder', function () {
        if (checkNodesOnline(false)) {
            $('#trigger-folder-select-modal').trigger('click');

            setTimeout(function () {
                createNiceScroll('#available-folder-list', false, true);
                reInitNiceScroll('#available-folder-list');
            }, 400);
            return true;
        } else {
            return false;
        }
    });

    $(document).on('click', '.manager-list__row', function() {
        selectFolder($(this));
    });

    $(document).on('change', '.form-reports-filter', function() {
        $('#form-reports-filter')[0].submit();
        return true;
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
                return false;

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
        return false;
    });

    $(document).on('click', '.create-new-folder-for-collaboration', function() {
        var $row_create = $('#available-folder-list').find('.create-new-folder').first();
        $row_create.show();
        var $inp = $row_create.find('input').first();

        $inp[0].focus();

        $inp.prop("onfocusout", null).off("focusout").unbind('focusout');
        $inp.on('focusout', function() {
            //$inp.val('');
            //$row_create.hide();
            var val = $.trim($inp.val());
            if (val == '') {
                $inp.val('');
                $row_create.hide();
            } else {
                //console_log( $inp.val() );
                $.ajax({
                    type: 'get',
                    url: '/elfind?cmd=mkdir&name=' + val + '&target=' + 'l1_' + '&showdeleted=0',
                    dataType: 'json',
                    statusCode: {
                        200: function(response) {
                            //console_log(response);
                            if (response) {
                                if ("added" in response) {
                                    //alert(typeof response.added[0]);
                                    if (typeof response.added[0] == 'object') {
                                        if ("file_uuid" in response.added[0]) {
                                            AdminPanelAddNewFolderForColleague(response.added[0].file_uuid);
                                        } else {
                                            getAvailableFolderList();
                                        }
                                    } else {
                                        getAvailableFolderList();
                                    }
                                } else if ("error" in response) {
                                    if (response.error.in_array("errExists")) {
                                        //$inp[0].focus();
                                        $inp.val('');
                                        $row_create.hide();
                                        var scroll_to = encodeName(val);
                                        var $el_scroll_to = $('#enc-' + scroll_to);
                                        if ($el_scroll_to.length) {
                                            //console_log($el_scroll_to);
                                            selectFolder($el_scroll_to);
                                            var h = $el_scroll_to.outerHeight() * $el_scroll_to.data('num-pp') - 70;
                                            //alert(h);
                                            $('#available-folder-list').getNiceScroll().doScrollPos(0, h);
                                            snackbar('Folder already exist', 'error', 3000);
                                        } else {
                                            snackbar('Folder already added to collaboration for this user', 'error', 3000);
                                        }
                                    } else {
                                        $inp.val('');
                                        $row_create.hide();
                                        console_log(response.error);
                                        snackbar('Some error on create folder', 'error', 3000);
                                    }
                                } else {
                                    alert('Wrong response.');
                                }
                            } else {
                                alert('An internal server error occurred.');
                            }
                        },
                        500: function(response) {
                            console_log(response);
                            alert('An internal server error occurred.');
                        }
                    }
                });
            }
        });

        $inp.keyup(function(event) {
            if ( event.which == 27 ) {
                $inp.val('');
                $row_create.hide();
            }
            if (event.which == 13) {
                $inp[0].blur();
                //createNewFolderInSelect($inp, $row_create)
            }
        }).keydown(function( event ) {
            if ( event.which == 27 ) {
                $inp.val('');
                $row_create.hide();
            }
        });

    });

    if ($('#reportTab').hasClass('active')) {

        /**/
        var $reports_container = $('#events-list-content');
        if ($reports_container) {
            current_count_unread_reports = $reports_container.data('current-count-unread-reports');
        }

        setReportsAsRead();
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
        createNiceScroll('#manager-list-scroll', false, true);
    }
});


/**
 *
 * @param obj
 * @returns {boolean}
 */
function managerColleagueFolder(obj)
{
    window.location.href = "/admin-panel/colleague-manage?colleague_email=" + encodeURIComponent(obj.attr('data-email'));
    return false;
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
                        var tpl_create = $('#create-new-folder-row-tpl').html();

                        var avail_list = "";
                        for (var i = 0; i < avail.length; i++) {
                            avail[i]['num_pp'] = i + 1;
                            avail[i]['enc_file_name'] = encodeName(avail[i]['file_name']);
                            if (avail[i]['is_collaborated'] == 1) {
                                avail[i]['full'] = 'Full';
                            } else {
                                avail[i]['full'] = '';
                            }
                            avail_list += tpl.replace(/\{([a-z\_]+)\}/g, function (s, e) {
                                return avail[i][e];
                            });
                        }

                        tpl_create = tpl_create.replace('{input_create_new_folder}', 'input-create-new-folder');

                        $('#available-folder-list').html(tpl_create + avail_list);

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
    if (row.length && !row.hasClass('row-empty')) {
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

    if (file_parent_id <= 0) {
        window.location.href = '/user/files';
        return false;
    }

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

                        //console_log(response);
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

                            $.fancybox.close();
                            //console_log(colleague_new);
                            flash_msg('success-added-to-collaborate-folder', 'success', 3000, false, null, 'admin-panel.colleague-change.add-to-folder');

                            $('#alert-block-container').find('.hide-on-add-folder').each(function() {
                                $(this).remove();
                            });


                            setTimeout(function () {
                                reInitNiceScroll('#manager-list-scroll');
                                //initDropDown();
                            }, 400);

                        } else if (response.action == 'delete') {

                            $('#row-colleague-id-' + response.data.colleague_id).remove();
                            var empty_row = $('#manager-list-empty-row').html();
                            var cur_l = $('#manager-list-folder').find('.manager-list__row').length;
                            var need_l = $('#manager-list-folder').attr('data-min-count-row');
                            if (cur_l < need_l) {
                                $('#manager-list-folder').append(empty_row);
                            }
                            flash_msg('success-deleted-from-collaborate-folder', 'success', 3000, false, null, 'admin-panel.colleague-change.del-from-folder');

                            setTimeout(function () {
                                reInitNiceScroll('#manager-list-scroll');
                            }, 400);

                        } else {

                            $('#access-colleague-id-' + response.data.colleague_id)
                                .attr('data-access-type', response.data.access_type)
                                .html(response.data.access_type_name);

                            $('#dropdown-trigger-' + response.data.colleague_id).removeClass('open');
                            flash_msg('success-changed-for-collaborate-folder', 'success', 3000, false, null, 'admin-panel.colleague-change.changed-access-for-folder');
                        }

                        //console_log(response.info);

                    } else {
                        $.fancybox.close();
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
 * @param file_uuid
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
 */
function setReportsAsRead()
{
    if ($('.reports-tbl').length) {

        setTimeout(function () {
            //console_log(obj.attr('data-tab'));
            //console_log($('#menu-count-new-events').find('b:first').length);
            //if ($('#menu-count-new-events').find('b:first').length) {

            var ids = [];
            var i = 0;
            $('.reports-tbl tbody').find('tr.report-row').each(function () {
                var id = $(this).data('report-id');
                if (id) {
                    ids[i] = id;
                    i++;
                }
            });
            //console_log(ids);

            $.ajax({
                type: 'post',
                url: _LANG_URL + '/user/set-reports-as-read',
                dataType: 'json',
                data: {
                    ids: ids
                },
                statusCode: {
                    200: function (response) {
                        var count_read = response.count_read;
                        var count_unread = response.count_unread;
                        current_count_unread_reports = count_unread;
                        $('.reports-tbl tbody').find('tr.report-row').each(function () {
                            $(this).removeClass('isnew');
                        });
                        if (count_unread > 0) {
                            $('.count-new-reports').html('<b>' + count_unread + '</b>');
                        } else {
                            $('.count-new-reports').html('');
                        }
                    },
                    500: function (response) {
                        console_log(response);
                        alert('An internal server error occurred.');
                    }
                }
            });

            //}
        }, setReportsAsReadTimeout);

    }
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
