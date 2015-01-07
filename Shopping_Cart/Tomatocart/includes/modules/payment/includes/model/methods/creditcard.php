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
                    'field' => '<div class="widget-container"></div>'. osc_draw_input_field('cko_cc_token','' ,'id="payment_checkoutapipayment_cko-cc-token" style="display:none"').osc_draw_input_field('cko_cc_email','','id="payment_checkoutapipayment_cko-cc-email" style="display:none"')
                )
            )
        );

        return $selection;
    }

    public function getJavascriptBlock($obj)
    {
        global $osC_Customer, $osC_Currencies, $osC_ShoppingCart,$osC_Language;
        $amountCents = (int)$osC_Currencies->formatRaw($osC_ShoppingCart->getTotal(),$osC_Currencies->getCode())*100;
        $Api = CheckoutApi_Api::getApi(array('mode'=>MODULE_PAYMENT_CHECKOUTAPIPAYMENT_GATEWAY_SERVER));
        $config = array();
        $config['debug'] = false;
        $config['renderMode'] = 2;
        $config['publicKey']        = MODULE_PAYMENT_CHECKOUTAPIPAYMENT_PUBLISHABLE_KEY;
        $config['email']            =   $osC_Customer->getEmailAddress();
        $config['name']             =   $osC_ShoppingCart->getShippingAddress('firstname'). ' '.$osC_ShoppingCart->getShippingAddress('lastname');
        $config['amount']           =  $amountCents;
        $config['currency']         =  $osC_Currencies->getCode();
        $config['widgetSelector']   =  '.widget-container';
        $config['cardTokenReceivedEvent'] = "
                        document.getElementById('payment_checkoutapipayment_cko-cc-token').value = event.data.cardToken;
                        document.getElementById('payment_checkoutapipayment_cko-cc-email').value = event.data.email;
                         $('btnSavePaymentMethod').fireEvent('click');
                      ";
        $config['widgetRenderedEvent'] ="";
        $config['readyEvent'] = '';


        $jsConfig = $Api->getJsConfig($config);
        $toreturn = "} ; function checkoutRender() {
            Checkout.render($jsConfig);
            };
            function loadExtScript(src, test, callback) {
                if( !document.getElementById('checkoutapi')) {
                    var s = document.createElement('script');
                    s.src = src;
                    s.id = 'checkoutapi';;

                   document.body.appendChild(s);
                }

                var callbackTimer = setInterval(function() {

                   if(Checkout.hasOwnProperty('render')){
                       clearInterval(callbackTimer);
                       checkoutRender();
                   }
                }, 100);
        }

        loadExtScript('https://www.checkout.com/cdn/js/Checkout.js','',function(){});".$this->hijackedJs()."function dummy(){";



        return $toreturn;
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
        }else {
            if(isset($_POST['cko_cc_token']) && !(trim($_POST['cko_cc_token'])) ){
                $error = true;
            }

            if(isset($_POST['cko_cc_email']) && !(trim($_POST['cko_cc_email'])) ){
                $error = true;
            }
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

}

