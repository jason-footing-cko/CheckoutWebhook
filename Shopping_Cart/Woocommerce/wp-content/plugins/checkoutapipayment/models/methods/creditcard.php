<?php
 class models_methods_creditcard extends models_methods_Abstract
 {

 	protected $_code = 'creditcard';
    private $_paymentToken = '';

 	public function __construct()
    {
 		$this ->id = 'checkoutapipayment';
 		$this->has_fields = true;
 		$this->checkoutapipayment_ispci = 'no';

        add_action ( 'woocommerce_checkout_order_review' , array ( $this , 'setPaymentToken' ) );
        add_action ( 'woocommerce_checkout_order_review' , array ( $this , 'setJsInit' ) );


 	}
    public function _initCode(){}

    public function setJsInit()
    {
        ?> <script src="https://www.checkout.com/cdn/js/checkout.js" async ></script>
        <?php
    }



 	public function payment_fields()
    {
        global $woocommerce;
        $grand_total = (float) WC()->cart->total;
        $amount = (int)$grand_total*100;
        $current_user = wp_get_current_user();

        $email = "Email@youremail.com";
        $name = 'Your card holder name';
        if(isset($current_user->data)){
            $email = $current_user->user_email;

        }

        if(isset($current_user->user_first_name)){

            $name = $current_user->user_first_name;
        }

        $paymentToken = $this->getPaymentToken();
 	?>
		
	    <div style="" class="widget-container">

            <input type="hidden" name="cko_cc_paymenToken" id="cko-cc-paymenToken" value="<?php echo $paymentToken ?>">

            <script type="text/javascript">
                if(window.CKOConfig) {
                    CheckoutIntegration.render(window.CKOConfig);
                }else {
                    window.CKOConfig = {
                        debugMode: false,
                        renderMode: 2,
                        namespace: 'CheckoutIntegration',
                        publicKey: '<?php echo CHECKOUTAPI_PUBLIC_KEY ?>',
                        paymentToken: "<?php echo $paymentToken ?>",
                        value: '<?php echo $amount ?>',
                        currency: '<?php echo get_woocommerce_currency() ?>',
                        customerEmail: '<?php echo $email ?>',
                        customerName: '<?php echo $name?>',
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
                            jQuery('.checkout.woocommerce-checkout')
                                .removeClass('processing')
                                .addClass('wasActived')
                                .trigger('submit');
                        }

                    };
                }

               // Checkout.render(window.CKOConfig);
                jQuery('.checkout.woocommerce-checkout')[0].onsubmit = function(){

                    if(jQuery('#payment_method_checkoutapipayment:checked') ) {
                        if(!jQuery('.checkout.woocommerce-checkout').is('processing')
                            && !jQuery('.checkout' +
                            '.woocommerce-checkout').is('wasActived')) {
                            jQuery('.checkout.woocommerce-checkout').addClass('processing');
                        }

                       if( !jQuery('.checkout.woocommerce-checkout').is('wasActived')) {
                           CheckoutIntegration.open();
                       } else {
                           //jQuery('.checkout.woocommerce-checkout').removeClass('wasActived')
                       }

                    }
                    return true;
                }

                jQuery('#place_order').click(function(event){
                    wc_checkout_params.is_checkout = 0;
                })


            </script>

        </div>
 	
		

	<?php

 	}

 	public function process_payment($order_id)
    {
        global $woocommerce;
        $order = new WC_Order( $order_id );
        $grand_total = $order->order_total;
        $amount = (int)$grand_total*100;
        $config['authorization'] = CHECKOUTAPI_SECRET_KEY;

        $config['paymentToken'] = parent::get_post('cko_cc_paymenToken');
        $Api = CheckoutApi_Api::getApi(array('mode'=>CHECKOUTAPI_ENDPOINT));

        $respondCharge = $Api->verifyChargePaymentToken($config);

        return parent::_validateChrage($order, $respondCharge);
 	}
	
	private function renderJsConfigrenderJsConfig($email, $amount, $name)
    {

    }

    public function setPaymentToken()
    {

        if(! WC()->cart) {
            return false;
        }
        $Api = CheckoutApi_Api::getApi(array('mode'=>CHECKOUTAPI_ENDPOINT));

        global $woocommerce;
        $cart = WC()->cart;
        $customer = WC()->customer;
        $productCart = WC()->cart->cart_contents;

        $current_user = wp_get_current_user();

        $email = "Email@youremail.com";
        if(isset($current_user->data)){
            $email = $current_user->user_email;

        }

        $grand_total = $cart->total;
        $amount = (int)$grand_total*100;
        $config['authorization'] = CHECKOUTAPI_SECRET_KEY;

        $config['timeout'] = CHECKOUTAPI_TIMEOUT;

        $config['postedParam'] = array(
            'email'       =>    $email,
            'value'       =>    $amount,

            'currency'    =>    get_woocommerce_currency()
        );

        $extraConfig = array();
        if(CHECKOUTAPI_PAYMENTACTION == 'Capture'){
            $extraConfig = parent::_captureConfig();
        }
        else {
            $extraConfig= parent::_authorizeConfig();
        }

        $config = array_merge($extraConfig,$config);

        if($customer) {
           $config['postedParam']['billingdetails'] = array (
                'addressline1'  =>    $customer->address,
                'addressline2'  =>    $customer->address_2,
                'city'          =>    $customer->city,
                'country'       =>    $customer->country,

                'state'         =>    $customer->country
            );
        }
        $products = null;


        foreach ($productCart as $item ) {

            $products[] = array (
                'name'       =>     $item['data']->post->post_title,
                'sku'        =>     $item['product_id'],
                'price'      =>     $item['line_total'],
                'quantity'   =>     $item['quantity'],

            );
        }

        if($products){
            $config[ 'postedParam' ][ 'products' ]  =   $products;
        }

        if($customer) {
            $config[ 'postedParam' ][ 'shippingdetails' ] = array (
                'addressline1'  =>    $customer->shipping_address ,
                'addressline2'  =>    $customer->shipping_address_2 ,
                'city'          =>    $customer->shipping_city ,
                'country'       =>    $customer->shipping_country ,
                'postcode'      =>    $customer->shipping_postcode ,
                'state'         =>    $customer->shipping_state
            );
        }

        $paymentTokenCharge = $Api->getPaymentToken($config);

        $paymentToken    =   '';

        if($paymentTokenCharge->isValid()){
            $paymentToken = $paymentTokenCharge->getId();
        }

        if(!$paymentToken) {

            $error_message = $paymentTokenCharge->getExceptionState()->getErrorMessage().
                ' ( '.$paymentTokenCharge->getEventId().')';
            wc_add_notice( __('Payment error: ', 'woothemes') . $error_message, 'error' );
        }

        $this->_paymentToken = $paymentToken;

    }

     public function getPaymentToken()
     {
         return $this->_paymentToken;
     }
 }
