var elfinderInstance = null;
var DropzoneInstance = null;
var deltaListHeight = - 10;
var deltaHeightSearch = 0;
var onCommandsNotShowBalloonNodes = true;
var delta_height = -165;
var alerts_outerHeight = [];
var last_reload_elf = 0;
var search_showed_by_user = false;

var dwl_pos_x = 100;
var dwl_pos_y = 100;

var current_W_W = 0;
var current_W_H = 0;
/**
 *
 * @returns {{pos_elf: (*|{top, left}), w_elf: *, h_elf: *, w_upl: *, h_upl: *}}
 */
function getElfinderParams()
{
    var $elf = $('#elfinder');
    var $upl = $('#upload-dialog');
    var $dwl = $('#download-dialog');
    return {
        pos_elf: $elf.offset(),
        w_elf:   $elf.width(),
        h_elf:   $elf.height(),
        w_upl:   $upl.width(),
        h_upl:   $upl.height(),
        w_dwl:   $dwl.width(),
        h_dwl:   $dwl.height()
    };
}

/**
 * @param file object
 * @returns {boolean}
 */
function checkIsFileSynced(file)
{
    //console_log(file);
    if ("file_uuid" in file) {
        if (!(file.file_uuid === null)) {
            return true;
        }
    }
    elfinderInstance.error('This file is not yet synced with your devices. Can\'t execute any action with it.');
    return false;
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
 */
function scrollForFucckingChromeAndSafari()
{
    /* +++ Этот ебанутый кусок кода для наиебанутейших браузеров */
    //console_log(navigator.userAgent);
    if (!checkIsMobile()) {
        //console_log('is_not_mobile');
        var fucking_navigators = navigator.userAgent.toLowerCase();
        //if (fucking_navigators.indexOf('chrome') >= 0 || fucking_navigators.indexOf('safari') >= 0) {
        if (fucking_navigators.indexOf('chrome') < 0 && fucking_navigators.indexOf('safari') >= 0) {
            //console_log("it's fucking chrome or safari");

            var $elfinder_cwd_wrapper = $('.elfinder-cwd-wrapper');
            $elfinder_cwd_wrapper.getNiceScroll().remove();
            $elfinder_cwd_wrapper.niceScroll({cursorcolor: "#CDCDCD", cursorwidth: "8px", zindex: 900});
            $elfinder_cwd_wrapper.getNiceScroll().hide();
            $elfinder_cwd_wrapper.getNiceScroll().show();

            var $elfinder_navbar = $('.elfinder-navbar');
            $elfinder_navbar.getNiceScroll().remove();
            $elfinder_navbar.niceScroll({cursorcolor: "#CDCDCD", cursorwidth: "8px", zindex: 900});
            $elfinder_navbar.getNiceScroll().hide();
            $elfinder_navbar.getNiceScroll().show();

            //if (fucking_navigators.indexOf('safari') >= 0) {
            if (fucking_navigators.indexOf('chrome') < 0 && fucking_navigators.indexOf('safari') >= 0) {
                //console_log('fucking safari need resize');
                var $elfinder = $('#elfinder').elfinder({resizable: true});
                $elfinder.height($elfinder.height() - 10).resize();
                $elfinder.height($elfinder.height() + 10).resize();
                $elfinder_navbar.height($elfinder_navbar.height() - 10).resize();
                $elfinder_navbar.height($elfinder_navbar.height() + 10).resize();
            }
        }
    }
    /* --- конченные браузеры */
}

function resizeFileName()
{
    var $file_list = $('#elfinder');

    //document.head
    var elStyle = document.getElementById('fileNameStyle');
    //elStyle.empty();
    document.head.removeChild(elStyle);
    var sheet;
    document.head.appendChild(elStyle);
    sheet = elStyle.sheet;

    var w_td = $file_list.find('.elfinder-cwd-view-th-name').first().width() - 10;
    //console_log(w_td);

    //sheet.deleteRule(0);
    var idx_rule = sheet.insertRule('.elfinder .elfinder-cwd-wrapper-list .elfinder-cwd-file .elfinder-cwd-filename { width: ' + w_td + 'px !important; text-overflow: ellipsis !important; overflow: hidden !important; }', 0);

    //sheet.deleteRule(1);
    //console_log('fff = ' + idx_rule);
    /*
    $file_list.find('.elfinder-cwd-filename').each(function() {
        //console_log($(this));
        $(this).attr('style', 'width: ' + w_td + 'px !important');
    });
    */
}

/**
 *
 */
function resizeElfinder()
{
    resizeFileName();

    var $item_search_form = $('.item-search-form');
    if ($(window).width() > 640) {
        $item_search_form.css({ display: "inline-block" });
    } else {
        //$item_search_form.css({ display: "none" });
    }

    var $elfinder = $('#elfinder').elfinder({resizable:true});
    var win_height = $(window).height() + delta_height; //delta;
    //console_log(win_height);
    if ( $elfinder.height() != win_height ) {
        $elfinder.height(win_height).resize();
    }
    /*
    if ($(window).width() <= 500) {
        if ($('.btn-palel--structure').hasClass('btn-palel--structure-list')) {
            //elfinderInstance.exec('view');
            //elfinderInstance.exec('reload');
        }
    }
    if ($(window).width() <= 900) {
        //$('#elfinder .elfinder-navbar').css({ width: "150px" });
    }
    */
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
 * @param e
 */
function showFileContextMenu(coord, target) {

    //console_log(coord);
    //console_log(e.target);
    //e.preventDefault();
    //e.stopPropagation();
    var fm = elfinderInstance;
    var sel = fm.selected();
    var tar = [target.attr('data-target-hash')];

    /* снимим отметки селектед и ховер со всех элементов */
    $('#elfinder').find('.elfinder-cwd-fixheader').first().find('tr').each(function() {
        $(this).removeClass('ui-selected ui-state-hover');
        $(this).find('input:checkbox').first().attr('checked', false);
    });

    /* снимем отметку селектед и ховер с папки UP */
    var cwd2 = fm.cwd();
    $("#" + cwd2.phash)
        .removeClass('ui-selected ui-state-hover')
        .find('input:checkbox')
        .first()
        .attr('checked', false);

    if (sel.in_array(cwd2.phash)) {
        sel.splice(sel.indexOf(cwd2.phash), 1);
    }
    fm.select({selected: sel});

    /* Вызов контекстного меню элемента */
    var deltaX = 75;
    setTimeout(function() {
        fm.trigger('contextmenu', {
            'type'    : 'files',
            'targets' : tar,//(sel.length && sel.in_array(tar[0])) ? sel : tar,
            'x'       : coord.pageX, //e.pageX + deltaX,
            'y'       : coord.pageY //e.pageY
        });

        /* Поставим отметку селектед и ховер на выбранную папку */
        $("#" + tar[0])
            //.addClass('ui-selected ui-state-hover')
            .removeClass('ui-selected')
            .find('input:checkbox')
            .first()
            //.attr('checked', "checked")
            //.prop('checked', true)
            .attr('checked', false)
            .prop('checked', false);

    }, 100);

}

/**
 *
 */
function showSortSattus()
{
    if (elfinderInstance) {
        var type = elfinderInstance.sortType;
        var order = elfinderInstance.sortOrder;
        var folder = elfinderInstance.sortStickFolders;
        //elfinder-button-menu-item-selected elfinder-button-menu-item-selected-desc
        $('#elfinder-sort-menu-list').find('.elfinder-button-menu-item').each(function () {
            $(this)
                .removeClass('elfinder-button-menu-item-selected')
                .removeClass('elfinder-button-menu-item-selected-desc')
                .removeClass('elfinder-button-menu-item-selected-asc');

            var attr_rel = $(this).attr('rel');
            if (attr_rel == type) {
                $(this)
                    .addClass('elfinder-button-menu-item-selected')
                    .addClass('elfinder-button-menu-item-selected-' + order);
            }
            if ($(this).hasClass('elfinder-button-menu-item-separated')) {
                if (folder) {
                    $(this).addClass('elfinder-button-menu-item-selected');
                }
            }
        });
    }
}

/**
 *
 */
function initSortButton()
{
    showSortSattus();

    $('#elfinder-sort-menu-list').hide()
        .on('mouseenter mouseleave', '.elfinder-button-menu-item', function() {
            $(this).toggleClass('hover')
        })
        .on('click', '.elfinder-button-menu-item', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#elfinder-sort-menu-list').hide();

            var folder = elfinderInstance.sortStickFolders;
            var type = $(this).attr('rel') ? $(this).attr('rel') : elfinderInstance.sortType;
            if ($(this).hasClass('elfinder-button-menu-item-separated')) {
                folder = !elfinderInstance.sortStickFolders;
            }
            elfinderInstance.setSort(
                type,
                (type == elfinderInstance.sortType)
                    ? (elfinderInstance.sortOrder == 'asc') ? 'desc' : 'asc'
                    : (type == 'date') ? 'desc' : 'asc', //elfinderInstance.sortOrder,
                folder
            );
            elfinderInstance.prependFolderUpZone();

            showSortSattus();
        });

    $(document).on('click', '.btn-palel--sort', function(e) {
        var elfinder_sort_menu_list = $('#elfinder-sort-menu-list');
        elfinder_sort_menu_list.is(':visible')
            ? elfinder_sort_menu_list.hide()
            : elfinder_sort_menu_list.show();
        e.stopPropagation();
    });
    $(document).click(function(){
        $("#elfinder-sort-menu-list").hide();
    });
}

/**
 *
 * @param elfInst
 */
function toolbarButtonsPrepare(elfInst) {
    $('#elfinderPanel').find('.all-btn-palel').each(function () {
        $(this).addClass('notActive');
    });


    $('.btn-palel--reload').removeClass('notActive');
    $('.btn-palel--view').removeClass('notActive');
    $('.btn-palel--structure').removeClass('notActive');
    $('.btn-palel--sort').removeClass('notActive');
    $('.btn-palel--search').removeClass('notActive');
    //$('.btn-palel--up').removeClass('notActive');

    var selectedfiles = elfInst.selectedFiles(),
        root = elfInst.root(),
        cwd = elfInst.cwd(),
        isRoot = false;
    if (!selectedfiles.length) {
        selectedfiles[0] = cwd;
    }

    if (root && cwd.hash && root != cwd.hash) {
        /*
         * Если текущая папка не корневая (главный контейнер)
         * тогда можно сделать активной кнопку домой
         */
        $('.btn-palel--home').removeClass('notActive');
        $('.btn-palel--up').removeClass('notActive');
    }

    if (nodesOnline.length) {

        /* Если есть какой то текущий каталог и он не удаленный */
        if (cwd && !cwd.file_deleted) {
            $('.btn-uploadFile').removeClass('notActive');
            $('.btn-createFolder').removeClass('notActive');
            $('.btn-palel--upload').removeClass('notActive');
            $('.btn-palel--folder').removeClass('notActive');
        }

        /* проверка какой сейчас текущий каталог */
        if (root && cwd.hash && root != cwd.hash) {
            /*
             * Если текущая папка не корневая (главный контейнер)
             * тогда можно сделать активной кнопку домой
             */
            $('.btn-palel--home').removeClass('notActive');
            $('.btn-palel--up').removeClass('notActive');
        } else {
            /*
             * Если текущая папка это root папка
             * ее нельзя ни удалить ни переименовать ни скопировать
             * это главный контейнер. Заблокируем все эти кнопки
             */
            isRoot = true;
            $('.btn-palel--home').addClass('notActive');
            $('.btn-palel--up').addClass('notActive');
            $('.btn-palel--download').addClass('notActive');
            $('.btn-palel--copy').addClass('notActive');
            $('.btn-palel--cut').addClass('notActive');
            $('.btn-palel--rename').addClass('notActive');
            $('.btn-palel--remove').addClass('notActive');
        }

        /* проверка того выбран (подсвечен) ли сейчас какой то файл или папка */
        if (/*выбран*/
            (selectedfiles.length > 0) &&
            /*и выбран не root*/
            (root != selectedfiles[0].hash) &&
            /*и не удален*/
            !selectedfiles[0].file_deleted) {
            $('.btn-palel--download').removeClass('notActive');
            $('.btn-palel--copy').removeClass('notActive');
            $('.btn-palel--cut').removeClass('notActive');
            $('.btn-palel--rename').removeClass('notActive');
            $('.btn-palel--remove').removeClass('notActive');
        } else {
            $('.btn-palel--download').addClass('notActive');
            $('.btn-palel--copy').addClass('notActive');
            $('.btn-palel--cut').addClass('notActive');
            $('.btn-palel--rename').addClass('notActive');
            $('.btn-palel--remove').addClass('notActive');
        }

        /* Для кнопки доунлоад свои проверки*/
        if (selectedfiles.length == 1 && !selectedfiles[0].file_deleted && selectedfiles[0].locked != 1 && !selectedfiles[0].is_folder) {
            $('.btn-palel--download').removeClass('notActive');
        } else {
            $('.btn-palel--download').addClass('notActive');
        }

        /* Для кнопки ренейм тоже свои проверки*/
        if (selectedfiles.length == 1 && !selectedfiles[0].file_deleted && selectedfiles[0].locked != 1) {
            $('.btn-palel--rename').removeClass('notActive');
        } else {
            $('.btn-palel--rename').addClass('notActive');
        }

        /* если что то есть в буфере копирования */
        var clp = elfInst.clipboard();
        if (clp.length > 0 && checkNodesOnline(true)) {
            $('.btn-palel--paste').removeClass('notActive');
            showPasteSpecialButtons();
        } else {
            $('.btn-palel--paste').addClass('notActive');
            removePasteSpecialButtons();
        }

        /* если есть история перемещения по каталогам */
        if (elfInst.history.canBack()) {
            $('.btn-palel--back').removeClass('notActive');
        } else {
            $('.btn-palel--back').addClass('notActive');
        }

        DropzoneInstance.enable();
    } else {
        DropzoneInstance.disable();
    }

    /* если есть история перемещения по каталогам */
    if (elfInst.history.canBack()) {
        $('.btn-palel--back').removeClass('notActive');
    } else {
        $('.btn-palel--back').addClass('notActive');
    }
}

/**
 *
 */
function showPasteSpecialButtons()
{
    var nav = $('#elfinder').find('.elfinder-navbar').first().is(':visible');
    if (!nav) {
        var $test = $('.elfinder-workzone').find('.paste-buttons').first();
        if (!$test.length) {
            $('.elfinder-workzone').append(
                '<div class="paste-buttons">' +
                    '<a href="javascript:void(0)" class="btn-clipboard-cancel"></a>' +
                    '<a href="javascript:void(0)" class="btn-clipboard-paste"></a>' +
                '</div>'
            );
        }
    }
}

/**
 *
 */
function removePasteSpecialButtons()
{
    if (elfinderInstance) {
        elfinderInstance.clipboard([]);
    }
    $('.elfinder-workzone').find('.paste-buttons').each(function () {
        $(this).remove();
    });
}

/**
 * showShareLink
 * @param f object
 * @param share_link string
 */
function showShareLink(f)
{
    //console_log(share_link);
    if (f.file_shared && f.share_link) {
        return '<a href="' + f.share_link + '" target="_blank" rel="noopener" alt="Link" title="Link" class="link-bunch"></a>';
    } else {
        return '';
    }
}

/**
 * showShareDropMenu
 * @param hash string
 */
function showShareDropMenu(hash)
{
    //console_log($('#shareDropMenu_' + hash).html());
    //$('#buttonDropMenu_' + hash).dropdown();

    if ($('#shareDropMenu_' + hash).hasClass('open')) {
        $('#shareDropMenu_' + hash).removeClass('open');
        $('#buttonDropMenu_' + hash).trigger('hide.bs.dropdown');
    } else {
        $(document).find('.dropdown').each(function() {
            $(this).removeClass('open');
        });

        $('#shareDropMenu_' + hash).addClass('open');
        $('#buttonDropMenu_' + hash).trigger('show.bs.dropdown');
    }

}

/**
 * showShareButton
 * @param f object
 * @returns {string}
 */
function showShareButton(f)
{
    if (!f.file_shared) {
        share = "show";
        unshare = "hide";
    } else {
        share = "hide";
        unshare = "show";
    }
    //console_log(f);
    if (f.is_folder && f.file_parent_id == 0) {
        if (f.cbl_is_owner == 0 && f.file_collaborated) {
            return $.trim($('#tpl-share-link-and-colleague-list').html().replace(/\{([a-z\_]+)\}/g, function (s, e) {
                return f[e];
            }).replace(/\s+/g," ").replace(/>\s+</g, '><'));
        } else {
            return $.trim($('#tpl-share-link-and-collaboration-settings').html().replace(/\{([a-z\_]+)\}/g, function (s, e) {
                return f[e];
            }).replace(/\s+/g," ").replace(/>\s+</g, '><'));
        }
    } else {
        return $.trim($('#tpl-share-link-only').html().replace(/\{([a-z\_]+)\}/g, function (s, e) {
            return f[e];
        }).replace(/\s+/g," ").replace(/>\s+</g, '><'));
    }
}

/**
 * Set Share params in popup
 * @param data object
 */
function setShareParams(data)
{
    var $share_password = $('#share-password');
    var $share_settings_button = $('#share-settings-button');
    var $share_ttl_div = $('#share-ttl-div');
    var $share_password_text = $('.field-share-password').find('input[type=text]').first();
    //console_log(data);
    $('.nav-tabs a[href="#link-get"]').tab('show');
    $('#filesystem-hash').val(data.hash);
    $share_password.parent().removeClass('has-error').find('p').first().html('');
    $('#share-email').val('').parent().removeClass('has-error').find('p').first().html('');
    if (data.payed) {
        $('#info-settings-link-update-to-pro').hide();
        $('#settings-link-update-to-pro').hide();
        $('#share-ttl').prop("disabled", false);
        $share_password
            .prop("readonly", false)
            //.attr('title', $share_password.attr('title_payed'))
            .removeClass('input-notActive');
        $share_password_text
            .prop("readonly", false)
            .removeClass('input-notActive');
        $share_settings_button
            //.prop("disabled", false)
            //.attr('title', $share_settings_button.attr('title_payed'))
            .removeClass('btn-notActive');
        $share_ttl_div.attr('title', $share_ttl_div.attr('title_payed'))
            .removeClass('select-color-gray')
            .addClass('select-color-orange');
    } else {
        $('#info-settings-link-update-to-pro').show();
        $('#settings-link-update-to-pro').show();
        $('#share-ttl').prop("disabled", true);
        $share_password.prop("readonly", true)
            //.attr('title', $share_password.attr('title_unpayed'))
            .addClass('input-notActive');
        $share_password_text
            .prop("readonly", true)
            .addClass('input-notActive');
        $share_settings_button
            //.prop("disabled", true)
            //.attr('title', $share_settings_button.attr('title_unpayed'))
            .addClass('btn-notActive');
        $share_ttl_div
            //.attr('title', $share_ttl_div.attr('title_unpayed'))
            .removeClass('select-color-orange')
            .addClass('select-color-gray');
        /*
        var $button_select = $share_ttl_div.find('.bootstrap-select').first().find('button').first();
        //alert($button_select.attr('title'));
        //$button_select.attr('title', 'ddddddddd');
        $button_select.addClass('masterTooltip').addClass('disabled');
        $share_ttl_div.removeClass('masterTooltip');
        initToolTip();
        $button_select.addClass('disabled');
        */
        var $button_select = $share_ttl_div.find('.bootstrap-select').first().find('button').first();
        $button_select[0].title = "";
        setTimeout(function() { $button_select[0].title = ""; }, 1000);
    }
    if (data.file_shared) {
        $('#share-hash').val(data.share_hash);
        $('#share-link-field').val(data.share_link);
        $('#share-ttl').val(data.share_ttl_info);
        $('#share-ttl').selectpicker('refresh');
        $share_password.val(data.share_password);

        $('.nav-tabs a[href="#link-get-active"]').tab('show');
    } else {
        $('#share-hash').val('');
        $('#share-link-field').val('');
        $('#share-ttl').val(0); // Ссылка бессрочна (TTL_WITHOUTEXPIRY)
        $('#share-ttl').selectpicker('refresh');
        $share_password.val('');

        $('.nav-tabs a[href="#link-get"]').tab('show');
    }
}

/**
 *
 * @param hash
 */
function exec_share(hash)
{
    //console_log(hash);
    var file = elfinderInstance.file(hash);
    showShareDialog(file);
}
/**
 * Show popup for sharing
 * @param file object
 */
function showShareDialog(file)
{
    //console_log(file);
    if (!checkIsFileSynced(file)) {
        return false;
    }

    if (file.is_folder && checkIsFreeLicense(true)) {
        return false;
    }

    var hash = file.hash;

    if (!checkNodesOnline(onCommandsNotShowBalloonNodes)) {
        return false;
    }
    //console_log("showShareDialog: " + hash);
    /** ++ Init share popup*/
    $('#share-create-remove-modal').modal({"show":true});
    $('.nav-tabs a[href="#link-get"]').tab('show');
    $('#filesystem-hash').val(hash);
    $('#share-password').parent().removeClass('has-error').find('p').first().html('');
    $('#share-email').val('').parent().removeClass('has-error').find('p').first().html('');

    $('#info-settings-link-update-to-pro').hide();
    $('#settings-link-update-to-pro').hide();
    $('#share-ttl').prop("disabled", true);
    var $share_ttl_div = $('#share-ttl-div');
    var $button_select = $share_ttl_div.find('.bootstrap-select').first().find('button').first();
    $button_select[0].title = "";
    setTimeout(function() { $button_select[0].title = ""; }, 1000);
    $('#share-password').prop("readonly", true);
    $('#share-settings-button')
        //.prop("disabled", true)
        .addClass('btn-notActive');
    var link = $('#' + hash).find('a.link-bunch:first');
    if (link.length) {
        $('#share-link-field').val(link.attr('href'));
        $('.nav-tabs a[href="#link-get-active"]').tab('show');
    } else {
        $('.nav-tabs a[href="#link-get"]').tab('show');
    }
    /** -- Init share popup */

    $.ajax({
        type: 'get',
        url: _LANG_URL + '/elfind?cmd=shareDialog&target=' + hash,
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true && typeof response.data != 'undefined') {

                    var data = response.data;
                    //$('#share-create-remove-modal').modal({"show":true});
                    setShareParams(data);

                } else {
                    //error
                    $('#share-create-remove-modal').modal('hide');
                    //console_log(response.info);
                    elfinderInstance.error(response.info);
                }
            },
            500: function(response) {
                console_log(response);
                alert('An internal server error occurred.');
            }
        }
    });
}

