/**
 *
 */
function sortGridDevices() {

    var rowsArray = [];
    var $list_items_node = $('#list-items-node');
    // Составить массив из TR
    var i = 0;
    $list_items_node.find('.item-node').each(function() {
        rowsArray[i] = $(this);
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
    }
    $list_items_node.show();
    initToolTip();
}

/**
 *
 * @param data
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
 * @param data
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

        var tr = $('#tpl-item-node').find('.item-node-not-empty').first().clone().removeClass('item-node-not-empty')
            .html()
            .replace(/\{([a-zA-Z0-9\_]+)\}/g, function (s, e) {
                return data[e];
            });
        $('#list-items-node')
            .prepend(tr)
            .find('.item-node-empty')
            .first().remove();
    }
}

/**
 *
 * @param node_id
 * @param data
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
            //$('#tr-node-' + node_id).remove();
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
 * @param node_id
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
    $('#tr-node-' + node_id).find('.hide-node').addClass('hide');
}

/**
 *
 * @param node_id
 */
function setNodeOffline(node_id)
{
    var $main_tr_node = $('#main-tr-node-' + node_id);

    $('#node-' + node_id + '-online')
        .removeClass('active')
        .attr('title', onlineLabels[0]);;
    $('#node-' + node_id + '-upload-speed').html(file_size_format(0, 0, 'KB'));
    $('#node-' + node_id + '-download-speed').html(file_size_format(0, 0, 'KB'));
    $('#tr-node-' + node_id).find('.hide-node').removeClass('hide');
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
 * @param node_id
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
                    //console_log($(document).find('#tr-node-' + node_id));
                    var $list_items_node = $('#list-items-node');
                    var min_page_size = $list_items_node.attr('data-min-page-size');
                    $list_items_node.find('#main-tr-node-' + node_id).remove();

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
 * @param node_id
 */
function showNodeLog(node_id)
{
    $('#list-items-node').find('.item-node-log').each(function() {
        if ($(this).attr('id') != 'tr-node-log-' + node_id) {
            //$(this).slideUp("slow", function() { $(this).html(''); });
        }
    });
    var el = $('#tr-node-log-' + node_id);
    if (el.is(":visible")) {
        $('#list-items-node').find('.scrollbar-box').each(function () {
            $(this).getNiceScroll().remove();
        });
        el.slideUp("slow", function() {
            $(this).html('');
            $('#list-items-node').find('.scrollbar-box').each(function () {
                $(this).niceScroll({cursorcolor:"#CDCDCD", cursorwidth:"5px", zindex:900});
            });
        });
    } else {
        $('#list-items-node').find('.scrollbar-box').each(function () {
            $(this).getNiceScroll().remove();
        });
        el.html($('#small-loading').html()).slideDown("slow", function () {
            $.ajax({
                type: 'get',
                url: _LANG_URL + '/user/devices-log?node_id=' + node_id,
                dataType: 'html',
                statusCode: {
                    200: function(response) {
                        el.html(response);
                        var $tr_node_log = $('#tr-node-log-' + node_id);
                        $tr_node_log.find('.replaceDateByJs').each(function() {
                            $(this).html(formDate.exec($(this).attr('data-ts')));
                        });
                        //el.slideDown("slow");
                        $('#list-items-node').find('.scrollbar-box').each(function () {
                            $(this).niceScroll({cursorcolor:"#CDCDCD", cursorwidth:"5px", zindex:900});
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

    /* Установка имени устройства и ос для ноды типа браузер */
    //console_log(jscd);
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

    //setNodeOffline(10441);
    //setNodeSpeedAndStatus(10441,  {"upload_speed": 0, "download_speed": 0, "disk_usage": 22084379, "node_status": 3});
    /*
     var lostRows = (countRows - (nodesList.length % countRows));
     for (var i = 1; i<=lostRows; i++) {
     nodesList[nodesList.length] = {node_id: null};
     }
     */
    //console_log(nodesList);

    $(document).on('click', '.show-node-log', function () {
        //console_log($('#share-ttl').val());
        showNodeLog($(this).attr('data-node-id'));
    });

    $(document).on('click', '.hide-node', function () {
        /*
         //console_log($('#share-ttl').val());
         var node_id = $(this).attr('data-node-id');
         var confirmation = true;
         if ($(this).attr('data-node-status') != 6 && !$('#node-wipe-button-' + node_id).hasClass('disabled')) {
         confirmation = confirm($.trim($('#confirm-hide-node').html().replace(/<br ?\/?>/g, "\n")));
         }
         if (confirmation) {
         setNodeHidden(node_id);
         } else {
         return false;
         }
         */
        var node_id = $(this).attr('data-node-id');

        //if ($(this).attr('data-node-status') != 6 && !$('#node-wipe-button-' + node_id).hasClass('disabled')) {
        var msg
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

    /*
     $(document).on('click touchend', '.devices-node-name-td', function(e) {
     alert($(this).children().first().html());
     })
     */

    //console_log(ws);
    if (typeof ws != 'undefined') {
        //ws.send('ddddd');
        ws.onmessage = function(message) {
            //console_log('MY');
            //console_log(statuses);
            console_log(message);
            //return true;
            var data = JSON.parse(message.data);

            if ("operation" in data) {

                /* обработка списка peer_list и установка всех значений для каждйо ноды */
                /*
                 data = {
                 "operation": "peer_list",
                 "data": [
                 { "node_ostype": "Linux", "ws_server_ip": "93.77.70.121", "node_devicetype": "desktop", "own": true, "disk_usage": 22084379.0, "is_online": true, "ws_server_port": "39037", "node_name": "progr-X556UQK", "node_status": "4", "upload_speed": 0.0, "download_speed": 0.0, "node_osname": "Ubuntu 17.10", "id": "12039", "type": "node" },
                 { "node_ostype": "Windows", "ws_server_ip": "93.77.70.121", "node_devicetype": "desktop", "own": true, "disk_usage": 22084379.0, "is_online": true, "ws_server_port": "39037", "node_name": "progr-1111111", "node_status": "5", "upload_speed": 0.0, "download_speed": 0.0, "node_osname": "Ubuntu 17.10", "id": "12040", "type": "node" }
                 ],
                 }
                 */
                if ((data.operation == "peer_list") && ("data" in data)) {
                    /* недописаный вариант для странице полностью на яваскрипте - гемор
                     var d = data.data;
                     var l = d.length;
                     var is_found = false;
                     for (var i=0; i<l; i++) {
                     is_found = false;
                     nodesList.find(function(node) {
                     if (node.node_id == d[i].id) {
                     is_found = true;
                     console_log(node);
                     node.node_download_speed = d[i].download_speed;
                     node.node_upload_speed   = d[i].upload_speed;
                     }
                     });
                     if (!is_found) {  console_log(''); }
                     }
                     */

                    /*
                    var nodes = data.data;
                    for (var i=0; i<nodes.length; i++) {
                        if (nodes[i].is_online && nodes[i].own && nodes[i].type == 'node') {
                            nodesOnline[nodesOnline.length] = nodes[i].id;
                            checkNodesOnline(false);
                        }
                    }
                    checkNodesOnline(false);
                    */

                    var d = data.data;
                    for (var i=0; i< d.length; i++) {
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
                /*
                 data = {
                 "operation": "node_status",
                 "node_id": "12039",
                 "data": { "upload_speed": 0, "download_speed": 0, "disk_usage": 22084379, "node_status": 4 }
                 }
                 */
                if ((data.operation == "node_status") && ("data" in data) && ("node_id" in data)) {

                    /*
                    if ("is_online" in data) {
                        if (data.is_online) {
                            if (!nodesOnline.in_array(node_id)) {
                                nodesOnline[nodesOnline.length] = node_id;
                            }
                        } else {
                            if (nodesOnline.length > 0) {
                                nodesOnline.unset(node_id);
                            }
                        }
                        checkNodesOnline(false);
                    }
                    */

                    setNodeSpeedAndStatus(data.node_id, data.data);
                    sortGridDevices();
                }

                /* установка статуса онлайн для ноды, если пришло сообщение о ее подключении к сигнальному */
                /*
                 data = {
                 "operation": "peer_connect",
                 "data": { "node_ostype": "Linux", "node_devicetype": "desktop", "ws_server_ip": "93.77.70.121", "type": "node", "node_name": "progr-X556UQK", "ws_server_port": "41225", "id": "12039", "is_online": true, "own": true, "node_osname": "Ubuntu 17.10" }
                 }
                 */
                if ((data.operation == "peer_connect") && ("data" in data) && ("id" in data.data)) {

                    /*
                    var node = data.data;
                    if (node.is_online && node.own && node.type != 'webfm') {
                        if (!nodesOnline.in_array(node.id)) {
                            nodesOnline[nodesOnline.length] = node.id;
                        }
                        checkNodesOnline(false);
                    }
                    */

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
                /*
                 data = {
                 "operation": "peer_disconnect",
                 "node_id": "12039"
                 }
                 */
                if ((data.operation == "peer_disconnect") && ("node_id" in data)) {

                    /*
                    if (nodesOnline.length > 0) {
                        nodesOnline.unset(data.node_id);
                        checkNodesOnline(false);
                    }
                    */

                    setNodeOffline(data.node_id);
                    sortGridDevices();
                }

                /* */
                /*
                 data = {
                 "operation": "remote_action_done",
                 "data": {тут_то_что пришло в канал}
                 }
                 */
                if ((data.operation == "remote_action_done") && ("data" in data)) {
                    setNodeRemoteActionDone(data.data);
                    sortGridDevices();
                }
            }
        }

    }
    sortGridDevices();
});
