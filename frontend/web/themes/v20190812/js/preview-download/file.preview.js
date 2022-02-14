/**
 * @param {array} scripts
 */
function load_scripts(scripts)
{
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
                };
                script.onerror = function(){
                    if (typeof(on_error_scripts) == "function")
                        on_error_scripts();
                };
            }
        }
        document.body.appendChild(script);
    }
}

/**
 * @param {string} file_name
 * @returns {*}
 */
function getMediaType(file_name)
{
    var media_types = {
        webm: { type: 'video/webm;codecs=vp8,vorbis', funct: 'media' },
        mpeg: { type: 'video/mpeg', funct: 'video', preview: true },
        mp4:  { type: 'video/mp4',  funct: 'video', preview: true },
        wmv:  { type: 'video/wmv',  funct: 'video', preview: true },
        mpg:  { type: 'video/mpeg', funct: 'video', preview: true },
        mov:  { type: 'video/mov',  funct: 'video', preview: true },
        avi:  { type: 'video/avi',  funct: 'video', preview: true }, // unsupported
        mkv:  { type: 'video/mkv',  funct: 'video', preview: true }, // unsupported
        ogv:  { type: 'video/ogg',  funct: 'video', preview: true },

        mp3:  { type: 'audio/mpeg',  funct: 'audio', preview: true },
        wma:  { type: 'audio/wma',  funct: 'audio', preview: true },
        ogg:  { type: 'audio/ogg',  funct: 'audio', preview: true },
        wav:  { type: 'audio/wav',  funct: 'audio', preview: true },

        pdf:  { type: 'pdf/pdf',    funct: 'pdf', preview: true },

        txt : { type: 'text/plain', funct: 'text', preview: true },
        js  : { type: 'text/plain', funct: 'text', preview: true },
        py  : { type: 'text/plain', funct: 'text', preview: true },
        html: { type: 'text/html',  funct: 'text', preview: true },
        md  : { type: 'text/plain', funct: 'text', preview: true },
        log : { type: 'text/plain', funct: 'text', preview: true },

        jpg  : { type: 'image/jpeg', funct: 'image', preview: true },
        jpeg : { type: 'image/jpeg', funct: 'image', preview: true },
        png  : { type: 'image/png',  funct: 'image', preview: true },
        bmp  : { type: 'image/bmp',  funct: 'image', preview: true },
        svg  : { type: 'image/svg',  funct: 'image', preview: true },
        gif  : { type: 'image/gif',  funct: 'image', preview: true },
        tiff : { type: 'image/tiff', funct: 'image', preview: true },
        tif  : { type: 'image/tiff', funct: 'image', preview: true }
    };

    var ext = file_name.split('.').pop();
    ext = ext.toLowerCase();

    if (ext in  media_types) {
        return media_types[ext];
    } else {
        return { type: 'application/octet-stream', funct: 'null', preview: false };
    }
}

/**
 * @param {string} file_name
 */
function checkIsCanPreviewed(file_name)
{
    var test = getMediaType(file_name);
    return test.preview;
}

/**
 * previewFile
 * @param {object} file
 */
function previewFile(file)
{
    $('#trigger-preview-modal').trigger( "click" );

    if (parseInt(file.file_size) <=0) {
        elfinderInstance.error("Preview impossible. File is empty.");
        return false;
    }
    if (("file_id" in file) && !(file.file_id === null)) {

        var $total_container_id = $('#total-container-id');
        var modal_h = Math.max(
                parseInt($total_container_id.height()),
                parseInt($total_container_id.innerHeight())
        ) - 60;
        var modal_w = Math.max(
            parseInt($total_container_id.width()),
            parseInt($total_container_id.innerWidth())
        ) - 100;

        var $preview_modal = $('#preview-modal');
        var preview_body_h = Math.max(
            parseInt($preview_modal.height()),
            parseInt($preview_modal.innerHeight())
        ) - 95;
        var preview_body_w = Math.max(
            parseInt($preview_modal.width()),
            parseInt($preview_modal.innerWidth())
        ) - 80;

        $('#preview-body').css({
            'height'     : preview_body_h + 'px',
            'overflow'   : 'hidden',
            'overflow-x' : 'hidden',
            'overflow-y' : 'hidden'
        });

        var $preview_file_name = $('#preview-file-name');
        $preview_file_name
            .html(file.file_name)
            .attr('title', file.file_name);
            //.css({ 'width' : (modal_w - 180) + 'px' });
        var $loading = $('#preview-tpl').find('.preview-loading').first().clone();
        $('#media_')
            .empty()
            .html('')
            .removeClass('media_')
            .removeClass('is-loaded')
            //.removeClass()
            .append($loading);

        startPreviewFile(file, {w: preview_body_w, h: preview_body_h});

        return true;

    } else {
        elfinderInstance.error("Preview impossible. File is not synced.");
        return false;
    }
}