/**
 * Execute sharing
 * @param hash string
 * @param share_ttl int
 * @param share_password string
 * @param only_change_share_settings
 */
function shareElement(hash, share_ttl, share_password, only_change_share_settings)
{
    var url = _LANG_URL + '/elfind?cmd=share&target=' + hash +
        '&share_ttl=' + parseInt(share_ttl) +
        '&share_password=' + encodeURIComponent(share_password);
    if (typeof only_change_share_settings != 'undefined' && only_change_share_settings == true) {
        url += '&only_change_share_settings=1';
    }
    $.ajax({
        type: 'get',
        url: url,
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true && typeof response.data != 'undefined') {

                    var data = response.data;

                    $('#nav-' + data.hash).addClass("shared_element");
                    $('#' + data.hash).addClass("shared_element");
                    var f = elfinderInstance.file(data.hash);
                    f.file_shared = true;

                    $('#' + data.hash).find('div.elfinder-cwd-icon').each(function() {
                        $(this).addClass('elfinder-cwd-iconshared_element').removeClass('elfinder-cwd-icon');
                    });

                    $('#' + data.hash).find('.sharelink').each(function() {
                        $(this).html(showShareLink(data));
                    });

                    setShareParams(data);

                    //console_log(response);
                    //console_log('share');
                    if ((typeof ws != "undefined") && ("event_data" in response) && (response.event_data)) {
                        //ws.send(JSON.stringify(response.event_data));
                        //console_log(JSON.stringify(response.event_data));
                    }

                } else {

                    $('#' + hash).removeClass("shared_element");
                    $('#' + hash).find('div.elfinder-cwd-iconshared_element').each(function() {
                        $(this).addClass('elfinder-cwd-icon').removeClass('elfinder-cwd-iconshared_element');
                    });

                    var replace = null;
                    if ("data" in response) {
                        replace = response.data;
                    }

                    snackbar(response.info, 'error', 3000, replace, 'shareElement');
                    //elfinderInstance.error(response.info);

                }
                elfinderInstance.exec('reload');
            },
            500: function(response) {
                console_log(response);
                alert('An internal server error occurred.');
            }
        }
    });
}

