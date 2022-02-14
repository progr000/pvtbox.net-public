<?php
$SignalAccessKey = "CDSBISUv32773687cbdj43cbidepd32323frevfvffwecfvDXSXNWKJdcds";
$node_hash  = "259326814434f8d363a183bb3b0d22f9bf17d3eebaa73eadbd69356a540b05a942a2a43625e04b6d146bd5fb6cd37c3221b8e40834c936822b5199350fa58765";
$node_hash2 = "259326814434f8d363a183bb3b0d22f9bf17d3eebaa73eadbd69356a540b05a942a2a43625e04b6d146bd5fb6cd37c3221b8e40834c936822b5199350fa58765";
//$node_hash  = "59592f679603ebe66e626adbc4ff06cee6e90f8b950b11517a73dbc70a1e5be222595b55abadb3001035fe04cdde059b4084a1136b505fbdc505b4792cbfddcc";
//$node_hash2 = "59592f679603ebe66e626adbc4ff06cee6e90f8b950b11517a73dbc70a1e5be222595b55abadb3001035fe04cdde059b4084a1136b505fbdc505b4792cbfddcc";
$user_email = "user222@mail.ru";
$user_password = hash("sha512", "qwerty");
$old_password = hash("sha512", "qwerty1");
$new_password = hash("sha512", "qwerty");
//$ip = '188.163.80.33';
$ip = $_SERVER['REMOTE_ADDR'];
//var_dump(ip2long($ip));
$node_sign  = hash("sha512", $node_hash  . ip2long($ip));
$node_sign2 = hash("sha512", $node_hash2 . ip2long($ip));
$userHashSalt = "vifewiCD32FD32568cdsd";
$user_hash = hash('sha512', $user_email . $user_password . $userHashSalt);

$file_event_create = '{
                         "action":"file_event_create",
                         "data":{
                             "user_hash":"' . $user_hash . '",
                             "node_hash":"' . $node_hash . '",
                             "node_sign":"' . $node_sign . '",
                             "file_name":"{file_name}",
                             "file_size":"{file_size}",
                             "folder_uuid":"a573844fe591caa242f968cd3d4321c2",
                             "diff_file_size":"456",
                             "hash":"{file_hash}"
                         }
                      }';
$folder_event_create = '{
                            "action":"folder_event_create",
                            "data":{
                                "user_hash":"' . $user_hash . '",
                                "node_hash":"' . $node_hash . '",
                                "node_sign":"' . $node_sign . '",
                                "folder_name":"{folder}",
                                "parent_folder_uuid":"{parent}"
                            }
                        }';

$stun = '{
            "action":"stun",
            "data":{
                "get":"candidate"
            }
         }';

$str = $stun;

function sendReq($str)
{
    $url = "http://dlink.frontend.home/api/events";
    $headers = ["Accept-Language: en"];
    $ch = curl_init();    // initialize curl handle
    curl_setopt($ch, CURLOPT_URL, $url); // set url to post to
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // times out after 40s
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $str); // add POST fields
    $answer = curl_exec($ch);// run the whole process
    curl_close($ch);
    return $answer;
}

var_dump(sendReq($stun));
set_time_limit(0);
//ob_start();
$answer = null;
for ($i=0; $i<10; $i++) {

    $folder_name = 'folder_' . $i;
    $folder_create = [
        'action' => "folder_event_create",
        'data'   => [
            'user_hash'          => $user_hash,
            'node_hash'          => $node_hash,
            'node_sign'          => $node_sign,
            'folder_name'        => $folder_name,
            'parent_folder_uuid' => $answer ? $answer['data']['folder_uuid'] : null,
        ]
    ];

    $folder_name = 'folder_' . $i;
    if ($answer) {
        for ($j=0; $j<10; $j++) {

            $file_name   = 'file_i' . $i . '_j' . $j . '.jpg';
            $file_hash   = md5($file_name);
            $file_size   = mt_rand(1111, 9999999);
            $file_create = [
                'action' => "file_event_create",
                'data'   => [
                    'user_hash'      => $user_hash ,
                    'node_hash'      => $node_hash,
                    'node_sign'      => $node_sign,
                    'file_name'      => $file_name,
                    'file_size'      => mt_rand(1111, 9999999),
                    'folder_uuid'    => $answer ? $answer['data']['folder_uuid'] : null,
                    'diff_file_size' => mt_rand(111, 999),
                    'hash'           => md5($file_name),
                ]
            ];
            sendReq(json_encode($file_create));

            $folder_name2 = 'folder_i' . $i . 'j' . $j;
            $folder_create2 = [
                'action' => "folder_event_create",
                'data'   => [
                    'user_hash'          => $user_hash,
                    'node_hash'          => $node_hash,
                    'node_sign'          => $node_sign,
                    'folder_name'        => $folder_name2,
                    'parent_folder_uuid' => $answer ? $answer['data']['folder_uuid'] : null,
                ]
            ];
            sendReq(json_encode($folder_create2));
        }

    }
    if ($answer || $i == 0) {
        $answer_tmp = json_decode(sendReq(json_encode($folder_create)), true);
        //var_dump($answer_tmp);
    }
    if (isset($answer_tmp['data']['folder_uuid'])) {
        $answer = $answer_tmp;
    }

    echo "created {$folder_name} <br />\n";
    //ob_flush();
    //flush();
    //var_dump($answer); exit;
}
//ob_end_flush();

//ob_clean();