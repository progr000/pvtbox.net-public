"use strict";


var check_browser_for_websocket = function(){

    // check websocket
    var websocket = window.WebSocket;
    if (websocket === undefined) {
        return false;
    }
    return true;
}


var check_browser_for_webrtc = function(){

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


var download_by_proxy_node = function() {

    web_tracking.send_webdownload_redirect(check_browser_for_webrtc());

    /*
    var href = proxy_node_url;
    var a = document.getElementById("download_by_proxy_node");
    a.href = href;
    //console_log(a);
    if (a.click === undefined) {
        window.location.href = href;
    } else {
        a.click();
    }
    */
    var download_anchor = document.getElementById("download-from-node");
    download_anchor.href = proxy_node_url;
    if (typeof showDownloadButton == 'function') {
        showDownloadButton(true);
    } else {
        if (typeof noPreview == 'function') { noPreview(); }
        //download_by_proxy_node();
        download_anchor.click();
        //window.location.href = proxy_node_url;
        //window.open(proxy_node_url);
        if (typeof parent != 'undefined' && typeof parent.closeDownloadIframe != 'undefined') {
            parent.closeDownloadIframe();
            //parent.checkIframeContent();
        }
    }

}


var preview_by_proxy_node = function() {

    web_tracking.send_webdownload_redirect(check_browser_for_webrtc());

    preview_mode = true;
    attempt_to_use_proxy = true;

    var media_type = get_media_type(file_name);
    console_log(media_type);

    var file_ext = get_file_extension(file_name);

    /* workaround for images */
    var img_exts = ["jpg", "jpeg", "png", "bmp", "svg", "gif", "tiff", "tif"];
    if (preview_mode && img_exts.indexOf(file_ext.toLowerCase()) >= 0)
    {
        var preload_image = new Image();
        //preload_image.onabort = inc;
        //preload_image.onerror = inc;
        preload_image.onload = function() {
            console_log('image loaded');
            console_log("width:" + preload_image.width + ", height:" + preload_image.height);
            /*
            var img = document.getElementById("img_") || document.createElement("img");
            img.id = "img_";
            img.src = proxy_node_url;
            img.hidden = false;
            */
            /*
            this.id = "img_";
            this.hidden = false;
            document.getElementById("media_").innerHTML = '';
            document.getElementById("media_").appendChild(this);
            if (typeof changeSizeOfContainer == 'function') { changeSizeOfContainer('img'); }
            */
            setTimeout(function() {
                document.getElementById("media_").innerHTML = '';
                document.getElementById("media_").appendChild(preload_image);
                if (typeof changeSizeOfContainer == 'function') { changeSizeOfContainer('img'); }
            }, 500);

        };
        preload_image.onerror = function() {
            console_log('image download error');
            if (typeof parent != 'undefined' && typeof parent.closeDownloadIframe != 'undefined') {
                parent.closeDownloadIframe("Preview task failure. File is not available.");
                parent.closePreviewIframe();
            }
        };
        preload_image.src = proxy_node_url;

        /*
        var img = document.getElementById("img_") || document.createElement("img");
        img.id = "img_";
        img.src = proxy_node_url;
        img.hidden = false;
        setTimeout(function() {
            document.getElementById("media_").innerHTML = '';
            document.getElementById("media_").appendChild(img);
            if (typeof changeSizeOfContainer == 'function') { changeSizeOfContainer('img'); }
        }, 1000);
        */
        return;
    }

    /* workaround for text */
    var text_exts = ["txt", "js", "py", "html", "md", "log"];
    if (preview_mode && text_exts.indexOf(file_ext.toLowerCase()) >= 0)
    {
        preview_text(proxy_node_url);
        return;
    }

    /* workaround for pdf */
    var text_exts = ["pdf"];
    if (preview_mode && text_exts.indexOf(file_ext.toLowerCase()) >= 0)
    {
        preview_pdf(proxy_node_url);
        return;
    }

    var media_element = get_media_element(media_type, proxy_node_url);
    if (!media_element)
    {
        if (typeof showDownloadButton == 'function') {
            var download_anchor = document.getElementById("download-from-node");
            download_anchor.href = proxy_node_url;
            showDownloadButton(true);
        } else {
            if (typeof noPreview == 'function') { noPreview(); }
            download_by_proxy_node();
        }
        return;
    } else {
        setTimeout(function() {
            document.getElementById("media_").innerHTML = '';
            document.getElementById("media_").appendChild(media_element);
            if (typeof changeSizeOfContainer == 'function') { changeSizeOfContainer(media_type); }
        }, 500);
    }

    /*
    media_element.autoplay = false;
    media_element.hidden = false;
    media_element.src = proxy_node_url;
    */
    console_log(media_element);
}


var get_file_extension = function (file_name)
{
    return file_name.slice((file_name.lastIndexOf(".") - 1 >>> 0) + 2);
}


var get_media_type = function (file_name)
{
    var file_extension = get_file_extension(file_name);
    var media_types = {

        webm: 'video/webm;codecs=vp8,vorbis',
        mp4:  'video/mp4',
        ogv:  'video/ogg',
        avi:  'video/avi',  // unsupported
        mkv:  'video/mkv',  // unsupported

        mp3:  'audio/mpeg',
        ogg:  'audio/ogg',
        wav:  'audio/wav'
    }
    return media_types[file_extension] || file_extension;
}


var get_media_element = function(media_type, url)
{
    var on_error = function(e)
    {
        console_log("On error handler for media element. Error: " + e);

        if (typeof showDownloadButton == 'function') {
            var download_anchor = document.getElementById("download-from-node");
            download_anchor.hidden = true;
            download_anchor.href = url;
            showDownloadButton(true);
        } else if (typeof parent != 'undefined' && typeof parent.closeDownloadIframe != 'undefined') {
            parent.closeDownloadIframe("Preview task failure. File is not available.");
            parent.closePreviewIframe();
        }
    };

    /* e.g  'video/webm;codecs="vp8"' */
    var t = media_type.split("/");
    var _type = t.length > 1 ? t[0] : media_type;
    if (_type == "video")
    {
        var video_element = document.getElementById('video_') || document.createElement("video");
        video_element.id = "video_";
        video_element.controls = true;
        video_element.hidden = false;
        video_element.autoplay = false;
        video_element.hidden = false;
        video_element.onerror = on_error;
        if (url) { video_element.src = url; }
        //video_element.type = media_type;

        //document.getElementById("media_").innerHTML = '';
        //document.getElementById("media_").appendChild(video_element);
        //if (typeof changeSizeOfContainer == 'function') { changeSizeOfContainer('video'); }
        return video_element;
    }
    else if (_type == "audio")
    {
        var audio_element = document.getElementById('audio_') || document.createElement("audio");
        audio_element.id = "audio_";
        audio_element.controls = true;
        audio_element.hidden = false;
        audio_element.autoplay = false;
        audio_element.hidden = false;
        audio_element.onerror = on_error;
        if (url) { audio_element.src = url; }
        //audio_element.type = media_type;

        //document.getElementById("media_").innerHTML = '';
        //document.getElementById("media_").appendChild(audio_element);
        //if (typeof changeSizeOfContainer == 'function') { changeSizeOfContainer('audio'); }
        return audio_element;
    }
    else
    {
        return null;
    }
}


var preview_text = function(url)
{
    var preview = function()
    {
        console.log("origin:", url);
        var pre = document.getElementById("pre_") || document.createElement("pre");
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.responseType = "arraybuffer";
        //xhr.overrideMimeType('text\/plain; charset=x-user-defined');
        xhr.send();
        xhr.onreadystatechange = function()
        {
            if (this.readyState != 4) {
                document.getElementById("media_").innerHTML = '';
                document.getElementById("media_").appendChild(pre);
                if (typeof changeSizeOfContainer == 'function') { changeSizeOfContainer('pre'); }
                pre.hidden = false;
                return;
            }
            if (this.status != 200)
            {
                /*
                pre.textContent = "Error: " + (this.status ? this.statusText : "Invalid request");
                document.getElementById("media_").innerHTML = '';
                document.getElementById("media_").appendChild(pre);
                if (typeof changeSizeOfContainer == 'function') { changeSizeOfContainer('pre'); }
                pre.hidden = false;
                */
                if (typeof parent != 'undefined' && typeof parent.closeDownloadIframe != 'undefined') {
                    parent.closeDownloadIframe("Preview task failure. File is not available.");
                    parent.closePreviewIframe();
                }
                return;
            }

            console_log("jschardet_is_ready: ", jschardet_is_ready);
            console_log("textdecoder_is_ready: ", textdecoder_is_ready);
            if (jschardet_is_ready == true && textdecoder_is_ready == true)
            {
                var data = new Uint8Array(this.response);
                var s = "";
                for (var i=0; i < data.length && i < 1024; i++)
                {
                    s += String.fromCharCode(data[i]);
                }
                var det = jschardet.detect(s);
                // it need analyze confidence
                console_log("jschardet: detection result: " + det.encoding);

                try
                {
                    if (det.encoding == 'MacCyrillic')
                        det.encoding = 'windows-1251';
                    pre.textContent = new TextDecoder(det.encoding).decode(data);
                }
                catch (e)
                {
                    pre.textContent = String.fromCharCode.apply(null, data);
                    console_log(e);
                }
            }
            else
            {
                //pre.textContent = this.responseText;
                pre.textContent = String.fromCharCode.apply(null, new Uint8Array(this.response));
            }
            //document.getElementById("media_").innerHTML = '';
            document.getElementById("media_").appendChild(pre);
            pre.hidden = false;
            if (typeof changeSizeOfContainer == 'function') { changeSizeOfContainer('pre'); }
        }
        return;
    }

    window.on_load_scripts = function()
    {
        if ( jschardet && typeof(jschardet.detect) == "function" )
        {
            jschardet_is_ready = true;
            textdecoder_is_ready = typeof(TextDecoder) == "function" ? true : false;
            if (textdecoder_is_ready == true)
            {
                preview();
            }
            else
            {
                if (attempt_to_load_textdecoder == true)
                {
                    textdecoder_is_ready == false;
                    preview();
                    return;
                }
                else
                {
                    attempt_to_load_textdecoder = true;
                }
                // use https://github.com/inexorabletash/text-encoding
                load_scripts([
                    "/themes/orange/js/encoding-indexes.js",
                    "/themes/orange/js/encoding.js"
                ]);
            }
        }
        else
        {
            jschardet_is_ready = false;
            preview();
        }
    }

    window.on_error_scripts = function()
    {
        jschardet_is_ready = false;
        textdecoder_is_ready = false;
        preview();
    }

    var jschardet_is_ready = false;
    var textdecoder_is_ready = false;
    var attempt_to_load_textdecoder = false;
    load_scripts(["https://cdnjs.cloudflare.com/ajax/libs/jschardet/1.6.0/jschardet.min.js"]);
}


var preview_pdf = function(url)
{
    console.debug(">> preview pdf: ", url);
    var canvasContainer = document.getElementById("media_");
    if (typeof changeSizeOfContainer == 'function') { changeSizeOfContainer('pdf'); }
    //canvasContainer.innerHTML = '';
    //var scale= 1.5;
    var scale= 1;
    var pdf_doc = null;
    var page_num = 1;

    var renderPDF = function (url, canvasContainer)
    {
        console.debug(">> render pdf ..");

        var renderPage = function(num)
        {
            pdf_doc.getPage(num).then(function(page){
                var viewport = page.getViewport(scale);
                var canvas = document.getElementById("canvas_" + num)
                             || document.createElement("canvas");
                canvas.id = "canvas_" + num;
                canvas.className = "canvas_";
                canvas.hidden = false;

                var ctx = canvas.getContext('2d');
                var renderContext = {
                  canvasContext: ctx,
                  viewport: viewport
                };

                /*
                var tmp_w = document.getElementById("media_").offsetWidth - 20;
                var tmp_delta = tmp_w / viewport.width;
                 console_log(tmp_w);
                 console_log(viewport.width * tmp_delta);
                 console_log(parseInt(viewport.height * tmp_delta));
                 console_log(parseInt(viewport.height));
                */

                canvas.width = parseInt(viewport.width);
                canvas.height = parseInt(viewport.height);

                var preview_node = document.getElementById("preview-loading");
                if (!(preview_node === null)) {
                    preview_node.parentNode.removeChild(preview_node);
                }
                canvasContainer.appendChild(canvas);
                //if (typeof changeSizeOfContainer == 'function') { changeSizeOfContainer('pdf'); }

                var render_task = page.render(renderContext);

                // wait
                render_task.promise.then(function() {
                    console_log("rendering complete :", page_num, "of", pdf_doc.numPages);
                    page_num++;
                    if (page_num <= pdf_doc.numPages)
                        renderPage(page_num);
                });

            }, function(e){
                // pdf loading error
                console.error(e);
            });
        }

        var renderPages = function (pdfDoc)
        {
            //for(var num = 1; num <= pdfDoc.numPages && num <= 1; num++) // FIXME
            //    pdfDoc.getPage(num).then(renderPage);
            pdf_doc = pdfDoc;
            renderPage(page_num);
        }

        var on_error = function (e)
        {
            // pdf loading error
            //console.error(e);
            if (typeof showDownloadButton == 'function') {
                var download_anchor = document.getElementById("download-from-node");
                download_anchor.hidden = true;
                download_anchor.href = url;
                showDownloadButton(true);
            } else if (typeof parent != 'undefined' && typeof parent.closeDownloadIframe != 'undefined') {
                parent.closeDownloadIframe("Preview task failure. File is not available.");
                parent.closePreviewIframe();
            }
        }

        //PDFJS.disableWorker = true;
        PDFJS.getDocument(url).then(renderPages, on_error);
    }

    var script = document.createElement('script');
    script.src = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.0.108/pdf.min.js";
    document.body.appendChild(script);
    var pdfjs_is_ready = false;

    script.onload = function()
    {
        pdfjs_is_ready = true;
        renderPDF(url, canvasContainer);
        if (typeof changeSizeOfContainer == 'function') { changeSizeOfContainer('pdf'); }
    }

    script.onerror = function()
    {
        pdfjs_is_ready = false;
        //alert("while loading pdf.js error occurted");
        console_log("while loading pdf.js error occurted");
    }

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
                        if (typeof(on_load_scripts) == "function")
                            on_load_scripts();
                    }
                }
            } else {
                script.onload = function(){
                    if (typeof(on_load_scripts) == "function")
                        on_load_scripts();
                }
                script.onerror = function(){
                    if (typeof(on_error_scripts) == "function")
                        on_error_scripts();
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
            //msg += "Error name: " + error.name + ". ";
            msg += error.name + ". ";
        }
        if (error.message !== undefined){
            //msg += "Error message: " + error.message + ". ";
            msg += error.message + ". ";
        } else {
            msg += error;
        }
    }
    // to avoid the same error message
    if (errors.length != 0 && errors[errors.length-1] == msg){
        //skip to show error.message
        ;
    } else {
        //console.info(msg);
        if (repeat == false){
            errors.push(msg);
        }
        if (warnings.length > 0){
            msg += "\nRecent warnings:";
            for (var i=0; i<warnings.length; i++){
                msg += "\n- " + warnings[i];
            }
        }
        //alert(msg);
        console_log(msg);
    }
}


