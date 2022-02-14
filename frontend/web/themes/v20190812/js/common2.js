/* -------------------------------------------*/
var nodesOnline = [];
var timeForShowNoNodesOnline = 6000;
var _LANGUAGE = 'en';
var _LANG_URL = '';
var createLogOfUserAlerts = 1;
var _USER_EMAIL = null;
/* -------------------------------------------*/

/**
 *
 */
function hideSiteLoader()
{
    var $preloader = $('#site-loader-div'),
        $spinner   = $preloader.find('.spinner');
    $spinner.fadeOut();
    $preloader.delay(500).fadeOut('slow', function() {
        document.body.classList.remove('not-loaded-body');
    });
    if (!$preloader.length) {
        document.body.classList.remove('not-loaded-body');
    }
    setTimeout(function() {
        //document.body.classList.remove('not-loaded-body');
    }, 300);
}

/**
 * Show/hide div with alert message about all nodes offline
 * @param {boolean} onlycheck
 * @returns {boolean}
 */
function checkNodesOnline(onlycheck)
{
    //console_log(nodesOnline.length);
    if (nodesOnline.length > 0) {
        if (!onlycheck) {
            if ($('#notif-show-for').length) {
                $('#alert-no-nodes-online' + $('#notif-show-for').attr('data-show-for')).addClass('hidden').hide();
            }
            $('#alert-no-nodes-online').addClass('hidden').hide();
        }
        //$('#manager-list-folder').find('.dropdown-toggle').addClass('enabled');
        $('.admin-panel-select-folder').removeClass('notActive');

        if (typeof reCalcDeltaHeight == 'function') { reCalcDeltaHeight(); }
        if (typeof resizeElfinder == 'function') { resizeElfinder(); }
        if (typeof reInitNiceScrollElfinder == 'function') { reInitNiceScrollElfinder(true); }

        return true;
    } else {
        if (!onlycheck) {
            if ($('#notif-show-for').length) {
                var $alert_div = $('#alert-no-nodes-online' + $('#notif-show-for').attr('data-show-for'));
            } else {
                var $alert_div = $('#alert-no-nodes-online');
            }
            $alert_div
                .removeClass('hidden')
                .show();
            var checkIsFmPage = $('#elfinder').length;
            if (!checkIsFmPage) {
                $alert_div.delay(timeForShowNoNodesOnline).fadeOut();
            }
        }

        $('.admin-panel-select-folder').addClass('notActive');

        if (typeof reCalcDeltaHeight == 'function') { reCalcDeltaHeight(); }
        if (typeof resizeElfinder == 'function') { resizeElfinder(); }
        if (typeof reInitNiceScrollElfinder == 'function') { reInitNiceScrollElfinder(true); }

        return false;
    }
}

/**
 *
 * @param {int} bytes
 * @param {int} decimal_digits
 * @param {string} force_size
 * @param {string} space_between
 * @returns {string}
 */
function file_size_format(bytes, decimal_digits, force_size, space_between)
{
    if (typeof space_between == 'undefined') { space_between = ' '; }
    //console_log(typeof decimal_digits);
    if (typeof decimal_digits == 'undefined') { decimal_digits = 2; }
    bytes = parseInt(bytes);
    decimal_digits = parseInt(decimal_digits);
    //console_log(bytes);
    var units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    var power = units.indexOf(force_size);
    //console_log(power);
    if (power < 0) { power = (bytes > 0) ? Math.floor(Math.log(bytes) / Math.log(1024)) : 0; }
    //console_log(power);
    return  (bytes / Math.pow(1024, power)).toFixed(decimal_digits) + space_between + units[power];
    //return  Math.round( (bytes / Math.pow(1024, power))*100 ) / 100 + '' + units[power];
}

/**
 * @param {mixed} value
 * @returns {boolean}
 */
function parseBool(value){
    if (typeof(value) === 'string'){
        value = value.trim().toLowerCase();
    }
    switch(value){
        case true:
        case "true":
        case 1:
        case "1":
        case "on":
        case "yes":
            return true;
        default:
            return false;
    }
}

/**
 * @param {mixed} value
 * @returns {*}
 */