/**
 * Основная функция запуска скачивания
 * @param {object} file
 * @param {object} size
 */
function startPreviewFile(file, size)
{
    if (typeof deactivatePreviewPageBtnDownload == 'function') {
        deactivatePreviewPageBtnDownload()
    }

    /*
     file.file_name = "1.jpg";
     file.file_size = 50873;
     file.last_event_uuid = "14c9f2bd4d1d4e58afaafe920162bca2";
     */
    var test_media = getMediaType(file.file_name);

    /* проверка что файл до этого не был скачан*/
    var anchor = document.getElementById(file.last_event_uuid);
    if (anchor !== null) {
        console_log('Already downloaded. Immediately give the link.');
        preview_case(anchor.href, file, size, true);
        return anchor.href;
    }

    /* подготовка урла на проксиноду, для скачивания файла с него, если по ртц не получится */
    var proxy_url = $('#wss-data').attr('data-proxy-url');
    proxy_url = proxy_url.replace(/\{([a-zA-Z0-9\_]+)\}/g, function (s, e) {
        return file[e];
    });
    //console_log(proxy_url);

    /* если иос то качаем через прокси */
    var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    if (iOS) {
        preview_case(proxy_url, file, size, true);
        return proxy_url;
    }

    /* если это видео --или аудио-- и это блядь сафари или фаерфокс то пошел он нахуй через прокси падла */
    //alert(navigator.userAgent);
    var Safari = /Safari|Firefox/.test(navigator.userAgent);
    if ((test_media.funct == 'video' /*|| test_media.funct == 'audio'*/) && Safari) {
        preview_case(proxy_url, file, size, true);
        return proxy_url;
    }

    /* если фаерфокс то качаем через прокси */
    //var isFirefox = typeof navigator.mozGetUserMedia !== 'undefined';
    //if (isFirefox) {
    //    preview_case(proxy_url, file, size, true);
    //    return proxy_url;
    //}

    /* проверка на вебсокет */
    if (!check_browser_for_websocket()) {
        console_log('WebSocket false. Download by Proxy');
        preview_case(proxy_url, file, size, true);
        return proxy_url;
    }

    /* проверка на ртц */
    if (!check_browser_for_webrtc()) {
        console_log('WebRTC false. Download by Proxy');
        preview_case(proxy_url, file, size, true);
        return proxy_url;
    }

    /* создание объекта transport */
    var transport = new Transport(ice_servers, NUM_OF_CHANNELS);
    transport.cb_error = function(error) {
        console_log(error);
        preview_case(proxy_url, file, size, true);
        return proxy_url;
    };
    transport.cb_state_changed = function(state_info) {
        console_log(state_info);
    };
    console_log('Object Transport created!');

    /** Тут если превью audio или video то пробуем создать File_Writer сразу в елемент иначе в обычный врайтер */
    var file_writer;
    var BUFFER_SIZE;
    if (test_media.preview && (test_media.funct == 'audio' || test_media.funct == 'video')) {
        var media_ = document.getElementById("media_");
        var media_el = create_media_element(proxy_url ,test_media.funct);
        /* создание объекта file-writer для видео или аудио */
        BUFFER_SIZE = MAX_CHUNKS_IN_BUFFER_FOR_PREVIEW;
        file_writer = new MediaSource_Writer({
            file_size: file.file_size,
            media_type: test_media.type,
            media_element: media_el
        });

        media_.innerHTML = '';
        media_.appendChild(media_el);
        if (test_media.funct == 'audio') {
            $('#media_').addClass('media_');
        } else {
            $('#video_').css({
                'height': (size.h - 8) + "px"
            });
        }
    } else {
        /* создание объекта file-writer */
        BUFFER_SIZE = MAX_CHUNKS_IN_BUFFER;
        var mime_type = MimeTypes.lookup(file.file_name) || "application/octet-stream";
        var File_Writer = get_appropriate_writer(file.file_size);
        file_writer = new File_Writer({file_size: file.file_size, mime_type: mime_type});
    }
    console_log('Object File_Writer created!');

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
            task_timeout: DOWNLOAD_TASK_TIMEOUT,
            chunk_timeout: DOWNLOAD_CHUNK_TIMEOUT,
            channel_opening_timeout: DOWNLOAD_CHANNEL_OPENING_TIMEOUT,
            availability_info_timeout: DOWNLOAD_AVAILABILITY_INFO_TIMEOUT,
            data_receiving_check_interval: DATA_RECEIVING_CHECK_INTERVAL,
            max_chunks_in_buffer: BUFFER_SIZE
        },
        {
            /* коллбек поосле успешного скачивания по ртц (тут сохраним в HTML объект типа <a> и инициируем клик по нему)*/
            cb_task_success: function (url) {
                if (typeof activatePreviewPageBtnDownload == 'function') {
                    activatePreviewPageBtnDownload()
                }
                console_log("download task success, url:" + url);
                var progress_info = {
                    received_size: file.file_size,
                    effective_received_size: file.file_size,
                    duration: 1000
                };
                console_log(progress_info);
                if (typeof media_el == 'undefined' && typeof url == 'string') {
                    preview_case(url, file, size, false);
                    return url;
                } else {
                    return null;
                }
            },

            /* коллбек в случае провала скачивания по ртц (тут скачиваем через прокси ноду)*/
            cb_task_failure: function (error) {
                if (typeof activatePreviewPageBtnDownload == 'function') {
                    activatePreviewPageBtnDownload()
                }
                console_log(error);
                if (error && error.code && error.code == Download_Task.ErrorCode.CHANNEL_OPENING_TIMEOUT) {
                    console_log("Switch to fallback mode (download by proxy-node)");
                    preview_case(proxy_url, file, size, true);
                    return proxy_url;
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
                    if (typeof media_el != 'undefined') {
                        //download_task.cancel();
                        //media_el = null;
                        //file_writer = null;
                        //transport = null;
                        preview_case(proxy_url, file, size, true);
                        return proxy_url;
                    }
                    //elfinderInstance.error(error_text);
                    tryDownloadIt(proxy_url);
                }
            },

            /**/
            cb_warning: function (warning) {
                if (typeof activatePreviewPageBtnDownload == 'function') {
                    activatePreviewPageBtnDownload()
                }
                console_log(warning);
            },

            /* прогрес на этапе скачивания */
            cb_task_progress: function (progress_info) {
                //console_log(progress_info);
            },

            /* коллбек метод который вызывается после успешного подключения к вебсокету объектом transport */
            cb_online_nodes: function(nodes) {
                if (!nodes.length) {
                    if (typeof activatePreviewPageBtnDownload == 'function') {
                        activatePreviewPageBtnDownload()
                    }
                    console_log('No online nodes');
                    if (typeof elfinderInstance != 'undefined') {
                        elfinderInstance.error('No online nodes');
                        //alert('create function for close preview-popup for this case');
                        $.fancybox.close();
                        return;
                    } else {
                        noOnlineNodes();
                        return;
                    }
                }
                if (typeof store_download_tasks[file.last_event_uuid] === 'undefined') {
                    console_log("Available nodes:");
                    console_log(nodes);
                    this.start();
                    if (typeof media_el == 'undefined' && typeof url == 'string') {
                        store_download_tasks[file.last_event_uuid] = this;
                    }
                }
            }
        }
    );
    console_log('Object Download_Task created!');

    /* подключаемся к сокету (после успешного подключения к сокету автоматически вызывается метод download_task.cb_online_nodes)*/
    for (var i=0; i < NUM_OF_SIGNAL_CONNECTIONS; i++){
        transport.open_signal_connection($('#wss-data').attr('data-signal-url'));
    }
}


