$(document).ready(function() {

    /** */
    $(document).on('click', '.profile-change-name', function () {
        $('#change-name-modal').modal({"show":true});
    });

    /** */
    $(document).on('click', '.profile-change-email', function () {
        $('#change-email-modal').modal({"show":true});
    });

    /** */
    $(document).on('click', '.profile-change-password', function () {
        $('#change-password-modal').modal({"show":true});
    });

    /** */
    $(document).on('click', '.btn-deleteAccount', function() {

        var msg = $('#text-inform-for-delete-account').text();

        prettyConfirm(function () {
            $.ajax({
                type: 'post',
                url: _LANG_URL + '/user/delete-account',
                //data: {},
                dataType: 'json',
                statusCode: {
                    200: function(response) {
                        if ("redirect" in response) {
                            window.location.href = response.redirect;
                        }
                    },
                    500: function(response) {
                        console_log(response);
                        alert('An internal server error occurred.');
                    }
                }
            });

        }, null, $.trim(msg), '', '');

        return false;
    });
});
