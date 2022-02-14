$(document).ready(function(){
    var $body_doc = $('body');
    var admin_role = parseInt($body_doc.attr('data-admin-role'));
    if (admin_role == 0) {
        getCountUnreadTikets();
    }
});

function getCountUnreadTikets()
{
    $.ajax({
        type: 'get',
        url: '/tikets/count-unread-tikets',
        dataType: 'json',
        statusCode: {
            200: function (response) {
                var countUnread = parseInt(response.countUnread);
                if (countUnread > 0) {
                    $('#count-new-tikets').html(' (' + countUnread +')');
                }
                setTimeout(function() { getCountUnreadTikets(); }, 180000);
            }
        }
    });
}