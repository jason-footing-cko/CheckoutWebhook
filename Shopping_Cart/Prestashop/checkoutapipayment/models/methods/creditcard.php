<?php
class models_methods_creditcard extends models_methods_Abstract
{
    protected  $_code = 'creditcard';

    public function __construct()
    {
        $this->name = 'creditcard';
        parent::__construct();
    }
    public  function _initCode()
    {

    }
    public function hookPayment($param)
    {
        $hasError = false;
        $cart = $this->context->cart;
        $currency = $this->context->currency;
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
        $amountCents = (int)$total*100;
        $customer = new Customer((int)$cart->id_customer);
        $Api = CheckoutApi_Api::getApi(array('mode'=> Configuration::get('CHECKOUTAPI_TEST_MODE')));
        $config = array();
        $config['debug'] = false;
        $config['publicKey'] = Configuration::get('CHECKOUTAPI_PUBLIC_KEY') ;
        $config['email'] =  $customer->email;
        $config['name'] = $customer->firstname . ' '.$customer->lastname ;
        $config['amount'] =  $amountCents;
        $config['currency'] =   $currency->iso_code;
        $config['widgetSelector'] =  '.widget-container';
        $config['cardTokenReceivedEvent'] = "
                document.getElementById('cko-cc-token').value = event.data.cardToken;
                document.getElementById('cko-cc-email').value = event.data.email;
                document.getElementById('checkoutapipayment_form').submit();";

        $config['widgetRenderedEvent'] ="";
        $config['readyEvent'] = '';


        $jsConfig = $Api->getJsConfig($config);

        return  array(
            'hasError' 			=>	 $hasError,
            'methodType' 		=>	 $this->getCode(),
            'template'          =>   'js.tpl',
            'jsScript'          =>   $jsConfig,
            'simulateEmail'     =>   'dhirajmetal@mail.com',
            'publicKey'         =>    Configuration::get('CHECKOUTAPI_PUBLIC_KEY'),
            'mailAddress'       =>   $customer->email,
            'amount'            =>   $amountCents,
            'currency'          =>   $currency->iso_code,

        );

    }

    public  function createCharge($config = array(),$cart)
    {

        $config['postedParam']['cardToken']  = Tools::getValue('cko_cc_token');
        $config['postedParam']['email']  = Tools::getValue('cko_cc_email');
        return parent::_createCharge($config);
    }


    public function simulateChargeToken()
    {
        $cardTokenConfig = array();
        $cardTokenConfig['authorization'] = Configuration::get('CHECKOUTAPI_PUBLIC_KEY');
        $cardTokenConfig['postedParam'] = array (
            'email' =>'dhiraj@checkout.com',
            'card' => array(
                'phoneNumber'=>'0123465789',
                'name'=>'test name',
                'number' => '4543474002249996',
                'expiryMonth' => 06,
                'expiryYear' => 2017,
                'cvv' => 956,
            )
        );
        $Api = CheckoutApi_Api::getApi();

        return $Api->getCardToken( $cardTokenConfig );
    }
}