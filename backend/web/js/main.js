$.urlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results==null){
        return null;
    }
    else{
        return decodeURI(results[1]) || 0;
    }
}

/**
 *
 */
function initToolTipImg()
{
    $('.masterTooltipImg').css({cursor: 'pointer'});
    var w_im;
    $('.masterTooltipImg').hover(function () {
        //console_log($(this).attr('title'));
        // Hover over code

        var $pp = $('<p class="tooltip2"><img src="' + $(this)[0].src + '" /></p>');
        w_im = $pp.children().first()[0].width;

        $pp.appendTo('body').fadeIn('slow');

    }, function () {
        // Hover out code
        //$(this).attr('title', $(this).data('tipText'));
        $('.tooltip2').remove();
    }).mousemove(function (e) {
        var mousex = e.pageX - parseInt(w_im/2); //Get X coordinates
        var mousey = e.pageY + 5; //Get Y coordinates
        $('.tooltip2')
            .css({top: mousey, left: mousex});
    });
}

function initToolTip()
{
    $('.masterTooltip').css({cursor: 'pointer'});
        $('.masterTooltip').hover(function () {
        //console_log($(this).attr('title'));
        // Hover over code
        if ($(this)[0].hasAttribute('title')) {
            var title = $(this).attr('title');
            //obj = $(this);
            $(this).data('tipText', title).removeAttr('title');
            if (title.length) {
                $('<p class="tooltip2"></p>')
                    .text(title)
                    .appendTo('body')
                    .fadeIn('slow');
            }
        }
    }, function () {
        // Hover out code
        $(this).attr('title', $(this).data('tipText'));
        $('.tooltip2').remove();
    }).mousemove(function (e) {
        var mousex = e.pageX + 5; //Get X coordinates
        var mousey = e.pageY + 5; //Get Y coordinates
        $('.tooltip2')
            .css({top: mousey, left: mousex});
    });
}


/**
 *
 */
function checkIsTestReadyToStart()
{
    var $bt = $('#exec-test-manually');

    if ($bt.hasClass('not-Active')) {
        $.ajax({
            type: 'get',
            url: '/site/check-is-test-ready-to-start',
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

                            $.pjax.reload({container: '#info-about-tests', async: false});

                        }

                    } else {
                        //error
                        console.log(response.info);
                        alert('Error: ' + response.info);
                    }
                }
            }
        });
    }
    setTimeout(function() { checkIsTestReadyToStart(); }, 30000);
}

function checkCahngeLicenseField(field)
{
    if (field.val() == 'FREE_TRIAL') {
        $('#users-license_expire').val('');
        $('#license-expire-field').hide();
        $('#license-expired-info').show();
    } else {
        $('#license-expire-field').show();
        $('#license-expired-info').hide();
    }

    if (field.val() == 'PAYED_BUSINESS_ADMIN') {
        $('#enable-admin-panel-div').show();
    } else {
        $('#enable-admin-panel-div').hide();
    }

    if (field.val() == 'PAYED_BUSINESS_USER') {
        $('#id_license_business_from').show();
        $('#id_previous_license_business_from').show();
    } else {
        $('#id_license_business_from').hide();
        $('#id_previous_license_business_from').hide();
    }
}

/**
 * @param {string} type
 */
function showExampleOfMaintenanceMessage(type)
{
    var $example_maintenance = $('#example-maintenance');
    if ($example_maintenance.length) {
        $example_maintenance
            .removeClass()
            .addClass('alert-' + type)
            .html( $('#maintenance-text').val().replace(/\n/g,"<br />") )
            .show();
    }
}

/**
 * *****
 * *****
 * *****
 */
