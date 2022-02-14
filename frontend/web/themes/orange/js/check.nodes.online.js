var need_reload = false;
var last_reload = 0;
var cur_timestamp = 0;
var timeout_for_reload = 30; //seconds
var timeoutSuspended;
var globalTimeoutRelodFM = 1000 * 60 * 5;
var globalClearReloadFM;

/**
 *
 */
function reloadFM()
{
    if (typeof globalClearReloadFM != 'undefined') { clearTimeout(globalClearReloadFM); }

    cur_timestamp = Date.now();

    console_log('reloading FM content');
    last_reload = Date.now();
    elfinderInstance.exec('reload');

    globalClearReloadFM = setTimeout(function() { reloadFM(); }, globalTimeoutRelodFM);
}


/**
 * ***
 * ***
 * ***
 * ***
 * ***
 */
$(document).ready(function() {

    if ((typeof elfinderInstance !== 'undefined') && elfinderInstance) {
        setTimeout(function () {
            reloadFM();
        }, globalTimeoutRelodFM);
    }

    if ($('#no-check-online-nodes').length) {
        nodesOnline[nodesOnline.length] = nodesOnline.length + 1;
    }

    if (typeof ws != 'undefined') {
        //ws.send('ddddd');
        var noShowBalloon = ($('.noShowBalloon').length > 0) ? true : false;
        //alert(noShowBalloon);
        ws.onmessage = function(message) {
            //console_log('MY');
            console_log(message);
            var data = JSON.parse(message.data);
            if ("operation" in data) {
                /*
                 {"operation": "peer_connect", "data": {"is_online": true, "node_osname": null, "ws_server_ip": "5.129.77.63", "node_devicetype": null, "type": "webfm", "node_name": null, "id": "d507bdd42f903382b65a5aa38b840767", "node_ostype": null, "own": true}}' to 3 node(s) of user ID '5420', except node(s) '('d507bdd42f903382b65a5aa38b840767',)'

                 {"operation": "peer_list", "data": [{"is_online": true, "node_status": "4", "disk_usage": 0.0, "id": "10642", "node_name": "asus ASUS_X014D", "node_devicetype": "phone", "node_ostype": "Android", "download_speed": 0.0, "node_osname": "Android 5.1.1", "ws_server_ip": "5.129.77.63", "type": "node", "own": true, "upload_speed": 0.0}, {"is_online": false, "disk_usage": 0.0, "id": "10641", "node_name": "asus ASUS_X014D", "node_devicetype": "phone", "node_ostype": "Android", "download_speed": 0.0, "node_osname": "Android 5.1.1", "node_status": "4", "type": "node", "own": true, "upload_speed": 0.0}, {"is_online": false, "node_osname": "Android 6.0.1", "node_devicetype": "phone", "type": "node", "node_name": "samsung SM-J106F", "id": "10643", "node_ostype": "Android", "own": true}, {"is_online": false, "node_osname": "Android 7.0", "node_devicetype": "phone", "type": "node", "node_name": "HUAWEI BG2-U01", "id": "10632", "node_ostype": "Android", "own": true}, {"is_online": false, "node_osname": "Android 7.0", "node_devicetype": "phone", "type": "node", "node_name": "HUAWEI BG2-U01", "id": "10633", "node_ostype": "Android", "own": true}, {"is_online": false, "disk_usage": 0.0, "id": "10640", "node_name": "asus ASUS_X014D", "node_devicetype": "phone", "node_ostype": "Android", "download_speed": 0.0, "node_osname": "Android 5.1.1", "node_status": "4", "type": "node", "own": true, "upload_speed": 0.0}]}

                 {"node_id": "10710", "operation": "peer_disconnect"}
                 */
                /* если получен список нод */
                if (data.operation == "peer_list" && "data" in data) {
                    /*
                     var nodes  = [
                     {"is_online": true, "node_status": "4", "disk_usage": 0.0, "id": "10642", "node_name": "asus ASUS_X014D", "node_devicetype": "phone", "node_ostype": "Android", "download_speed": 0.0, "node_osname": "Android 5.1.1", "ws_server_ip": "5.129.77.63", "type": "node", "own": true, "upload_speed": 0.0},
                     {"is_online": false, "disk_usage": 0.0, "id": "10641", "node_name": "asus ASUS_X014D", "node_devicetype": "phone", "node_ostype": "Android", "download_speed": 0.0, "node_osname": "Android 5.1.1", "node_status": "4", "type": "node", "own": true, "upload_speed": 0.0},
                     {"is_online": false, "node_osname": "Android 6.0.1", "node_devicetype": "phone", "type": "node", "node_name": "samsung SM-J106F", "id": "10643", "node_ostype": "Android", "own": true},
                     {"is_online": false, "node_osname": "Android 7.0", "node_devicetype": "phone", "type": "node", "node_name": "HUAWEI BG2-U01", "id": "10632", "node_ostype": "Android", "own": true}, {"is_online": false, "node_osname": "Android 7.0", "node_devicetype": "phone", "type": "node", "node_name": "HUAWEI BG2-U01", "id": "10633", "node_ostype": "Android", "own": true},
                     {"is_online": false, "disk_usage": 0.0, "id": "10640", "node_name": "asus ASUS_X014D", "node_devicetype": "phone", "node_ostype": "Android", "download_speed": 0.0, "node_osname": "Android 5.1.1", "node_status": "4", "type": "node", "own": true, "upload_speed": 0.0}
                     ];
                     */
                    var nodes = data.data;
                    for (var i=0; i<nodes.length; i++) {
                        if (nodes[i].is_online && nodes[i].own && nodes[i].type == 'node') {
                            nodesOnline[nodesOnline.length] = nodes[i].id;
                            checkNodesOnline(noShowBalloon);
                        }
                    }
                    checkNodesOnline(noShowBalloon);
                    if ((typeof elfinderInstance !== 'undefined') && elfinderInstance) { toolbarButtonsPrepare(elfinderInstance); }
                }

                /* если получен коннект от какой то ноды */
                if (data.operation == "peer_connect" && "data" in data) {
                    //var node = {"is_online": true, "node_osname": null, "ws_server_ip": "5.129.77.63", "node_devicetype": null, "type": "webfm", "node_name": null, "id": "d507bdd42f903382b65a5aa38b840767", "node_ostype": null, "own": true};
                    var node = data.data;
                    if (node.is_online && node.own && node.type != 'webfm') {
                        if (!nodesOnline.in_array(node.id)) {
                            nodesOnline[nodesOnline.length] = node.id;
                        }
                        checkNodesOnline(noShowBalloon);
                        if ((typeof elfinderInstance !== 'undefined') && elfinderInstance) { toolbarButtonsPrepare(elfinderInstance); }
                    }
                }

                /* если получен дисконнект от какой то ноды */
                if (data.operation == "peer_disconnect" && "node_id" in data) {
                    //var node_id = data.node_id;
                    if (nodesOnline.length > 0) {
                        nodesOnline.unset(data.node_id);
                        checkNodesOnline(noShowBalloon);
                        if ((typeof elfinderInstance !== 'undefined') && elfinderInstance) { toolbarButtonsPrepare(elfinderInstance); }
                    }
                }

                if (data.operation == "upload_complete") {
                    if ((typeof elfinderInstance !== 'undefined') && elfinderInstance) {
                        need_reload = true;
                    }
                }

                cur_timestamp = Date.now();
                if (data.operation == "file_events") {
                    if ((need_reload || (cur_timestamp - last_reload > timeout_for_reload * 1000))) {
                        if ((typeof elfinderInstance !== 'undefined') && elfinderInstance) {
                            need_reload = false;
                            elfinderInstance.exec('reload');
                            last_reload = Date.now();
                        }
                    } else {
                        //первый вариант - переоткладывание на 30 секунд таймера при повторах данной ситуации
                        if (typeof timeoutSuspended != 'undefined') { clearTimeout(timeoutSuspended); }
                        timeoutSuspended = setTimeout(function() {
                            elfinderInstance.exec('reload');
                            console_log('reload for suspended');
                        }, 3000);
                        //второй вариант - ничего не делать в случае если таймер уже установлен а иначе запустить таймер откладывания
                        /*
                         if (typeof timeoutSuspended == 'undefined' || !timeoutSuspended) {
                         timeoutSuspended = setTimeout(function() { elfinderInstance.exec('reload'); timeoutSuspended = false; }, 3000);
                         }
                         */
                    }

                    if ((typeof elfinderInstance !== 'undefined') && elfinderInstance) {
                        setTimeout(function () {
                            reloadFM();
                        }, globalTimeoutRelodFM);
                    }
                }
            }
        }
    }

});