/**
 * Cancel sharing
 * @param hash string
 */
function unshareElement(hash)
{
    //console_log(hash);
    $.ajax({
        type: 'get',
        url: _LANG_URL + '/elfind?cmd=unshare&target=' + hash,
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true) {

                    var data = response.data;

                    $('#nav-' + data.hash).removeClass("shared_element");
                    $('#' + data.hash).removeClass("shared_element");
                    var f = elfinderInstance.file(data.hash);
                    f.file_shared = false;

                    $('#' + data.hash).find('div.elfinder-cwd-iconshared_element').each(function() {
                        $(this).addClass('elfinder-cwd-icon').removeClass('elfinder-cwd-iconshared_element');
                    });

                    $('#' + data.hash).find('.sharelink').each(function() {
                        $(this).html('');
                    });

                    setShareParams(data);

                    //console_log(response);
                    //console_log('unshare');
                    if ((typeof ws != "undefined") && ("event_data" in response) && (response.event_data)) {
                        //ws.send(JSON.stringify(response.event_data));
                        //console_log(JSON.stringify(response.event_data));
                    }

                } else {
                    //error
                    //console_log(response.info);
                    elfinderInstance.error(response.info);
                }
            },
            500: function(response) {
                console_log(response);
                alert('An internal server error occurred.');
            }
        }
    });
}

/**
 * Show Colleague List popup dialog
 * @param hash string
 */
function showColleagueList(hash)
{
    if (!checkNodesOnline(onCommandsNotShowBalloonNodes)) {
        return false;
    }

    /** ++ Init collaborate popup */
    $('#colleague-list-modal').modal({"show":true});
    $('#collaborate-filesystem-hash').val(hash);
    $('#collaborate-file-uuid').val('');
    var fname = $('#' + hash).find('.elfinder-cwd-filename').first();
    if (fname.length) {
        $('#colleague-list-file-name').html(fname.text());
    } else {
        $('#colleague-list-file-name').html('');
    }

    $('#colleagues-list').html('');
    /** -- Init collaborate popup */

    $.ajax({
        type: 'get',
        url: _LANG_URL + '/elfind?cmd=collaborationDialog&target=' + hash,
        //url: _LANG_URL + '/elfind?cmd=shareDialog&target=' + hash,
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true && typeof response.data != 'undefined') {

                    var data = response.data;
                    $('#leave-collaborate-filesystem-hash').val(data.hash);
                    $('#leave-collaborate-file-uuid').val(data.file_uuid);
                    $('#colleague-list-file-name').html(data.file_name);

                    //$('#colleague-list-modal').modal({"show":true});
                    //setShareParams(data);

                    var colleagues_tpl = $('#colleagues_view_tpl').html();

                    var colleagues_list = "";
                    if (typeof response.colleagues != 'undefined') {
                        for (var i = 0; i < response.colleagues.length; i++) {
                            //console_log(response.colleagues[i]);
                            if (response.colleagues[i].access_type == 'owner') {
                                response.colleagues[i].show_can = "hide";
                                response.colleagues[i].show_is = "";
                            } else {
                                response.colleagues[i].show_can = "";
                                response.colleagues[i].show_is = "hide";
                            }
                            response.colleagues[i]['date'] = formDate.exec(response.colleagues[i]['ts']);
                            colleagues_list += colleagues_tpl.replace(/\{([a-z\_]+)\}/g, function (s, e) {
                                //console_log(s);
                                //console_log(e);
                                //console_log(response.colleagues[i][e]);
                                return response.colleagues[i][e];
                            });
                        }
                    }
                    $('#colleagues-list-view').html(colleagues_list);
                    $('.scrollbar-program').scrollbar();
                } else {
                    $('#colleague-list-modal').modal('hide');
                    elfinderInstance.error(response.info);
                }
            },
            500: function(response) {
                console_log(response);
                alert('An internal server error occurred.');
            }
        }
    });
}

/**
 * Show CollaborateWindow settings or list
 * @param sel object
 */
function showCollaborationSettings(sel)
{
    //console_log(sel);
    if (sel.cbl_is_owner != 0) {
        showCollaborationDialog(sel.hash);
    } else {
        showColleagueList(sel.hash);
    }
}

/**
 * Show collaboration popup dialog
 * @param hash string
 */
function showCollaborationDialog(hash)
{
    if (!checkNodesOnline(onCommandsNotShowBalloonNodes)) {
        return false;
    }

    /*
    if (checkIsFreeLicense(true)) {
        return false;
    }
    */

    /*
    var colleagues = [
        {
            color: "red",
            name: "AL",
            email: "alexkorvo@gmail.com",
            status: "Joined",
            date: "12/10/2016  10:24:19",
            access_type: "view",
            access_type_name: "View",
            colleague_id: "456",
        },
        ...
    ];
    */
    //console_log("showShareDialog: " + hash);
    /** ++ Init collaborate popup */
    $('#collaborate-modal').modal({"show":true});
    $('#collaborate-filesystem-hash').val(hash);
    $('#collaborate-file-uuid').val('');
    var fname = $('#' + hash).find('.elfinder-cwd-filename').first();
    if (fname.length) {
        $('#collaborate-file-name').html(fname.text()).attr('title', fname.text());
    } else {
        $('#collaborate-file-name').html('').attr('title', '');
    }

    $('#colleagues-list').html($('#waiting-tpl').html());
    $('#colleague-email').val('').parent().removeClass('has-error').find('p').first().html('');
    $('#colleague-message').val('');
    $('#invite-message-form').hide();
    $('#colleagues-list-form').show();
    $('#waiting-form').show();
    $('#button-invite-email').removeClass('btn-notActive');
    $('#btn-cancel-collaboration').addClass('btn-notActive');
    /** -- Init collaborate popup */

    $.ajax({
        type: 'get',
        url: _LANG_URL + '/elfind?cmd=collaborationDialog&target=' + hash,
        //url: _LANG_URL + '/elfind?cmd=shareDialog&target=' + hash,
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true && typeof response.data != 'undefined') {

                    var data = response.data;
                    $('#collaborate-filesystem-hash').val(data.hash);
                    $('#collaborate-file-uuid').val(data.file_uuid);
                    $('#collaborate-file-name').html(data.file_name);

                    $('#colleagues-list-form').show();
                    //$('#waiting-form').hide();

                    //$('#collaborate-modal').modal({"show":true});
                    //setShareParams(data);

                    var colleagues_tpl = $('#colleagues_tpl').html();

                    var colleagues_list = "";
                    var is_empty_list = true;
                    if (typeof response.colleagues != 'undefined') {
                        for (var i = 0; i < response.colleagues.length; i++) {
                            //console_log(response.colleagues[i]);
                            if (response.colleagues[i]['access_type'] == 'owner') {
                                response.colleagues[i]['isright'] = '';
                                response.colleagues[i]['canright'] = 'hidden';
                                response.colleagues[i]['hideforowner'] = 'hidden';
                                response.colleagues[i]['owner_or_colleague'] = 'is-owner';
                            } else {
                                response.colleagues[i]['isright'] = 'hidden';
                                response.colleagues[i]['canright'] = '';
                                response.colleagues[i]['hideforowner'] = '';
                                response.colleagues[i]['owner_or_colleague'] = 'is-colleague';
                                is_empty_list = false;
                            }
                            response.colleagues[i]['date'] = formDate.exec(response.colleagues[i]['ts']);
                            colleagues_list += colleagues_tpl.replace(/\{([a-z\_]+)\}/g, function (s, e) {
                                //console_log(s);
                                //console_log(e);
                                //console_log(response.colleagues[i][e]);
                                return response.colleagues[i][e];
                            });
                        }
                        if (is_empty_list) {
                            colleagues_list = '';
                        }

                        //$('#btn-cancel-collaboration').removeClass('btn-notActive');
                        var el_id = $('#collaborate-filesystem-hash').val();
                        if (is_empty_list) {
                            $('#' + el_id).removeClass('collaborated_element');
                            $('#nav-' + el_id).removeClass("collaborated_element");
                            $('#btn-cancel-collaboration').addClass('btn-notActive');
                        } else {
                            $('#' + el_id).addClass('collaborated_element');
                            $('#nav-' + el_id).addClass("collaborated_element");
                            $('#btn-cancel-collaboration').removeClass('btn-notActive');
                        }

                    } else {
                        /*
                        var $owner = $('#colleagues-list').find('.is-owner').first();
                        if (!$owner.length) {
                            var app_owner = $('#owner_tpl').html();
                            colleagues_list = app_owner;
                        }
                        */
                    }
                    $('#colleagues-list').html(colleagues_list);
                    $('.scrollbar-program').scrollbar();
                } else {
                    $('#collaborate-modal').modal('hide');
                    elfinderInstance.error(response.info);
                }
            },
            500: function(response) {
                console_log(response);
                alert('An internal server error occurred.');
            }
        }
    });
}

/**
 * Allow for user to leave collaboration
 */
function leaveCollaboration()
{
    var file_uuid = $('#leave-collaborate-file-uuid').val();
    if (!file_uuid.length) {
        return false;
    }

    $.ajax({
        type: 'post',
        url: _LANG_URL + '/user/leave-collaboration',
        data: {
            file_uuid : file_uuid,
        },
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true) {

                    var el_id = $('#leave-collaborate-filesystem-hash').val();
                    $('#colleague-list-modal').modal('hide');
                    $('#' + el_id).removeClass('collaborated_element');
                    $('#nav-' + el_id).removeClass("collaborated_element");
                    setTimeout(function() {
                        elfinderInstance.exec('reload');
                    }, 3000);

                } else {
                    //error
                    //console_log(response.info);
                }
            },
            500: function(response) {
                console_log(response);
                alert('An internal server error occurred.');
            }
        }
    });
}