// handle error
window.onerror = function(err_msg, url, line, pos, error){
    if (download_task){
        download_task.cancel();
    }
    //console.error(err_msg, url, line, pos, error);

    //send error to tracking-server
    web_tracking.send_websession_error("unhandled_error", err_msg);

    // maybe file already downloaded
    if (typeof(window.check_state) == "function" && check_state() == false)
        return;

    if (preview_mode)
        if (attempt_to_use_proxy == true)
            show_alert_message("Unhandled error. ", err_msg, false);
        else
            preview_by_proxy_node();
    else
        download_by_proxy_node();
}


/* set constants */
var CHUNK_SIZE = 65536;
var FIRST_CHUNK_SIZE = 1024;
var CHUNK_MULTIPLIER = 16;
var DOWNLOAD_TASK_TIMEOUT = 70*1000;
var DOWNLOAD_CHUNK_TIMEOUT = 30*1000;
var NUM_OF_CHANNELS = 5;
var NUM_OF_SIGNAL_CONNECTIONS = 1;
var MAX_CHUNKS_IN_BUFFER = 500;
var MAX_CHUNKS_IN_BUFFER_FOR_PREVIEW = 500;  // for preview mode
var DOWNLOAD_CHANNEL_OPENING_TIMEOUT = 8000;
var DOWNLOAD_AVAILABILITY_INFO_TIMEOUT = 12000;
var DATA_RECEIVING_CHECK_INTERVAL = 5000;
/* end set constants */

