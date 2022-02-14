var socket = null;
var elfinderInstance = null;
var elFinderLang = 'en';
var deltaListHeight = - 10;
//var ii = 0;

$(document).ready(function() {
    //var elfinderInstance = $('#elfinder').elfinder('instance');
    elfinderInstance = initElFinder(elFinderLang);
    initToolBar();

    $(document).on('click', '#create-share-button', function () {
        shareElement(
            $('#filesystem_hash').val(),
            $('#shareelementform-share_lifetime').val(),
            $('#shareelementform-share_password').val()
        );
    });
    $(document).on('click', '#remove-share-button', function () {
        unshareElement($('#filesystem_hash').val());
    });
    $(document).on('click', '#share-show-settings-button', function() {
        showMainOrSetings('settings');
    });
    $(document).on('click', '#share-show-main-button', function() {
        showMainOrSetings('main');
    });
    $(document).on('click', '#share-set-settings-button', function() {
        shareSettings();
    });

    if (elfinderInstance) {
        $(document).on('click', '.btn-createFolder', function() {
            elfinderInstance.exec('mkdir');
        });
        $(document).on('click', '.btn-uploadFile', function() {
            elfinderInstance.exec('upload');
        });
        $(document).on('click', '.btn-palel--home', function() {
            elfinderInstance.exec('home');
        });
        $(document).on('click', '.btn-palel--download', function() {
            var sel = elfinderInstance.selectedFiles();
            if (sel.length) {
                alert('download: ' + sel[0].hash);
            }
        });
        $(document).on('click', '.btn-palel--copy', function() {
            elfinderInstance.exec('copy');
        });
        $(document).on('click', '.btn-palel--cut', function() {
            elfinderInstance.exec('cut');
        });
        $(document).on('click', '.btn-palel--info', function() {
            elfinderInstance.exec('paste');
        });
        $(document).on('click', '.btn-palel--font', function() {
            elfinderInstance.exec('rename');
        });
        $(document).on('click', '.btn-palel--remove', function() {
            elfinderInstance.exec('rm');
        });
        $(document).on('click', '.btn-palel--view', function() {
            var sel = elfinderInstance.selectedFiles();
            if (sel.length) {
                alert('view: ' + sel[0].hash + '\n Хз что за кнопка и как будет работать');
            }
        });
        $(document).on('click', '.manager-search__button', function() {
            elfinderInstance.exec('search');
        });

        $(document).on('click', '.manager-searchreset__button', function() {
            $('#manager-search-text').val('');
            elfinderInstance.exec('open', elfinderInstance.cwd().hash);
        });
        $(document).on('click', '.btn-palel--structure', function() {
            alert('Хз что за кнопка и как будет работать');
        });
    }


    if ($('#SignUrl').length) {
        socket = wsOpen($('#SignUrl').text());
    }
});

function initToolBar()
{
    if (elfinderInstance) {
        elfinderInstance.bind('select', function(e) {
            //var sel = (e.data && e.data.selected)? e.data.selected : [];
            var sel = elfinderInstance.selectedFiles();
            //console.log(sel);

            var root = elfinderInstance.root(),
                cwd  = elfinderInstance.cwd().hash;

            var isRoot = false;
            //console.log(root);
            //console.log(cwd);
            if (root && cwd && root != cwd) {
                $('.btn-palel--home').removeClass('notActive');
                isRoot = true;
            } else {
                $('.btn-palel--home').addClass('notActive');
                $('.btn-palel--download').addClass('notActive');
                $('.btn-palel--copy').addClass('notActive');
                $('.btn-palel--cut').addClass('notActive');
                $('.btn-palel--font').addClass('notActive');
                $('.btn-palel--remove').addClass('notActive');
            }


            if (sel.length > 0) {
                //console.log(sel[0]);
                if (root != sel[0].hash) {
                    $('.btn-palel--download').removeClass('notActive');
                    $('.btn-palel--copy').removeClass('notActive');
                    $('.btn-palel--cut').removeClass('notActive');
                    $('.btn-palel--font').removeClass('notActive');
                    $('.btn-palel--remove').removeClass('notActive');
                    $('.btn-palel--view').removeClass('notActive');
                }
                if (!sel[0].is_folder) {
                    //$('.btn-palel--view').removeClass('notActive');
                }
            } else {
                $('.btn-palel--download').addClass('notActive');
                $('.btn-palel--copy').addClass('notActive');
                $('.btn-palel--cut').addClass('notActive');
                $('.btn-palel--font').addClass('notActive');
                $('.btn-palel--remove').addClass('notActive');
                $('.btn-palel--view').addClass('notActive');
            }

            var clp = elfinderInstance.clipboard();
            if (clp.length > 0) {
                $('.btn-palel--info').removeClass('notActive');
            } else {
                $('.btn-palel--info').addClass('notActive');
            }

        });
    }
}

