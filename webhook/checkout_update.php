<?php

if ($_POST['submit']) {

    //set url
    $post_url = 'https://api2.checkout.com/v1/charges/'.$_POST['charge_id'];
    $url = $_POST['url'];

    $post_data = array(
        'description' => $_POST['url'],
        'metadata' => array (
            'code key'=> 'Test updated',
        )
    );
}

$data_string = json_encode($post_data);
$request = curl_init($post_url);
curl_setopt($request, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($request, CURLOPT_HTTPHEADER, array('Content-Type:application/json;charset=UTF-8', 'Authorization:sk_test_2381d84e-6a1b-49bf-86c9-e4840c7d0f28'));
curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($request, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE);
$post_response = curl_exec($request);
curl_close($request);
//var_dump($post_response);

$data_string = json_encode($post_response);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
$post_response = curl_exec($ch);

//$input = json_decode($post_response, true);


//echo '<form id="form_cartid">';
//foreach ($input as $key => $value) {
//    if (!is_array($value)) {
//        echo '<label>'. $key.' :</label><input type="text" name="'.$key.'" value="'. $value.'"/><br />';
//    }
//    else {
//        foreach ($value as $k => $data) {
//            if (!is_array($data)) {
//                echo '<label>'. $k.' :</label><input type="text" name="'.$k.'" value="'. $data.'"/><br />';;
//            }
//            else {
//                foreach($data as $key_data => $string) {
//                    echo '<label>'. $key_data.' :</label><input type="text" name="'.$key_data.'" value="'. $string.'"/><br />';;
//                }
//            }
//        }
//    }
//};
//echo '</form>';
