<?php
/*
Plugin Name: Checkout.com Payment Gateway (GW 3.0) 
Description: Add Checkout.com Payment Gateway (GW 3.0) for WooCommerce. 
Version: 1.0.0
Author: Checkout Integration Team
Author URI: http://www.checkout.com
*/

require_once 'autoload.php';

//include ("models/Checkoutapi.php");

add_action( 'plugins_loaded', 'checkoutapipayment_init', 0);

DEFINE ('PLUGIN_DIR', plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) . '/' );

function checkoutapipayment_init(){

	function add_checkoutapipayment_gateway( $methods ) {
		$methods[] = 'WC_Gateway_checkoutapipayment'; 
		return $methods;
	}

	add_filter( 'woocommerce_payment_gateways', 'add_checkoutapipayment_gateway' );

	
	class WC_Gateway_checkoutapipayment extends models_Checkoutapi
	{

	    protected $_methodType;
    	protected $_methodInstance;

    	public function __construct()
	    {
    		parent::__construct();
    	}


	    public function _initCode()
	    {
	        $this->_code = $this->_methodInstance->getCode();
	    }

	    public function admin_options()
	    {
	    	parent::admin_options();
	    }
	
		public function init_form_fields()
		{
			parent::init_form_fields();
		}

		public function payment_fields()
		{
			return parent::payment_fields();
		}

		public function process_payment($order_id)
		{
			return parent::process_payment($order_id);
		}
	}

}



	
