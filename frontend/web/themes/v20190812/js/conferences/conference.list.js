var $participant_add_button = $('#participant-add-button');
var $available_participants_list = $('#available-participants-list');
var $available_participants_loading = $('#progress-tpl');
var cnt_added = 0;
var $wss_data = $('#wss-data');
var cnt_license_available = 0;

/**
 *
 */
$(document).ready(function() {

    /**
     *
     */
    var clipboard = new Clipboard('.copy-button');
    clipboard.on('success', function(e) {
        snackbar('copied-ok', 'success', 5000, null, 'guest-link-copied-button');
    });

    /**
     *
     */
    cnt_license_available = $wss_data.data('license-available');

    /* разблокируем или заблокируем кнопку */
    lock_unlock_AddParticipantButton();

    /**
     *
     */
    if (window.location.href.indexOf('mark') > 0) {
        var conference_id = parseInt(getUrlVars()['mark']);
        if (conference_id > 0) {
            var $tr = $('#td-participants-' + conference_id).parent();
            scrollToAndBlink($tr);
        }
    }

    /**
     *
     */
    $(document).on('click', '.close-popup-participants', function () {
        //$('#conferenceaddform-conference_name').val('');
        var $form = $("#form-conference-create");
        $form[0].reset();
        $form.focus();
        $form.yiiActiveForm("resetForm");
    });

    /**
     *
     */
    $(document).on('click', '#voice-device, #video-device', function() {
        if ($(this).attr('data-on') == "1") {
            setKeyAsOff($(this));
            $.cookie($(this).data('cookie-name'), "0", { expires: 365, path: '/' });
        } else {
            setKeyAsOn($(this));
            $.cookie($(this).data('cookie-name'), "1", { expires: 365, path: '/' });
        }
        $('.tooltip2').remove();
    });

    /**
     *
     */
    if (parseInt($.cookie('cookie_video_status')) == 0) {
        $('#video-device').trigger('click');
        $.cookie('cookie_video_status', "0", { expires: 365, path: '/' });
    } else {
        $.cookie('cookie_video_status', "1", { expires: 365, path: '/' });
    }

    /**
     *
     */
    if (parseInt($.cookie('cookie_voice_status')) == 0) {
        $('#voice-device').trigger('click');
        $.cookie('cookie_voice_status', "0", { expires: 365, path: '/' });
    } else {
        $.cookie('cookie_voice_status', "1", { expires: 365, path: '/' });
    }

    /**
     *
     */
    $(document).on('click', '.manager-list__row', function() {
        selectParticipant($(this));
    });

    /**
     *
     */
    //$('#form-conference-create').on('afterValidate', function (event, messages, errorAttributes) {
    $(document).on("beforeSubmit", "#form-conference-create", function () {
        startImgProgressForm($(this));
        //openAvailableParticipantsList($('#conferenceaddform-conference_name').val(), 0);
        setParticipantsForConference(true);
        return false;
    });

    /**
     *
     */
    $('#form-conference-create').on('afterValidateAttribute', function(event, attr, msg) {
        var tr_md5 = MD5(attr.value);
        //console_log(msg);
        if (typeof attr.value == 'string') {
            var $tr = $('#conferences-list-content').find('.conf-name-md5-' + tr_md5).first();
            scrollToAndBlink($tr);
            if ($tr.length) {
                $('#conferenceaddform-conference_name').val('');
            }
        }
    });

    /**
     *
     */
    $(document).on('click', '.open-conference-participants', function () {
        openAvailableParticipantsList($(this).data('conference-name'), $(this).data('conference-id'));
        return false;
    });

    /**
     *
     */
    $(document).on("beforeSubmit", "#form-participant-add", function () {
        /* если уже был запущен сабмит и он не закончен, то выходим */
        if ($participant_add_button.hasClass('btn-notActive')) {
            return false;
        }

        /* добавляем участника */
        addParticipant();

        /* не сабмитим форму (она локальная) */
        return false; // Cancel form submitting.
    });

    /**
     *
     */
    $(document).on('beforeSubmit', '#guest-link-send-to-email-form', function () {
        var form = $(this);

        if (form.find('.has-error').length) {
            return false;
        }

        var $guest_email = $('#guest-email');

        $.ajax({
            type: 'post',
            url: _LANG_URL + '/conferences/guest-link-send-to-email',
            data: {
                conference_id         : $('#for-send-conference-id').val(),
                conference_guest_hash : $('#for-send-conference-guest-hash').val(),
                participant_email     : $guest_email.val()
            },
            dataType: 'json',
            statusCode: {
                200: function (response) {
                    if (response.status == true) {

                        snackbar('guest-link-sent-ok', 'success', 2000, {'email': $guest_email.val()}, 'guest-link-sent-to-email');
                        $guest_email.val('');

                    } else {
                        snackbar(response.info, 'error', 2000, null, 'guest-link-sent-to-email');
                    }
                },
                500: function (response) {
                    console_log(response);
                    alert('An internal server error occurred.');
                }
            }
        });

        return false;
    });

    /**
     *
     */
    $(document).on('click', '#set-participants-for-conference', function() {
        setParticipantsForConference(false);
    });

    /**
     *
     */
    $(document).on('click', '.cancel-conference', function() {
        var conference_id = $(this).data('conference-id');
        var msg = $(this).data('confirm-message');
        prettyConfirm(function () {

            cancelConference(conference_id);

        }, null, $.trim(msg), '', '');
        return false;
    });
});

