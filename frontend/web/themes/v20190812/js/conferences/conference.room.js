var video_checkbox = document.querySelector('#local-cam-checkbox');
var audio_checkbox = document.querySelector('#local-mic-checkbox');
var $participant_list = $('#remote-video');
var $main_video = $('#main-video');
var $main_video_stream = $('#main-video-stream');
var lock_scroll_participant = false;
var main_video_height = 200;
var gallery_video_height = 200;
var timeoutOnResize;
var isInFullScreen;
var elementFullScreen;
var $saveParentBeforeFullScreen;
var autoFullScreenEnabled = false;
var VIEW_SINGLE  = 'single';
var VIEW_GALLERY = 'gallery';
var CURRENT_VIEW_MODE = VIEW_GALLERY;

document.fullscreenEnabled =
    document.fullscreenEnabled ||
    document.mozFullScreenEnabled ||
    document.documentElement.webkitRequestFullScreen;

var onFullScreenChange =  function(e){
    if (elementFullScreen) {
        var $el_video = $(elementFullScreen).find('video').first();
        $el_video.removeClass('full-screen');
    }

    isInFullScreen = (document.fullscreenElement && document.fullscreenElement !== null) ||
        (document.webkitFullscreenElement && document.webkitFullscreenElement !== null) ||
        (document.mozFullScreenElement && document.mozFullScreenElement !== null) ||
        (document.msFullscreenElement && document.msFullscreenElement !== null);

    elementFullScreen =
        document.fullscreenElement ||
        document.webkitFullscreenElement ||
        document.mozFullScreenElement ||
        document.msFullscreenElement;

    if (!isInFullScreen) {
        $(document).find('.full-screen-participant-video, .full-screen').each(function () {
            setKeyAsOff($(this));
        });
    }
    //console.log(elementFullScreen);
};

// Событие об изменениии режима
document.addEventListener("webkitfullscreenchange", onFullScreenChange);
document.addEventListener("mozfullscreenchange",    onFullScreenChange);
document.addEventListener("msfullscreenchange",     onFullScreenChange);
document.addEventListener("fullscreenchange",       onFullScreenChange);

/**
 * @param element
 */
function requestFullScreen(element)
{
    if (autoFullScreenEnabled) {
        if (element.requestFullscreen) {
            element.requestFullscreen();
        } else if (element.mozRequestFullScreen) {
            element.mozRequestFullScreen();
        } else if (element.webkitRequestFullScreen) {
            element.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
        }
    } else {
        var $this = $(element);
        $saveParentBeforeFullScreen = $this.parent();
        if ($this.hasClass('main-conference-container')) {
            $this.addClass('container-full-screen');
            $(window).resize();
        } else {
            //console_log(element);
            var el_full_screen = document.createElement('div');
            el_full_screen.id = 'temporary-full-screen-element';
            el_full_screen.classList = "container-full-screen temporary-container-full-screen";
            document.body.appendChild(el_full_screen);
            el_full_screen.appendChild(element);
            var $el_video = $this.find('video').first();
            $el_video.addClass('full-screen');
            if (CURRENT_VIEW_MODE == VIEW_GALLERY) {
                $this.css({ height: '' });
                $el_video.css({ height: '' });
            }
        }
        elementFullScreen = element;
        isInFullScreen = true;
    }
}

/**
 * @param element
 */
function exitFullScreen(element)
{
    if (autoFullScreenEnabled) {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    } else {
        var $this = $(element);
        if ($this.hasClass('main-conference-container')) {
            $this.removeClass('container-full-screen');
        } else {
            if ($saveParentBeforeFullScreen && $saveParentBeforeFullScreen.length) {
                ($saveParentBeforeFullScreen)[0].appendChild(element);
            }
            var $el_video = $this.find('video').first();
            $el_video.removeClass('full-screen');
            $('#temporary-full-screen-element').remove();
        }
        $(window).resize();
        elementFullScreen = null;
        isInFullScreen = false;
    }
}

/**
 *
 */
function initVoiceVideoButton()
{
    var $video = $('#video-device');
    var $voice = $('#voice-device');
    if (video_checkbox.checked) {
        setKeyAsOn($video);
    } else {
        setKeyAsOff($video);
    }

    if (audio_checkbox.checked) {
        setKeyAsOn($voice);
    } else {
        setKeyAsOff($voice);
    }

    $('.tooltip2').remove();
}

/**
 *
 */
function unlockShareScreenBtn()
{
    setKeyAsOn($('#share-screen-btn'));
}

/**
 *
 */
function lockShareScreenBtn()
{
    setKeyAsOff($('#share-screen-btn'));
}

/**
 *
 * @param direction
 */
