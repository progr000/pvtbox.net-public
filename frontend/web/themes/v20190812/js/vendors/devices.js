/**
 *
 */
function sortGridDevices() {

    var rowsArray = [];
    var rowsLogArray = [];
    var $list_items_node = $('#list-items-node');
    // Составить массив из TR
    var i = 0;
    $list_items_node.find('.item-node').each(function() {
        rowsArray[i] = $(this);
        rowsLogArray[$(this).data('node-id')] = $('#tr-node-log-' + $(this).data('node-id'));
        i++;
    });

    //console_log(rowsArray);

    var replaceStatusOnSort = function (status) {
        switch (status) {
            case 4:
                return 0;
                break;
            case 3:
                return 1;
                break;
            case 9:
                return 2;
                break;
            case 8:
                return 3;
                break;
            case 7:
                return 4;
                break;
            case 5:
                return 5;
                break;
            case 1:
                return 6;
                break;
            case 6:
                return 7;
                break;
            case 0:
                return 8;
                break;
            case 2:
                return 9;
                break;
            case 100:
                return 10;
                break;
            default:
                return 100;
        }
    }
    // определить функцию сравнения, в зависимости от типа
    var compare = function(rowA, rowB) {
        var sort1 = replaceStatusOnSort(parseInt(rowA.attr('data-node-status')));
        var sort2 = replaceStatusOnSort(parseInt(rowB.attr('data-node-status')));
        return sort1 - sort2;
    };

    // сортировать
    rowsArray.sort(compare);

    // Убрать tbody из большого DOM документа для лучшей производительности
    //$list_items_node.html('');

    //console_log(rowsArray);
    // добавить результат в нужном порядке в TBODY
    // они автоматически будут убраны со старых мест и вставлены в правильном порядке
    for (var j = 0; j < rowsArray.length; j++) {
        $list_items_node.append(rowsArray[j]);
        $list_items_node.append(rowsLogArray[rowsArray[j].attr('data-node-id')]);
    }
    $list_items_node.show();
    initToolTip();
}

/**
 * @param {json} data
 */
function setNodeRemoteActionDone(data)
{
    if (("action_type" in data) && ("node_id" in data) && ("node_status" in data)) {
        $('#node-' + data.node_id + '-status')
            .html(statuses[data.node_status])
            .attr('class', '')
            .addClass(statusesHtmlClass[data.node_status]);

        var $main_tr_node = $('#main-tr-node-' + data.node_id);
        $main_tr_node.attr('data-node-status', data.node_status);

        if (data.action_type == 'logout') {
            $('#node-logout-button-' + data.node_id)
                .html(logoutStatuses[2])
                .addClass('disabled')
                .removeClass('logout-button');
        }
        if (data.action_type == 'wipe') {
            $('#node-logout-button-' + data.node_id)
                .html(logoutStatuses[2])
                .addClass('disabled')
                .removeClass('logout-button');
            $('#node-wipe-button-' + data.node_id)
                .html(wipeStatuses[2])
                .addClass('disabled')
                .removeClass('wipe-button');

            $main_tr_node.attr('data-node-wipe-status', 2);
            $main_tr_node.find('.hide-node').first().attr('data-node-wipe-status', 2);
        }
    }
}

/**
 *
 * @param {json} data
 */
