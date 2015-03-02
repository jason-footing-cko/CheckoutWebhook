<?php

include 'autoload.php';
if ($_POST['submit']) {

    //set url
    $post_url = 'http://preprod.checkout.com/api.gw3/v2/charges/' . $_POST['charge_id'];
    $url = $_POST['url'];
}

$param = array();
$param['authorization'] = $_POST['key'];
$param['chargeId'] = $_POST['charge_id'];

$Api = new CheckoutApi_Api();
$respond = $Api::getApi(array('mode' => $_POST['mode']));
$charge = $respond->getCharge($param);

if ($charge->isValid()) {

    $post_charge = $charge->getRawOutput();
    //post charge to web hook
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_charge);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
    $response = curl_exec($ch);
    curl_close($ch);
}
else {

    die('here');
}




