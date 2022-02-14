<?php
$SignalAccessKey = "CDSBISUv32773687cbdj43cbidepd32323frevfvffwecfvDXSXNWKJdcds";
$node_hash  = "5a5d29728a1051979374a59f96abaadfd827af2797c22a18e65b9ff275b60dc6bc351d3cbdeedf95d876289c66a51478c384cd503c21b02b2336003550feaf78";
$node_hash2 = "6a5d29728a1051979374a59f96abaadfd827af2797c22a18e65b9ff275b60dc6bc351d3cbdeedf95d876289c66a51478c384cd503c21b02b2336003550feaf78";
$node_hash_user1 = "812416bce85c8eacaa25eea3e9edda9181f987c4c84eb8981ec25888b23dd7b1eaf4d0b56146fcf5613bb78baa8bbc602240bb1b1e8f2d64a0f29d6dd2df1916";
//$node_hash  = "59592f679603ebe66e626adbc4ff06cee6e90f8b950b11517a73dbc70a1e5be222595b55abadb3001035fe04cdde059b4084a1136b505fbdc505b4792cbfddcc";
//$node_hash2 = "59592f679603ebe66e626adbc4ff06cee6e90f8b950b11517a73dbc70a1e5be222595b55abadb3001035fe04cdde059b4084a1136b505fbdc505b4792cbfddcc";
$user_email = "user804@mail.ru";
$user_email = "user5@mail.ru";
//$user_email = "user1@mail.ru"; $node_hash = $node_hash_user1;
$user_password = hash("sha512", "qwerty");
$old_password = hash("sha512", "qwerty1");
$new_password = hash("sha512", "qwerty");
//$ip = '188.163.80.33';
$ip = $_SERVER['REMOTE_ADDR'];
//var_dump(ip2long($ip));
$node_sign  = hash("sha512", $node_hash  . ip2long($ip));
$node_sign2 = hash("sha512", $node_hash2 . ip2long($ip));
//$node_sign  = hash("sha512", $node_hash  . 2996235018);
//$node_sign2 = hash("sha512", $node_hash2 . 1565345401);
$userHashSalt = "vifewiCD32FD32568cdsd";
//73d1bd18fb9b149b8f1fae247c66f0e5ea9129a943ccee7bd55f2ad993c2ee2822201687deeac0c1f5b8b6d89293518c0dfef050f77525e1c7eb37b110ecc558
$user_hash = hash('sha512', $user_email . $user_password . $userHashSalt);
//var_dump($user_hash);

$signup = '{
                "action": "signup",
                "data": {
                    "user_email":"' . $user_email . '",
                    "user_password":"' . $user_password . '",
                    "node_hash":"' . $node_hash . '",
                    "node_sign":"' . $node_sign . '",
                    "node_name":"NodeName",
                    "node_ostype":"Windows",
                    "node_osname":"Windows 10",
                    "node_devicetype":"desktop"
                }
           }';

$logout = '{
                "action":"logout",
                "data":{
                    "user_hash":"' . $user_hash . '",
                    "node_hash":"' . $node_hash . '",
                    "node_sign":"' . $node_sign .  '"
                }
            }';

$login = '{
                "action":"login",
                "data":{
                    "user_hash":"' . $user_hash . '",
                    "user_email": null,
                    "user_password": null,
                    "node_hash":"' . $node_hash . '",
                    "node_sign":"' . $node_sign . '",
                    "node_name":"NodeName1",
                    "node_ostype":"Windows",
                    "node_osname":"Windows 10",
                    "node_devicetype":"desktop",
                    "is_server":"1"
                }
          }';

$login2 = '{
                "action":"login",
                "data":{
                    "user_hash": null,
                    "user_email": "' . $user_email . '",
                    "user_password": "' . $user_password . '",
                    "node_hash":"' . $node_hash2 . '",
                    "node_sign":"' . $node_sign2 . '",
                    "node_name":"Darwin",
                    "node_ostype":"Darwin",
                    "node_osname":"Darwin 10",
                    "node_devicetype":"desktop",
                    "is_server":"0"
                }
           }';

