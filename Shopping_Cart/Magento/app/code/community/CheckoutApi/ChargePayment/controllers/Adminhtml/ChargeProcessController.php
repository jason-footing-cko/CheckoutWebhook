<?php
class CheckoutApi_ChargePayment_Adminhtml_ChargeProcessController extends Mage_Adminhtml_Controller_Action
{

    protected $_code = 'creditcard';

    public function CaptureAction()
    {
        $_id = $this->getRequest()->getParam('order_id');
        /** @var Mage_Sales_Model_Order $_order */
        $_order = Mage::getModel('sales/order')->load($_id);

        $_payment = $_order->getPayment();
        $chargeId = preg_replace('/\-capture$/','',$_payment->getLastTransId());
        $_authorizeAmount = $_payment->getAmountAuthorized();
        /** @var CheckoutApi_Client_ClientGW3  $Api */
        $_Api = CheckoutApi_Api::getApi(array('mode'=>$this->getConfigData('mode')));
        $secretKey = $this->getConfigData('privatekey');

        $_config = array();
        $_config['authorization'] = $secretKey ;
        $_config['chargeId'] = $chargeId ;
        $_config['postedParam'] = array (
            'value'=>(int)($_authorizeAmount*100)
        );

        $_captureCharge = $_Api->captureCharge($_config);

        if($_captureCharge->isValid() && $_captureCharge->getCaptured() &&
            preg_match('/^1[0-9]+$/',$_captureCharge->getResponseCode()) ) {

            if ($_payment->getMethodInstance() instanceof CheckoutApi_ChargePayment_Model_Method_Abstract) {

                $_payment->capture(null);
                $_rawInfo = $_captureCharge->toArray();

                $_payment->setAdditionalInformation('rawrespond',$_rawInfo);
                $_payment->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,$_rawInfo);
                $orderStatus = $this->getConfigData('order_status_capture');

                $_order->setStatus($orderStatus ,false );

                $_order->addStatusToHistory($orderStatus, 'Payment Sucessfully captured
                  with Transaction ID '.$_captureCharge->getId());

                $_order->save();
                Mage::getSingleton('adminhtml/session')->addSuccess('Payment Sucessfully Placed
                  with Transaction ID '.$_captureCharge->getId());
            }


        } else {

            Mage::getSingleton('adminhtml/session')->addError($_captureCharge->getExceptionState()->getErrorMessage());

        }


     $this->_redirectReferer();
    }

    public  function  VoidAction()
    {
        $_id = $this->getRequest()->getParam('order_id');
        $_order = Mage::getModel('sales/order')->load($_id);

        $_payment = $_order->getPayment();
        $chargeId = preg_replace('/\-capture$/','',$_payment->getLastTransId());
        $_authorizeAmount = $_payment->getAmountAuthorized();
        /** @var CheckoutApi_Client_ClientGW3  $Api */

        $_Api = CheckoutApi_Api::getApi(array('mode'=>$this->getConfigData('mode')));
        $secretKey = $this->getConfigData('privatekey');
        $_config = array();
        $_config['authorization'] = $secretKey ;
        $_config['chargeId'] = $chargeId ;
        $_config['postedParam'] = array (
            'value'=>(int)($_authorizeAmount*100)
        );

        $_refundCharge = $_Api->refundCharge($_config);
        $_payment->void(
            new Varien_Object()
        );
        if($_refundCharge->isValid() && $_refundCharge->getRefunded() &&
            preg_match('/^1[0-9]+$/',$_refundCharge->getResponseCode())) {
            if ($_payment->getMethodInstance() instanceof CheckoutApi_ChargePayment_Model_Method_Creditcard) {
                $_voidObj =  new Varien_Object();
                $_id = $this->getRequest()->getParam('order_id');
                /** @var Mage_Sales_Model_Order $_order */
                $_order = Mage::getModel('sales/order')->load($_id);

                $_order->getPayment()
                    ->setTransactionId(null)
                    ->setParentTransactionId($_refundCharge->getId())
                    ->void( new Varien_Object());
            }
            $_order->registerCancellation('Transaction has been void')
                ->save();

                $_rawInfo = $_refundCharge->toArray();
                $_payment->setAdditionalInformation('rawrespond',$_rawInfo);
                $_payment->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,$_rawInfo);
                $_payment->setTransactionId($_refundCharge->getId());
                $_payment->addTransaction($_refundCharge->getId());
                $_payment
                    ->setIsTransactionClosed(1)
                    ->setShouldCloseParentTransaction(1);

                $_payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID, null, false , 'Transaction has been void');
                $_payment->void(
                    $_voidObj
                );
                $_payment->unsLastTransId();


        }else {

            Mage::getSingleton('adminhtml/session')->addError($_refundCharge->getExceptionState()->getErrorMessage());

        }

        $this->_redirectReferer();
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|Mage_Core_Model_Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = Mage::app()->getStore()->getStoreId();
        }
        $path = 'payment/'.$this->getCode().'/'.$field;
        return Mage::getStoreConfig($path, $storeId);
    }


    public  function getCode()
    {
        return $this->_code;
    }
} 