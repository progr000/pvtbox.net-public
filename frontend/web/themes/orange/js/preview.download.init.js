$(document).ready(function() {
    var w_h = $('body').innerHeight() - 20;
    var w_w = $('body').innerWidth() - 10;

    //console_log('w=' + w_w + '; h=' + w_h);
    //console_log(typeof start_preview);

    $('#btn-controls').hide();

    $('#download-loading').css({ 'height': w_h + "px", 'width': "auto", 'padding-top': parseInt(w_h/2) - 50 + 'px' }).show();
    //console_log($('#download-loading'));

    // workaround for iOS, because browsers in the system cannot save Blob to file
    var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent);

    (check_browser_for_websocket() && check_browser_for_webrtc() && !iOS)
        ? start_download()
        : download_by_proxy_node();
});


function ___showDownloadButton(by_proxy)
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
    $('#download-from-node').show();
    $('#btn-controls').show();
    $('#media_').hide();
}
