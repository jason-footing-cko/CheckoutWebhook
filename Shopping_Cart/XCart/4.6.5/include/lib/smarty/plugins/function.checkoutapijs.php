<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.checkoutapijs.php
 * Type:     function
 * Name:     checkoutapijs
 * Purpose:  check string length and outputs a response
 * -------------------------------------------------------------
 */

global $xcart_dir;

include ($xcart_dir . '/payment/includes/autoload.php');

function cko_config()
{
    global $sql_tbl;

    $payment_cc_data = func_query_first("SELECT * FROM $sql_tbl[ccprocessors] WHERE processor='checkoutapipayment.php'");

    return $payment_cc_data;
}

function getInstance($methodType = null)
{
    global $sql_tbl;

    $payment_cc_data = cko_config();

    if (!$methodType) {
        $methodType = $payment_cc_data['param05'];

        switch ($methodType) {
            case 'yes':
                $instance = CheckoutApi_Lib_Factory::getInstance('model_methods_creditcardpci');

                break;
            default :
                $instance = CheckoutApi_Lib_Factory::getInstance('model_methods_creditcard');

                break;
        }
    }
    return $instance;
}

function getData()
{
    global $userinfo, $cart;

    $payment_cc_data = cko_config();
   
    $publictKey = $payment_cc_data['param03'];
    $email = $userinfo['email'];
    $name = $userinfo['s_firstname'] . (empty($userinfo['s_firstname']) ? "" : " ") . $userinfo['s_lastname'];
    $amount = (int) (100 * $cart['total_cost']);
    $currency = $payment_cc_data['param09'];

    $paymentTokenArray  = generatePaymentToken();

    $toReturn = array(

        'email'           =>    $email,
        'currency'        =>    $currency,
        'amount'          =>    $amount,
        'publicKey'       =>    $publictKey,
        'name'            =>    $name,
        'paymentToken'    =>    $paymentTokenArray['token'],
        'message'         =>    $paymentTokenArray['message'],
        'success'         =>    $paymentTokenArray['success'],
        'eventId'         =>    $paymentTokenArray['eventId'],
    );
    
    return $toReturn;
}

function generatePaymentToken()
{
    global $userinfo, $cart;


    $instance = getInstance();
    $payment_cc_data = cko_config();

    $config = array();

    $productsLoad = $cart['products'];
    $scretKey = $payment_cc_data['param02'];
    $amountCents = (int) (100 * $cart['total_cost']);
    $config['authorization'] = $scretKey;
    $config['mode'] = $payment_cc_data['param01'];
    $config['timeout'] = $payment_cc_data['param08'];

    if ($payment_cc_data['param06'] == 'Authorize and Capture') {

        $config = array_merge($instance->_captureConfig(), $config);
    }
    else {

        $config = array_merge($instance->_authorizeConfig(), $config);
    }

    $products = array();
    foreach ($productsLoad as $item) {

        $products[] = array(
            'name'        =>    $item['product'],
            'sku'         =>    $item['productcode'],
            'price'       =>    $item['price'],
            'quantity'    =>    $item['amount']
        );
    }
    $billingAddressConfig = array(
        'addressLine1'       =>    $userinfo['b_address'],
        'addressPostcode'    =>    $userinfo['b_address_2'],
        'addressCountry'     =>    $userinfo['b_country'],
        'addressCity'        =>    $userinfo['b_city'],
        'addressPhone'       =>    $userinfo['b_phone']
    );

    $shippingAddressConfig = array(
        'addressLine1'      =>    $userinfo['s_address'],
        'addressLine2'      =>    $userinfo['s_address_2'],
        'postcode'          =>    $userinfo['s_zipcode'],
        'country'           =>    $userinfo['s_country'],
        'city'              =>    $userinfo['s_city'],
        'phone'             =>    $userinfo['s_phone'],
        'recipientName'     =>    $userinfo['s_firstname'] . (empty($userinfo['s_firstname']) ? "" : " ") . $userinfo['s_lastname']
    );


    $config['postedParam'] = array_merge($config['postedParam'], array(
        'email'              =>   $userinfo['email'],
        'value'              =>   $amountCents,
        'currency'           =>   $payment_cc_data['param09'],
        'shippingDetails'    =>   $shippingAddressConfig,
        'products'           =>   $products,
        'billingDetails'     =>   $billingAddressConfig
    ));

    $Api = CheckoutApi_Api::getApi(array('mode' => $payment_cc_data['param01']));

    $paymentTokenCharge = $Api->getPaymentToken($config);

    $paymentTokenArray = array(
        'message' => '',
        'success' => '',
        'eventId' => '',
        'token' => 'test2',
    );

    if ($paymentTokenCharge->isValid()) {
        $paymentTokenArray['token'] = $paymentTokenCharge->getId();
        $paymentTokenArray['success'] = true;
    }
    else {


        $paymentTokenArray['message'] = $paymentTokenCharge->getExceptionState()->getErrorMessage();
        $paymentTokenArray['success'] = false;
        $paymentTokenArray['eventId'] = $paymentTokenCharge->getEventId();
    }
    return $paymentTokenArray;
}

function smarty_function_checkoutapijs($params, &$smarty)
{
    $data = getData();
    $smarty->assign('checkoutapiData', $data);
    
}
