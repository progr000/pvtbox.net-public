<?php
$SignalAccessKey = "CDSBISUv32773687cbdj43cbidepd32323frevfvffwecfvDXSXNWKJdcds";
$node_hash  = "5a5d29728a1051979374a59f96abaadfd827af2797c22a18e65b9ff275b60dc6bc351d3cbdeedf95d876289c66a51478c384cd503c21b02b2336003550feaf78";
$node_hash2 = "6a5d29728a1051979374a59f96abaadfd827af2797c22a18e65b9ff275b60dc6bc351d3cbdeedf95d876289c66a51478c384cd503c21b02b2336003550feaf78";
$node_hash_user1 = "812416bce85c8eacaa25eea3e9edda9181f987c4c84eb8981ec25888b23dd7b1eaf4d0b56146fcf5613bb78baa8bbc602240bb1b1e8f2d64a0f29d6dd2df1916";
//$node_hash  = "59592f679603ebe66e626adbc4ff06cee6e90f8b950b11517a73dbc70a1e5be222595b55abadb3001035fe04cdde059b4084a1136b505fbdc505b4792cbfddcc";
//$node_hash2 = "59592f679603ebe66e626adbc4ff06cee6e90f8b950b11517a73dbc70a1e5be222595b55abadb3001035fe04cdde059b4084a1136b505fbdc505b4792cbfddcc";
$user_email = "user804@mail.ru";
$user_email = "user5@mail.ru";
$user_email = "user1@mail.ru"; $node_hash = $node_hash_user1;
$user_password = hash("sha512", "qwerty");
$old_password = hash("sha512", "qwerty1");
$new_password = hash("sha512", "qwerty");
//$ip = '188.163.80.33';
$ip = $_SERVER['REMOTE_ADDR'];
//var_dump(ip2long($ip));
$node_sign  = hash("sha512", $node_hash . ip2long($ip));
$node_sign2 = hash("sha512", $node_hash2 . ip2long($ip));
//$node_sign  = hash("sha512", $node_hash  . 2996235018);
//$node_sign2 = hash("sha512", $node_hash2 . 1565345401);
$userHashSalt = "vifewiCD32FD32568cdsd";
//73d1bd18fb9b149b8f1fae247c66f0e5ea9129a943ccee7bd55f2ad993c2ee2822201687deeac0c1f5b8b6d89293518c0dfef050f77525e1c7eb37b110ecc558
$user_hash = hash('sha512', $user_email . $user_password . $userHashSalt);
//var_dump($user_hash);

?>
<html>
    <head>
        <meta charset="UTF-8"/>
        <title>Test</title>
    </head>
    <body>
    <form method="post" enctype="multipart/form-data" action="http://dlink.frontend.home/api/upload">
        <input type="hidden" name="node_hash" value="<?= $node_hash ?>" />
        <input type="hidden" name="node_sign" value="<?= $node_sign ?>" />
        <input type="hidden" name="user_hash" value="<?= $user_hash ?>" />
        <input type="file" name="UploadLogsForm[uploadedFile]" />
        <br /><br />
        <input type="submit" />
    </form>
    </body>
</html>
