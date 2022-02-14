"use strict";

/* set constants */
var CHUNK_SIZE = 65536;
var FIRST_CHUNK_SIZE = 1024;
var CHUNK_MULTIPLIER = 16;
var DOWNLOAD_TASK_TIMEOUT = 70*1000;
var DOWNLOAD_CHUNK_TIMEOUT = 30*1000;
var NUM_OF_CHANNELS = 5;
var NUM_OF_SIGNAL_CONNECTIONS = 1;
var MAX_CHUNKS_IN_BUFFER = 500;
var DOWNLOAD_CHANNEL_OPENING_TIMEOUT = 8000;
var DOWNLOAD_AVAILABILITY_INFO_TIMEOUT = 12000;
var DATA_RECEIVING_CHECK_INTERVAL = 5000;

/* add some required elements */
var ice_servers = { "iceServers": [ {"urls": "stun:stun.pvtbox.net:443" } ] };

var store_download_tasks = [];

/* common-functions */
/**
 * функция для проверки возможности работы с вебсокетом
 * @returns {boolean}
 */
function check_browser_for_websocket()
{
    // check websocket
    var websocket = window.WebSocket;
    if (websocket === undefined) {
        return false;
    }
    return true;
}

/**
 * функция для проверки возможности работы с ртц
 * @returns {boolean}
 */
function check_browser_for_webrtc()
{
    // check webrtc
    var RTCPeerConnection = window.RTCPeerConnection || window.mozRTCPeerConnection || window.webkitRTCPeerConnection;
    var RTCSessionDescription = window.RTCSessionDescription || window.mozRTCSessionDescription;
    var RTCIceCandidate = window.RTCIceCandidate || window.mozRTCIceCandidate;

    if (RTCPeerConnection === undefined
        || RTCSessionDescription === undefined
        || RTCIceCandidate === undefined
        || RTCPeerConnection.prototype.createDataChannel === undefined){

        return false;
    }

    // RTCPeerConnection must be a constructor
    if (typeof(RTCPeerConnection) !== "function")
        return false;

    return true;
}

/**
 * форматирование размера
 * @param size integer
 * @returns string
 */
function human_readable_size(size) {
    /*
     * Converts number of bytes to human readable view,
     * e.g. 53889710 -> "51.39 MBytes"
     */
    size = +size ;
    if (size >=0 && size < 1000) {
        size = size + ' Bytes';
    } else if ( size >= 1000 && size < 1000000) {
        size = (size/1024).toFixed(2) + ' KBytes';
    } else if (size >= 1000000 && size < 1000000000) {
        size = (size/(1024*1024)).toFixed(2) + ' MBytes';
    } else if (size >= 1000000000){
        size = (size/(1024*1024*1024)).toFixed(2) + ' GBytes';
    } else {
        size = NaN;
    }
    return size;
}

/**
 * Скачиваем файл по урлу
 * @param url
 * @param file
 * @param is_proxy
 * @returns {boolean}
 */
function download_by_url(url, file, is_proxy)
{
    //console_log(url);return void(0);
    var anchor = document.createElement('a');
    anchor.href = url;
    anchor.download = file.file_name;
    anchor.id = file.last_event_uuid;
    //anchor.target = 'download_frame';
    anchor.target = '_self';
    var tmp = document.getElementById('div-for-download');
    tmp.appendChild(anchor);

    return clickOrLocation(anchor, is_proxy);
}

/**
 * Функция выполняет клик на анкоре или делает локайшн на урл в зависимости от юзер агента
 * @param anchor
 * @param is_proxy
 * @returns {boolean}
 */
function clickOrLocation(anchor, is_proxy)
{
    if (is_proxy) {
        $('#download-dialog-tpl').show();
    }
    var Touch = typeof window.ontouchstart != "undefined";
    var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    //$('#download-dialog-tpl').hide();
    if (anchor.click === 'undefined' || (Touch && iOS)) {
        //alert('location');
        closeDownloadIframe();
        window.location.href = anchor.href;
    } else {
        //alert('click');
        anchor.click();
        closeDownloadIframe();
    }
    return true;
}