$addNode = '{
                "action":"addNode",
                "data":{
                    "user_email":"' . $user_email . '",
                    "user_password":"'. $user_password .'",
                    "node_hash":"' . $node_hash2 . '",
                    "node_sign":"' . $node_sign2 . '",
                    "node_name":"NodeName5",
                    "node_ostype":"Windows",
                    "node_osname":"Windows 7",
                    "node_devicetype":"desktop"
                }
            }';

$delNode = '{
                "action":"delNode",
                "data":{
                    "user_hash":"' . $user_hash . '",
                    "node_hash":"' . $node_hash . '",
                    "node_sign":"' . $node_sign . '",
                    "node_id": 131
                }
            }';

$support = '{
                "action":"support",
                "data":{
                    "user_hash":"' . $user_hash . '",
                    "node_hash":"' . $node_hash . '",
                    "node_sign":"' . $node_sign . '",
                    "subject": "TECHNICAL",
                    "body": "Your body"
                }
            }';
$support2 = '{
                "action":"support",
                "data":{
                    "user_hash":"' . $user_hash . '",
                    "node_hash":"' . $node_hash . '",
                    "node_sign":"' . $node_sign . '",
                    "subject": "TECHNICAL",
                    "body": "Your body",
                    "log_file_name": "cad63ecb7215a492b566dd2cc713d02d.tar"
                }
            }';

$license = '{
                "action":"license",
                "data":{
                    "user_hash":"' . $user_hash . '",
                    "node_hash":"' . $node_hash . '",
                    "node_sign":"' . $node_sign .  '"
                }
            }';

$gettime = '{
                "action":"gettime",
                "data":{
                    "node_hash":"' . $node_hash . '",
                    "node_sign":"' . $node_sign . '"
                }
            }';

$changepassword = '{
                     "action":"changepassword",
                     "data":{
                         "user_hash":"' . $user_hash . '",
                         "node_hash":"' . $node_hash . '",
                         "node_sign":"' . $node_sign . '",
                         "old_password":"' . $user_password . '",
                         "new_password":"' . $new_password . '"
                     }
                   }';

$resetpassword = '{
                     "action":"resetpassword",
                     "data":{
                         "node_hash":"' . $node_hash . '",
                         "node_sign":"' . $node_sign . '",
                         "user_email":"' . $user_email . '"
                     }
                   }';

$patch_ready = '{
                    "action":"patch_ready",
                    "data":{
                        "user_hash":"' . $user_hash . '",
                        "node_hash":"' . $node_hash . '",
                        "node_sign":"' . $node_sign . '",
                        "diff_type":"direct",
                        "diff_uuid":"b94ddedc7fdf7474b200c9664db213d7",
                        "diff_size":"222"
                    }
                }';
$file_event_update = '{
                         "action":"file_event_update",
                         "data":{
                             "user_hash":"' . $user_hash . '",
                             "node_hash":"' . $node_hash . '",
                             "node_sign":"' . $node_sign . '",
                             "file_uuid":"9b9d2fc08b1e922db52041a910fbba28",
                             "file_size":"156161355546",
                             "diff_file_size":"6666",
                             "rev_diff_file_size":"5546",
                             "last_event_id":"88",
                             "hash":"111_____________________________"
                         }
                      }';

$file_event_delete = '{
                         "action":"file_event_delete",
                         "data":{
                             "user_hash":"' . $user_hash . '",
                             "node_hash":"' . $node_hash . '",
                             "node_sign":"' . $node_sign . '",
                             "file_uuid":"821bb879c842bb4cb09b7f7dcdd8d805",
                             "last_event_id":"2448852"
                         }
                      }';

$file_event_move = '{
                         "action":"file_event_move",
                         "data":{
                             "user_hash":"' . $user_hash . '",
                             "node_hash":"' . $node_hash . '",
                             "node_sign":"' . $node_sign . '",
                             "file_uuid":"44e9310baf394e03bb24c562d574cf03",
                             "new_folder_uuid":"fc988b80da175f171034bb730306fa36",
                             "new_file_name":"26_black.jpg",
                             "last_event_id":"1983"
                         }
                    }';

