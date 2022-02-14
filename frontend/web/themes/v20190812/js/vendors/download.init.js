/**
 *
 */
function showDownloadOther()
{
    if ($('#other-platforms').is(':visible')) {
        $('#other-platforms').hide('500');
    } else {
        $('#other-platforms').show('500');
    }
}

$(document).ready(function() {
    var os = $.client.os.toLowerCase();
    //console_log($.client);
    var url = '';
    var version = '';
    var software_type = '';
    var ismobile = false;
    var os_found = false;
    switch (os) {
        case 'iphone':
            url = $('#download-ios').attr('href');
            version = $('#download-ios').attr('data-version');
            software_type = $('#download-ios').data('software-type');
            ismobile = true;
            os_found = true;
            break;
        case 'ipad':
            url = $('#download-ios').attr('href');
            version = $('#download-ios').attr('data-version');
            software_type = $('#download-ios').data('software-type');
            os = 'iphone';
            ismobile = true;
            os_found = true;
            break;
        case 'android':
            url = $('#download-android').attr('href');
            version = $('#download-android').attr('data-version');
            software_type = $('#download-android').data('software-type');
            ismobile = true;
            os_found = true;
            break;
        case 'linux':
            //url = $('#download-linux').attr('href');
            //version = $('#download-linux').attr('data-version');
            var distr = $.client.useragent.toLowerCase();
            if (distr.indexOf('debian') > 0) {
                url = $('#download-debian').attr('href');
                version = $('#download-debian').attr('data-version');
                software_type = $('#download-debian').data('software-type');
                os_found = true;
            } else if (distr.indexOf('ubuntu') > 0) {
                url = $('#download-ubuntu').attr('href');
                version = $('#download-ubuntu').attr('data-version');
                software_type = $('#download-ubuntu').data('software-type');
                os_found = true;
            } else if (distr.indexOf('centos') > 0) {
                url = $('#download-centos').attr('href');
                version = $('#download-centos').attr('data-version');
                software_type = $('#download-centos').data('software-type');
                os_found = true;
            } else if (distr.indexOf('suse') > 0) {
                url = $('#download-suse').attr('href');
                version = $('#download-suse').attr('data-version');
                software_type = $('#download-suse').data('software-type');
                os_found = true;
            } else {
                url = '';
                version = '';
                software_type = '';
                os_found = false;
            }
            break;
        case 'windows':
            url = $('#download-windows').attr('href');
            version = $('#download-windows').attr('data-version');
            software_type = $('#download-windows').data('software-type');
            os_found = true;
            break;
        case 'mac':
            url = $('#download-mac').attr('href');
            version = $('#download-mac').attr('data-version');
            software_type = $('#download-mac').data('software-type');
            os_found = true;
            break;
        default:
            url = '';
            version = '';
            software_type = '';
            os_found = false;
            break;
    }

    //console_log(software_type);
    $('#os-type').val(os);

    $(document).find('a.download-link').each(function () {
        $(this).attr("href", url);
        if (ismobile) { $(this).attr("target", '_blank').attr('rel', "noopener"); }
    });
    $('#software-version').html(version);

    if (os == 'iphone' && IS_GUEST) {
        $('#create-account-button-ios').show();
    } else {
        $('#create-account-button-ios').hide();
    }

    var is_download_page = $('#is-download-page').length;

    if (is_download_page) {
        $(document).on('click', '.download-other-platforms', function () {
            showDownloadOther();
            return false;
        });

        $('#is-download-page').show();
        $('#row-loading-indicator').hide();
        $('.div-row-download').hide();
        $('#show-other-platform').show();
        if (!ismobile) {
            if (os_found) {
                $('#div-download-desktop').show();
                hideSiteLoader();
                setTimeout(function () {
                    /*
                     var f = document.createElement('form');
                     //console_log(f);
                     f.action = url;
                     f.method = 'get';
                     f.enctype = "multipart/form-data";
                     f.download = true;
                     document.body.appendChild(f);
                     f.submit();
                     */
                    var anchor = document.createElement('a');
                    anchor.href = url;
                    anchor.download = true;
                    anchor.class = "hidden";
                    anchor.target = '_self';
                    //var tmp = document.getElementById('wss-data');
                    //tmp.appendChild(anchor);
                    anchor.click();
                    return false;
                }, 1500);
            } else {
                $('#div-download-desktop').hide();
                $('.download-other-platforms').hide();
                showDownloadOther();
            }
        } else {

            $('#is-download-page').css({ "text-align" : "center"});
            $('#div-download-' + os).show();


            if (os == 'iphone' && IS_GUEST) {
                $('#create-account-button').show();
            } else {
                $('#create-account-button').hide();
            }
        }
    }
});
