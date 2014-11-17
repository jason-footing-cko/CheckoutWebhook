<?php
class CheckoutApi_ChargePayment_Model_Observer
{
    public function  salesOrderPaymentPlaceStart(Varien_Event_Observer $observer)
    {

    }

    public function adminhtmlWidgetContainerHtmlBefore(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View) {
            $order = $block->getOrder();
            $payment = $order->getPayment();
            $tnxType  = $order->getPayment()->getTransaction($order->getPayment()->getLastTransId())->getTxnType();

                //Mage::getModel('sales/order_payment_transaction')
            if ($payment->getMethodInstance() instanceof CheckoutApi_ChargePayment_Model_Method_Creditcard
                || $payment->getMethodInstance() instanceof CheckoutApi_ChargePayment_Model_Method_Creditcardpci) {
                $rawrespond = $payment->getAdditionalInformation('rawrespond');

                if( $tnxType!=Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE && $order->getStatus()!= 'canceled') {
                    $message = Mage::helper('checkoutapi_chargePayment')->__('Are you sure you want to do caputre this transaction?');
                    $block->addButton('checkoutapi_capture', array(
                        'label'     => Mage::helper('checkoutapi_chargePayment')->__('Capture payment'),
                        'onclick'   => "confirmSetLocation('{$message}', '{$block->getUrl('*/chargeProcess/capture')}')",

                    ));

                    $message = Mage::helper('checkoutapi_chargePayment')->__('Are you sure you void this transaction?');
                    $block->addButton('checkoutapi_void', array(
                        'label'     => Mage::helper('checkoutapi_chargePayment')->__('Void  payment'),
                        'onclick'   => "confirmSetLocation('{$message}', '{$block->getUrl('*/chargeProcess/void')}')",

                    ));
                }
            }


        }
    }
}

