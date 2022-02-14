"use strict";
var try_pass = 0;
var current_download_mode = 0;

/* set constants */
var CHUNK_SIZE = 65536;
var FIRST_CHUNK_SIZE = 1024;
var CHUNK_MULTIPLIER = 16;
var DOWNLOAD_TASK_TIMEOUT = 70*1000;
var DOWNLOAD_CHUNK_TIMEOUT = 30*1000;
var NUM_OF_CHANNELS = 5;
var NUM_OF_SIGNAL_CONNECTIONS = 1;
var MAX_CHUNKS_IN_BUFFER = 500;
var MAX_CHUNKS_IN_BUFFER_FOR_PREVIEW = 50;
var DOWNLOAD_CHANNEL_OPENING_TIMEOUT = 8000;
var DOWNLOAD_AVAILABILITY_INFO_TIMEOUT = 12000;
var DATA_RECEIVING_CHECK_INTERVAL = 5000;
/* end set constants */

var store_download_tasks = [];

var DOWNLOAD_MODE = {
    WEBRTC: 1,
    PROXY: 2,
    APP: 3
};

var scripts = [];
var errors = [];
var warnings = [];

var web_tracking = new Web_Tracking("https://tracking.pvtbox.net:443/1", 2, "browser", "webshare");
var _is_started = false;  //flag to know if user starts download by clicking on button
var _attempt_to_proxy = false;


// set some html elements
var $wss_data,
    $btn_download,
    $btn_download_by_app,
    $total_progress_download_rtc;

// pass some parameters to variables
var ice_servers,
    connection_string_orig,
    connection_string,
    proxy_node_url,
    proxy_node_url_orig,
    app_node_url,
    app_node_url_orig,
    file_name,
    file_size,
    share_hash,
    event_uuid,
    share_delete_immediately,
    share_enable_password;

// some rtc vars
var pending_transport = false,
    _rx = 0,
    _duration = 0.0,
    progress_interval;

var run_check_pass_for_preview = false;

/************ ******* ************/
$(document).ready(function() {

    // set some html elements
    $wss_data = $('#wss-data');
    $btn_download = $('#btn_download');
    $btn_download_by_app = $('#btn_download_by_app');
    $total_progress_download_rtc = $('#total-progress-download-rtc');

    // pass some parameters to variables
    ice_servers = {"iceServers":[{"urls": "stun:" + $wss_data.data('stun-url')}]};
    connection_string = jQuery.trim($wss_data.data('signal-url'));
    connection_string_orig = connection_string;
    proxy_node_url = jQuery.trim($wss_data.data('proxy-url'));
    proxy_node_url_orig = proxy_node_url;
    app_node_url = jQuery.trim($wss_data.data('app-url'));
    app_node_url_orig = app_node_url;
    file_name = jQuery.trim($wss_data.data('file-name'));
    file_size = parseInt($wss_data.data('file-size'));
    share_hash = jQuery.trim($wss_data.data('share-hash'));
    event_uuid = jQuery.trim($wss_data.data('event-uuid'));
    share_delete_immediately = parseInt($wss_data.data('share-delete-immediately'));
    share_enable_password = parseInt($wss_data.data('share-enable-pass'));


    $('#td-file-size').html(human_readable_size(file_size) + " (" + file_size + " Bytes)");

    if ($wss_data.length) {

        /**/
        startDownloadShare();

        /**/
        if (checkIsCanPreviewed(file_name) && !share_delete_immediately) {

            var file_obj = {
                "file_name": file_name,
                "file_size": file_size,
                "last_event_uuid": event_uuid
            };
            //console_log(file_obj);
            hideSiteLoader();

            var $total_container_id = $('#total-container-id');
            var total_h = Math.max(
                parseInt($total_container_id.height()),
                parseInt($total_container_id.innerHeight())
            );

            var $control_container_id = $('#wss-data');
            var control_h = Math.max(
                parseInt($control_container_id.height()),
                parseInt($control_container_id.innerHeight())
            )

            var preview_h = parseInt(total_h - control_h) - 120;
            if (preview_h > 0) {
                var $preview_container = $('#preview-body');
                $preview_container.css({
                    'height': preview_h + 'px',
                });
            }

            previewLoading();
            $preview_container.show();

            if (!share_enable_password) {
                startPreviewFile(file_obj, file_size);
            } else {
                shareHasPassword();
            }
        }

    }

    $(document).on('click', '.enter-share-preview-password', function () {
        run_check_pass_for_preview = true;
        $('#trigger-password-required-modal').trigger('click');
    });

});

