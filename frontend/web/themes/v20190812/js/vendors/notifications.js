var setNotificationsAsReadTimeout = 5000;
var current_count_unread_notifications = 0;
var setReportsAsReadTimeout = 5000;
var current_count_unread_reports = 0

/**
 *
 * @param {boolean} is_first
 */
function getCountNewNotifications(is_first)
{
    $.ajax({
        type: 'get',
        url: _LANG_URL + '/user/count-new-notifications',
        dataType: 'json',
        statusCode: {
            200: function(response) {

                var total_count = 0;

                /* проверка есть ли новые нотификайшены или нет*/
                if (response.count_new_notifications > 0) {
                    /* установка красного кружочка с количеством новых нотификайшенов */
                    total_count += parseInt(response.count_new_notifications);
                    if ($('#menu-count-new-notifications').length) {
                        $('#menu-count-new-notifications').find('b:first').remove();
                        $('#menu-count-new-notifications').prepend('<b>' + response.count_new_notifications + '</b>');
                    }
                    /* Обновление списка нотификайшенов через паджакс*/
                    if ($('#notifications-list-content').length) {
                        if (!is_first) {
                            $.pjax.reload({container: '#notifications-list-content', async: true});
                        }
                    }
                } else {
                    if ($('#menu-count-new-notifications').length) {
                        $('#menu-count-new-notifications').find('b:first').remove();
                    }
                }

                /* проверка есть ли новые евенты или нет*/
                var $count_new_reports = $('.count-new-reports');
                if (response.count_new_events > 0) {
                    /* установка красного кружочка с количеством новых евентов */
                    //total_count += parseInt(response.count_new_events);
                    if ($count_new_reports.length) {
                        $count_new_reports.html('<b>' + response.count_new_events + '</b>');
                    }
                    /* Обновление списка евентов через паджакс*/
                    if ($('#events-list-content').length) {
                        if (!is_first) {
                            var $reportTab = $('#reportTab');
                            if ($reportTab.length && $reportTab.hasClass('active')) {
                                window.location.reload();
                            }
                            //$.pjax.reload({container: '#events-list-content', async: true});
                        }
                    }
                } else {
                    if ($count_new_reports.length) {
                        $count_new_reports.html('');
                    }
                }

                /* проверка есть ли суммарно новые нотификайшены+евенты или нет */
                if (total_count > 0) {
                    $('#count-new-notifications').html('<b>' + total_count + '</b>');
                } else {
                    $('#count-new-notifications').html('');
                }

                /* перезапуск ф-ии каждые 30 сек */
                setTimeout(function() { getCountNewNotifications(false); }, 30000);
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
function setNotificationsAsRead()
{
    if ($('.notify-tbl').length) {

        setTimeout(function () {

            var ids = [];
            var i = 0;
            $('.notify-tbl tbody').find('tr.notif-row').each(function () {
                var id = $(this).data('notif-id');
                if (id) {
                    ids[i] = id;
                    i++;
                }
            });
            //console_log(ids);

            $.ajax({
                type: 'post',
                url: _LANG_URL + '/user/set-notifications-as-read',
                dataType: 'json',
                data: {
                    ids: ids
                },
                statusCode: {
                    200: function (response) {
                        var count_read = response.count_read;
                        var count_unread = response.count_unread;
                        current_count_unread_notifications = count_unread;
                        $('.notify-tbl tbody').find('tr.notif-row').each(function () {
                            $(this).removeClass('isnew');
                        });
                        if (count_unread > 0) {
                            $('#count-new-notifications').html('<b>' + count_unread + '</b>');
                        } else {
                            $('#count-new-notifications').html('');
                        }
                    },
                    500: function (response) {
                        console_log(response);
                        alert('An internal server error occurred.');
                    }
                }
            });

        }, setNotificationsAsReadTimeout);

    }
}

/** */
var is_first_open = true;
$(document).ready(function(){

    /* пресеты для звука */
    ion.sound({
        sounds: [
            {
                name: "call",
                loop: false,
                volume: 0.5
            }
        ],
        volume: 0.5,
        path: "/themes/v20190812/sounds/",
        preload: true
    });
    /* sound usage */
    //ion.sound.play("call", { loop: true });
    //setTimeout(function() {
    //    ion.sound.stop();
    //}, 5000);

    /* test */
    //var data = {
    //    "operation": "call",
    //    "data": {
    //        "room_uuid":"fff", "conference_name": "ffdfdf", "conference_id": 36, "caller_id": 1, "room_ur": ""
    //    }
    //};
    //flash_msg('conference_room_was_opened', 'success', 30000, true, data.data, null, 'call', true);

    /**/
    var $notifications_container = $('#notifications-container');
    if ($notifications_container) {
        current_count_unread_notifications = $notifications_container.data('current-count-unread-notifications');
    }

    //getCountNewNotifications(true);
    setNotificationsAsRead();

    /**/
    $(document).on('click', '.go-to-webfm-folder', function() {
        $.ajax({
            type: 'get',
            url: _LANG_URL + '/elfind?cmd=hashForPath&name=' + $(this).data('folder-name'),
            dataType: 'json',
            statusCode: {
                200: function(response) {
                    if (typeof response.link != 'undefined') {
                        window.location.href = response.link;
                    }
                },
                500: function(response) {
                    console_log(response);
                    alert('An internal server error occurred.');
                }
            }
        });
        return false;
    });

    /**/
    $(document).on('click', '.go-to-conference', function() {
        window.location.href = '/user/conferences?mark=' + $(this).data('conference-id');
        return false;
    });

    /* wss begin */
    var ws;
    var $wss_data = $('#wss-data-notifications');

    if ($wss_data.length && $wss_data[0].hasAttribute('data-wss-url')) {
        //ws = new Ws('ws://echo.websocket.org', 5);
        ws = new Ws($wss_data.data('wss-url'), 5); // второй параметр если 0 то реконекта не будет, иначе реконект после заданного кол-ва секунд

        if (typeof ws != 'undefined') {
            ws.onclose = function () {

                if (ws.reconnect > 0) {
                    setTimeout(function () {

                        console_log('Connection lost to ' + ws._url);
                        console_log('Trying restore connection to ' + ws._url + 'in ' + ws.reconnect + ' seconds');

                        if ($wss_data[0].hasAttribute('data-token')) {
                            $.ajax({
                                type: 'post',
                                url: _LANG_URL + '/user/check-site-token',
                                data: {
                                    site_token: $wss_data.data('token')
                                },
                                dataType: 'json',
                                success: function (response) {
                                    if ("result" in response && response.result == "success") {

                                        //console_log(typeof ws.connect);
                                        ws.connect();//.bind(this);

                                    } else {
                                        console_log('Disconnected from ' + ws._url);
                                        console_log('Session is die. Reloading the page.');
                                        window.location.href = "/";
                                    }
                                },
                                error: function (e) {
                                    console_log(e);
                                    console_log('Disconnected from ' + ws._url);
                                },
                            });
                        } else {
                            console_log('Disconnected from ' + ws._url);
                            console_log("#wss-data hasn't attribute data-token. Can't reconnect.");
                        }
                    }.bind(this), ws.reconnect * 1000);
                } else {
                    console_log('Disconnected from ' + ws._url);
                }
            };
            ws.onmessage = function (message) {
                console_log(message);
                var data = JSON.parse(message.data);
                if ("operation" in data) {

                    if ((data.operation == "new_notifications_count") && ("data" in data)) {

                        //console_log(data);
                        var count_notifications = parseInt(data.data.count);

                        /* проверка есть ли суммарно новые нотификайшены+евенты или нет */
                        if (count_notifications > 0) {
                            $('#count-new-notifications').html('<b>' + count_notifications + '</b>');
                        } else {
                            setTimeout(function() {
                                $('#count-new-notifications').html('');
                            }, setNotificationsAsReadTimeout);
                        }

                        /* Обновление списка нотификайшенов через паджакс */
                        if ($('#notifications-list-content').length) {
                            if (!is_first_open) {
                                if (current_count_unread_notifications != count_notifications) {
                                    if (count_notifications > 0) {
                                        $.pjax.reload({container: '#notifications-list-content', async: true});
                                    }
                                }
                            }
                        }
                        current_count_unread_notifications = count_notifications;
                    }

                    if ((data.operation == "new_reports_count") && ("data" in data)) {

                        //console_log(data);
                        var count_reports = parseInt(data.data.count);

                        /* проверка есть ли суммарно новые нотификайшены+евенты или нет */
                        if (count_reports > 0) {
                            $('.count-new-reports').html('<b>' + count_reports + '</b>');
                        } else {
                            setTimeout(function() {
                                $('.count-new-reports').html('');
                            }, setReportsAsReadTimeout);
                        }

                        /* Обновление списка репортов через релоад */
                        if ($('#events-list-content').length) {
                            if (!is_first_open) {
                                if (current_count_unread_reports != count_reports) {
                                    if (count_reports > 0) {
                                        setTimeout(function () {
                                            window.location.reload();
                                        }, 1000);
                                    }
                                }
                            }
                        }

                    }

                    //{"operation": "call", "data": {"room_uuid": <room_uuid>, "conference_name": <conference_name>, "conference_id": <conference_id>, "caller_id": <caller_id>, "room_ur": <url>}}
                    if ((data.operation == "call") && ("data" in data)) {
                        flash_msg('conference_room_was_opened', 'success', 30000, true, data.data, null, 'call', true);
                        if ($('#conferences-list-content').length) {
                            $.pjax.reload({container: '#conferences-list-content', async: true});
                        }
                    }

                    //{operation: "room_closed", data: {room_uuid=room_uuid} }
                    if ((data.operation == "room_closed") && ("data" in data)) {
                        if ($('#conferences-list-content').length) {
                            $.pjax.reload({container: '#conferences-list-content', async: true});
                        }
                    }

                    //{"operation": "call_processed", "data": {"room_uuid": <room_uuid>, "caller_id": <caller_id>, "accepted": <0|1>}}
                    if ((data.operation == "call_processed") && ("data" in data)) {
                        /*
                         оно приходит если на других нодах этого юзера приняли или отклонили звонок
                         хз нужно ли оно нам
                         поидее можно переставать звонить если кто-то на другом девайсе принял вызов
                         так не бывает )
                         ну и когда сами принимаем вызов, то нужно посылать точно такое же сообщение
                         звонок это вызов какого то звукового файла
                         он доиграет же
                         ну я про нотификацию
                         типа скрывать
                         ну и аудио наверное можно останавливать
                         в общем пока можно забить на это сообщение
                        */
                    }
                }
            };
        }
    }
    /* wss end */

    is_first_open = false;
});
