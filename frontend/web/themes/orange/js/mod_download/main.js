"use strict";

var progres_interval;
function progress_wait()
{
    var progress_direction = '+';
    var progress_width = $('#wait_progress').width();
    //console_log(progress_width);
    var $progress_bar = $('#wait_progress .progress-bar');
    var progress_wait_width = parseInt(progress_width/3);
    $progress_bar.css({
        'left'  : '0px',
        'width' : progress_wait_width + 'px',
    });

    progres_interval = setInterval(function() {

        var progress_bar_left = parseFloat($progress_bar.css('left')+'');
        if (progress_bar_left >= progress_width - progress_wait_width && progress_direction == '+') {
            progress_direction = '-';
        }
        if (progress_bar_left <= 0 && progress_direction == '-') {
            progress_direction = '+';
        }
        $progress_bar.css({
            'left'  : progress_direction + '=3',
            //'width' : progress_wait_width + 'px',
        });

    }, 5);
}

$(document).ready(function () {
    progress_wait();
});

var manage_download_task = function ()
{
    var state = download_task.get_state();

    switch(state)
    {
        case Download_Task.State.WAITING:
        case Download_Task.State.FINISHED:
            /*
             * Start downloading process
             */

             _is_started = true;

            if (!check_state()) return;

            if (transport.get_signal_connections_num() == 0)
            {
                pending_transport = true;
                auth_check(DOWNLOAD_MODE.WEBRTC, "");
                return;
            }

            label_result.textContent = "Starting download ...";
            $('#wait_progress').show();
            $('#start_progress').hide();
            $('#p_progress').show();

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
            ;
    }
}


var make_save_href =function (url)
{
    var download_anchor = document.getElementById("download");
    download_anchor.hidden = true;
    download_anchor.href = url;
    download_anchor.download = file_name;
    download_anchor.textContent = "Click to open or save";
    download_anchor.click();
    label_result.textContent = "Downloaded successfully!";
}


var check_state = function ()
{
    // maybe file already downloaded
    var download_anchor = document.getElementById("download");
    if (download_anchor.href
        && download_task.get_state() == Download_Task.State.FINISHED)
    {
        make_save_href(download_anchor.href);
        return false;
    }
    return true;
}


var transport_error = function (error)
{
    //show_alert_message("Transport error. ", error, false);
    download_by_proxy();
}


var transport_state_changed = function (state_info)
{
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
        if(p2p_count) s += "; P2P channels: " + p2p_count;
        if(turn_count) s += "; TURN channels: " + turn_count;
        if(unknown_count) s += "; UNKNOWN channels: " + unknown_count;
    }
    //label_state.textContent = s;
}


var transport_share_info = function (file_name, file_size, share_hash, event_uuid)
{
    console.debug("share info:", file_name, file_size, share_hash, event_uuid);
    web_tracking.send_webdownload_fileinfo(file_size, event_uuid, share_hash);

    if (pending_transport == true)
    {
        pending_transport = false;
        manage_download_task();  //will start downloading
    }
}


var download_task_online_nodes = function (node_ids_)
{
    var node_ids = node_ids_;
    out_online_nodes.textContent = node_ids.join(", ");
}


var download_task_progress = function (progress_info)
{
    var recv_size = progress_info.received_size;
    var effective_received_size = progress_info.effective_received_size;
    _rx = progress_info.received_size;  // for web_tracking
    if (recv_size == 0)
    {
        label_result.textContent = "Downloading ...";
        return;
    }
    var recv_time = progress_info.duration;
    _duration = (progress_info.duration/1000).toFixed(2);  // for web_tracking
    var total_time = progress_info.total_duration;
    var result = "Downloaded: " + human_readable_size(effective_received_size);
    result += " (" + effective_received_size + " Bytes), ";
    result += "time: " + (total_time/1000).toFixed(2) + " s , ";
    var average_speed = 0;
    if (download_task.get_state() != Download_Task.State.PAUSED)
        average_speed = ((recv_size/1024.0/1024.0)*8/(recv_time/1000)).toFixed(2);
    result += "average speed: " + average_speed + " Mbit/s";
    label_result.textContent = result;
    progress.value = effective_received_size;
    //console_log('recive_size: ' + recv_size + ' -- ' + file_size);
    clearInterval(progres_interval);
    $('#progress_').css({
        'left': '0px',
        'width': parseInt(effective_received_size / file_size * 100) + '%',
    }).show();
    $('#wait_progress').hide();
    $('#start_progress').show();
    $('#p_progress').show();

    // if already downloaded
    var download_anchor = document.getElementById("download");
    if (download_anchor.href
        && download_task.get_state() == Download_Task.State.FINISHED)
    {
        label_result.textContent = "Downloaded successfully!";
    }
}