// handle error
window.onerror = function(err_msg, url, line, pos, error){

    //send error to tracking-server
    web_tracking.send_websession_error("unhandled_error", err_msg);

};


/**
 *
 */
function deactivatePreviewPageBtnDownload()
{
    $btn_download.addClass('btn-notActive');
    $btn_download.attr('disabled', true);
}

/**
 *
 */
function activatePreviewPageBtnDownload()
{
    $btn_download.removeClass('btn-notActive');
    $btn_download.removeAttr('disabled');
}

/********* COMMON PART *********/
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
    if (typeof(RTCPeerConnection) !== "function") {
        return false;
    }

    return true;
}

/**
 * форматирование размера
 * @param {int} size
 * @returns {string}
 */
function human_readable_size(size)
{
    /*
     * Converts number of bytes to human readable view,
     * e.g. 53889710 -> "51.39 MBytes"
     */
    size = +size ;
    if (size >=0 && size < 1000) {
        return size + ' Bytes';
    } else if ( size >= 1000 && size < 1000000) {
        return (size/1024).toFixed(2) + ' KBytes';
    } else if (size >= 1000000 && size < 1000000000) {
        return (size/(1024*1024)).toFixed(2) + ' MBytes';
    } else if (size >= 1000000000){
        return (size/(1024*1024*1024)).toFixed(2) + ' GBytes';
    } else {
        return NaN;
    }
    //return size;
}

/**
 *
 */
function start_download_by_app()
{
    $btn_download_by_app.removeClass('btn-notActive');
    $btn_download_by_app.removeAttr('disabled');
    console_log('in function <<start_download_by_app>>');
    var $download_anchor = $('#download-anchor-app');
    if ($download_anchor.length) {
        $download_anchor.attr('href', app_node_url);
        $download_anchor.attr('data-file-name', file_name);
        $download_anchor.attr('data-file-size', file_size);
        $download_anchor.attr('download', file_name);
        $download_anchor.html(file_name);
        setTimeout(function() {
            //$download_anchor.trigger( "click" );
            $download_anchor[0].click();
        }, 200);
    }
}

/**
 *
 */
function start_download_by_proxy()
{
    $btn_download.removeClass('btn-notActive');
    $btn_download.removeAttr('disabled');

    console_log('in function <<start_download_by_proxy>>');
    web_tracking.send_webdownload_redirect(check_browser_for_webrtc());

    $total_progress_download_rtc.hide();

    var $download_anchor = $('#download-anchor-proxy');
    if ($download_anchor.length) {
        $download_anchor.attr('href', proxy_node_url);
        $download_anchor.attr('data-file-name', file_name);
        $download_anchor.attr('data-file-size', file_size);
        $download_anchor.attr('download', file_name);
        $download_anchor.html(file_name);
        setTimeout(function() {
            //$download_anchor.trigger( "click" );
            $download_anchor[0].click();
        }, 200);
    }

    $('#click-here-for-manually').attr('href', proxy_node_url);
    $('.label-result').hide();
    $('.label-download-should-start').show();
}

/**
 * @param {string} url
 */
function start_download_by_webrtc(url)
{
    console_log('in function <<start_download_by_webrtc>>');
    var $download_anchor = $('#download-anchor');
    if ($download_anchor.length) {
        $download_anchor.attr('href', url);
        $download_anchor.attr('data-file-name', file_name);
        $download_anchor.attr('data-file-size', file_size);
        $download_anchor.attr('download', file_name);
        $download_anchor.html(file_name);
        setTimeout(function() {
            //$download_anchor.trigger( "click" );
            $download_anchor[0].click();
        }, 200);
    }
    $('.label-result').hide();
    $('.label-downloaded-successfully').show();
}

/**
 * @param message
 * @param error
 * @param repeat_
 */
