<?php

abstract class CheckoutApi_ChargePayment_Model_Method_Abstract extends Mage_Payment_Model_Method_Cc
{
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canRefund               = true;
    protected $_canVoid              = true;


    private function _placeOrder(Varien_Object $payment,$amount ,$messageSuccess, $extraConfig)
    {
        /** @var CheckoutApi_Lib_RespondObj  $respondCharge */

        $respondCharge = $this->_createCharge($payment,$amount,$extraConfig);

        if( $respondCharge->isValid()) {

            if(preg_match('/^1[0-9]+$/',$respondCharge->getResponseCode())) {
                /** @var Mage_Sales_Model_Order_Payment_Transaction  $payment */
                /** @var Mage_Sales_Model_Order  $order */
                $order = $payment->getOrder();
                $payment->setTransactionId($respondCharge->getId());
                $rawInfo = $respondCharge->toArray();
                $payment->setAdditionalInformation('rawrespond',$rawInfo);
                $payment->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,$rawInfo);
                $orderStatus = $this->getConfigData('order_status');
                $order->setState($orderStatus ,false );

                $order->addStatusToHistory($orderStatus, $messageSuccess.$respondCharge->getId()
                    .' and respond code '.$respondCharge->getResponseCode(), false);
                $order->save();
                $payment->save();

                return $respondCharge;
            }

        } else {

            Mage::throwException(Mage::helper('payment')->__( $respondCharge->getExceptionState()->getErrorMessage() ));

        }

        return false;

    }
    /**
     * @param Varien_Object $payment
     * @param $amount
     * @param array $extraConfig
     * @return mixed
     */


    protected function _createCharge(Varien_Object $payment,$amount,$extraConfig = array())
    {
        $config = array();
        $scretKey = $this->getConfigData('privatekey');
        $order = $payment->getOrder();
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getBillingAddress();
        $orderedItems = $order->getAllItems();
        $currencyDesc = $order->getBaseCurrencyCode();
        $orderId = $order->getIncrementId();
        $amountCents = (int)$amount*100;
        $street = Mage::helper('customer/address')
            ->convertStreetLines($billingAddress->getStreet(), 2);
        $billingAddressConfig = array(
                                        'addressLine1'       =>  $street[0],
                                        'addressLine2'       =>  $street[1],
                                        'addressPostcode'    =>  $billingAddress->getPostcode(),
                                        'addressCountry'     =>  $billingAddress->getCountry(),
                                        'addressCity'        =>  $billingAddress->getCity(),
                                        'addressPhone'       =>  $billingAddress->getTelephone(),

                                     );

        $street = Mage::helper('customer/address')
            ->convertStreetLines($shippingAddress->getStreet(), 2);
        $shippingAddressConfig = array(
                                        'addressLine1'       =>  $street[0],
                                        'addressLine2'       =>  $street[1],
                                        'addressPostcode'    =>  $shippingAddress->getPostcode(),
                                        'addressCountry'     =>  $shippingAddress->getCountry(),
                                        'addressCity'        =>  $shippingAddress->getCity(),
                                        'addressPhone'       =>  $shippingAddress->getTelephone(),
                                        'recipientName'      =>  $shippingAddress->getFirstname(). ' '.$shippingAddress->getLastname()

                                     );

        $products = array();
        foreach ($orderedItems as $item ) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $products[] = array (
                            'name'       =>     $item->getName(),
                            'sku'        =>     $item->getSku(),
                            'price'      =>     $item->getPrice()*100,
                            'quantity'   =>     $item->getQtyOrdered(),
                            'image'      =>     Mage::helper('catalog/image')->init($product, 'image')->__toString()
                         );
        }

        $config = array();
        $config['authorization'] = $scretKey  ;
        $config['mode'] = $this->getConfigData('mode');
        $config['timeout'] = $this->getConfigData('timeout');

        $config['postedParam'] = array (
            'amount'           =>    $amountCents,
            'currency'         =>    $currencyDesc,
            'shippingDetails'  =>    $shippingAddressConfig,
            'products'         =>    $products,
            'description'      =>    "Order number::$orderId",
            'card'             =>     array (
                                            'billingDetails'   =>    $billingAddressConfig

                                         )
        );

        $config['postedParam'] = array_merge($config['postedParam'],$extraConfig);
        return $config;

    }

    /**
     * Capture payment abstract method
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return CheckoutApi_ChargePayment_Model_Method_Creditcard
     */
    public function capture(Varien_Object $payment, $amount)
    {
        if (!$this->canCapture()) {
            Mage::throwException(Mage::helper('payment')->__('Capture action is not available.'));

        }else {
            $lastTransactionId =  $payment->getLastTransId();
            $lastTransactionIdCapture =  $payment->getTransactionId();


            if($lastTransactionId.'-capture' == $lastTransactionIdCapture ) {
                return $this;
            }

            $extraConfig = array (
                'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_CAPTURE,
                'autoCapTime' => $this->getConfigData('auto_capture_time')
            );

            $this->_placeOrder($payment, $amount, "Payment has been successfully captured for Transaction ",$extraConfig);
        }

        return $this;
    }

    /**
     * Authorize payment abstract method
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return CheckoutApi_ChargePayment_Model_Method_Creditcard
     */

    public function authorize(Varien_Object $payment, $amount)
    {

        if (!$this->canAuthorize()) {

            Mage::throwException(Mage::helper('payment')->__('Authorize action is not available.'));
        }else {

            $extraConfig = array (
                'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_AUTH,
                'autoCapTime' => 0
            );

            $this->_placeOrder($payment,$amount, "Payment has been successfully authorize for Transaction ",$extraConfig);
        }

        return $this;
    }




}