$file_event_fork = '{
                         "action":"file_event_fork",
                         "data":{
                             "user_hash":"' . $user_hash . '",
                             "node_hash":"' . $node_hash . '",
                             "node_sign":"' . $node_sign . '",
                             "file_name":"/home/test10.txt",
                             "diff_file_size":"35645",
                             "last_event_id":10
                         }
                    }';

$folder_event_create = '{
                            "action":"folder_event_create",
                            "data":{
                                "user_hash":"' . $user_hash . '",
                                "node_hash":"' . $node_hash . '",
                                "node_sign":"' . $node_sign . '",
                                "folder_name":"f2",
                                "parent_folder_uuid":null
                            }
                        }';

$folder_event_move = '{
                         "action":"folder_event_move",
                         "data":{
                             "user_hash":"' . $user_hash . '",
                             "node_hash":"' . $node_hash . '",
                             "node_sign":"' . $node_sign . '",
                             "folder_uuid":"59e1b72a886fc8d12d1970efc6e621f3",
                             "new_folder_name":"folder5",
                             "new_parent_folder_uuid":null,
                             "last_event_id":"112"
                         }
                      }';

$folder_event_delete = '{
                            "action":"folder_event_delete",
                            "data":{
                                "user_hash":"' . $user_hash . '",
                                "node_hash":"' . $node_hash . '",
                                "node_sign":"' . $node_sign . '",
                                "folder_uuid":"894d4662615cba58110074278c47631c",
                                "last_event_id":"6236"
                            }
                        }';

$file_list =  '{
                     "action":"file_list",
                     "data":{
                         "user_hash":"' . $user_hash . '",
                         "node_hash":"' . $node_hash . '",
                         "node_sign":"' . $node_sign . '",
                         "last_event_id":"3"
                     }
               }';

$file_events = '{
                     "action":"file_events",
                     "data":{
                         "user_hash":"' . $user_hash . '",
                         "node_hash":"' . $node_hash . '",
                         "node_sign":"' . $node_sign . '",
                         "last_event_id":"0",
                         "limit": "9999999",
                         "offset": "9999",
                         "node_without_backup": 0,
                         "events_count_check": 0,
                         "checked_event_id" :0
                     }
                }';


$node_id = 10441;
$node_ip = "111.112.113.114";
$node_status = "3";
$nodeinfo = '{
                 "action":"nodeinfo",
                 "data":{
                     "signal_passphrase":"' . hash("sha512", $node_id  . $node_ip . $node_status . $SignalAccessKey) . '",
                     "node_id":"' . $node_id . '",
                     "node_ip":"' . $node_ip . '",
                     "node_online":"1",
                     "node_status":"' . $node_status . '"
                 }
             }';

$user_id = 101;
$nodelist = '{
                 "action":"nodelist",
                 "data":{
                     "signal_passphrase":"' . hash("sha512", $user_id . $SignalAccessKey) . '",
                     "user_id":"' . $user_id . '"
                 }
             }';

//var_dump(hash("sha512", $user_hash . $node_hash . $SignalAccessKey));
$checknodeauth = '{
                    "action":"checknodeauth",
                    "data":{
                        "signal_passphrase":"' . hash("sha512", $user_hash . $node_hash . $SignalAccessKey) . '",
                        "user_hash":"' . $user_hash . '",
                        "node_hash":"' . $node_hash . '"
                    }
                  }';

$share_hash = "c26dea5ebb938b2efef37732e1ecaaef";
$checkbrowserauth = '{
                        "action":"checkbrowserauth",
                        "data":{
                            "signal_passphrase":"' . hash("sha512", $share_hash . $SignalAccessKey) . '",
                            "share_hash":"' . $share_hash . '"
                        }
                     }';

$getNotifications = '{
    "action": "getNotifications",
    "data": {
        "user_hash": "' . $user_hash . '",
        "node_hash": "' . $node_hash . '",
        "node_sign": "' . $node_sign . '",
        "from": 0,
        "limit": 10
    }
}';

