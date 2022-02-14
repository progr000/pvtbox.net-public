/* -------------------------------------------*/
var nodesOnline = [];
var timeForShowNoNodesOnline = 6000;
var _LANGUAGE = 'en';
var _LANG_URL = '';
var createLogOfUserAlerts = 1;
/* -------------------------------------------*/


/**
 * Show/hide div with alert message about all nodes offline
 * @param onlycheck boolean
 * @returns {boolean}
 */
function checkNodesOnline(onlycheck)
{
    //console_log(nodesOnline.length);
    if (nodesOnline.length > 0) {
        if (!onlycheck) {
            if ($('#notif-show-for').length) {
                $('#alert-no-nodes-online' + $('#notif-show-for').attr('data-show-for')).addClass('hidden');
            }
            $('#alert-no-nodes-online').addClass('hidden');
        }
        //$('#manager-list-folder').find('.dropdown-toggle').addClass('enabled');
        $('.admin-panel-select-folder').removeClass('notActive');
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
        return false;
    }
}



/* -------------------------------------------*/
window.onload = function(){

    //form.init();
    //popUp.init();
    select.init();
    //table.init();
    tabBlock.init();
    //inform.init();

    //$('.scrollbar-program').scrollbar();
};

/* inform -------------------------------------*/
var inform = {
    notActive: 'notActive',
    cont: '.inform-manager',
    tim: null
};

inform.init = function(){

    if(!$(this.cont).length) return;

    inform.tim = setTimeout(function(){

        clearTimeout(inform.tim);
        $(inform.cont).fadeOut(400, function(){

            $(inform.cont).addClass(inform.notActive);
        });

    }, 3500);
};





/* tabBlock -----------------------------------*/
var tabBlock = {
    active: 'active',
    button: '.tabBlock-list li',
    contentBox: '.tabBlock-content__box'
};

tabBlock.init = function(){

    this.events();
};

tabBlock.events = function(){

    $('body').on('click', this.button, function(event){

        $(this).parents('ul').find('li').removeClass(tabBlock.active);
        $(this).addClass(tabBlock.active);

        if ($(this).attr('data-tab')) {
            history.pushState({}, '', '?tab=' + $(this).attr('data-tab'));
            if ($(this).attr('data-function')) {
                var functionName = $(this).attr('data-function');
                //console_log(functionName);
                //console_log(typeof window[functionName]);
                if (typeof window[functionName] == "function") {
                    window[functionName]($(this));
                }
            }
            //history.replaceState({}, '', '?tab=' + $(this).attr('data-tab'));
        }

        var ind = $(this).index();

        $(tabBlock.contentBox).removeClass(tabBlock.active);
        $(tabBlock.contentBox).eq(ind).addClass(tabBlock.active);

        table.act();
    });
};





/* table --------------------------------------*/
var table = {
    bl: '.table, .workspace',

    headCont: '.table__head-cont, .workspace__head-cont',
    bodyCont: '.table__body-cont, .workspace__body-cont',

    headBox: '.table__head-box, .workspace__head-box',
    bodyBox: '.table__body-box, .workspace__body-box',

    sizeBox: null,
    xBox: null,
    tim: null,
    x_head: null,
    x_body: null
};

table.init = function(){

    this.events();
};

table.events = function(){

    table.act();
    $(window).resize(function(){
        table.act();
    });

    $('#settings').on('show.bs.modal', function (e) {

        table.tim = setTimeout(function(){

            table.act();
            clearTimeout(table.tim);

        }, 500);
    });
};

table.act = function(){

    for(var i=0; i<$(this.bl).length; i++){

        this.sizeBox = $(this.bl).eq(i).find(this.headBox).length;

        this.x_head = $(this.bl).eq(i).find(this.headCont).innerWidth();
        this.x_body = $(this.bl).eq(i).find(this.bodyCont).innerWidth();
        $(this.bl).eq(i).find(this.headCont).css('paddingRight', this.x_head - this.x_body);

        for(var j=0; j<this.sizeBox; j++){

            this.xBox = $(this.bl).eq(i).find(this.bodyBox).eq(j).outerWidth();
            $(this.bl).eq(i).find(this.headBox).eq(j).outerWidth(this.xBox);
        }
    }
};





