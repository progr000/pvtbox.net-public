$(document).ready(function() {
    $(document).on('click', '.profile-change-name', function () {
        $('#change-name-modal').modal({"show":true});
    });
    $(document).on('click', '.profile-change-email', function () {
        $('#change-email-modal').modal({"show":true});
    });
    $(document).on('click', '.profile-change-password', function () {
        $('#change-password-modal').modal({"show":true});
    });
});
