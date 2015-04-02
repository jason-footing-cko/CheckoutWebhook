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

        $paymentTokenArray    =    $this->generatePaymentToken();
        return  array(
            'hasError' 			=>	 $hasError,
            'methodType' 		=>	 $this->getCode(),
            'template'          =>   'js.tpl',

            'simulateEmail'     =>   'dhirajmetal@mail.com',
            'publicKey'         =>    Configuration::get('CHECKOUTAPI_PUBLIC_KEY'),
            'paymentToken'      =>   $paymentTokenArray['token'],
            'message'           =>   $paymentTokenArray['message'],
            'success'           =>   $paymentTokenArray['success'],
            'eventId'           =>   $paymentTokenArray['eventId'],
            'amount'            =>    $amountCents,
            'mailAddress'       =>   $customer->email,
            'amount'            =>   $amountCents,
            'name'              =>   $customer->firstname . ' '.$customer->lastname ,
            'store'             =>   $customer->firstname . ' '.$customer->lastname ,
            'currencyIso'          =>   $currency->iso_code,

        );

    }

    public  function createCharge($config = array(),$cart)
    {

        $config['paymentToken']  = Tools::getValue('cko_cc_paymenToken');
        $scretKey =  Configuration::get('CHECKOUTAPI_SECRET_KEY');
        $config['authorization'] = $scretKey  ;

        $config['mode'] = Configuration::get('CHECKOUTAPI_TEST_MODE');
        $config['timeout'] =  Configuration::get('CHECKOUTAPI_GATEWAY_TIMEOUT');
        $Api = CheckoutApi_Api::getApi(array('mode'=> Configuration::get('CHECKOUTAPI_TEST_MODE')));
        return $Api->verifyChargePaymentToken($config);
    }


    private function generatePaymentToken()
    {
        $config = array();
        $cart = $this->context->cart;
      //  $currentOrder =;
        $currency = $this->context->currency;
        $customer = new Customer((int)$cart->id_customer);
        $billingAddress = new Address((int)$cart->id_address_invoice);
        $shippingAddress = new Address((int)$cart->id_address_delivery);
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);


        $scretKey =  Configuration::get('CHECKOUTAPI_SECRET_KEY');

        $orderId = (int)$cart->id;
        $amountCents = (int)$total*100;
        $config['authorization'] = $scretKey  ;

        $config['mode'] = Configuration::get('CHECKOUTAPI_TEST_MODE');
        $config['timeout'] =  Configuration::get('CHECKOUTAPI_GATEWAY_TIMEOUT');

        $billingAddressConfig = array(
            'addressLine1'       =>  $billingAddress->address1,
            'addressLine2'       =>  $billingAddress->address2,
            'addressPostcode'    =>  $billingAddress->postcode,
            'addressCountry'     =>  $billingAddress->country,
            'addressCity'        =>  $billingAddress->city ,
            'addressPhone'       =>  $billingAddress->phone,

        );


        $shippingAddressConfig = array(
            'addressLine1'       =>  $shippingAddress->address1,
            'addressLine2'       =>  $shippingAddress->address1,
            'addressPostcode'    =>  $shippingAddress->postcode,
            'addressCountry'     =>  $shippingAddress->country,
            'addressCity'        =>  $shippingAddress->city,
            'addressPhone'       =>  $shippingAddress->phone,
            'recipientName'      =>  $shippingAddress->firstname . ' '.$shippingAddress->lastname

        );
        $products = array();
        foreach ($cart->getProducts() as $item ) {

            $products[] = array (
                'name'          =>     strip_tags($item['name']),
                'sku'           =>     strip_tags($item['reference']),
                'price'         =>     $item['price']*100,
                'quantity'      =>     $item['cart_quantity']

            );
        }

        $config['postedParam']  = array (
                'email'             =>  $customer->email ,
                'value'             =>  $amountCents,
                'currency'          =>  $currency->iso_code,
                'description'       =>  "Order number::$orderId",
                'shippingDetails'   =>  $shippingAddressConfig,
                'products'          =>  $products,
                'metadata'          =>  array('trackId' => $orderId),
                'billingDetails'   =>    $billingAddressConfig
        );

        if(Configuration::get('CHECKOUTAPI_PAYMENT_ACTION') =='authorize_capture') {
            $config['postedParam'] = array_merge_recursive($config['postedParam'],$this->_captureConfig());

        }else {

            $config['postedParam'] = array_merge_recursive($config['postedParam'],$this->_authorizeConfig());
        }

        $Api = CheckoutApi_Api::getApi(array('mode'=> Configuration::get('CHECKOUTAPI_TEST_MODE')));
        $paymentTokenCharge = $Api->getPaymentToken($config);

        $paymentTokenArray    =   array(
                                    'message'   =>    '',
                                    'success'   =>    '',
                                    'eventId'   =>    '',
                                    'token'     =>    '',
                );

        if($paymentTokenCharge->isValid()){
            $paymentTokenArray['token'] = $paymentTokenCharge->getId();
            $paymentTokenArray['success'] = true;

        }else {


            $paymentTokenArray['message']    =    $paymentTokenCharge->getExceptionState()->getErrorMessage();
            $paymentTokenArray['success']    =    false;
            $paymentTokenArray['eventId']    =    $paymentTokenCharge->getEventId();

        }

        return $paymentTokenArray;

    }


}