$user_id = 121;
$last_event_id = 3;
$share_hash = "eccb61f38a37a08521bc5e6bc72403c8";
$sharing_list = '{
                    "action":"sharing_list",
                    "data":{
                        "signal_passphrase":"' . hash("sha512", $user_id . $SignalAccessKey) . '",
                        "user_id":"' . $user_id . '"
                    }
                 }';

$user_id = 5593;
$user_collaborations = '{
                    "action":"user_collaborations",
                    "data":{
                        "signal_passphrase":"' . hash("sha512", $user_id . $SignalAccessKey) . '",
                        "user_id":"' . $user_id . '"
                    }
                 }';

$get_redis_safe = '{
                    "action":"get_redis_safe",
                    "data":{
                        "signal_passphrase":"' . hash("sha512", 'get_redis_safe' . $SignalAccessKey) . '"
                    }
                 }';

$sharing_info = '{
                "action":"sharing_info",
                "data":{
                    "signal_passphrase":"' . hash("sha512", $user_id . $share_hash . $SignalAccessKey) . '",
                    "user_id":"' . $user_id . '",
                    "share_hash":"' . $share_hash . '"
                }
             }';


$allfilelist = '{
                "action":"allfilelist",
                "data":{
                    "signal_passphrase":"' . hash("sha512", $user_id . $SignalAccessKey) . '",
                    "user_id":"' . $user_id . '"
                }
             }';

$file_list_signal =  '{
                     "action":"file_list",
                     "data":{
                         "signal_passphrase":"' . hash("sha512", $node_id . $last_event_id . $SignalAccessKey) . '",
                         "node_id":"' . $node_id . '",
                         "last_event_id":"' . $last_event_id . '"
                     }
               }';

$node_id=10444;
$last_event_id = 0;
$file_events_signal = '{
                     "action":"file_events",
                     "data":{
                         "signal_passphrase":"' . hash("sha512", $node_id . $last_event_id . $SignalAccessKey) . '",
                         "node_id":"' . $node_id . '",
                         "last_event_id":"' . $last_event_id . '",
                         "node_without_backup": "0",
                         "events_count_check": "0",
                         "offset": "0",
                         "checked_event_id": "0",
                         "limit": "100"
                     }
                }';

$file_events_signal = '{
    "action": "file_events",
    "data": {
        "node_without_backup": "0",
        "signal_passphrase": "51a188dfee8cbe6c7072019990512922f52619bf8b319263488f5523e938d8225a6bc9402ac721fa2808c068535f3d6c9d83fca54ee165a6181922aa788b1f6e",
        "last_event_id": "0",
        "events_count_check": "0",
        "offset": "0",
        "checked_event_id": "0",
        "limit": "100",
        "node_id": "19684"
    }
}';

$file_events_signal = '{
    "action": "file_events",
    "data": {
        "node_without_backup": "0",
        "signal_passphrase":"' . hash("sha512", '17397' . '0' . $SignalAccessKey) . '",
        "last_event_id": "0",
        "events_count_check": "0",
        "offset": "0",
        "checked_event_id": "0",
        "limit": "100",
        "node_id": "17397"
    }
}';

$node_id = 172;
$direct_patch_event_id = 0;
$reversed_patch_event_id = 0;
$last_event_id = 2000;
$patches_info = '{
                    "action":"patches_info",
                    "data":{
                        "signal_passphrase":"' . hash("sha512", $node_id . $direct_patch_event_id. $reversed_patch_event_id . $last_event_id . $SignalAccessKey) . '",
                        "node_id":"'.$node_id.'",
                        "direct_patch_event_id": "' . $direct_patch_event_id . '",
                        "reversed_patch_event_id": "' . $reversed_patch_event_id . '",
                        "last_event_id":"' . $last_event_id . '"
                    }
                 }';

$site_token = 'dd20f0e693056848a0ce5a502dc625b5';
$check_site_token = '{
                         "action":"check_site_token",
                         "data":{
                             "signal_passphrase":"' . hash("sha512", $site_token . $SignalAccessKey) . '",
                             "site_token":"' . $site_token . '"
                         }
                      }';
