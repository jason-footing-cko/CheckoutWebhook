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
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        $path = 'payment/creditcard/'.$field;
        return Mage::getStoreConfig($path, $storeId);
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
 }