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
$adminSetting = array_merge($adminSetting,$processor_data);

$processorModel = CheckoutApi_Lib_Factory::getInstance('Model')->getInstance($adminSetting);
$post = $_POST;

/**
 * Handling the request to send to the gateway
 */


if(isset($post['dispatch']) && isset($post['dispatch']['checkout.place_order'])) {
    $pp_response =  $processorModel->processRequest($order_info,$post,$adminSetting);
}


?>
