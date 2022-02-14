(function () {
    'use strict';

    $(function(){

        // Variables
        let min1101 = window.matchMedia('(min-width: 1101px)'),
            min601 = window.matchMedia('(min-width: 601px)'),
            min741 = window.matchMedia('(min-width: 741px)'),
            max1200 = window.matchMedia('(max-width: 1200px)'),
            max1000 = window.matchMedia('(max-width: 1000px)'),
            max440 = window.matchMedia('(max-width: 440px)'),

            $win = $(window),
            $doc = $(document),
            $body = $('body'),
            $page = $('.page'),
            $pageHeader = $('.js-page-header'),
            $menu = $('.js-main-menu'),
            $menuBtn = $('.js-main-menu-btn'),
            $menuOverlay = $('.menu-overlay');


        /*
         *
         * form select
         *
         * */
        initSelectFields();

        /*
         *
         * sliders
         *
         * */
        let $priceSlider = $('.js-pricing-slider');
        $priceSlider.on('setPosition', function () {
            let $this = $(this);
            if(min601.matches){
                $this.find('.slick-slide').height('auto');
                let slickTrack = $this.find('.slick-track');
                let slickTrackHeight = $(slickTrack).height();
                $this.find('.slick-slide').css('height', slickTrackHeight + 'px');
            }
        });
        $priceSlider.each(function () {
            let $slickIndividual = $(this);
            $slickIndividual.slick({
                dots: false,
                arrows: true,
                speed: 650,
                slidesToShow: 3,
                useTransform: true,
                useCss: true,
                prevArrow: $slickIndividual.parents('.slider-wrap').find('.slider-nav__item--prev'),
                nextArrow: $slickIndividual.parents('.slider-wrap').find('.slider-nav__item--next'),
                responsive: [
                    {
                        breakpoint: 941,
                        settings: {
                            slidesToShow: 2,
                            initialSlide: 1
                        }
                    },
                    {
                        breakpoint: 601,
                        settings: {
                            slidesToShow: 1,
                            initialSlide: 2,
                            adaptiveHeight: true
                        }
                    }
                ]
            });
        });

        $('.editor-area table').each(function () {
            $(this).wrap('<div class="table-wrap"><div class="table-wrap__inner">');
        });

        /*
          *
          * animated items
          *
          * */
        let $items = $('.animated-item'),
            itemsOffset = 100;

        if(max440.matches){
            itemsOffset = 50;
        }
        //$items.each(function () {
        //    let $this = $(this);
        //    if($this.hasClass('animated-now')){
        //        itemsOffset = 0;
        //    }
        //    $this.viewportChecker({
        //        offset: itemsOffset,
        //        repeat: false,
        //        classToAdd: 'visible-item',
        //        callbackFunction: function(elem, action){
        //            //$this.addClass('visible-item');
        //        },
        //        scrollHorizontal: false
        //    });
        //});


        /*
          *
          * youtube
          *
          * */
        function getYoutubeVideo($wrapper,id,scr,parent_src, autorun){
            if(!id) id = $wrapper.prop('id');

                //$this.append('<img src="http://i.ytimg.com/vi/' + this.id + '/sddefault.jpg" class="img-responsive">');
            if(scr){
                $wrapper.append($('<div class="youtube-play"><div/>'));
                $wrapper.css({'background-image': 'url(https://img.youtube.com/vi/' + id + '/maxresdefault.jpg)'});
            }
            if(parent_src){
                $wrapper.parent().css({'background-image': 'url(https://img.youtube.com/vi/' + id + '/maxresdefault.jpg)'});
            }
            if(autorun){
                let iframe_url = "https://www.youtube.com/embed/" + id + "?autoplay=1&autohide=1";
                if ($wrapper.data('params')) iframe_url += '&' + $wrapper.data('params');
                let iframe = $('<iframe/>', {'frameborder': '0', 'allowfullscreen': '1', 'src': iframe_url, 'width': $wrapper.width(), 'height': $wrapper.height()});
                $wrapper.append(iframe);
            }

        }


        $('.youtube').each(function() {
            let $this = $(this),
                id = $this.prop('id');
            getYoutubeVideo($this,id,1,1);
            $(document).delegate('#' + $this.attr('id'), 'click', function () {
                let iframe_url = "https://www.youtube.com/embed/" + id + "?autoplay=1&autohide=1";
                if ($this.data('params')) iframe_url += '&' + $this.data('params');
                let iframe = $('<iframe/>', {'frameborder': '0', 'allowfullscreen': '1', 'src': iframe_url, 'width': $this.width(), 'height': $this.height()});
                $(this).replaceWith(iframe);
            });
        });

        $(document).delegate('.fullscreen', 'click', function() {
            let iframe_url = "https://www.youtube.com/embed/" + this.id + "?autoplay=1&autohide=1";
            if ($this.data('params')) iframe_url+='&'+$this.data('params');
            let iframe = $('<iframe/>', {'frameborder': '0', 'allowfullscreen':'1','src': iframe_url, 'width': $this.width(), 'height': $this.height() });
            $(this).replaceWith(iframe);
        });


        init();

        function init() {
            if(max1200.matches){
                let $lng = $('.language'),
                    $lngList = $lng.find('.dropdown-menu');
                $lng.on('click', function () {
                    let $this = $(this);
                    if($lngList.hasClass('opened')){
                        $this.removeClass('active');
                        $lngList.removeClass('opened');
                    }else{
                        $this.addClass('active');
                        $lngList.addClass('opened');
                    }
                });
                $doc.bind('mouseup touchend', function (e){
                    if($lngList.hasClass('opened')
                        && !$lngList.is(e.target)
                        && $lngList.has(e.target).length === 0 ){
                        $lng.removeClass('active');
                        $lngList.removeClass('opened');
                    }
                });
            }
        }

        $win.resize(function () {
            init();
        });


        $doc.bind('mouseup touchend', function (e){

        });

        /*
         *
         * fixed menu
         *
         * */
        let lastY = $win.scrollTop();
        $win.scroll(function () {
            let currY = $win.scrollTop(),
                y = (currY > lastY) ? 'down' : ((currY === lastY) ? 'none' : 'up'),
                $this = $(this);

            if(min741.matches) {

                if ($this.scrollTop() > 200) {
                    $pageHeader.addClass('fixed');
                    $page.addClass('has-fixed-menu');

                }
                else {
                    $pageHeader.removeClass('fixed');
                    $page.removeClass('has-fixed-menu');
                }
                if ($this.scrollTop() > 600) {
                    if (y === 'up') {
                        $pageHeader.addClass('show');
                        $page.addClass('has-opened-fixed-menu');
                    } else {
                        $pageHeader.removeClass('show');
                        $page.removeClass('has-opened-fixed-menu');
                    }

                }else{
                    $pageHeader.removeClass('show');
                    $page.removeClass('has-opened-fixed-menu');
                }

                if($this.scrollTop() < 400){
                    $pageHeader.removeClass('show').removeClass('fixed');
                    $page.removeClass('has-opened-fixed-menu').removeClass('has-fixed-menu');
                }

            }else{
                if(!$body.hasClass('has-overlay')){
                    if ($this.scrollTop() > 400) {
                        $pageHeader.addClass('fixed');
                        $page.addClass('has-fixed-menu');
                    }
                    else {
                        $pageHeader.removeClass('fixed');
                        $page.removeClass('has-fixed-menu');
                    }
                    if ($this.scrollTop() > 600) {

                        if (y === 'up') {
                            $pageHeader.addClass('show');
                            $page.addClass('has-opened-fixed-menu');
                        } else {
                            $pageHeader.removeClass('show');
                            $page.removeClass('has-opened-fixed-menu');
                        }

                    }else{
                        $pageHeader.removeClass('show');
                        $page.removeClass('has-opened-fixed-menu');
                    }
                }
                if($this.scrollTop() < 400){
                    $pageHeader.removeClass('show').removeClass('fixed');
                    $page.removeClass('has-opened-fixed-menu').removeClass('has-fixed-menu');
                }
            }
            lastY = currY;
        });


        /*
         *
         * user menu
         *
         * */


        initDropDown();


        function closeMainMenu() {
            $menuBtn.removeClass('active');
            $menu.removeClass('opened');
            $body.removeClass('has-overlay');
            $body.removeClass('has-opened-menu');
            $('.menu-overlay').fadeOut(300, function () {
                $('.menu-overlay').remove();
            });
        }


        $menuBtn.on('click', function () {
            let $this = $(this);

            if($menu.hasClass('opened')){
                closeMainMenu();
            }else{
                $menu.addClass('opened');
                $this.addClass('active');
                $.when(
                    $body
                        .addClass('has-overlay')
                        .find('.page-header').append('<div class="menu-overlay"></div>')
                ).then(function(){
                    $('.menu-overlay').fadeIn(300);
                });
            }
        });

        $doc.mouseup(function (e){
            if ($('.menu-overlay').is(e.target)) {
                closeMainMenu();
            }
        });

        let $secMenuBtn = $('.js-sec-menu-btn'),
            $secMenu = $('.js-sec-menu'),
            $secMenuBtn2 = $('.js-sec-menu-btn-2'),
            $secMenu2 = $('.js-sec-menu-2');

        function closeSecMenu() {
            $secMenuBtn.removeClass('active');
            $secMenu.removeClass('opened');
        }

        $secMenuBtn.on('click', function (e) {
            let $this = $(this);
            if($this.hasClass('active')){
                closeSecMenu();
            }else{
                $this.addClass('active');
                $secMenu.addClass('opened');

            }
        });

        $secMenuBtn2.on('click', function (e) {
            let $this = $(this);
            if($this.hasClass('active')){
                $this.removeClass('active');
                $secMenu2.removeClass('opened');
            }else{
                $this.addClass('active');
                $secMenu2.addClass('opened');

            }
        });

        $doc.bind('mouseup touchend', function (e){
            if($secMenu.hasClass('opened')
                && !$secMenu.is(e.target)
                //&& !$('.hamburger').is(e.target)
                && !$secMenuBtn.find('span').is(e.target)
                && $secMenu.has(e.target).length === 0 ){
                closeSecMenu();
            }
        });






        /*
       *
       * popups
       *
       * */

        $body.append('<style>.fancybox-active .page-header.fixed{padding-right:' + scrollbarWidth() + 'px;}</style>');

        $('.js-open-popup').fancybox({
            animationDuration: 350,
            slideClass: "popup-wrap",
            //animationEffect: 'material',
            btnTpl: {
                close:
                    '<button type="button" data-fancybox-close="" class="fancybox-button fancybox-close-small" title="Закрыть"><svg xmlns="http://www.w3.org/2000/svg" version="1" viewBox="0 0 24 24"><path d="M13 12l5-5-1-1-5 5-5-5-1 1 5 5-5 5 1 1 5-5 5 5 1-1z"></path></svg></button>',
                smallBtn:
                '<button type="button" data-fancybox-close="" class="fancybox-button fancybox-close-small" title="Закрыть"><svg xmlns="http://www.w3.org/2000/svg" version="1"' +
                ' viewBox="0 0 24 24"><path d="M13 12l5-5-1-1-5 5-5-5-1 1 5 5-5 5 1 1 5-5 5 5 1-1z"></path></svg></button>'
            }
        });

        $doc.delegate('.js-open-form', 'click', function () {
            let $this = $(this),
                tab = $this.data('tab') - 1;
            let modal = (typeof $this.data('modal') != 'undefined')
                ? parseBool($this.data('modal'))
                : false;
            let has_nicescroll = parseBool($($this.data('src')).data('has-nicescroll'));
            let close_callback = (typeof $($this.data('src')).data('close-callback') != 'undefined')
                ? $($this.data('src')).data('close-callback')
                : false;

            if(tab !== undefined){
                let $auth = $('#auth-popup'),
                    $tabs = $auth.find('li');
                $tabs.removeClass('active');
                $tabs.eq(tab).trigger('click');
            }
            if (!$this.data('no-close-other-fancy')) {
                $.fancybox.close(true);
            }
            $.fancybox.open({
                src  : $this.data('src'),
                //type : 'inline',
                opts : {
                    autoFocus: false,
                    autoCenter: true,
                    animationDuration: 350,
                    slideClass: "popup-form",
                    animationEffect: 'material',
                    showCloseButton: false,
                    smallBtn:false,
                    touch: false,
                    scrolling: false,
                    buttons: [],
                    modal: modal,
                    afterClose: function() {
                        if (has_nicescroll) {
                            removeNiceScroll(null);
                        }
                        if (typeof window[close_callback] == 'function') {
                            window[close_callback]();
                        }
                    }
                }
            });
            return false;
        });

        $doc.delegate('.js-close-popup', 'click', function () {
            $.fancybox.close();
        });


        /*
         *
         * tabs
         *
         * */
        $('.js-tabs').on('click', 'li:not(.active)', function() {
            var current_form_id = $(this).data('current-form-id');
            var replace_form_id = $(this).data('replace-form-id');
            var $current_form_id_user_email = $('#' + current_form_id + '-user_email');
            var $current_form_id_password   = $('#' + current_form_id + '-password');
            if ($current_form_id_user_email.length && $current_form_id_password.length) {
                if ($current_form_id_user_email.val().length) {
                    $('#' + replace_form_id + '-user_email').val($current_form_id_user_email.val());
                }
                if ($current_form_id_password.val().length) {
                    $('#' + replace_form_id + '-password').val($current_form_id_password.val());
                }
            }

            $(this).addClass('active').siblings().removeClass('active')
                .parents('.tabs-wrap').find('.box').eq($(this).index()).fadeIn(300).addClass('visible').siblings('.box').hide().removeClass('visible');
        });

        $('.js-param-tabs').on('click', 'li:not(.active)', function () {
            let newUrl = '',
                $this = $(this),
                param = $this.data('tab');

            let before_function = (typeof $this.data('before-function') != 'undefined')
                ? $this.data('before-function')
                : false;
            if (typeof window[before_function] == 'function') {
                window[before_function]();
            }


            let tab_location = (typeof $this.data('location') != 'undefined')
                ? $this.data('location')
                : false;
            if (tab_location) {
                window.location.href = tab_location;
                return false;
            }

            $this
                .addClass('active')
                .siblings()
                .removeClass('active')
                .parents('.tabs-wrap')
                .find('.box')
                .eq($(this).index())
                .fadeIn(300)
                .addClass('visible')
                .siblings('.box')
                .hide()
                .removeClass('visible');
            newUrl += ('?tab=' + param);

            let callback_function = (typeof $this.data('callback-function') != 'undefined')
                ? $this.data('callback-function')
                : false;
            if (typeof window[callback_function] == 'function') {
                window[callback_function]();
            }

            history.replaceState(null, null, newUrl);
        });


        //Липкие кнопки
        $(window).scroll(function () {
            if ($(this).scrollTop() > 750) {
                $('.js-fixed-btn').fadeIn(650)

            } else {
                $('.js-fixed-btn').fadeOut(650);
            }
        });

        /*
         *
         * scroll top
         *
         * */
        $('.js-scroll-top').on('click',function(){
            $('html, body').animate({scrollTop : 0},800);
            return false;
        });

        /*
         *
         * scroll to
         *
         * */
        $('.js-scroll-to').on('click', function () {
            let target = $(this).attr('href'),
                destination = $(target).offset().top;
            $('html, body').animate({scrollTop: destination},1100);
            return false;
        });

        /*
         *
         * tooltip
         *
         * */
        if(min1101.matches){
            $('.js-tooltip').hover(function(){
                let title = $(this).attr('title');
                $(this).data('tooltip-text', title).removeAttr('title');
                $('<p class="tooltip"></p>')
                    .text(title)
                    .appendTo('body')
                    .fadeIn(300);
            }, function() {
                $(this).attr('title', $(this).data('tooltip-text'));
                $('.tooltip').fadeOut(300, function () {
                    $(this).remove();
                })
            }).mousemove(function(e) {
                let mousex = e.pageX + 20;
                let mousey = e.pageY + 10;
                $('.tooltip')
                    .css({ top: mousey, left: mousex })
            });
        }


        $('.js-open-hidden-tr ').on('click', function () {
            let $this = $(this),
                $hiddenTr = $this.parents('tr').siblings('.hidden-tr');
            if($hiddenTr.hasClass('opened')){
                $hiddenTr.removeClass('opened');
                $this.removeClass('active');
            }else{
                $hiddenTr.addClass('opened');
                $this.addClass('active');
            }

        });

        $('.js-close-inform').on('click', function () {
            $(this).parent().hide();
        });

    });

})();