var download_task_warning = function(warning)
{
    if (warnings.length != 0 && warnings[warnings.length-1] == warning){
        ;
    } else {
        warnings.push(warning);
    }
}


var download_task_success = function (url)
{
    console.info("download task success, url:", url);
    web_tracking.send_webdownload_end(_rx, _duration);

    make_save_href(url);
}


function download_task_failure(error)
{
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
        ;  //suppress error 'TASK_CANCELED'
    }
    else
    {
        label_result.textContent = "Download failed.";
        show_alert_message("Download task failure. ", error, false);
        console_log("Recent warnings: ", warnings);
    }
}


function download_task_state(state)
{
    var button_text = function(text)
    {
        if (btn_download.value)
            btn_download.value = text;
        else
            btn_download.innerHTML = text;
    }

    var button_color = function(color_)
    {
        var color = color_ || btn_download_by_app.style.backgroundColor;
        btn_download.style.backgroundColor = color;
    }

    switch(state)
    {
        case Download_Task.State.WAITING:
        case Download_Task.State.FINISHED:
            button_text("Download");
            //btn_download.className = 'btn-default';
            button_color();
            btn_download_by_app.style.visibility = "visible";
            break;
        case Download_Task.State.INPROGRESS:
            button_text("Pause");
            //btn_download.className = 'btn-default.cancel';
            button_color("#7F7F7F");
            btn_download_by_app.style.visibility = "hidden";
            break;
        case Download_Task.State.PAUSED:
            button_text("Resume");
            //btn_download.className = 'btn-default';
            button_color();
            btn_download_by_app.style.visibility = "visible";
            break;
        default:
            ;
    }
}


var on_load_scripts = function()
{
    console.info(">> In main script");
    btn_download.onclick = manage_download_task;

    // workaround for iOS, because browsers in the system cannot save Blob to file
    var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    if (iOS == true)
        btn_download.onclick = download_by_proxy;

    // unfortunately the workaround for Firefox was added 26.08.2018,
    //   because there is issue with saving Blob to file
    // Addition on 03.10.2019:
    //   the issue was fixed in Filefox release 60.2.0esr
    var isFirefox = typeof navigator.mozGetUserMedia !== 'undefined';
    //if (isFirefox == true)
    //    btn_download.onclick = download_by_proxy;
}


var ss_connect = function()
{
    for (var i=0; i < NUM_OF_SIGNAL_CONNECTIONS; i++){
        transport.open_signal_connection(connection_string);
    }
}


/* transport setup */
var transport = new Transport(ice_servers, NUM_OF_CHANNELS);
transport.cb_error = transport_error;
transport.cb_state_changed = transport_state_changed;
transport.cb_share_info = transport_share_info;


/* file_writer setup */
var mime_type = MimeTypes.lookup(file_name) || "application/octet-stream";
var File_Writer = get_appropriate_writer(file_size);
var file_writer = new File_Writer({file_size: file_size, mime_type: mime_type});


/* download_task setup */
var download_task = new Download_Task(
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
        cb_task_success: download_task_success,
        cb_task_failure: download_task_failure,
        cb_warning: download_task_warning,
        cb_task_progress: download_task_progress,
        cb_task_state: download_task_state,
        cb_online_nodes: download_task_online_nodes
    }
);

var pending_transport = false;
var _rx = 0;
var _duration = 0.0;
