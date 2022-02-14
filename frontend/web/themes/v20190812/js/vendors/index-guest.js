$(document).ready(function() {
    var lastWidth = $(window).width();
    var timeoutIndexOnResize;
    var $body_doc = $('body');

    $(window).on('resize', function(e) {
        clearTimeout(timeoutIndexOnResize);
        timeoutIndexOnResize = setTimeout(function() {
            var currentWidth = $(window).width();
            if ((lastWidth > 768) && currentWidth <= 768) {
                window.location.reload();
            }
            if ((lastWidth <= 768) && currentWidth > 768) {
                window.location.reload();
            }
            lastWidth = currentWidth;
        }, 400);
    });

    $body_doc.find('.other-sections').each(function () {
        if (!$(this).is(":visible")) {
            $(this).remove();
        }
    });

    /** +++ begin Video p2p */
    //console_log($.client);
    //$('body').on('click touchstart scroll', function () {
        const videoElement = document.getElementById('video-gif-object');
        if (videoElement.playing) {
            // video is already playing so do nothing
        }
        else {
            // video is not playing
            // so play video now
            videoElement.play();
        }

        const videoElement2 = document.getElementById('video-p2p-object');
        var $data_src = $('#animation_container');
        if ($.client.browser == 'safari' ||
            $.client.browser == 'msie' ||
            $.client.browser == 'edge' ||
            $.client.os == 'iPhone' ||
            $.client.os == 'iPad')
        {
            videoElement2.src = $data_src.data('mp4');
        } else {
            videoElement2.src = $data_src.data('webm');
        }
        if (videoElement2.playing) {
            // video is already playing so do nothing
        }
        else {
            // video is not playing
            // so play video now
            videoElement2.play();
        }
    //});
    /** --- end Video p2p */
});