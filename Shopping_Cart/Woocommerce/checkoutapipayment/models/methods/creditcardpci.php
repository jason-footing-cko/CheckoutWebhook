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
 		
 		// $args = array();

 		// Credit Card Form Name Field
 		// $fields = array('card-holder' => '<p class="form-row form-row-wide">
			// 	<label for="' . esc_attr( $this->id ) . '-card-holder">' . __( 'Card Holder Name', 'woocommerce' ) . ' <span class="required">*</span></label>
			// 	<input id="' . esc_attr( $this->id ) . '-card-holder" class="input-text wc-credit-card-form-card-holder" type="text" maxlength="20" autocomplete="off" placeholder="Your Name Here" name="' . ( $args['fields_have_names'] ? $this->id . '-card-holder' : '' ) . '" />
			// </p>');
 		// $this->credit_card_form($args, $fields);

 		$this->credit_card_form();
 		

 	}

 	public function process_payment($order_id){

 		global $woocommerce;
 		$order = new WC_Order( $order_id );
		$grand_total = $order->order_total;
		$amount = (int)$grand_total*100;
		$config['authorization'] = CHECKOUTAPI_SECRET_KEY;
		$config['mode'] = CHECKOUTAPI_ENDPOINT;
		$config['timeout'] = CHECKOUTAPI_TIMEOUT;
		$config['postedParam'] = array('email' =>$order->billing_email,
			'amount'=> $amount,
			'currency' => $order->order_currency,
			'description'=>"Order number::$order_id"
		);
		$extraConfig = array();
		if(CHECKOUTAPI_PAYMENTACTION == 'Capture'){
			$extraConfig = $this->_captureConfig();
		}
		else {
			$extraConfig= $this->_authorizeConfig();
		}

		$config['postedParam'] = array_merge($config['postedParam'],$extraConfig);

		$cardnumber = preg_replace('/\D/', '', $this->get_post($this->id.'-card-number'));
		$cardexpiry = explode(" / ", $this->get_post($this->id.'-card-expiry')) ;

		$config['postedParam']['card'] = array(
			'name' => $order->billing_first_name .' '.$order->billing_last_name,
			'number' => $cardnumber,
			'expiryMonth' => $cardexpiry[0],
            'expiryYear' => $cardexpiry[1],
            'cvv' => $this->get_post($this->id.'-card-cvc'),
			'addressLine1' => $order->billing_address_1,
			'addressLine2' => $order->billing_address_2,
			'addressPostcode' => $order->billing_postcode,
			'addressCountry' => $order->billing_country,
			'addressCity'=>$order->billing_city,
			'addressState'=>$order->billing_state,
			'addressPhone' => $order->billing_phone
		);

		$respondCharge = parent::_createCharge($config);

		return parent::_validateChrage($order, $respondCharge);

 	}

 	private function get_post( $name ) {
		if ( isset( $_POST[ $name ] ) ){
				return $_POST[ $name ];
			}
			return null;
	}


    private function _captureConfig()
    {
        $to_return['postedParam'] = array (
            'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_CAPTURE,
            'autoCapTime' => CHECKOUTAPI_AUTOCAPTIME
        );

        return $to_return;
    }

    private function _authorizeConfig()
    {
        $to_return['postedParam'] = array (
            'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_AUTH,
            'autoCapTime' => 0
        );

        return $to_return;
    }

 }

?>