function wsOpen(url)
{
    var socket = new WebSocket(url);
    if (socket) {
        socket.onopen = function () {
            console.log("Соединение установлено.");
        };

        socket.onclose = function (event) {
            if (event.wasClean) {
                console.log('Соединение закрыто чисто');
            } else {
                console.log('Обрыв соединения'); // например, "убит" процесс сервера
            }
            console.log('Код: ' + event.code + ' причина: ' + event.reason);
        };

        socket.onmessage = function (event) {
            console.log("Получены данные " + event.data);
        };

        socket.onerror = function (error) {
            console.log("Ошибка " + error.message);
        };
    }
    return socket;
}

function wsSend(message)
{
    if (!socket) {
        socket = wsOpen($('#SignUrl').text());
    }
    socket.send(message);
}

/**
 *
 * @param f object
 * @param share_link string
 */
function showShareLink(f)
{
    //alert(share_link);
    if (f.file_shared && f.share_link) {
        return '<a href="' + f.share_link + '" target="_blank" alt="Link" title="Link" class="link-bunch"></a>';
    } else {
        return '';
    }
}

/**
 *
 * @param hash string
 */
function showShareDropMenu(hash)
{
    //console.log($('#shareDropMenu_' + hash).html());
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
 *
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
    //return '<input type="button" class="btn btn-success btn-xs shareelement" name="' + f.hash + '" onclick="showShareDialog(\'' + f.hash + '\')" value="Share" />';
    //console.log(f);
    if (f.is_folder) {
        return '<div class="workspace-sub__box">' +
                '<div class="dropdown-share dropdown" id="shareDropMenu_' + f.hash + '">' +
                    '<div class="dropdown-toggle" data-toggle="dropdown" id="buttonDropMenu_' + f.hash + '" onclick="showShareDropMenu(\'' + f.hash + '\')">Share</div>' +
                    '<ul class="dropdown-menu">' +
                        '<li><span data-toggle="modal" data-target="#getLink" onclick="showShareDialog(\'' + f.hash + '\')">Get link</span></li>' +
                        '<li><span data-toggle="modal" data-target="#settings">Collaboration Settings</span></li>' +
                    '</ul>' +
                '</div>' +
               '</div>';
    } else {
        return '<div class="dropdown-share dropdown-share-empty" data-toggle="modal" data-target="#getLink">' +
                '<div class="dropdown-toggle">Share</div>' +
               '</div>';
    }

    /*
    return '<input type="button" class="btn btn-success btn-xs shareelement ' + share +'" name="' + hash + '" onclick="shareElement(\'' + hash + '\')" value="Share" />' +
           '<input type="button" class="btn btn-danger btn-xs unshareelement ' + unshare + '" name="' + hash + '" onclick="shareElement(\'' + hash + '\')" value="Cancel share" />';
    */
}

/**
 *
 * @param data object
 */
