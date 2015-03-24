<?php

class models_methods_creditcard extends models_methods_Abstract{

 	protected $_code = 'creditcard';

    public function __construct()
    {
         $this ->id = 'checkoutapipayment';
         $this->has_fields = true;
         $this->pci_enable = 'no';
         //parent::__construct();

        add_action ( 'jigoshop_checkout_order_review' , array ( $this , 'generatePaymentToken' ) );
        add_action ( 'jigoshop_checkout_order_review' , array ( $this , 'setJsInit' ) );
    }

 	public function _initCode(){

 	}

    public function setJsInit()
    {
        ?> <script src="https://www.checkout.com/cdn/js/checkout.js" async ></script>
    <?php
    }

 	public function payment_fields()
    {

        $amount = (int)(jigoshop_cart::$total)*100;
        $current_user = wp_get_current_user();
        $currencyCode = Jigoshop_Base::get_options()->get('jigoshop_currency');

        $email = "Email@youremail.com";
        $name = "Card holder name";
        if(isset($current_user->data)){
            $email = $current_user->user_email;
            $name = $current_user->user_nicename;

        }

        $paymentToken = $this->generatePaymentToken();

        ?>
        <div style="" class="widget-container">

            <input type="hidden" name="cko_cc_paymenToken" id="cko-cc-paymenToken" value="<?php echo $paymentToken?>">

            <script type="text/javascript">
                if(window.CKOConfig) {
                    CheckoutIntegration.render(window.CKOConfig);
                }else {
                    window.CKOConfig = {
                        debugMode: false,
                        renderMode: 2,
                        apiUrl: 'https://preprod.checkout.com/api2/v2/',
                        namespace: 'CheckoutIntegration',
                        publicKey: '<?php echo CHECKOUTAPI_PUBLIC_KEY ?>',
                        paymentToken: "<?php echo $paymentToken ?>",
                        value: '<?php echo $amount ?>',
                        currency: '<?php echo $currencyCode ?>',
                        customerEmail: '<?php echo $email ?>',
                        customerName: '<?php echo $name ?>',
                        paymentMode: 'card',
                        title: '<?php  ?>',
                        subtitle: '<?php echo __('Please enter your credit card details') ?>',
                        widgetContainerSelector: '.widget-container',
                        ready: function (event) {
                            var cssAdded = jQuery('.widget-container link');
                            if (!cssAdded.hasClass('checkoutAPiCss')) {
                                cssAdded.addClass('checkoutAPiCss');
                            }

                            jQuery('head').append(cssAdded);
                        },
                        cardCharged: function (event) {
                            document.getElementById('cko-cc-paymenToken').value = event.data.paymentToken;
                            jQuery('.checkout.checkout')
                                .removeClass('processing')
                                .addClass('wasActived')
                                .trigger('submit');
                        }

                    };
                }

                    // Checkout.render(window.CKOConfig);
                        jQuery('.checkout.checkout')[0].onsubmit = function(){

                            if(jQuery('#payment_method_checkoutapipayment:checked') ) {
                                if(!jQuery('.checkout.checkout').is('processing')
                                    && !jQuery('.checkout' +
                                    '.checkout').is('wasActived')) {
                                    jQuery('.checkout.checkout').addClass('processing');
                                }

                                if( !jQuery('.checkout.checkout').is('wasActived')) {
                                    CheckoutIntegration.open();
                                } else {
                                    //jQuery('.checkout.woocommerce-checkout').removeClass('wasActived')
                                }

                            }
                            return true;
                        }

                jQuery('#place_order').click(function(event){
                    jigoshop_params.is_checkout = 0;
                })

            </script>

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

        }


        return $paymentToken;

    }

 	public function process_payment($order_id){

        $order = new jigoshop_order($order_id);

        $grand_total = $order->order_total;
        $amount = (int)$grand_total*100;
        $currencyCode = Jigoshop_Base::get_options()->get('jigoshop_currency');


        die('here');

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
	

 }