//$check_site_token = '{"action": "check_site_token", "data": {"site_token": "82e12f08bp4046ncpjstvm7le1", "signal_passphrase": "af181f5488250c7bcb029ccaf702bcce4fec7025f7087190426339a28a4fcd0c1d4ee7510a6acc4364a1865005eec7635c7e6525aa379dff0da5ab2996cf0b91"}}';

$upload_id = 35;
$download = '{
                "action":"download",
                "data":{
                    "user_hash":"' . $user_hash . '",
                    "node_hash":"' . $node_hash . '",
                    "node_sign":"' . $node_sign . '",
                    "upload_id":"'. $upload_id .'"
                }
             }';

$stun = '{
            "action":"stun",
            "data":{
                "get":"candidate"
            }
         }';

$turn_get_bytes = '{
                     "action":"turn_get_bytes",
                     "data":{
                        "user_hash":"' . $user_hash . '",
                        "node_hash":"' . $node_hash . '",
                        "node_sign":"' . $node_sign . '",
                        "bytes":"104857601"
                      }
                   }';

$turn_set_bytes = '{
                     "action":"turn_set_bytes",
                     "data":{
                        "user_hash":"' . $user_hash . '",
                        "node_hash":"' . $node_hash . '",
                        "node_sign":"' . $node_sign . '",
                        "bytes":"2"
                      }
                   }';

$sharing_enable = '{
                     "action":"sharing_enable",
                     "data":{
                         "user_hash":"' . $user_hash . '",
                         "node_hash":"' . $node_hash . '",
                         "node_sign":"' . $node_sign . '",
                         "uuid":"2e180fd41ffd548f6b0174ec493db4b3",
                         "share_ttl": 12000,
                         "share_password": "qwerty11"
                     }
                   }';

$sharing_disable = '{
                         "action":"sharing_disable",
                         "data":{
                             "user_hash":"' . $user_hash . '",
                             "node_hash":"' . $node_hash . '",
                             "node_sign":"' . $node_sign . '",
                             "uuid":"ed075021074fc79c82c5cccf23644bdb"
                         }
                    }';

$get_token_login_link = '{
                            "action":"get_token_login_link",
                            "data":{
                                "user_hash":"' . $user_hash . '",
                                "node_hash":"' . $node_hash2 . '",
                                "node_sign":"' . $node_sign2 . '"
                            }
                         }';




$folder_event_create = '{
                            "action":"folder_event_create",
                            "data":{
                                "user_hash":"' . $user_hash . '",
                                "node_hash":"' . $node_hash . '",
                                "node_sign":"' . $node_sign . '",
                                "folder_name":"йцукенгшщзйцукенгшщзйцукенгшщзйцукенгшщзйцукенгшщзйцукенгшщзйцукенгшщзйцукенгшщзйцукенгшщзйцукенгшщзйцукенгшщзйцукенгшщзйцукенг",
                                "parent_folder_uuid": "87d07754d4ba201716b74d2a2a424c6c"
                            }
                        }';
$file_event_create = '{
                         "action":"file_event_create",
                         "data":{
                             "user_hash":"' . $user_hash . '",
                             "node_hash":"' . $node_hash . '",
                             "node_sign":"' . $node_sign . '",
                             "file_name":"Test2.txt",
                             "file_size":"123456",
                             "folder_uuid":"d938a13d7da41b75ad53189d1fbef923",
                             "diff_file_size":"321",
                             "hash":"5d0e26da3a754aeb89aa7a07c02585d3"
                         }
                      }';


$collaboration_cancel = '{
                     "action":"collaboration_cancel",
                     "data":{
                         "user_hash":"' . $user_hash . '",
                         "node_hash":"' . $node_hash . '",
                         "node_sign":"' . $node_sign . '",
                         "uuid":"090dcda8016499771e2bcf918b5ee69d"
                     }
                   }';

$colleague_add = '{
                     "action":"colleague_add",
                     "data":{
                         "user_hash":"' . $user_hash . '",
                         "node_hash":"' . $node_hash . '",
                         "node_sign":"' . $node_sign . '",
                         "uuid":"090dcda8016499771e2bcf918b5ee69d",
                         "access_type":"view",
                         "colleague_email":"user221@mail.ru"
                     }
                   }';

