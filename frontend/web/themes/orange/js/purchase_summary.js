$(document).ready(function() {

    /** */
    $(document).on('change', 'input[type=radio][name=radio-billed]', function() {
        changeFormPeriod($(this));
    });

    /** */
    $(document).on('change', '#pbm-os0', function() {
        //alert($(this).val());
        checkIsLicenseLessThanBefore( $(this).val() );

        $('#pba-os0').val( $(this).val() );
        $('#pba-os0').selectpicker('refresh');
        recalculateSaveSum();
    });

    /** */
    $(document).on('change', '#pba-os0', function() {
        //alert($(this).val());
        checkIsLicenseLessThanBefore( $(this).val() );

        $('#pbm-os0').val( $(this).val() );
        $('#pbm-os0').selectpicker('refresh');
        recalculateSaveSum();
    });

    /** */
    $(document).on('change', '#server-license-count-monthly', function() {
        //alert($(this).val());
        //checkIsLicenseLessThanBefore( $(this).val() );

        $('#server-license-count-annually').val( $(this).val() );
        $('#server-license-count-annually').selectpicker('refresh');
        recalculateSaveSum();
    });

    /** */
    $(document).on('change', '#server-license-count-annually', function() {
        //alert($(this).val());
        //checkIsLicenseLessThanBefore( $(this).val() );

        $('#server-license-count-monthly').val( $(this).val() );
        $('#server-license-count-monthly').selectpicker('refresh');
        recalculateSaveSum();
    });

    /** */
    if (typeof count_licenses_total != 'undefined') {
        //$('#pbm-os0').val( count_licenses_total );
        $("#pbm-os0 [value='" + count_licenses_total + "']").attr("selected", "selected");
        $('#pbm-os0').selectpicker('refresh');
        //$('#pba-os0').val( count_licenses_total );
        $("#pba-os0  [value='" + count_licenses_total + "']").attr("selected", "selected");
        $('#pba-os0').selectpicker('refresh');
    }

    /** */
    changeFormPeriod($('input[type=radio][name=radio-billed]:checked'));
});

/**
 * @param count_buy_now
 */
function checkIsLicenseLessThanBefore(count_buy_now)
{
    if (typeof count_licenses_used != 'undefined') {
        if (count_licenses_used > count_buy_now) {
            var $text_container = $('#alert-text-for-less-licenses-than-before');
            if ($text_container.length) {
                prettyAlert($text_container.text());
            }
        }
    }
}
/**
 * @param radio
 */
function changeFormPeriod(radio)
{
    var billed_var = radio.val();
    if (billed_var == BILLED_MONTHLY) {

        $('#form-monthly').show();
        $('#form-annually').hide();
        $('.save-sum-info').hide();
    } else {
        $('#form-monthly').hide();
        $('#form-annually').show();
        $('.save-sum-info').show();
    }

    recalculateSaveSum();
}

function recalculateSaveSum()
{
    var license_type = $('#license-type').attr('data-license-type');
    var billed_var = $('#license-type').attr('data-billed-var');
    var radio = $('input[type=radio][name=radio-billed]:checked');
    var count_license = 5;
    var count_server_license = 0;
    var save_sum = 0;
    var total_sum = 0;

    if (radio.length) {
        billed_var = radio.val();
    }

    //console_log(billed_var);
    if (billed_var == BILLED_MONTHLY) {
        //count_license = parseInt($('#pbm-os0').val());
        count_license = parseInt($('#pbm-os0 :selected').attr('data-value'));
        count_server_license = parseInt($('#server-license-count-monthly').val());

    } else if (billed_var == BILLED_ANNUALLY) {
        //count_license = parseInt($('#pba-os0').val());
        count_license = parseInt($('#pba-os0 :selected').attr('data-value'));
        count_server_license = parseInt($('#server-license-count-annually').val());
    }

    /* переопределить value для селектов pbm-os0 и pba-os0 в соответствии со значением count_server_license */
    /* а также hosted-button-id- */
    var hosted_button_id_monthly = $('#server-license-count-monthly :selected').attr('data-form-id');
    //alert(hosted_button_id_monthly);
    $('#hosted-button-id-monthly').val(hosted_button_id_monthly);
    var hosted_button_id_annually = $('#server-license-count-annually :selected').attr('data-form-id');
    //alert(hosted_button_id_annually);
    $('#hosted-button-id-annually').val(hosted_button_id_annually);
    $('#pbm-os0 option').each(function(){
        $(this).attr('value', count_server_license + '-' + $(this).attr('data-value'));
    });
    $('#pba-os0 option').each(function(){
        $(this).attr('value', count_server_license + '-' + $(this).attr('data-value'));
    });

    if (license_type == 'business') {
        if (billed_var == BILLED_MONTHLY) {
            total_sum = ((count_license * PricePerMonthUserForLicenseBusiness) + (count_server_license * PricePerMonthForServerLicenseBusiness)).toFixed(2);
        } else {
            total_sum = ((count_license * PricePerYearUserForLicenseBusiness * 12) + (count_server_license * PricePerYearForServerLicenseBusiness * 12)).toFixed(2);
        }
        save_sum = (
            ((count_license * PricePerMonthUserForLicenseBusiness * 12) + (count_server_license * PricePerMonthForServerLicenseBusiness * 12)) -
            ((count_license * PricePerYearUserForLicenseBusiness * 12) + (count_server_license * PricePerYearForServerLicenseBusiness * 12))
        ).toFixed(2);
    } else {
        if (billed_var == BILLED_MONTHLY) {
            total_sum = (PricePerMonthForLicenseProfessional).toFixed(2);
        } else if (billed_var == BILLED_ANNUALLY) {
            total_sum = (PricePerYearForLicenseProfessional * 12).toFixed(2);
        } else {
            total_sum = (PriceOneTimeForLicenseProfessional).toFixed(2);
        }
        save_sum = (PricePerMonthForLicenseProfessional * 12 - PricePerYearForLicenseProfessional * 12).toFixed(2);
    }
    $('.save-sum-val').html(save_sum);
    $('.pp-total-sum-val').html(total_sum);
}
