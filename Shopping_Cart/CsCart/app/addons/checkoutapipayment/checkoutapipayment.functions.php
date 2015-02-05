<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
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

function fn_checkoutapipayment_complete_checkout($token, $processor_data, $order_info)
{
    $pp_response['order_status'] = 'F';
    $reason_text = '';

    $checkoutapipayment_checkout_details = fn_checkoutapipayment_get_express_checkout_details($processor_data, $token);
    if (fn_checkoutapipayment_ack_success($checkoutapipayment_checkout_details)) {
        $result = fn_checkoutapipayment_do_express_checkout($processor_data, $checkoutapipayment_checkout_details, $order_info);
        if (fn_checkoutapipayment_ack_success($result)) {

            $status = $result['PAYMENTINFO_0_PAYMENTSTATUS'];
            $pp_response['transaction_id'] = $result['PAYMENTINFO_0_TRANSACTIONID'];

            if ($status == 'Completed' || $status == 'Processed') {
                $pp_response['order_status'] = 'P';
                $reason_text = 'Accepted ';

            } elseif ($status == 'Pending') {
                $pp_response['order_status'] = 'O';
                $reason_text = 'Pending ';

            } else {
                $reason_text = 'Declined ';
            }

            $reason_text = fn_checkoutapipayment_process_add_fields($result, $reason_text);

            if (!empty($result['L_ERRORCODE0'])) {
                $reason_text .= ', ' . fn_checkoutapipayment_get_error($result);
            }
        } else {
            $reason_text = fn_checkoutapipayment_get_error($result);
        }
    } else {
        $reason_text = fn_checkoutapipayment_get_error($checkoutapipayment_checkout_details);
    }

    $pp_response['reason_text'] = $reason_text;

    if (fn_check_payment_script($processor_data['processor_script'], $order_info['order_id'])) {
        unset($_SESSION['pp_express_details']);

        fn_finish_payment($order_info['order_id'], $pp_response);
        fn_order_placement_routines('route', $order_info['order_id'], false);
    }
}

function fn_checkoutapipaymentl_ack_success($checkoutapipayment_checkout_details)
{
    return !empty($checkoutapipayment_checkout_details['ACK']) && ($checkoutapipayment_checkout_details['ACK'] == 'Success' || $checkoutapipayment_checkout_details['ACK'] == 'SuccessWithWarning');
}

function fn_checkoutapipayment_user_login($checkout_details)
{

    return true;
}

function fn_checkoutapipayment_build_request($processor_data, &$request, &$post_url, &$cert_file)
{

}

function fn_checkoutapipayment_get_express_checkout_details($processor_data, $token)
{


    fn_checkoutapipayment_build_request($processor_data, $request, $post_url, $cert_file);

    return fn_checkoutapipayment_request($request, $post_url, $cert_file);
}

function fn_checkoutapipayment_do_express_checkout($processor_data, $checkoutapipayment_checkout_details, $order_info)
{


}

function fn_checkoutapipayment_payment_form($processor_data, $token)
{

}

function fn_checkoutapipayment_request($request, $post_url, $cert_file)
{
    $extra = array(
        'headers' => array(
            'Connection: close'
        ),
        'ssl_cert' => $cert_file
    );

    $response = Http::post($post_url, $request, $extra);

    if (!empty($response)) {
        parse_str($response, $result);

    } else {
        $result['ERROR'] = Http::getError();
    }

    return $result;
}

function fn_checkoutapipayment_build_details($data, $processor_data, $express = true)
{
    $details = array();
    $shipping_data = array();

    if (!empty($processor_data['processor_params']['send_adress']) && $processor_data['processor_params']['send_adress'] == 'Y') {
        if ($express) {
            $shipping_data = fn_checkoutapipayment_get_shipping_data($data['user_data']);
        } else {
            $shipping_data = fn_checkoutapipayment_get_shipping_data($data);
        }
    }

    $order_data = fn_checkoutapipayment_get_order_data($data);

    return array_merge($details, $shipping_data, $order_data);
}

function fn_checkoutapipayment_get_shipping_data($data)
{
    $shipping_data = array();


    return $shipping_data;
}

function fn_checkoutapipayment_get_order_data($data)
{


}

function fn_checkoutapipayment_sum_taxes($order_info)
{

}

function fn_checkoutapipayment_apply_discount($data, &$order_data, $product_index)
{

}

function fn_checkoutapipayment_get_product_option($product)
{

}

function fn_checkoutapipayment_process_add_fields($result, $reason_text)
{

}

function fn_checkoutapipayment_get_error($result)
{

}

function fn_checkoutapipayment_set_express_checkout($payment_id, $order_id = 0, $order_info = array(), $cart = array(), $area = AREA)
{

}
CURL_SSLVERSION_TLSv1_2;