$colleague_delete = '{
                     "action":"colleague_delete",
                     "data":{
                         "user_hash":"' . $user_hash . '",
                         "node_hash":"' . $node_hash . '",
                         "node_sign":"' . $node_sign . '",
                         "uuid":"090dcda8016499771e2bcf918b5ee69d",
                         "colleague_user_id":"121"
                     }
                   }';

$colleague_edit = '{
                     "action":"colleague_edit",
                     "data":{
                         "user_hash":"' . $user_hash . '",
                         "node_hash":"' . $node_hash . '",
                         "node_sign":"' . $node_sign . '",
                         "uuid":"090dcda8016499771e2bcf918b5ee69d",
                         "access_type":"view",
                         "colleague_user_id":"121"
                     }
                   }';

$collaboration_info = '{
                     "action":"collaboration_info",
                     "data":{
                         "user_hash":"' . $user_hash . '",
                         "node_hash":"' . $node_hash . '",
                         "node_sign":"' . $node_sign . '",
                         "uuid":"b88cffecfb73d563c5268524d9ebb44a"
                     }
                   }';
$collaboration_join = '{
                     "action":"collaboration_join",
                     "data":{
                         "user_hash":"' . $user_hash . '",
                         "node_hash":"' . $node_hash . '",
                         "node_sign":"' . $node_sign . '",
                         "colleague_id": 190
                     }
                   }';

//$str = $folder_event_create;
$str = $collaboration_info;
//$str = $stun;

$folder_event_copy = '{
                            "action":"folder_event_copy",
                            "data":{
                                "user_hash":"' . $user_hash . '",
                                "node_hash":"' . $node_hash . '",
                                "node_sign":"' . $node_sign . '",
                                "target_folder_name":"f22",
                                "source_folder_uuid":"dc125506b76c8ff7f667b7b52d02c9f8",
                                "target_parent_folder_uuid":"19b6a325bd874dcace4711da205187a9",
                                "last_event_id":"719343"
                            }
                        }';
/*
$user_hash = "c892e9d9c7629e6c89b9b3e65553d742cb0b7754536702c2ce73c1f998aea5917f1fc28188976701f2be204d5d747f7924cc004480a8361f0f78a2090d3205ed";
$node_hash = "c112b9da81ee63105151a54dc698736f386d94c39040135488a88093f3876b35a4af6a29f9a211c12bea450885b008932e7a7e7c23321f39759d42b6d1295797";
$node_sign  = hash("sha512", $node_hash  . 1565345401);
$folder_event_copy = '{
                            "action":"folder_event_copy",
                            "data":{
                                "user_hash":"' . $user_hash . '",
                                "node_hash":"' . $node_hash . '",
                                "node_sign":"' . $node_sign . '",
                                "target_folder_name":"dir.copy-5",
                                "source_folder_uuid":"04a9a231d3b01ead8eddb4c323079131",
                                "target_parent_folder_uuid":"",
                                "last_event_id":"2444669"
                            }
                        }';
$node_sign  = hash("sha512", "6813bff1d9ec5473867af37a446fc7017ae6ae95163d740c0c490fddf89e22a7659a2875964ff9a24f399a750e74926788620d53b5a46b2af407c3c037b17f8a"  . "1565345401");
$download = '{
"action": "download",
"data": {
"node_sign": "'. $node_sign .'",
"upload_id": "347",
"user_hash": "17f2a3f58dac16a59ab207747a427cf685b813035046b71c9ed926f6f6b7099073731eeb01c1656d35424a4b5217cf47c97a951eea71463b808e80ac00a5e663",
"node_hash": "6813bff1d9ec5473867af37a446fc7017ae6ae95163d740c0c490fddf89e22a7659a2875964ff9a24f399a750e74926788620d53b5a46b2af407c3c037b17f8a"
}
}';
*/


$license_check_data = '{
                 "action":"license_check_data",
                 "data":{
                     "signal_passphrase":"' . hash("sha512", $SignalAccessKey) . '",
                     "user_id":"' . $user_id . '"
                 }
             }';