/**
 * @param {string} url
 */
function tryDownloadIt(url)
{
    var $try_download_id = $('#preview-tpl').find('.try-download-it').first().clone();
    var $btn = $try_download_id.find('.btn-try-download-it').first();
    $btn.attr('href', url);

    $('#media_')
        .empty()
        .html('')
        .removeClass('media_')
        .addClass('share-media_')
        .append($try_download_id);
}

/**
 *
 */
function noOnlineNodes()
{
    var $no_online_nodes_id = $('#preview-tpl').find('.no-online-nodes').first().clone();

    $('#media_')
        .empty()
        .html('')
        .removeClass('media_')
        .addClass('share-media_')
        .append($no_online_nodes_id);
}

/**
 *
 */
function previewLoading()
{
    var $preview_loading = $('#preview-tpl').find('.preview-loading').first().clone();

    $('#media_')
        .empty()
        .html('')
        .removeClass('media_')
        .addClass('share-media_')
        .append($preview_loading);
}

/**
 *
 */
function shareHasPassword()
{
    var $share_has_password = $('#preview-tpl').find('.share-has-password').first().clone();

    $('#media_')
        .empty()
        .html('')
        .removeClass('media_')
        .addClass('share-media_')
        .append($share_has_password);
}

/**
 *
 */
