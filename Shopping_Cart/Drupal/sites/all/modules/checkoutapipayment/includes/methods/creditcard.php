<?php

class methods_creditcard extends methods_Abstract
{

    public function submitFormCharge($payment_method, $pane_form, $pane_values, $order, $charge)
    {

        $config = parent::submitFormCharge($payment_method, $pane_form, $pane_values, $order, $charge);
        $config['postedParam']['paymentToken'] = $pane_values['credit_card']['cko-cc-paymenToken'];

        return $this->_placeorder($config, $charge, $order, $payment_method);
    }

    public function submit_form($payment_method)
    {

        $form['credit_card']['cko-cc-paymenToken'] = array(
            '#type' => 'textfield',
            '#title' => '',
            '#default_value' => '',
            '#attributes' => array(
                'style' => array(
                    'display:none'
                )
            ),
        );

        return $form;
    }

    public function getExtraInit()
    {

        //$toReturn = parent::getExtraInit();
        $array = array();
        $payment_method = commerce_payment_method_instance_load('commerce_gw3_checkoutapipayment|commerce_payment_commerce_gw3_checkoutapipayment');
        module_load_include('inc', 'commerce_payment', 'includes/commerce_payment.credit_card');
        global $user;

        $order = commerce_cart_order_load($user->uid);
        if ($order) {
            $order_wrapper = entity_metadata_wrapper('commerce_order', $order);
            $billing_address = $order_wrapper->commerce_customer_billing->commerce_customer_address->value();
            $order_array = $order_wrapper->commerce_order_total->value();
            $config = array();

            $config['readyEvent'] = isset($config['readyEvent']) ? $config['readyEvent'] : '';

            //to remove url
            $config['url'] = isset($config['url']) ? $config['url'] : '';

            $paymentToken = $this->generatePaymentToken();
            $config['publicKey'] = $payment_method['settings']['public_key'];
            $config['email'] = $order->mail;
            $config['name'] = "{$billing_address['first_name']} {$billing_address['last_name']}";
            $config['amount'] = $order_array['amount'];
            $config['currency'] = $order_array['currency_code'];
            $config['renderMode'] = 2;
            $config['paymentToken'] = $paymentToken['token'];
            $config['widgetSelector'] = '.widget-container';
            $config['widgetRenderedEvent'] = "jQuery('#cko-widget').hide();" .
                    "jQuery('[name=\"commerce_payment[payment_method]\"]:checked').trigger('click');";

            $config['cardChargedEvent'] = "
              document.getElementById('edit-commerce-payment-payment-details-credit-card-cko-cc-paymentoken').value = event.data.paymentToken;
               jQuery('#commerce-checkout-form-review').trigger('submit');
               ";
            //to remove config url
            if ($payment_method['settings']['mode'] == 'preprod') {
                $config['url'] = 'http://preprod.checkout.com/api2/v2/';
            }

            $jsConfig = $this->getJsConfig($config);
            $array['script'] = $jsConfig . '';
            $array['script_external'] = 'https://www.checkout.com/cdn/js/checkout.js';
        }

        return $array;
    }