Array.prototype.remove = function(value) {
    var idx = this.indexOf(value);
    if (idx != -1) {
        // Второй параметр - число элементов, которые необходимо удалить
        return this.splice(idx, 1);
    }
    return false;
};

/**
 * @param {mixed} value
 * @returns {*}
 */
Array.prototype.unset = function(value) {
    return this.remove(value);
};

/**
 * @param {mixed} value
 * @returns {boolean}
 */
Array.prototype.in_array = function(value) {
    var idx = this.indexOf(value);
    if (idx != -1) {
        return true;
    } else {
        return false;
    }
};

/**
 * @param search
 * @param replace
 * @returns {string}
 */
String.prototype.replaceAll = function(search, replace){
    return this.split(search).join(replace);
};

/**
 * @param {mixed} p_val
 * @returns {boolean}
 */
Array.prototype.in_array = function(p_val) {
    for(var i = 0, l = this.length; i < l; i++)  {
        if(this[i] == p_val) {
            return true;
        }
    }
    return false;
};

/**
 * @returns {number}
 */
String.prototype.hashCode = function() {
    var hash = 0, i, chr;
    if (this.length === 0) return hash;
    for (i = 0; i < this.length; i++) {
        chr   = this.charCodeAt(i);
        hash  = ((hash << 5) - hash) + chr;
        hash |= 0; // Convert to 32bit integer
    }
    return hash;
};

/**
 *
 */
function initToolTip()
{
    var $masterTooltip =  $('.masterTooltip');

    $masterTooltip.css({cursor: 'pointer'});
    var Touch = typeof window.ontouchstart != "undefined";
    if (Touch) {
        $masterTooltip.on('touchstart', function (e) {
            $('.tooltip2').remove();
            if ($(this)[0].hasAttribute('title')) {
                var touches = e.originalEvent.touches || [{}],
                    mousex = touches[0].pageX + 8 || 20,
                    mousey = touches[0].pageY - 8 || 20,
                    title = $(this).attr('title');
                if (title.length) {
                    $('<p class="tooltip2"></p>')
                        .text(title)
                        .appendTo('body')
                        .fadeIn('slow');
                    $('.tooltip2')
                        .css({top: mousey, left: mousex});

                    setTimeout(function () {
                        $('.tooltip2').fadeOut('slow', function () {
                            $('.tooltip2').remove();
                        })
                    }, 6000);
                }
            }
        });
    } else {
        $masterTooltip.hover(function () {
            // Hover over code
            if ($(this)[0].hasAttribute('title')) {
                var title = $(this).attr('title');
                //obj = $(this);
                $(this).data('tipText', title).removeAttr('title');
                if (title.length) {
                    $('<p class="tooltip2"></p>')
                        .html(title)
                        .appendTo('body')
                        .fadeIn('slow');
                }
            }
        }, function () {
            // Hover out code
            $(this).attr('title', $(this).data('tipText'));
            $('.tooltip2').remove();
        }).mousemove(function (e) {
            var mousex = e.pageX + 5; //Get X coordinates
            var mousey = e.pageY + 5; //Get Y coordinates
            $('.tooltip2')
                .css({top: mousey, left: mousex});
        });
    }
}

/**
 *
 */
function sendTimeZoneOffset()
{
    if (typeof IS_GUEST != 'undefined' && !IS_GUEST) {
        if (typeof TIMEZONE_OFFSET_SECONDS != 'undefined') {
            $.ajax({
                type: 'get',
                url: _LANG_URL + '/user/set-timezone-offset?timezone_offset_seconds=' + TIMEZONE_OFFSET_SECONDS,
                dataType: 'json',
                statusCode: {
                    200: function (response) {
                        console_log(response);
                    },
                    500: function (response) {
                        console_log(response);
                    }
                }
            });
        }
    }
}

/**
 * Pretty-Confirm-Window
 *
 * @param {function} funct_yes
 * @param {function} funct_no
 * @param {string} question
 * @param {string} button_yes
 * @param {string} button_no
 * @param {boolean} show_close_x
 */
