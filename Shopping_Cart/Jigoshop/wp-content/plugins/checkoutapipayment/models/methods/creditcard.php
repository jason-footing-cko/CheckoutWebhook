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

 	public function payment_fields(){

       print_r($this->generatePaymentToken());


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

         $paymentTokenArray = array(
             'message' => '',
             'success' => '',
             'eventId' => '',
             'token' => '',
         );

         if ($paymentTokenCharge->isValid()) {
             $paymentTokenArray['token'] = $paymentTokenCharge->getId();
             $paymentTokenArray['success'] = true;
         }
         else {


             $paymentTokenArray['message'] = $paymentTokenCharge->getExceptionState()->getErrorMessage();
             $paymentTokenArray['success'] = false;
             $paymentTokenArray['eventId'] = $paymentTokenCharge->getEventId();
         }

        return $paymentTokenArray;
    }

 	public function process_payment($order_id){

        die('here');
        global $woocommerce;
        //$order = new WC_Order( $order_id );
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
        }
        else {
            $extraConfig= parent::_authorizeConfig();
        }

        $config['postedParam'] = array_merge($config['postedParam'],$extraConfig);
        $config['postedParam']['cardToken'] = parent::get_post('cko_cc_token');
		
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