/* select -------------------------------------*/
var select = {};
select.init = function(){

    $('.selectpicker').selectpicker({
        //size: ($(this).hasAttribute('data-size-height') ? parseInt($(this).attr('data-size-height')) : 4)
        //size: 4
    });
};





/* popUp --------------------------------------*/
var popUp = {
    active: 'active'
};

popUp.init = function(){

    this.events();
};

popUp.events = function(){

    $('#entrance').on('show.bs.modal', function (event) {

        var button = $(event.relatedTarget);
        var recipient = button.data('whatever');

        if(recipient == 'log'){
            $('.modal-body .btn-radio').eq(0).click();
        }
        if(recipient == 'reg'){
            $('.modal-body .btn-radio').eq(1).click();
        }
    });

    $('#getLink').on('hide.bs.modal', function (event) {

        $('#getLink .tab-pane').removeClass(popUp.active);
        $('#getLink .tab-pane').eq(0).addClass(popUp.active);
    });
};





/* form ---------------------------------------*/
var form = {
    active: 'active',
    bl: '.form-block',
    button: '.btn-radio',
    tab: '.form-box'
};

form.init = function(){

    this.events();
};

form.events = function(){

    $('body').on('click', this.button, function(event){

        if(!$(this).hasClass(form.active)){

            var ind = $(this).index();

            $(this).parents(form.bl).find(form.tab).removeClass(form.active);
            $(this).parents(form.bl).find(form.tab).eq(ind).addClass(form.active);
        }
    });
};

/**
 *
 */
function imageAfterLoader()
{
    $(document).find('.img-after-loader').each(function () {
        if ($(this)[0].hasAttribute('data-src-after')) {
            $(this).attr( 'src', $(this).attr('data-src-after') );
        }
        $(this).show();
    });
}

/**
 *
 */
function replacePictureContainerEdgeSafari()
{
    if ($.client.browser == 'edge' || $.client.browser == 'safari' ) {
        $('#container-picture').find('source').each(function () {
            $(this).attr( 'srcset', $(this).attr('data-src-edge-safari') );
        });
    } else {
        $('#container-picture').find('source').each(function () {
            $(this).attr( 'srcset', $(this).attr('data-src-other') );
        });
    }
}

/**
 *
 */
function changeSizeOfDoc()
{
    //alert(navigator.userAgent);
    //var screenWidth = screen.width;
    var screenWidth = $(window).width();

    if ($.client.browser == 'edge' || $.client.browser == 'safari' || $.client.browser == 'msie') {
        var container_home_img = "/themes/orange/images/home-2x.jpg";
        if (screenWidth <= 1440) {
            container_home_img = "/themes/orange/images/home-2x_1440.jpg";
        }
        if (screenWidth <= 1360) {
            container_home_img = "/themes/orange/images/home-2x_1360.jpg";
        }
        if (screenWidth <= 1280) {
            container_home_img = "/themes/orange/images/home-2x_1280.jpg";
        }
        if (screenWidth <= 1024) {
            container_home_img = "/themes/orange/images/home-2x_1024.jpg";
        }
        if (screenWidth <= 800) {
            container_home_img = "/themes/orange/images/home-2x_800.jpg";
        }
        //alert(screenWidth);
        $('#container-home-img').attr('src', container_home_img);
    }


    if (screenWidth < 800) {
        $('.row-big-cloud').addClass('hide');
        $('.row-small-cloud').removeClass('hide');
    } else {
        $('.row-big-cloud').removeClass('hide');
        $('.row-small-cloud').addClass('hide');
    }
}

