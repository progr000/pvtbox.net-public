<?php
//$post_signup = [
//    'last_name' => 'last name',
//    'residence_country' => 'RU',
//    'business' => 'pro@pvtbox.net',
//    'recurring' => '1',
//    'address_street' => 'Frunze 49',
//    'payer_status' => 'verified',
//    'payer_email' => 'gaste.sib@gmail.com',
//    'address_status' => 'unconfirmed',
//    'first_name' => 'first name',
//    'receiver_email' => 'pro@pvtbox.net',
//    'address_country_code' => 'RU',
//    'payer_id' => 'MAMQHF9K3P2QE',
//    'address_city' => 'Novosibirsk',
//    'reattempt' => '1',
//    'address_state' => '',
//    'subscr_date' => '00:32:39 Apr 12, 2019 PDT',
//    'btn_id' => '178292538',
//    'address_zip' => '630005',
//    'charset' => 'windows-1252',
//    'notify_version' => '3.9',
//    'period3' => '1 D',
//    'address_country' => 'Russia',
//    'mc_amount3' => '0.13',
//    'address_name' => 'Alexander Anikeev',
//
//
//    /* обязательны всегда */
//    'verify_sign' => 'AB64awpw3VpOW4Ua-rCybYOWchcoA51yOaIjVqUdSi0Bp7qnA.BBzgm.',
//    'subscr_id' => 'I-7GNDHFM1W6EW',
//    'mc_currency' => 'USD',
//    'ipn_track_id' => '37f3ff076a04c',
//    'item_name' => '5638',   // here user_id
//
//
//    //'txn_type' => 'subscr_signup',
//    /* Обязательное поле в случае типа subscribe */
//    //'amount3' => '0.13',
//
//    'txn_type' => 'subscr_payment',
//    /* Обязательные поля в случае типа payment */
//    'mc_fee' =>  '0.15',
//    'payment_fee' =>  '0.15',
//    'payment_gross' =>  '0.15',
//    'txn_id' => '9L074297N97826743',
//
//
//
//    //'item_number' => 'professional_peer_day',
//    //'item_number' => 'professional_peer_year',
//    //'item_number' => 'professional_peer_month',
//
//
//    'item_number' => 'business_peer_day',
//    //'item_number' => 'business_peer_month',
//    //'item_number' => 'business_peer_year',
//    /* обязательны только в случае бизнес версии год или месяц */
//    'option_selection1' => '5',
//    'option_selection2' => 'Test Company Name',
//    'option_selection3' => 'Test Company Administrator Name',
//];


$post_signup = [
    'txn_type' => 'subscr_signup',
    'subscr_id' => 'I-WKTA9FW9XNAC',
    'last_name' => 'Аникеев',
    'option_selection1' => '5',
    'option_selection2' => 'Юникод',
    'residence_country' => 'RU',
    'option_selection3' => 'العَرَبِيَّة',
    'mc_currency' => 'USD',
    'item_name' => '5420',
    'business' => 'pro@pvtbox.net',
    'amount3' => '0.15',
    'recurring' => '1',
    'verify_sign' => 'AsSPJayvwCQ9zwBcyWSviX1LLRI6AgkrENCNvIaBtONL4ePCBNzxgWdh',
    'payer_status' => 'verified',
    'payer_email' => 'gaste.sib@gmail.com',
    'first_name' => 'Александр',
    'receiver_email' => 'pro@pvtbox.net',
    'payer_id' => 'MAMQHF9K3P2QE',
    'option_name1' => 'Licenses amount',
    'option_name2' => 'Company name',
    'reattempt' => '1',
    'option_name3' => 'Administrator full name',
    //'item_number' => 'business_peer_year',
    'item_number' => 'professional_peer_day',
    'subscr_date' => '04:19:41 Apr 19, 2019 PDT',
    'btn_id' => '178621601',
    'charset' => 'UTF-8',
    'notify_version' => '3.9',
    'period3' => '1 D',
    'mc_amount3' => '0.15',
    'ipn_track_id' => '1fe83fa28a6',
];