function buttonShareDialog(data)
{
    //alert(data.file_shared);
    if (data.file_shared) {
        $('#create-share-button').hide();
        $('#remove-share-button').show();
        $('#share-show-settings-button').attr('disabled', 'false').removeAttr('disabled');
        //alert($('#share-show-settings-button').attr('disabled'));
    } else {
        $('#create-share-button').show();
        $('#remove-share-button').hide();
        $('#share-show-settings-button').attr('disabled', 'disabled');
    }
    $('#filesystem_hash').val(data.hash);
    $('#share-link-field').val(data.share_link);
}

/**
 *
 * @param v string
 */
function showMainOrSetings(v)
{
    if (v == 'main') {
        $('#share-popup-link-main').show();
        $('#share-popup-link-main-header').show();
        $('#share-popup-link-settings').hide();
        $('#share-popup-link-settings-header').hide();
    } else {
        $('#share-popup-link-main').hide();
        $('#share-popup-link-main-header').hide();
        $('#share-popup-link-settings').show();
        $('#share-popup-link-settings-header').show();
        return true;
    }
}

/**
 *
 * @param hash string
 */
function showShareDialog(hash)
{
    //alert("showShareDialog: " + hash);
    $.ajax({
        type: 'get',
        url: '/elfind?cmd=shareDialog&target=' + hash,
        dataType: 'json',
        statusCode: {
            200: function (response) {
                if (response.status == true && typeof response.data != 'undefined') {
                    $('#share-create-remove-modal').modal({"show":true});
                    $('#shareelementform-share_lifetime').val(response.data.share_lifetime);
                    $("#shareelementform-share_password").val(response.data.share_password);
                    showMainOrSetings('main');
                    buttonShareDialog(response.data);
                } else {
                    //error
                    alert(response.info);
                }
            }
        }
    });

}


function shareSettings()
{
    res = shareElement(
        $('#filesystem_hash').val(),
        $('#shareelementform-share_lifetime').val(),
        $('#shareelementform-share_password').val()
    );
}

/**
 *
 * @param hash string
 * @param share_lifetime string
 * @param share_password string
 */
function shareElement(hash, share_lifetime, share_password)
{
    $.ajax({
        type: 'get',
        url: '/elfind?cmd=share&target=' + hash +
             '&share_lifetime=' + encodeURIComponent(share_lifetime) +
             '&share_password=' + encodeURIComponent(share_password),
        dataType: 'json',
        statusCode: {
            200: function (response) {
                if (response.status == true && typeof response.data != 'undefined') {

                    var data = response.data;
                    buttonShareDialog(data);

                    $('#nav-' + data.hash).addClass("shared_element");
                    $('#' + data.hash).addClass("shared_element");
                    $('#' + data.hash).find('.sharelink').each(function() {
                        $(this).html(showShareLink(data));
                    });
                    showMainOrSetings('main');

                    console.log(response);
                    console.log('share');
                    if (typeof wsSend == "function" && response.event_data) {
                        wsSend(JSON.stringify(response.event_data));
                        console.log(JSON.stringify(response.event_data));
                    }

                } else {
                    //error
                    alert(response.info);
                }
            }
        }
    });
}

/**
 *
 * @param hash string
 */
function unshareElement(hash)
{
    //alert(hash);
    $.ajax({
        type: 'get',
        url: '/elfind?cmd=unshare&target=' + hash,
        dataType: 'json',
        statusCode: {
            200: function (response) {
                if (response.status == true) {

                    var data = response.data;
                    buttonShareDialog(data);

                    $('#nav-' + data.hash).removeClass("shared_element");
                    $('#' + data.hash).removeClass("shared_element");
                    $('#' + data.hash).find('.sharelink').each(function() {
                        $(this).html('');
                    });

                    console.log(response);
                    console.log('unshare');
                    if (typeof wsSend == "function" && response.event_data) {
                        wsSend(JSON.stringify(response.event_data));
                        console.log(JSON.stringify(response.event_data));
                    }

                } else {
                    //error
                    alert(response.info);
                }
            }
        }
    });
}

