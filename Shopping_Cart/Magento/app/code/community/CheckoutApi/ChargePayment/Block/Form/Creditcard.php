<?php

class CheckoutApi_ChargePayment_Block_Form_Creditcard  extends Mage_Payment_Block_Form_Cc
 {
     /**
      * setting up block template
      */
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('chargepayment/form/creditcard.phtml');


    }

    private function _getQuote()
    {
        return  Mage::getSingleton('checkout/session')->getQuote();
    }
    /**
     * Retrieve payment configuration object
     *
     * @return Mage_Payment_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('checkoutapi_chargePayment/config');
    }

    public function simulateChargeToken()
    {
        $cardTokenConfig = array();
        $cardTokenConfig['authorization'] = $this->getConfigData('publickey');
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

    public function getConfigData($field, $storeId = null)
    {
        return Mage::helper('checkoutapi_chargePayment')->getConfigData($field,'creditcard',$storeId);;
    }

    public  function getPublicKey()
    {
        return $this->getConfigData('publickey');
    }

    public function getAmount()
    {
        return   $this->_getQuote()->getGrandTotal()*100;

    }

    public function getCurrency()
    {
        return   Mage::app()->getStore()->getCurrentCurrencyCode();

    }

    public function getEmailAddress()
    {
        return  $this->_getQuote()->getBillingAddress()->getEmail();

    }

    public function getName()
    {
        return  $this->_getQuote()->getBillingAddress()->getName();

    }

    public function renderJsConfig()
    {
        $Api = CheckoutApi_Api::getApi(array('mode'=>$this->getConfigData('mode')));
        $config = array();
        $config['debug'] = false;
        $config['publicKey'] = $this->getPublicKey() ;
        $config['email'] =  $this->getEmailAddress();
        $config['name'] = $this->getName();
        $config['amount'] =  $this->getAmount();
        $config['currency'] =  $this->getCurrency();
        $config['widgetSelector'] =  '.widget-container';
        $config['cardTokenReceivedEvent'] = "
                        document.getElementById('cko-cc-token').value = event.data.cardToken;
                        document.getElementById('cko-cc-email').value = event.data.email;
                        payment.save();";
        $config['widgetRenderedEvent'] ="if ($$('.cko-pay-now')[0]) {
                                                $$('.cko-pay-now')[0].hide();
                                            }";
        $config['readyEvent'] = '';


        $jsConfig = $Api->getJsConfig($config);

        return $jsConfig;
    }

    public function getStoreName()
    {

        return  Mage::app()->getStore()->getName();
    }

    public function getPaymentToken()
    {
//        getPaymentToken
//
        $Api = CheckoutApi_Api::getApi(array('mode'=>$this->getConfigData('mode')));


        $scretKey = $this->getConfigData('privatekey');

        $billingAddress = $this->_getQuote()->getBillingAddress();
        $shippingAddress = $this->_getQuote()->getBillingAddress();
        $orderedItems = $this->_getQuote()->getAllItems();
        $currencyDesc = $this->_getQuote()->getBaseCurrencyCode();

        $amountCents = $this->getAmount();


        $street = Mage::helper('customer/address')
            ->convertStreetLines($shippingAddress->getStreet(), 2);
        $shippingAddressConfig = array(
            'addressLine1'       =>     $street[0],
            'addressLine2'       =>     $street[1],
            'postcode'           =>     $shippingAddress->getPostcode(),
            'country'            =>     $shippingAddress->getCountry(),
            'city'               =>     $shippingAddress->getCity(),
            'phone'              =>     $shippingAddress->getTelephone(),
            'recipientName'      =>     $shippingAddress->getFirstname(). ' '.$shippingAddress->getLastname()

        );

        $products = array();
        foreach ($orderedItems as $item ) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());

            $products[] = array (
                'name'       =>     $item->getName(),
                'sku'        =>     $item->getSku(),
                'price'      =>     $item->getPrice(),
                'quantity'   =>     $item->getQty(),
                'image'      =>     Mage::helper('catalog/image')->init($product, 'image')->__toString()
            );
        }

        $config = array();
        $config['authorization'] = $scretKey  ;
        $config['mode'] = $this->getConfigData('mode');
        $config['timeout'] = $this->getConfigData('timeout');

        $street = Mage::helper('customer/address')
            ->convertStreetLines($billingAddress->getStreet(), 2);

        $billingAddressConfig = array(
            'addressLine1'   =>    $street[0],
            'addressLine2'   =>    $street[1],
            'postcode'       =>    $billingAddress->getPostcode(),
            'country'        =>    $billingAddress->getCountry(),
            'city'           =>    $billingAddress->getCity(),
            'phone'          =>    $billingAddress->getTelephone(),

        );

        $config['postedParam'] = array (
            'value'             =>    $amountCents,
            "chargeMode"        =>    1,
            'currency'          =>    $currencyDesc,
            'shippingDetails'   =>    $shippingAddressConfig,
            'products'          =>    $products,
            'billingDetails'    =>    $billingAddressConfig

        );

        if($this->getConfigData('order_status_capture') == Mage_Paygate_Model_Authorizenet::ACTION_AUTHORIZE ) {
            $config['postedParam']['autoCapture']  = CheckoutApi_Client_Constant::AUTOCAPUTURE_AUTH;
            $config['postedParam']['autoCapTime']  = 0;

        } else {

            $config['postedParam']['autoCapture']  = CheckoutApi_Client_Constant::AUTOCAPUTURE_CAPTURE;
            $config['postedParam']['autoCapTime']  = $this->getConfigData('auto_capture_time');

        }

        $paymentTokenCharge = $Api->getPaymentToken($config);

        $paymentToken    =   '';

        if($paymentTokenCharge->isValid()){
            $paymentToken = $paymentTokenCharge->getId();
        }

        if(!$paymentToken) {
            Mage::throwException(Mage::helper('payment')->__( $paymentTokenCharge->getExceptionState()->getErrorMessage().
                ' ( '.$paymentTokenCharge->getEventId().')'
            ));
        }

        return $paymentToken;

    }
 }