var preview_mode = false;  // try to preview file in browser

// input parameters
var opt_file_name = document.getElementById('file_name');
var opt_file_size = document.getElementById('file_size');
var opt_event_uuid = document.getElementById('event_uuid');
var opt_stun_server_url = document.getElementById('stun_server_url');
var ice_servers = {"iceServers":[{"urls": "stun:" + opt_stun_server_url.value}]};
//console_log(ice_servers);
var opt_sig_server_url = document.getElementById('sig_server_url');
var opt_proxy_node_url = document.getElementById('proxy_node_url');
var scripts = [];
var opt_scripts_el = document.getElementById('scripts');
if (opt_scripts_el) {
    var opt_scripts = opt_scripts_el.options;
    for (var i = 0; i < opt_scripts.length; i++) {
        //console_log(opt_scripts[i].value);
        scripts.push(opt_scripts[i].value);
    }
}


// add some required elements
var a_refresh = document.getElementById('a_refresh');
var label_result = document.getElementById('label_result');
var btn_download = document.getElementById('btn_download');
//var btn_preview = document.getElementById('btn_preview');
//var btn_preview_by_proxy = document.getElementById('btn_preview_by_proxy');
//btn_preview_by_proxy.onclick = preview_by_proxy_node;
var progress = document.getElementById('progress_');
var label_state = document.getElementById('label_state');


