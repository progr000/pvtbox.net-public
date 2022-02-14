$(document).ready(function() {

    $(document).on('click', '.glyphicon-check-log', function() {
        $('.caption-modal-list').hide();
        $('#content-modal-list').html('Loading...');
        $('#sh-check-log-caption').show();
        $('#file-on-nodes-modal').modal({"show":true});
        $('#file-on-nodes-modal').find('.modal-dialog').first().css({ width: '40%' });
        $.ajax({
            type: 'get',
            url: '/self-host-users/check-log?shu_id=' + $(this).data('shu-id'),
            dataType: 'json',
            statusCode: {
                404: function (response) {
                    alert('Error: 404 Not Found.');
                },
                200: function (response) {
                    if (response.status == true) {

                        $('#content-modal-list').html(response.data);

                    } else {
                        //error
                        $('#content-modal-list').html('Error: ' + response.info);
                    }
                }
            }
        });
        return false;
    });

});