$license_check_result_SUCCESS = '{
                 "action":"license_check_result",
                 "data":{
                     "signal_passphrase":"' . hash("sha512", $SignalAccessKey) . '",
                     "result": "success",
                     "license_count_available": 3
                 }
             }';
$license_check_result_ERROR = '{
                 "action":"license_check_result",
                 "data":{
                     "signal_passphrase":"' . hash("sha512", $SignalAccessKey) . '",
                     "result": "error",
                     "errcode": "SELF_HOSTED_CLIENT_BLOCKED",
                     "license_count_available": 1
                 }
             }';
$license_check = '{
            "action":"license_check",
            "data":{
                "shu_user_hash": "14093e4285f0c2f77ea9b932be4858df6d89954621702a72b64a7648997f50113f212218bff57a7b558f09ccf6098f8f4951ea0591497a77042d2c0d7a3d7940",
                "license_count_used": 1
            }
        }';

//$file_events = '{"data": {"last_event_id": "0", "signal_passphrase": "a8fe91ac7135986e3c4a1ce0144bc35c447c1683ca40b289a8dc80b170437f35d163e1ff8b9a09d52998afd2d7a8e4461951cf8a0d4adee21a4ddd0ed351a64c", "node_id": "13760", "offset": "0", "limit": "999999"}, "action": "file_events"}';
//$str = $signup;
//$str = $login;
//$str = $login2;
//$str = $addNode;
//$str = $delNode;
//$str = $support;
//$str = $license;
//$str = $fsdata;
//$str = $fschange0;
//$str = $getfschange;
//$str = $confirmfschanges;
//$str = $gettime;
//$str = $changepassword;
//$str = $resetpassword;
//$str = $patch_ready;
//$str = $getNotifications;
//$str = $file_event_create;
//$str = $file_event_update;
//$str = $file_event_move;
//$str = $file_event_delete;
//$str = $folder_event_create;
//$str = $folder_event_delete;
//$str = $folder_event_move;
//$str = $file_event_fork;
//$str = $file_list;
//$str = $file_events;
//$str = $file_list_signal;
//$str = $file_events_signal;
//$str = $download;
//$str = $turn_get_bytes;
//$str = $turn_set_bytes;
//$sharing_enable = '{"action": "sharing_enable", "data": {"node_sign": "18de36883b4a93b7ddbbd8777a642401e80eb437dbea930cb3826b1ed4b4ec0bc598cc885b2e619d4627e02a40de04258cfcf9345a46506e71bc2cefa94ea00e", "uuid": "XXXX", "share_password": null, "node_hash": "dad85f1d448a3cffb41c9ef15c992830e276240f5064fb1ce549c50a684f09d4984ea026d8da1da7e23bc7c1bcfe562f2767c97e5badc53130874042d371e62e", "share_ttl": "600", "user_hash": "5a95fa872bbc8afb5947658838ef05582e7d696d2b5c27fb8d54e8a36487f4106a044ea23dcd469c6228c1ce2322c1178224e7c568e1891b62c072f5b7b3e214"}}';
//$str = $sharing_enable;
//$str = $sharing_disable;
//$str = $get_token_login_link;
//$str = '{"action":"support","data":{"subject":"Support message from android application","body":"tttt","node_hash":"c9c0bed0a303899303ab6cbe5d105f2f95cb3ab10ab8510c7792c74fadbe5d94b93ce700c16466d95311e90524058620d707fc42117af7c2fefef01de75044f4","node_sign":"e186de7baa4bcd33bd58636d8da050386afc98d5f279689be50462d0331f8ac1cb35928f378a2c485493d94658514505cbdd5bde06f85519930362cb7ab7c580","user_hash":"ae42fee10c0dfd5618180eff1bf7bd0c59a3547c2bc976da444610f13c81759d4fb7c904b904382b5cd685d559157a7e4b42969170beb7e27250c6e2aecf530f"}}';
//$str = $stun;

