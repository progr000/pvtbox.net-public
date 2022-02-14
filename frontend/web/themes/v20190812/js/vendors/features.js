$(document).ready(function() {

    let href = window.location.href,
        target;

    if (href.indexOf('#confidentiality') > 0) {
        target = $('#confidentiality-target');
    } else if (href.indexOf('#security') > 0) {
        target = $('#security-target');
    } else if (href.indexOf('#speed') > 0) {
        target = $('#speed-target');
    }

    if ($(target).length) {
        let destination = $(target).offset().top;
        $('html, body').animate({scrollTop: destination}, 1100);
    }

});