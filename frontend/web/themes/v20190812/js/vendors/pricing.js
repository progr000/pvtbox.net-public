/**
 * @param {object} radio
 */
function changePrice(radio)
{
    $('.js-pricing-toggle').removeClass('active');
    radio.addClass('active');

    var saveProfessional = (PricePerMonthForLicenseProfessional * 12 - PricePerYearForLicenseProfessional * 12).toFixed(2);
    var saveBusiness     = (PricePerMonthUserForLicenseBusiness * 12 - PricePerYearUserForLicenseBusiness * 12).toFixed(2);

    var billed_var = radio.val();
    //alert(billed_var);
    if (billed_var == BILLED_MONTHLY) {
        $('#price-professional').html(PricePerMonthForLicenseProfessional.toFixed(2));
        $('#price-business').html(PricePerMonthUserForLicenseBusiness.toFixed(2));
    } else if (billed_var == BILLED_ANNUALLY) {
        $('#price-professional').html((PricePerYearForLicenseProfessional * 12).toFixed(2));
        $('#price-business').html((PricePerYearUserForLicenseBusiness * 12).toFixed(2));
    }

    var $link_business = $('#link-business');
    var $link_professional = $('#link-professional');
    $link_business
        .removeClass('btn-notActive')
        .removeAttr('onclick');

    $link_professional
        .removeClass('btn-notActive')
        .removeAttr('onclick');
    $link_professional.attr('href', link_professional + '&billed=' + billed_var);
    $link_business.attr('href', link_business + '&billed=' + billed_var);

    $('.pricing__head-time-pro').html(radio.attr('data-period-business'));
    $('.pricing__head-time-business').html(radio.attr('data-period-business'));

    $('#pro-sticker').html(radio.attr('data-sticker-pro').replace('{sum}', saveProfessional));
    $('#business-sticker').html(radio.attr('data-sticker-business').replace('{sum}', saveBusiness));

    if (USER_LICENSE_PERIOD > 0 && USER_BILLED_PERIOD != billed_var) {
        /*
         $link_business
            .addClass('btn-notActive')
            .attr('href', '#')
            .attr('onclick', "return false");

         $link_professional
            .addClass('btn-notActive')
            .attr('href', '#')
            .attr('onclick', "return false");
        */
    }
}

/**
 * @param {object} radio
 */
function change_SaaS_Self(radio)
{
    $('.js-saas-self-toggle').removeClass('active');
    radio.addClass('active');

    var val = radio.val();
    //console_log(typeof val);
    $('.saas-self-divs').hide();
    if (typeof val !== 'undefined') {
        $('#div-' + radio.val()).show();
        $('.common-saas-self-text').show();
    }
}

/** */
$(document).ready(function() {

    //$(document).on('change', 'input[type=radio][name=radio-billed]', function() {
    //    changePrice($(this));
    //});
    //changePrice($('input[type=radio][name=radio-billed]:checked'));

    $(document).on('change', 'input[type=radio][name=radio-saas-self-hosted]', function() {
        change_SaaS_Self($(this));
    });
    change_SaaS_Self($('input[type=radio][name=radio-saas-self-hosted]:checked'));
    if (window.location.href.indexOf('type=saas') > 0) {
        $('.js-saas-self-toggle').removeClass('active');
        var $cur_radio = $('#radio_saas');
        $cur_radio
            .prop("checked", true)
            .attr("checked", true)
            .addClass('active');
        change_SaaS_Self($cur_radio);
    }
    if (window.location.href.indexOf('type=self') > 0) {
        $('.js-saas-self-toggle').removeClass('active');
        var $cur_radio = $('#radio_self');
        $cur_radio
            .prop("checked", true)
            .attr("checked", true)
            .addClass('active');
        change_SaaS_Self($cur_radio);
    }

    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('scroll')) {
        var target = '#' + urlParams.get('scroll'),
            destination = $(target).offset().top;

        $('html, body').animate({scrollTop: destination},1100);
    }

    $('#pricingfeedbackform-phone').mask('+00 000 000 00 00', {placeholder: "+__ ___ ___ __ __"});
});

document.addEventListener('touchstart', function(e) {
    //console.log(e.defaultPrevented);  // will be false
    //e.preventDefault();   // does nothing since the listener is passive
    //console.log(e.defaultPrevented);  // still false
}, {passive: true});