/**
 * Cancel collaboration for folder (delete all colleagues)
 */
function cancelCollaboration()
{
    var file_uuid = $('#collaborate-file-uuid').val();
    if (!file_uuid.length) {
        return false;
    }

    $('#btn-cancel-collaboration').addClass('btn-notActive');
    $.ajax({
        type: 'post',
        url: _LANG_URL + '/user/cancel-collaboration',
        data: {
            file_uuid : file_uuid,
        },
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true) {

                    $('#colleagues-list').html('');
                    /*
                    $('#colleagues-list').find('.is-colleague').each(function() {
                        $(this).remove();
                    });
                    */
                    $('.scrollbar-program').scrollbar();

                    //console_log(response.info);

                } else {
                    //error
                    //console_log(response.info);
                    snackbar(response.info, 'error', 3000, null, 'cancelCollaboration');
                }

                var el_id = $('#collaborate-filesystem-hash').val();
                if ($('#colleagues-list').find('.is-colleague').length > 0) {
                    $('#' + el_id).addClass('collaborated_element');
                    $('#nav-' + el_id).addClass("collaborated_element");
                } else {
                    $('#' + el_id).removeClass('collaborated_element');
                    $('#nav-' + el_id).removeClass("collaborated_element");
                }

            },
            500: function(response) {
                console_log(response);
                alert('An internal server error occurred.');
            }
        }
    });
}

/**
 * changeUserCollaborateAccess
 * @param data
 */
function changeUserCollaborateAccess(data)
{
    if (data.access_type && data.colleague_id && data.file_uuid) {
        //$('#invite-title-message').hide();
        $('#invite-message').hide();
        $('#waiting-form-on-add').show();
        $('#button-invite-email').addClass('btn-notActive');
        /*
        $('#colleague-email').val('');
        $('#colleague-message').val(''),
        $('#invite-message-form').hide();

        $('#colleagues-list').prepend($('#waiting-tpl').html());
*/
        var change_acton = data.colleague_id == 'new' ? "add" : (data.access_type == 'delete' ? "delete" : "edit");
        $.ajax({
            type: 'post',
            url: _LANG_URL + '/user/change-collaboration-access',
            data: {
                action            : change_acton,
                colleague_email   : data.colleague_id == 'new' ? data.colleague_email : "",
                colleague_message : data.colleague_id == 'new' ? data.colleague_message : "",
                file_uuid         : data.file_uuid,
                access_type       : data.access_type,
                access_type_name  : data.access_type_name,
                colleague_id      : data.colleague_id == 'new' ? "" : data.colleague_id,
            },
            dataType: 'json',
            statusCode: {
                200: function(response) {
                    if (response.status == true) {

                        if (response.action == 'add') {
                            response.data['date'] = formDate.exec(response.data['ts']);
                            var colleagues_tpl = $('#colleagues_tpl').html();
                            //console_log(response.data);
                            if (response.data['access_type'] == 'owner') {
                                response.data['show_can'] = "hide";
                                response.data['show_is'] = "";
                                response.data['isright'] = '';
                                response.data['canright'] = 'hidden';
                                response.data['owner_or_colleague'] = 'is-owner';
                            } else {
                                response.data['show_can'] = "";
                                response.data['show_is'] = "hide";
                                response.data['isright'] = 'hidden';
                                response.data['canright'] = '';
                                response.data['owner_or_colleague'] = 'is-colleague';
                            }
                            var colleague_new = colleagues_tpl.replace(/\{([a-z\_]+)\}/g, function(s, e) {
                                //console_log(s);
                                //console_log(e);
                                //console_log(colleagues[i][e]);
                                return response.data[e];
                            });

                            var $owner = $('#colleagues-list').find('.is-owner').first();
                            if (!$owner.length) {
                                var app_owner = $('#owner_tpl').html();

                                app_owner = app_owner.replace(/\{([a-z\_]+)\}/g, function(s, e) {
                                    return response.data[e];
                                });

                                $('#colleagues-list').prepend(app_owner);
                                $owner = $('#colleagues-list').find('.is-owner').first();
                            }

                            if ($owner.length) {
                                $owner.after(colleague_new);
                            } else {
                                $('#colleagues-list').prepend(colleague_new);
                            }

                            $('#colleague-email').val('');
                            $('#colleague-message').val(''),
                            $('#invite-message-form').hide();
                            $('#colleagues-list-form').show();
                            $('#button-invite-email').removeClass('btn-notActive');
                            //console_log(colleague_new);
                        } else if (response.action == 'delete') {
                            if ("data" in response) {
                                if (response.status) {
                                    $('#collaborate-user-' + response.data.colleague_id).remove();
                                }
                                if (!$('#colleagues-list').find('.is-colleague').length) {
                                    $('#colleagues-list').html('');
                                    /*
                                    var el_id = $('#collaborate-filesystem-hash').val();
                                    $('#' + el_id).removeClass('collaborated_element');
                                    $('#nav-' + el_id).removeClass("collaborated_element");
                                    $('#btn-cancel-collaboration').addClass('btn-notActive');
                                    */
                                }
                                /*
                                if (response.data.status == 'deleted') {
                                    $('#collaborate-user-' + response.data.colleague_id).remove();
                                } else {
                                    $('#collaborate-user-' + response.data.colleague_id)
                                        .find('.table-status:first')
                                        .html(response.data.status);
                                }
                                */
                            }

                        } else {
                            $('#collaborate-user-access-type-' + response.data.colleague_id)
                                .attr('data-action', response.data.access_type)
                                .html(response.data.access_type_name);
                        }

                        if ((typeof ws != "undefined") && ("event_data" in response) && (response.event_data)) {
                            for (var ii=0; ii<response.event_data.length; ii++) {
                                ////console_log(JSON.stringify(response.event_data[ii]));
                                //ws.send(JSON.stringify(response.event_data[ii]));
                            }
                        }

                        //console_log(response.info);
                        if ("license_restriction" in response.data) {
                            var restriction_type = 'error';
                            if ("license_restriction_type" in response.data) {
                                restriction_type = response.data.license_restriction_type;
                            }
                            snackbar(response.data.license_restriction, restriction_type, 10000, null, 'changeUserCollaborateAccess.' + change_acton);
                        }

                    } else {
                        //error
                        if (!("hidden_info" in response) && ("info" in response) && (response.info.length) > 0) {
                            snackbar(response.info, 'error', 10000, null, 'changeUserCollaborateAccess.' + change_acton);
                        }
                    }

                    //console_log($('#collaborate-filesystem-hash').val());
                    var el_id = $('#collaborate-filesystem-hash').val();
                    if ($('#colleagues-list').find('.is-colleague').length > 0) {
                        $('#' + el_id).addClass('collaborated_element');
                        $('#nav-' + el_id).addClass("collaborated_element");
                        $('#btn-cancel-collaboration').removeClass('btn-notActive');
                    } else {
                        $('#' + el_id).removeClass('collaborated_element');
                        $('#nav-' + el_id).removeClass("collaborated_element");
                        $('#btn-cancel-collaboration').addClass('btn-notActive');
                    }

                    //$('#invite-title-message').show();
                    $('#invite-message').show();
                    $('#waiting-form-on-add').hide();
                    $('#button-invite-email').removeClass('btn-notActive');
                },
                403: function(response) {
                    snackbar(response.responseText, 'error', 3000, 'changeUserCollaborateAccess.' + change_acton);

                    //$('#invite-title-message').show();
                    $('#invite-message').show();
                    $('#waiting-on-add').hide();
                    $('#button-invite-email').removeClass('btn-notActive');
                },
                500: function(response) {
                    console_log(response);
                    alert('An internal server error occurred.');
                }
            }
        });
    }
}

/**
 * showFileVersionsDialog
 * @param hash
 */
function showFileVersionsDialog(hash)
{
    /** ++ Init FileVersions popup */
    $('#fileversions-modal').modal({"show":true});
    $('#fileversions-filesystem-hash').val(hash);
    $('#fileversions-file-uuid').val('');
    var fname = $('#' + hash).find('.elfinder-cwd-filename').first();
    if (fname.length) {
        $('#fileversions-file-name').html(fname.text());
    } else {
        $('#fileversions-file-name').html('');
    }

    $('#fileversions-list').html('');
    /** -- Init collaborate popup */

    $.ajax({
        type: 'get',
        url: _LANG_URL + '/elfind?cmd=fileversionsDialog&target=' + hash,
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true && typeof response.data != 'undefined') {

                    var data = response.data;
                    /** @var object data */
                    $('#fileversions-filesystem-hash').val(data.hash);
                    $('#fileversions-file-id').val(data.file_id);
                    $('#fileversions-file-uuid').val(data.file_uuid);
                    $('#fileversions-file-name').html(data.file_name);

                    var version_tpl = $('#version_tpl').html();

                    var fileversions_list = "";
                    /** @var object data.fileversions */
                    if (typeof data.fileversions != 'undefined' && data.fileversions.length) {
                        for (var i = 0; i < data.fileversions.length; i++) {
                            //console_log(response.colleagues[i]);
                            if (data.fileversions[i].status == 1) {
                                data.fileversions[i]['disabled'] = "";
                            } else {
                                data.fileversions[i]['disabled'] = "disabled";
                                //data.fileversions[i]['disabled'] = ""; data.fileversions[i].status = 1;
                            }
                            data.fileversions[i]['date_restored'] ='';
                            if (data.fileversions[i].status == -1) {
                                data.fileversions[i]['show_restore']  = "none";
                                data.fileversions[i]['show_restored'] = "none";
                                data.fileversions[i]['show_current']  = "block";
                            } else if (data.fileversions[i].status == -2) {
                                data.fileversions[i]['show_restore']  = "none";
                                data.fileversions[i]['show_restored'] = "block";
                                data.fileversions[i]['show_current']  = "none";

                                data.fileversions[i]['date_restored'] = data.fileversions[0]['event_timestamp'];
                            } else {
                                data.fileversions[i]['show_restore'] = "block";
                                data.fileversions[i]['show_restored'] = "none";
                                data.fileversions[i]['show_current'] = "none";
                            }
                            data.fileversions[i]['file_size_after_event'] = file_size_format(data.fileversions[i]['file_size_after_event'], 2);
                            //console_log(data.fileversions[i]);
                            fileversions_list += version_tpl.replace(/\{([a-z\_]+)\}/g, function (s, e) {
                                //console_log(s);
                                //console_log(e);
                                //console_log(response.colleagues[i][e]);
                                return data.fileversions[i][e];
                            });
                        }
                        $('#fileversions-list').html(fileversions_list);
                        $('.scrollbar-program').scrollbar();
                    } else {
                        var f = elfinderInstance.file(data.hash);
                        f.file_updated = false;
                        elfinderInstance.error("File " + data.file_name + " hasn't available versions.");
                        $('#fileversions-modal').modal('hide');
                    }
                } else {
                    elfinderInstance.error('System error.');
                    $('#fileversions-modal').modal('hide');
                }
            },
            500: function(response) {
                console_log(response);
                alert('An internal server error occurred.');
            }
        }
    });
}