function createNodeNewRecord(data)
{
    if (!(['webshare', 'webfm'].in_array(data.type))) {
        //console_log(data);
        if (!("upload_speed" in data)) { data.upload_speed = 0; }
        if (!("download_speed" in data)) { data.download_speed = 0; }
        if (!("disk_usage" in data)) { data.disk_usage = 0; }
        if (!("node_status" in data)) { data.node_status = 1; }
        data.node_id = data.id;
        data.onlineLabel = (data.is_online) ? onlineLabels[1] : onlineLabels[0];
        data.active = (data.is_online) ? 'active' : '';
        data.node_devicetype_label = devicesLabels[data.node_devicetype];
        data.node_ostype_lower = data.node_ostype.toLowerCase();
        data.node_disk_usage = file_size_format(parseInt(data.disk_usage), 0);
        data.node_status_int = data.node_status;
        data.node_status = statuses[data.node_status];
        data.node_status_html_class = statusesHtmlClass[data.node_status_int];
        data.node_upload_speed = file_size_format(parseInt(data.upload_speed), 0, 'KB');
        data.node_download_speed = file_size_format(parseInt(data.download_speed), 0, 'KB');

        var tr = $('#tpl-item-node')
            .find('.item-node-not-empty').first()
            //.find('tr.item-node').first()
            .clone().removeClass('item-node-not-empty')
            .html()
            .replace(/\{([a-zA-Z0-9\_]+)\}/g, function (s, e) {
                return data[e];
            });
        console_log(tr);
        $('#list-items-node')
            .prepend(tr)
            .find('.item-node-empty')
            .first().remove();
    }
}

/**
 *
 * @param {int} node_id
 * @param {json} data
 */
function setNodeSpeedAndStatus(node_id, data)
{
    if ("is_online" in data) { data.is_online ? setNodeOnline(node_id) : setNodeOffline(node_id); }
    if ("disk_usage" in data) { $('#node-' + node_id + '-disk-usage').html(file_size_format(data.disk_usage, 1)); }
    if ("upload_speed" in data) { $('#node-' + node_id + '-upload-speed').html(file_size_format(data.upload_speed, 0, 'KB') + 's'); }
    if ("download_speed" in data) { $('#node-' + node_id + '-download-speed').html(file_size_format(data.download_speed, 0, 'KB') + 's'); }

    if ("node_status" in data) {
        var status = parseInt(data.node_status);
        if (status in statuses) {
            $('#node-' + node_id + '-status')
                .html(statuses[status])
                .attr('data-status', status)
                .attr('class', '')
                .addClass(statusesHtmlClass[status]);
            $('#main-tr-node-' + node_id).attr('data-node-status', status);
        }

        /* вычисление онлайн или офлайн для ноды*/
        var OFFLINE_STATUSES = {0:"", 2:"", 5:"", 6:"", 7:""};
        var DELETED_STATUSES = {0:"", 2:"", 6:""};
        if (status in DELETED_STATUSES) {
            //$('#main-tr-node-' + node_id).remove();
            //$('#tr-node-log-' + node_id).remove();
        }
        if (status in OFFLINE_STATUSES) {
            /* при таких статусах нода офлайн */
            setNodeOffline(node_id);
        } else {
            /* иначе нода онлайн */
            setNodeOnline(node_id);
        }
    }
}

/**
 *
 * @param {int} node_id
 */
function setNodeOnline(node_id)
{
    $('#node-' + node_id + '-online')
        .removeClass('active')
        .addClass('active')
        .attr('title', onlineLabels[1]);
    $('#node-logout-button-' + node_id)
        .html(logoutStatuses[0])
        .addClass('logout-button')
        .removeClass('disabled');
    $('#node-wipe-button-' + node_id)
        .html(wipeStatuses[0])
        .addClass('wipe-button')
        .removeClass('disabled');
    $('#main-tr-node-' + node_id).find('.hide-node').addClass('hidden');
}

/**
 *
 * @param {int} node_id
 */
