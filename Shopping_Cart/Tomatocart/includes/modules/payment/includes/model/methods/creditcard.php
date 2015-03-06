<?php
class model_methods_creditcard extends model_methods_Abstract
{
    private $_paymentToken = null;
    private  $error  = null;

    public function confirmation($obj)
    {
        $paymentToken =  $_POST['cko_cc_paymentToken'];
        $selection = array(
            'id'     => $obj->_code,
            'title'  => $obj->_method_title,
            'fields' => array(
                array(
                    'title' => '',
                    'field' => osc_draw_input_field('cko_cc_paymentToken',$paymentToken ,'id="cko-paymentToken"
                        style="display:none"')
                     )
                )
            );

        return $selection;
    }

    public function selection($obj)
    {
        $paymentToken =  $this->getPaymentToken();
        $selection = array('id' => $obj->_code,
            'module' => $obj->_method_title,
            'fields' => array(
                array(
                    'title' => '',
                    'field' => '<div class="widget-container"></div>'.
                        osc_draw_input_field('cko_cc_paymentToken',$paymentToken ,'id="cko-cc-paymentToken"
                        style="display:none"')

                )
            )
        );

        return $selection;
    }

    public function getJavascriptBlock($obj)
    {
        global $osC_Customer, $osC_Currencies, $osC_ShoppingCart;
        $amountCents = (int)$osC_Currencies->formatRaw($osC_ShoppingCart->getTotal(),$osC_Currencies->getCode())*100;
        $publicKey = MODULE_PAYMENT_CHECKOUTAPIPAYMENT_PUBLISHABLE_KEY;
        $paymentToken =  $this->getPaymentToken();
        $url = '';

        $content =
            <<<EOD


            window.CKOConfig = {
                publicKey: "{$publicKey}",
                 renderMode: 2,
                 namespace: 'CheckoutIntegration',
                 customerEmail: '{$osC_Customer->getEmailAddress()}' ,
                 customerName: '{$osC_ShoppingCart->getBillingAddress('firstname')} {$osC_ShoppingCart->getBillingAddress('lastname')}',
                 paymentToken:'{$paymentToken}',
                 value: "{$amountCents}",
                 currency: "{$osC_Currencies->getCode() }",
                 widgetContainerSelector: '.widget-container',
                 ready:function(event){

                if (typeof s_ajaxListener == 'undefined') {
                  var s_ajaxListener = new Object();
                  // Added for IE support
                  if (typeof XMLHttpRequest === 'undefined') {
                    XMLHttpRequest = function () {
                      try {
                        return new ActiveXObject('Msxml2.XMLHTTP.6.0');
                      }
                      catch (e) {
                      }
                      try {
                        return new ActiveXObject('Msxml2.XMLHTTP.3.0');
                      }
                      catch (e) {
                      }
                      try {
                        return new ActiveXObject('Microsoft.XMLHTTP');
                      }
                      catch (e) {
                      }
                      throw new Error('This browser does not support XMLHttpRequest.');
                    };
                  }
                  s_ajaxListener.tempOpen = XMLHttpRequest.prototype.open;
                  s_ajaxListener.tempSend = XMLHttpRequest.prototype.send;
                  s_ajaxListener.callback = function () {
                    // this.method :the ajax method used
                    // this.url    :the url of the requested script (including query string, if any) (urlencoded)
                    // this.data   :the data sent, if any ex: foo=bar&a=b (urlencoded)
                    var queryArray = this.data.split('&'),
                        subArray   = new Array(),
                        tempHolder = '',
                        isPaymentCheckoutApiPayment = false,
                        isPaymentprocess = false;

                    for(index in queryArray) {
                      if(queryArray.hasOwnProperty(index)) {
                       tempHolder = queryArray[index].split('=');
                        if(tempHolder[0] == 'action' ) {
                            if( tempHolder[1] == 'save_payment_method' ) {
                              isPaymentprocess = true;
                            }else {
                              isPaymentprocess = false;

                            }

                        }

                        if(tempHolder[0] == 'payment_method' ) {
                            if( tempHolder[1] == 'checkoutapipayment' ) {
                              isPaymentCheckoutApiPayment = true;
                            }else {
                              isPaymentCheckoutApiPayment = false;

                            }

                        }
                      }
                    }

                    if(isPaymentCheckoutApiPayment && isPaymentprocess ) {
                         var callbackT = setInterval(function() {
                             if($$('#btnConfirmOrder').length){
                              clearInterval(callbackT);
                              $$('[name^=checkout_confirmation]')[0].onsubmit = function(){
                               Checkout.open();
                               return false;
                              }

                                }
                        }, 100);

                    }
                  }

                  XMLHttpRequest.prototype.open = function (a, b) {
                    if (!a) var a = '';
                    if (!b) var b = '';
                    s_ajaxListener.tempOpen.apply(this, arguments);
                    s_ajaxListener.method = a;
                    s_ajaxListener.url = b;
                    if (a.toLowerCase() == 'get') {
                      s_ajaxListener.data = b.split('?');
                      s_ajaxListener.data = s_ajaxListener.data[1];
                    }
                  }
                  XMLHttpRequest.prototype.send = function (a, b) {
                    if (!a) var a = '';
                    if (!b) var b = '';
                    s_ajaxListener.tempSend.apply(this, arguments);
                    if (s_ajaxListener.method.toLowerCase() == 'post') s_ajaxListener.data = a;
                    s_ajaxListener.callback();
                  }
                }

                 },
                  cardCharged: function(event){
                     $$('[name^=checkout_confirmation]')[0].submit();
                }
           }

EOD;

        $toreturn = "} ; function checkoutRender() {
            Checkout.render($content);
            };
            function loadExtScript(src, test, callback) {
                if( !document.getElementById('checkoutapi')) {
                    var s = document.createElement('script');
                    s.src = src;
                    s.id = 'checkoutapi';

                    s.async = 'true';
                   document.body.appendChild(s);
                }
                var callbackTimer = setInterval(function() {
                   if(Checkout.hasOwnProperty('render')){
                       clearInterval(callbackTimer);
                       checkoutRender();
                   }
                }, 100);
        }
        loadExtScript('http://ckofe.com/js/checkout.js','',function(){});".$this->hijackedJs()."function
        dummy(){";
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

        $this->_placeorder($config);
    }





    private  function _verifyData()
    {
        global $osC_Language,$messageStack;
        $error = false;
        $errorMsg = $osC_Language->get('payment_checkoutapipayment_js_token');
        if(( !isset($_POST['cko_cc_paymentToken']))){

            $error = true;
        }

        if($error){
            $messageStack->add_session('checkout_payment',  $this->error , 'error');
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
                '  }' . "\n\n ";
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

    private function setPaymentToken()
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

            $this->error = $error_message;

        }
        return  $this->_paymentToken = $paymentToken;

    }

    public function getPaymentToken()
    {
        if(!$this->_paymentToken) {
            $this->setPaymentToken();
        }

        return $this->_paymentToken;
    }

    protected function _createCharge($config)
    {
        $Api = CheckoutApi_Api::getApi(array('mode'=> MODULE_PAYMENT_CHECKOUTAPIPAYMENT_GATEWAY_SERVER));
        $config['paymentToken'] = $_POST['cko_cc_paymentToken'];

        return $Api->verifyChargePaymentToken($config);
    }


}

