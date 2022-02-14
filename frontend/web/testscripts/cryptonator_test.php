<?php
$secretKey = "22b1f1688739b5b0a2231032ef3bfa16";

$status = "unpaid";
$status = "confirming";
$status = "paid";
//$status = "cancelled";
//$status = "mispaid";

$order_id = 84;

//merchant_id&invoice_id&invoice_created&invoice_expires&invoice_amount&invoice_currency&invoice_status&invoice_url&
//order_id&checkout_address&checkout_amount&checkout_currency&date_time&secret

$post = [
    'merchant_id'       => '3043697132544b39ddbf71f65aeeed6b',
    'invoice_id'        => md5(time()),
    'invoice_created'   => time(),
    'invoice_expires'   => time() + 3600,
    'invoice_amount'    => '19.96',
    'invoice_currency'  => 'usd',
    'invoice_status'    => $status,
    'invoice_url'       => 'http://test.ru',
    'order_id'          => $order_id,
    'checkout_address'  => 'test',
    'checkout_amount'   => '0.001',
    'checkout_currency' => 'btc',
    'date_time'         => time(),
    //'secret_hash'       => "",
];
$post['secret_hash'] = sha1(implode('&', $post) . '&' . $secretKey);

$url = "http://dlink.frontend.home/cryptonator";
//$url = "https://pvtbox.net/api/sharing";

$headers = ["Accept-Language: en"];
$ch = curl_init();    // initialize curl handle
curl_setopt($ch, CURLOPT_URL, $url); // set url to post to
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // times out after 40s
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // add POST fields
$answer = curl_exec($ch);// run the whole process
curl_close($ch);

echo($answer);