    /**
     * @param $config array of configuration
     * @return string script tag
     */
    public function getJsConfig($config)
    {
        $script = " window.CKOConfig = {
                debug: false,
                renderMode:{$config['renderMode']},
                publicKey: '{$config['publicKey']}',
                customerEmail: '{$config['email']}',
                namespace: 'CheckoutIntegration',
                customerName: '{$config['name']}',
                value: '{$config['amount']}',
                currency: '{$config['currency']}',
                namespace: 'CheckoutIntegration',
                paymentToken: '{$config['paymentToken']}',
                paymentMode: 'card',
                widgetContainerSelector: '.widget-container',
                apiUrl: '{$config['url']}',
                cardCharged: function(event) {
                    {$config['cardChargedEvent']}
                },
                widgetRendered: function(event) {
                    {$config['widgetRenderedEvent']}
                },

                ready: function() {
                     {$config['readyEvent']};

                }
            }";
        return $script;
    }

    public function generatePaymentToken()
    {
        
        global $user;
        $config = array();
        $shippingAddressConfig = null;

        $order = commerce_cart_order_load($user->uid);
        $order_wrapper = entity_metadata_wrapper('commerce_order', $order);
        $payment_method = commerce_payment_method_instance_load('commerce_gw3_checkoutapipayment|commerce_payment_commerce_gw3_checkoutapipayment');
        $billing_address = $order_wrapper->commerce_customer_billing->commerce_customer_address->value();
        $order_array = $order_wrapper->commerce_order_total->value();
        $product_line_items = $order->commerce_line_items[LANGUAGE_NONE];

        if (isset($order)) {

            $orderId = $order->order_id;
            $amountCents = $order->commerce_order_total['und'][0]['amount'];

            $scretKey = $payment_method['settings']['private_key'];
            $mode = $payment_method['settings']['mode'];
            $timeout = $payment_method['settings']['timeout'];

            $config['authorization'] = $scretKey;
            $config['mode'] = $mode;
            $config['timeout'] = $timeout;

            if ($payment_method['settings']['payment_action'] == 'authorize') {

                $config = array_merge($config, $this->_authorizeConfig());
            }
            else {

                $config = array_merge($config, $this->_captureConfig($payment_method));
            }


            $products = array();
            if (!empty($product_line_items)) {
                foreach ($product_line_items as $key => $item) {

                    $line_item[$key] = commerce_line_item_load($item['line_item_id']);

                    $products[$key] = array(
                        'name' => commerce_line_item_title($line_item[$key]),
                        'sku' => $line_item[$key]->line_item_label,
                        'price' => $line_item[$key]->commerce_unit_price[LANGUAGE_NONE][0]['amount'],
                        'quantity' => (int) $line_item[$key]->quantity,
                    );
                }
            }

            $billingAddressConfig = array(
                'addressLine1' => $billing_address['thoroughfare'],
                'addressLine2' => $billing_address['premise'],
                'addressPostcode' => $billing_address['postal_code'],
                'addressCountry' => $billing_address['country'],
                'addressCity' => $billing_address['locality'],
            );

            if (module_exists('commerce_shipping') && !empty($order_wrapper->commerce_customer_shipping->commerce_customer_address)) {
                $shipping_address = $order_wrapper->commerce_customer_shipping->commerce_customer_address->value();

                // Add the shipping address parameters to the request.
                $shippingAddressConfig = array(
                    'addressLine1' => $shipping_address['thoroughfare'],
                    'addressLine2' => $shipping_address['premise'],
                    'addressPostcode' => $shipping_address['postal_code'],
                    'addressCountry' => $shipping_address['country'],
                    'addressCity' => $shipping_address['locality'],
                );
            }

            $config['postedParam'] = array_merge($config['postedParam'], array(
                'email' => $order->mail,
                'value' => $amountCents,
                'trackId' => $orderId,
                'currency' => $order->commerce_order_total[LANGUAGE_NONE][0]['currency_code'],
                'description' => 'Order number::' . $orderId,
                'shippingDetails' => $shippingAddressConfig,
                'products' => $products,
                'metadata' => array('trackId' => $orderId),
                'billingDetails' => $billingAddressConfig
            ));

            $Api = CheckoutApi_Api::getApi(array('mode' => $mode));
 
            $paymentTokenCharge = $Api->getPaymentToken($config);
            
            $paymentTokenArray = array(
                'message' => '',
                'success' => '',
                'eventId' => '',
                'token' => '',
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
        }

        return $paymentTokenArray;
    }

    protected function _createCharge($config)
    {
        global $user;
        $config = array();

        $payment_method = commerce_payment_method_instance_load('commerce_gw3_checkoutapipayment|commerce_payment_commerce_gw3_checkoutapipayment');
        $scretKey = $payment_method['settings']['private_key'];
        $mode = $payment_method['settings']['mode'];
        $timeout = $payment_method['settings']['timeout'];

        $config['authorization'] = $scretKey;
        $config['timeout'] = $timeout;
        $config['paymentToken'] = $_POST['commerce_payment']['payment_details']['credit_card']['cko-cc-paymenToken'];

        $Api = CheckoutApi_Api::getApi(array('mode' => $mode));
        return $Api->verifyChargePaymentToken($config);
    }

    protected function _captureConfig($action)
    {
        $to_return['postedParam'] = array(
            'autoCapture' => 'y',
            'autoCapTime' => $action['settings']['autocaptime']
        );

        return $to_return;
    }

    protected function _authorizeConfig()
    {
        $to_return['postedParam'] = array(
            'autoCapture' => 'n',
            'autoCapTime' => 0
        );
        return $to_return;
    }

}
