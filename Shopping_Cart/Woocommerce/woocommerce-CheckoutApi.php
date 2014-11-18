<?php
/*
Plugin Name: Checkout.com Payment Gateway (GW 3.0) 
Description: Add Checkout.com Payment Gateway (GW 3.0) for WooCommerce. 
Version: 1.0.0
Author: Checkout Integration Team
Author URI: http://www.checkout.com
*/

function init_CheckoutApi(){

  function add_CheckoutApi_gateway_class( $methods ) {
	$methods[] = 'WC_Gateway_CheckoutApi'; 
	return $methods;
  }
  DEFINE ('PLUGIN_DIR', plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) . '/' );
  add_filter( 'woocommerce_payment_gateways', 'add_CheckoutApi_gateway_class' );
  if(class_exists('WC_Payment_Gateway')){
	class WC_Gateway_CheckoutApi extends WC_Payment_Gateway {
	  
	  public function __construct(){
		$this->id               = 'CheckoutApi';
		$this->icon             = apply_filters( 'woocommerce_CheckoutApi_icon', plugins_url( 'images/CheckoutLogo.png' , __FILE__ ) );
		$this->has_fields       = true;
		$this->method_title     = __( 'Credit Card (Checkout.com)', 'woocommerce' );		
		$this->init_form_fields();
		$this->init_settings();
		$this->title              	  = $this->get_option( 'title' );
		$this->CheckoutApi_secretkey    = $this->get_option( 'CheckoutApi_secretkey' );
		$this->CheckoutApi_publickey  = $this->get_option( 'CheckoutApi_publickey' );
		$this->CheckoutApi_paymentaction = $this -> get_option ('CheckoutApi_paymentaction');
		$this->CheckoutApi_cardtype = $this -> get_option ('CheckoutApi_cardtype');
		$this->CheckoutApi_autoCaptime = $this -> get_option ('CheckoutApi_autoCaptime');
		$this->CheckoutApi_timeout = $this -> get_option ('CheckoutApi_timeout');
		$this->CheckoutApi_endpoint = $this -> get_option ('CheckoutApi_endpoint');
		$this->CheckoutApi_ispci = $this -> get_option ('CheckoutApi_ispci');

		define("CHECKOUTAPI_SECRET_KEY", $this->CheckoutApi_secretkey); 
		define("CHECKOUTAPI_PUBLIC_KEY", $this->CheckoutApi_publickey);
		define("CHECKOUTAPI_PAYMENTACTION", $this ->CheckoutApi_paymentaction);
		define("CHECKOUTAPI_AUTOCAPTIME", $this->CheckoutApi_autoCaptime );
		define("CHECKOUTAPI_TIMEOUT", $this->CheckoutApi_timeout);
		define("CHECKOUTAPI_ENDPOINT", $this->CheckoutApi_endpoint);
		define("CHECKOUTAPI_ISPCI", $this->CheckoutApi_ispci);
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	  }
	  
	  public function admin_options(){
		?>
		<h3><?php _e( 'Credit Card (Checkout.com)', 'woocommerce' ); ?></h3>
		<p><?php _e( 'Credit Card payment offered by Checkout.com.', 'woocommerce' ); ?></p>
		<table class="form-table">
			  <?php $this->generate_settings_html(); ?>
		</table>
		<?php
	  }
	  
	  public function init_form_fields(){
		$this->form_fields = array(
			'enabled' => array(
			  'title' => __( 'Enable/Disable', 'woocommerce' ),
			  'type' => 'checkbox',
			  'label' => __( 'Enable Credit Card (Checkout.com) payment method', 'woocommerce' ),
			  'default' => 'yes'
			  ),
			'CheckoutApi_ispci' => array(
			  'title' => __( 'Is PCI Compliance?', 'woocommerce' ),
			  'type' => 'select',
			  'description' => __( 'Please select whether you are PCI Compliance', 'woocommerce' ),
			  'desc_tip'      => true,
			  'options'     => array(
				  'yes' => 'YES',
				  'no' => 'NO',
				  ),
			  'default'     => 'Development'
			  ),
			'title' => array(
			  'title' => __( 'Title', 'woocommerce' ),
			  'type' => 'text',
			  'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
			  'default' => __( 'Credit Card (Checkout.com)', 'woocommerce' ),
			  'desc_tip'      => true,
			  ),
			'CheckoutApi_secretkey' => array(
			  'title' => __( 'Secret Key', 'woocommerce' ),
			  'type' => 'text',
			  'description' => __( 'This is the Secret Key where you could find on Checkout Hub Settings.', 'woocommerce' ),
			  'default' => '',
			  'desc_tip'      => true,
			  'placeholder' => 'Your secret Key'
			  ),
			'CheckoutApi_publickey' => array(
			  'title' => __( 'Public Key', 'woocommerce' ),
			  'type' => 'text',
			  'description' => __( 'This is the Secret Key where you could find on Checkout Hub Settings.', 'woocommerce' ),
			  'default' => '',
			  'desc_tip'      => true,
			  'placeholder' => 'Your public Key'
			  ),
			'CheckoutApi_paymentaction' => array(
			  'title' => __( 'Payment Action', 'woocommerce' ),
			  'type' => 'select',
			  'description' => __( 'Select which payment action to use. Authorize Only will authorize the customers card for the purchase amount only.  Authorize &amp; Capture will authorize the customer\'s card and collect funds.', 'woothemes' ),
			  'options'     => array(
				  'Capture' => 'Authorize &amp; Capture',
				  'Auth' => 'Authorize Only',
				  ),
			  'default'     => 'Authorize &amp; Capture'
			  ),
			'CheckoutApi_cardtype' => array(
			  'title' => __( 'Credit Card Types', 'woocommerce' ),
			  'type' => 'multiselect',
			  'description' => __( 'Select the Credit Card Types','woocommerce' ),
			  'desc_tip'      => true,
			  'options'     => array(
				  'Visa' => 'VISA',
				  'MasterCard' => 'MasterCard',
				  'American Express' => 'Amercian Express',
				  'Discover' => 'Discover',
				  'Diners Club' => 'Diners Club',
				  'JCB' => 'JCB',
				  'Maestro' => 'Maestro/Switch',
				  'Other' => 'Other'
				  )
			  ),
			'CheckoutApi_autoCaptime' => array(
			  'title' => __( 'Auto Capture Time (Seconds)', 'woocommerce' ),
			  'type' => 'text',
			  'description' => __( 'This is the setting for when auto capture would occur after authorization', 'woocommerce' ),
			  'default' => '0',
			  'desc_tip'      => true
			  ),
			  
			'CheckoutApi_timeout' => array(
			  'title' => __( 'Timeout (Seconds)', 'woocommerce' ),
			  'type' => 'text',
			  'description' => __( 'This is the setting for time out value for a request to the gateway', 'woocommerce' ),
			  'default' => '60',
			  'desc_tip'      => true
			  ),
			
			'CheckoutApi_endpoint' => array(
			  'title' => __( 'Endpoint Mode URL', 'woocommerce' ),
			  'type' => 'select',
			  'description' => __( 'This is the setting for identifying the API URL used (dev/preprod/live)', 'woocommerce' ),
			  'desc_tip'      => true,
			  'options'     => array(
				  'dev' => 'Development',
				  'preprod' => 'Preprod',
				  'live' => 'Live' 
				  ),
			  'default'     => 'Development'
			)
			
		  );
	  }
	  
	  public function payment_fields(){
		$style = file_get_contents(PLUGIN_DIR.'css/style.css');
		echo '<style>'.$style. '</style>';
		?>
        <table>
            <tr>
              <td><label class="" for="CheckoutApi_cardtype"><?php echo __( 'Card Type', 'woocommerce') ?></label></td>
              <td>
                <select name="cardtype_CheckoutApi">
                  <?php foreach ($this->CheckoutApi_cardtype as $typeCode => $typeName):?>
					<option value="<?php echo $typeCode ?>"><?php echo $typeName ?></option>
				  <?php endforeach; ?>
                </select>  
              </td>
            </tr>
            <tr>
            	<td><label class="" for="CheckoutApi_cardno"><?php echo __( 'Card Number*', 'woocommerce') ?></label></td>
                <td><input type="text" name="CheckoutApi_cardnumber" class="input-text required-entry" /></td>
            </tr>
            <tr>
            	<td><label class="" for="CheckoutApi_date"><?php echo __( 'Expiration date', 'woocommerce') ?></label></td>
                <td>
                	<select name="CheckoutApi_expmonth" id="expmonth">
                      <option value=""><?php _e( 'Month', 'woocommerce' ) ?></option>
                      <option value='01'>01</option>
                      <option value='02'>02</option>
                      <option value='03'>03</option>
                      <option value='04'>04</option>
                      <option value='05'>05</option>
                      <option value='06'>06</option>
                      <option value='07'>07</option>
                      <option value='08'>08</option>
                      <option value='09'>09</option>
                      <option value='10'>10</option>
                      <option value='11'>11</option>
                      <option value='12'>12</option>  
                    </select>
                    <select name="CheckoutApi_expyear" id="expyear">
                      <option value=""><?php _e( 'Year', 'woocommerce' ) ?></option><?php
                      $years = array();
                      for ( $i = date( 'y' ); $i <= date( 'y' ) + 15; $i ++ ) {
                        printf( '<option value="20%u">20%u</option>', $i, $i );
                      } ?>
                    </select>
                </td>
            </tr>
            <tr>
            	<td><label class="" for="cardcvv"><?php echo __( 'Card CVV*', 'woocommerce') ?></label></td>
                <td><input type="text" name="CheckoutApi_cardcvv" class="input-text required-entry" /></td>
            </tr>
        </table>
		<?php
	  }
	  
	  public function process_payment( $order_id ){
		require_once 'autoload.php';
		global $woocommerce;
		$order = new WC_Order( $order_id );
		$grand_total = $order->order_total;
		$amount = (int)$grand_total*100;
		
		$Api = CheckoutApi_Api::getApi();
		$config = array();
		$config['authorization'] = CHECKOUTAPI_SECRET_KEY;
		$config['mode'] = CHECKOUTAPI_ENDPOINT;
		$config['timeout'] = CHECKOUTAPI_TIMEOUT;
		$config['postedParam'] = array('email' =>$order->billing_email,
			'amount'=> $amount,
			'currency' => $order->order_currency,
			'description'=>"Order number::$order_id"
		);
		$extraConfig = array();
		if(CHECKOUTAPI_PAYMENTACTION == 'Capture'){
			$extraConfig = array(
				'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_CAPTURE,
				'autoCapTime' => CHECKOUTAPI_AUTOCAPTIME
			);
		}
		if(CHECKOUTAPI_PAYMENTACTION == 'Auth'){
			$extraConfig = array(
				'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_AUTH,
				'autoCapTime' => 0
			);
		}
		
		$config['postedParam'] = array_merge($config['postedParam'],$extraConfig);
		$config['postedParam']['card'] = array(
			'name' => $order->billing_first_name .' '.$order->billing_last_name,
			'number' => $_POST['CheckoutApi_cardnumber'],
			'expiryMonth' => $_POST['CheckoutApi_expmonth'],
            'expiryYear' => $_POST['CheckoutApi_expyear'],
            'cvv' => $_POST['CheckoutApi_cardcvv'],
			'addressLine1' => $order->billing_address_1,
			'addressLine2' => $order->billing_address_2,
			'addressPostcode' => $order->billing_postcode,
			'addressCountry' => $order->billing_country,
			'addressCity'=>$order->billing_city,
			'addressState'=>$order->billing_state,
			'addressPhone' => $order->billing_phone
		);
		echo json_encode($config);
		$respondCharge = $Api->createCharge($config);
		echo CHECKOUTAPI_ENDPOINT;
		echo '<br/>';
		print_r ($respondCharge);
		
		die();
		
		if( $respondCharge->isValid()){
			 if (preg_match('/^1[0-9]+$/', $respondCharge->getResponseCode())) {
				
			 } else {
				
			 
			 }
		
		}
		
	
	   }
	}
  }
}

add_action( 'plugins_loaded', 'init_CheckoutApi' );