/**
 * Основная функция запуска скачивания
 * @param file object
 */
function startDownloadFile(file)
{
    /*
    file.file_name = "1.jpg";
    file.file_size = 50873;
    file.last_event_uuid = "14c9f2bd4d1d4e58afaafe920162bca2";
    */

    /* проверка что файл до этого не был скачан*/
    var anchor = document.getElementById(file.last_event_uuid);
    if (anchor !== null) {
        console_log('Уже ранее скачано. Сразу отдаем ссылку.');
        clickOrLocation(anchor, true);
        return true;
    }

    /* подготовка урла на проксиноду, для скачивания файла с него, если по ртц не получится */
    var proxy_url = $('#div-for-download').attr('data-proxy-url');
    proxy_url = proxy_url.replace(/\{([a-zA-Z0-9\_]+)\}/g, function (s, e) {
        return file[e];
    });
    //console_log(proxy_url);

    /* если иос то качаем через прокси */
    var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    if (iOS) {
        download_by_url(proxy_url, file, true);
        return true;
    }

    /* если фаерфокс то качаем через прокси */
    // Addition on 03.10.2019:
    //   the issue was fixed in Filefox release 60.2.0esr
    var isFirefox = typeof navigator.mozGetUserMedia !== 'undefined';
    //if (isFirefox) {
    //    download_by_url(proxy_url, file, true);
    //    return true;
    //}

    /* проверка на вебсокет */
    if (!check_browser_for_websocket()) {
        console_log('WebSocket false. Download by Proxy');
        download_by_url(proxy_url, file, true);
        return false;
    }

    /* проверка на ртц */
    if (!check_browser_for_webrtc()) {
        console_log('WebRTC false. Download by Proxy');
        download_by_url(proxy_url, file, true);
        return false;
    }

    /* создание объекта transport */
    var transport = new Transport(ice_servers, NUM_OF_CHANNELS);
    transport.cb_error = function(error) {
        console_log(error);
        download_by_url(proxy_url, file, true);
        return false;
    };
    transport.cb_state_changed = function(state_info) {
        console_log(state_info);
    };

    /* создание объекта file-writer */
    var mime_type = MimeTypes.lookup(file.file_name) || "application/octet-stream";
    var File_Writer = get_appropriate_writer(file.file_size);
    var file_writer = new File_Writer({file_size: file.file_size, mime_type: mime_type});

    /* создание объекта download-task */
    var download_task = new Download_Task(
        transport,
        file_writer,
        file.last_event_uuid,
        file.file_name,
        file.file_size,
        {
            chunk_size: CHUNK_SIZE,
            first_chunk_size: FIRST_CHUNK_SIZE,
            chunk_multiplier: CHUNK_MULTIPLIER,
            task_timeout: DOWNLOAD_TASK_TIMEOUT,
            chunk_timeout: DOWNLOAD_CHUNK_TIMEOUT,
            channel_opening_timeout: DOWNLOAD_CHANNEL_OPENING_TIMEOUT,
            availability_info_timeout: DOWNLOAD_AVAILABILITY_INFO_TIMEOUT,
            data_receiving_check_interval: DATA_RECEIVING_CHECK_INTERVAL,
            max_chunks_in_buffer: MAX_CHUNKS_IN_BUFFER
        },
        {
            /* коллбек поосле успешного скачивания по ртц (тут сохраним в HTML объект типа <a> и инициируем клик по нему)*/
            cb_task_success: function (url) {
                console.info("download task success, url:", url);
                var progress_info = {
                    received_size: file.file_size,
                    effective_received_size: file.file_size,
                    duration: 1000
                };
                setInfoDownloadRow(file, progress_info, 'finish');
                download_by_url(url, file, false);
                return true;
            },

            /* коллбек в случае провала скачивания по ртц (тут скачиваем через прокси ноду)*/
            cb_task_failure: function (error) {
                if (error && error.code && error.code == Download_Task.ErrorCode.CHANNEL_OPENING_TIMEOUT) {
                    console_log("Switch to fallback mode (download by proxy-node)");
                    download_by_url(proxy_url, file, true);
                    return false;
                }
                else if (error && error.code && error.code == Download_Task.ErrorCode.TASK_CANCELED) {
                    console_log("TASK_CANCELED");
                }
                else {
                    var error_text = "Download task failure."
                    if (error && error.code) {
                        if ("message" in error) {
                            error_text += " (" + error.message + ")"
                        }
                        error_text += " [Error.Code = " + error.code + "]"
                    }
                    console_log(error_text);
                    elfinderInstance.error(error_text);
                    setCancelDownloadRow(file.last_event_uuid);
                }
            },

            /**/
            cb_warning: function (warning) {
                console_log(warning);
            },

            /* прогрес на этапе скачивания */
            cb_task_progress: function (progress_info) {
                setInfoDownloadRow(file, progress_info, 'progress');
                /*
                var recv_size = progress_info.received_size;
                var recv_time = progress_info.duration;
                var result = "received: " + human_readable_size(recv_size);
                result += " (" + recv_size + " Bytes), ";
                result += "time: " + (recv_time/1000).toFixed(2) + " s , ";
                result += "average speed: " + ((recv_size/1024.0/1024.0)*8/(recv_time/1000)).toFixed(2) + " Mbit/s";
                console_log('event_uuid: ' + file.last_event_uuid);
                console_log(result);
                */
            },

            /* коллбек метод который вызывается после успешного подключения к вебсокету объектом transport */
            cb_online_nodes: function(nodes) {
                if (typeof store_download_tasks[file.last_event_uuid] === 'undefined') {
                    console_log("Available nodes:");
                    console_log(nodes);
                    this.start();
                    store_download_tasks[file.last_event_uuid] = this;
                    addDownloadRow(file);
                }
            }
        }
    );

    /* подключаемся к сокету (после успешного подключения к сокету автоматически вызывается метод download_task.cb_online_nodes)*/
    for (var i=0; i < NUM_OF_SIGNAL_CONNECTIONS; i++){
        transport.open_signal_connection($('#div-for-download').attr('data-signal-url'));
    }
}