function scrollParticipantList(direction)
{
    if (!lock_scroll_participant) {
        lock_scroll_participant = true;
        var speed = 500;
        var participant_video_width = $participant_list.find('video').first().width();
        var leftPos = $participant_list.scrollLeft();
        if (!participant_video_width) {
            //participant_video_width = 150;
        }

        if (direction == 'left') {
            participant_video_width = -1 * participant_video_width;
        }

        $participant_list.animate({scrollLeft: leftPos - participant_video_width}, speed, function () {
            lock_scroll_participant = false;
        });
    }
}

/**
 *
 */
function initMainVideoHeight()
{
    var delta_height = 0;
    var doc_width = $(window).width();
    var delta_manual = 110;
    if (doc_width <= 540) {
        delta_manual = 60;
    }
    if (isInFullScreen) {
        delta_manual = -53;
        if (!autoFullScreenEnabled) {
            delta_manual = -53;
        }
    }
    $(document).find('.delta-height-div').each(function() {
        delta_height += $(this).height();
    });
    delta_height = (delta_height + delta_manual) * (-1);
    main_video_height = $(window).height() + delta_height;

    if (elementFullScreen) {
        if ($(elementFullScreen).hasClass('main-video-stream-div')) {
            $main_video.addClass('full-screen');
            return void(0);
        }
        if ($(elementFullScreen).hasClass('carousel-video-streams')) {
            var $participant_video = $(elementFullScreen).find('video').first();
            $participant_video.addClass('full-screen');
            return void(0);
        }
    }

    $main_video.removeClass('full-screen');
    $main_video.css({ height: main_video_height + 'px' });
    return void(0);
}

/**
 *
 */
function initGalleryHeight()
{
    var delta_height = 0;
    var doc_width = $(window).width();
    var delta_manual = 92;
    if (doc_width <= 540) {
        delta_manual = 30;
    }
    if (isInFullScreen) {
        delta_manual = -80;
        if (!autoFullScreenEnabled) {
            delta_manual = -80;
        }
    }
    $(document).find('.delta-height-div').each(function() {
        delta_height += $(this).height();
    });
    delta_height = (delta_height + delta_manual) * (-1);
    gallery_video_height = $(window).height() + delta_height;

    if (elementFullScreen) {
        if ($(elementFullScreen).hasClass('carousel-video-streams')) {
            var $participant_video = $(elementFullScreen).find('video').first();
            $participant_video.addClass('full-screen');
            return void(0);
        }
    }

    $participant_list.css({ height: gallery_video_height + 'px', 'min-height': gallery_video_height + 'px' });
    return void(0);
}

/**
 *
 */
function initGalleryVideoHeight()
{
    var video_rate = 1.333;
    var conteinerWidth  = $participant_list.width();
    var conteinerHeight = $participant_list.height();

    if (conteinerWidth > 230) {
        var currentVideoWidth = 'auto';
        var currentVideoHeight = ((conteinerWidth - 20) / 2) / video_rate;
    } else {
        currentVideoHeight = (conteinerWidth - 20) / video_rate;
    }

    if (currentVideoHeight > conteinerHeight) {
        currentVideoHeight = conteinerHeight - 10;
    }

    var $list = $participant_list.find('.carousel-video-streams');
    if ($list.length == 1) {
        currentVideoHeight = Math.min((conteinerHeight - 20), (conteinerWidth / video_rate));
    }
    if ($list.length == 2 && (conteinerWidth - (conteinerHeight/3) <= conteinerHeight)) {
        currentVideoHeight = (conteinerHeight / 2) - 5;
        if ($.client.browser == 'safari') {
            currentVideoHeight = (conteinerHeight / 2) - 5;
        }
        if (isInFullScreen && autoFullScreenEnabled) {
            currentVideoHeight -= 60;
        }
    }

    if ($participant_list.hasClass('gallery-mode')) {
        $list.each(function() {
            var $this = $(this);
            $this.css({ height: currentVideoHeight + 'px' });
            $this.find('video').first().css({ height: (currentVideoHeight - 6) + 'px' });
        });
    }
}

/**
 *
 */
function initControlsKey()
{
    /**/
    var leave_enable = getUrlVars()['leave_enable'];
    if (typeof leave_enable != 'undefined') {
        leave_enable = parseInt(leave_enable);
        if (leave_enable == 0) {
            $('.exit-room').hide();
        } else {
            $('.exit-room').show();
        }
    } else {
        $('.exit-room').show();
    }

    /**/
    var screenshare_enable = getUrlVars()['screenshare_enable'];
    if (typeof screenshare_enable != 'undefined') {
        screenshare_enable = parseInt(screenshare_enable);
        if (screenshare_enable == 0) {
            $('.share-screen').hide();
        } else {
            $('.share-screen').show();
        }
    }

    /**/
    if (checkIsMobile()) {
        $('.only-for-mobile').show();
        $('.only-for-desktop').hide();
    } else {
        $('.only-for-mobile').hide();
        $('.only-for-desktop').show();
    }

    $('.full-screen').show();
}

