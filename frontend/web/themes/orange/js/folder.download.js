$(document).ready(function() {


    resizeFileName();
    $(window).on('resize', function(e) {
        resizeFileName();
    });

});

function resizeFileName()
{
    var $file_list = $('#file-list-table');

    var elStyle = document.getElementById('fileNameStyle');
    document.head.removeChild(elStyle);
    var sheet;
    document.head.appendChild(elStyle);
    sheet = elStyle.sheet;

    var w_td = $file_list.find('.file-list-table-td-name').first().width() - 10;
    //console_log(w_td);


    var idx_rule  = sheet.insertRule('.file-catalog, .elfinder-cwd-icon-folder-download { width: ' + w_td + 'px !important; text-overflow: ellipsis !important; overflow: hidden !important; }', 0);
    var idx_rule2 = sheet.insertRule('.folder-download-top-name { width: ' + parseInt(w_td + w_td *0.2) + 'px !important; }', 1);
}