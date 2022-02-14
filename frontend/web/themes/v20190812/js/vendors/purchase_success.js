$(document).ready(function(){
    checkUserLicense(true);
});

function checkUserLicense()
{
    if ($('#link-to-pricing-in-header').length) {
        $.ajax({
            type: 'get',
            url: _LANG_URL + '/user/get-user-license',
            dataType: 'json',
            statusCode: {
                200: function (response) {
                    /* проверка есть ли новые нотификайшены или нет*/
                    if ("user_license" in response && response.user_license != UserLicense) {
                        window.location.reload();
                    } else {
                        /* перезапуск ф-ии каждые 30 сек */
                        setTimeout(function () {
                            checkUserLicense();
                        }, 30000);
                    }
                },
                500: function (response) {
                    console_log(response);
                    alert('An internal server error occurred.');
                }
            }
        });
    }
}