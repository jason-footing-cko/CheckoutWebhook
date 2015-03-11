<?php
class methods_creditcard extends methods_Abstract
{
    public function submitFormCharge ($order, $amount, $data) {
        $config = parent::submitFormCharge($order, $amount, $data);
        $config['postedParam']['cardToken'] = $_SESSION['cko_token'];
        $config['postedParam']['email'] = $order->primary_email;
        return $this->_placeorder($config,$order);
    }

    public function getExtraInit($order = null){
        $array = array();

        if($order) {
            // Prepare the fields to include on the credit card form.
            $Api = CheckoutApi_Api::getApi(array('mode' => variable_get('mode')));
            $config = array();
            $config['debug'] = false;
            $config['publicKey'] = variable_get('public_key');
            $config['email'] =  $order->primary_email;
            $config['name'] = $order->billing_first_name . ' ' . $order->billing_last_name;
            $config['amount'] = getInstance()->formatAmountToCents($order->order_total);
            $config['currency'] = strtolower($order->currency);
            $config['renderMode'] = 2;
            $config['widgetSelector'] =  '.widget-container';
            $config['cardTokenReceivedEvent'] = "
                document.getElementById('credit-card-cko-cc-token').value = event.data.cardToken;
                document.getElementById('credit-card-cko-cc-email').value = event.data.email;
                ";
            $jsConfig = $Api->getJsConfig($config);

            $array['script'] = $jsConfig
                    .';(function($){$(function(){ var script = document.getElementById("checkoutApiJs");'
                    . 'if(!script) {'
                    .'   script = document.createElement("script");'
                    .'   script.src = "https://www.checkout.com/cdn/js/Checkout.js"; '
                    .'   script.async = "true";'
                    . '  script.id = "checkoutApiJs" ; '
                    . '  document.body.appendChild(script);'
                    . '}})})(jQuery);  '
                    ;
        }
        return $array;
    }
}