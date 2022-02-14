$(document).ready(function(){
    getCountNewNotifications(true);
});

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
                            $.pjax.reload({container: '#notifications-list-content', async: false});
                        }
                    }
                } else {
                    if ($('#menu-count-new-notifications').length) {
                        $('#menu-count-new-notifications').find('b:first').remove();
                    }
                }

                /* проверка есть ли новые евенты или нет*/
                if (response.count_new_events > 0) {
                    /* установка красного кружочка с количеством новых евентов */
                    //total_count += parseInt(response.count_new_events);
                    if ($('#menu-count-new-events').length) {
                        $('#menu-count-new-events').find('b:first').remove();
                        $('#menu-count-new-events').prepend('<b>' + response.count_new_events + '</b>');
                    }
                    /* Обновление списка евентов через паджакс*/
                    if ($('#events-list-content').length) {
                        if (!is_first) {
                            $.pjax.reload({container: '#events-list-content', async: false});
                        }
                    }
                } else {
                    if ($('#menu-count-new-events').length) {
                        $('#menu-count-new-events').find('b:first').remove();
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