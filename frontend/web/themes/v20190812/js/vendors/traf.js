// https://github.com/HubSpot/odometer
// https://github.com/wqzwh/odometer

var recalcTimeout;

/**
 *
 * @param bytes
 * @param decimal_digits
 * @param force_size
 * @param space_between
 * @returns {{value: string, power: string}}
 */
function tr_file_size_format(bytes, decimal_digits, force_size)
{
    if (typeof decimal_digits == 'undefined') { decimal_digits = 2; }
    bytes = parseInt(bytes);
    decimal_digits = parseInt(decimal_digits);
    var units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    var power = units.indexOf(force_size);
    if (power < 0) { power = (bytes > 0) ? Math.floor(Math.log(bytes) / Math.log(1024)) : 0; }
    return  {
        'value': (bytes / Math.pow(1024, power)).toFixed(decimal_digits),
        'power': units[power]
    };
}

function getRandomInt(max) {
    return parseInt(Math.floor(Math.random() * Math.floor(max)) + 1);
}

/**
 *
 */
function recalcTraf()
{
    var $traf_info_check = $('#traf-info-check'),
        checksum      =  $traf_info_check.attr('data-checksum'),

        $traf_today   = $('#traf-today'),
        today_calc    = parseFloat($traf_today.attr('data-traf-calc')),
        today_prev    = parseFloat($traf_today.attr('data-traf-prev')),
        today_current = parseFloat($traf_today.attr('data-traf-current')),

        $traf_month   = $('#traf-month'),
        month_calc    = parseFloat($traf_month.attr('data-traf-calc')),
        month_current = parseFloat($traf_month.attr('data-traf-current')),

        $traf_total   = $('#traf-total'),
        total_calc    = parseFloat($traf_total.attr('data-traf-calc')),
        total_current = parseFloat($traf_total.attr('data-traf-current')),

        interval = parseInt($traf_today.attr('data-traf-interval')),

        rnd = getRandomInt(3),
        delta    = rnd * ((today_current - today_prev) / interval),
        functionTimeout = parseInt(Math.round(interval / 60) * 1000 * rnd);

    today_calc = today_calc + delta;
    if (today_calc > today_current) { today_calc = today_current; }
    var today_calc_tmp = tr_file_size_format(today_calc * 1024 * 1024, 0, 'MB');
    //console_log(today_calc_tmp);
    $traf_today.attr('data-traf-calc', today_calc);
    $traf_today.html(parseFloat(today_calc_tmp.value) + 0.1);
    $('#traf-today-power').html(today_calc_tmp.power);

    month_calc = month_calc + delta;
    if (month_calc > month_current) { month_calc = month_current; }
    var month_calc_tmp = tr_file_size_format(month_calc * 1024 * 1024, 2, 'GB');
    $traf_month.attr('data-traf-calc', month_calc);
    $traf_month.html(parseFloat(month_calc_tmp.value) + 0.001);
    $('#traf-month-power').html(month_calc_tmp.power);

    total_calc = total_calc + delta;
    if (total_calc > total_current) { total_calc = total_current; }
    var total_calc_tmp = tr_file_size_format(total_calc * 1024 * 1024, 2, 'GB');
    $traf_total.attr('data-traf-calc', total_calc);
    $traf_total.html(parseFloat(total_calc_tmp.value) + 0.001);
    $('#traf-total-power').html(total_calc_tmp.power);


    if (today_calc < today_current) {

        recalcTimeout = setTimeout(function () {
            recalcTraf();
        }, functionTimeout);

    } else {

        clearTimeout(recalcTimeout);
        $.ajax({
            type: 'get',
            url: _LANG_URL + '/site/get-traf-info',
            dataType: 'json',
            statusCode: {
                200: function (response) {

                    if ("data" in response) {
                        if (response.data.checksum != checksum) {
                            $traf_today.attr('data-traf-calc', response.data.today_amount_prev);
                            $traf_today.attr('data-traf-prev', response.data.today_amount_prev);
                            $traf_today.attr('data-traf-current', response.data.today_amount_current);

                            $traf_month.attr('data-traf-calc', response.data.month_amount_prev);
                            $traf_month.attr('data-traf-prev', response.data.month_amount_prev);
                            $traf_month.attr('data-traf-current', response.data.month_amount_current);

                            $traf_total.attr('data-traf-calc', response.data.total_amount_prev);
                            $traf_total.attr('data-traf-prev', response.data.total_amount_prev);
                            $traf_total.attr('data-traf-current', response.data.total_amount_current);
                        }
                    }

                    if (response.data.today_amount_prev === null || response.data.month_amount_prev === null || response.data.total_amount_prev === null) {
                        functionTimeout = 60 * 1000;
                    }
                    recalcTimeout = setTimeout(function () {
                        recalcTraf();
                    }, functionTimeout);

                },
                500: function (response) {
                    //console_log(response);

                    recalcTimeout = setTimeout(function () {
                        recalcTraf();
                    }, functionTimeout);

                }
            }
        });

    }

}

var visible_and_started = false;
/** */
$(document).ready(function() {

    // 1836 =  w>=1120
    // 1955 =  w>768 w<1836


    //var max440 = window.matchMedia('(max-width: 440px)');
    var target = '#traf-today',
        destination = $(target).offset().top - 1000;
    if ($(window).scrollTop() > destination && !visible_and_started) {
        recalcTraf();
        visible_and_started = true;
    }
    $(window).on("load scroll", function() {
        if ($(window).scrollTop() > destination && !visible_and_started) {
            recalcTraf();
            visible_and_started = true;
            //console_log($(window).scrollTop());
            //console_log(destination);
        }
    });

    //recalcTraf();

    var $traf_today = $('#traf-today');
    var today_start = parseFloat(tr_file_size_format(parseFloat($traf_today.attr('data-traf-prev')) * 1024 * 1024, 0, 'MB').value);
    today_start = (today_start > 500) ? today_start - 500 + 0.1 : today_start + 0.1;
    od_today = new Odometer({
        el: $traf_today[0],
        value: today_start,
        format: '(ddd).d',
        theme: 'car',
        //zeroFlag: true,
        //numberLength: 8,
        //duration: 500,
    });
    var $traf_month = $('#traf-month');
    var month_start = parseFloat(tr_file_size_format(parseFloat($traf_month.attr('data-traf-prev')) * 1024 * 1024, 2, 'GB').value);
    month_start = (month_start > 100) ? month_start - 100 + 0.001 : month_start + 0.001;
    od_month = new Odometer({
        el: $traf_month[0],
        value: month_start,
        format: '(ddd).ddd',
        theme: 'car',
        //zeroFlag: true,
        //numberLength: 8,
        //duration: 500,
    });
    var $traf_total = $('#traf-total');
    //var total_start = parseFloat(tr_file_size_format(parseFloat($traf_total.attr('data-traf-prev')) * 1024 * 1024, 2, 'TB').value);
    //total_start = total_start + 0.001;
    //total_start = 0.001;
    var total_start = parseFloat(tr_file_size_format(parseFloat($traf_total.attr('data-traf-prev')) * 1024 * 1024, 2, 'GB').value);
    total_start = (total_start > 100) ? total_start - 100 + 0.001 : total_start + 0.001;
    od_total = new Odometer({
        el: $traf_total[0],
        value: total_start,
        format: '(ddd).ddd',
        theme: 'car',
        //zeroFlag: true,
        //numberLength: 8,
        //duration: 500,
    });
    //od_day.update(today_current);

});

//window.odometerOptions = {
//    format: '(ddd).ddd'
//};