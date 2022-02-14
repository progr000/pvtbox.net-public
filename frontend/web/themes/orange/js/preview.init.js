$(document).ready(function() {
    var w_h = $('body').innerHeight() - 20;
    var w_w = $('body').innerWidth() - 10;

    //console_log('w=' + w_w + '; h=' + w_h);
    //console_log(typeof start_preview);

    $(document).on('mouseover', '#media_', function() {
        var test = $(this).getNiceScroll();
        //console_log(test);
        if (!test.length) {
            //console_log('activate nice');
            $(this).niceScroll({cursorcolor: '#CDCDCD', cursorwidth: '8px'});
        } else {
            $(this).getNiceScroll().resize();
        }
        var pre_ = $(this).find('pre').first();
        if (pre_.length) {
            var test_pre = pre_.getNiceScroll();
            if (!test_pre.length) {
                pre_.niceScroll({cursorcolor: '#CDCDCD', cursorwidth: '8px'});
            } else {
                pre_.getNiceScroll().resize();
            }
        }
    });

    $('#btn-controls').hide();

    //$('#preview-loading').css({ 'height': w_h + "px", 'width': "auto", 'padding-top': parseInt(w_h/2) - 50 + 'px' }).show();
    $('#preview-loading').css({
        'position': 'absolute',
        'top': '40%',
        'left': '50%',
        'transform': 'translate(-50%, -50%)'
    }).show();

    // unfortunately the workaround for Firefox was added 26.08.2018,
    //   because there is issue with saving Blob to file
    // Addition on 03.10.2019:
    //   the issue was fixed in Filefox release 60.2.0esr
    var isFirefox = typeof navigator.mozGetUserMedia !== 'undefined';

    (check_browser_for_websocket() && check_browser_for_webrtc())
    //(check_browser_for_websocket() && check_browser_for_webrtc() && !isFirefox)
        ? start_preview()
        : preview_by_proxy_node();
    /*
    (check_browser_for_websocket() && check_browser_for_webrtc())
        ? start_download()
        : download_by_proxy_node();
    ;
    */
    //changeSizeOfContainer();
});

/**
 *
 * @param type
 * @param container_id
 */
function changeSizeOfContainer(type, container_id)
{
    if (!container_id) {
        container_id = '#media_';
    }

    //console_log(type);
    var w_h = $('body').innerHeight() - 5;
    var w_w = $('body').innerWidth() - 5;
    var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    if (iOS) {
        w_w = getUrlVars()['w'] - 50;
        w_h = getUrlVars()['h'] - 50;
    }
    var w_wa = 'auto';
    if (type.indexOf('audio') >= 0) {
        w_h = w_h / 2;
        w_wa = (w_w - 10) + 'px';
    }
    var $media = $(container_id);

    if (type == 'pdf' || type == 'pre') {
        if (iOS) {
            if (w_w < w_h) {
                $media.css({
                    'height': (w_h - 60) + 'px',
                    'width': (w_w - 20) + 'px',
                    'position': 'relative'
                });
                $media.show();
                $media.children().first().css({'height': (w_h - 70) + 'px', 'width': (w_w - 30) + 'px'});
            } else {
                $media.css({
                    'height': (w_h - 60) + 'px',
                    'width': (w_w - 90) + 'px',
                    'position': 'relative'
                });
                $media.show();
                $media.children().first().css({'height': (w_h - 70) + 'px', 'width': (w_w - 100) + 'px'});
            }
        } else {
            $media.css({
                'height': w_h + 'px',
                'width': w_w + 'px',
                'position': 'relative'
            });
            $media.show();

            if (type == 'pre') {
                $media.children().each(function() {
                    $(this).css({
                        'position': 'absolute',
                        'top': '40%',
                        'left': '50%',
                        'transform': 'translate(-50%, -50%)'
                    });
                });
            } else {
                $media.children().first().css({'height': (w_h - 10) + 'px', 'width': (w_w - 10) + 'px'});
            }
        }
    } else {
        //alert(w_w);
        //alert(w_h);
        //console_log('w_w = ' + w_w + '; w_h = ' + w_h);
        $media
            .show();

        $media.children().first().css({
            'position': 'absolute',
            'top': '40%',
            'left': '50%',
            'transform': 'translate(-50%, -50%)'
        });

        if (w_w < w_h) {
            $media.children().first().css({
                'height': w_wa,
                'width': (w_w - 10) + 'px'
            });
        } else {
            var delta = 10;
            if (iOS) { delta = 50; }
            $media.children().first().css({
                'height': (w_h - delta) + 'px',
                'width': w_wa
            });
        }

        //$media.children().first().css({'height': (w_h - 10) + 'px', 'width': (w_w - 10) + 'px'});
        /*
        var elStyle = document.getElementById('previewStyle');
        document.head.removeChild(elStyle);
        var sheet;
        document.head.appendChild(elStyle);
        sheet = elStyle.sheet;
        var idx_rule = sheet.insertRule('#img_ { width: ' + (w_w - 10) + 'px !important; height: ' + (w_h - 10) + 'px !important; }', 0);
        */
    }

    //$media.niceScroll({cursorcolor: '#CDCDCD', cursorwidth: '8px'});
    //$media.find('pre').first().css({'width': (w_w - 10) + 'px'}).niceScroll({cursorcolor: '#CDCDCD', cursorwidth: '8px'});
}

/**
 *
 */
function noPreview()
{
    $("#media_").html('').append("<pre>This file can't be previewed.\nDownload should start automatically.</pre>")
    changeSizeOfContainer('pre');
}

function failPreview()
{
    /*
    $("#media_").html('').append($('#preview-fail-download-start').html());
    changeSizeOfContainer('pre');
    */
    $("#media_").html('').hide();
    $('#preview-fail-download-start').show();
    changeSizeOfContainer('pre', '#info-and-controls');
}

/**
 *
 * @param by_proxy
 */
function showDownloadButton(by_proxy)
{
    /*
    if (by_proxy) {
        $('#btn_download').show();
        $('#download-from-node').hide();
    } else {
        $('#btn_download').hide();
        $('#download-from-node').show();
    }
    */
    $('#preview-fail-download-start').hide();
    $('#download-from-node').show();
    $('#btn-controls').show();
    $('#media_').hide();
    changeSizeOfContainer('pre', '#info-and-controls');
}

/**
 *
 * @returns {Array}
 */
function getUrlVars()
{
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}
