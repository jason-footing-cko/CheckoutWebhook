<?php

 class models_methods_creditcard extends models_methods_Abstract{

 	protected $_code = 'creditcard';

    public function __construct()
    {
         $this ->id = 'checkoutapipayment';
         $this->has_fields = true;
         $this->pci_enable = 'no';
         parent::__construct();
    }

 	public function _initCode(){

 	}

 	public function payment_fields()
    {
        $amount = (int)(jigoshop_cart::$total)*100;
        $current_user = wp_get_current_user();
        $currencyCode = Jigoshop_Base::get_options()->get('jigoshop_currency');

        $email = "Email@youremail.com";
        if(isset($current_user->data)){
            $email = $current_user->user_email;

        }

        $paymentToken = $this->generatePaymentToken();

        ?>
        <div style="" class="widget-container">

            <input type="hidden" name="cko_cc_paymenToken" id="cko-cc-paymenToken" value="<?php echo $paymentToken?>">

            <script type="text/javascript">
                window.CKOConfig = {
                    debugMode: false,
                    renderMode: 2,
                    namespace: 'CheckoutIntegration',
                    publicKey: '<?php echo CHECKOUTAPI_PUBLIC_KEY ?>',
                    paymentToken: "<?php echo $paymentToken ?>",
                    value: '<?php echo $amount ?>',
                    currency: '<?php echo $currencyCode ?>',
                    customerEmail: '<?php echo $email ?>',
                    paymentMode: 'card',
                    title: '<?php  ?>',
                    subtitle:'<?php echo __('Please enter your credit card details') ?>',
                    widgetContainerSelector: '.widget-container',
                    cardCharged: function(event){
                        document.getElementById('cko-cc-paymenToken').value = event.data.paymentToken;
                        jQuery('.checkout.jigoshop-checkout')[0].submit();
                    }

                };

                // Checkout.render(window.CKOConfig);
                jQuery('.checkout.jigoshop-checkout')[0].onsubmit = function(){

                    if(jQuery('#payment_method_checkoutapipayment:checked')) {
                        CheckoutIntegration.open();
                    }
                }


            </script>
            <script src="http://ckofe.com/js/checkout.js" async ></script>
        </div>
        <?php
 	}

    public function generatePaymentToken()
    {
         $config = array();
         $amountCents = (int)(jigoshop_cart::$total)*100;
         $currencyCode = Jigoshop_Base::get_options()->get('jigoshop_currency');

         $config['authorization'] = CHECKOUTAPI_SECRET_KEY;
         $config['mode'] = CHECKOUTAPI_MODE;
         $config['timeout'] = CHECKOUTAPI_TIMEOUT;

         if (CHECKOUTAPI_PAYMENTACTION == 'Capture') {
             $config = array_merge($config, $this->_captureConfig());
         }
         else {

             $config = array_merge($config, $this->_authorizeConfig());
         }

         $config['postedParam'] = array_merge($config['postedParam'], array(
             'value' => $amountCents,
             'currency' => $currencyCode
         ));

         $Api = CheckoutApi_Api::getApi(array('mode' => CHECKOUTAPI_MODE));
         $paymentTokenCharge = $Api->getPaymentToken($config);

        $paymentToken    =   '';

        if($paymentTokenCharge->isValid()){
            $paymentToken = $paymentTokenCharge->getId();
        }

        if(!$paymentToken) {

            $error_message = $paymentTokenCharge->getExceptionState()->getErrorMessage().
                ' ( '.$paymentTokenCharge->getEventId().')';
          //  ( __('Payment error: ', 'woothemes') . $error_message, 'error' );
        }

        return $paymentToken;

    }

 	public function process_payment($order_id){

        $order = new jigoshop_order($order_id);

        $grand_total = $order->order_total;
        $amount = (int)$grand_total*100;
        $currencyCode = Jigoshop_Base::get_options()->get('jigoshop_currency');
//        $config['authorization'] = CHECKOUTAPI_SECRET_KEY;
//        $config['timeout'] = CHECKOUTAPI_TIMEOUT;
//        $config['postedParam'] = array('email' =>$order->billing_email,
//            'value'=> $amount,
//            'currency' => $currencyCode,
//            'description'=>"Order number::$order->id"
//        );
//
//        $extraConfig = array();
//
//        if(CHECKOUTAPI_PAYMENTACTION == 'Capture'){
//            $extraConfig = parent::_captureConfig();
//        } else {
//            $extraConfig= parent::_authorizeConfig();
//        }
//
//        $config['postedParam'] = array_merge($config['postedParam'],$extraConfig);
//        $config['postedParam']['cardToken'] = parent::get_post('cko_cc_token');
//
//
//
//        $config['postedParam']['shippingdetails'] = array(
//            'addressline1'   =>    $order->shipping_address_1,
//            'addressline2'   =>    $order->shipping_address_2,
//            'city'           =>    $order->shipping_city,
//            'country'        =>    $order->shipping_country,
//            'phone'          =>    $order->shipping_phone,
//            'postcode'       =>    $order->shipping_postcode,
//            'state'          =>    $order->shipping_state
//        );
//
//        $respondCharge = parent::_createCharge($config);
//        return parent::_validateChrage($order, $respondCharge);
//


 	}
	
	private function renderJsConfig($email, $amount, $name)
    {
//        $Api = CheckoutApi_Api::getApi(array('mode'=>CHECKOUTAPI_ENDPOINT));
//        $config = array();
//        $config['debug'] = false;
//        $config['publicKey'] = CHECKOUTAPI_PUBLIC_KEY ;
//        $config['email'] =  $email;
//        $config['name'] = $name;
//        $config['amount'] =  $amount;
//        $config['currency'] =  get_woocommerce_currency();
//        $config['widgetSelector'] =  '.widget-container';
//        $config['cardTokenReceivedEvent'] = "
//                        document.getElementById('cko-cc-token').value = event.data.cardToken;
//                        document.getElementById('cko-cc-email').value = event.data.email;
//                        payment.save();";
//        $config['widgetRenderedEvent'] ="if (jQuery('.cko-pay-now')) {
//                                                jQuery('.cko-pay-now').hide();
//                                            }";
//        $config['readyEvent'] = '';
//
//
//        $jsConfig = $Api->getJsConfig($config);
//
//        return $jsConfig;
    }
 }
