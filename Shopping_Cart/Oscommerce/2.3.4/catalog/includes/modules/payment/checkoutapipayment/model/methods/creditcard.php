<?php
class model_methods_creditcard extends model_methods_Abstract
{
     public function pre_confirmation_check()
     {
         global $oscTemplate;
         if ( $this->templateClassExists() ) {

            // $oscTemplate->addBlock('<script type="text/javascript" src="http://ckofe.com/js/Checkout.js"></script>', 'header_tags');
         }
     }
    public  function templateClassExists() {
        return class_exists('oscTemplate') && isset($GLOBALS['oscTemplate']) && is_object($GLOBALS['oscTemplate']) && (get_class($GLOBALS['oscTemplate']) == 'oscTemplate');
    }
    public function confirmation()
    {
        global $customer_id, $order, $currencies, $currency;

        $amountCents = (int)$this->format_raw($order->info['total']) ;
        $Api = CheckoutApi_Api::getApi(array('mode'=>MODULE_PAYMENT_CHECKOUAPIPAYMENT_TYPE));
        $config = array();
        $config['debug'] = false;
        $config['renderMode'] = 2;
        $config['publicKey'] = MODULE_PAYMENT_CHECKOUTAPIPAYMENT_PUBLISHABLE_KEY ;
        $config['email'] =  $order->customer['email_address'];
        $config['name'] = $order->billing['firstname'] . ' ' . $order->billing['lastname'];
        $config['amount'] = $amountCents;
        $config['currency'] =  $order->info['currency'];
        $config['widgetSelector'] =  '.widget-container';
        $config['cardTokenReceivedEvent'] = "
                       document.getElementById('cko-cc-token').value = event.data.cardToken;
                        document.getElementById('cko-cc-email').value = event.data.email;

                        jQuery('[name=checkout_confirmation]').trigger('submit');
                        ";
        $config['widgetRenderedEvent'] ="";
        $config['readyEvent'] = '';


        $jsConfig = $Api->getJsConfig($config);


        $content =  <<<EOD
        <p>Please select a credit/debit card</p>
<div class="widget-container"></div>
<input type="hidden" name="cko_cc_token" id="cko-cc-token" value="">
<input type="hidden" name="cko_cc_email" id="cko-cc-email" value="" />
<script async src="https://www.checkout.com/cdn/js/Checkout.js"></script>
    <script type="text/javascript">
{$jsConfig}
</script>
EOD;




        $confirmation = array('title' => $content);

        return $confirmation;

    }


    public function before_process()
    {
        global  $HTTP_POST_VARS,$order;

        $config = parent::before_process();

        $config['postedParam']['email'] = $HTTP_POST_VARS['cko_cc_email'];
        $config['postedParam']['cardToken'] = $HTTP_POST_VARS['cko_cc_token'];
        $this->_placeorder($config);
    }

    public function  process_button()
    {

    }
}