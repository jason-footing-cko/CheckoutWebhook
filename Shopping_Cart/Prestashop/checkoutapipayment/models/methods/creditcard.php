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

        return  array(
            'hasError' 			=>	 $hasError,
            'methodType' 		=>	 $this->getCode(),
            'template'          =>   'js.tpl',
            'simulateEmail'     =>   'dhirajmetal@mail.com',
            'publicKey'         =>    Configuration::get('CHECKOUTAPI_PUBLIC_KEY'),
            'mailAddress'       =>   $customer->email,
            'amount'            =>   $amountCents,
            'currency'          =>   $currency->iso_code,

        );

    }

    public  function createCharge($config = array(),$cart)
    {

        $config['postedParam']['token']  = Tools::getValue('cko_cc_token');
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