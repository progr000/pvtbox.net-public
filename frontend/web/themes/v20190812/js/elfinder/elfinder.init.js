var elfinderInstance = null;
var DropzoneInstance = null;
var deltaListHeight = - 10;
var deltaHeightSearch = 0;
var onCommandsNotShowBalloonNodes = true;
var delta_height = -165;
var alerts_outerHeight = [];
var last_reload_elf = 0;
var search_showed_by_user = false;

var current_W_W = 0;
var current_W_H = 0;

var timeoutOnResize;


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
 * установка начальных координат для попап
 */
var dwl_pos_x = 100;
var dwl_pos_y = 100;
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
 * @param {object} file
 * @returns {boolean}
 */
function checkIsFileSynced(file)
{
    if ("file_uuid" in file) {
        if (!(file.file_uuid === null)) {
            return true;
        }
    }
    elfinderInstance.error('This file is not yet synced with your devices. Can\'t execute any action with it.');
    return false;
}

/**
 * @param {object} coord
 * @param {object} target
 */
function showFileContextMenu(coord, target)
{
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
            .removeClass('ui-selected')
            .find('input:checkbox')
            .first()
            .attr('checked', false)
            .prop('checked', false);

    }, 100);
}

/**
 *
 */
function showSortStatus()
{
    if (elfinderInstance) {
        var type = elfinderInstance.sortType;
        var order = elfinderInstance.sortOrder;
        var folder = elfinderInstance.sortStickFolders;
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
    showSortStatus();

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

            showSortStatus();
        });

    $(document).on('click', '.btn-panel--sort', function(e) {
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
 * @param {object} elfInst
 */
function toolbarButtonsPrepare(elfInst)
{
    $('#elfinderPanel').find('.all-btn-panel').each(function () {
        $(this).addClass('notActive');
    });

    $('.btn-panel--reload').removeClass('notActive');
    $('.btn-panel--view').removeClass('notActive');
    $('.btn-panel--structure').removeClass('notActive');
    $('.btn-panel--sort').removeClass('notActive');
    $('.btn-panel--search').removeClass('notActive');
    //$('.btn-panel--up').removeClass('notActive');

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
        $('.btn-panel--home').removeClass('notActive');
        $('.btn-panel--up').removeClass('notActive');
    }

    if (nodesOnline.length) {

        /* Если есть какой то текущий каталог и он не удаленный */
        if (cwd && !cwd.file_deleted) {
            $('.btn-uploadFile').removeClass('notActive');
            $('.btn-createFolder').removeClass('notActive');
            $('.btn-panel--upload').removeClass('notActive');
            $('.btn-panel--folder').removeClass('notActive');
            $('.unlocked-upload').show().css({display: "inline-block"});
            $('.locked-upload').hide();
        } else {
            $('.unlocked-upload').hide();
            $('.locked-upload').show().css({display: "inline-block"});
        }

        /* проверка какой сейчас текущий каталог */
        if (root && cwd.hash && root != cwd.hash) {
            /*
             * Если текущая папка не корневая (главный контейнер)
             * тогда можно сделать активной кнопку домой
             */
            $('.btn-panel--home').removeClass('notActive');
            $('.btn-panel--up').removeClass('notActive');
        } else {
            /*
             * Если текущая папка это root папка
             * ее нельзя ни удалить ни переименовать ни скопировать
             * это главный контейнер. Заблокируем все эти кнопки
             */
            isRoot = true;
            $('.btn-panel--home').addClass('notActive');
            $('.btn-panel--up').addClass('notActive');
            $('.btn-panel--download').addClass('notActive');
            $('.btn-panel--copy').addClass('notActive');
            $('.btn-panel--cut').addClass('notActive');
            $('.btn-panel--rename').addClass('notActive');
            $('.btn-panel--remove').addClass('notActive');
        }

        /* проверка того выбран (подсвечен) ли сейчас какой то файл или папка */
        if (/*выбран*/
            (selectedfiles.length > 0) &&
            /*и выбран не root*/
            (root != selectedfiles[0].hash) &&
            /*и не удален*/
            !selectedfiles[0].file_deleted) {
            $('.btn-panel--download').removeClass('notActive');
            $('.btn-panel--copy').removeClass('notActive');
            $('.btn-panel--cut').removeClass('notActive');
            $('.btn-panel--rename').removeClass('notActive');
            $('.btn-panel--remove').removeClass('notActive');
        } else {
            $('.btn-panel--download').addClass('notActive');
            $('.btn-panel--copy').addClass('notActive');
            $('.btn-panel--cut').addClass('notActive');
            $('.btn-panel--rename').addClass('notActive');
            $('.btn-panel--remove').addClass('notActive');
        }

        /* Для кнопки доунлоад свои проверки*/
        if (selectedfiles.length == 1 && !selectedfiles[0].file_deleted && selectedfiles[0].locked != 1 && !selectedfiles[0].is_folder) {
            $('.btn-panel--download').removeClass('notActive');
        } else {
            $('.btn-panel--download').addClass('notActive');
        }

        /* Для кнопки ренейм тоже свои проверки*/
        if (selectedfiles.length == 1 && !selectedfiles[0].file_deleted && selectedfiles[0].locked != 1) {
            $('.btn-panel--rename').removeClass('notActive');
        } else {
            $('.btn-panel--rename').addClass('notActive');
        }

        /* если что то есть в буфере копирования */
        var clp = elfInst.clipboard();
        if (clp.length > 0 && checkNodesOnline(true)) {
            $('.btn-panel--paste').removeClass('notActive');
            showPasteSpecialButtons();
        } else {
            $('.btn-panel--paste').addClass('notActive');
            removePasteSpecialButtons();
        }

        /* если есть история перемещения по каталогам */
        if (elfInst.history.canBack()) {
            $('.btn-panel--back').removeClass('notActive');
        } else {
            $('.btn-panel--back').addClass('notActive');
        }

        DropzoneInstance.enable();
    } else {
        DropzoneInstance.disable();
    }

    /* если есть история перемещения по каталогам */
    if (elfInst.history.canBack()) {
        $('.btn-panel--back').removeClass('notActive');
    } else {
        $('.btn-panel--back').addClass('notActive');
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
 * замена старой функции scrollForFucckingChromeAndSafari - теперь для всех производим реинит найсскролла
 */
function reInitNiceScrollElfinder(is_from_window_resize)
{
    var $elfinder_cwd_wrapper = $('.elfinder-cwd-wrapper');
    var $elfinder_navbar = $('.elfinder-navbar');
    $elfinder_cwd_wrapper.getNiceScroll().resize();
    $elfinder_navbar.getNiceScroll().resize();

    if (!is_from_window_resize) {
        var $elfinder = $('#elfinder');
        $elfinder.elfinder({resizable: true});
        $elfinder.height($elfinder.height() - 10).resize();
        $elfinder.height($elfinder.height() + 10).resize();
        $elfinder_navbar.height($elfinder_navbar.height() - 10).resize();
        $elfinder_navbar.height($elfinder_navbar.height() + 10).resize();
        //$elfinder_cwd_wrapper.css({width: 'auto'});
    }
}

/**
 *
 */
function resizeFileName()
{
    var min_width = 200,
        elStyle = document.getElementById('fileNameStyle'),
        sheet,
        idx_rule;

    document.head.removeChild(elStyle);
    document.head.appendChild(elStyle);
    sheet = elStyle.sheet;

    var $file_list = $('#elfinder');
    var $elfinder_cwd_wrapper_table = $file_list.find('.elfinder-cwd-wrapper').first().find('table').first();
    var $elfinder_cwd_wrapper_elfinder_cwd_view_icons = $file_list.find('.elfinder-cwd-wrapper').first().find('.elfinder-cwd-view-icons').first();
    var $elfinder_cwd_view_th_name = $file_list.find('.elfinder-cwd-view-th-name').first();

    if ($elfinder_cwd_view_th_name.length) {
        min_width = $elfinder_cwd_view_th_name.width() - 10;
    } else {
        var $wss_data = $('#wss-data');
        if ($wss_data.length && !checkIsMobile()) {
            min_width = parseInt($wss_data.width() / 3);
        }
        //alert(min_width);
        if (!$elfinder_cwd_wrapper_table.length && !$elfinder_cwd_wrapper_elfinder_cwd_view_icons.length) {
            setTimeout(function () {
                resizeFileName();
            }, 1000);
        }
    }

    //sheet.deleteRule(0);
    idx_rule = sheet.insertRule('.elfinder .elfinder-cwd-wrapper-list .elfinder-cwd-file .elfinder-cwd-filename { width: ' + min_width + 'px !important; text-overflow: ellipsis !important; overflow: hidden !important; }', 0);
}

/**
 *
 */
function resizeElfinder()
{
    //if (checkIsMobile() && $(window).width() <= 540) {
    //    $('.hide-on-mobile-when-fm-and-width-less-than-540').hide();
    //} else {
    //    $('.hide-on-mobile-when-fm-and-width-less-than-540').show();
    //}
    resizeFileName();
    var $item_search_form = $('.item-search-form');
    if ($(window).width() >= 660) {
        $item_search_form.css({ display: "inline-block" });
    }
    var $elfinder = $('#elfinder').elfinder({resizable:true});
    var win_height = $(window).height() + delta_height; //delta;
    if ( $elfinder.height() != win_height ) {
        $elfinder.height(win_height).resize();
    }
}

/**
 *
 * @returns {{w_w: number, w_h: number, delta_height: number}}
 */
function reCalcDeltaHeight()
{
    var dh_less_400 = -124,
        dh_400_540  = -124,
        dh_540_657  = -124,
        dh_657_768  = -156,
        dh_768_815  = -176,
        dh_815_860  = -176,
        dh_more_860 = -176;

    var w_w = $(window).width(),
        w_h = $(window).height();

    if (w_w <= 400) { delta_height = dh_less_400; }
    //else if (w_w > 312 && w_w <= 500) { delta_height = dh_less_500; }
    else if (w_w > 400 && w_w <= 540) { delta_height = dh_400_540; }
    else if (w_w > 540 && w_w <= 657) { delta_height = dh_540_657; }
    else if (w_w > 657 && w_w <= 768) { delta_height = dh_657_768; }
    else if (w_w > 768 && w_w <= 815) { delta_height = dh_768_815; }
    else if (w_w > 815 && w_w < 860) { delta_height = dh_815_860; }
    else { delta_height = dh_more_860; }

    if (deltaHeightSearch) { delta_height = delta_height - deltaHeightSearch; }

    var $alert_block_container = $('#alert-block-container');
    if ($alert_block_container.length && $alert_block_container.is(':visible')) {
        delta_height = delta_height - $alert_block_container.outerHeight(true);
    }

    return {
        'w_w' : w_w,
        'w_h' : w_h,
        'delta_height' : delta_height
    };
}

/**
 * @param {string} link
 */
function openInOnlineOffice(link)
{
    var anchor = document.getElementById('online_office_tpl_link');
    if (anchor !== null) {
        anchor.href = link;
        anchor.target = '_blank';
        anchor.click();
    }
}

/** ******************************************* INIT ELFINDER *********************************************** */
/**
 * initElFinder
 * @returns {jQuery}
 */
function initElFinder() {

    /** open-in-oo command */
    elFinder.prototype.commands.open_in_oo = function () {
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
            if (sel[0].oo_link === null) {
                return false;
            }
            openInOnlineOffice(sel[0].oo_link);
        };
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
            if (sel[0].oo_link === null) {
                return -1;
            }
            return 0;
        };
    };

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
        };
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
        };
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
        };
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
        };
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
        };
        this.getstate = function (sel) {
            //return 0;
            //return 0 to enable, -1 to disable icon access
            var sel = this.files(sel);
            if (sel.length != 1) {
                return -1;
            }
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
        };
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
        };
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
        };
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
        };
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
        };
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
        };
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
        };
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
        };
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
        };
    };

    /** Share command */
    elFinder.prototype.commands.globalshare = function () {

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
        };
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
        };
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
        };
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
        };
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
            showShareDialog(sel[0]);
        };
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
        };
    };

    /** Search command */
    elFinder.prototype.commands.search = function() {
        this.title          = 'Find files';
        this.options        = {ui : 'searchbutton'}
        this.alwaysEnabled  = true;
        this.updateOnSelect = false;

        this.getstate = function(sel) {
            return 0;
        };

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
        };

    };

    var $elf = $('#elfinder');
    $elf.elfinder({
        height: $(window).height() + delta_height,
        url: _LANG_URL + '/elfind',     // connector URL (REQUIRED)
        lang: $elf.attr('lang'),  // language (OPTIONAL)
        soundPath: "/themes/v20190812/sounds/",
        defaultView: 'list',
        dateFormat: _GLOBAL.datetime_short_format,
        fancyDateFormat: _GLOBAL.datetime_fancy_format,
        UTCDate: false,
        //reloadClearHistory: true,
        uiOptions: {
            // toolbar configuration
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
                maxWidth: 400
            },

            // current working directory options
            cwd: {
                oldSchool: false,
                listView : {
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
                    showHeader: false
                }
            }

        },
        commands: [
            'download', 'preview', 'restoredeletedfile', 'showfileversions', 'showfileversionsnotactive',
            'collaborate', 'colleaguelist', 'unshare', 'share', 'globalshare', 'open_in_oo',
            'open', 'reload', 'home', 'up', 'back', 'forward', 'getfile',
            'download', 'rm', 'duplicate', 'rename', 'mkdir', 'mkfile', 'copy', //'upload',
            'cut', 'paste', 'edit', 'extract', 'archive', 'search', 'info', 'view', 'help', 'resize', 'sort'
        ],

        contextmenu: {
            // navbarfolder menu
            navbar: ['open', 'download', 'globalshare', '|', 'copy', 'cut', 'paste', 'rename', 'rm', /*'|',*/ 'info'],
            // current directory menu
            cwd: ['reload', 'back', '|', 'mkdir', 'paste', '|', 'sort' /*'mkfile', 'upload', 'preview', 'info', '|', 'share', 'unshare', 'collaborate', 'colleaguelist', 'showfileversions', 'showfileversionsnotactive', 'restoredeletedfile' */],
            // current directory file menu
            files: ['open', 'preview', 'download', 'open_in_oo', 'globalshare', 'restoredeletedfile', '|', 'copy', 'cut', 'rename', 'rm', 'showfileversions', 'showfileversionsnotactive', /*'|',*/ 'info' ]
        },
        resizable: false,
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
            select: function (event, elfinderInstance, origEvent) {

                $("#elfinder-sort-menu-list").hide();
                toolbarButtonsPrepare(elfinderInstance);

            }
        }
    });

    $('#elfinderPanel').show();

    /** https://github.com/inuyaksa/jquery.nicescroll */

    createNiceScroll('.elfinder-navbar', true, true);
    createNiceScroll('.elfinder-cwd-wrapper', false, true);


    // https://github.com/Studio-42/elFinder/wiki/Client-event-API

    DropzoneInstance = initDropzone();

    return $elf.elfinder('instance');
}

