$(document).ready(function() {
    $(document).on('change', 'input[type=radio][name=radio-billed]', function() {
        changePrice($(this));
    });

    changePrice($('input[type=radio][name=radio-billed]:checked'));
});

/**
 * @param radio
 */
function changePrice(radio)
{
    var saveProfessional = (PricePerMonthForLicenseProfessional * 12 - PricePerYearForLicenseProfessional * 12).toFixed(2);
    var saveBusiness     = (PricePerMonthUserForLicenseBusiness * 12 - PricePerYearUserForLicenseBusiness * 12).toFixed(2);

    $('#professional-save-sticker-text').html("$" + saveProfessional);
    $('#business-save-sticker-text').html("$" + saveBusiness);
    //console_log(radio);
    //console_log(radio.val());
    $('#link-business')
        .removeClass('btn-notActive')
        .removeAttr('onclick');

    $('#link-professional')
        .removeClass('btn-notActive')
        .removeAttr('onclick');

    var billed_var = radio.val();
    if (billed_var == BILLED_MONTHLY) {
        $('#price-professional').html(PricePerMonthForLicenseProfessional.toFixed(2));
        $('#price-business').html(PricePerMonthUserForLicenseBusiness.toFixed(2));
        $('#link-professional').attr('href', link_professional + '&billed=' + billed_var);
        $('#link-business').attr('href', link_business + '&billed=' + billed_var);
        $('.pricing__head-time-month').show().css({ 'display' : 'block'});
        $('.pricing__head-time-year').hide();

        $('#professional-save-sticker').hide();
        $('#ideal-for-business-sticker').show();
        $('#business-save-sticker').hide();
    }
    else if (billed_var == BILLED_ANNUALLY) {
        $('#price-professional').html((PricePerYearForLicenseProfessional * 12).toFixed(2));
        $('#price-business').html((PricePerYearUserForLicenseBusiness * 12).toFixed(2));
        $('#link-professional').attr('href', link_professional + '&billed=' + billed_var);
        $('#link-business').attr('href', link_business + '&billed=' + billed_var);
        $('.pricing__head-time-month').hide();
        $('.pricing__head-time-year').show().css({ 'display' : 'block'});

        $('#professional-save-sticker').show();
        $('#ideal-for-business-sticker').hide();
        $('#business-save-sticker').show();
    }


    if (USER_LICENSE_PERIOD > 0 && USER_BILLED_PERIOD != billed_var) {
        /*
        $('#link-business')
            .addClass('btn-notActive')
            .attr('href', '#')
            .attr('onclick', "return false");

        $('#link-professional')
            .addClass('btn-notActive')
            .attr('href', '#')
            .attr('onclick', "return false");
        */
    }

}