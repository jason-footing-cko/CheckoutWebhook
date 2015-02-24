<?php
/*
Plugin Name: Checkout.com Payment Gateway (GW 3.0) 
Description: Add Checkout.com Payment Gateway (GW 3.0) for WooCommerce. 
Version: 1.0.0
Author: Checkout Integration Team
Author URI: http://www.checkout.com
*/

require_once 'autoload.php';

add_action( 'plugins_loaded', 'checkoutapipayment_init', 0);

DEFINE ('PLUGIN_DIR', plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) . '/' );

function checkoutapipayment_init()
{
    function add_checkoutapipayment_gateway( $methods ) {
        $methods[] = 'checkoutapipayment';
        return $methods;
    }
    add_filter( 'jigoshop_payment_gateways', 'add_checkoutapipayment_gateway' );

    class checkoutapipayment extends models_Checkoutapi
    {
        protected $_methodType;
        protected $_methodInstance;

        public function _initCode()
        {
            $this->_code = $this->_methodInstance->getCode();
        }

        public function __construct(){
            parent::__construct();
        }

        public function payment_fields(){
            return parent::payment_fields();
        }

        public function process_payment($order_id){
            return parent::process_payment($order_id);
        }
    }

}