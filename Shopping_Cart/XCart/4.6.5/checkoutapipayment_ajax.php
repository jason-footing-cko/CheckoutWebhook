<?php

require_once './auth.php';

$toReturn = array();

x_session_register('cart');
x_session_register('config');
x_session_register('XCART_SESSION_VARS');
x_session_register('smarty');
$payment_cc_data =  func_query_first("SELECT * FROM $sql_tbl[ccprocessors] WHERE processor='checkoutapipayment.php'");

if(!empty($payment_cc_data)) {

    $toReturn['publicKey'] = $payment_cc_data['param03'];
}


if(!empty($XCART_SESSION_VARS)) {

    if (isset($XCART_SESSION_VARS['login']) && $XCART_SESSION_VARS['login']) {
        $toReturn['customerEmail'] = $XCART_SESSION_VARS['login'];

    } elseif (isset($XCART_SESSION_VARS['anonymous_userinfo']['email'])) {

        $toReturn['customerEmail'] = $XCART_SESSION_VARS['anonymous_userinfo']['email'];
    }
}

if(!empty($cart) ) {

    if ((isset($smarty) && isset($smarty->_tpl_vars) ) && $smarty->_tpl_vars['fullname'] ) {
        $toReturn['customerName'] = $smarty->_tpl_vars['fullname'];

    } else {

        if(!empty($XCART_SESSION_VARS) && isset($XCART_SESSION_VARS['anonymous_userinfo']) ) {

            $toReturn['customerName'] = $XCART_SESSION_VARS['anonymous_userinfo']['firstname']. ' '.$XCART_SESSION_VARS['anonymous_userinfo']['lastname'];
        }
    }

    if (isset($cart['orders'])) {
        $toReturn['value'] = $cart['orders'][0]['total_cost']*100;
    }
}

if(!empty($config) && (isset($config['General']['currency_symbol']))) {
    $toReturn['currency'] = $config['General']['currency_symbol'];
}

echo json_encode($toReturn);


