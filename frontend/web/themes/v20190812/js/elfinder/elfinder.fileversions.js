/** ******************************************* FILEVERSIONS *********************************************** */
/**
 * showFileVersionsDialog
 * @param {string} hash
 */
function showFileVersionsDialog(hash)
{
    /** ++ Init FileVersions popup */
    $('#trigger-fileversions-modal').trigger( "click" );

    $('#fileversions-filesystem-hash').val(hash);
    $('#fileversions-file-uuid').val('');
    var fname = $('#' + hash).find('.elfinder-cwd-filename').first();
    if (fname.length) {
        $('#fileversions-file-name').html(fname.text()).attr('title', fname.text());
    } else {
        $('#fileversions-file-name').html('').attr('title', '');
    }

    $('#fileversions-list').html('');
    /** -- Init collaborate popup */

    $.ajax({
        type: 'get',
        url: _LANG_URL + '/elfind?cmd=fileversionsDialog&target=' + hash,
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true && typeof response.data != 'undefined') {

                    var data = response.data;
                    /** @var object data */
                    $('#fileversions-filesystem-hash').val(data.hash);
                    $('#fileversions-file-id').val(data.file_id);
                    $('#fileversions-file-uuid').val(data.file_uuid);
                    $('#fileversions-file-name').html(data.file_name).attr('title', data.file_name);

                    var version_tpl = $('#version_tpl').html();

                    var fileversions_list = "";
                    /** @var object data.fileversions */
                    if (typeof data.fileversions != 'undefined' && data.fileversions.length) {
                        for (var i = 0; i < data.fileversions.length; i++) {
                            //console_log(response.colleagues[i]);
                            if (data.fileversions[i].status == 1) {
                                data.fileversions[i]['disabled'] = "";
                            } else {
                                data.fileversions[i]['disabled'] = "disabled";
                                //data.fileversions[i]['disabled'] = ""; data.fileversions[i].status = 1;
                            }
                            data.fileversions[i]['date_restored'] ='';
                            data.fileversions[i]['event_timestamp']= formDate.exec(data.fileversions[i]['timestamp']);
                            if (data.fileversions[i].status == -1) {
                                data.fileversions[i]['show_restore']  = "none";
                                data.fileversions[i]['show_restored'] = "none";
                                data.fileversions[i]['show_current']  = "block";
                            } else if (data.fileversions[i].status == -2) {
                                data.fileversions[i]['show_restore']  = "none";
                                data.fileversions[i]['show_restored'] = "block";
                                data.fileversions[i]['show_current']  = "none";

                                data.fileversions[i]['date_restored'] = data.fileversions[0]['event_timestamp'];
                                data['date_restored'] = formDate.exec(data['event_timestamp']);
                            } else {
                                data.fileversions[i]['show_restore'] = "block";
                                data.fileversions[i]['show_restored'] = "none";
                                data.fileversions[i]['show_current'] = "none";
                            }
                            data.fileversions[i]['file_size_after_event'] = file_size_format(data.fileversions[i]['file_size_after_event'], 2);
                            //console_log(data.fileversions[i]);
                            fileversions_list += version_tpl.replace(/\{([a-z\_]+)\}/g, function (s, e) {
                                //console_log(s);
                                //console_log(e);
                                //console_log(response.colleagues[i][e]);
                                return data.fileversions[i][e];
                            });
                        }
                        $('#fileversions-list').html(fileversions_list);

                        createNiceScroll('.scrollbar-program-vertical', false, true);
                        createNiceScroll('.scrollbar-program-horizontal', true, false);
                        setTimeout(function() { reInitNiceScroll(null); }, 100);
                        setTimeout(function() { reInitNiceScroll(null); }, 200);

                    } else {
                        var f = elfinderInstance.file(data.hash);
                        f.file_updated = false;
                        elfinderInstance.error("File " + data.file_name + " hasn't available versions.");
                        $.fancybox.close();
                    }
                } else {
                    elfinderInstance.error('System error.');
                    $.fancybox.close();
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
 * restorePatchForFile
 * @param {int} event_id
 * @param {int} status
 * @returns {boolean}
 */
function restorePatchForFile(event_id, status)
{
    if (status != 1) { return false;}

    $.ajax({
        type: 'post',
        url: _LANG_URL + '/user/restore-patch',
        data: {
            event_id : event_id,
        },
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true) {

                    elfinderInstance.exec('reload');
                    showFileVersionsDialog($('#fileversions-filesystem-hash').val());
                    //snackbar('success-restored-patch', 'success', 3000, null, 'restorePatchForFile');
                    flash_msg('success-restored-patch', 'success', 3000, false, null, 'restorePatchForFile');
                    $.fancybox.close();
                } else {
                    flash_msg(response.info, 'error', 3000, false, null, 'restorePatchForFile');
                    $.fancybox.close();
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
 * restoreDeletedFile
 * @param {string} hash
 */
function restoreDeletedFile(hash)
{
    $.ajax({
        type: 'get',
        url: _LANG_URL + '/elfind?cmd=restoreDeletedFile&target=' + hash,
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true) {
                    elfinderInstance.exec('reload');
                } else {
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

/** ******************************************* DOCUMENT READY *********************************************** */
$(document).ready(function() {

    if (elfinderInstance) {

        $(document).on('click', '.restore-patch', function() {
            restorePatchForFile($(this).attr('data-event-id'), $(this).attr('data-restore-status'));
        });

    }

});