//$str = $nodeinfo;
//$str = $nodelist;
//$str = $checknodeauth;
//$str = $checkbrowserauth;
//$str = $allfilelist;
//$str = $sharing_list;
//$str = $sharing_info;
//$str = $check_site_token;
//$str = $patches_info;
//$str = $user_collaborations;
//$str = $get_redis_safe;
//$str = $collaboration_join;

$str = $license_check_result_SUCCESS;

/*
$user_id = 5420;
$node_id = 10441;
$str = '{
    "action": "traffic_info",
    "data":{
        "user_id": "'.$user_id.'",
        "node_id": "'.$node_id.'",
        "signal_passphrase":"' . hash("sha512", $user_id  . $node_id . $SignalAccessKey) . '",
        "traffic_data": [
            {"is_share": "1", "rx_wr": "0", "event_uuid": "6b770cff6ccf4949a99de1089515d435", "interval": "21", "tx_wr": "444", "rx_wd": "555", "tx_wd": "388753"},
            {"is_share": "0", "rx_wr": "0", "event_uuid": "6b770cff6ccf4949a99de1089515d437", "interval": "30", "tx_wr": "88", "rx_wd": "222", "tx_wd": "3333"}
        ]
    }
}';
*/

$str = '{
                     "action":"set_list_participants",
                     "data":{
                         "conference_id": 0,
                         "conference_name": "abcdeh",
                         "participants": [{"participant_email": "t081@ao.com", "user_enabled": 0}],
                         "user_hash":"' . $user_hash . '",
                         "node_hash":"' . $node_hash . '",
                         "node_sign":"' . $node_sign . '"
                     }
                   }';
$get_list_conferences = '{
                     "action":"get_list_conferences",
                     "data":{
                         "user_hash":"' . $user_hash . '",
                         "node_hash":"' . $node_hash . '",
                         "node_sign":"' . $node_sign . '"
                     }
                   }';
$open_conference = '{
                     "action":"open_conference",
                     "data":{
                         "user_hash":"' . $user_hash . '",
                         "node_hash":"' . $node_hash . '",
                         "node_sign":"' . $node_sign . '",
                         "conference_id": 36
                     }
                   }';
$str = $open_conference;
//var_dump($str);
//$str = '{"action": "file_events", "data": {"signal_passphrase": "c647776504ef665a11a7ca6d788463cd08a39756d90740732276e1b45a966c2fc97374dfa7ad4a4a51f141af0426d266be1d364573d970fc650d333bd3c2f028", "node_id": "13710", "last_event_id": "0"}}';
//var_dump($str);
//$str = $folder_event_copy;
//$str = $stun;
//$url = "https://pvtbox.net/api";
//$url = "https://dlink.frontend.home/api";
//$url = "https://pvtbox.net/api/events";
//$url = "https://dlink.frontend.home/api/events";
//$url = "https://pvtbox.net/api/sharing";
//$url = "https://dlink.frontend.home/api/sharing";
//$url = "https://pvtbox.net/api/signal";
//$url = "https://dlink.frontend.home/api/signal";
//$url = "https://dlink.frontend.home/api/self-hosted";
$url = "https://dlink.frontend.home/api/conferences";

/*
var_dump($str);
echo "<hr />\n\n";
var_dump($node_hash);
var_dump($node_sign);
var_dump($_SERVER['REMOTE_ADDR']);
echo "<hr />\n\n";
*/
$headers = [
    "Accept-Language: en",
    //"Accept: application/json, text/javascript, */*; q=0.01",
    //"Accept-Encoding: gzip, deflate",
    //"Accept-Language: ru,uk;q=0.9,en-US;q=0.8,en;q=0…fr;q=0.4,de-DE;q=0.3,de;q=0.1",
    //"User-Agent: Mozilla/5.0 (X11; Ubuntu; Linu…) Gecko/20100101 Firefox/65.0",
];
//var_dump($url);
$ch = curl_init();    // initialize curl handle
curl_setopt($ch, CURLOPT_URL, $url); // set url to post to
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // times out after 40s
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $str); // add POST fields
$answer = curl_exec($ch);// run the whole process
curl_close($ch);

echo($answer);