/**
 *
 * @param file
 */
function addDownloadRow(file)
{
    file.bytesSent = file_size_format(0, 1);
    file.bytesTotal = file_size_format(file.file_size, 1);
    var row = $('#download-dialog-rtc-row-tpl').html();
    var $row = row.replace(/\{([a-zA-Z0-9\_]+)\}/g, function (s, e) {
        return file[e];
    });

    var $total_sys_info = $('#total-download-sys-info');
    $total_sys_info.attr('data-total-size', parseInt($total_sys_info.attr('data-total-size')) + file.file_size);

    $('#preview_downloads-rtc').append($row);

    $('#download-dialog-rtc-tpl').show();
}

/**
 *
 * @param file
 * @param progress_info
 * @param status string progress|finish|pause|cancel
 */
function setInfoDownloadRow(file, progress_info, status)
{
    var recv_size = progress_info.effective_received_size;
    var recv_time = progress_info.duration;

    if (recv_size >= file.file_size) {
        status = 'finish';
    }
    file.bytesSent = file_size_format(recv_size, 1);
    file.bytesTotal = file_size_format(file.file_size, 1);
    file.percent = recv_size * 100 / file.file_size;
    //var speed = recv_size*8/(recv_time/1000);
    var speed = recv_size/(recv_time/1000);
    if (status == 'finish') {
        speed = 0;
        file.percent = 100;

        $('#download-task-' + file.last_event_uuid).find('.btn-pause-rtc-download').first().addClass('btn-notActive');
    }
    if (status == 'pause') {
        speed = 0;
    }
    //WAITING:1,INPROGRESS:2,FINISHED:3,PAUSED:4
    if (typeof store_download_tasks[file.last_event_uuid] != 'undefined') {
        if (store_download_tasks[file.last_event_uuid].get_state() == Download_Task.State.PAUSED) {
            speed = 0;
        }
    }
    file.speed = file_size_format(speed, 0) + 'ps';
    var pcnt = file.percent.toFixed(2) + '%';
    //console_log(pcnt);

    var $total_sys_info = $('#total-download-sys-info');
    $total_sys_info.attr('data-total-downloaded-size', parseInt($total_sys_info.attr('data-total-downloaded-size')) + recv_size);

    $('#download-file-info-' + file.last_event_uuid).html(file.bytesSent + '/' + file.bytesTotal + ', ' + file.speed);
    $('#download-file-percent-' + file.last_event_uuid).css({ width: pcnt });
    $('#download-task-' + file.last_event_uuid)
        .attr('data-file-downloaded', recv_size)
        .attr('data-file-speed', speed);

    recalcTotal();
}

