$(document).ready(function() {

    checkIsDopReadyToStart();

    //$('#userfileeventssearch-created_at_range').parent().append('x');
    /** show-node-with-file */
    $(document).on('click', '.glyphicon-nodes', function() {
        $('.caption-modal-list').hide();
        $('#content-modal-list').html('Loading...');
        $('#nodes-list-caption').show();
        $('#file-on-nodes-modal').modal({"show":true});
        $('#file-on-nodes-modal').find('.modal-dialog').first().css({ width: '95%' });
        //alert($(this).attr('data-file-id'));
        $.ajax({
            type: 'get',
            url: '/users/nodes-with-file?file_id=' + $(this).attr('data-file-id'),
            dataType: 'json',
            statusCode: {
                404: function (response) {
                    $('#content-modal-list').html('Error: 404 Not Found.');
                },
                200: function (response) {
                    if (response.status == true) {

                        $('#content-modal-list').html(response.data);

                    } else {
                        //error
                        $('#content-modal-list').html('Error: ' + response.info);
                    }
                }
            }
        });
        return false;
    });

    /** show-hide-deleted' */
    $(document).on('change', '.users-show-hide-deleted', function () {
        var append = '';
        if ($(this).is(':checked')) {
            append = '&UserFilesSearch%5Bshow_deleted%5D=1';
        }
        //alert(repl);
        var url = window.location.href
            .replace(/\&UserFilesSearch\[show\_deleted\]\=[\d]+/, '')
            .replace(/\&UserFilesSearch\%5Bshow_deleted\%5D\=[\d]+/, '')
            .replace(/&UserFilesSearch\[tab\]\=/, '')
            .replace(/&UserFilesSearch\%5Btab\%5D\=/, '')
            .replace('#file-info', '');

        //url += append + '#file-info';
        url += append + '&UserFilesSearch%5Btab%5D=';
        window.location.href = url;
    });

    /** show-events-for-file */
    $(document).on('click', '.glyphicon-events', function() {
        $('.caption-modal-list').hide();
        $('#content-modal-list').html('Loading...');
        $('#events-list-caption').show();
        $('#file-on-nodes-modal').modal({"show":true});
        $('#file-on-nodes-modal').find('.modal-dialog').first().css({ width: '95%' });
        //alert($(this).attr('data-file-id'));
        $.ajax({
            type: 'get',
            url: '/users/events-for-file?file_id=' + $(this).attr('data-file-id'),
            dataType: 'json',
            statusCode: {
                404: function (response) {
                    $('#content-modal-list').html('Error: 404 Not Found.');
                },
                200: function (response) {
                    if (response.status == true) {

                        $('#content-modal-list').html(response.data);

                    } else {
                        //error
                        $('#content-modal-list').html('Error: ' + response.info);
                    }
                }
            }
        });
        return false;
    });

    /** show-node-name */
    $(document).on('click', '.show-node-name', function() {
        $('.caption-modal-list').hide();
        $('#content-modal-list').html('Loading...');
        $('#node-name-info').show();
        $('#file-on-nodes-modal').modal({"show":true});
        $('#file-on-nodes-modal').find('.modal-dialog').first().css({ width: '30%' });
        $('#content-modal-list').html($(this).attr('data-node-name'));
    });

    /** show-full-path */
    $(document).on('click', '.show-full-path', function() {
        $('.caption-modal-list').hide();
        $('#content-modal-list').html('Loading...');
        $('#file-path-info').show();
        $('#file-on-nodes-modal').modal({"show":true});
        $('#file-on-nodes-modal').find('.modal-dialog').first().css({ width: '30%' });
        $.ajax({
            type: 'get',
            url: '/users/show-full-path?file_id=' + $(this).attr('data-file-id'),
            dataType: 'json',
            statusCode: {
                404: function (response) {
                    $('#content-modal-list').html('Error: 404 Not Found.');
                },
                200: function (response) {
                    if (response.status == true) {

                        $('#content-modal-list').html(response.data);

                    } else {
                        //error
                        $('#content-modal-list').html('Error: ' + response.info);
                    }
                }
            }
        });
        return false;
    });

    /** show-only-path */
    $(document).on('click', '.show-only-path', function() {
        $('.caption-modal-list').hide();
        $('#content-modal-list').html('Loading...');
        $('#file-path-info').show();
        $('#file-on-nodes-modal').modal({"show":true});
        $('#file-on-nodes-modal').find('.modal-dialog').first().css({ width: '30%' });
        var $target_link_object = $(this);
        $.ajax({
            type: 'get',
            url: '/users/show-only-path?file_id=' + $(this).attr('data-file-id'),
            dataType: 'json',
            statusCode: {
                404: function (response) {
                    $('#content-modal-list').html('Error: 404 Not Found.');
                },
                200: function (response) {
                    if (response.status == true) {

                        $('#content-modal-list').html(response.data + $target_link_object.attr('data-file-name'));

                    } else {
                        //error
                        $('#content-modal-list').html('Error: ' + response.info);
                    }
                }
            }
        });
        return false;
    });

    /** show-colleagues */
    $(document).on('click', '.show-colleagues', function() {
        $('.caption-modal-list').hide();
        $('#content-modal-list').html('Loading...');
        $('#colleagues-list-caption').show();
        $('#file-on-nodes-modal').modal({"show":true});
        $('#file-on-nodes-modal').find('.modal-dialog').first().css({ width: '95%' });
        //alert($(this).attr('data-file-id'));
        $.ajax({
            type: 'get',
            url: '/users/colleagues-for-collaboration?collaboration_id=' + $(this).attr('data-collaboration-id'),
            dataType: 'json',
            statusCode: {
                404: function (response) {
                    $('#content-modal-list').html('Error: 404 Not Found.');
                },
                200: function (response) {
                    if (response.status == true) {

                        $('#content-modal-list').html(response.data);

                    } else {
                        //error
                        $('#content-modal-list').html('Error: ' + response.info);
                    }
                }
            }
        });
        return false;
    });


    /** show-files-for-traffic-info */
    $(document).on('click', '.show-files-for-traffic-info', function() {
        $('.caption-modal-list').hide();
        $('#content-modal-list').html('Loading...');
        $('#files-for-traffic-info').show();
        $('#file-on-nodes-modal').modal({"show":true});
        $('#file-on-nodes-modal').find('.modal-dialog').first().css({ width: '75%' });
        //alert($(this).attr('data-file-id'));
        $.ajax({
            type: 'get',
            url: '/users/show-files-for-traffic-info?user_id=' + $(this).attr('data-user-id') + '&type=' + $(this).attr('data-type') + '&date=' + $(this).attr('data-date'),
            dataType: 'json',
            statusCode: {
                404: function (response) {
                    $('#content-modal-list').html('Error: 404 Not Found.');
                },
                200: function (response) {
                    if (response.status == true) {

                        $('#content-modal-list').html(response.data);

                    } else {
                        //error
                        $('#content-modal-list').html('Error: ' + response.info);
                    }
                }
            }
        });
        return false;
    });

    /** lock-unlock-share-link */
    $(document).on('click', '.lock-unlock-share-link', function() {
        //alert($(this).attr('data-file-id'));
        var link = $(this);
        $.ajax({
            type: 'get',
            url: '/users/lock-unlock-share-link?file_id=' + $(this).attr('dta-file-id') + '&share_is_locked=' + $(this).attr('data-share-is-locked'),
            dataType: 'json',
            statusCode: {
                404: function (response) {
                    console.log('Error: 404 Not Found.');
                },
                200: function (response) {
                    if (response.status == true) {

                        if (link.attr('data-share-is-locked') == 1) {
                            link
                                .attr('data-share-is-locked', 0)
                                .html(link.attr('data-name-unlock'));
                        } else {
                            link
                                .attr('data-share-is-locked', 1)
                                .html(link.attr('data-name-lock'));
                        }

                    } else {
                        //error
                        console.log(response.info);
                        alert('Error: ' + response.info);
                    }
                }
            }
        });
        return false;
    });

    /** #get-log-dop-for-user */
    $(document).on('click', '#get-log-dop-for-user', function() {

        getLogDopForUser($(this).attr('data-user-id'));

        return false;

    });

    /** start-dop-for-user */
    $(document).on('click', '#start-dop-for-user', function() {
        //alert($(this).attr('data-file-id'));
        var $bt = $(this);

        $bt
            .prop('disabled', true)
            .addClass('not-Active')
            .val($bt.attr('data-value-in-progress'));

        $.ajax({
            type: 'get',
            url: '/users/start-dop-for-user?id=' + $bt.attr('data-user-id') + '&restorePatchTTL=' + parseInt($('#restore-patch-ttl').val()),
            dataType: 'json',
            statusCode: {
                404: function (response) {
                    console.log('Error: 404 Not Found.');
                },
                200: function (response) {
                    if ("status" in response && "result" in response && response.status == true) {

                        if (response.result == 'ok') {

                            $bt
                                .prop('disabled', false)
                                .removeProp('disabled')
                                .removeClass('not-Active')
                                .val($bt.attr('data-value-ready-to-start'));

                            getLogDopForUser($bt.attr('data-user-id'));

                        } else {
                            $bt
                                .prop('disabled', 'disabled')
                                .addClass('not-Active')
                                .val($bt.attr('data-value-in-progress'));

                        }

                    } else {
                        //error
                        console.log(response.info);
                        alert('Error: ' + response.info);
                    }
                }
            }
        });
        return false;
    });
});