function setNodeOffline(node_id)
{
    var $main_tr_node = $('#main-tr-node-' + node_id);

    $('#node-' + node_id + '-online')
        .removeClass('active')
        .attr('title', onlineLabels[0]);;
    $('#node-' + node_id + '-upload-speed').html(file_size_format(0, 0, 'KB'));
    $('#node-' + node_id + '-download-speed').html(file_size_format(0, 0, 'KB'));
    $main_tr_node.find('.hide-node').removeClass('hidden');
    var $node_status = $('#node-' + node_id + '-status');
    //console_log(!([5, 7].in_array(parseInt($node_status.attr('data-status')))));
    if (!([5, 7].in_array(parseInt($node_status.attr('data-status'))))) {
        $node_status
            .html(statuses[7])
            .attr('data-status', 7)
            .attr('class', '')
            .addClass(statusesHtmlClass[7]);
        $main_tr_node.attr('data-node-status', 7);
    }

    var wiped_status = $main_tr_node.attr('data-node-wipe-status');
    if (wiped_status == 2) {
        $node_status
            .html(statuses[6])
            .attr('data-status', 6)
            .attr('class', '')
            .addClass(statusesHtmlClass[6]);
        $main_tr_node.attr('data-node-status', 6);
    }
    //.html(statuses[7]);
    //$('#node-' + node_id + '-status').html(statuses[status]).attr('data-status', status);
    /*
    $('#node-logout-button-' + node_id)
        .html(logoutStatuses[2])
        .addClass('disabled')
        .removeClass('logout-button');
    $('#node-wipe-button-' + node_id)
        .html(wipeStatuses[0]);
        .addClass('disabled')
        .removeClass('wipe-button');
    */
}

/**
 *
 * @param {int} node_id
 */