/**
 *
 * @param obj
 */
function setPauseDownloadRow(obj)
{
    var last_event_uuid = obj.attr('data-event-uuid');
    if (typeof store_download_tasks[last_event_uuid] != 'undefined') {
        //console_log(store_download_tasks[last_event_uuid]);
        //console_log(store_download_tasks[last_event_uuid].get_state());
        if (obj.hasClass('pause')) {
            obj.removeClass('pause');
            obj.find('.glyphicon').first()
                .removeClass('glyphicon-pause')
                .addClass('glyphicon-play');
            store_download_tasks[last_event_uuid].pause();
        } else {
            obj.addClass('pause');
            obj.find('.glyphicon').first()
                .removeClass('glyphicon-play')
                .addClass('glyphicon-pause');
            store_download_tasks[last_event_uuid].resume();
        }
    }
}

/**
 *
 * @param event_uuid string
 */
function setCancelDownloadRow(event_uuid)
{
    var last_event_uuid = event_uuid; //obj.attr('data-event-uuid');
    if (typeof store_download_tasks[last_event_uuid] != 'undefined') {
        store_download_tasks[last_event_uuid].cancel();
        store_download_tasks[last_event_uuid] = null;
        delete store_download_tasks[last_event_uuid];
        //store_download_tasks.splice(last_event_uuid, 1);
        $('#download-task-' + last_event_uuid).fadeOut(1000, function() {
            $(this).remove();
            recalcTotal();
            //setTimeout(function() { recalcTotal(); }, 1000);
        });
    }
}

/**
 *
 */
function recalcTotal()
{
    var totla_downloaded = 0;
    var total_speed = 0;
    var total_size = 0;
    $('#preview_downloads-rtc').find('.file-row').each(function() {
        totla_downloaded += parseInt($(this).attr('data-file-downloaded'));
        total_speed += parseInt($(this).attr('data-file-speed'));
        total_size += parseInt($(this).attr('data-file-size'));
    });

    if (total_size > 0) {
        var percent = totla_downloaded * 100 / total_size;
    } else {
        percent = 0;
        $('#download-dialog-rtc-tpl').hide();
    }
    var pcnt = percent.toFixed(2) + '%';

    $('#total-download-file-info').html(file_size_format(totla_downloaded, 1) + '/' + file_size_format(total_size, 1) + ', ' + file_size_format(total_speed, 0) + 'ps');
    $('#total-download-file-percent').css({ width: pcnt });
}

/**
 *
 */
function cancelDownloadRTCFile()
{
    $('#preview_downloads-rtc').find('.file-row').each(function() {

        setCancelDownloadRow($(this).attr('data-event-uuid'));

    });

    //$('#download-dialog-rtc-tpl').hide();
}

/**
 *
 */
$(document).ready(function() {

    $(document).on('click', '.ui-dialog-titlebar-close4', function () {
        cancelDownloadRTCFile();
    });

    $(document).on('click', '.btn-pause-rtc-download', function() {
        if (!$(this).hasClass('btn-notActive')) {
            setPauseDownloadRow($(this));
        }
    });

    $(document).on('click', '.btn-cancel-rtc-download', function() {
        setCancelDownloadRow($(this).attr('data-event-uuid'));
    });

});