/**
 * @param user_id integer
 */
function getLogDopForUser(user_id)
{
    $('.caption-modal-list').hide();
    $('#content-modal-list').html('Loading...');
    $('#log-dop-for-user').show();
    $('#file-on-nodes-modal').modal({"show":true});
    $('#file-on-nodes-modal').find('.modal-dialog').first().css({ width: '75%' });

    $.ajax({
        type: 'get',
        url: '/users/get-log-dop-for-user?id=' + user_id,
        dataType: 'json',
        statusCode: {
            404: function (response) {
                console.log('Error: 404 Not Found.');
            },
            200: function (response) {
                if ("status" in response && response.status == true) {

                    $('#content-modal-list').html(response.data);

                } else {
                    //error
                    console.log(response.info);
                    alert('Error: ' + response.info);
                }
            }
        }
    });
}

/**
 *
 */
function checkIsDopReadyToStart()
{
    var $bt = $('#start-dop-for-user');
    if ($bt.hasClass('not-Active')) {
        $.ajax({
            type: 'get',
            url: '/users/check-is-dop-ready-to-start?id=' + $bt.attr('data-user-id'),
            dataType: 'json',
            statusCode: {
                404: function (response) {
                    console.log('Error: 404 Not Found.');
                },
                200: function (response) {
                    if ("status" in response) {

                        if (response.status == true) {

                            $bt
                                .prop('disabled', false)
                                .removeProp('disabled')
                                .removeClass('not-Active')
                                .val($bt.attr('data-value-ready-to-start'));

                            getLogDopForUser($bt.attr('data-user-id'));

                        }/* else {
                            $bt
                                .prop('disabled', 'disabled')
                                .addClass('not-Active')
                                .val($bt.attr('data-value-in-progress'));
                        }*/

                    } else {
                        //error
                        console.log(response.info);
                        alert('Error: ' + response.info);
                    }
                }
            }
        });
    }
    setTimeout(function() { checkIsDopReadyToStart(); }, 30000);
}