function setNodeHidden(node_id)
{
    $.ajax({
        type: 'get',
        url: _LANG_URL + '/user/set-node-hidden?node_id=' + node_id,
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true) {
                    //console_log($(document).find('#main-tr-node-' + node_id));
                    var $list_items_node = $('#list-items-node');
                    var min_page_size = $list_items_node.attr('data-min-page-size');
                    $list_items_node.find('#main-tr-node-' + node_id).remove();
                    $list_items_node.find('#tr-node-log-' + node_id).remove();

                    var cur_size = $list_items_node.find('.item-node').length;
                    if (cur_size < min_page_size) {
                        var $item_node_empty = $('#tpl-item-node').find('.item-node-empty').first();
                        if ($item_node_empty.length) {
                            $list_items_node.append($item_node_empty.clone());
                        }
                    }
                    //el.slideDown("slow");
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
 * @param {int} node_id
 */
function showNodeLog(node_id)
{
    var $el_tr = $('#tr-node-log-' + node_id);
    var $el_td = $el_tr.children().first();
    if ($el_tr.is(":visible")) {

        $el_td.fadeOut("slow", function () {
            $el_tr.slideUp("slow");
            $(this).html('');
        });

    } else {

        $el_tr.slideDown("slow");
        $el_td.hide().html($('#small-loading').html());
        $el_td.fadeIn("slow", function () {
            $.ajax({
                type: 'get',
                url: _LANG_URL + '/user/devices-log?node_id=' + node_id,
                dataType: 'html',
                statusCode: {
                    200: function(response) {
                        $el_td.html(response);
                        var $tr_node_log = $('#tr-node-log-' + node_id);
                        $tr_node_log.find('.replaceDateByJs').each(function() {
                            $(this).html(formDate.exec($(this).attr('data-ts')));
                        });
                        initToolTip();
                    },
                    500: function(response) {
                        console_log(response);
                        alert('An internal server error occurred.');
                    }
                }
            });
        });

    }
}

/**
 * ***
 * ***
 * ***
 * ***
 * ***
 */
$(document).ready(function() {

    /* Для тестирования добавления строк */
    $(document).on('click', '.test-add-empty-tr', function() {
        var $list_items_node = $('#list-items-node');
        var $item_node_empty = $('#tpl-item-node').find('.item-node-empty').first();
        if ($item_node_empty.length) {
            $list_items_node.append($item_node_empty.clone());
        }
    });
    $(document).on('click', '.test-add-node-tr', function() {
        var data = {
            type: 'addnode',
            upload_speed: 100,
            download_speed: 256,
            disk_usage: 5678,
            node_status: 3,
            id: 55,
            is_online: 1,
            node_devicetype: 'phone',
            node_ostype: 'Windows',
            node_osname: 'Windows 10',
            node_name: 'test-add-record'
        };
        createNodeNewRecord(data);
    });

    /* Установка имени устройства и ос для ноды типа браузер */
    var $node_devicetype_browser = $('#list-items-node').find('.node_devicetype-browser').first();
    if ($node_devicetype_browser.length && typeof jscd == 'object') {

        var os_and_version = jscd.os + ' ' + jscd.osVersion;
        var brovser_and_version = jscd.browser + ' ' + jscd.browserVersion;
        if (jscd.os == 'Linux' && jscd.osVersion != '') { os_and_version = jscd.osVersion; }

        var $device_os_type_td = $node_devicetype_browser.find('.device-os-type-td').first();
        $device_os_type_td.find('span').first().html(os_and_version);
        $device_os_type_td.find('div').first().removeClass().addClass('icon').addClass('icon-' + jscd.os.toLowerCase() + '-os');
        $node_devicetype_browser.find('.devices-node-name-td').first().find('span').first().attr('title', brovser_and_version).html(brovser_and_version);
    }

    $(document).on('click', '.show-node-log', function () {
        //console_log($('#share-ttl').val());
        showNodeLog($(this).attr('data-node-id'));
        return false;
    });

    $(document).on('click', '.hide-node', function () {

        var node_id = $(this).attr('data-node-id');

        var msg;
        if (($(this).attr('data-node-status') == 6) || ($(this).attr('data-node-wipe-status') == 1)) {
            msg = $('#confirm-hide-node-wiped').html()
        } else {
            msg = $('#confirm-hide-node').html()
        }

        prettyConfirm(function () {

            setNodeHidden(node_id);

        }, null, $.trim(msg), '', '');

        return false;
    });

    /* wss begin */
    var ws;
    var $wss_data = $('#wss-data');
    var noShowBalloon = ($('.noShowBalloon').length > 0);

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
            }
            ws.onmessage = function (message) {
                //console_log(statuses);
                console_log(message);
                //return true;
                var data = JSON.parse(message.data);

                if ("operation" in data) {

                    if ((data.operation == "peer_list") && ("data" in data)) {

                        var d = data.data;
                        for (var i = 0; i < d.length; i++) {
                            if (("id" in d[i]) && ("own" in d[i])) {
                                if (d[i].own) {
                                    if ($('#node-' + d[i].id + '-status').length) {
                                        /* если для ноды уже есть строка в таблице то просто обновим данные по этой ноде */
                                        setNodeSpeedAndStatus(d[i].id, d[i]);
                                    } else {
                                        /* если же строки нет, то создаем новую строку в таблице для этой ноды по шаблону */
                                        createNodeNewRecord(d[i]);
                                    }
                                }
                            }
                        }
                        sortGridDevices();
                    }

                    /* установка статусов, скорости отдачти, скорости загрузки, занятого дискового пространства для одной ноды */
                    if ((data.operation == "node_status") && ("data" in data) && ("node_id" in data)) {

                        setNodeSpeedAndStatus(data.node_id, data.data);
                        sortGridDevices();
                    }

                    /* установка статуса онлайн для ноды, если пришло сообщение о ее подключении к сигнальному */
                    if ((data.operation == "peer_connect") && ("data" in data) && ("id" in data.data)) {

                        if ($('#node-' + data.data.id + '-online').length) {
                            /* если для ноды уже есть строка в таблице то просто обновим данные по этой ноде */
                            setNodeOnline(data.data.id);
                        } else {
                            /* если же строки нет, то создаем новую строку в таблице для этой ноды по шаблону */
                            createNodeNewRecord(data.data);
                        }
                        sortGridDevices();
                    }

                    /* установка статуса офлайн для ноды, если пришло сообщение о ее отключении от сигнального */
                    if ((data.operation == "peer_disconnect") && ("node_id" in data)) {

                        setNodeOffline(data.node_id);
                        sortGridDevices();
                    }

                    /* */
                    if ((data.operation == "remote_action_done") && ("data" in data)) {
                        setNodeRemoteActionDone(data.data);
                        sortGridDevices();
                    }
                }
            }
        }
    }
    /* wss end */

    sortGridDevices();
});