function prettyConfirm(funct_yes, funct_no, question, button_yes, button_no, show_close_x) {
    /* Устанавливаем текст вопроса для конфирма */
    if (question && typeof question == 'string' && $.trim(question) != '') {
        $('#pretty-confirm-question-text').html(question);
    }
    /* Устанавливаем текст для кнопки ДА */
    if (button_no && typeof button_no == 'string' && $.trim(button_no) != '') {
        $('#button-confirm-no').val(button_no);
    }
    /* Устанавливаем текст для кнопки НЕТ */
    if (button_yes && typeof button_yes == 'string' && $.trim(button_yes) != '') {
        $('#button-confirm-yes').val(button_yes);
    }
    if (typeof funct_yes == 'function') {

        /* Навешиваем событие на нажатие YES */
        $('.button-confirm-yes')
            .off("click")
            .on('click', function() {
                $('#button-confirm-yes').off("click");
                $('#button-confirm-no').off("click");
                funct_yes();
            });

        /* Навешиваем событие на нажатие NO */
        if (typeof funct_no == 'function') {
            $('.button-confirm-no')
                .off("click")
                .on('click', function() {
                    $('#button-confirm-yes').off("click");
                    $('#button-confirm-no').off("click");
                    funct_no();
                });
        } else {
            $('.button-confirm-no')
                .off("click")
                .on('click', function() {
                    $('#button-confirm-yes').off("click");
                    $('#button-confirm-no').off("click");
                });
        }

        /* если show_close_x true тогда показать крестик закрытия окна */
        if (typeof show_close_x != 'undefined' && show_close_x) {
            $('#confirm-close-x').show();
        } else {
            $('#confirm-close-x').hide();
        }

        /* Показываем попап конфирмации */
        $("#trigger-pretty-confirm-modal").trigger( "click" );
    }
}

/**
 * @param {string} text
 * @param {function} funct_ok
 * @param {boolean} show_close_x
 */
function prettyAlert(text, funct_ok, show_close_x)
{
    /* Навешиваем событие на нажатие OK если оно есть */
    if (funct_ok && typeof funct_ok == 'function') {
        $('.button-alert-ok')
            .off("click")
            .on('click', function() {
                $('#button-confirm-yes').off("click");
                $('#button-confirm-no').off("click");
                $('#pretty-alert-modal-text').html('');
                funct_ok();
            });
    } else {
        $('.button-alert-ok')
            .off("click")
            .on('click', function() {
                $('#button-confirm-yes').off("click");
                $('#button-confirm-no').off("click");
                $('#pretty-alert-modal-text').html('');
            });
    }

    /* если show_close_x true тогда показать крестик закрытия окна */
    if (show_close_x) {
        $('#alert-close-x').show();
    } else {
        $('#alert-close-x').hide();
    }

    /* Устанавливаем текст для алерта */
    $('#pretty-alert-modal-text').html(text);

    /* Показываем попап алерта */
    $("#trigger-pretty-alert-modal").trigger( "click" );
}

/**
 * @returns {boolean}
 */
function checkIsMobile()
{
    var navigators = navigator.userAgent.toLowerCase();
    if ((navigators.indexOf('android') >= 0) || (navigators.indexOf('iphone') >= 0)) {
        return true;
    }
    return false;
}

/**
 *
 * @param {string} target
 * @param {boolean} horizontal
 * @param {boolean} vertical
 */
function createNiceScroll(target, horizontal, vertical)
{
    if (typeof jQuery.nicescroll == 'undefined') {
        return;
    }

    if (!checkIsMobile()) {
        var niceScrollColor = "#CDCDCD",
            niceScrollWidth = "8px",
            niceScrollZIndex = "inherit";
        var $nc = $(target).getNiceScroll();
        if ($nc.length) {
            $nc.hide();
            $nc.remove();
        }
        $(target).niceScroll({
            cursorcolor: niceScrollColor,
            cursorwidth: niceScrollWidth,
            zindex: niceScrollZIndex,
            horizrailenabled: horizontal,
            verticalenabled: vertical
        });
    } else {
        $(target).css({
            'overflow-x': (horizontal ? 'auto' : 'hidden'),
            'overflow-y': (vertical   ? 'auto' : 'hidden'),
        });
    }
}

/**
 *
 * @param {string|null} target
 */