/**
 * @param {integer} conference_id
 */
function cancelConference(conference_id)
{
    $.ajax({
        type: 'get',
        url: _LANG_URL + '/conferences/cancel-conference?conference_id=' + conference_id,
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true) {

                    $('#tr-conference-' + conference_id).remove();

                } else {

                    if ("info" in response) {
                        snackbar(response.info, 'error', 3000, false, null, 'conferences.participants');
                    }

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
 * @param object
 */
function scrollToAndBlink($object)
{
    if ($object.length) {
        var destination = $object.offset().top;
        $('html, body').animate({scrollTop: destination}, 1100);
        $object.blink({delay: 300, count: 3, className: "ui-selected"});
        $('#conferenceaddform-conference_name').val('');
    }
}

/**
 *
 */
function lock_unlock_AddParticipantButton()
{
    if (cnt_license_available > 0) {
        $participant_add_button.removeAttr('disabled').removeClass('btn-notActive');
    } else {
        $participant_add_button
            .addClass('masterTooltip')
            .removeAttr('disabled')
            .attr('title', $wss_data.data('error_no_more_license'));
        initToolTip();
    }
}

/**
 * @param {string} conference_name
 * @param {number} conference_id
 */
function openAvailableParticipantsList(conference_name, conference_id)
{
    //var progress_tpl = $available_participants_loading.html();

    /* установим имя конфы в попапе и откроем этот попап */
    $('.popup-conference-name').html(conference_name);
    $('#trigger-participants-select-modal').trigger('click');

    /* установка ид конференции */
    $('#conference-id').val(conference_id);
    $('#conference-name').val(conference_name);

    /* залочим кнопку и покажем стрелку лоадинг пока выполняется подгрузка списка */
    $participant_add_button.attr('disabled', true).addClass('btn-notActive');
    //$available_participants_list.html(progress_tpl);
    $available_participants_loading.show();
    $available_participants_list.hide();

    /* далее тут аякс запрос для получения возможных партиципантов для conference_id */
    $.ajax({
        type: 'get',
        url: _LANG_URL + '/conferences/get-available-participants?conference_id=' + conference_id,
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if ((response.status == true) && ("data" in response)) {

                    var tpl = $('#participant-row-tpl').html();
                    var participants = response.data;

                    //console_log(participants);
                    var participants_list = "";
                    for (var i = 0; i < participants.length; i++) {
                        participants[i]['num_pp'] = i + 1;
                        participants[i]['enc_participant_email'] = encodeName(participants[i]['participant_email']);
                        participants[i]['checked'] = participants[i]['user_enabled'] ? 'checked="checked"' : "";

                        participants_list += tpl.replace(/\{([a-z\_]+)\}/g, function (s, e) {
                            return participants[i][e];
                        });
                    }

                    $available_participants_list.html(participants_list);
                    $available_participants_loading.hide();
                    $available_participants_list.show();
                    setTimeout(function () {
                        createNiceScroll('#available-participants-list', false, true);
                        reInitNiceScroll('#available-participants-list');
                    }, 400);

                    if ("cnt_license_available" in response) {
                        cnt_license_available = response.cnt_license_available;
                    }
                }
                lock_unlock_AddParticipantButton();
            },
            500: function(response) {
                console_log(response);
                alert('An internal server error occurred.');
                lock_unlock_AddParticipantButton();
            }
        }
    });
}

/**
 * @param {object} row
 */
function selectParticipant(row)
{
    //console_log(row);
    if (row.length && !row.hasClass('row-empty')) {
        $available_participants_list.find('.manager-list__row').each(function() {
            $(this).removeClass('ui-selected');
        });
        row.addClass('ui-selected');
    }
}

/**
 * @returns {*}
 */
function addParticipant()
{
    /* блокируем кнопку на время выполнения (это заблокирует и повторный сабмит пока не уберем класс с кнопки) */
    $participant_add_button.attr('disabled', true).addClass('btn-notActive');

    var $participant_email = $('#participant-email');
    var email = $participant_email.val();

    /* если больше не хватает лицензий */
    if (cnt_license_available <= 0) {
        snackbar($wss_data.data('error_no_more_license'), 'error', 3000);
        /* разблокируем кнопку */
        lock_unlock_AddParticipantButton();
        return void(0);
    }

    /* если пытается добавить себя */
    if (email == _USER_EMAIL) {
        snackbar($wss_data.data('cant_add_self_into_the_list'), 'error', 3000);
        /* разблокируем кнопку */
        lock_unlock_AddParticipantButton();
        return void(0);
    }

    var enc_email = encodeName(email);
    var $el_scroll_to = $('#enc-' + enc_email);
    if ($el_scroll_to.length) {
        /* проверка нет ли такого участника уже в списке */
        //console_log($el_scroll_to);
        selectParticipant($el_scroll_to);
        var npp = $el_scroll_to.data('num-pp');
        if (npp > 0) { npp = npp + cnt_added; } else { npp = 0; }
        var h = $el_scroll_to.outerHeight() * npp - 70;
        //alert(h);
        $available_participants_list.getNiceScroll().doScrollPos(0, h);
        snackbar($wss_data.data('participant_already_exist'), 'error', 3000);
    } else {
        /* тут добавление строки в список (!! ЛОКАЛЬНОЕ ДОБАВЛЕНИЕ !! без отправки на сервер) */
        var tpl = $('#participant-row-tpl').html();
        var participants = {
            "participant_email": email,
            "user_id": null,
            "user_enabled": 0,
            "num_pp": 0,
            "enc_participant_email": enc_email,
            "checked": ""
        };

        var new_participants = tpl.replace(/\{([a-z\_]+)\}/g, function (s, e) {
            return participants[e];
        });

        $available_participants_list
            .prepend(new_participants);
        $el_scroll_to = $('#enc-' + enc_email);
        cnt_added++;
        selectParticipant($el_scroll_to);
        //$participant_email[0].blur();
        //$participant_email.val('');
        $participant_email[0].blur();
        var $form = $("#form-participant-add");
        $form[0].reset();
        $form.focus();
        $form.yiiActiveForm("resetForm");

        cnt_license_available--;

        $.ajax({
            type: 'get',
            url: _LANG_URL + '/conferences/check-participant?participant_email=' + email,
            dataType: 'json',
            statusCode: {
                200: function(response) {
                    if ((response.status == true) && ("data" in response)) {

                        var plus = response.data;
                        setTimeout(function() {
                            cnt_license_available = cnt_license_available + plus;
                            lock_unlock_AddParticipantButton();
                        }, 500);

                    }
                }
            }
        });
    }

    lock_unlock_AddParticipantButton();
    return void(0);
}

/**
 *
 * @param {boolean} is_new
 */
function setParticipantsForConference(is_new)
{
    var conference_id;
    var conference_name;
    var participants = [];
    if (!is_new) {
        conference_id = $('#conference-id').val();
        conference_name = $('#conference-name').val();
        var progress_tpl = $available_participants_loading.html();
        var $td_participants = $('#td-participants-' + conference_id);
        var i = 0;
        $available_participants_list.find('input[type=checkbox]:checked').each(function () {
            participants[i] = {
                "participant_email": $(this).data('participant-email'),
                "user_id": $(this).data('user-id'),
                "user_enabled": $(this).data('enabled'),
            };
            i++;
        });
        //console_log(participants);

        $td_participants.html(progress_tpl);
        //$.fancybox.close();
        $available_participants_loading.show();
        $available_participants_list.hide();

        /* блокируем кнопку на время выполнения (это заблокирует и повторный сабмит пока не уберем класс с кнопки) */
        $participant_add_button.attr('disabled', true).addClass('btn-notActive');
    } else {
        conference_id = 0
        conference_name = $('#conferenceaddform-conference_name').val();
    }

    $.ajax({
        type: 'post',
        url: _LANG_URL + '/conferences/set-participants',
        data: {
            conference_id   : conference_id,
            conference_name : conference_name,
            participants    : participants
        },
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true && "data" in response) {

                    $.fancybox.close();

                    //console_log(response.data);
                    if (response.data.is_new_conference) {
                        $.pjax.reload({container: '#conferences-list-content', async: true});
                        $('#conferenceaddform-conference_name').val('');
                        finishImgProgressForm($('#form-conference-create'));

                        /* показываем попап-окно с гостевой ссылкой */
                        $('#for-send-conference-id').val(response.data.conference_id);
                        $('#for-send-conference-name').val(response.data.conference_name);
                        $('#for-send-conference-guest-hash').val(response.data.conference_guest_hash);
                        $('#guest-link-field').val(response.data.conference_guest_link);
                        $('#trigger-guest-link-modal').trigger('click');

                    } else {
                        $td_participants.html(response.data.participants_html);
                    }
                    initToolTip();

                } else {

                    $available_participants_loading.hide();
                    $available_participants_list.show();
                    if ("info" in response) {
                        snackbar(response.info, 'error', 3000, false, null, 'conferences.participants');
                    }
                }

                lock_unlock_AddParticipantButton();
            },
            500: function(response) {
                console_log(response);
                alert('An internal server error occurred.');
            }
        }
    });
}