/**
 *
 */
function initViewMode()
{
    if ($participant_list.data('view-mode') == VIEW_GALLERY) {
        CURRENT_VIEW_MODE = VIEW_GALLERY;
        Client.switchToGalleryMode();
    } else {
        CURRENT_VIEW_MODE = VIEW_SINGLE;
        Client.switchToSpeakerMode();
    }
}

/**
 *
 */
$(document).ready(function() {

    autoFullScreenEnabled = document.fullscreenEnabled;
    //console_log($.client);
    if ($.client.browser == 'safari') {
        autoFullScreenEnabled = false;
    }
    //autoFullScreenEnabled = false;

    /**/
    initVoiceVideoButton();

    /**/
    initControlsKey();

    /**/
    if (typeof Client != 'undefined') {
        Client.main();
    }

    /**/
    initViewMode();

    /**/
    if (CURRENT_VIEW_MODE == VIEW_SINGLE) {
        /**/
        createNiceScroll('#remote-video', true, false);

        /**/
        initMainVideoHeight();
    } else {
        /**/
        createNiceScroll('#remote-video', false, true);

        /**/
        initGalleryHeight();
        initGalleryVideoHeight();

        setTimeout(function () {
            initGalleryHeight();
            initGalleryVideoHeight();
        }, 1000);
    }

    /**/
    $(window).on('resize', function(e) {
        clearTimeout(timeoutOnResize);
        timeoutOnResize = setTimeout(function() {

            if (CURRENT_VIEW_MODE == VIEW_SINGLE) {
                initMainVideoHeight();
            } else {
                initGalleryHeight();
                initGalleryVideoHeight();
            }

        }, 400);
    });

    /**/
    $(document).on('click', '.full-screen', function(event) {
        if (isInFullScreen) {
            setKeyAsOff($(this));
            exitFullScreen($('#main-conference-container-div')[0]);
        } else {
            setKeyAsOn($(this));
            requestFullScreen($('#main-conference-container-div')[0]);
        }
    });

    /**/
    $(document).on('click', '.full-screen-participant-video', function(event) {
        event.stopPropagation();
        var $this = $(this);
        var elFS = ($this.parent())[0];

        if (isInFullScreen) {
            if (elementFullScreen && $(elementFullScreen).hasClass('main-conference-container')) {
                exitFullScreen(elementFullScreen);
                setTimeout(function() {
                    setKeyAsOn($this);
                    requestFullScreen(elFS);
                    //$main_video.css({ height: '100%', width: '100%' });
                }, 500);
            } else {
                setKeyAsOff($this);
                exitFullScreen(elFS);
            }
        } else {
            setKeyAsOn($this);
            requestFullScreen(elFS);
            //$main_video.css({ height: '100%', width: '100%' });
        }
    });

    /**/
    $(document).on('click', '.participant-mic', function(event) {
        event.stopPropagation();

        /**/
        if ($(this).hasClass('is-main')) {
            var $nextMic = $participant_list.find('[data-peer-id="' + $(this).data('peer-id') +'"]').first();
        } else {
            var $nextMic = $main_video_stream.find('[data-peer-id="' + $(this).data('peer-id') + '"]').first();
        }

        /**/
        if (Client.isSubscribedToTrack($(this).data('peer-id'), $(this).data('media-tag'))) {
            Client.unsubscribeFromTrack($(this).data('peer-id'), $(this).data('media-tag'));
            setKeyAsOff($(this));
            setKeyAsOff($nextMic);
        } else {
            Client.subscribeToTrack($(this).data('peer-id'), $(this).data('media-tag'));
            setKeyAsOn($(this));
            setKeyAsOn($nextMic);
        }
    });

    /**/
    $(document).on('click', '.carousel-video-streams', function() {
        Client.setManualSpeaker($(this).data('id'));
    });

    /**/
    $(document).on('click', '.slick-next-participant', function() {
        scrollParticipantList('left');
    });

    /**/
    $(document).on('click', '.slick-prev-participant', function() {
        scrollParticipantList('right');
    });

    /**/
    $(document).on('click', '#video-device', function() {
        video_checkbox.click();
        initVoiceVideoButton();
    });

    /**/
    $(document).on('click', '#voice-device', function() {
        audio_checkbox.click();
        initVoiceVideoButton();
    });

    /**/
    $(document).on('click', '#share-screen-btn', function () {
        if ($(this).hasClass('on')) {
            Client.startScreenshare();
        } else {
            Client.stopScreenshare();
        }
    });

});