$post_suspend = [
    'payment_cycle' => 'Daily',
    'txn_type' => 'recurring_payment_suspended_due_to_max_failed_payment',
    'last_name' => 'Аникеев',
    'next_payment_date' => 'N/A',
    'residence_country' => 'RU',
    'initial_payment_amount' => '0.00',
    'currency_code' => 'USD',
    'time_created' => '04:19:41 Apr 19, 2019 PDT',
    'verify_sign' => 'Ab1qKKaTEzzspa9okSMs-F65Rn1CAaWfkkt1kXsBS0Z8lwqsXcjmU6Jf',
    'period_type' => ' Regular',
    'payer_status' => 'verified',
    'tax' => '0.00',
    'payer_email' => 'gaste.sib@gmail.com',
    'first_name' => 'Александр',
    'receiver_email' => 'pro@pvtbox.net',
    'payer_id' => 'MAMQHF9K3P2QE',
    'product_type' => '1',
    'shipping' => '0.00',
    'amount_per_cycle' => '0.15',
    'profile_status' => 'Suspended',
    'charset' => 'UTF-8',
    'notify_version' => '3.9',
    'amount' => '0.15',
    'outstanding_balance' => '0.15',
    'recurring_payment_id' => 'I-WKTA9FW9XNAC',
    'product_name' => '5420',
    'ipn_track_id' => '640db36818f7b',
];
$post_eot = [
    'txn_type' => 'subscr_eot',
    'subscr_id' => 'I-8CPMVLRTLFKH',
    'last_name' => 'Аникеев',
    'option_selection1' => '10',
    'option_selection2' => '123',
    'residence_country' => 'RU',
    'option_selection3' => '321',
    'mc_currency' => 'USD',
    'item_name' => '5420',
    'business' => 'pro@pvtbox.net',
    'verify_sign' => 'AwPuxLAikr7z7OvfUilHguG.JtvdALiKxZKCdg-leBan-zVA1vASuW0P',
    'payer_status' => 'verified',
    'payer_email' => 'gaste.sib@gmail.com',
    'first_name' => 'Александр',
    'receiver_email' => 'pro@pvtbox.net',
    'payer_id' => 'MAMQHF9K3P2QE',
    'option_name1' => 'Licenses amount',
    'option_name2' => 'Company name',
    'option_name3' => 'Administrator full name',
    'item_number' => 'business_peer_day',
    'btn_id' => '178621601',
    'charset' => 'UTF-8',
    'notify_version' => '3.9',
    'ipn_track_id' => '3e7dc677f4c4d',
];
$post_cancel = [
    'payment_cycle' => 'Daily',
    'txn_type' => 'subscr_cancel',
    'last_name' => 'Аникеев',
    'next_payment_date' => 'N/A',
    'residence_country' => 'RU',
    'initial_payment_amount' => '0.00',
    'currency_code' => 'USD',
    'time_created' => '04:19:41 Apr 19, 2019 PDT',
    'verify_sign' => 'Ab1qKKaTEzzspa9okSMs-F65Rn1CAaWfkkt1kXsBS0Z8lwqsXcjmU6Jf',
    'period_type' => ' Regular',
    'payer_status' => 'verified',
    'tax' => '0.00',
    'payer_email' => 'gaste.sib@gmail.com',
    'first_name' => 'Александр',
    'receiver_email' => 'pro@pvtbox.net',
    'payer_id' => 'MAMQHF9K3P2QE',
    'product_type' => '1',
    'shipping' => '0.00',
    'amount_per_cycle' => '0.15',
    'profile_status' => 'Suspended',
    'charset' => 'UTF-8',
    'notify_version' => '3.9',
    'amount3' => '0.15',
    'item_number' => 'business_peer_day',
    'outstanding_balance' => '0.15',
    'recurring_payment_id' => 'I-WKTA9FW9XNAC',
    'product_name' => '5420',
    'ipn_track_id' => '640db36818f7b',
    'option_selection1' => 'العَرَبِيَّة',
    'option_selection2' => 'Юникод',
    'option_selection3' => '5',
];

