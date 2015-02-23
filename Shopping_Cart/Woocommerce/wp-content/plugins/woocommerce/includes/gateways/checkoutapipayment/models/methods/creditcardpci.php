<?php

 class models_methods_creditcardpci extends models_methods_Abstract{

 	protected $_code = 'creditcardpci';

 	public function __construct(){
 		$this ->id = 'checkoutapipayment';
 		$this->has_fields = true;
 		$this->checkoutapipayment_ispci = 'yes';
 		$this->supports[] = 'default_credit_card_form';
 		parent::__construct();
 	}

 	public function _initCode(){

 	}

 	public function payment_fields(){

 		$this->credit_card_form();
 	
 	}

 	public function validate_fields(){
 		$this->credit_card_form();
 	}

 	public function process_payment($order_id){

 		global $woocommerce;
 		$order = new WC_Order( $order_id );
		$grand_total = $order->order_total;
		$amount = (int)$grand_total*100;
		$config['authorization'] = CHECKOUTAPI_SECRET_KEY;
		//$config['mode'] = CHECKOUTAPI_ENDPOINT;
		$config['timeout'] = CHECKOUTAPI_TIMEOUT;
		$config['postedParam'] = array('email' =>$order->billing_email,
			'value'=> $amount,
			'currency' => $order->order_currency,
			'description'=>"Order number::$order_id"
		);
		$extraConfig = array();
		if(CHECKOUTAPI_PAYMENTACTION == 'Capture'){
			$extraConfig = parent::_captureConfig();
		}
		else {
			$extraConfig= parent::_authorizeConfig();
		}

		$config['postedParam'] = array_merge($config['postedParam'],$extraConfig);

		$cardnumber = preg_replace('/\D/', '', parent::get_post($this->id.'-card-number'));
		$cardexpiry = explode(" / ", parent::get_post($this->id.'-card-expiry')) ;


		
		$config['postedParam']['card'] = array(
			'name' => $order->billing_first_name .' '.$order->billing_last_name,
			'number' => $cardnumber,
			'expiryMonth' => $cardexpiry[0],
            'expiryYear' => $cardexpiry[1],
            'cvv' => parent::get_post($this->id.'-card-cvc'),
		);
		
		$config['postedParam']['card']['billingdetails'] = array(
			'addressline1' => $order->billing_address_1,
			'addressline2' => $order->billing_address_2,
			'city'=>$order->billing_city,
			'country' => $order->billing_country,
			'phone' => $order->billing_phone,
			'postcode' => $order->billing_postcode,
			'state'=>$order->billing_state
		);
		
		$config['postedParam']['shippingdetails'] = array(
			'addressline1' => $order->shipping_address_1,
			'addressline2' => $order->shipping_address_2,
			'city'=>$order->shipping_city,
			'country' => $order->shipping_country,
			'phone' => $order->shipping_phone,
			'postcode' => $order->shipping_postcode,
			'state'=>$order->shipping_state
		);
		
		
		$respondCharge = parent::_createCharge($config);
		
		return parent::_validateChrage($order, $respondCharge);

 	}

 }

?>