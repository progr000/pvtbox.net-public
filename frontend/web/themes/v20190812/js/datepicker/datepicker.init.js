//(function () {
//    'use strict';
//
//    $(function(){
//
//        // Variables
//        let $doc = $(document);
//
//        /*
//         *
//         * range dates
//         *
//         * */
//        $.fn.datepicker.language['en'] =  {
//            days: ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
//            daysShort: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
//            daysMin: ['Su','Mo','Tu','We','Th','Fr','Sa'],
//            months: ['January','February','March','April','May','June','July','August','September','October','November','December'],
//            monthsShort: ['Jan','Feb','Mar','Apr','May','June','July','Aug','Sept','Oct','Nov','Dec'],
//            today: 'Today',
//            clear: 'Clear',
//            dateFormat: 'dd.mm.yyyy',
//            timeFormat: 'hh:ii',
//            firstDay: 1
//        };
//
//        $('.js-datepicker-range').each( function(index, element) {
//            let $el = $(element);
//            // if ($el.attr('data-start-date') === 1) {
//            //     start = new Date($el.attr('data-start-date'));
//            // }else{
//            //     start = new Date();
//            // }
//            let options = {
//                language: 'en',
//                autoClose: true,
//                //startDate: start,
//                toggleSelected: false,
//                range: true,
//                classes:'single-datepicker',
//                multipleDatesSeparator: ' - ',
//                view: 'days',
//                todayButton: true,
//                //clearButton: true,
//                //onSelect: function(formattedDate, date, inst) { console_log(date); },
//                onHide: function(inst, animationCompleted) { alert(1); $('#form-reports-filter')[0].submit(); }
//            };
//            $el
//                .attr('type', 'text')
//                .datepicker(options)
//                .data('datepicker');
//        });
//
//        $('.datepicker-wrap').on('click', function (e) {
//            $(this).find('input').trigger('focus');
//        });
//
//        $('.js-datepicker-reset').on('click', function (e) {
//            $(this).siblings('.date-input').datepicker().data('datepicker').clear();
//            return false;
//        });
//
//        $doc.bind('mouseup touchend', function (e){
//            let $dateContainer = $('#datepickers-container'),
//                $datepicker = $('.js-datepicker');
//            if($datepicker.length > 0 && !$dateContainer.is(e.target) && $dateContainer.has(e.target).length === 0 ){
//                $datepicker.datepicker().data('datepicker').hide();
//            }
//        });
//    });
//
//})();

/**
 *
 */
function initDatepickerFields()
{
    $('#date-range-reports').datepicker({
        language: 'i18n',
        autoClose: true,
        //startDate: start,
        toggleSelected: false,
        range: true,
        classes:'single-datepicker',
        multipleDatesSeparator: ' - ',
        view: 'days',
        todayButton: true,
        onShow: function(dp, animationCompleted){
        },
        onHide: function(dp, animationCompleted){
        },
        onSelect: function(formattedDate, date, inst)  {
            //console_log(typeof date);
            if (typeof date == 'object' && date.length == 2) {
                //console_log(formattedDate);
                $('#form-reports-filter')[0].submit();
            }
        }
    });

    $('.js-datepicker-reset').on('click', function (e) {
        $(this).siblings('.date-input').datepicker().data('datepicker').clear();
        $('#form-reports-filter')[0].submit();
        return false;
    });
}

/**
 *
 *
 *
 */
$(document).ready(function() {

    initDatepickerFields();

});