function reInitNiceScroll(target)
{
    if (typeof jQuery.nicescroll == 'undefined') {
        return;
    }

    if (!checkIsMobile()) {
        if (target) {
            $(target).getNiceScroll().resize();
        } else {
            $('.scrollbar-program-vertical').getNiceScroll().resize();
            $('.scrollbar-program-horizontal').getNiceScroll().resize();
            $('.scrollbar-program').getNiceScroll().resize();
        }
    }
}

/**
 *
 * @param {string|null} target
 */
function removeNiceScroll(target)
{
    if (typeof jQuery.nicescroll == 'undefined') {
        return;
    }

    if (target) {
        $(target).getNiceScroll().remove();
    } else {
        $('.scrollbar-program-vertical').getNiceScroll().remove();
        $('.scrollbar-program-horizontal').getNiceScroll().remove();
        $('.scrollbar-program').getNiceScroll().remove();
    }
}


/**
 *
 */
(function($) {
    $.each(['show', 'hide'], function(i, ev) {
        var el = $.fn[ev];
        $.fn[ev] = function() {
            this.trigger(ev);
            return el.apply(this, arguments);
        };
    });
})(jQuery);

/**
 * @param {array} scripts
 * @param {function} callback
 */
function reloadAnyScripts(scripts, callback)
{
    for (var i=0; i < scripts.length; i++){
        var script = document.createElement('script');
        script.src = scripts[i];
        script.async = false; // to order
        if (i == scripts.length-1){
            if (script.readyState) {
                script.onreadystatechange = function(){
                    if (this.readyState == "loaded" || this.readyState == "complete"){
                        if (typeof callback == 'function') {
                            callback();
                        }
                    }
                }
            } else {
                script.onload = function(){
                    if (typeof callback == 'function') {
                        callback();
                    }
                }
            }
        }
        document.body.appendChild(script);
    }
}

/**
 * @param {string} name
 * @returns {string}
 */
function encodeName(name)
{
    name = encodeURI(name);
    return name.replace(/[^a-zA-Z0-9]/gi, '-');
    //return name.replaceAll('%', '-').replaceAll('@', '-').replaceAll('.', ',');
}

/**
 *
 */
(function($) {
    $.fn.blink = function(options) {
        var defaults = {
            delay: 500,
            count: 5,
            className: "ui-selected",
        };
        var options = $.extend(defaults, options);

        return this.each(function() {
            var obj = $(this);

            var interval;
            var cnt = $(obj).attr('data-blink-count');
            if (cnt == undefined) {
                cnt = 0;
            }
            //console_log(cnt);

            interval = setInterval(function () {
                if (cnt < options.count) {
                    if ($(obj).hasClass(options.className)) {
                        $(obj).removeClass(options.className);
                    }
                    else {
                        cnt++;
                        //console_log(cnt);
                        $(obj).attr('data-blink-count', cnt);
                        $(obj).addClass(options.className);
                    }
                } else {
                    $(obj).removeClass(options.className);
                    $(obj).removeAttr('data-blink-count');
                    clearInterval(interval);
                }
            }, options.delay);

        });
    }
}(jQuery))

/**
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

/**
 * @param $obj
 */
function setKeyAsOff($obj)
{
    $obj
        .attr('data-on', "0")
        .attr('title', $obj.data('title-off'))
        .data('tipText', $obj.data('title-off'))
        .addClass('off')
        .removeClass('on');
}

/**
 * @param $obj
 */
function setKeyAsOn($obj)
{
    $obj
        .attr('data-on', "1")
        .attr('title', $obj.data('title-on'))
        .data('tipText', $obj.data('title-on'))
        .addClass('on')
        .removeClass('off');
}

/**
 * @param $this
 * @returns {boolean}
 */
function startImgProgressForm($this)
{
    var $button = $this.find('[type=submit]').first();
    var $img_progress = $this.find('.img-progress').first();
    if ($button.hasClass('btn-notActive')) {
        return false;
    } else {
        $button.addClass('btn-notActive').hide();
        $img_progress.show();
        if ($img_progress[0].hasAttribute('data-add-class')) {
            $img_progress.addClass($img_progress.data('add-class'));
        }
        $this.find('input[type=text], input[type=email], input[type=password], input[type=checkbox]').each(function () {
            if ($(this).attr('type') == "checkbox") {
                $(this).parent().addClass('label-notActive')
            } else {
                $(this).attr('readonly', "readonly").addClass('input-notActive');
            }
        });
        return true;
    }
}