$post_signup = [
    'txn_type' => 'subscr_signup',
    'subscr_id' => 'I-WKTA9FW9XNAC',
    'last_name' => 'Аникеев',
    'option_selection1' => '3-4',
    'option_selection2' => 'Юникод',
    'option_selection3' => 'العَرَبِيَّة',
    'residence_country' => 'RU',
    'mc_currency' => 'USD',
    'item_name' => '5420',
    'business' => 'pro@pvtbox.net',
    'amount3' => '0.15',
    'recurring' => '1',
    'verify_sign' => 'AsSPJayvwCQ9zwBcyWSviX1LLRI6AgkrENCNvIaBtONL4ePCBNzxgWdh',
    'payer_status' => 'verified',
    'payer_email' => 'gaste.sib@gmail.com',
    'first_name' => 'Александр',
    'receiver_email' => 'pro@pvtbox.net',
    'payer_id' => 'MAMQHF9K3P2QE',
//    'option_name1' => 'Licenses amount',
//    'option_name2' => 'Company name',
//    'option_name3' => 'Administrator full name',
    'reattempt' => '1',
    //'item_number' => 'business_peer_year',
    //'item_number' => 'professional_peer_day',
    'item_number' => 'business_peer_day',
    'subscr_date' => '04:19:41 Apr 19, 2019 PDT',
    'btn_id' => '178621601',
    'charset' => 'UTF-8',
    'notify_version' => '3.9',
    'period3' => '1 D',
    'mc_amount3' => '0.15',
    'ipn_track_id' => '1fe83fa28a6',
];
$post_payment = [
    'mc_gross' => '0.15',
    'protection_eligibility' => 'Eligible',
    'payer_id' => 'MAMQHF9K3P2QE',
    'payment_date' => '04:19:41 Apr 19, 2019 PDT',
    'payment_status' => 'Completed',
    'charset' => 'UTF-8',
    'first_name' => 'Александр',
    'option_selection1' => '2-2',
    'option_selection2' => 'Юникод',
    'option_selection3' => 'العَرَبِيَّة',
    'mc_fee' => '0.15',
    'notify_version' => '3.9',
    'subscr_id' => 'I-WKTA9FW9XNAC',
    'payer_status' => 'verified',
    'business' => 'pro@pvtbox.net',
    'verify_sign' => 'AvmLEef5A8a-rIiuKA9gLXkKtEiqAoyk9PUh5tkj9eEd3pemvAo44OoE',
    'payer_email' => 'gaste.sib@gmail.com',
//    'option_name1' => 'Licenses amount',
//    'option_name2' => 'Company name',
//    'option_name3' => 'Administrator full name',
    'contact_phone' => '+7 79994696666',
    'txn_id' => '3F4344263L574644H',
    'payment_type' => 'instant',
    'btn_id' => '178621601',
    'last_name' => 'Аникеев',
    'receiver_email' => 'pro@pvtbox.net',
    'payment_fee' => '0.15',
    'receiver_id' => 'LCMD9XRW36LDN',
    'txn_type' => 'subscr_payment',
    'item_name' => '5420',
    'mc_currency' => 'USD',
    //'item_number' => 'business_peer_year',
    //'item_number' => 'professional_peer_day',
    'item_number' => 'business_peer_month',
    'residence_country' => 'RU',
    'transaction_subject' => '5420',
    'payment_gross' => '0.15',
    'ipn_track_id' => '1fe83fa28a6',
];

$post_webaccept = [
    'mc_gross' => '0.23',
    'protection_eligibility' => 'Eligible',
    'payer_id' => 'MAMQHF9K3P2QE',
    'payment_date' => '06:59:24 May 29, 2019 PDT',
    'payment_status' => 'Completed',
    'charset' => 'UTF-8',
    'first_name' => 'Александр',
    'mc_fee' => '0.23',
    'notify_version' => '3.9',
    'custom' => '',
    'payer_status' => 'verified',
    'business' => 'pro@pvtbox.net',
    'quantity' => '1',
    'verify_sign' => 'AZ1cfeX2Waq8VVtN8pXYlc7dVKj4AyafpABkoV.UaMyd8OwJa02IEqtw',
    'payer_email' => 'gaste.sib@gmail.com',
    'txn_id' => '6824439713742231J',
    'payment_type' => 'instant',
    'last_name' => 'Аникеев',
    'receiver_email' => 'pro@pvtbox.net',
    'payment_fee' => '0.23',
    'shipping_discount' => '0.00',
    'receiver_id' => 'LCMD9XRW36LDN',
    'insurance_amount' => '0.00',
    'txn_type' => 'web_accept',
    'item_name' => '5640',
    'discount' => '0.00',
    'mc_currency' => 'USD',
    'item_number' => 'professional_peer_onetime',
    'residence_country' => 'RU',
    'shipping_method' => 'Default',
    'transaction_subject' => '',
    'payment_gross' => '0.23',
    'ipn_track_id' => 'ec320a95edf27',
];

$url = "http://dlink.frontend.home/paypal/ipn";
//$url = "https://pvtbox.net/api/sharing";

$headers = ["Accept-Language: en"];
$ch = curl_init();    // initialize curl handle
curl_setopt($ch, CURLOPT_URL, $url); // set url to post to
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // times out after 40s
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_payment); // add POST fields
$answer = curl_exec($ch);// run the whole process
curl_close($ch);

echo($answer);