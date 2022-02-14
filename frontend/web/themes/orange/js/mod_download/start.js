"use strict";


var check_browser_for_websocket = function()
{
    var websocket = window.WebSocket;
    if (websocket === undefined) {
        return false;
    }
    return true;
}

var check_browser_for_webrtc = function()
{
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

var human_readable_size = function(size) {
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

var getText = function(el) {
    // for IE8 compat
    return el.textContent || el.innerText;
}

var setText = function(el, text) {
    // for IE8 compat
    if(typeof(el.textContent) === "undefined") {
        el.innerText = text;
    } else {
        el.textContent = text;
    }
}

var start_download_by_app = function(){
    var href = app_node_url;
    var a = document.getElementById("download_by_app");
    a.href = href;
    //console_log(a);
    if (a.click === undefined) {
        window.location.href = href;
    } else {
        a.click();
    }
}

var download_by_app = function() {
    //cancel download_task if any
    if (window.download_task)
        window.download_task.cancel();

    console_log('start download by app');
    auth_check(DOWNLOAD_MODE.APP, "");
}


var start_download_by_proxy = function()
{
    web_tracking.send_webdownload_redirect(check_browser_for_webrtc());

    progress.hidden =true;

    setText(label_result, "Download should start automatically ");

    var href2 = document.getElementById("_href2") || document.createElement("a");
    href2.id = "_href2";
    href2.href = proxy_node_url;
    setText(href2, "click here");

    var label2 = document.getElementById("_label2") || document.createElement("label");
    label2.id = "_label2";
    setText(label2, "to download manually");

    var p_result = document.getElementById("p_result");
    p_result.appendChild(href2);
    p_result.appendChild(label2);

    var href = proxy_node_url;
    var a = document.getElementById("download_by_proxy");
    a.href = href;
    //console_log(a);
    if (a.click === undefined) {
        window.location.href = href;
    } else {
        a.click();
    }
}


var download_by_proxy = function() {
    console_log('start download by proxy-node');
    _is_started = true;

    //disable button in three second
    btn_download.disabled = true;
    setTimeout(function(){btn_download.disabled = false;}, 3000);

    auth_check(DOWNLOAD_MODE.PROXY, "");
}


var dummy = function(){
    ;
}


var load_scripts = function(scripts){
    for (var i=0; i < scripts.length; i++){
        var script = document.createElement('script');
        script.src = scripts[i];
        script.async = false; // to order
        if (i == scripts.length-1){
            if (script.readyState) {
                script.onreadystatechange = function(){
                    if (this.readyState == "loaded" || this.readyState == "complete"){
                        on_load_scripts();
                    }
                }
            } else {
                script.onload = function(){
                    on_load_scripts();
                }
            }
        }
        document.body.appendChild(script);
    }
}

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
    if (errors.length != 0 && errors[errors.length-1] == msg){
        ;
    } else {
        if (repeat == false){
            errors.push(msg);
        }
        if (warnings.length > 0){
            //msg += "\nRecent warnings:";
            //for (var i=0; i<warnings.length; i++){
            //    msg += "\n- " + warnings[i];
            //}
            ;
        }
        alert(msg);
    }
}

// handle error
window.onerror = function(err_msg, url, line, pos, error){
    if (download_task){
        download_task.cancel();
    }
    //console.error(err_msg, url, line, pos, error);
    //show_alert_message("Unhandled error. ", err_msg, false);

    //send error to tracking-server
    web_tracking.send_websession_error("unhandled_error", err_msg);

    if (_is_started == true && _attempt_to_proxy == false)
    {
        _attempt_to_proxy = true;
        download_by_proxy();
    }
}


var try_pass = 0;
var current_download_mode = 0;
var checkPass = function()
{
    $('#group-passrequired-password').removeClass('has-error');
    $('#group-passrequired-password .help-block-error').addClass('hide');
    var pass = $('#passrequired-password').val();
    try_pass++;
    console_log('try=' + try_pass);
    if (jQuery.trim(pass) == "") {
        $('#group-passrequired-password').addClass('has-error');
        $('#group-passrequired-password #passrequired-blank-password').removeClass('hide');
        return false;
    }
    auth_check(current_download_mode, Base64.encodeURI(pass));
}

var auth_check = function(download_mode, passwd)
{
    passwd = passwd || "";

    var url = connection_string.replace("wss://", "https://");
    url = url.replace("ws://", "http://");
    if (passwd)
        url += "?passwd=" + passwd;

    console_log(url);

    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.send();

    xhr.onreadystatechange = function()
    {
        console_log("xhr.readyState:", this.readyState);
        if (this.readyState != 4) return;

        console_log(this.status);
        console_log(this.responseText);
        //console_log(this.responseType);
        //console_log(this);

        var errcode = 'UNKNOWN';
        if (this.status == 400) {

        } else {
            try {
                var responseJson = jQuery.parseJSON(this.responseText);
                if (typeof responseJson == 'object' && "errcode" in responseJson) {
                    errcode = responseJson.errcode;
                }
            } catch (e) {}
        }
        //console_log(typeof responseJson);
        if (this.status == 400)
        {
            if (passwd)
            {
                connection_string += "?passwd=" + passwd;
                proxy_node_url += "?passwd=" + passwd;
                app_node_url += "?passwd=" + passwd;
            }

            $('#password-required-modal').modal('hide');

            if (download_mode == DOWNLOAD_MODE.WEBRTC)
                ss_connect();
            else if (download_mode == DOWNLOAD_MODE.PROXY)
                start_download_by_proxy();
            else if (download_mode == DOWNLOAD_MODE.APP)
                start_download_by_app();
            else
                ;
        }
        else if (this.status == 403
                 && errcode == "SHARE_WRONG_PASSWORD")
        {
            current_download_mode = download_mode;
            $('#password-required-modal').modal({"show": true});
            if (try_pass > 0) {
                $('#group-passrequired-password').addClass('has-error');
                $('#group-passrequired-password #passrequired-wrong-password').removeClass('hide');
            }
        }
        else if (this.status == 403
            && errcode == "LOCKED_CAUSE_TOO_MANY_BAD_LOGIN")
        {
            current_download_mode = download_mode;
            $('#password-required-modal').modal({"show": true});
            if (try_pass > 0) {
                $('#group-passrequired-password').addClass('has-error');
                var $err_container = $('#group-passrequired-password #passrequired-block-ip-tries');
                $err_container.removeClass('hide');
                if ("info" in responseJson) {
                    $err_container.html(responseJson.info);
                }
            }
        }
        else if (this.status == 403
                 && errcode == "SHARE_NOT_FOUND")
        {
            alert("Share unavailable. Perhaps access was closed or expired.");
        }
        else
        {
            alert("Cannot connect to signalling server. Error: " + this.status + ": " + errcode);
        }
    }
}


var uuid = function(){return(""+1e7+-1e3+-4e3+-8e3+-1e11).replace(/1|0/g,function(){return(0|Math.random()*16).toString(16)})}


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
/* end set constants */

var DOWNLOAD_MODE = {
    WEBRTC: 1,
    PROXY: 2,
    APP: 3
}

// input parameters
var opt_share_hash = document.getElementById('share_hash');
var opt_file_name = document.getElementById('file_name');
var opt_file_size = document.getElementById('file_size');
var opt_event_uuid = document.getElementById('event_uuid');
var opt_stun_server_url = document.getElementById('stun_server_url');
var ice_servers = {"iceServers":[{"urls": "stun:" + opt_stun_server_url.value}]};
//console_log(ice_servers);
var opt_sig_server_url = document.getElementById('sig_server_url');
var opt_proxy_node_url = document.getElementById('proxy_node_url');
var opt_app_node_url = document.getElementById('app_node_url');
var opt_node_ids = document.getElementById('node_ids');
var scripts = [];
var opt_scripts = document.getElementById('scripts').options;
for (var i=0; i < opt_scripts.length; i++) {
    //console_log(opt_scripts[i].value);
    scripts.push(opt_scripts[i].value);
}

// add some required elements
var a_refresh = document.getElementById('a_refresh');
var label_result = document.getElementById('label_result');
var btn_download = document.getElementById('btn_download');
var btn_download_by_app = document.getElementById('btn_download_by_app');
var progress = document.getElementById('progress_');
var label_state = document.getElementById('label_state');

// pass some parameters to variables
var file_name = getText(opt_file_name);
var file_size = parseInt(getText(opt_file_size));
setText(opt_file_size, human_readable_size(file_size) + " (" + file_size + " Bytes)");
var share_hash = getText(opt_share_hash);
var event_uuid = getText(opt_event_uuid);
var sig_server_url = opt_sig_server_url.value;
var proxy_node_url = opt_proxy_node_url.value;
var app_node_url = opt_app_node_url.value;
var out_online_nodes = document.getElementById('online_nodes');
out_online_nodes.textContent = "n/a"
progress.max = file_size;
var p_progress = document.getElementById('p_progress');
var p_result = document.getElementById('p_result');

var errors = [];
var warnings = [];
var download_task = null;

var web_tracking = new Web_Tracking("https://tracking.pvtbox.net:443/1", 2, "browser", "webshare");
var _is_started = false;  //flag to know if user starts download by clicking on button
var _attempt_to_proxy = false;

var connection_string = sig_server_url;


if (check_browser_for_websocket()) {

    if (!check_browser_for_webrtc()) {
        btn_download.onclick = download_by_proxy;
        console_log($("#message-info-WebRTC").html());
        p_result.setAttribute("class", "");
        web_tracking.send_websession_start(false);
    } else {
        //btn_download.onclick = download_task.start;
        load_scripts(scripts);

        // show hidden elements
        p_progress.setAttribute("class", "");
        p_result.setAttribute("class", "");
        web_tracking.send_websession_start(true);
    }

} else {
    btn_download.onclick = download_by_proxy;
    console_log($("#message-info-Websocket").html());
}
