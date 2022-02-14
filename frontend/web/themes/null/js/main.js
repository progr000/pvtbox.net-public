$(document).ready(function(){
    getCountNewEvents();
});

function getCountNewEvents()
{
    $.ajax({
        type: 'get',
        url: '/site/count-new-events',
        dataType: 'json',
        statusCode: {
            200: function (response) {

                // ++Tikets
                var countUnreadTikets = parseInt(response.countUnreadTikets);
                if (countUnreadTikets > 0) {
                    $('#count-new-tikets').html(' (' + countUnreadTikets +')');
                }

                // ++Nodes
                var countOnlineNodes = parseInt(response.countOnlineNodes);
                if (countOnlineNodes > 0) {
                    $(document).find('.count-online-nodes').each(function() {
                        $(this).html(' (' + countOnlineNodes +')');
                    })
                }

                setTimeout(function() { getCountNewEvents(); }, 10000);
            }
        }
    });
}