/**
 *
 */
function animateImages() {
    let $items = $('.animated-item'),
        itemsOffset = 100,
        max440 = window.matchMedia('(max-width: 440px)');

    if (max440.matches) {
        itemsOffset = 50;
    }
    $items.each(function () {
        let $this = $(this);
        if ($this.hasClass('animated-now')) {
            itemsOffset = 0;
        }
        $this.viewportChecker({
            offset: itemsOffset,
            repeat: false,
            classToAdd: 'visible-item',
            callbackFunction: function (elem, action) {
                //$this.addClass('visible-item');
            },
            scrollHorizontal: false
        });
    });
}
/**
 *
 */
function initDropDown()
{
    let $doc = $(document),
        min1101 = window.matchMedia('(min-width: 1101px)'),
        max1000 = window.matchMedia('(max-width: 1000px)');

    let $userMenuWrap = $('.user-menu-wrap'),
        $userMenu = $('.user-menu'),
        $dropDown = $('.js-dropdown');
    if(min1101.matches) {

        let intervalID1;
        $dropDown.hover(
            function () {
                let $this = $(this),
                    $dropList = $this.find('.js-droplist');
                intervalID1 = setTimeout(
                    function () {
                        $this.addClass('active');
                        $dropList
                            .fadeIn(200)
                            .addClass('opened');

                    }, 300);
            },
            function () {
                let $this = $(this),
                    $dropList = $this.find('.js-droplist');
                setTimeout(function () {
                    $this.removeClass('active');
                    $dropList
                        .removeClass('opened')
                        .fadeOut(200);

                }, 300);
                clearTimeout(intervalID1);
            }
        );
    }

    if(max1000.matches){
        $dropDown.on('click', function () {
            let $this = $(this),
                $dropList = $this.find('.js-droplist');
            if($dropList.hasClass('opened')){
                $this.removeClass('active');
                $dropList
                    .removeClass('opened')
                    .fadeOut(200);
            }else{
                $this.addClass('active');
                $dropList
                    .fadeIn(150)
                    .addClass('opened');
            }
        });
    }


    $doc.bind('mouseup touchend', function (e){
        if($dropDown.hasClass('active')
            && !$dropDown.is(e.target)
            && $dropDown.has(e.target).length === 0 ){
            $dropDown.removeClass('active');
            $('.js-droplist').fadeOut(200).removeClass('opened');
        }
    });
};

/**
 *
 */
function initSelectFields()
{
    $('.js-select').each(function () {
        let $this = $(this),
            $parent = $this.parents('.select-wrap');
            $this.select2({
                minimumResultsForSearch: -9999999,
                dropdownAutoWidth : true,
                width: 'auto',
                dropdownParent: $parent,
                placeholder: {
                    id: '-9999999',
                    text: $this.data('placeholder')
                }
            });
    });
}

/**
 *
 * @returns {number}
 */
function scrollbarWidth() {
    // создадим элемент с прокруткой
    var div = document.createElement('div');

    div.style.overflowY = 'scroll';
    div.style.width = '50px';
    div.style.height = '50px';

// при display:none размеры нельзя узнать
// нужно, чтобы элемент был видим,
// visibility:hidden - можно, т.к. сохраняет геометрию
    div.style.visibility = 'hidden';

    document.body.appendChild(div);
    var scrollWidth = div.offsetWidth - div.clientWidth;
    document.body.removeChild(div);
    return scrollWidth;
}

