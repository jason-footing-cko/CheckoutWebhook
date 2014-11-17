<?php
class CheckoutApi_ChargePayment_Model_Method_Creditcardpci extends CheckoutApi_ChargePayment_Model_Method_Abstract
{
    /**
    * Is this payment method a gateway (online auth/charge) ?
    */
    protected $_isGateway = true;
    protected $_canUseInternal = true;
    protected $_code = 'creditcardpci';

    protected $_formBlockType = 'checkoutapi_chargePayment/form_creditcardpci';
   // protected $_infoBlockType = 'checkoutapi_chargePayment/info_creditcard';
    /**
     * @param Varien_Object $payment
     * @param $amount
     * @param array $extraConfig
     * @return mixed
     */

    protected  function _createCharge(Varien_Object $payment,$amount,$extraConfig = array())
    {

        /** @var CheckoutApi_Client_ClientGW3  $Api */
        $Api = CheckoutApi_Api::getApi(array('mode'=>$this->getConfigData('mode')));
        $scretKey = $this->getConfigData('privatekey');
        $order = $payment->getOrder();
        $billingaddress = $order->getBillingAddress();
        $currencyDesc = $order->getBaseCurrencyCode();
        $orderId = $order->getIncrementId();
        $amountCents = (int)$amount*100;
        $config = array();
        $config['authorization'] = $scretKey  ;
        $config['mode'] = $this->getConfigData('mode');
        $config['timeout'] = $this->getConfigData('timeout');
        $config['postedParam'] = array ( 'email'=>$billingaddress->getData('email'),
            'amount'=>$amountCents,
            'currency'=> $currencyDesc,
            'description'=>"Order number::$orderId",
             );

        $config['postedParam'] = array_merge($config['postedParam'],$extraConfig);
        $config['postedParam']['card'] = array(

                'phoneNumber'=>$billingaddress->getData('telephone'),
                'name'=>$payment->getCcOwner(),
                'number' => $payment->getCcNumber(),
                'expiryMonth' => $payment->getCcExpMonth(),
                'expiryYear' => $payment->getCcExpYear(),
                'cvv' => $payment->getCcCid(),

            );

        return $Api->createCharge($config);

    }

    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        parent::assignData($data);
        $info = $this->getInfoInstance();
        $info->setCcOwner($data->getCcOwner());


        return $this;
    }


}