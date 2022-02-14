/** ******************************************* SHARE *********************************************** */
/**
 * showShareLink
 * @param {object} f
 */
function showShareLink(f)
{
    if (f.file_shared && f.share_link) {
        return '<a href="' + f.share_link + '" target="_blank" rel="noopener" alt="Link" title="Link" class="link-bunch"></a>';
    } else {
        return '';
    }
}

/**
 * showShareDropMenu
 * @param {string} hash
 */
function showShareDropMenu(hash)
{
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
 * @param {object} f
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
 * @param {object} data
 */
function setShareParams(data)
{
    var $share_password = $('#share-password'),
        $share_settings_button = $('#share-settings-button'),
        $share_ttl_div = $('#share-ttl-div'),
        $share_password_text = $('.field-share-password').find('input[type=text]').first(),
        $share_ttl = $('#share-ttl');

    showShareTabs('#link-get');
    $('#filesystem-hash').val(data.hash);
    $share_password.parent().removeClass('has-error').find('p').first().html('');
    $('#share-email').val('').parent().removeClass('has-error').find('p').first().html('');
    if (data.payed) {
        $('#info-settings-link-update-to-pro').hide();
        $('#settings-link-update-to-pro').hide();
        $share_ttl.prop("disabled", false);
        $share_password
            .prop("readonly", false)

            .removeClass('input-notActive');
        $share_password_text
            .prop("readonly", false)
            .removeClass('input-notActive');
        $share_settings_button
            .removeClass('btn-notActive');
        $share_ttl_div.attr('title', $share_ttl_div.attr('title_payed'))
            .removeClass('select-color-gray')
            .addClass('select-color-orange');
    } else {
        $('#info-settings-link-update-to-pro').show();
        $('#settings-link-update-to-pro').show();
        $share_ttl.prop("disabled", true);
        $share_password.prop("readonly", true)
            .addClass('input-notActive');
        $share_password_text
            .prop("readonly", true)
            .addClass('input-notActive');
        $share_settings_button
            .addClass('btn-notActive');
        $share_ttl_div
            .removeClass('select-color-orange')
            .addClass('select-color-gray');

        var $button_select = $share_ttl_div.find('.bootstrap-select').first().find('button').first();
        $button_select[0].title = "";
        setTimeout(function() { $button_select[0].title = ""; }, 1000);
    }
    if (data.file_shared) {
        $('#share-hash').val(data.share_hash);
        $('#share-link-field').val(data.share_link);
        $share_ttl.val(data.share_ttl_info);
        $share_password.val(data.share_password);

        showShareTabs('#link-get-active');
    } else {
        $('#share-hash').val('');
        $('#share-link-field').val('');
        $share_ttl.val(0); // Ссылка бессрочна (TTL_WITHOUTEXPIRY)
        $share_password.val('');

        showShareTabs('#link-get');
    }

    var $parent = $share_ttl.parents('.select-wrap');
    $share_ttl.select2({
        minimumResultsForSearch: -9999999,
        dropdownAutoWidth : true,
        width: 'auto',
        dropdownParent: $parent,
        placeholder: {
            id: '-9999999',
            text: $share_ttl.data('placeholder')
        }
    });
}

/**
 *
 * @param $tab_id
 */
function showShareTabs(tab_id)
{
    $('.tab-pane-share').hide();
    $('.link-get-active').hide();
    if (tab_id == '#link-settings') {
        $('.link-get-active').show();
    }
    $(tab_id).show();
}

/**
 *
 * @param {string} hash
 */
function exec_share(hash)
{
    var file = elfinderInstance.file(hash);
    showShareDialog(file);
}

/**
 * Show popup for sharing
 * @param {object} file
 */
function showShareDialog(file)
{
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

    /** ++ Init share popup*/
    var $share_password  = $('#share-password'),
        $share_ttl_div   = $('#share-ttl-div');

    $('#trigger-share-create-remove-modal').trigger( "click" );
    showShareTabs('#link-get');

    $('#filesystem-hash').val(hash);
    $share_password.parent().removeClass('has-error').find('p').first().html('');
    $('#share-email').val('').parent().removeClass('has-error').find('p').first().html('');

    $('#info-settings-link-update-to-pro').hide();
    $('#settings-link-update-to-pro').hide();
    $('#share-ttl').prop("disabled", true);

    $share_password.prop("readonly", true);
    $('#share-settings-button')
        .addClass('btn-notActive');
    var link = $('#' + hash).find('a.link-bunch:first');
    if (link.length) {
        $('#share-link-field').val(link.attr('href'));
        showShareTabs('#link-get-active');
    } else {
        showShareTabs('#link-get');
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
                    setShareParams(data);
                } else {
                    $.fancybox.close();
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
 * @param {string} hash
 * @param {string} share_ttl
 * @param {string} share_password
 */
function shareElement(hash, share_ttl, share_password)
{
    $.ajax({
        type: 'get',
        url: _LANG_URL + '/elfind?cmd=share&target=' + hash +
        '&share_ttl=' + parseInt(share_ttl) +
        '&share_password=' + encodeURIComponent(share_password),
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true && typeof response.data != 'undefined') {
                    var data = response.data,
                        $data_hash = $('#' + data.hash);
                    $('#nav-' + data.hash).addClass("shared_element");
                    $data_hash.addClass("shared_element");
                    var f = elfinderInstance.file(data.hash);
                    f.file_shared = true;
                    $data_hash.find('div.elfinder-cwd-icon').each(function() {
                        $(this).addClass('elfinder-cwd-iconshared_element').removeClass('elfinder-cwd-icon');
                    });
                    $data_hash.find('.sharelink').each(function() {
                        $(this).html(showShareLink(data));
                    });
                    setShareParams(data);
                } else {
                    var $data_hash_err = $('#' + hash);
                    $data_hash_err.removeClass("shared_element");
                    $data_hash_err.find('div.elfinder-cwd-iconshared_element').each(function() {
                        $(this).addClass('elfinder-cwd-icon').removeClass('elfinder-cwd-iconshared_element');
                    });
                    var replace = null;
                    if ("data" in response) {
                        replace = response.data;
                    }
                    snackbar(response.info, 'error', 3000, replace, 'shareElement');
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
 * @param {string} hash
 */
function unshareElement(hash)
{
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


/** ******************************************* DOCUMENT READY *********************************************** */
$(document).ready(function() {

    if (elfinderInstance) {

        $(document).on('click', '.link-settings', function () {
            showShareTabs('#link-settings');
        });

        $(document).on('click', '.link-get-active', function () {
            showShareTabs('#link-get-active');
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

            if (form.find('.has-error').length) {
                return false;
            }
            if ($('#share-settings-button').hasClass('btn-notActive')) {
                return false;
            }

            shareElement(
                $('#filesystem-hash').val(),
                $('#share-ttl').val(),
                $('#share-password').val()
            );
            return false;
        });

        $(document).on('beforeSubmit', '#share-send-to-email-form', function () {
            var form = $(this);

            if (form.find('.has-error').length) {
                return false;
            }

            var $share_email = $('#share-email');
            if ($share_email.val() == '') {
                $share_email.parent().removeClass('has-success').addClass('has-error').find('p').first().html('E-mail can\'t be empty');
                return false;
            }

            $.ajax({
                type: 'post',
                url: _LANG_URL + '/user/share-send-to-email',
                data: '&share_hash=' + $('#share-hash').val() + '&share_email=' + encodeURIComponent($share_email.val()),
                dataType: 'json',
                statusCode: {
                    200: function (response) {
                        if (response.status == true) {

                            snackbar('share-sent-ok', 'success', 2000, {'email': $share_email.val()}, 'share-send-to-email');
                            $share_email.val('');

                        } else {
                            //error
                            snackbar(response.info, 'error', 2000, null, 'share-send-to-email');
                        }
                    },
                    500: function (response) {
                        console_log(response);
                        alert('An internal server error occurred.');
                    }
                }
            });

            return false;
        });

    }
});