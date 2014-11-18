<?php

class CheckoutapipaymentValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $cart = $this->context->cart;

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
            Tools::redirect('index.php?controller=order&step=1');

        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module)
            if ($module['name'] == 'checkoutapipayment')
            {
                $authorized = true;
                break;
            }

        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'validation'));
        }

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer))
            Tools::redirect('index.php?controller=order&step=1');

        $this->_placeorder();

//        Tools::redirect('index.php?controller=order-confirmation&id_cart='
//            .(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='
//            .$this->module->currentOrder.'&key='.$customer->secure_key);
    }

    public function _placeorder()
    {
        $cart = $this->context->cart;
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
        $currency = $this->context->currency;
        $customer = new Customer((int)$cart->id_customer);
        //building charge
        $respondCharge = $this->_createCharge();

        if( $respondCharge->isValid()) {

            if (preg_match('/^1[0-9]+$/', $respondCharge->getResponseCode())) {

                $order_state =( Configuration::get('CHECKOUTAPI_PAYMENT_ACTION') =='authorize_capture' &&
                $respondCharge->getCaptured())
                    ? Configuration::get('PS_OS_PAYMENT'):Configuration::get('PS_OS_CHECKOUT');


                $this->module->validateOrder((int)$cart->id, $order_state,
                    $total, $this->module->displayName, NULL, NULL, (int)$currency->id,
                    false, $customer->secure_key);

            } else {

                $this->module->validateOrder((int)$cart->id, Configuration::get('PS_OS_ERROR'),
                    $total, $this->module->displayName, $respondCharge->getResponseCode(), NULL, (int)$currency->id,
                    false, $customer->secure_key);

            }

            $dbLog = models_FactoryInstance::getInstance( 'models_DataLayer' );
            $dbLog->logCharge($this->module->currentOrder,$respondCharge->getId(),$respondCharge);

            Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?key='.$customer->secure_key.'&id_cart='
                .(int)$this->context->cart->id.'&id_module='.(int)$this->module->id.'&id_order='
                .(int)$this->module->currentOrder);
        } else  {

            throw new PrestaShopException($respondCharge->getExceptionState()->getErrorMessage());
        }

    }
    private function _createCharge()
    {
        $config = array();
        $cart = $this->context->cart;
        $currency = $this->context->currency;
        $customer = new Customer((int)$cart->id_customer);
        $invoiceAddress = new Address((int)$cart->id_address_invoice);
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);


        $scretKey =  Configuration::get('CHECKOUTAPI_SECRET_KEY');

        $orderId =(int)$cart->id;
        $amountCents = (int)$total*100;
        $config['authorization'] = $scretKey  ;

        $config['mode'] = Configuration::get('CHECKOUTAPI_TEST_MODE');
        $config['timeout'] =  Configuration::get('CHECKOUTAPI_GATEWAY_TIMEOUT');

        if(Configuration::get('CHECKOUTAPI_PAYMENT_ACTION') =='authorize_capture') {
            $config = array_merge($config, $this->_captureConfig());

        }else {

            $config = array_merge($config,$this->_authorizeConfig());
        }

        $config['postedParam'] = array_merge($config['postedParam'],array (
            'email'=>$customer->email ,
            'amount'=>$amountCents,
            'currency'=> $currency->iso_code,
            'description'=>"Order number::$orderId",
        ));


       return $this->module->getInstanceMethod()->createCharge($config,$cart);
    }


    private function _captureConfig()
    {
        $to_return['postedParam'] = array (
            'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_CAPTURE,
            'autoCapTime' => Configuration::get('CHECKOUTAPI_AUTOCAPTURE_DELAY')
        );

        return $to_return;
    }

    private function _authorizeConfig()
    {
        $to_return['postedParam'] = array (
            'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_AUTH,
            'autoCapTime' => 0
        );

        return $to_return;
    }

}