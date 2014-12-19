<?php
class model_methods_creditcard extends model_methods_Abstract
{
    var $code = 'checkoutapipayment';
    var $title = MODULE_PAYMENT_CHECKOUTAPIPAYMENT_TEXT_PUBLIC_TITLE;

    public function javascript_validation()
    {
        return false;
    }

    public function selection()
    {
        global $order;
        $amount = (int)$order->info['total'];
        $amountCents = $amount *100;
        $email = $order->customer['email_address'];
        $currency = $order->info['currency'];
        $publicKey = MODULE_PAYMENT_CHECKOUTAPIPAYMENT_PUBLISHABLE_KEY;
        $onFocus = ' onfocus="methodSelect(\'pmt-' . $this->code . '\')"';
        $content = 
        <<<EOD
        <p>Please select a credit/debit card</p>
        <div class="widget-container"></div>
        <input type="hidden" name="cko_cc_token" id="cko-cc-token" value="" >
        <input type="hidden" name="cko_cc_email" id="cko-cc-email" value="" >
        <script type="text/javascript" src="https://www.checkout.com/cdn/js/Checkout.js"></script>
        <script type="text/javascript">
        function CheckoutReady() {
            Checkout.render({
            publicKey: "{$publicKey}",
            customerEmail:"{$order->customer['email_address']}",
            customerName:"{$order->billing['firstname']} {$order->billing['lastname']}",
            value: "{$amountCents}",
            currency: "{$order->info['currency']}",
            billingDetails:{
                addressLine1: "{$order->billing['street_address']}",
                addressLine2: "{$order->billing['address_line_2']}",
                postcode: "{$order->billing['postcode']}",
                country: "{$order->billing['country']['title']}",
                city: "{$order->billing['city']}",
                state: "{$order->billing['state']}",
                phone: "{$order->customer['telephone']}"
            },
            widgetContainerSelector: '.widget-container',
            widgetRendered: function (event) {
                $('.cko-pay-now').hide();
                document.onfocus = methodSelect("pmt-checkoutapipayment");
            },
            cardTokenReceived: function (event) {
                document.getElementById('cko-cc-token').value = event.data.cardToken;
                document.getElementById('cko-cc-email').value = event.data.email;
            }
            });
        }
        if (window.addEventListener) {

            window.addEventListener('load', CheckoutReady, false)
        }
        else if (window.attachEvent) {
            window.attachEvent('onload', CheckoutReady)
        }
        </script>
EOD;
        $selection = array('id' => $this->code,
                            'module' => $this->title,
                            'fields' =>  array( array('field' => $content)));

        return $selection;

    }

    public function pre_confirmation_check()
    {

    }


    public function confirmation()
    {        

        return $confirmation;
    }

    public function process_button()
    {

        $process_button_string = '<input type="hidden" name="cko_cc_token" value = "'.$_POST['cko_cc_token'].'">';
        $process_button_string .= '<input type="hidden" name="cko_cc_email" value = "'.$_POST['cko_cc_email'].'">';

        $process_button_string.= '<input type="hidden" name="'. zen_session_name() .'" value = "'.zen_session_id().'">';

        echo $process_button_string;

        return $process_button_string;
    }


    public function before_process()
    {
        global $_POST, $order;

        $config = parent::before_process();

        $config['postedParam']['email'] = $_POST['cko_cc_email'];
        $config['postedParam']['cardToken'] = $_POST['cko_cc_token'];

        $this->_placeorder($config);
    }


}