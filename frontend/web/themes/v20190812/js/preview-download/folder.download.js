var timeoutOnResizeSH;

/**
 *
 */
function resizeFileNameInSharedFolder()
{
    var $file_list = $('#file-list-table');

    var elStyle = document.getElementById('fileNameStyle');
    document.head.removeChild(elStyle);
    var sheet;
    document.head.appendChild(elStyle);
    sheet = elStyle.sheet;

    var w_td = $file_list.find('.file-list-table-td-name').first().width() - 10;

    sheet.insertRule('.file-catalog, .elfinder-cwd-icon-folder-download { width: ' + w_td + 'px !important; }', 0);
    sheet.insertRule('.folder-download-top-name { width: ' + parseInt(w_td + w_td * 0.2) + 'px !important; }', 1);
}


/********* ***** *********/
$(document).ready(function() {

    resizeFileNameInSharedFolder();
    $(window).on('resize', function(e) {
        clearTimeout(timeoutOnResizeSH);
        timeoutOnResizeSH = setTimeout(function() {

            resizeFileNameInSharedFolder();

        }, 400);
    });

});
