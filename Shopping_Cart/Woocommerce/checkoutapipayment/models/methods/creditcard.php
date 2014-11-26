<?php
 class models_methods_creditcard extends models_methods_Abstract{

 	protected $_code = 'creditcard';

 	public function __construct(){
 		$this ->id = 'checkoutapipayment';
 		$this->has_fields = true;
 		$this->checkoutapipayment_ispci = 'no';
 		parent::__construct();
 	}

 	public function _initCode(){

 	}

 	public function payment_fields(){
        global $woocommerce;;
        $grand_total = (float) WC()->cart->total;
        $amount = (int)$grand_total*100;
        $current_user = wp_get_current_user();

        $email = "Email@youremail.com";
        if(isset($current_user->data)){
            $email = $current_user->user_email;
        }


            
 	?>
 	<p>Please select a Credit / Debit card</p>
    <div class="widget-container"></div>
    <div class="content" id="payment">
    <input type="hidden" name="cko_cc_token" id="cko-cc-token" value="">
    <input type="hidden" name="cko_cc_email" id="cko-cc-email" value="" />

        <script type="text/javascript">
        function checkoutRender() {
            Checkout.render({
                publicKey: "<?php echo CHECKOUTAPI_PUBLIC_KEY ?>",
                userEmail: "<?php echo $email ?>",
                value: "<?php echo $amount ?>",
                currency: "<?php echo get_woocommerce_currency() ?>",
                widgetContainerSelector: '.widget-container',
                widgetRendered: function (event) {
					if (jQuery('.cko-pay-now')) {
					 	jQuery('.cko-pay-now').hide();
					 }
                },
                cardTokenReceived: function (event) {
                    document.getElementById('cko-cc-token').value = event.data.cardToken;
                    document.getElementById('cko-cc-email').value = event.data.email;
                }
            });

        }
        function loadExtScript(src, test, callback) {
            var s = document.createElement('script');
            s.src = src;
            document.body.appendChild(s);

            var callbackTimer = setInterval(function() {

               if(typeof Checkout!='undefined'){
                   clearInterval(callbackTimer);
                   checkoutRender();
               }
            }, 100);
        }

    loadExtScript('http://ckofe.com/js/Checkout.js',checkoutRender,function(){})
        
    </script>


	<?php

 	}

 	public function process_payment($order_id){
        global $woocommerce;
        $order = new WC_Order( $order_id );
        $grand_total = $order->order_total;
        $amount = (int)$grand_total*100;
        $config['authorization'] = CHECKOUTAPI_SECRET_KEY;
        $config['mode'] = CHECKOUTAPI_ENDPOINT;
        $config['timeout'] = CHECKOUTAPI_TIMEOUT;
        $config['postedParam'] = array('email' =>parent::get_post('cko_cc_email'),
            'amount'=> $amount,
            'currency' => $order->order_currency,
            'description'=>"Order number::$order_id"
        );
        $extraConfig = array();
        if(CHECKOUTAPI_PAYMENTACTION == 'Capture'){
            $extraConfig = parent::_captureConfig();
        }
        else {
            $extraConfig= parent::_authorizeConfig();
        }

        $config['postedParam'] = array_merge($config['postedParam'],$extraConfig);
        $config['postedParam']['cardToken'] = parent::get_post('cko_cc_token');

        //var_dump($config);

        $respondCharge = parent::_createCharge($config);

        return parent::_validateChrage($order, $respondCharge);


 	}
 }


?>