// pass some parameters to variables
var file_name = getText(opt_file_name);
var file_size = parseInt(getText(opt_file_size));
setText(opt_file_size, human_readable_size(file_size) + " (" + file_size + " Bytes)");
var event_uuid = getText(opt_event_uuid);
var sig_server_url = opt_sig_server_url.value;
var proxy_node_url = opt_proxy_node_url.value;
var out_online_nodes = document.getElementById('online_nodes');
progress.max = file_size;
var p_progress = document.getElementById('p_progress');
var p_result = document.getElementById('p_result');


var errors = [];
var warnings = [];
var download_task = null;
var attempt_to_use_proxy = false;

var web_tracking = new Web_Tracking("https://tracking.pvtbox.net:443/1", 2, "browser", "webfm");

var connection_string = sig_server_url;


if (check_browser_for_websocket())
{
    if (!check_browser_for_webrtc())
    {
        btn_download.onclick = download_by_proxy_node;
        console_log($('#message-info-WebRTC').html());
        web_tracking.send_websession_start(false);
    }
    else
    {
        load_scripts(scripts);
        web_tracking.send_websession_start(true);
    }
}
else
{
    btn_download.onclick = download_by_proxy_node;
    console_log($('#message-info-Websocket').html());
    web_tracking.send_websession_start(false);
}

web_tracking.send_webdownload_fileinfo(file_size, event_uuid);
