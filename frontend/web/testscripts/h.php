<?php
$str = ">> NOOP

<< 250 2.0.0 Ok

>> MAIL FROM:<support@pvtbox.net>

>> RCPT TO:<support@pvtbox.net>

>> DATA

<< 250 2.1.0 Ok

<< 250 2.1.5 Ok

<< 354 End data with <CR><LF>.<CR><LF>

>>
.

<< 250 2.0.0 Ok: queued as B2A2E7E0411
";

preg_match("/queued as ([a-z0-9]*)(?:$|\s)/siU", $str, $ma);
var_dump($ma);
exit;
//var_dump(time());
/*
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Expires: " . date("r"));

$fname = "/NodeUpload/406/_black.jpg";
$fname = "/NodeUpload/357/FD.rtf";
$fname = "/NodeFS/UserID-2457/All files/123/simple.access.log";

//echo $fname;

header('X-Accel-Redirect: '.$fname);
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . basename($fname));
exit;
*/
//
//$file_name = "11216516 (Deleted 21-09-2018 08.16.40).txt";
//var_dump(preg_replace("/\s\(Deleted [\d]{2}\-[\d]{2}\-[\d]{4} [\d]{2}\.[\d]{2}\.[\d]{2}\)/", "", $file_name));
//\yii\helpers\ArrayHelper::multisort($tzs, ['offset', 'name']);
//var_dump($tzs);
//var_dump($ma);
$a = [1, 2, 3, 4, 5, 6, 7, 8, 9];
$b = array_slice($a, 0, 11);
array_splice($a, 0, 11);
var_dump($a);
var_dump($b);
//var_dump(date('Y-m-d H:i:s', "1541361766"));
//exit;
?>
<html>
<head>

</head>
<body>

<script type="text/javascript" _src="/themes/orange/js/dateformat/format.date.js?v=1"></script>
<script type="text/javascript" _src="/themes/orange/js/dateformat/i18n/format.date.de.js?v=1"></script>
<script>

    window.onload = function() {

        console.log(navigator.vendor.toLowerCase());

        /*
        var f = new formatDate(false);
        f.fancyFormat = '$1, H:i';
        alert(f.exec(<?= time() /*- 60*60*24*2;*/ ?>, 'd-m-Y---'));
        */
    }
</script>
</body>
</html>
