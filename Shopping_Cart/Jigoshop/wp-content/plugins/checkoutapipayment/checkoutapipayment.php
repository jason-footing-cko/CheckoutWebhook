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

        public function init_form_fields()
        {
            parent::init_form_fields();
        }

        public function __construct()
        {
            add_action ( 'valid-checkoutapipayment-webhook' , array ( $this , 'valid_webhook' ) );
            parent::__construct();
        }

        public function payment_fields(){
            return parent::payment_fields();
        }

        public function process_payment($order_id){
            return parent::process_payment($order_id);
        }

        public function valid_webhook()
        {

            die('here');

            $stringCharge = file_get_contents("php://input");
            $Api = CheckoutApi_Api::getApi ( array ( 'mode' => $this->checkoutapipayment_endpoint ) );

            $objectCharge = $Api->chargeToObj ( $stringCharge );

            if ( $objectCharge->getResponseCode () == '10000' ) {
                //  $this->load->model('sale/order');
                /*
                    * Need to get track id
                    */
                $order_id = $objectCharge->getMetadata ()->getTrackId ();

               // $modelOrder = wc_get_order ( $order_id );
                $modelOrder = get_jigoshop_view_order($order_id);

//                echo '<pre>';
//                print_r($modelOrder);
//                die();

                if ( $objectCharge->getCaptured () && !$objectCharge->getRefunded () ) {
                    if($modelOrder->get_status() !='completed' && $modelOrder->get_status() !='cancel') {

                        $modelOrder->update_status ( 'wc-completed' , __ ( 'Order status changed by webhook' , 'woocommerce'
                        ) );
                        echo "Order has been captured";
                    }else {
                        echo "Order has already been captured";
                    }

                } elseif ( $objectCharge->getCaptured () && $objectCharge->getRefunded () ) {
                    if( $modelOrder->get_status() !='cancel') {
                        $modelOrder->update_status ( 'wc-refunded' , __ ( 'Order status changed by webhook' , 'woocommerce' ) );
                        echo "Order has been refunded";


                    }else {
                        echo "Order has already been refunded";
                    }

                } elseif ( !$objectCharge->getCaptured () && $objectCharge->getRefunded () ) {

                    if( $modelOrder->get_status() !='cancel') {
                        $modelOrder->update_status ( 'wc-cancelled' , __ ( 'Order status changed by webhook:' , 'woocommerce' ) );
                        $modelOrder->cancel_order();
                        echo "Order has been cancel";
                    }

                }else {
                    echo "Order has already been cancel";
                }
            }
            exit();
        }

//        private function _process ()
//        {
//            $config[ 'chargeId' ] = $_GET[ 'chargeId' ];
//            $config[ 'authorization' ] = $this->checkoutapipayment_secretkey;
//            $Api = CheckoutApi_Api::getApi ( array ( 'mode' => $this->checkoutapipayment_endpoint ) );
//            $respondBody = $Api->getCharge ( $config );
//
//            $json = $respondBody->getRawOutput ();
//            return $json;
//        }


    }

    function jigoshop_checkoutapipayment_webhook()
    {
        //http://localhost:8080/wordpress-4.1/?checkoutapipaymentListener=valid-checkoutapipayment-webhook
        //$notify_url = add_query_arg( 'js-api', 'valid_webhook', home_url( '/' ) );

        if ( !empty( $_GET[ 'checkoutapipaymentListener' ] ) && $_GET[ 'checkoutapipaymentListener' ] ==
            'checkoutapi_payment_Listener'
        ) {

            //die('here');
            jigoshop_payment_gateways::payment_gateways();

            do_action ( 'valid-checkoutapipayment-webhook' );
        }
    }

    add_action ( 'init' , 'jigoshop_checkoutapipayment_webhook' );

}