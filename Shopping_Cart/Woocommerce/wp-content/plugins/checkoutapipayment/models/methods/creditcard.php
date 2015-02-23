<?php
 class models_methods_creditcard extends models_methods_Abstract
 {

 	protected $_code = 'creditcard';
 	public function _construct()
    {
 		$this ->id = 'checkoutapipayment';
 		$this->has_fields = true;
 		$this->checkoutapipayment_ispci = 'no';

 	}

 	public function _initCode(){}

 	public function payment_fields()
    {
        global $woocommerce;;
        $grand_total = (float) WC()->cart->total;
        $amount = (int)$grand_total*100;
        $current_user = wp_get_current_user();

        $email = "Email@youremail.com";
        if(isset($current_user->data)){
            $email = $current_user->user_email;
			$name = $current_user->user_first_name;
        }
            
 	?>
		
	    <div style="" class="widget-container">
            <p>Please select your credit card type</p>
            <input type="hidden" name="cko_cc_token" id="cko-cc-token" value="">
            <input type="hidden" name="cko_cc_email" id="cko-cc-email" value="" />
			<script  src="https://www.checkout.com/cdn/js/Checkout.js"></script>
            <script>
                <?php echo $this->renderJsConfig($email, $amount, $name) ?>
                //Checkout.render();
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
        //$config['mode'] = CHECKOUTAPI_ENDPOINT;
        $config['timeout'] = CHECKOUTAPI_TIMEOUT;
        $config['postedParam'] = array('email' =>parent::get_post('cko_cc_email'),
            'value'=> $amount,
            'currency' => $order->order_currency,
            'description'=>"Order number::$order_id"
        );

        $extraConfig = array();

        if(CHECKOUTAPI_PAYMENTACTION == 'Capture'){
            $extraConfig = parent::_captureConfig();
        } else {
            $extraConfig= parent::_authorizeConfig();
        }

        $config['postedParam'] = array_merge($config['postedParam'],$extraConfig);
        $config['postedParam']['cardToken'] = parent::get_post('cko_cc_token');

        $config['postedParam']['shippingdetails'] = array(
			'addressline1'   =>    $order->shipping_address_1,
			'addressline2'   =>    $order->shipping_address_2,
			'city'           =>    $order->shipping_city,
			'country'        =>    $order->shipping_country,
			'phone'          =>    $order->shipping_phone,
			'postcode'       =>    $order->shipping_postcode,
			'state'          =>    $order->shipping_state
		);

        $respondCharge = parent::_createCharge($config);
        return parent::_validateChrage($order, $respondCharge);
 	}
	
	private function renderJsConfig($email, $amount, $name)
    {
        $Api = CheckoutApi_Api::getApi(array('mode'=>CHECKOUTAPI_ENDPOINT));
        $config = array();
        $config['debug'] = false;
        $config['publicKey'] = CHECKOUTAPI_PUBLIC_KEY ;
        $config['email'] =  $email;
        $config['name'] = $name;
        $config['amount'] =  $amount;
        $config['currency'] =  get_woocommerce_currency();
        $config['widgetSelector'] =  '.widget-container';
        $config['cardTokenReceivedEvent'] = "
                        document.getElementById('cko-cc-token').value = event.data.cardToken;
                        document.getElementById('cko-cc-email').value = event.data.email;
                        payment.save();";

        $config['widgetRenderedEvent'] ="if (jQuery('.cko-pay-now')) {
                                                jQuery('.cko-pay-now').hide();
                                            }";
        $config['readyEvent'] = '';


        $jsConfig = $Api->getJsConfig($config);

        return $jsConfig;
    }
 }
