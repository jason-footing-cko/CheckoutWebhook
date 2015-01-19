<?php
class model_methods_creditcard extends model_methods_Abstract
{
    private $cko_cc_email ;
    private $cko_cc_token ;

    public function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0,&$htmlIn,$obj)
    {

        $toReturn = true;

        JHTML::script('vmcreditcard.js', 'components/com_virtuemart/assets/js/', FALSE);
        VmConfig::loadJLang('com_virtuemart', true);
        vmJsApi::jCreditCard();

        $currentMethod =  $obj->getCurrentMethod();
        $method_name = $obj->getPsType() . '_name';
        $methodSalesPrice = $obj->setCartPrices($cart, $cart->cartPrices, $currentMethod);

        $html = array();
        $obj->getInstance()->getC->$method_name = $obj->getRenderPluginName($currentMethod);
        $html[] = $obj->pluginHtml($currentMethod, $selected, $methodSalesPrice);
        if ($selected == $currentMethod->virtuemart_paymentmethod_id) {
            $this->_getSessionData();
        }
        $paymentId = $currentMethod->virtuemart_paymentmethod_id;

        $Api = CheckoutApi_Api::getApi(array('mode'=>$obj->getCurrentMethod()->sandbox));

        $amountCents = ceil((float)($cart->pricesUnformatted['billTotal'])*100.00);

        $cart_currency_code = ShopFunctions::getCurrencyByID ($cart->pricesCurrency, 'currency_code_3');
        $config = array();
        $config['debug'] = false;
        $config['renderMode'] = 2;
        $config['publicKey']        =   $obj->getCurrentMethod()->public_key;
        $config['email']            =   $cart->BT['email'];
        $config['name']             =   $cart->BT['first_name']. ' '.$cart->BT['last_name'];
        $config['amount']           =   $amountCents;
        $config['currency']         =   $cart_currency_code;
        $config['widgetSelector']   =  '.widget-container';
        $config['cardTokenReceivedEvent'] = "
                        document.getElementById('cko-cc-token').value = event.data.cardToken;
                        document.getElementById('cko-cc-email').value = event.data.email;

                      ";
        $config['widgetRenderedEvent'] = "";
        $config['readyEvent'] = '';


        $jsConfig = $Api->getJsConfig($config);

        $html[] = '<br/>';
        $html[] = '<span class="vmpayment_cardinfo">';
        $html[] =  vmText::_('VMPAYMENT_CHECKOUTAPIPAYMENT_COMPLETE_FORM');
        $html[] =  '<div class="widget-container"></div>';
        $html[] =  '<input type="hidden" name="cko_cc_token_'.$paymentId.'" id="cko-cc-token" value="'.$this->cko_cc_token.'">
            <input type="hidden" name="cko_cc_email_'.$paymentId.'" id="cko-cc-email" value="'.$this->cko_cc_email.'" />';
        $html[] =  '<script type="text/javascript">';
        $html[] =  $jsConfig;
        $html[] =  '</script>';
        $html[] =  '<script async src="https://www.checkout.com/cdn/js/Checkout.js"></script>';
        $html[] =  '</span>';

        $htmlIn[] = array(join("\n",$html));

        return $toReturn;
    }


    public function process(VirtueMartCart $cart, $order,$obj)
    {
        $this->_getSessionData();

        $config = parent::process($cart, $order,$obj);

        $config['postedParam']['email'] = $this->cko_cc_email;
        $config['postedParam']['cardToken'] =  $this->cko_cc_token;
        return $this->_placeorder($config,$obj,$order);
    }


    protected function _getSessionData()
    {

        $session = JFactory::getSession();
        $checkoutSession = $session->get('checkoutapipayment', 0, 'vm');

        if (!empty($checkoutSession)) {
            $sessiontData = (object)json_decode($checkoutSession,true);
            $this->cko_cc_email = $sessiontData->cko_cc_email;
            $this->cko_cc_token =  $sessiontData->cko_cc_token;
        }
    }

    public function sessionSave(VirtueMartCart $cart,$obj)
    {

        $this->cko_cc_email = vRequest::getVar('cko_cc_email_' . $cart->virtuemart_paymentmethod_id, '');
        $this->cko_cc_token = vRequest::getVar('cko_cc_token_' . $cart->virtuemart_paymentmethod_id, '');

        $this->_setSession();
        return true;
    }

    private  function _setSession()
    {

        $session = JFactory::getSession();
        $sessionObj = new stdClass();

        // card information
        $sessionObj->cko_cc_email = $this->cko_cc_email;
        $sessionObj->cko_cc_token = $this->cko_cc_token;

        $session->set('checkoutapipayment', json_encode($sessionObj), 'vm');
    }

    public  function validate($enqueueMessage)
    {
        $this->_getSessionData();
        return $this->cko_cc_token?true:false;
    }
}

