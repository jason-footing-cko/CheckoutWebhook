<?php
class CheckoutApi_ChargePayment_IndexController extends Mage_Core_Controller_Front_Action
{
	private $_code    =    null;

	private function _process()
	{
		$config['chargeId']    =    $this->getRequest()->getParam('chargeId');
		$config['authorization']    =    $this->_requesttConfigData('privatekey');
		$Api    =    CheckoutApi_Api::getApi(array('mode'=>$this->_requesttConfigData('mode')));
		$respondBody =    $Api->getCharge($config);
		$json = $respondBody->getRawOutput();
		return $json;
	}


	public function processAction()
	{
		if($this->getRequest()->getParam('chargeId')) {
			$stringCharge = $this->_process();
		}else {
			$stringCharge = file_get_contents("php://input");
		}



		if($stringCharge) {

			$Api    =    CheckoutApi_Api::getApi(array('mode'=>$this->_requesttConfigData('mode')));
			$objectCharge = $Api->chargeToObj($stringCharge);

			if($chargeId = $objectCharge->getId()) {
				/** @var Mage_Sales_Model_Resource_Order_Payment_Transaction  $transactionObject */
				$transactionObject = Mage::getModel('sales/order_payment_transaction')
					->load($chargeId,'txn_id');

				if($orderId = $transactionObject->getOrderId()) {

					$_order = Mage::getModel('sales/order')->load($orderId);
					$_payment = $_order->getPayment();
					$chargeIdPayment = preg_replace('/\-capture$/','',$_payment->getLastTransId());
					$chargeIdPayment = preg_replace('/\-void$/','',$chargeIdPayment);

					$this->setCode($_payment->getMethod());

					if($chargeIdPayment == $chargeId) {

						if($objectCharge->getCaptured() && $_order->getStatus()!=
							'canceled') {
							$transactionCapture = Mage::getModel('sales/order_payment_transaction')
								->load($chargeId.'-'.Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,'txn_id');


							if(!$transactionCapture->getOrderId()) {
								/** @var Mage_Sales_Model_Order_Payment $_payment  */

								$_payment->setParentTransactionId($chargeId);
								$_payment->capture ( null );
								$orderStatus = $this->_requesttConfigData ( 'order_status_capture' );
								$_rawInfo = $objectCharge->toArray ();

								$_payment->setAdditionalInformation ( 'rawrespond' , $_rawInfo )
										->setShouldCloseParentTransaction('Completed' === $orderStatus)
										->setIsTransactionClosed(0)
											->setTransactionAdditionalInfo ( Mage_Sales_Model_Order_Payment_Transaction
											::RAW_DETAILS , $_rawInfo );
									$_payment->save();

								$_order->setStatus ( $orderStatus , false );
								$_order->addStatusToHistory ( $orderStatus , 'Payment Sucessfully captured
                                with Transaction ID ' . $objectCharge->getId () );
								$_order->save ();
								$this->getResponse()->setBody('Payment Sucessfully captured
                                with Transaction ID '.$objectCharge->getId());

							}else {

								$this->getResponse()->setBody('Payment was already captured
                                with Transaction ID '.$objectCharge->getId());
							}

						} elseif($objectCharge->getRefunded() ) {
//cancel order
						}elseif($objectCharge->getVoided() || $objectCharge->getExpired()) {

							$transactionVoid = Mage::getModel('sales/order_payment_transaction')
								->load($chargeId.'-'.Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID,'txn_id');
							if(!$transactionVoid->getOrderId()) {
								$_voidObj = new Varien_Object();
								$_order->getPayment ()
									->setTransactionId ( null )
									->setParentTransactionId ( $objectCharge->getId () )
									->void ( new Varien_Object() );
								$_order->registerCancellation ( 'Transaction has been void' )
									->save ();
								$_rawInfo = $objectCharge->toArray ();
								$_payment->setAdditionalInformation ( 'rawrespond' , $_rawInfo );
								$_payment->setTransactionAdditionalInfo (
									Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS , $_rawInfo );
								$_payment->setTransactionId ( $objectCharge->getId () );

								$_payment
									->setIsTransactionClosed ( 1 )
									->setShouldCloseParentTransaction ( 1 );

								$_payment->addTransaction ( Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID ,
									null , false , 'Transaction has been void' );
								$_payment->void ($_voidObj);
								$_payment->unsLastTransId ();
								$this->getResponse()->setBody('Payment void
                                with Transaction ID '.$objectCharge->getId());
							}else {

								$this->getResponse()->setBody('Payment already void
                                with Transaction ID '.$objectCharge->getId());
							}
						}


					}

				}


			}



		} else {
			Mage::throwException ( Mage::helper ( 'payment' )->__ ( 'Fail: No Charge object posted' ));

		}
	}

	public function getCode()
	{
		if(!$this->_code) {
			$this->setCode();
		}
		return $this->_code;
	}

	private function _requesttConfigData($field, $storeId = null)
	{
		if (null === $storeId) {
			$storeId = Mage::app()->getStore()->getId();
		}

		$path = 'payment/'.$this->getCode().'/'.$field;
		return Mage::getStoreConfig($path, $storeId);
	}

	private function setCode($code = '')
	{
		if(!$code) {
			$storeId = Mage::app ()->getStore ()->getId ();
			if ( Mage::getStoreConfig ( 'payment/creditcard/active' , $storeId ) ) {
				$this->_code = 'creditcard';

			} elseif ( Mage::getStoreConfig ( 'payment/creditcardpci/active' , $storeId ) ) {
				$this->_code = 'creditcardpci';
			}
		}else {

			$this->_code = $code;
		}
	}



}