function previewShareShow()
{
    $('#media_').removeClass('share-media_');
    var $preview_share = $('.preview-share');
    $preview_share.show();

    /* fix for video and img */
    if ($preview_share.length) {
        var $preview_container = $('#preview-body');
        var $video_ = $('#video_');
        var $img_ = $('#img_');
        if ($video_.length) {
            $video_.css({
                'width': '100%',
            });
            $preview_container.css({
                'height' : 'auto'
            });
        }
        if ($img_.length) {
            $preview_container.css({
                'height' : 'auto'
            });
        }
    }
}

/**
 * @param {string} url
 * @param {object} file
 */
function create_anchor(url, file)
{
    if (!$('#' + file.last_event_uuid).length) {
        var anchor = document.createElement('a');
        anchor.href = url;
        anchor.download = file.file_name;
        anchor.id = file.last_event_uuid;
        anchor.class = "hidden";
        //anchor.target = 'download_frame';
        anchor.target = '_self';
        var tmp = document.getElementById('wss-data');
        tmp.appendChild(anchor);
    }
}

/**
 * @param {string} url
 * @param {object} file
 * @param {object} size
 * @param {boolean} rtc_failed
 */
function preview_case(url, file, size, rtc_failed)
{
    if (typeof activatePreviewPageBtnDownload == 'function') {
        activatePreviewPageBtnDownload()
    }

    /* создаем невидимую ссылку на скачанный файл */
    create_anchor(url, file);

    //setTimeout(function() {

    var media_type = getMediaType(file.file_name);

    if (!media_type.preview) {
        // Превью невозможно.
        tryDownloadIt(url);
        return;
    }

    switch(media_type.funct) {
        case 'video':
            if (rtc_failed) {
                //url = '/testscripts/video.mp4';
            }
            preview_video(url, size);
            break;
        case 'audio':
            if (rtc_failed) {
                //url = '/testscripts/mp3.mp3';
            }
            preview_audio(url, size);
            break;
        case 'pdf':
            if (rtc_failed) {
                //url = '/testscripts/pdf.pdf';
            }
            preview_pdf(url, size);
            break;
        case 'text':
            if (rtc_failed) {
                //url = '/testscripts/time1.txt';
            }
            preview_text(url, size);
            break;
        case 'image':
            if (rtc_failed) {
                //url = '/testscripts/top.jpg';
            }
            preview_image(url, size);
            break;
        default:
            // Превью невозможно.
            tryDownloadIt(url);
            break;
    }


    //}, 1000);
}

/**
 * @param {string} url
 * @param {object} size
 */
