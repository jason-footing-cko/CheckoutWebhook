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
        $confirmation = '';
        return $confirmation;

    }


    public function before_process()
    {
        global  $HTTP_POST_VARS,$order;

        $config = parent::before_process();

        $this->_placeorder($config);
    }

    public function  process_button()
    {
        global $order,$messageStack;
        $amount = $order->info['total'];
        $amountCents = (int)($amount *100);

        $publicKey = MODULE_PAYMENT_CHECKOUTAPIPAYMENT_PUBLISHABLE_KEY;

        $paymentToken =  $_POST['cko-paymentToken'];

        if(!$paymentToken) {


            $messageStack->add_session ( 'checkout_payment' , 'Please try again there was an issue your payment details' ,
                'error' );
            if ( !isset( $_GET[ 'error' ] ) ) {
                tep_redirect(tep_href_link ( FILENAME_CHECKOUT_PAYMENT , 'error=true' , 'SSL' , true , false ) );
            }
        }
        $content =
            <<<EOD
	        <div class="widget-container" style="display:none"></div>
        <script src="https://www.checkout.com/cdn/js/checkout.js" async ></script>
        <input type="hidden" name="cko-paymentToken" id="cko-paymentToken" value="{$paymentToken}" />
        <script type="text/javascript">

            window.CKOConfig = {
                publicKey: "{$publicKey}",
                 renderMode: 2,
                 customerEmail: '{$order->customer['email_address']}' ,
                 customerName: '{$order->customer['firstname']} {$order->customer['lastname']}',
                 namespace: 'CheckoutIntegration',
                 paymentToken:'{$paymentToken}',
                 value: "{$amountCents}",
                 currency: "{$order->info['currency']}",
                 widgetContainerSelector: '.widget-container',
                 cardCharged: function(event){
                     $('[name^=checkout_confirmation]').trigger('submit');
                }
           };
            (function($){
             $(function(){
                $('[name^=checkout_confirmation] button[type=submit]').click(function(event){
                 event.preventDefault();
                 CheckoutIntegration.open();
                });
             });

            })(jQuery);
        </script>
EOD;

        $process_button_string = '<input type="hidden" name="cko_paymentToken" value = "'.$paymentToken.'">';


        echo $process_button_string;
        echo $content;

        return $process_button_string;
    }

    private function getPaymentToken()
    {
        global  $order,  $_POST,$messageStack;
        $config = array();
        $Api = CheckoutApi_Api::getApi(array('mode'=> MODULE_PAYMENT_CHECKOUTAPIPAYMENT_TRANSACTION_SERVER));
        $amount = (int)$order->info['total'];
        $amountCents = $amount *100;
        $config['authorization'] = MODULE_PAYMENT_CHECKOUTAPIPAYMENT_SECRET_KEY;
        $config['mode'] = MODULE_PAYMENT_CHECKOUTAPIPAYMENT_TRANSACTION_SERVER;

        $products = array();
        $i = 1;
        foreach($order->products as $product) {

            $products[] = array (
                'name'       =>    $product['name'],
                'sku'        =>    $product['id'],
                'price'      =>    $product['final_price'],
                'quantity'   =>     $product['qty'],
            );
            $i++;
        }

        $billingAddress = array (
            'addressLine1'    => $order->billing['street_address'],
            'addressLine2'    => $order->billing['address_line_2'],
            'postcode'        => $order->billing['postcode'],
            'country'         => $order->billing['country']['title'],
            'city'            => $order->billing['city'],
            'state'           => $order->billing['state'],
            'phone'           => $order->customer['telephone']
        );

        $config['postedParam'] = array (
            'email'           =>    $order->customer['email_address'] ,
            'name'           =>    "{$order->customer['firstname']} {$order->customer['lastname']}",
            'value'           =>    $amountCents,
            'currency'        =>    $order->info['currency'] ,

            'products'        =>    $products,
            'shippingDetails' =>
                array (
                    'addressLine1'    =>    $order->delivery['street_address'],
                    'addressLine2'    =>    $order->delivery['address_line_2'],
                    'Postcode'        =>    $order->delivery['postcode'],
                    'Country'         =>    $order->delivery['country']['title'],
                    'City'            =>    $order->delivery['city'],
                    'Phone'           =>    $order->customer['telephone'],
                    'recipientname'   =>    $order->delivery['firstname'].' '.$order->delivery['lastname']
                )
        );

        if (MODULE_PAYMENT_CHECKOUAPIPAYMENT_AUTOCAPTIME == 'Authorize and Capture') {
            $config = array_merge_recursive ( $this->_captureConfig(),$config);
        } else {
            $config = array_merge_recursive ( $this->_authorizeConfig(),$config);
        }

        $paymentTokenCharge = $Api->getPaymentToken($config);
        $paymentToken    =   '';

        if($paymentTokenCharge->isValid()){
            $paymentToken = $paymentTokenCharge->getId();
        }

        if(!$paymentToken) {
            $error_message = $paymentTokenCharge->getExceptionState()->getErrorMessage().
                ' ( '.$paymentTokenCharge->getEventId().')';

            $messageStack->add_session('checkout_payment', $error_message . '<!-- ['.$this->code.'] -->', 'error');
            if(!isset($_GET['payment_error'])) {
              //  tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' .
              //      $paymentTokenCharge->getErrorCode(), 'SSL'));
            }
        }

        return $paymentToken;
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
        $paymentToken =  $this->getPaymentToken();

        $content =
            <<<EOD
	        <div class="widget-container"></div>
        <script src="https://www.checkout.com/cdn/js/checkout.js" async ></script>
        <input type="hidden" name="cko-paymentToken" id="cko-paymentToken" value="{$paymentToken}" />
        <script type="text/javascript">

            window.CKOConfig = {
                publicKey: "{$publicKey}",
                 renderMode: 2,
                 customerEmail: '{$email}' ,
                 customerName: '{$order->customer['firstname']} {$order->customer['lastname']}',
                 paymentToken:'{$paymentToken}',
                 value: "{$amountCents}",
                 currency: "{$currency}",
                 widgetContainerSelector: '.widget-container'
           }

        </script>
EOD;
        $selection = array(

                    'fields' => array(
                                    array('field' => $content)
                                )
                    );

        return $selection;

    }

    protected function _createCharge($config)
    {
        $Api = CheckoutApi_Api::getApi(array('mode'=> MODULE_PAYMENT_CHECKOUTAPIPAYMENT_TRANSACTION_SERVER));
        $config['paymentToken'] = $_POST['cko_paymentToken'];

        return $Api->verifyChargePaymentToken($config);
    }
}