function initElFinder(lang) {

    elFinder.prototype.commands.share = function () {
        this.exec = function (hashes) {
            //implement what the custom command should do here
            var sel = this.files(sel);
            if (sel.length != 1) {
                return false;
            }
            shareElement(sel[0].hash, "", "");
        }
        this.getstate = function () {
            //return 0 to enable, -1 to disable icon access
            var sel = this.files(sel);
            if (sel.length != 1) {
                return -1;
            }
            if (sel[0].locked == 1) {
                return -1;
            }
            if ($('#' + sel[0].hash).hasClass('shared_element')) {
                return -1;
            }
            if ($('#nav-' + sel[0].hash).hasClass('shared_element')) {
                return -1;
            }

            return 0;
        }
    };

    elFinder.prototype.commands.unshare = function () {
        this.exec = function (hashes) {
            //implement what the custom command should do here
            var sel = this.files(sel);
            if (sel.length != 1) {
                return false;
            }
            unshareElement(sel[0].hash);
        }
        this.getstate = function () {
            //return 0 to enable, -1 to disable icon access
            var sel = this.files(sel);
            if (sel.length != 1) {
                return -1;
            }
            if (sel[0].locked == 1) {
                return -1;
            }
            //alert($('#' + sel[0].hash).hasClass('shared_element'));
            //alert($('#nav-' + sel[0].hash).hasClass('shared_element'));
            if (!$('#' + sel[0].hash).hasClass('shared_element') &&
                !$('#nav-' + sel[0].hash).hasClass('shared_element')) {
                return -1;
            }

            return 0;
        }
    };


    elFinder.prototype.commands.search = function() {
        this.title          = 'Find files';
        this.options        = {ui : 'searchbutton'}
        this.alwaysEnabled  = true;
        this.updateOnSelect = false;

        this.getstate = function() {
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
        height: $(window).height() - 200,
        url: '/elfind',     // connector URL (REQUIRED)
        lang: lang,  // language (OPTIONAL)
        defaultView: 'list',
        uiOptions: {
            // toolbar configuration
            toolbar: [
                //['back', 'forward'],
                ['reload'],
                ['home', 'up'],
                ['mkdir', /*'mkfile',*/ 'upload'],
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
                // auto load current dir parents
                syncTree: true,
            },

            // navbar options
            navbar: {
                //width: 200,
                minWidth: 200,
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
                    columns: ['date', 'size', 'sharelink', 'sharebutton'],
                    // override this if you want custom columns name
                    // example
                    columnsCustomName : {
                        //date        : 'Modified',
                        sharelink   : '&nbsp;',
                        sharebutton : '&nbsp;'
                    },
                    // fixed list header colmun
                    fixedHeader: true,
                },
            },

        },
        commands: [
            'unshare', 'share', 'open', 'reload', 'home', 'up', 'back', 'forward', 'getfile',
            'download', 'rm', 'duplicate', 'rename', 'mkdir', 'mkfile', 'upload', 'copy',
            'cut', 'paste', 'edit', 'extract', 'archive', 'search', 'info', 'view', 'help', 'resize', 'sort'
        ],

        contextmenu: {
            // navbarfolder menu
            navbar: ['open', '|', 'copy', 'cut', 'paste', /*'duplicate',*/ '|', 'rm', '|', 'info', '|', 'share', 'unshare'],
            // current directory menu
            cwd: ['reload', 'back', '|', 'upload', 'mkdir', /*'mkfile',*/ 'paste', '|', 'sort', '|', 'info', '|', 'share', 'unshare'],
            // current directory file menu
            files: ['copy', 'cut', 'paste', /*'duplicate',*/ '|', 'rm', '|', /*'edit',*/ 'rename', '|', 'info', '|', 'share', 'unshare']
        },
        /*
        commandsOptions : {
            getfile: {
                multiple: true
            }
        },
        */
        getFileCallback: function () {
            return false;
        },
    });
    //console.log($('#elfinder-elfinder-cwd-thead').html());
    $('#elfinder-elfinder-cwd-thead').find('tr').each(function() {
        console.log($(this).css());
    })
    return $('#elfinder').elfinder('instance');
}