function preview_text(url, size)
{
    $('#preview-body').css({
        'overflow-y' : 'auto'
    });

    var preview = function()
    {
        console_log("origin:" + url);
        var pre = document.getElementById("pre_") || document.createElement("pre");
        var media_ = document.getElementById("media_");
        pre.id = 'pre_';
        pre.textContent = 'null';
        //var xhr = new XMLHttpRequest();
        var XHR = ("onload" in new XMLHttpRequest()) ? XMLHttpRequest : XDomainRequest;
        var xhr = new XHR();
        xhr.open('GET', url, true);
        xhr.responseType = "arraybuffer";
        //xhr.overrideMimeType('text\/plain; charset=x-user-defined');
        xhr.send();
        xhr.onreadystatechange = function()
        {
            if (this.readyState != 4) {
                media_.innerHTML = '';
                media_.appendChild(pre);
                pre.hidden = false;
                return void(0);
            }
            if (this.status != 200) {
                console_log("Failed preview_text function. Error: " + this.status);
                tryDownloadIt(url);
                return void(0);
            }

            console_log("jschardet_is_ready: " + jschardet_is_ready);
            console_log("textdecoder_is_ready: " + textdecoder_is_ready);
            if (jschardet_is_ready == true && textdecoder_is_ready == true) {
                var data = new Uint8Array(this.response);
                var s = "";
                for (var i=0; i < data.length && i < 1024; i++) {
                    s += String.fromCharCode(data[i]);
                }
                var det = jschardet.detect(s);
                // it need analyze confidence
                console_log("jschardet: detection result: " + det.encoding);

                try {
                    if (det.encoding == 'MacCyrillic') {
                        det.encoding = 'windows-1251';
                    }
                    pre.textContent = new TextDecoder(det.encoding).decode(data);
                    //console_log(typeof pre.textContent);
                    //alert(pre.textContent);
                } catch (e) {
                    pre.textContent = String.fromCharCode.apply(null, data);
                    console_log(e);
                }
            } else {
                pre.textContent = String.fromCharCode.apply(null, new Uint8Array(this.response));
            }
            //alert(pre.textContent);
            media_.appendChild(pre);
            //pre.hidden = false;
            previewShareShow();
        };

        xhr.onerror = function(e) {
            console_log("Failed preview_text function. Error: " + e);
            tryDownloadIt(url);
        };
    };

    window.on_load_scripts = function()
    {
        if ( jschardet && typeof(jschardet.detect) == "function" ) {
            jschardet_is_ready = true;
            textdecoder_is_ready = (typeof(TextDecoder) == "function");
            if (textdecoder_is_ready == true) {
                preview();
            } else {
                if (attempt_to_load_textdecoder == true) {
                    textdecoder_is_ready == false;
                    preview();
                    return;
                } else {
                    attempt_to_load_textdecoder = true;
                }
                load_scripts([
                    "/themes/v20190812/js/encoding/encoding-indexes.js",
                    "/themes/v20190812/js/encoding/encoding.js"
                ]);
            }
        } else {
            jschardet_is_ready = false;
            preview();
        }
    };

    window.on_error_scripts = function()
    {
        jschardet_is_ready = false;
        textdecoder_is_ready = false;
        preview();
    };

    var jschardet_is_ready = false;
    var textdecoder_is_ready = false;
    var attempt_to_load_textdecoder = false;
    load_scripts(["https://cdnjs.cloudflare.com/ajax/libs/jschardet/1.6.0/jschardet.min.js"]);
}

var STOP_RENDER_PDF;
/**
 * @param {string} url
 * @param {object} size
 */
function preview_pdf(url, size)
{
    STOP_RENDER_PDF = false;
    $('#preview-body').css({
        'overflow-y' : 'auto'
    });

    console_log(">> preview pdf: " + url);
    var canvasContainer = document.getElementById("media_");
    //canvasContainer.innerHTML = '';
    //var scale= 1.5;
    var scale= 1;
    var pdf_doc = null;
    var page_num = 1;

    var renderPDF = function (url, canvasContainer)
    {
        console_log(">> render pdf ..");

        var renderPage = function(num)
        {
            pdf_doc.getPage(num).then(function(page){
                if (num == 1) {
                    canvasContainer.innerHTML = '';
                }
                var viewport = page.getViewport(scale);
                var canvas = document.getElementById("canvas_" + num) || document.createElement("canvas");
                canvas.id = "canvas_" + num;
                canvas.className = "canvas_";
                canvas.hidden = false;

                var ctx = canvas.getContext('2d');
                var renderContext = {
                    canvasContext: ctx,
                    viewport: viewport
                };

                canvas.width = parseInt(viewport.width);
                canvas.height = parseInt(viewport.height);

                var preview_node = document.getElementById("preview-loading");
                if (!(preview_node === null)) {
                    preview_node.parentNode.removeChild(preview_node);
                }
                canvasContainer.appendChild(canvas);

                var render_task = page.render(renderContext);

                // wait
                render_task.promise.then(function() {
                    console_log("rendering complete : " + page_num + " of " + pdf_doc.numPages);
                    page_num++;
                    if (page_num <= pdf_doc.numPages && !STOP_RENDER_PDF)
                        renderPage(page_num);
                });

            }, function(e){
                // pdf loading error
                console_log(e);
            });
        };

        var renderPages = function (pdfDoc)
        {
            //for(var num = 1; num <= pdfDoc.numPages && num <= 1; num++) // FIXME
            //    pdfDoc.getPage(num).then(renderPage);
            pdf_doc = pdfDoc;
            previewShareShow();
            renderPage(page_num);
        };

        var on_error = function (e)
        {
            console_log("Failed preview_pdf function. Error: " + e);
            tryDownloadIt(url);
        };

        //PDFJS.disableWorker = true;
        PDFJS.getDocument(url).then(renderPages, on_error);
        return void(0);
    };

    var script = document.createElement('script');
    script.src = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.0.108/pdf.min.js";
    document.body.appendChild(script);
    var pdfjs_is_ready = false;

    script.onload = function()
    {
        pdfjs_is_ready = true;
        renderPDF(url, canvasContainer);
    };

    script.onerror = function()
    {
        pdfjs_is_ready = false;
        console_log("while loading pdf.js error occurted");
    }
}