/**
 *
 * @param bytes integer
 * @param decimal_digits integer
 * @param force_size string
 * @param space_between string
 * @returns string
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

/** */
Array.prototype.remove = function(value) {
    var idx = this.indexOf(value);
    if (idx != -1) {
        // Второй параметр - число элементов, которые необходимо удалить
        return this.splice(idx, 1);
    }
    return false;
};

Array.prototype.unset = function(value) {
    return this.remove(value);
};

Array.prototype.in_array = function(value) {
    var idx = this.indexOf(value);
    if (idx != -1) {
        return true;
    } else {
        return false;
    }
};

Array.prototype.in_array = function(p_val) {
    for(var i = 0, l = this.length; i < l; i++)  {
        if(this[i] == p_val) {
            return true;
        }
    }
    return false;
};

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

/*
 String.prototype.replaceArray = function(find, replace) {
 var replaceString = this;
 for (var i = 0; i < find.length; i++) {
 replaceString = replaceString.replace(find[i], replace[i]);
 }
 return replaceString;
 };

 //For global replace you could use regex:

 String.prototype.replaceArray = function(find, replace) {
 var replaceString = this;
 var regex;
 for (var i = 0; i < find.length; i++) {
 regex = new RegExp(find[i], "g");
 replaceString = replaceString.replace(regex, replace[i]);
 }
 return replaceString;
 };

 //To use the function it'd be similar to your PHP example:

 var textarea = $(this).val();
 var find = ["<", ">", "\n"];
 var replace = ["&lt;", "&gt;", "<br/>"];
 textarea = textarea.replaceArray(find, replace);
 */

/**
 *
 */
