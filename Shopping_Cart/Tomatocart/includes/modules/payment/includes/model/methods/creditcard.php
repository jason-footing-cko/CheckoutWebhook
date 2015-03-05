<?php
class model_methods_creditcard extends model_methods_Abstract
{


    public function confirmation($obj)
    {
        $selection = array('id' => $obj->_code,
            'title' => $obj->_method_title,
            'fields' => array(
                array(
                    'title' => '',
                    'field' =>  osc_draw_input_field('cko_cc_token',$_POST['cko_cc_token'] ,'id="payment_checkoutapipayment_cko-cc-token" style="display:none"')
                        .osc_draw_input_field('cko_cc_email',$_POST['cko_cc_email'],'id="payment_checkoutapipayment_cko-cc-email" style="display:none"')
                )
            )
        );

        return $selection;
    }

    public function selection($obj)
    {

        $selection = array('id' => $obj->_code,
            'module' => $obj->_method_title,
            'fields' => array(
                array(
                    'title' => '',
                    'field' => '<div class="widget-container"></div>'.
                        osc_draw_input_field('cko_cc_paymentToken','' ,'id="cko-cc-paymentToken"
                        style="display:none"')

                )
            )
        );

        return $selection;
    }

    public function getJavascriptBlock($obj)
    {
        global $order;
        $amount = (int)$order->info['total'];
        $amountCents = $amount *100;
        $email = $order->customer['email_address'];
        $currency = $order->info['currency'];
        $publicKey = MODULE_PAYMENT_CHECKOUTAPIPAYMENT_PUBLISHABLE_KEY;
        $paymentToken =  $this->getPaymentToken();
        $content =
            <<<EOD
	      
        <script src="http://ckofe.com/js/checkout.js" async ></script>
        <input type="hidden" name="cko-paymentToken" id="cko-paymentToken" value="{$paymentToken}" />
        <script type="text/javascript">

            window.CKOConfig = {
                publicKey: "{$publicKey}",
                 renderMode: 2,
                 customerEmail: '{$email}' ,
                 customerName: '{$order->customer['firstname']} {$order->customer['lastname']}',
                 paymentToken:'{$paymentToken}',
                 value: "{$amountCents}",
                 currency: "{$currency}",
                 widgetContainerSelector: '.widget-container'
           }

        </script>
EOD;



        return $content;

    }
    public function pre_confirmation_check()
    {
        $this->_verifyData();
    }
    public function process()
    {
        global  $osC_ShoppingCart, $osC_CreditCard;
        $this->_verifyData();
        $config = parent::process();
        $config['postedParam']['email'] = $_POST['cko_cc_email'];
        $config['postedParam']['cardToken'] =  $_POST['cko_cc_token'];

        $this->_placeorder($config);
    }





    private  function _verifyData()
    {
        global $osC_Language,$messageStack;
        $error = false;
        $errorMsg = $osC_Language->get('payment_checkoutapipayment_js_token');
        if(( !isset($_POST['cko_cc_token']) || !isset($_POST['cko_cc_email']))){

            $error = true;
        }

        if($error){
            $messageStack->add_session('checkout_payment', $errorMsg, 'error');
        }

    }

    public function hijackedJs()
    {
        global $osC_Database, $osC_Language;

        $Qmodules = $osC_Database->query('select code from :table_templates_boxes where modules_group = "payment"');
        $Qmodules->bindTable(':table_templates_boxes', TABLE_TEMPLATES_BOXES);
        $Qmodules->setCache('modules-payment');
        $Qmodules->execute();
        $modules = array();
        while ($Qmodules->next()) {
            $moduleCode = $Qmodules->value('code');
            $module_class = 'osC_Payment_' . $moduleCode;
            $modules[$moduleCode] = new $module_class();
        }

        $js = '';
        if (is_array($modules)) {
            $js = 'function check_form() {' . "\n" .
                '  var error = 0;' . "\n" .
                '  var error_message = "' . str_replace(array("\r", "\n"), "", $osC_Language->get('js_error')) . '";' . "\n" .
                '  var payment_value = null;' . "\n" .
                '  if (document.checkout_payment.payment_method.length) {' . "\n" .
                '    for (var i=0; i<document.checkout_payment.payment_method.length; i++) {' . "\n" .
                '      if (document.checkout_payment.payment_method[i].checked) {' . "\n" .
                '        payment_value = document.checkout_payment.payment_method[i].value;' . "\n" .
                '      }' . "\n" .
                '    }' . "\n" .
                '  } else if (document.checkout_payment.payment_method.checked) {' . "\n" .
                '    payment_value = document.checkout_payment.payment_method.value;' . "\n" .
                '  } else if (document.checkout_payment.payment_method.value) {' . "\n" .
                '    payment_value = document.checkout_payment.payment_method.value;' . "\n" .
                '  }' . "\n\n ".
                'if (payment_value=="checkoutapipayment"){
                    if(document.getElementById("payment_checkoutapipayment_cko-cc-token").value==""){
                       error_message = error_message + "' . str_replace(array("\r", "\n"), "", $osC_Language->get('payment_checkoutapipayment_js_token')) . '\n";'.

                   'return false; }
                   if(document.getElementById("payment_checkoutapipayment_cko-cc-email").value==""){
                       error_message = error_message + "' . str_replace(array("\r", "\n"), "", $osC_Language->get('payment_checkoutapipayment_js_token')) . '\n";'.

                '  return false; }
                }';
            foreach ($modules as $module) {
                if ( $module->isEnabled() && $module->_code!='checkoutapipayment') {
                    $js .= $module->getJavascriptBlock();
                }
            }
            $js .= "\n" . '  if (payment_value == null) {' . "\n" .
                '    error_message = error_message + "' . str_replace(array("\r", "\n"), "", $osC_Language->get('js_no_payment_module_selected')) . '\n";' . "\n" .
                '    error = 1;' . "\n" .
                '  }' . "\n\n" .
                '  if (error == 1) {' . "\n" .
                '    alert(error_message);' . "\n" .
                '    return false;' . "\n" .
                '  } else {' . "\n" .
                '    return true;' . "\n" .
                '  }' . "\n" .
                '}' ;
        }

        return $js;

    }

    private function getPaymentToken()
    {
        global $osC_Customer, $osC_Currencies, $osC_ShoppingCart,$messageStack;
        $amountCents = (int)$osC_Currencies->formatRaw($osC_ShoppingCart->getTotal(),$osC_Currencies->getCode())*100;
        $config['authorization'] = MODULE_PAYMENT_CHECKOUTAPIPAYMENT_SECRET_KEY;
        $Api = CheckoutApi_Api::getApi(array('mode'=> MODULE_PAYMENT_CHECKOUTAPIPAYMENT_GATEWAY_SERVER));
        $shippingAddressConfig = array(
            'addressLine1'       =>  $osC_ShoppingCart->getShippingAddress('street_address'),
            'postcode'           =>  $osC_ShoppingCart->getShippingAddress('postcode'),
            'country'            =>  $osC_ShoppingCart->getShippingAddress('country_title'),
            'city'               =>  $osC_ShoppingCart->getShippingAddress('city'),
            'phone'              =>  $osC_ShoppingCart->getShippingAddress('telephone_number'),
            'recipientName'      =>  $osC_ShoppingCart->getShippingAddress('firstname'). ' '.$osC_ShoppingCart->getShippingAddress('lastname')

        );
        $products = array();
        if ($osC_ShoppingCart->hasContents()) {
            $i = 1;
            foreach($osC_ShoppingCart->getProducts() as $product) {

                $products[] = array (
                    'name'       =>    $product['name'],
                    'sku'        =>    $product['sku'],
                    'price'      =>    $product['final_price'],
                    'quantity'   =>     $product['quantity'],

                );
                $i++;
            }
        }
        $this->_order_id     = osC_Order::insert();
        $config['postedParam'] = array (
            'email'            => $osC_Customer->getEmailAddress() ,
            'value'            => $amountCents,
            'shippingDetails'  => $shippingAddressConfig,
            'currency'         => $osC_Currencies->getCode() ,
            'products'         => $products,
            'metadata'         => array("trackId" => "{$this->_order_id}"),
            'billingDetails'   => array (
                'addressLine1'    => $osC_ShoppingCart->getBillingAddress('street_address'),
                'addressPostcode' => $osC_ShoppingCart->getBillingAddress('postcode'),
                'addressCountry'  => $osC_ShoppingCart->getBillingAddress('country_title'),
                'addressCity'     => $osC_ShoppingCart->getBillingAddress('city'),
                'addressPhone'    => $osC_ShoppingCart->getBillingAddress('telephone_number')
            )
        );

        if (MODULE_PAYMENT_CHECKOUTAPIPAYMENT_TRANSACTION_METHOD == 'Authorize and Capture') {
            $config = array_merge_recursive( $this->_captureConfig(),$config);
        } else {
            $config = array_merge_recursive( $this->_authorizeConfig(),$config);
        }

        $paymentTokenCharge = $Api->getPaymentToken($config);
        $paymentToken    =   '';

        if($paymentTokenCharge->isValid()){
            $paymentToken = $paymentTokenCharge->getId();
        }

        if(!$paymentToken) {
            $error_message = $paymentTokenCharge->getExceptionState()->getErrorMessage().
                ' ( '.$paymentTokenCharge->getEventId().')';

            $messageStack->add_session('checkout_payment', $error_message . '<!-- ['.$this->code.'] -->', 'error');

        }

        return $paymentToken;
    }
}

