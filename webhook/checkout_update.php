<?php

include 'autoload.php';
if ($_POST['submit']) {

    //set url
    $post_url = 'http://preprod.checkout.com/api.gw3/v2/charges/'.$_POST['charge_id'];
    $url = $_POST['url'];
}

$Api = new CheckoutApi_Api();



// get charge
$request = curl_init($post_url);
curl_setopt($request, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($request, CURLOPT_HTTPHEADER, array('Content-Type:application/json;charset=UTF-8', 'Authorization:sk_test_8655e7cb-d031-4444-802b-388d8f96820c'));
curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
$post_response = curl_exec($request);
curl_close($request);

//post charge to web hook
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_response);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
$response = curl_exec($ch);
curl_close($ch );;

//$input = json_decode($post_response, true);