/**
 * restorePatchForFile
 * @param event_id integer
 * @param status
 * @returns {boolean}
 */
function restorePatchForFile(event_id, status)
{
    if (status != 1) { return false;}

    $.ajax({
        type: 'post',
        url: _LANG_URL + '/user/restore-patch',
        data: {
            event_id : event_id,
        },
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true) {

                    //console_log(response);
                    /*
                    if ((typeof ws != "undefined") && ("event_data" in response) && (response.event_data)) {
                        for (var ii=0; ii<response.event_data.length; ii++) {
                            //console_log(JSON.stringify(response.event_data[ii][0]));
                            ws.send(JSON.stringify(response.event_data[ii][0]));
                        }
                    }
                    */

                    //console_log(response.info);

                    elfinderInstance.exec('reload');
                    showFileVersionsDialog($('#fileversions-filesystem-hash').val());
                    //snackbar('success-restored-patch', 'success', 3000, null, 'restorePatchForFile');
                    flash_msg('success-restored-patch', 'success', 3000, false, null, 'restorePatchForFile');
                    $('#fileversions-modal').modal('hide');
                } else {
                    //elfinderInstance.error(response.info);
                    //snackbar(response.info, 'error', 3000, null, 'restorePatchForFile');
                    flash_msg(response.info, 'error', 3000, false, null, 'restorePatchForFile');
                    $('#fileversions-modal').modal('hide');
                }
            },
            500: function(response) {
                console_log(response);
                alert('An internal server error occurred.');
            }
        }
    });
}

/**
 * restoreDeletedFile
 * @param hash string
 */
function restoreDeletedFile(hash)
{
    $.ajax({
        type: 'get',
        url: _LANG_URL + '/elfind?cmd=restoreDeletedFile&target=' + hash,
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true) {

                    //console_log(response);
                    //console_log(file);
                    /*
                    var f = elfinderInstance.file(response.data.hash);
                    f.file_deleted = false;
                    $('#' + f.hash)
                        .removeClass('show-deleted')
                        .removeClass('hide-deleted')
                        .removeClass('isdeleted ');
                    */

                    if ((typeof ws != "undefined") && ("event_data" in response) && (response.event_data)) {
                        for (var ii=0; ii<response.event_data.length; ii++) {
                            //console_log(JSON.stringify(response.event_data[ii]));
                            ws.send(JSON.stringify(response.event_data[ii]));
                        }
                    }

                    elfinderInstance.exec('reload');
                } else {
                    elfinderInstance.error(response.info);
                }
            },
            500: function(response) {
                console_log(response);
                alert('An internal server error occurred.');
            }
        }
    });
}

/**
 * @param file_name string
 */
function checkIsCanPreviewed(file_name)
{
    var ext_list = [
        "jpg", "jpeg", "png", "bmp", "svg", "gif", "tiff", "tif",
        "txt", "js", "py", "html", "md", "log",
        "pdf",
        "mpeg", "mp4", "wmv", "mpg", "mov", "webm", "avi", "mkv", "ogv",
        "mp3", "wma", "ogg", "wav",
    ];
    var ext = file_name.split('.').pop();
    return ext_list.in_array(ext.toLowerCase());
}

/**
 * previewFile
 * @param file object
 */
function previewFile(file)
{
    if (parseInt(file.file_size) <=0) {
        elfinderInstance.error("Preview impossible. File is empty.");
        return false;
    }
    if (("file_id" in file) && !(file.file_id === null)) {
        if ($.client.browser == 'msie') {
            window.open('/preview/' + file.file_id + '?&rand=' + Math.random());
            //$('#preview-modal').modal({show: true});
        } else {
            var w_h = $('#total-container-id').innerHeight() - 100;
            var w_w = $('#total-container-id').innerWidth() - 100;
            if (w_w > 520) {
                $('#preview-container').css({
                    'max-width': w_w + "px",
                    'width': w_w + "px",
                    'max-height': w_h + "px",
                    'height': w_h + "px",
                });
            }
            var w_to_iframe = $('#total-container-id').innerWidth();
            var h_to_iframe = $('#total-container-id').innerHeight();
            var $iframe = $('<iframe class="preview-iframe" width="100%" height="100%" frameborder="0"></iframe>');
            $iframe.attr('src', '/preview/' + file.file_id + '?w=' + w_to_iframe + '&h=' + h_to_iframe + '&rand=' + Math.random());
            $('#preview-body').css({'height': w_h + "px"}).empty().append($iframe);
            var w_w = $(window).width();
            if (w_w > 520) {
                $('#preview-modal')
                    .css({'margin-top': "-50px", 'z-index': '9000000000'})
                    .modal({show: true});
            } else {
                $('#preview-modal')
                    //.css({'margin-top': "0px"})
                    .css({'z-index': '9000000000'})
                    .modal({show: true});
            }
            var $preview_file_name = $('#preview-file-name');
            $preview_file_name.html(file.file_name).css({ 'width' : (w_w - 180) + 'px' });
            //console_log('ddddddd' + w_w);

            return true;
        }
    } else {
        //console_log(file);
        //snackbar('preview_impossible_file_is_not_synced', 'error', 3000, null, 'previewFile');
        //flash_msg('preview_impossible_file_is_not_synced', 'error', 3000, false, null, 'previewFile');
        elfinderInstance.error("Preview impossible. File is not synced.");
        return false;
    }
}

/**
 *
 */
function initDownloadDiv()
{
    var elf_params = getElfinderParams();
    dwl_pos_x = elf_params.pos_elf.top + 12;
    dwl_pos_y = elf_params.w_elf - elf_params.w_dwl + elf_params.pos_elf.left - 12;
    $('#download-dialog').css({top: dwl_pos_x + 'px', left: dwl_pos_y + 'px'}).draggable({
        drag: function() {
            if ($('#preview_uploads').is(":visible")) {
                var pos_dwl = $(this).offset();
                dwl_pos_x = pos_dwl.top;
                dwl_pos_y = pos_dwl.left;
                //console_log('new Top: ' + upl_pos_x + ' new Left: ' + upl_pos_y);
                $(this).css({ height: 'auto'});
            }
        }
    });

}

/**
 * downloadFile
 * @param file object
 */
function downloadFile(file)
{
    if (parseInt(file.file_size) <=0) {
        elfinderInstance.error("Download impossible. File is empty.");
        return false;
    }

    if (("file_id" in file) && !(file.file_id === null)) {

        $('#download-iframe').html('');
        var $iframe = $('<iframe name="download_frame" class="download-iframe" width="1px" height="1px" frameborder="0"></iframe>');
        $('#download-iframe').append($iframe);

        startDownloadFile(file);
        /*
        if ($.client.browser == 'msie') {
            //console_log('fucking ie');
            window.open('/preview/' + file.file_id + '?download=true&rand=' + Math.random());
        } else {
            $('#download-iframe').html('');
            var $iframe = $('<iframe name="download_frame" class="download-iframe" width="1px" height="1px" frameborder="0"></iframe>');
            $iframe.attr('src', '/preview/' + file.file_id + '?download=true&rand=' + Math.random());
            $('#download-iframe').append($iframe);
            $('#download-dialog-tpl').show();
            return true
        }
        */
    } else {
        //console_log(file);
        //snackbar('preview_impossible_file_is_not_synced', 'error', 3000, null, 'downloadFile');
        //flash_msg('download_impossible_file_is_not_synced', 'error', 3000, false, null, 'downloadFile');
        elfinderInstance.error("Download impossible. File is not synced.");
        return false;
    }
}

/**
 *
 */
function cancelDownloadFile()
{
    $('#download-iframe').html('');
    $('#download-dialog-tpl').hide();
}

/**
 *
 * @param error
 */
function closeDownloadIframe(error)
{
    if (error) {
        elfinderInstance.error(error);
        $('#download-dialog-tpl').hide();
    } else {
        $('#download-dialog-tpl').delay(3000).fadeOut();
    }
    //$('#download-iframe').html('');
}

/**
 *
 */
function closePreviewIframe()
{
    $('#preview-modal').modal('hide');
}

/**
 *
 */
function checkIframeContent()
{
    console_log($('#download-iframe').find('.download-iframe').first());
}

/**
 * initElFinder
 * @param lang string
 * @returns {jQuery}
 */