$(document).ready(function() {

    initToolTip();
    initToolTipImg();
    checkIsTestReadyToStart();
    if ($('#user-change-form-license-type').length) {
        checkCahngeLicenseField($('#user-change-form-license-type'));
    }
    if ($('#maintenance-type').length) {
        showExampleOfMaintenanceMessage($('#maintenance-type').val());
        $(document).on('change', '#maintenance-text', function() {
            showExampleOfMaintenanceMessage($('#maintenance-type').val());
        });
        $(document).on('input', '#maintenance-text', function() {
            showExampleOfMaintenanceMessage($('#maintenance-type').val());
        });
    }

    /** */
    $(document).on('click', '.empty-link, .void-0', function () {
        return false;
    });

    $(document).on('click', '.view-php-log', function () {
        $('#dashboard-modal-list').html('Loading...');
        $('#dashboard-modal').modal({"show":true});
        $.ajax({
            type: 'get',
            url: '/site/view-php-log?target=' + $(this).data('target'),
            dataType: 'text',
            statusCode: {
                404: function (response) {
                    $('#dashboard-modal-list').html('Error: 404 Not Found.');
                },
                500: function (response) {
                    $('#dashboard-modal-list').html(JSON.stringify(response));
                },
                200: function (response) {
                    //alert(response);
                    $('#dashboard-modal-list').html(response);
                }
            }
        });
    });

    /** show-cron-task-log */
    $(document).on('click', '.show-cron-task-log', function() {
        $('#dashboard-modal-list').html('Loading...');
        $('#dashboard-modal').modal({"show":true});
        $.ajax({
            type: 'get',
            url: '/site/cron-info-task-log?task_id=' + $(this).attr('data-task-id'),
            dataType: 'json',
            statusCode: {
                404: function (response) {
                    $('#dashboard-modal-list').html('Error: 404 Not Found.');
                },
                200: function (response) {
                    if (response.status == true) {

                        $('#dashboard-modal-list').html(response.data);

                    } else {
                        //error
                        $('#dashboard-modal-list').html('Error: ' + response.info);
                    }
                }
            }
        });
        return false;
    });

    /** show-cron-task-schedule */
    $(document).on('click', '.show-cron-task-schedule', function() {
        alert($(this).attr('title'));
        return false;
    });

    /** show-alert-message-text */
    $(document).on('click', '.show-alert-message-text', function() {
        $('.caption-modal-list').hide();
        $('#content-modal-list').html('Loading...');
        $('#alert-message-text').show();
        $('#file-on-nodes-modal').modal({"show":true});
        $('#file-on-nodes-modal').find('.modal-dialog').first().css({ width: '40%' });
        $.ajax({
            type: 'get',
            url: '/users/show-alert-message-text?record_id=' + $(this).attr('data-record-id'),
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

    /** */
    $(document).on('change', '#create-business-account', function() {
        var $user_license_count = $('#user-license-count');
        var $registeruserbysellerform_license_count = $('#registeruserbysellerform-license_count');
        if ($(this).is(':checked')) {
            $user_license_count.show();
            $registeruserbysellerform_license_count.removeAttr('disabled');
        } else {
            $user_license_count.hide();
            $registeruserbysellerform_license_count.attr('disabled', 'disabled');
        }
    });

    /** */
    $(document).on('change', '#user-change-form-license-type', function() {
        checkCahngeLicenseField($(this));
    });

    $(document).on('change', '#maintenance-type', function() {
        showExampleOfMaintenanceMessage($(this).val());
    });

    /** #exec-test-manually */
    $(document).on('click', '#exec-test-manually', function() {

        var $bt = $(this);

        if (!$bt.hasClass('not-Active')) {

            $bt
                .prop('disabled', true)
                .addClass('not-Active')
                .val($bt.attr('data-value-in-progress'));

            $.ajax({
                type: 'get',
                url: '/site/exec-test-manually',
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

                                $.pjax.reload({container: '#info-about-tests', async: false});

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
        }
    });

    /** */
    $(document).on('click', '.show-all-with-promo', function() {
        var $el = $('input[name="UsersSearch[user_promo_code]"]');
        if (!$el.length) {
            var $el = $('input[name="SelfHostUsersSearch[shu_promo_code]"]');
        }

        if ($el.length) {
            $el.val('show_not_null');
            $el.change();
        }
        return false;
    });

});