function show_alert_message(message, error, repeat_){
    var repeat = repeat_ || true;
    var msg = "" + message;
    if (error !== undefined){
        if (error.name !== undefined){
            msg += error.name + ". ";
        }
        if (error.message !== undefined){
            msg += error.message + ". ";
        } else {
            msg += error;
        }
    }
    // to avoid the same error message
    if (errors.length != 0 && errors[errors.length-1] == msg) {

    } else {
        if (repeat == false){
            errors.push(msg);
        }
        if (warnings.length > 0){
            //msg += "\nRecent warnings:";
            //for (var i=0; i<warnings.length; i++){
            //    msg += "\n- " + warnings[i];
            //}
        }
        alert(msg);
    }
}

/********** RTC PART ***********/
/**
 *
 */
function startDownloadShare()
{
    var download_task;
    var transport;

    /**/
    $total_progress_download_rtc.hide();

    /**
     * @returns {boolean}
     */
    var check_already_downloaded_by_preview = function()
    {
        var anchor = document.getElementById(event_uuid);
        if (anchor !== null) {
            console_log('Already downloaded by preview. Immediately give the link.');

            //$total_progress_download_rtc.remove();
            $total_progress_download_rtc.hide();
            setTimeout(function() {
                $total_progress_download_rtc.hide();
                anchor.click();
            }, 200);

            return true;
        }
        return false;
    };

    /**
     * @returns {boolean}
     */
    var check_already_downloaded = function()
    {
        console_log('in function <<check_already_downloaded>>');
        // maybe file already downloaded
        var $download_anchor = $('#download-anchor');
        if ($download_anchor.length &&
            $download_anchor.attr('href') &&
            $download_anchor.attr('href').length &&
            download_task.get_state() == Download_Task.State.FINISHED)
        {
            //$download_anchor.trigger( "click" );
            setTimeout(function() {
                //$download_anchor.trigger( "click" );
                $download_anchor[0].click();
            }, 200);
            return true;
        }
        return false;
    };

    /**
     * @returns {boolean}
     */
    var checkPass = function()
    {
        $('#group-passrequired-password').removeClass('has-error');
        $('#group-passrequired-password .help-block-error').addClass('hidden');
        var pass = $('#passrequired-password').val();
        try_pass++;
        console_log('try=' + try_pass);
        if (jQuery.trim(pass) == "") {
            $('#group-passrequired-password').addClass('has-error');
            $('#group-passrequired-password #passrequired-blank-password').removeClass('hidden');
            return false;
        }
        auth_check(current_download_mode, Base64.encodeURI(pass));
    };

    // check password if clicking on #btn-enter-password
    $('#btn-enter-password').on('click', function () {
        checkPass();
    });

    /**
     * @param download_mode
     * @param passwd
     */
    var auth_check = function(download_mode, passwd)
    {
        console_log('in function <<auth_check>>');

        passwd = passwd || "";

        var url = connection_string_orig.replace("wss://", "https://");
        url = url.replace("ws://", "http://");
        if (passwd) {
            url += "?passwd=" + passwd;
        }

        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.send();

        xhr.onreadystatechange = function()
        {
            console_log("xhr.readyState:", this.readyState);
            if (this.readyState != 4) return;

            console_log(this.status);
            console_log(this.responseText);

            var errcode = 'UNKNOWN';
            if (this.status != 400) {
                try {
                    var responseJson = jQuery.parseJSON(this.responseText);
                    if (typeof responseJson == 'object' && "errcode" in responseJson) {
                        errcode = responseJson.errcode;
                    }
                } catch (e) {}
            }

            //console_log(typeof responseJson);
            if (this.status == 400) {
                if (passwd) {
                    connection_string = connection_string_orig + "?passwd=" + passwd;
                    proxy_node_url = proxy_node_url_orig + "?passwd=" + passwd;
                    app_node_url = app_node_url + "?passwd=" + passwd;
                    $('#wss-data').attr('data-signal-url', connection_string);
                    $('#wss-data').attr('data-proxy-url', proxy_node_url);
                }

                /* hide password-form */
                $.fancybox.close();

                if (run_check_pass_for_preview) {

                    previewLoading();

                    var file_obj = {
                        "file_name": file_name,
                        "file_size": file_size,
                        "last_event_uuid": event_uuid
                    };
                    startPreviewFile(file_obj, file_size);

                } else {
                    if (download_mode == DOWNLOAD_MODE.WEBRTC) {

                        $total_progress_download_rtc.show();
                        for (var i = 0; i < NUM_OF_SIGNAL_CONNECTIONS; i++) {
                            transport.open_signal_connection(connection_string);
                        }

                    } else if (download_mode == DOWNLOAD_MODE.PROXY) {
                        start_download_by_proxy();
                    } else if (download_mode == DOWNLOAD_MODE.APP) {
                        start_download_by_app();
                    }
                }
            } else if (this.status == 403 && errcode == "SHARE_WRONG_PASSWORD") {
                current_download_mode = download_mode;

                /* show password-form */
                $('#trigger-password-required-modal').trigger('click');

                if (try_pass > 0) {
                    $('#group-passrequired-password').addClass('has-error');
                    $('#group-passrequired-password #passrequired-wrong-password').removeClass('hidden');
                }
            } else if (this.status == 403 && errcode == "LOCKED_CAUSE_TOO_MANY_BAD_LOGIN") {
                current_download_mode = download_mode;

                /* show password-form */
                $('#trigger-password-required-modal').trigger('click');

                if (try_pass > 0) {
                    $('#group-passrequired-password').addClass('has-error');
                    var $err_container = $('#group-passrequired-password #passrequired-block-ip-tries');
                    $err_container.removeClass('hidden');
                    if ("info" in responseJson) {
                        $err_container.html(responseJson.info);
                    }
                }
            } else if (this.status == 403 && errcode == "SHARE_NOT_FOUND") {
                alert("Share unavailable. Perhaps access was closed or expired.");
            } else {
                alert("Cannot connect to signalling server. Error: " + this.status + ": " + errcode);
            }
        }
    };

    /**
     *
     */
    var download_by_proxy = function()
    {
        console_log('in function <<download_by_proxy>>');

        $btn_download.addClass('btn-notActive');
        $btn_download.attr('disabled', true);

        console_log('start download by proxy-node');
        _is_started = true;

        //disable button in three second
        $btn_download.attr('disabled', true);
        setTimeout(function() {
            $btn_download.removeAttr('disabled');
        }, 3000);

        auth_check(DOWNLOAD_MODE.PROXY, "");
    };

    /**
     *
     */
    var download_by_app = function()
    {
        $btn_download_by_app.addClass('btn-notActive');
        $btn_download_by_app.attr('disabled', true);
        auth_check(DOWNLOAD_MODE.APP, "");
    }

    /**
     *
     */
    var manage_download_task = function()
    {
        console_log('in function <<manage_download_task>>');
        var state = download_task.get_state();

        switch(state)
        {
            case Download_Task.State.WAITING:
            case Download_Task.State.FINISHED:
                /*
                 * Start downloading process
                 */

                _is_started = true;

                if (check_already_downloaded_by_preview()) {
                    $total_progress_download_rtc.remove();
                    download_task.cancel();
                    //download_task = null;
                    //transport = null;
                    //file_writer = null;
                    return;
                }

                if (check_already_downloaded()) {
                    return;
                }

                if (transport.get_signal_connections_num() == 0)
                {
                    pending_transport = true;
                    auth_check(DOWNLOAD_MODE.WEBRTC, "");
                    return;
                }

                //$('.label-result').hide();
                //$('.label-starting-download').show();

                //$('#wait_progress').show();
                //$('#start_progress').hide();

                web_tracking.send_webdownload_start(true);

                download_task.start();
                break;

            case Download_Task.State.INPROGRESS:
                download_task.pause();
                break;

            case Download_Task.State.PAUSED:
                download_task.resume();
                break;

            default:
                break;
        }
    };

    // download by app if clicking on it button
    $btn_download_by_app.on('click', function () {
        download_by_app();
    });

    // workaround for iOS, because browsers in the system cannot save Blob to file
    var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    if (iOS == true) {
        $btn_download.on('click', function () {
            run_check_pass_for_preview = false;
            download_by_proxy();
        });
        console_log('Cause  <<workaround for iOS>>');
        return;
    }

    // unfortunately the workaround for Firefox was added 26.08.2018,
    //   because there is issue with saving Blob to file
    //var isFirefox = typeof navigator.mozGetUserMedia !== 'undefined';
    //if (isFirefox == true) {
    //    $btn_download.on('click', function () {
    //        run_check_pass_for_preview = false;
    //        download_by_proxy();
    //    });
    //    console_log('Cause  <<isFirefox>>');
    //    return;
    //}

    /* no support websocket */
    if (!check_browser_for_websocket()) {
        $btn_download.on('click', function () {
            run_check_pass_for_preview = false;
            download_by_proxy();
        });
        console_log($("#message-info-Websocket").html());
        console_log('Cause  <<no support websocket>>');
        return;
    }

    /* no support webrtc */
    if (!check_browser_for_webrtc()) {

        $btn_download.on('click', function () {
            run_check_pass_for_preview = false;
            download_by_proxy()
        });
        console_log($("#message-info-WebRTC").html());
        web_tracking.send_websession_start(false);
        console_log('Cause  <<no support webrtc>>');
        return;
    }


    /* transport setup */
    transport = new Transport(ice_servers, NUM_OF_CHANNELS);
    transport.cb_error = function (error) {
        //show_alert_message("Transport error. ", error, false);
        download_by_proxy();
    };
    transport.cb_state_changed = function transport_state_changed(state_info) {
        console_log("state_info: ", state_info);
        var s = "";
        if(state_info["num_of_signal_connections"] >= 0){
            s += "Signal connections: ";
            s += state_info["num_of_signal_connections"];
        }
        if(state_info["channels"]){
            var p2p_count = 0;
            var turn_count = 0;
            var unknown_count = 0;
            for (var channel_id in state_info["channels"]){
                if (state_info["channels"][channel_id] == "P2P") {
                    p2p_count++;
                } else if (state_info["channels"][channel_id] == "TURN") {
                    turn_count++;
                } else {
                    unknown_count++;
                }
            }
            if(p2p_count) { s += "; P2P channels: " + p2p_count; }
            if(turn_count) { s += "; TURN channels: " + turn_count; }
            if(unknown_count) { s += "; UNKNOWN channels: " + unknown_count; }
        }
        $('#label-state').html( s );
    };
    transport.cb_share_info = function (file_name, file_size, share_hash, event_uuid) {
        console.debug("share info:", file_name, file_size, share_hash, event_uuid);
        web_tracking.send_webdownload_fileinfo(file_size, event_uuid, share_hash);

        if (pending_transport == true) {
            pending_transport = false;
            manage_download_task();  //will start downloading
        }
    };


    /* file_writer setup */
    var mime_type = MimeTypes.lookup(file_name) || "application/octet-stream";
    var File_Writer = get_appropriate_writer(file_size);
    var file_writer = new File_Writer({file_size: file_size, mime_type: mime_type});

    /* download_task setup */
    download_task = new Download_Task(
        transport,
        file_writer,
        event_uuid,
        file_name,
        file_size,
        {
            chunk_size: CHUNK_SIZE,
            first_chunk_size: FIRST_CHUNK_SIZE,
            chunk_multiplier: CHUNK_MULTIPLIER,
            task_timeout: DOWNLOAD_TASK_TIMEOUT,
            chunk_timeout: DOWNLOAD_CHUNK_TIMEOUT,
            keep_signal_connections: true,
            channel_opening_timeout: DOWNLOAD_CHANNEL_OPENING_TIMEOUT,
            availability_info_timeout: DOWNLOAD_AVAILABILITY_INFO_TIMEOUT,
            data_receiving_check_interval: DATA_RECEIVING_CHECK_INTERVAL,
            max_chunks_in_buffer: MAX_CHUNKS_IN_BUFFER
        },
        {
            cb_task_success: function (url) {
                console_log("download task success, url:" + url);
                web_tracking.send_webdownload_end(_rx, _duration);
                start_download_by_webrtc(url);
            },

            cb_task_failure: function (error) {
                web_tracking.send_webdownload_end(_rx, _duration, error.name, error.message);

                if (error
                    && error.code
                    && error.code == Download_Task.ErrorCode.CHANNEL_OPENING_TIMEOUT
                    && transport.get_online_nodes().length > 0)
                {
                    console_log("Switch to fallback mode (download by proxy-node)");
                    download_by_proxy();
                }
                else if (error
                    && error.code
                    && error.code == Download_Task.ErrorCode.TASK_CANCELED)
                {
                    //suppress error 'TASK_CANCELED'
                }
                else
                {
                    $('.label-result').hide();
                    $('.label-download-failed').show();
                    show_alert_message("Download task failure. ", error, false);
                    console_log("Recent warnings: ", warnings);
                }
            },

            cb_warning: function (warning) {
                if (!(warnings.length != 0 && warnings[warnings.length-1] == warning)){
                    warnings.push(warning);
                }
            },

            cb_task_progress: function (progress_info) {
                _rx = progress_info.received_size;  // for web_tracking
                _duration = (progress_info.duration/1000).toFixed(2);  // for web_tracking+

                var status = download_task.get_state();
                var recv_size = progress_info.received_size;
                var effective_received_size = progress_info.effective_received_size;
                var recv_time = progress_info.duration;
                var total_time = progress_info.total_duration;

                if (recv_size >= file_size) {
                    status = Download_Task.State.FINISHED;
                }

                var totla_downloaded = file_size_format(recv_size, 1);
                var total_size = file_size_format(file_size, 1);
                var percent = recv_size * 100 / file_size;
                var speed = recv_size/(recv_time/1000);

                if (status == Download_Task.State.FINISHED) {
                    speed = 0;
                    percent = 100;
                }
                if (status == Download_Task.State.PAUSED) {
                    speed = 0;
                }

                speed = file_size_format(speed, 0) + 'ps';
                var pcnt = percent.toFixed(2) + '%';


                var $total_sys_info = $('#total-download-sys-info');
                $total_sys_info.attr('data-total-downloaded-size', parseInt($total_sys_info.attr('data-total-downloaded-size')) + recv_size);

                $('#total-download-file-info').html(totla_downloaded + '/' + total_size + ', ' + speed);
                $('#total-download-file-percent').css({ width: pcnt });


                // status in label during downloading
                var result = "Downloaded: " + human_readable_size(effective_received_size);
                result += " (" + effective_received_size + " Bytes), ";
                result += "time: " + (total_time/1000).toFixed(2) + " s , ";
                var average_speed = 0;
                if (status != Download_Task.State.PAUSED) {
                    average_speed = ((recv_size / 1024.0 / 1024.0) * 8 / (recv_time / 1000)).toFixed(2);
                }
                //result += "average speed: " + average_speed + " Mbit/s";
                result += "average speed: " + speed;
                console_log(result);
                //$('.label-result').hide();
                $('#label-dynamic-result').html(result).show();

                // status in label when starting
                if (recv_size == 0)
                {
                    $('.label-result').hide();
                    $('.label-downloading').show();
                }

                // status in label when already downloaded
                var $download_anchor = $('#download-anchor-app');
                if ($download_anchor.length &&
                    $download_anchor.attr('href') &&
                    $download_anchor.attr('href').length &&
                    download_task.get_state() == Download_Task.State.FINISHED)
                {
                    $('.label-result').hide();
                    $('.label-downloaded-successfully').show();
                }
            },

            cb_task_state: function (state) {

                var button_change_on_status_change = function(current_status)
                {
                    $btn_download.val($btn_download.data('btn-name-when-' + current_status));
                    $btn_download
                        .removeClass('btn-download-pause btn-download-resume btn-download-ready')
                        .addClass('btn-download-' + current_status)
                };

                switch(state) {
                    case Download_Task.State.WAITING:
                    case Download_Task.State.FINISHED:
                        button_change_on_status_change('ready');
                        $btn_download_by_app.show();
                        break;
                    case Download_Task.State.INPROGRESS:
                        button_change_on_status_change('pause');
                        $btn_download_by_app.hide();
                        break;
                    case Download_Task.State.PAUSED:
                        button_change_on_status_change('resume');
                        $btn_download_by_app.show();
                        break;
                    default:
                        break;
                }
            },

            cb_online_nodes: function (node_ids_) {
                $('#td-online-nodes').html(node_ids_.join(", "));
            }

        }
    );


    console.info(">> In main script");
    $btn_download.on('click', function () {
        run_check_pass_for_preview = false;
        manage_download_task();
    });

    // show hidden elements
    web_tracking.send_websession_start(true);

}