function initElFinder() {

    /** Preview-file command */
    elFinder.prototype.commands.preview = function () {
        this.exec = function (hashes) {

            if (!checkNodesOnline(onCommandsNotShowBalloonNodes)) {
                return false;
            }
            //implement what the custom command should do here
            var fm     = this.fm,
                dfrd   = $.Deferred()
                    .fail(function(error) {
                        error && fm.error(error);
                    });

            var sel = this.files(sel);
            if (sel.length != 1) {
                return false;
            }
            if (!checkIsFileSynced(sel[0])) {
                return false;
            }
            if (sel[0].is_folder) {
                return false;
            }
            if (sel[0].file_deleted) {
                return false;
            }
            if (!checkIsCanPreviewed(sel[0].file_name)) {
                return false;
            }
            previewFile(sel[0]);
        }
        this.getstate = function (sel) {
            var sel = this.files(sel);
            if (sel.length != 1) {
                return -1;
            }
            //console_log(sel[0]);
            if (sel[0].is_folder) {
                return -1;
            }
            if (sel[0].file_deleted) {
                return -1;
            }
            if (sel[0].locked == 1) {
                return -1;
            }
            if (!checkIsCanPreviewed(sel[0].file_name)) {
                return -1;
            }
            return 0;
        }
    };

    /** Download command */
    elFinder.prototype.commands.download = function () {
        this.exec = function (hashes) {
            if (!checkNodesOnline(onCommandsNotShowBalloonNodes)) {
                return false;
            }
            //implement what the custom command should do here
            var fm     = this.fm,
                dfrd   = $.Deferred()
                    .fail(function(error) {
                        error && fm.error(error);
                    });

            var sel = this.files(sel);
            if (sel.length != 1) {
                return false;
            }
            if (!checkIsFileSynced(sel[0])) {
                return false;
            }
            if (sel[0].file_deleted) {
                return false;
            }
            downloadFile(sel[0]);
        }
        this.getstate = function (sel) {
            var sel = this.files(sel);
            if (sel.length != 1) {
                return -1;
            }
            if (sel[0].file_deleted) {
                return -1;
            }
            if (sel[0].locked == 1) {
                return -1;
            }
            if (sel[0].is_folder) {
                return -1;
            }
            return 0;
        }
    };

    /** Restore-deleted-file command */
    elFinder.prototype.commands.restoredeletedfile = function () {
        this.exec = function (hashes) {
            if (!checkNodesOnline(onCommandsNotShowBalloonNodes)) {
                return false;
            }
            //implement what the custom command should do here
            var fm     = this.fm,
                dfrd   = $.Deferred()
                    .fail(function(error) {
                        error && fm.error(error);
                    });

            var sel = this.files(sel);
            if (sel.length != 1) {
                return false;
            }
            if (!checkIsFileSynced(sel[0])) {
                return false;
            }
            if (sel[0].is_folder) {
                return false;
            }
            if (!sel[0].file_deleted) {
                return false;
            }
            restoreDeletedFile(sel[0].hash);
        }
        this.getstate = function (sel) {
            //return 0;
            //return 0 to enable, -1 to disable icon access
            var sel = this.files(sel);
            if (sel.length != 1) {
                return -1;
            }
            //console_log(sel[0]);
            if (sel[0].is_folder) {
                return -1;
            }
            if (!sel[0].file_deleted) {
                return -1;
            }
            if (sel[0].locked == 1) {
                return -1;
            }
            return 0;
        }
    };

    /** Муляж Show-file-versions command  */
    elFinder.prototype.commands.showfileversionsnotactive = function () {
        this.exec = function() {
            //this.fm._commands.mkdir._disabled = true;
            //console_log(this.fm);
            if (!checkNodesOnline(onCommandsNotShowBalloonNodes)) {
                return false;
            }
            return false;
        }
        this.getstate = function (sel) {
            var sel = this.files(sel);
            if (sel.length != 1) {
                return -1;
            }
            if (sel[0].file_deleted) {
                return -1;
            }
            if (sel[0].locked == 1) {
                return -1;
            }
            if (sel[0].file_updated) {
                return -1;
            }
            if (sel[0].is_folder) {
                return -1;
            }
            return 0;
        }
    };

    /** Show-file-versions command */
    elFinder.prototype.commands.showfileversions = function () {
        this.exec = function (hashes) {
            if (!checkNodesOnline(onCommandsNotShowBalloonNodes)) {
                return false;
            }
            var fm     = this.fm,
                dfrd   = $.Deferred()
                    .fail(function(error) {
                        error && fm.error(error);
                    });

            //implement what the custom command should do here
            var sel = this.files(sel);
            if (sel.length != 1) {
                return false;
            }
            if (!checkIsFileSynced(sel[0])) {
                return false;
            }
            if (sel[0].file_deleted) {
                return false;
            }
            if (!sel[0].file_updated) {
                return false;
            }
            showFileVersionsDialog(sel[0].hash);
        }
        this.getstate = function (sel) {
            //return 0;
            //return 0 to enable, -1 to disable icon access
            var sel = this.files(sel);
            if (sel.length != 1) {
                return -1;
            }
            if (sel[0].file_deleted) {
                return -1;
            }
            if (sel[0].locked == 1) {
                return -1;
            }
            if (!sel[0].file_updated) {
                return -1;
            }
            return 0;
        }
    };

    /** Collaborate command */
    elFinder.prototype.commands.collaborate = function () {
        this.exec = function (hashes) {
            if (!checkNodesOnline(onCommandsNotShowBalloonNodes)) {
                return false;
            }
            //implement what the custom command should do here
            var sel = this.files(sel);
            if (sel.length != 1) {
                return false;
            }
            if (!checkIsFileSynced(sel[0])) {
                return false;
            }
            if (sel[0].file_deleted) {
                return false;
            }
            if (!sel[0].is_folder) {
                return false;
            }
            if (!("file_parent_id" in sel[0]) || sel[0].file_parent_id != 0) {
                return false;
            }
            if (sel[0].cbl_is_owner != 1) {
                return false;
            }
            showCollaborationDialog(sel[0].hash);
        }
        this.getstate = function (sel) {
            //return 0;
            //return 0 to enable, -1 to disable icon access
            var sel = this.files(sel);
            if (sel.length != 1) {
                return -1;
            }
            if (sel[0].locked == 1) {
                return -1;
            }
            //if (!$('#' + sel[0].hash).hasClass('directory') && !$('#nav-' + sel[0].hash).hasClass('elfinder-navbar-dir')) {
            if (!sel[0].is_folder) {
                return -1;
            }
            if (sel[0].file_deleted) {
                return -1;
            }
            if (!("file_parent_id" in sel[0]) || sel[0].file_parent_id != 0) {
                return -1;
            }
            if (sel[0].cbl_is_owner != 1) {
                return -1;
            }
            return 0;
        }
    };

    /** Colleague list command */
    elFinder.prototype.commands.colleaguelist = function () {
        this.exec = function (hashes) {
            if (!checkNodesOnline(onCommandsNotShowBalloonNodes)) {
                return false;
            }
            //implement what the custom command should do here
            var sel = this.files(sel);
            if (sel.length != 1) {
                return false;
            }
            if (!checkIsFileSynced(sel[0])) {
                return false;
            }
            if (sel[0].file_deleted) {
                return false;
            }
            if (!sel[0].is_folder) {
                return false;
            }
            if (!("file_parent_id" in sel[0]) || sel[0].file_parent_id != 0) {
                return false;
            }
            if (!sel[0].file_collaborated) {
                return false;
            }
            if (sel[0].cbl_is_owner != 0) {
                return false;
            }
            showColleagueList(sel[0].hash);
        }
        this.getstate = function (sel) {
            //return 0;
            //return 0 to enable, -1 to disable icon access
            var sel = this.files(sel);
            if (sel.length != 1) {
                return -1;
            }
            if (sel[0].locked == 1) {
                return -1;
            }
            //if (!$('#' + sel[0].hash).hasClass('directory') && !$('#nav-' + sel[0].hash).hasClass('elfinder-navbar-dir')) {
            if (!sel[0].is_folder) {
                return -1;
            }
            if (sel[0].file_deleted) {
                return -1;
            }
            if (!("file_parent_id" in sel[0]) || sel[0].file_parent_id != 0) {
                return -1;
            }
            if (!sel[0].file_collaborated) {
                return -1;
            }
            if (sel[0].cbl_is_owner != 0) {
                return -1;
            }
            return 0;
        }
    };

    /** Share command */
    elFinder.prototype.commands.globalshare = function () {

        //var sel = this.fm.selectedFiles();

        //console_log(this);
        this.exec = function (hashes, cmdname) {
            //console_log(cmdname);
            if (!checkNodesOnline(onCommandsNotShowBalloonNodes)) {
                return false;
            }
            //implement what the custom command should do here
            var sel = this.files();
            if (sel.length != 1) {
                return false;
            }
            if (cmdname == 'collaborationsettings') {
                showCollaborationSettings(sel[0]);
            } else {
                showShareDialog(sel[0]);
            }
        }

        this.getstate = function (sel) {
            //return 0 to enable, -1 to disable icon access
            var sel = this.files(sel);
            if (sel.length != 1) {
                return -1;
            }
            if (sel[0].file_deleted) {
                return -1;
            }
            if (sel[0].locked == 1) {
                return -1;
            }
            /*
            if (sel[0].file_shared) {
                    return -1;
            }
            */
            if (("file_parent_id" in sel[0]) && sel[0].file_parent_id == 0 && sel[0].is_folder) {
                this.variants = [];
                this.variants.push(['getlink', this.fm.i18n('cmdgetlink')]);
                this.variants.push(['collaborationsettings', this.fm.i18n('cmdcollaborationsettings')]);
            } else {
                this.variants = null;
            }

            return 0;
        }
    };

    /** Share command */
    elFinder.prototype.commands.share = function () {
        this.exec = function (hashes) {
            if (!checkNodesOnline(onCommandsNotShowBalloonNodes)) {
                return false;
            }
            //implement what the custom command should do here
            var sel = this.files(sel);
            if (sel.length != 1) {
                return false;
            }
            showShareDialog(sel[0]);
        }
        this.getstate = function (sel) {
            //return 0 to enable, -1 to disable icon access
            var sel = this.files(sel);
            if (sel.length != 1) {
                return -1;
            }
            if (sel[0].file_deleted) {
                return -1;
            }
            if (sel[0].locked == 1) {
                return -1;
            }
            if (sel[0].file_shared) {
                return -1;
            }
            return 0;
        }
    };

    /** UnShare command */
    elFinder.prototype.commands.unshare = function () {
        this.exec = function (hashes) {
            if (!checkNodesOnline(onCommandsNotShowBalloonNodes)) {
                return false;
            }
            //implement what the custom command should do here
            var sel = this.files(sel);
            if (sel.length != 1) {
                return false;
            }
            //unshareElement(sel[0].hash);
            showShareDialog(sel[0]);
        }
        this.getstate = function (sel) {
            //return 0 to enable, -1 to disable icon access
            var sel = this.files(sel);
            if (sel.length != 1) {
                return -1;
            }
            if (sel[0].file_deleted) {
                return -1;
            }
            if (sel[0].locked == 1) {
                return -1;
            }
            if (!sel[0].file_shared) {
                return -1;
            }

            return 0;
        }
    };

    /** Search command */
    elFinder.prototype.commands.search = function() {
        this.title          = 'Find files';
        this.options        = {ui : 'searchbutton'}
        this.alwaysEnabled  = true;
        this.updateOnSelect = false;

        this.getstate = function(sel) {
            return 0;
        }

        this.exec = function(q, target, mime) {
            var fm = this.fm,
                reqDef;

            q = $('#manager-search-text').val();
            mime = false;
            target = false;
            if (typeof q == 'string' && q) {
                if (typeof target == 'object') {
                    mime = target.mime || '';
                    target = target.target || '';
                }
                target = target? target : '';
                mime = mime? $.trim(mime).replace(',', ' ').split(' ') : [];
                $.each(mime, function(){ return $.trim(this); });
                fm.trigger('searchstart', {query : q, target : target, mimes : mime});

                reqDef = fm.request({
                    data   : {cmd : 'search', q : q, target : target, mimes : mime},
                    notify : {type : 'search', cnt : 1, hideCnt : true},
                    cancel : true
                });
                return reqDef;
            }
            fm.getUI('toolbar').find('.'+fm.res('class', 'searchbtn')+' :text').focus();
            return $.Deferred().reject();
        }

    };


    $('#elfinder').elfinder({
        height: $(window).height() + delta_height,
        url: _LANG_URL + '/elfind',     // connector URL (REQUIRED)
        lang: $('#elfinder').attr('lang'),  // language (OPTIONAL)
        soundPath: "/themes/orange/sounds/",
        defaultView: 'list',
        dateFormat: _GLOBAL.datetime_short_format,
        fancyDateFormat: _GLOBAL.datetime_fancy_format,
        UTCDate: false,
        //reloadClearHistory: true,
        uiOptions: {
            // toolbar configuration
            toolbar: [
                //['back', 'forward'],
                ['reload'],
                ['home', 'up'],
                ['mkdir', /*'mkfile',*/ /*'upload'*/],
                //['open', 'download', 'getfile'],
                //['info'],
                //['quicklook'],
                ['copy', 'cut', 'paste'],
                ['rm'],
                ['rename'/*, 'duplicate', 'edit', 'resize'*/],
                //['extract', 'archive'],
                //['share', 'unshare'],
                ['search'],
                ['view'],
                ['sort'],
                //['help'],
            ],

            toolbar: false,

            // directories tree options
            tree: {
                // expand current root on init
                openRootOnLoad: true,
                // expand current work directory on open
                openCwdOnOpen  : false,
                // auto load current dir parents
                syncTree: true,
            },

            // navbar options
            navbar: {
                //width: 200,
                minWidth: 150,
                maxWidth: 400,
                //minHeight: 200,
                //maxHeight: 600,
            },

            // current working directory options
            cwd: {
                oldSchool: false,
                listView : {
                    // name is always displayed, cols are ordered
                    // e.g. ['perm', 'date', 'size', 'kind', 'owner', 'group', 'mode']
                    // mode: 'mode'(by `fileModeStyle` setting), 'modestr'(rwxr-xr-x) , 'modeoct'(755), 'modeboth'(rwxr-xr-x (755))
                    // 'owner', 'group' and 'mode', It's necessary set volume driver option "statOwner" to `true`
                    //columns: ['kind', 'date', 'perm', 'size'],
                    columns: ['date', 'size', 'sharelink', 'sharebutton', 'contmenu'],
                    // override this if you want custom columns name
                    // example
                    columnsCustomName : {
                        //date        : 'Modified',
                        sharelink   : '&nbsp;',
                        sharebutton : '&nbsp;',
                        contmenu    : '&nbsp;'
                    },
                    // fixed list header colmun
                    fixedHeader: true,
                    showHeader: false,
                },
            },

        },
        commands: [
            'download', 'preview', 'restoredeletedfile', 'showfileversions', 'showfileversionsnotactive',
            'collaborate', 'colleaguelist', 'unshare', 'share', 'globalshare',
            'open', 'reload', 'home', 'up', 'back', 'forward', 'getfile',
            'download', 'rm', 'duplicate', 'rename', 'mkdir', 'mkfile', 'copy', //'upload',
            'cut', 'paste', 'edit', 'extract', 'archive', 'search', 'info', 'view', 'help', 'resize', 'sort'
        ],

        contextmenu: {
            // navbarfolder menu
            //navbar: ['open', 'download', '|', 'share', 'unshare', 'collaborate', 'colleaguelist', '|', 'copy', 'cut', 'paste', 'rename', 'rm', '|', 'info'],
            navbar: ['open', 'download', 'globalshare', '|', 'copy', 'cut', 'paste', 'rename', 'rm', /*'|',*/ 'info'],
            // current directory menu
            cwd: ['reload', 'back', '|', 'mkdir', 'paste', '|', 'sort' /*'mkfile', 'upload', 'preview', 'info', '|', 'share', 'unshare', 'collaborate', 'colleaguelist', 'showfileversions', 'showfileversionsnotactive', 'restoredeletedfile' */],
            // current directory file menu
            //files: ['open', 'preview', 'download', '|', 'share', 'unshare', 'collaborate', 'colleaguelist', 'restoredeletedfile', '|', 'copy', 'cut', 'rename', 'rm', 'showfileversions', 'showfileversionsnotactive', '|', 'info' /*,'paste', 'duplicate',*/  /*'edit',*/]
            files: ['open', 'preview', 'download', 'globalshare', 'restoredeletedfile', '|', 'copy', 'cut', 'rename', 'rm', 'showfileversions', 'showfileversionsnotactive', /*'|',*/ 'info' ]
        },
        resizable: true,
        /*
        commandsOptions : {
            getfile: {
                multiple: true
            }
        },
        */
        getFileCallback: function () {
            var fm     = this.fm,
                dfrd   = $.Deferred()
                    .fail(function(error) {
                        error && fm.error(error);
                    });

            var sel = this.files(sel);
            if (sel.length != 1) {
                return false;
            }
            if (!checkIsFileSynced(sel[0])) {
                return false;
            }
            if (sel[0].is_folder) {
                return false;
            }
            if (sel[0].file_deleted) {
                return false;
            }
            if (checkNodesOnline(onCommandsNotShowBalloonNodes)) {
                if (checkIsCanPreviewed(sel[0].file_name)) {
                    previewFile(sel[0]);
                } else {
                    downloadFile(sel[0]);
                }
                //previewFile(sel[0]);
            } else {
                return false;
            }
            //return false;
        },

        handlers: {
            /*
            select: function (event, elfinderInstance) {
                $("#elfinder-sort-menu-list").hide();
                toolbarButtonsPrepare(elfinderInstance);
                /*
                console_log(event);
                console_log(event.data.selected); // selected files hashes list
                console_log(elfinderInstance);

            }
            */
            select: function (event, elfinderInstance, origEvent) {

                $("#elfinder-sort-menu-list").hide();
                toolbarButtonsPrepare(elfinderInstance);

                /*
                //if (navigator.userAgent.match(/Android|BlackBerry|iPhone|iPad|iPod|Opera Mini|IEMobile/i)) {
                //console_log(origEvent);
                //console_log(elfinderInstance);
                var sel_arr = event.data.selected;
                if (sel_arr.length > 0) {
                    var selected = elfinderInstance.file(sel_arr[0]);
                    if (selected.mime == 'directory') {
                        //opens a folder
                        elfinderInstance.request({
                            data: {cmd: 'open', target: sel_arr[0]},
                            notify: {type: 'open', target: sel_arr[0]},
                            syncOnFail: true
                        });
                    }
                }
                */
            }
        },
    });
    //console_log($('#elfinder-elfinder-cwd-thead').html());
    /*
    $('#elfinder-elfinder-cwd-thead').find('tr').each(function() {
        console_log($(this).css());
    });
    */
    $('#elfinderPanel').show();

    /** https://github.com/inuyaksa/jquery.nicescroll */
    if (!checkIsMobile()) {
        $('.elfinder-navbar').niceScroll({cursorcolor: "#CDCDCD", cursorwidth: "8px", zindex: 900});
        $('.elfinder-cwd-wrapper').niceScroll({cursorcolor: "#CDCDCD", cursorwidth: "8px", zindex: 900});
    }

    // https://github.com/Studio-42/elFinder/wiki/Client-event-API

    DropzoneInstance = initDropzone();

    return $('#elfinder').elfinder('instance');
}


