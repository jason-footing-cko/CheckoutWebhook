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

use Tygh\Registry;
use Tygh\Settings;
use Tygh\Http;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

require_once dirname(__FILE__) . "/checkoutapipayment.functions.php";

function fn_checkoutapipayment_delete_payment_processors()
{
    db_query("DELETE FROM ?:payment_descriptions WHERE payment_id IN (SELECT payment_id FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM ?:payment_processors WHERE processor_script IN
 ('checkoutapipayment.php')))");
    db_query("DELETE FROM ?:payments WHERE processor_id IN
 (SELECT processor_id FROM ?:payment_processors WHERE processor_script IN ('checkoutapipayment.php'))");
    db_query("DELETE FROM ?:payment_processors WHERE processor_script IN
('checkoutapipayment.php')");
}

function fn_checkoutapipayment_get_checkout_payment_buttons(&$cart, &$cart_products, &$auth, &$checkout_buttons, &$checkout_payments, &$payment_id)
{
    $processor_data = fn_get_processor_data($payment_id);

}

function fn_checkoutapipayment_payment_url(&$method, &$script, &$url, &$payment_dir)
{
    if (strpos($script, 'checkoutapipayment.php') !== false) {
        $payment_dir = '/app/addons/checkoutapipayment/payments/';
    }
}

function fn_update_checkoutapipayment_settings($settings)
{
    if (isset($settings['pp_statuses'])) {
        $settings['checkoutapipayment_setting'] = serialize($settings);
    }

    foreach ($settings as $setting_name => $setting_value) {
        Settings::instance()->updateValue($setting_name, $setting_value);
    }



}

function fn_get_checkoutapipayment_settings($lang_code = DESCR_SL)
{
    $pp_settings = Settings::instance()->getValues('checkoutapipayment', 'ADDON');
    if (!empty($pp_settings['general']['pp_statuses'])) {
        $pp_settings['general']['pp_statuses'] = unserialize($pp_settings['general']['pp_statuses']);
    }

    return $pp_settings['general'];
}

function fn_checkoutapipayment_get_logo_id()
{
    if (Registry::get('runtime.simple_ultimate')) {
        $logo_id = 1;
    } elseif (Registry::get('runtime.company_id')) {
        $logo_id = Registry::get('runtime.company_id');
    } else {
        $logo_id = 0;
    }

    return $logo_id;
}

function fn_checkoutapipayment_update_payment_pre(&$payment_data, &$payment_id, &$lang_code, &$certificate_file, &$certificates_dir)
{

}

function fn_checkoutapipayment_rma_update_details_post(&$data, &$show_confirmation_page, &$show_confirmation, &$is_refund, &$_data, &$confirmed)
{

}

function fn_validate_checkoutapipayment_order_info($data, $order_info)
{
    if (empty($data) || empty($order_info)) {
        return false;
    }
    $errors = array();

    if (!isset($data['num_cart_items']) || count($order_info['products']) != $data['num_cart_items']) {
        $errors[] = __('pp_product_count_is_incorrect');
    }
    if (!isset($order_info['payment_method']['processor_params']) || !isset($order_info['payment_method']['processor_params']['currency']) || !isset($data['mc_currency']) || $data['mc_currency'] != $order_info['payment_method']['processor_params']['currency']) {
        //if cureency defined in checkoutapipayment settings do not match currency in IPN
        $errors[] = __('pp_currency_is_incorrect');
    } elseif (!isset($data['mc_gross']) || !isset($order_info['total']) || (float)$data['mc_gross'] != (float)$order_info['total']) {
        //if currency is ok, check totals
        $errors[] = __('pp_total_is_incorrect');
    }

    if (!empty($errors)) {
        $pp_response['ipn_errors'] = implode('; ', $errors);
        fn_update_order_payment_info($order_info['order_id'], $pp_response);
        return false;
    }
    return true;
}

function fn_checkoutapipayment_get_customer_info($data)
{
    $user_data = array();


    return $user_data;
}

function fn_process_checkoutapipayment_ipn($order_id, $data)
{
    $order_info = fn_get_order_info($order_id);

}

//function fn_pp_get_ipn_order_ids($data)
//{
//    $order_ids = (array)(int)$data['custom'];
//    fn_set_hook('checkoutapipayment_get_ipn_order_ids', $data, $order_ids);
//
//    return $order_ids;
//}

function fn_checkoutapipayment_prepare_checkout_payment_methods(&$cart, &$auth, &$payment_groups)
{

}
