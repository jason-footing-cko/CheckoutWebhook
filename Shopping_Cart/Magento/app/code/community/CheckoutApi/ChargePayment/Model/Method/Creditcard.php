<?php
class CheckoutApi_ChargePayment_Model_Method_Creditcard extends CheckoutApi_ChargePayment_Model_Method_Abstract
{
    /**
    * Is this payment method a gateway (online auth/charge) ?
    */
    protected $_isGateway = true;
    protected $_canUseInternal = true;
    protected $_code = 'creditcard';

    protected $_formBlockType = 'checkoutapi_chargePayment/form_creditcard';
   // protected $_infoBlockType = 'checkoutapi_chargePayment/info_creditcard';

    /**
     * @param Varien_Object $payment
     * @param $amount
     * @param array $extraConfig
     * @return mixed
     */


    protected function _createCharge(Varien_Object $payment,$amount,$extraConfig = array())
    {
        /** @var CheckoutApi_Client_ClientGW3  $Api */
        $Api = CheckoutApi_Api::getApi(array('mode'=>$this->getConfigData('mode')));
        $config = parent::_createCharge($payment,$amount,$extraConfig);
        $config['postedParam']['email'] = $payment->getAdditionalData('cko_cc_email');
        $config['postedParam']['cardToken'] = $payment->getAdditionalData('cko_cc_token');

        return $Api->createCharge($config);

    }

    public function validate()
    {
        /**
         * to validate payment method is allowed for billing country or not
         */
        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
            $billingCountry = $paymentInfo->getOrder()->getBillingAddress()->getCountryId();
        } else {
            $billingCountry = $paymentInfo->getQuote()->getBillingAddress()->getCountryId();
        }
        if (!$this->canUseForCountry($billingCountry)) {
            Mage::throwException(Mage::helper('payment')->__('Selected payment type is not allowed for billing country.'));
        }
        return $this;
    }

    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        parent::assignData($data);
        $info = $this->getInfoInstance();

//        $info->setData('cko_cc_token',$data->getData('cko_cc_token'));
//        $info->setData('cko_cc_email',$data->getData('cko_cc_email'));
        $details['cko_cc_token'] = $data->getData('cko_cc_token');
        $details['cko_cc_email'] = $data->getData('cko_cc_email');
        $info->setAdditionalData($details);
        return $this;
    }


}