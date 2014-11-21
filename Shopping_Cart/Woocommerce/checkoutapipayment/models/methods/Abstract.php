<?php
	
abstract class models_methods_Abstract extends WC_Payment_Gateway implements models_InterfacePayment
{
	public function __construct(){
		
	}

	public function getCode(){
        return $this->_code;
    }

	public function admin_options(){}
	public function init_form_fields(){}
	public function payment_fields(){}
	public function process_payment($order_id){}


	protected function _createCharge($config)
    {

        $Api = CheckoutApi_Api::getApi();
        
        return $Api->createCharge($config);
    }

    protected function _validateChrage($order,$respondCharge)
    {


		if (preg_match('/^1[0-9]+$/', $respondCharge->getResponseCode())){

			$order->payment_complete( $respondCharge->getId() );

			$order->add_order_note( sprintf(__('Checkout.com Credit Card Payment Approved - ChargeID: %s with Response Code: %s', 'woocommerce'), 
				$respondCharge->getId(), $respondCharge->getResponseCode()));

			//WC()->cart->empty_cart();

			return array (
				  'result'   => 'success',
				  'redirect' => $this->get_return_url( $order )
			);

		}
		else {

			$order->add_order_note( sprintf(__('Checkout.com Credit Card Payment Declined - Error Code: %s, Decline Reason: %s', 'woocommerce'), 
				$respondCharge->getErrorCode(), $respondCharge->getMessage()));

			$error_message = 'The transaction was declined. Please check your Payment Details';
			wc_add_notice( __('Payment error: ', 'woothemes') . $error_message, 'error' );
			return;

			
		}

    }


}

?>