/**
 * @param {string} url
 * @param {object} size
 */
function preview_image(url, size)
{
    //console_log("--------------------------------------");
    //console_log(url);
    var media_ = document.getElementById("media_");
    var preload_image = new Image();
    //var preload_image = document.createElement('img');
    preload_image.id = "img_";
    preload_image.hidden = false;
    preload_image.onload = function() {
        console_log('image loaded');
        console_log("width:" + preload_image.width + ", height:" + preload_image.height);
        media_.innerHTML = '';
        var test_img_h = size.h;
        test_img_h = preload_image.height;
        if (test_img_h < size.h) {
            $('#media_').addClass('media_');
        } else {
            $('#media_').removeClass('media_');
        }
        setTimeout(function() {
            media_.appendChild(preload_image);
            $('#img_').css({
                'height': (test_img_h > size.h) ? (size.h - 8) + "px" : "auto",
                'width' : 'auto'
                //'height' : 'auto'
            });
            previewShareShow();
        }, 100);
    };
    preload_image.onerror = function(e) {
        console_log("Failed preview_image function. Error: ");
        console_log(e);
        console_log("--------------------------------------");
        //console_log(url);
        tryDownloadIt(url);
    };
    preload_image.src = url;
}

/**
 * @param {string} type
 * @returns {*}
 */
function create_media_element(url, type)
{
    var on_error = function(e)
    {
        console_log("Failed preview_media function.");
        console_log(e);
        tryDownloadIt(url);
    };

    if (type == 'audio')
    {
        var audio_element = document.getElementById('audio_') || document.createElement("audio");
        audio_element.id = "audio_";
        audio_element.controls = true;
        audio_element.hidden = false;
        audio_element.autoplay = true;
        audio_element.hidden = false;
        audio_element.onerror = on_error;

        return audio_element;
    }

    if (type == 'video')
    {
        var video_element = document.getElementById('video_') || document.createElement("video");
        video_element.id = "video_";
        video_element.controls = true;
        video_element.hidden = false;
        video_element.autoplay = true;
        video_element.hidden = false;
        video_element.onerror = on_error;

        return video_element;
    }

    return false;
}

/**
 * @param {string} url
 * @param {object} size
 */
function preview_audio(url, size)
{
    var media_ = document.getElementById("media_");

    var audio_element = create_media_element(url, 'audio');
    audio_element.src = url;

    //https://developer.mozilla.org/ru/docs/Web/Guide/Events/Media_events
    //audio_element.oncanplay = function() {
        var $media__ = $('#media_');
        if (!$media__.hasClass('is-loaded')) {
            media_.innerHTML = '';
            media_.appendChild(audio_element);
            $media__.addClass('media_').addClass('is-loaded');
            previewShareShow();
        }
    //};
}

/**
 * @param {string} url
 * @param {object} size
 */
function preview_video(url, size)
{
    var media_ = document.getElementById("media_");

    var video_element = create_media_element(url, 'video');
    video_element.src = url;

    //https://developer.mozilla.org/ru/docs/Web/Guide/Events/Media_events
    //video_element.oncanplay = function() {
        var $media__ = $('#media_');
        if (!$media__.hasClass('is-loaded')) {
            media_.innerHTML = '';
            media_.appendChild(video_element);
            $('#video_').css({
                'height': (size.h - 8) + "px",
                //'width' : '100%',
            });
            $media__.addClass('is-loaded');
            previewShareShow();
        }
    //};
}

/**
 *
 */
function afterClosePreview()
{
    STOP_RENDER_PDF = true;
    //alert('closePreview');
}

/********** ********** *********/
$(document).ready(function() {

});