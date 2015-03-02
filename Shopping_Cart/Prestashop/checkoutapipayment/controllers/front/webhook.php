<?php
class CheckoutapipaymentWebhookModuleFrontController extends ModuleFrontController
{
	public $display_column_left = false;
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		$this->display_column_left = false;
		parent::initContent();
		$stringCharge     =    file_get_contents("php://input");
		$Api    =    CheckoutApi_Api::getApi(array('mode' => Configuration::get('CHECKOUTAPI_TEST_MODE')));
		$objectCharge = $Api->chargeToObj($stringCharge);
		$dbLog = models_FactoryInstance::getInstance( 'models_DataLayer' );
        $transaction = $dbLog->getOrderId($objectCharge->getId());
		$id_order = $transaction['id_order'];

		$order = new Order($id_order);
		$history = new OrderHistory();
		$history->id_order = $id_order;
		$current_order_state = $order->getCurrentOrderState();

		if($objectCharge->getCaptured() && !$objectCharge->getRefunded()  ) {
			if(!$order->hasBeenPaid()) {
				$order_state = new OrderState(Configuration::get('PS_OS_PAYMENT'));
				if (!Validate::isLoadedObject($order_state)) {
					echo sprintf(Tools::displayError('Order status #%d cannot be loaded'), Configuration::get('PS_OS_PAYMENT'));
				}else {
					$current_order_state = $order->getCurrentOrderState();
					if ($current_order_state->id == $order_state->id ) {
						echo  sprintf ( Tools::displayError ( 'Order #%d has already been captured.' ) ,
							$id_order);
					} else {

						$history->changeIdOrderState(Configuration::get('PS_OS_WS_PAYMENT'), (int)$id_order);

						$history->addWithemail();

						echo  sprintf ( Tools::displayError ( 'Order #%d has  been captured.' ) ,
							$id_order);
					}
				}

			} else {
				echo 'Payment was already captured
                                for Transaction ID '.$objectCharge->getId();
			}
		} elseif($objectCharge->getCaptured() && $objectCharge->getRefunded()) {
			$order_state = new OrderState(Configuration::get('PS_OS_REFUND'));
			if ($current_order_state->id == $order_state->id ) {
				echo  sprintf ( Tools::displayError ( 'Order #%d has already been refunded.' ) ,
					$id_order );
			}else {

				$history->changeIdOrderState ( Configuration::get ( 'PS_OS_REFUND' ) , (int)$id_order );

				$history->addWithemail ();
				echo  sprintf ( Tools::displayError ( 'Order #%d has  been refunded.' ) ,
					$id_order );
			}
		}elseif(!$objectCharge->getCaptured() && $objectCharge->getRefunded()) {
			$order_state = new OrderState(Configuration::get('PS_OS_CANCELED'));
			if ($current_order_state->id == $order_state->id ) {
				echo  sprintf ( Tools::displayError ( 'Order #%d has already been cancel.' ) ,
					$id_order );
			}else {

				$history->changeIdOrderState ( Configuration::get ( 'PS_OS_CANCELED' ) , (int)$id_order );
				$history->addWithemail ();
				echo  sprintf ( Tools::displayError ( 'Order #%d has  been cancel.' ) ,
					$id_order );
			}
		}
		CheckoutApi_Utility_Utilities::dump($order->hasBeenPaid());
		
	}


	private function _process()
	{
		$config['chargeId']    =    $_GET['chargeId'];
		$config['authorization']    =    Configuration::get('CHECKOUTAPI_SECRET_KEY');
		$Api    =    CheckoutApi_Api::getApi(array('mode' => Configuration::get('CHECKOUTAPI_TEST_MODE')));
		$respondBody    =    $Api->getCharge($config);

		$json = $respondBody->getRawOutput();
		return $json;
	}
}