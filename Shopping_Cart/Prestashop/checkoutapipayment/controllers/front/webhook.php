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
		$stringCharge = $this->_process();//file_get_contents("php://input");
		$Api    =    CheckoutApi_Api::getApi(array('mode' => Configuration::get('CHECKOUTAPI_TEST_MODE')));
		$objectCharge = $Api->chargeToObj($stringCharge);

		CheckoutApi_Utility_Utilities::dump($objectCharge);
		die('here');
	}


	private function _process()
	{
		$config['chargeId']    =    $_GET['chargeId'];
		$config['authorization']    =    Configuration::get('CHECKOUTAPI_SECRET_KEY');
		$Api    =    CheckoutApi_Api::getApi(array('mode' => Configuration::get('CHECKOUTAPI_TEST_MODE')));
		$respondBody    =    $Api->updateCharge($config);

		$json = $respondBody->getRawOutput();
		return $json;
	}
}