function initToolTip()
{
    $('.masterTooltip').css({cursor: 'pointer'});
    var Touch = typeof window.ontouchstart != "undefined";
    if (Touch) {
        $('.masterTooltip').on('touchstart', function (e) {
            console.log(e);
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
        var obj;
        $('.masterTooltip').hover(function () {
            //console_log($(this).attr('title'));
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
                    /*
                     setTimeout(function () {
                     //obj.attr('title', obj.data('tipText'));
                     $(this).attr('title', $(this).data('tipText'));
                     $('.tooltip2').remove();
                     }, 4000);
                     */
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
    if (typeof TIMEZONE_OFFSET_SECONDS != 'undefined') {
        $.ajax({
            type: 'get',
            url: _LANG_URL + '/site/set-timezone-offset?timezone_offset_seconds=' + TIMEZONE_OFFSET_SECONDS,
            dataType: 'json',
            statusCode: {
                200: function(response) {
                    console_log(response);
                },
                500: function(response) {
                    console_log(response);
                }
            }
        });
    }
}

/**
 * Pretty-Confirm-Window
 *
 * @param funct_yes
 * @param funct_no
 * @param question
 * @param button_yes
 * @param button_no
 */
function prettyConfirm(funct_yes, funct_no, question, button_yes, button_no) {
    if (question && typeof question == 'string' && $.trim(question) != '') {
        $('#pretty-confirm-question-text').html(question);
    }
    if (button_no && typeof button_no == 'string' && $.trim(button_no) != '') {
        $('#button-confirm-no').val(button_no);
    }
    if (button_yes && typeof button_yes == 'string' && $.trim(button_yes) != '') {
        $('#button-confirm-yes').val(button_yes);
    }
    if (typeof funct_yes == 'function') {

        /* Навешиваем событие на нажатие YES */
        $('#button-confirm-yes')
            .off("click")
            .on('click', function() {
                $('#pretty-confirm-modal').modal('hide');
                $('#button-confirm-yes').off("click");
                $('#button-confirm-no').off("click");
                funct_yes();
            });

        /* Навешиваем событие на нажатие NO */
        if (typeof funct_no == 'function') {
            $('#button-confirm-no')
                .off("click")
                .on('click', function() {
                    $('#pretty-confirm-modal').modal('hide');
                    $('#button-confirm-yes').off("click");
                    $('#button-confirm-no').off("click");
                    funct_no();
                });
        } else {
            $('#button-confirm-no')
                .off("click")
                .on('click', function() {
                    $('#pretty-confirm-modal').modal('hide');
                    $('#button-confirm-yes').off("click");
                    $('#button-confirm-no').off("click");
                });
        }

        /* Показываем попап конфирм */
        $('#pretty-confirm-modal').modal({"show": true});
    }
}

/**
 * @param text
 */
function prettyAlert(text)
{
    $('#button-alert-ok')
        .off("click")
        .on('click', function() {
            $('#pretty-alert-modal').modal('hide');
            $('#pretty-alert-modal-text').html('');
        });
    $('#pretty-alert-modal-text').html(text);
    $('#pretty-alert-modal').modal({"show": true});
}

/**  */
$(document).ready(function() {

    var $body_doc = $('body');
    /** User alerts log */
    var body_cloua = $body_doc.attr('data-cloua');
    if (typeof body_cloua != 'undefined') {
        createLogOfUserAlerts = parseInt(body_cloua);
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
    replacePictureContainerEdgeSafari();
    changeSizeOfDoc();
    $(window).resize(function(){
        changeSizeOfDoc();
    });

    /** */
    sendTimeZoneOffset();

    if ($.cookie('cookie_polices_accept') != 1) {
        $('#cookie-policies-layer').slideDown(400);
    }

    $(document).on('click', '.cookie-layer__button', function () {
        $(this).parent().parent().slideUp(400);
        $.cookie('cookie_polices_accept', "1", { expires: 365, path: '/' });
    });

    if ($.cookie('respect_privacy_accept') != 1) {
        var $respect_privacy_badge = $('#respect-privacy-badge');
        var max_width_respect_privacy_badge = 767;
        if ($respect_privacy_badge.length && $respect_privacy_badge[0].hasAttribute('data-max-width-for-show')) {
            max_width_respect_privacy_badge = $respect_privacy_badge.attr('data-max-width-for-show');
        }
        if ($(window).width() > max_width_respect_privacy_badge) {
            $('#respect-privacy-badge').slideDown(200);
        } else {
            $('#respect-privacy-layer').slideDown(400);
        }
    }

    $(document).on('click', '.respect-privacy-close', function () {
        $('#respect-privacy-badge').slideUp(200);
        $('#respect-privacy-layer').slideUp(400);
        $.cookie('respect_privacy_accept', "1", { expires: 365, path: '/' });
    });

    $(document).on('submit', '#form-signup, #form-signup2, #form-login, #form-reset, #form-contact, #form-change-name, #form-profile, #form-changePassword, #reset-password-form', function() {
        var form_id = $(this).attr('id');
        //alert(form_id);
        var $button = $('#' + form_id).find('input[type=submit]').first();
        var $img_progress = $('#' + form_id).find('.img-progress').first();
        //var $signup_button_form1 = $('#signup-button-form1');
        if ($button.hasClass('btn-notActive')) {
            return false;
        } else {
            $button.addClass('btn-notActive').hide();
            $img_progress.show();
            $('#' + form_id).find('input[type=text], input[type=email], input[type=password], input[type=checkbox]').each(function () {
                if ($(this).attr('type') == "checkbox") {
                    $(this).parent().addClass('label-notActive')
                } else {
                    //alert($(this).attr('name'));
                    $(this).attr('readonly', "readonly").addClass('input-notActive');
                }

                $(document).on('click', '#label-accept-rules', function () {
                    $('#accept-rules').prop('checked', true);
                    $(this).addClass('active');
                    return false;
                });
                $(document).on('click', '#label-accept-rules2', function () {
                    $('#accept-rules2').prop('checked', true);
                    $(this).addClass('active');
                    return false;
                });
            });
            return true;
        }
    });

    /** реинит всплывающих подсказок после выполнения pjax */
    $(document).on('pjax:complete' , function(event) {
        initToolTip();
    });

    /** Инициация системы всплывающих подсказок */
    initToolTip();

    /** Пост-загрузка некоторых изображений */
    imageAfterLoader();
});
