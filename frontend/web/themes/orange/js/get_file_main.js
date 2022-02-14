"use strict";


var start_download_task = function ()
{
    if (!check_state())
        return;
    if (transport.get_signal_connections_num() == 0)
    {
        pending_transport = true;
        ss_connect();
        return;
    }

    web_tracking.send_webdownload_start(true);

    download_task.start();
}


/* Transport settings =======================================================*/

var transport = new Transport(ice_servers, NUM_OF_CHANNELS);


transport.cb_error = function(error){
    //show_alert_message("Transport error. ", error, false);
    if (preview_mode)
        preview_by_proxy_node();
    else
        download_by_proxy_node();
}


transport.cb_state_changed = function(state_info){
    console_log("state_info: ", state_info);
    var s = "";
    if(state_info["num_of_signal_connections"] >= 0){
        //s += "State: number of signal connections: ";
        s += "Signal connections: ";
        s += state_info["num_of_signal_connections"];
    }
    if(state_info["channels"]){
        var p2p_count = 0;
        var turn_count = 0;
        var unknown_count = 0;
        for (var channel_id in state_info["channels"]){
            //s += "id: " + channel_id;
            //s += ", connection type: " + state_info["channels"][channel_id] + "; ";
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
    label_state.textContent = s;
}


/* Download Task settings ====================================================*/

var download_task_online_nodes = function(node_ids_)
{
    var node_ids = node_ids_;
    out_online_nodes.textContent = node_ids.join(", ");

    if (pending_transport)
    {
        pending_transport = false;
        start_download_task();
    }
}


var download_task_progress = function (progress_info)
{
    var recv_size = progress_info.received_size;
    var effective_received_size = progress_info.effective_received_size;
    _rx = progress_info.received_size;  // for web_tracking
    var recv_time = progress_info.duration;
    _duration = (progress_info.duration/1000).toFixed(2);  // for web_tracking
    var result = "received: " + human_readable_size(effective_received_size);
    result += " (" + effective_received_size + " Bytes), ";
    result += "time: " + (recv_time/1000).toFixed(2) + " s , ";
    result += "average speed: " +
        ((recv_size/1024.0/1024.0)*8/(recv_time/1000)).toFixed(2) +
        " Mbit/s";
    label_result.textContent = result;
    progress.value = effective_received_size;
    //
    $('#progress_').css(
        'width',
        parseInt(effective_received_size/file_size*100) + '%'
    );
}


var download_task_warning = function (warning)
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


var download_task_failure = function (error)
{
    web_tracking.send_webdownload_end(_rx, _duration, error.name, error.message);

    if (error
        && error.code
        && error.code == Download_Task.ErrorCode.CHANNEL_OPENING_TIMEOUT)
    {
        if (preview_mode)
        {
            console_log("Switch to fallback mode (preview by proxy-node)");
            preview_by_proxy_node();
        }
        else
        {
            console_log("Switch to fallback mode (download by proxy-node)");
            download_by_proxy_node();
        }
    }
    else if (error
        && error.code
        && error.code == Download_Task.ErrorCode.TASK_CANCELED)
    {
        ;  //suppress error 'TASK_CANCELED'
    }
    else
    {
        //show_alert_message("Download task failure. ", error, false);
        if (typeof parent != 'undefined' && typeof parent.closeDownloadIframe != 'undefined') {
            var message = "Download task failure.";
            if (preview_mode) {
                message = "Preview task failure.";
            }
            if (error.name == "FILE_WRITER_ERROR"/*"NO_AVAILABLE_NODES"*/) {
                failPreview();
                is_restart_task_onerror = true;
                download_task = null;
                download_task = createDownloadTask();
                start_download(false);
            } else {
                parent.closeDownloadIframe(message + " " + error.message);
                parent.closePreviewIframe();
            }
        }
        console_log(warnings);
    }
}


var download_task = createDownloadTask();
console_log(download_task);

function createDownloadTask()
{
    return new Download_Task(
        transport,
        null,  // file_writer will be assigned later
        event_uuid,
        file_name,
        file_size,
        {
            chunk_size: CHUNK_SIZE,
            first_chunk_size: FIRST_CHUNK_SIZE,
            chunk_multiplier: CHUNK_MULTIPLIER,
            task_timeout: DOWNLOAD_TASK_TIMEOUT,
            chunk_timeout: DOWNLOAD_CHUNK_TIMEOUT,
            // timer_interval: timer_interval,
            // range: null;
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
            cb_online_nodes: download_task_online_nodes
        }
    );
}
/* ===========================================================================*/

function check_state()
{
    // maybe file already downloaded
    var download_anchor = document.getElementById("download-from-node");
    if (download_anchor.href
        && download_task.get_state() == Download_Task.State.FINISHED)
    {
        make_save_href(download_anchor.href);
        return false;
    }

    return true;
}


function start_download(preview_mode_)
{
    /*
     * Start downloading process
     */

    preview_mode = preview_mode_ == true ? true : false;
    //console_log(preview_mode);
    if (!check_state()) return;

    var mime_type = MimeTypes.lookup(file_name) || "application/octet-stream";
    var File_Writer = get_appropriate_writer(file_size);
    var file_writer = new File_Writer({file_size: file_size, mime_type: mime_type});
    console_log(file_writer);

    download_task.set_file_writer(file_writer);
    download_task.set_max_chunks_in_buffer(MAX_CHUNKS_IN_BUFFER);

    //download_task.start(download_task_success, download_task_failure);
    start_download_task();
}


function start_preview()
{
    /*
     * Start downloading process
     */

    preview_mode = true;

    if (!check_state()) return;

    var media_type = get_media_type(file_name);
    var media_element = get_media_element(media_type, false);
    if (!media_element)
    {
        start_download(preview_mode);
        return;
    }

    setTimeout(function() {
        document.getElementById("media_").innerHTML = '';
        document.getElementById("media_").appendChild(media_element);
        if (typeof changeSizeOfContainer == 'function') { changeSizeOfContainer(media_type); }
    }, 500);

    //media_element.autoplay = true;
    // media_element.hidden = false; // it will be show by MediaSource_Writer
    //media_element.type = media_type;

    // events callback
    //media_element.addEventListener("pause", function() { download_task.pause(); });
    //media_element.addEventListener("play", function() { download_task.resume(); });

    /* workaround for play mp3, ogg by full download */
    if (media_type == "audio/mpeg" || media_type == "audio/ogg")
    {
        if (!window.MediaSource.isTypeSupported(media_type))
        {
            start_download(preview_mode);
            return;
        }
    }

    var file_writer = new MediaSource_Writer({
        file_size: file_size,
        media_type: media_type,
        media_element: media_element
    });
    console_log(file_writer);

    download_task.set_file_writer(file_writer);
    download_task.set_max_chunks_in_buffer(MAX_CHUNKS_IN_BUFFER_FOR_PREVIEW);

    //download_task.start(download_task_success, download_task_failure);
    start_download_task();
}


var make_save_href = function (url)
{
    if (!url) return;

    var download_anchor = document.getElementById("download-from-node");
    download_anchor.hidden = true;
    download_anchor.href = url;

    if (is_restart_task_onerror) {
        if (typeof showDownloadButton == 'function') {
            showDownloadButton(false);
        }
        return;
    }

    /* workaround for play mp3, ogg by full download .. and other type if downloaded */
    var media_type = get_media_type(file_name);
    var media_element = get_media_element(media_type, url);
    if (preview_mode 
        && media_element 
        /*&&(media_type == "audio/mpeg" || media_type == "audio/ogg")*/)
    {
        //media_element.autoplay = true;
        //media_element.hidden = false;
        //media_element.type = media_type;
        //media_element.src = url;
        setTimeout(function() {
            document.getElementById("media_").innerHTML = '';
            document.getElementById("media_").appendChild(media_element);
            if (typeof changeSizeOfContainer == 'function') { changeSizeOfContainer(media_type); }
        }, 500);
        return;
    }

    /* workaround for images */
    var mime_type = MimeTypes.lookup(file_name);
    if (preview_mode && mime_type && mime_type.match(/image/))
    {
        var img = document.getElementById("img_") || document.createElement("img");
        img.id = "img_";
        img.src = url;
        img.hidden = false;
        setTimeout(function() {
            document.getElementById("media_").innerHTML = '';
            document.getElementById("media_").appendChild(img);
            if (typeof changeSizeOfContainer == 'function') { changeSizeOfContainer('img'); }
        }, 500);
        return;
    }

    /* workaround for text */
    if (preview_mode && mime_type && mime_type.match(/text/))
    {
        preview_text(url);
        return;
    }

    /* workaround for pdf */
    if (preview_mode && mime_type && mime_type.match(/pdf/))
    {
        preview_pdf(url);
        return;
    }

    preview_mode = false;
    if (!preview_mode)
    {
        download_anchor.download = file_name;
    }

    if (typeof noPreview == 'function') { noPreview(); }
    //download_anchor.textContent = "Click to open or save";
    download_anchor.type = MimeTypes.lookup(file_name) || "application/octet-stream";
    console_log(download_anchor);
    //download_anchor.click();

    if (typeof showDownloadButton == 'function') {
        showDownloadButton(false);
    } else {
        if (typeof noPreview == 'function') { noPreview(); }
        download_anchor.click();
        if (typeof parent != 'undefined' && typeof parent.closeDownloadIframe != 'undefined') {
            parent.closeDownloadIframe();
        }
    }
}


var on_load_scripts = function()
{
    console.info(">> In main script");
    window.btn_download.onclick = start_download;
    window.btn_preview.onclick = start_preview;

    ss_connect();
}


var ss_connect = function()
{
    for (var i=0; i < NUM_OF_SIGNAL_CONNECTIONS; i++){
        transport.open_signal_connection(connection_string);
    }
}


var pending_transport = false;
var _rx = 0;
var _duration = 0.0;
var is_restart_task_onerror = false;
