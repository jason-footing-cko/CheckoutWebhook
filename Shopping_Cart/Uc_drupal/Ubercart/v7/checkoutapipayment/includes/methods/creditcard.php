<?php

class methods_creditcard extends methods_Abstract
{

    public function submitFormCharge($order, $amount, $data)
    {
        $config = parent::submitFormCharge($order, $amount, $data); 
        $config['postedParam']['paymentToken'] = $_SESSION['cko_paymentToken'];
        return $this->_placeorder($config, $order);
    }

    /**
     * @param $config array of configuration
     * @return string script tag
     */
    public function getJsConfig($config)
    {
        $script = "window.CKOConfig = {
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
            } ";
        return $script;
    }

    public function getExtraInit($order = null)
    {
        $array = array();

        if ($order) {
            $paymentToken = $this->generatePaymentToken($order);
            $_SESSION['cko_paymentToken'] = $paymentToken['token'];
            // Prepare the fields to include on the credit card form.
            
            $config = array();
            $config['widgetRenderedEvent'] = isset($config['widgetRenderedEvent'])?$config['widgetRenderedEvent']:'';
            $config['cardChargedEvent'] = isset($config['cardChargedEvent'])?$config['cardChargedEvent']:'';
            $config['readyEvent'] = isset($config['readyEvent'])?$config['readyEvent']:'';
       
            $config['debug'] = false;
            $config['publicKey'] = variable_get('public_key');
            $config['email'] = $order->primary_email;
            $config['name'] = $order->billing_first_name . ' ' . $order->billing_last_name;
            $config['amount'] = getInstance($order->payment_method)->formatAmountToCents($order->order_total);
            $config['currency'] = strtolower($order->currency);
            $config['paymentToken'] = $paymentToken['token'];
            $config['renderMode'] = 0;
            $config['widgetSelector'] = '.widget-container';
            $config['cardChargedEvent'] = "
              document.getElementById('cko-cc-paymenToken').value = event.data.paymentToken;
               ";
            $config['widgetRenderedEvent'] = "jQuery('.cko-pay-now').hide();";
            //to remove config url

            $mode = variable_get('mode');
            if ($mode == 'preprod') {
                $config['url'] = 'http://preprod.checkout.com/api2/v2/';
            }

            $jsConfig = $this->getJsConfig($config);

            $array['script'] = $jsConfig;
        }
        return $array;
    }

    public function generatePaymentToken($order)
    {
        if (isset($order)) {

            $orderId = $order->order_id;
            $amountCents = $this->formatAmountToCents($order->order_total);
            $currency_code = strtolower($order->currency);

            $scretKey = variable_get('private_key');
            $mode = variable_get('mode');
            $timeout = variable_get('timeout', 60);

            $config['authorization'] = $scretKey;
            $config['mode'] = $mode;
            $config['timeout'] = $timeout;

            $transaction_type = variable_get('payment_action');

            if ($transaction_type == 'authorize') {
                $config = array_merge($this->_authorizeConfig(), $config);
            }
            else {
                $config = array_merge($this->_captureConfig(), $config);
            }


            $products = array();

            foreach ($order->products as $item) {

                $products[] = array(
                    'name'     => $item->title,
                    'sku'      => $item->model,
                    'price'    => uc_currency_format($item->price, $sign = FALSE, $thou = FALSE, $dec = '.'),
                    'quantity' => $item->qty,
                );
            }

            // Add the shipping address parameters to the request.
            $shipping_array = array(
                'addressLine1'    => $order->delivery_street1,
                'addressLine2'    => $order->delivery_street2,
                'addressPostcode' => $order->delivery_postal_code,
                'addressCountry'  => $order->delivery_country,
                'addressCity'     => $order->delivery_city,
                'recipientName'   => $order->delivery_first_name . ' ' . $order->delivery_last_name,
                'phone'           => $order->delivery_phone
            );

            $config['postedParam'] = array_merge_recursive($config['postedParam'], array(
                'email'           => $order->primary_email,
                'value'           => $amountCents,
                'trackId'         => $order->order_id,
                'currency'        => $currency_code,
                'shippingDetails' => $shipping_array,
                'products'        => $products,
                'metadata'        => array('trackId' => $order->order_id),
                'card'            => array(
                    'name' => $order->billing_first_name . ' ' . $order->billing_last_name,
                    'billingDetails' => array(
                        'addressLine1'    => $order->billing_street1,
                        'addressLine2'    => $order->billing_street2,
                        'addressPostcode' => $order->billing_postal_code,
                        'addressCountry'  => $order->billing_country,
                        'addressCity'     => $order->billing_city,
                    )
                )
                    )
            );

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
        $config = array();


        $scretKey = variable_get('private_key');
        $mode = variable_get('mode');
        $timeout = variable_get('timeout', 60);

        $config['authorization'] = $scretKey;
        $config['timeout'] = $timeout;
        $config['paymentToken'] = $_SESSION['cko_paymentToken'];

        $Api = CheckoutApi_Api::getApi(array('mode' => $mode));
        return $Api->verifyChargePaymentToken($config);
    }

    protected function _captureConfig()
    {
        $to_return['postedParam'] = array(
            'autoCapture' => 'y',
            'autoCapTime' => variable_get('autocaptime', 0)
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
