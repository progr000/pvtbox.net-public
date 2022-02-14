/**
 * @param {integer} colleague_id
 */
function resendInvite(colleague_id, flash_or_snack)
{
    $.ajax({
        type: 'get',
        url: _LANG_URL + '/user/resend-invite?colleague_id=' + colleague_id,
        dataType: 'json',
        statusCode: {
            200: function(response) {
                if (response && ("status" in response) && ("info" in response)) {
                    if (response.status == true) {

                        if (flash_or_snack == 'snack') {
                            snackbar(response.info, 'success', 3000);
                        } else {
                            flash_msg(response.info, 'success', 3000, true);
                        }

                    } else {

                        if (flash_or_snack == 'snack') {
                            snackbar(response.info, 'error', 3000);
                        } else {
                            flash_msg(response.info, 'error', 3000, true);
                        }

                    }
                } else {
                    alert('An internal server error occurred.');
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

    $(document).on('click', '.resend-invite', function () {
        resendInvite($(this).data('tokens'), $(this).data('alert-type'));
    });

});