<?php
/***************************************************************************
*                                                                          *
*    Copyright (c) 2009 Simbirsk Technologies Ltd. All rights reserved.    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/



use Tygh\Http;
use Tygh\Registry;
include 'CheckoutAPi/autoload.php';
if (!defined('BOOTSTRAP')) { die('Access denied'); }
$adminSetting = fn_get_checkoutapipayment_settings();
if(is_array($processor_data)) {
    $adminSetting = array_merge($adminSetting,$processor_data);
}
$processorModel = CheckoutApi_Lib_Factory::getInstance('Model')->getInstance($adminSetting);
$post = $_POST;

/**
 * Handling the request to send to the gateway
 */


if(isset($post['dispatch']) && isset($post['dispatch']['checkout.place_order'])) {
    $pp_response =  $processorModel->processRequest($order_info,$post,$adminSetting);
}


if (defined('PAYMENT_NOTIFICATION')) {
    if(isset($_GET['chargeId'])) {
        $stringCharge = _process($adminSetting);
    }else {
        $stringCharge = file_get_contents ( "php://input" );
    }

    $Api = CheckoutApi_Api::getApi(array('mode'=> $adminSetting['mode_type']));

    $objectCharge = $Api->chargeToObj($stringCharge);
    $pp_response = array(
        'order_status'   => null,
        'transaction_id' => $objectCharge->getId(),
        'reason_text'    => ''
    );

    if($objectCharge->isValid()) {
        $order_id = $objectCharge->getMetadata()->getTrackId();
        $pp_response['reason_text'] = "Order #$order_id has been updated to {$objectCharge->getStatus()} by Checkout
        .com";
        if (fn_check_payment_script('checkoutapipayment.php', $order_id)) {
            $order_info = fn_get_order_info($order_id, true);
            if ($order_info['status'] == 'N') {
                fn_change_order_status($order_id, 'O', '', false);
            }
        }

        if ( $objectCharge->getCaptured ()) {
            $pp_response['order_status'] = getStatus('completed');
            echo "Order has been captured";

        } elseif ( $objectCharge->getRefunded () ) {
            $pp_response['order_status'] = getStatus($adminSetting['order_cancel']);
            echo "Order has been refunded";

        } elseif(!$objectCharge->getAuthorised()) {
            $pp_response['order_status'] = getStatus($adminSetting['order_cancel']);
            echo "Order has been Cancel";
        }

        if (fn_check_payment_script('checkoutapipayment.php', $order_id)) {
            fn_set_hook('finish_payment', $order_id, $pp_response);

            fn_change_order_status($order_id, $pp_response['order_status'], '', $pp_response);
        }
    }
die();
}


 function _process($setting)
{
    $config['chargeId']    =    $_GET['chargeId'];
    $config['authorization']    =    $setting['secret_key'];
    $Api = CheckoutApi_Api::getApi(array('mode'=> $setting['mode_type']));
    $respondBody    =    $Api->getCharge($config);

    $json = $respondBody->getRawOutput();
    return $json;
}

 function getStatus($state)
{
    $_order_status = array(
        "processed" => "P",
        "pending" => "0",
        "cancel" => "I",
        "completed" => "C",
        "Decline" => "D",
        "Fail" => "F" // Transaction is held for review... I think open order status is good for such situation
    );
    return $_order_status[$state];
}
?>
