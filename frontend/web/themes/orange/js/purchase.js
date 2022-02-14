$(document).ready(function() {

    setSumForLicense();

    $(document).on('change', '#count-of-licenses', function () {
        setSumForLicense();
    });
});

function setSumForLicense()
{
    var price_for_period = ($('#price-for-period').html());
    var count_of_licenses = parseInt($('#count-of-licenses').val());
    //console_log(price_for_period);
    $('#set-total-on-select').html(parseFloat(price_for_period * count_of_licenses - DELTA_SUM_FROM_PRO).toFixed(2));
}