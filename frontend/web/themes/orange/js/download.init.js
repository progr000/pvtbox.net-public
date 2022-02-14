$(document).ready(function() {
    var os = $.client.os.toLowerCase();
    //console_log($.client);
    //console_log(navigator.userAgent);
    var url = '';
    var version = '';
    var ismobile = false;
    switch (os) {
        case 'iphone':
            url = $('#download-ios').attr('href');
            version = $('#download-ios').attr('data-version');
            //$('.os-type-ios').hide();
            ismobile = true;
            break;
        case 'android':
            url = $('#download-android').attr('href');
            version = $('#download-android').attr('data-version');
            //$('.os-type-android').hide();
            ismobile = true;
            break;
        case 'linux':
            //var arch = $.client.arch;
            //url = $('#download-linux' + arch).attr('href');
            url = $('#download-linux').attr('href');
            version = $('#download-linux').attr('data-version');
            //$('.os-type-linux').hide();
            break;
        case 'windows':
            url = $('#download-windows').attr('href');
            version = $('#download-windows').attr('data-version');
            //$('.os-type-windows').hide();
            //ismobile = true;
            break;
        case 'mac':
            url = $('#download-mac').attr('href');
            version = $('#download-mac').attr('data-version');
            //$('.os-type-mac').hide();
            break;
        default:
            url = '';
            version = '';
            break;
    }

    $('#os-type').val(os);

    $(document).find('a.download-link').each(function () {
        $(this).attr("href", url);
        //alert($(this).html());
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

        //if (ismobile) {
        //    $('#download-descktop').hide();
        //    $('#download-mobile').show();
        //    $('.device-img').hide();
        //    $('#img-' + os).show();
        //} else {
        $('#row-loading-indicator').hide();
        $('.div-row-download').hide();
        $('#show-other-platform').show();
        if (!ismobile) {
            $('#div-download-descktop').show();
            setTimeout(function () {

                //$(location).attr("href", url);

                var f = document.createElement('form');
                console_log(f);
                f.action = url;
                //f.target = "_blank";
                f.method = 'get';
                //f.target = '_blank';
                document.body.appendChild(f);
                f.submit();
                //window.location = url;
                return false;

                /*
                 var f = document.createElement('iframe');
                 f.width = "1px";
                 f.height = "1px";
                 f.style = "display: none;";
                 document.body.appendChild(f);
                 f.src = url;
                 */
            }, 1500);
        } else {

            $('#div-download-' + os).show();

            if (os == 'iphone' && IS_GUEST) {
                $('#create-account-button').show();
            } else {
                $('#create-account-button').hide();
            }
        }
    }
});

function showDownloadOther()
{
    if ($('#other-platforms').is(':visible')) {
        $('#other-platforms').hide('500');
    } else {
        $('#other-platforms').show('500');
    }
}