var OLD_WH;
var OLD_WW;

// add listener to disable scroll
function noScrollFmWindow() {
    window.scrollTo(0, 0);
}
window.addEventListener('scroll', noScrollFmWindow);

/** ******************************************* DOCUMENT READY *********************************************** */
$(document).ready(function() {

    /**  Инициализируем елфайндер */
    elfinderInstance = initElFinder();
    setTimeout(function() {
        reCalcDeltaHeight();
        resizeElfinder();
        reInitNiceScrollElfinder(false);
    }, 1000);


    /** В момент ресайза окна делаем ресайз ФМ и реинит скроллов */
    $(window).on('resize', function(e) {
        clearTimeout(timeoutOnResize);
        timeoutOnResize = setTimeout(function() {

            var recalc = reCalcDeltaHeight();
            var w_w = recalc.w_w;
            var w_h = recalc.w_h;

            /* остановка ресайза если размеры не поменялись */
            if (OLD_WH == w_h && OLD_WW == w_w) {
                clearTimeout(timeoutOnResize);
                return;
            }

            OLD_WW = w_w;
            OLD_WH = w_h;
            resizeElfinder();
            reInitNiceScrollElfinder(true);
            //console_log(delta_height);

            if (checkIsMobile()) {
                e.preventDefault();
                e.stopPropagation();
                //elfinderInstance.exec('reload');
                var $item_search_form = $('.item-search-form');
                if (w_w < 660 && !search_showed_by_user) {
                    $item_search_form.css({display: "none"});
                    deltaHeightSearch = 1;
                }
            }

        }, 400);
    });

    var $alert_no_nodes_online = $('#alert-no-nodes-online');
    $alert_no_nodes_online
        .on('show', function() {
            //console_log('is Shown ' + parseInt($alert_no_nodes_online.outerHeight(true)));
            //delta_height = delta_height + 20 + (-1 * parseInt($alert_no_nodes_online.outerHeight(true)));
            reCalcDeltaHeight();
            resizeElfinder();
            reInitNiceScrollElfinder(true);
        });
    $alert_no_nodes_online
        .on('hide', function() {
            //console_log('is Hide ' + parseInt($alert_no_nodes_online.outerHeight(true)));
            //delta_height = delta_height - 20 - (-1 * parseInt($alert_no_nodes_online.outerHeight(true)));
            reCalcDeltaHeight();
            resizeElfinder();
            reInitNiceScrollElfinder(true);
        });


    if (elfinderInstance) {

        initSortButton();

        $(document).on('click', '.btn-panel', function() {
            if (!$(this).hasClass('notActive')) {
                $(this).addClass('clicked');
                setTimeout(function() {
                    $('.btn-panel').removeClass('clicked');
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
            }
        });
        $(document).on('click', '.btn-panel--home', function() {
            if (!$(this).hasClass('notActive')) {
                elfinderInstance.exec('home');
            }
        });
        $(document).on('click', '.btn-panel--up', function() {
            if (!$(this).hasClass('notActive')) {
                elfinderInstance.exec('up');
            }
        });
        $(document).on('click', '.btn-panel--reload', function(event) {
            elfinderInstance.exec('reload');
        });
        $(document).on('click', '.btn-panel--back', function() {
            if (!$(this).hasClass('notActive')) {
                elfinderInstance.exec('back');
            }
        });
        $(document).on('click', '.btn-panel--download', function() {
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
        $(document).on('click', '.btn-panel--copy', function() {
            if (!$(this).hasClass('notActive')) {
                elfinderInstance.exec('copy');
            }
        });
        $(document).on('click', '.btn-panel--cut', function() {
            if (!$(this).hasClass('notActive')) {
                elfinderInstance.exec('cut');
            }
        });
        $(document).on('click', '.btn-panel--paste, .btn-clipboard-paste', function() {
            if (!$(this).hasClass('notActive')) {
                elfinderInstance.exec('paste');
                elfinderInstance.clipboard([]);
            }
        });
        $(document).on('click', '.btn-clipboard-cancel', function(e) {
            removePasteSpecialButtons();
        });
        $(document).on('click', '.btn-panel--rename', function() {
            if (!$(this).hasClass('notActive')) {
                elfinderInstance.exec('rename');
            }
        });
        $(document).on('click', '.btn-panel--remove', function() {
            if (!$(this).hasClass('notActive')) {
                elfinderInstance.exec('rm');
            }
        });
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
        $(document).on('click', '.btn-panel--structure', function() {
            if (!$(this).hasClass('notActive')) {
                elfinderInstance.exec('view');
                elfinderInstance.exec('reload');
            }
        });
        $(document).on('click', '.btn-panel--view', function() {
            if (!$(this).hasClass('notActive')) {

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

        $(document).on('click', '.btn-panel--search', function() {
            var $item_search_form = $('.item-search-form');
            if ($item_search_form.is(':visible')) {
                $('#manager-search-text').val('');
                elfinderInstance.exec('open', elfinderInstance.cwd().hash);
                $item_search_form.css({display: "none"});
                deltaHeightSearch = 1;
            } else {
                $item_search_form.css({display: "inline-block"});
                deltaHeightSearch = 30;
            }

            search_showed_by_user = !search_showed_by_user;
            if (deltaHeightSearch) { delta_height = delta_height - deltaHeightSearch; }

            reCalcDeltaHeight();
            resizeElfinder();
            reInitNiceScrollElfinder(true);
        });

        $(document).on('touchstart click', '.elfinder-cwd-view-th-contmenu', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            var coord = e.originalEvent.touches[0];
            var target = $(e.target);
            showFileContextMenu(coord, target);
            return false;
        });

        var clipboard = new Clipboard('.copy-button');
        clipboard.on('success', function(e) {
            snackbar('copied-ok', 'success', 5000, null, 'share-link-copied-button');
        });

        /** FOR TESTING */
        $(document).on('click', '.btn-showNodes', function() {
            nodesOnline = [];
            console_log(nodesOnline);
            console_log(typeof nodesOnline);
            checkNodesOnline(false);
        });
        $(document).on('click', '.btn-addNode', function() {
            nodesOnline[nodesOnline.length] = nodesOnline.length + 1;
            console_log(nodesOnline);
            checkNodesOnline(false);
        });
    }

    initDownloadDiv();
});