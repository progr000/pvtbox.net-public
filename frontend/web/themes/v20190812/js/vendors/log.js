var IS_DEBUG = true;

/**
 *
 * @param {mixed} data
 */
function console_log(data)
{
    if (IS_DEBUG) {
        console.log(data);
        //console.trace();
    }
}

/** */
$(document).ready(function() {
    var $body_doc = $('body');
    /** User alerts log */
    var body_data_is_debug = $body_doc.attr('data-is-debug');
    if (typeof body_data_is_debug != 'undefined') {
        IS_DEBUG = parseInt(body_data_is_debug);
    }
});