/**
 * ***
 * ***
 * ***
 * ***
 * ***
 */
$(document).ready(function() {

    //var elfinderInstance = $('#elfinder').elfinder('instance');
    elfinderInstance = initElFinder();

    var dh_less_312 = -120,
        dh_less_500 = -120,
        dh_500_800  = -127,
        dh_800_900  = -127,
        dh_900_992  = -190,
        dh_more_992 = -160;

    /*
     var W__W = $(window).width();
     if (W__W <= 500) { delta_height = dh_less_500; }
     else if (W__W > 500 && W__W <= 800) { delta_height = dh_500_800; }
     else if (W__W > 800 && W__W <= 900) { delta_height = dh_800_900; }
     else if (W__W > 900 && W__W < 992) { delta_height = dh_900_992; }
     else { delta_height = dh_more_992; }
     */

    $(window).on('resize', function(e) {

        var w_w = $(window).width();
        var w_h = $(window).height();
        if (w_w <= 312) { delta_height = dh_less_312; }
        else if (w_w > 312 && w_w <= 500) { delta_height = dh_less_500; }
        else if (w_w > 500 && w_w <= 800) { delta_height = dh_500_800; }
        else if (w_w > 800 && w_w <= 900) { delta_height = dh_800_900; }
        else if (w_w > 900 && w_w < 992) { delta_height = dh_900_992; }
        else { delta_height = dh_more_992; }

        if (deltaHeightSearch) { delta_height = delta_height - deltaHeightSearch; }

        resizeElfinder();

        if (checkIsMobile()) {

            e.preventDefault();
            e.stopPropagation();

            //if (current_W_H != w_h || current_W_W != w_w) {
            if (current_W_W != w_w) {
                var cur_reload_elf = Date.now();
                //console_log(cur_reload_elf - last_reload_elf);
                if (cur_reload_elf - last_reload_elf > 5 * 1000) {
                    setTimeout(function () {
                        elfinderInstance.exec('reload');
                    }, 1000);
                    //setTimeout(function() { elfinderInstance.prependFolderUpZone(); }, 1000);
                    console_log('onorientationchange');
                    last_reload_elf = Date.now();

                    var $item_search_form = $('.item-search-form');
                    if (w_w <= 640 && !search_showed_by_user) {
                        $item_search_form.css({display: "none"});
                        //console_log('hide_search');
                        deltaHeightSearch = 1;
                    }
                    current_W_W = w_w;
                    current_W_H = w_h;
                    //elfinderInstance.exec('reload');
                }
            }
        }
    });

    var $alert_no_nodes_online = $('#alert-no-nodes-online');
    $alert_no_nodes_online
        .on('show', function() {
            //console_log('is Shown ' + parseInt($alert_no_nodes_online.outerHeight(true)));
            //delta_height = delta_height + 20 + (-1 * parseInt($alert_no_nodes_online.outerHeight(true)));
            resizeElfinder();
        })
        .on('hide', function() {
            //console_log('is Hide ' + parseInt($alert_no_nodes_online.outerHeight(true)));
            //delta_height = delta_height - 20 - (-1 * parseInt($alert_no_nodes_online.outerHeight(true)));
            resizeElfinder();
        });

    var $alert_block_container = $('#alert-block-container');
    $alert_block_container.find('.alert-danger').each(function() {
        //console_log($(this).attr('id') + ' = ' + $(this).outerHeight(true));
        if ($(this).attr('id') != 'alert-no-nodes-online') {
            if ($(this).is(':visible')) {
                alerts_outerHeight[$(this).attr('id')] = parseInt($(this).height()) + 4;
                //delta_height = delta_height + 20 - parseInt($(this).outerHeight(true));
                resizeElfinder();
            }
            $(this).on("remove", function () {

                var o_h = parseInt($(this).outerHeight(true));
                if (o_h <= 0) {
                    if (alerts_outerHeight.hasOwnProperty($(this).attr('id'))) {
                        o_h = alerts_outerHeight[$(this).attr('id')];
                    } else {
                        o_h = 40;
                    }
                }

                //console_log('removed ' + $(this).attr('id') + ' = ' + o_h + ' >> ' + alerts_outerHeight[$(this).attr('id')]);
                //delta_height = delta_height + o_h;
                resizeElfinder();
                return true;
            });
        }
    });


    $('#preview-modal').on('hidden.bs.modal', function () {
        $('#preview-modal').css('margin-top', "");
        $('#preview-body').empty();
    });
    $(document).on('click', '#cancelAllUploads', function() {
        $('#file-upload-modal').fadeOut(1500, function() {
            $('#file-upload-modal').modal('hide');
        });
        return true;
    });
    $(document).on('click', '.create-share-button', function () {
        //console_log($('#share-ttl').val());
        shareElement(
            $('#filesystem-hash').val(),
            $('#share-ttl').val(),
            $('#share-password').val()
        );
    });
    $(document).on('click', '.remove-share-button', function () {
        unshareElement($('#filesystem-hash').val());
    });

    $(document).on('beforeSubmit', '#share-create-remove-form', function () {
        var form = $(this);
        //console_log(form);
        // return false if form still have some validation errors
        if (form.find('.has-error').length) {
            return false;
        }
        if ($('#share-settings-button').hasClass('btn-notActive')) {
            return false;
        }
        //alert($('#link-settings').is(":visible"));
        // submit form
        shareElement(
            $('#filesystem-hash').val(),
            $('#share-ttl').val(),
            $('#share-password').val(),
            true
        );
        return false;
    });

    $(document).on('beforeSubmit', '#share-send-to-email-form', function () {
        var form = $(this);
        //console_log(form);
        // return false if form still have some validation errors
        if (form.find('.has-error').length) {
            return false;
        }

        if ($('#share-email').val() == '') {
            $('#share-email').parent().removeClass('has-success').addClass('has-error').find('p').first().html('E-mail can\'t be empty');
            return false;
        }

        $.ajax({
            type: 'post',
            url: _LANG_URL + '/user/share-send-to-email',
            data: '&share_hash=' + $('#share-hash').val() + '&share_email=' + encodeURIComponent($('#share-email').val()),
            dataType: 'json',
            statusCode: {
                200: function(response) {
                    if (response.status == true) {

                        snackbar('share-sent-ok', 'success', 2000, {'email': $('#share-email').val()}, 'share-send-to-email');
                        //flash_msg('share-sent-ok', 'success', 2000, false, null, 'share-send-to-email');
                        $('#share-email').val('');

                    } else {
                        //error
                        snackbar(response.info, 'error', 2000, null, 'share-send-to-email');
                        //flash_msg(response.info, 'error', 2000, false, null, 'share-send-to-email');
                    }
                },
                500: function(response) {
                    console_log(response);
                    alert('An internal server error occurred.');
                }
            }
        });


        return false;
    });

    $(document).on('beforeSubmit', '#collaborate-form', function () {
        if ($('#button-invite-email').hasClass('btn-notActive')) {
            return false;
        }

        var form = $(this);
        //console_log(form);
        // return false if form still have some validation errors
        if (form.find('.has-error').length) {
            return false;
        }

        if ($('#colleague-email').val() == '') {
            $('#colleague-email').parent().removeClass('has-success').addClass('has-error').find('p').first().html('E-mail can\'t be empty');
            return false;
        }

        var data = {
            colleague_email   : $('#colleague-email').val(),
            colleague_message : $('#colleague-message').val(),
            access_type       : $('#collaborate-user-access-type-new').attr('data-action'),
            access_type_name  : $('#collaborate-user-access-type-new').text(),
            colleague_id      : "new",
            file_uuid         : $('#collaborate-file-uuid').val(),
        }
        //console_log(data);
        changeUserCollaborateAccess(data)

        return false;
    });

    $(document).on('input', '#colleague-email', function() {
        if ($(this).val() != '') {
            $('#invite-message-form').show();
            $('#invite-message').show();
            //$('#waiting-form-on-add').hide();
            $('#colleagues-list-form').hide();
        } else {
            $('#invite-message-form').hide();
            $('#colleagues-list-form').show();
        }
    });

    $(document).on('click', '.ui-dialog-titlebar-close3', function () {
        cancelDownloadFile();
    });

    if (elfinderInstance) {

        initSortButton();

        $(document).on('click', '.btn-palel', function() {
            if (!$(this).hasClass('notActive')) {
                $(this).addClass('clicked');
                setTimeout(function() {
                    $('.btn-palel').removeClass('clicked');
                }, 150);
            };
        });

        $(document).on('click', '.btn-exec-createFolder', function() {
            if (!$(this).hasClass('notActive') && !$(this).hasClass('waitingCreate')) {
                var cwd = elfinderInstance.cwd();
                if (cwd.file_deleted) {
                    return false;
                }
                elfinderInstance.exec('mkdir');
                $('.btn-exec-createFolder').addClass('waitingCreate');
            }
        });
        $(document).on('click', '.btn-exec-uploadFile', function() {
            if (!$(this).hasClass('notActive')) {
                if (!checkNodesOnline(onCommandsNotShowBalloonNodes)) {
                    return false;
                }
                var cwd = elfinderInstance.cwd();
                if (cwd.file_deleted) {
                    return false;
                }
                //elfinderInstance.exec('upload');
                //$('#file-upload-modal').modal({"show": true});
                //$('.scrollbar-program').scrollbar();
            }
        });
        $(document).on('click', '.btn-palel--home', function() {
            if (!$(this).hasClass('notActive')) {
                elfinderInstance.exec('home');
            }
        });
        $(document).on('click', '.btn-palel--up', function() {
            if (!$(this).hasClass('notActive')) {
                elfinderInstance.exec('up');
            }
        });
        $(document).on('click', '.btn-palel--reload', function(event) {
            //if (!$(this).hasClass('notActive')) {
            elfinderInstance.exec('reload');
            //}
        });
        $(document).on('click', '.btn-palel--back', function() {
            if (!$(this).hasClass('notActive')) {
                elfinderInstance.exec('back');
            }
        });
        $(document).on('click', '.btn-palel--download', function() {
            if (!$(this).hasClass('notActive')) {
                var sel = elfinderInstance.selectedFiles();

                if (sel.length != 1) {
                    return false;
                }
                if (sel[0].file_deleted) {
                    return false;
                }
                if (sel[0].locked == 1) {
                    return false;
                }
                if (sel[0].is_folder) {
                    return false;
                }

                if (sel.length) {
                    //console_log('download: ' + sel[0].hash);
                    downloadFile(sel[0])
                }
            }
        });
        $(document).on('click', '.btn-palel--copy', function() {
            if (!$(this).hasClass('notActive')) {
                elfinderInstance.exec('copy');
            }
        });
        $(document).on('click', '.btn-palel--cut', function() {
            if (!$(this).hasClass('notActive')) {
                elfinderInstance.exec('cut');
            }
        });
        $(document).on('click', '.btn-palel--paste, .btn-clipboard-paste', function() {
            if (!$(this).hasClass('notActive')) {
                elfinderInstance.exec('paste');
                elfinderInstance.clipboard([]);
            }
        });
        $(document).on('click', '.btn-clipboard-cancel', function(e) {
            removePasteSpecialButtons();
        });
        $(document).on('click', '.btn-palel--rename', function() {
            if (!$(this).hasClass('notActive')) {
                elfinderInstance.exec('rename');
            }
        });
        $(document).on('click', '.btn-palel--remove', function() {
            if (!$(this).hasClass('notActive')) {
                elfinderInstance.exec('rm');
            }
        });
        /*
         $(document).on('click', '.btn-palel--view', function() {
         var sel = elfinderInstance.selectedFiles();
         if (sel.length) {
         console_log('view: ' + sel[0].hash + '\n Хз что за кнопка и как будет работать');
         }
         });
         */
        $(document).on('click', '.manager-search__button', function() {
            if (!$(this).hasClass('notActive')) {
                if ($.trim($('#manager-search-text').val()) == '') {
                    $('#manager-search-text').val('');
                    elfinderInstance.exec('open', elfinderInstance.cwd().hash);
                } else {
                    elfinderInstance.exec('search');
                }
            }
        });
        $('#manager-search-text').on('keyup input', function(event) {
            //$( "#manager-search-text" ).keyup(function() {
            if ($.trim($(this).val()) == '') {
                $('#manager-search-text').val('');
                elfinderInstance.exec('open', elfinderInstance.cwd().hash);
            }
        });
        $(document).on('click', '.manager-searchreset__button', function() {
            if (!$(this).hasClass('notActive')) {
                elfinderInstance.trigger('searchend');
                $('#manager-search-text').val('');
                elfinderInstance.exec('open', elfinderInstance.cwd().hash);
            }
        });
        $(document).on('click', '.btn-palel--structure', function() {
            if (!$(this).hasClass('notActive')) {
                elfinderInstance.exec('view');
                elfinderInstance.exec('reload');
            }
        });
        $(document).on('click', '.btn-palel--view', function() {
            if (!$(this).hasClass('notActive')) {
                /*
                 var $manager_search_text = $('#manager-search-text');
                 if ($.trim($manager_search_text.val()) != '') {
                 $manager_search_text.val('');
                 elfinderInstance.searchStatus.state = 0;

                 //elfinderInstance.sync();
                 elfinderInstance.exec('open', elfinderInstance.root());
                 elfinderInstance.request({
                 data   : {cmd  : 'parents', target : elfinderInstance.root()},
                 syncOnFail : false
                 });

                 }
                 */
                elfinderInstance.trigger('searchend');
                $('#manager-search-text').val('');

                if ($(this).hasClass('show-deleted-files')) {
                    $(this).removeClass('show-deleted-files').addClass('hide-deleted-files');
                } else {
                    $(this).removeClass('hide-deleted-files').addClass('show-deleted-files');
                }
                //elfinderInstance.exec('view');
                console_log('reload');
                //elfinderInstance.exec('open', elfinderInstance.cwd().hash);
                //elfinderInstance.exec('parents', elfinderInstance.cwd().hash);

                elfinderInstance.exec('reload');
                console_log('reload2');
            }
        });
        $(document).on('click', '.btn-palel--search', function() {

            var $item_search_form = $('.item-search-form');
            if ($item_search_form.is(':visible')) {
                $('#manager-search-text').val('');
                elfinderInstance.exec('open', elfinderInstance.cwd().hash);
                $item_search_form.css({display: "none"});
                //console_log('hide_search');
                deltaHeightSearch = 1;
            } else {
                $item_search_form.css({display: "inline-block"});
                deltaHeightSearch = 30;
                //console_log('show_search');
            }

            search_showed_by_user = !search_showed_by_user;
            if (deltaHeightSearch) { delta_height = delta_height - deltaHeightSearch; }
            resizeElfinder();

        });

        $(document).on('click', '.ch-user-collaborate-access', function() {
            var data = {
                colleague_email   : $('#colleague-email').val(),
                colleague_message : $('#colleague-message').val(),
                access_type       : $(this).attr('data-action'),
                access_type_name  : $(this).attr('data-subtext'),
                colleague_id      : $(this).attr('data-tokens'),
                file_uuid         : $('#collaborate-file-uuid').val(),
            }
            console_log(data);
            changeUserCollaborateAccess(data)
        });
        $(document).on('click', '.ch-user-collaborate-access-new', function() {
            $('#collaborate-user-access-type-new')
                .attr('data-action', $(this).attr('data-action'))
                .html($(this).attr('data-subtext'));
        });

        $(document).on('click', '.restore-patch', function() {
            restorePatchForFile($(this).attr('data-event-id'), $(this).attr('data-restore-status'));
        });

        $(document).on('click', '.cancel-collaboration', function() {
            if (!$(this).hasClass('btn-notActive')) {
                cancelCollaboration();
            }
        });

        $(document).on('click', '.leave-collaboration', function() {
            leaveCollaboration();
        });

        $(document).on('click', '.close-alert-no-nodes', function() {
            $('#alert-no-nodes-online').addClass('hidden');
        });

        /*
         $(document).on('click', '.a-context-menu', function() {
         console_log($(this).attr('data-target-hash'));
         //e.preventDefault();
         //e.stopPropagation();
         });
         */

        $(document).on('touchstart click', '.elfinder-cwd-view-th-contmenu', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            var coord = e.originalEvent.touches[0];
            var target = $(e.target);
            showFileContextMenu(coord, target);
            return false;
        });

        /*
         $(document).on('touchmove touchend ', '.elfinder-cwd-view-th-contmenu a', function (e) {
         clearTimeout(touch_tmt);
         if (e.type == 'touchmove') {
         $(this).parent().parent().removeClass(hover);
         }
         });
         */
        /*
         $(document).on('click','.folder-up', function(e) {
         elfinderInstance.exec('up');
         //e.preventDefault();
         //e.stopPropagation();
         });

         $(document).on('dblclick','.folder-up', function(e) {
         elfinderInstance.exec('up');
         e.preventDefault();
         e.stopPropagation();
         });
         */

        /*
         $(document).on('click', '.btn-showNodes', function() {
         console_log(nodesOnline);
         console_log(typeof nodesOnline);
         checkNodesOnline(onCommandsNotShowBalloonNodes);
         });
         $(document).on('click', '.btn-addNode', function() {
         nodesOnline[nodesOnline.length] = nodesOnline.length + 1;
         console_log(nodesOnline);
         checkNodesOnline(onCommandsNotShowBalloonNodes);
         });
         */

        var clipboard = new Clipboard('.copy-button');
        clipboard.on('success', function(e) {
            snackbar('copied-ok', 'success', 5000, null, 'share-link-copied-button');
            //flash_msg('copied-ok', 'success', 2000, false, null, 'share-link-copied-button');
        });
    }

    initDownloadDiv();
    //checkNodesOnline(onCommandsNotShowBalloonNodes);
});