/**
 * @param $this
 * @returns {boolean}
 */
function finishImgProgressForm($this)
{
    var $button = $this.find('[type=submit]').first();
    var $img_progress = $this.find('.img-progress').first();
    if ($button.hasClass('btn-notActive')) {
        $button.removeClass('btn-notActive').show();
        $img_progress.hide();
    }
    if ($img_progress[0].hasAttribute('data-add-class')) {
        $img_progress.removeClass($img_progress.data('add-class'));
    }
    $this.find('input[type=text], input[type=email], input[type=password], input[type=checkbox]').each(function () {
        if ($(this).attr('type') == "checkbox") {
            $(this).parent().removeClass('label-notActive')
        } else {
            $(this).removeAttr('readonly').removeClass('input-notActive');
        }
    });
    return true;
}

/**  */
$(document).ready(function() {

    var $body_doc = $('body');
    /** User alerts log */
    var body_cloua = $body_doc.attr('data-cloua');
    if (typeof body_cloua != 'undefined') {
        createLogOfUserAlerts = parseInt(body_cloua);
    }

    /** User email */
    var body_ue = $body_doc.attr('data-user-email');
    if (typeof body_ue != 'undefined') {
        _USER_EMAIL = body_ue;
    }

    /** Lang url */
    var body_lang = $body_doc.attr('lang');
    if (typeof body_lang != 'undefined') {

        _LANGUAGE = body_lang;

        var url = window.location.href;
        var a = $('<a>', { href: url});
        //a.prop('protocol'); a.prop('hostname'); a.prop('search'); a.prop('hash');
        var path = a.prop('pathname');

        var regexp = new RegExp('(^/' + _LANGUAGE + '\$)|(^/' + _LANGUAGE + '/)');
        //console_log(regexp.test(path));
        if (regexp.test(path)) {
            _LANG_URL = '/' + _LANGUAGE;
        }

    }

    /** */
    //sendTimeZoneOffset();

    if ($.cookie('cookie_polices_accept') != 1) {
        $('#cookie-policies-layer').slideDown(400);
    }

    $(document).on('click', '.empty-link, .void-0', function () {
        return false;
    });

    $(document).on('click', '.cookie-layer__button', function () {
        $('#cookie-policies-layer').slideUp(400);
        $.cookie('cookie_polices_accept', "1", { expires: 365, path: '/' });
    });

    if ($.cookie('respect_privacy_accept') != 1) {
        if (!checkIsMobile()) {
            $('#respect-privacy-layer').slideDown(400);
        }
    }

    $(document).on('click', '.respect-privacy-close', function () {
        $('#respect-privacy-layer').slideUp(400);
        $.cookie('respect_privacy_accept', "1", { expires: 365, path: '/' });
    });

    $(document).on('change', '#supportform-subject', function () {
        if ($(this).val() != -1) {
            $("#supportform-subject [value='-1']").remove();
            initSelectFields();
        }
    });

    $(document).on('submit', '.img-progress-form', function() {
        startImgProgressForm($(this));
    });

    /** реинит всплывающих подсказок и всякой другой хуйни после выполнения pjax */
    $(document).on('pjax:complete' , function(event) {

        //reloadAnyScripts([
        //    "/themes/v20190812/js/vendors/select2.min.js",
        //    "/themes/v20190812/js/datepicker/datepicker.min.js",
        //    "/themes/v20190812/js/datepicker/i18n/datepicker.en.js"
        //], function() {
            if (typeof initToolTip == 'function') { initToolTip(); }
            if (typeof setNotificationsAsRead == 'function') { setNotificationsAsRead(); }
            //if (typeof initSelectFields == 'function') { initSelectFields(); }
            //if (typeof initDatepickerFields == 'function') { initDatepickerFields(); }
        //});
    });

    /** Инициация системы всплывающих подсказок */
    initToolTip();
});

