$(document).ready(function() {

    /**
     *
     */
    $(document).on('click', '.conference-manage-guest-link', function () {
        var $this = $(this);
        $('#for-send-conference-id').val($this.attr('data-conference-id'));
        $('#for-send-conference-name').val($this.attr('data-conference-name'));
        $('#for-send-conference-guest-hash').val($this.attr('data-conference-guest-hash'));
        $('#guest-link-field').val($this.attr('data-conference-guest-link'));
        $('#trigger-guest-link-modal').trigger('click');
    });

    /**
     *
     */
    $(document).on('click', '.generate-new-guest-link', function () {
        var $this = $(this);
        prettyConfirm(function() {

            generateNewGuestLink($('#for-send-conference-id').val());

        }, null, $this.data('confirm-text'));
    });

});

/**
 * @param {integer} conference_id
 */
function generateNewGuestLink(conference_id)
{
    $.ajax({
        type: 'post',
        url: _LANG_URL + '/conferences/generate-new-guest-link',
        data: {
            conference_id : conference_id,
        },
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response.status == true && "data" in response) {

                    var data = response.data;
                    var $a = $('#tr-conference-' + data.conference_id).find('a.conference-manage-guest-link').first();
                    if (!$a.length) {
                        $a = $('#owner-stream-controls').find('a.conference-manage-guest-link').first();
                    }
                    if ($a.length) {
                        $a.attr('data-conference-guest-link', data.conference_guest_link);
                        $a.attr('data-conference-guest-hash', data.conference_guest_hash);
                    }
                    $('#for-send-conference-guest-hash').val(data.conference_guest_hash);
                    $('#guest-link-field').val(data.conference_guest_link);

                } else {
                    if ("info" in response) {
                        snackbar(response.info, 'error', 3000, false, null, 'conferences.generateNewGuestLink');
                    }
                }
            },
            500: function(response) {
                console_log(response);
                alert('An internal server error occurred.');
            }
        }
    });
}