<?php
class model_methods_creditcardpci extends model_methods_Abstract
{

    private $_cc_type;
    private $_cc_number;
    private $_cc_cvv;
    private $_cc_expire_year;
    private $_cc_expire_month;
    public function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0,&$htmlIn,$obj)
    {

       $toReturn = true;

        JHTML::script('vmcreditcard.js', 'components/com_virtuemart/assets/js/', FALSE);
        VmConfig::loadJLang('com_virtuemart', true);
        vmJsApi::jCreditCard();

        $currentMethod =  $obj->getCurrentMethod();
        $method_name = $obj->getPsType() . '_name';
        $methodSalesPrice = $obj->setCartPrices($cart, $cart->cartPrices, $currentMethod);

        $obj->getInstance()->getC->$method_name = $obj->getRenderPluginName($currentMethod);
        $html = $obj->pluginHtml($currentMethod, $selected, $methodSalesPrice);

        if ($selected == $currentMethod->virtuemart_paymentmethod_id) {
             $this->_getSessionData();
        }

        if (empty($currentMethod->creditcards)) {
            $currentMethod->creditcards = self::getCreditCards();
        } elseif (!is_array($currentMethod->creditcards)) {
            $currentMethod->creditcards = (array)$currentMethod->creditcards;
        }
        $creditCards = $currentMethod->creditcards;
        $creditCardList = '';
        if ($creditCards) {
            $creditCardList = ($this->_renderCreditCardList($creditCards, $this->_cc_type, $currentMethod->virtuemart_paymentmethod_id, false));
        }
        $cvv_images = $this->_displayCVVImages($currentMethod,$obj);
        $htmla = '';
        $paymentId = $currentMethod->virtuemart_paymentmethod_id;
        $html = array();
        $html[] = '<br/>';
        $html[] = '<span class="vmpayment_cardinfo">';
        $html[] =  vmText::_('VMPAYMENT_CHECKOUTAPIPAYMENT_COMPLETE_FORM');


            $html[] = '<table border="0" cellspacing="0" cellpadding="2" width="100%">';
                $html[] = '<tr valign="top">';

                    $html[] = '<td nowrap width="10%" align="right">';

                            $html[] = '<label for="creditcardtype">';
                            $html[] =  vmText::_('VMPAYMENT_CHECKOUTAPIPAYMENT_CCTYPE');
                            $html[] = '</label>';
                    $html[] = '</td>';

                    $html[] = '<td>';
                    $html[] = $creditCardList;
                    $html[] = '</td>';

                $html[] = '</tr>';

                $html[] = '<tr valign="top">';

                    $html[] = '<td nowrap width="10%" align="right">';

                    $html[] = '<label for="cc_type">';
                    $html[] =  vmText::_('VMPAYMENT_CHECKOUTAPIPAYMENT_CCNUM');
                    $html[] = '</label>';

                    $html[] = '</td>';

                    $html[] = '<td>';
                    $html[] = <<<EOD
                                <script type="text/javascript">
                                                //<![CDATA[
                                                  function checkAuthorizeNet(id, el)
                                                   {
                                                       ccError=razCCerror(id);
                                                       CheckCreditCardNumber(el.value, id);
                                                       if (!ccError) {
                                                           el.value='';}
                                                   }
                                                //]]></script>
EOD;
                    $html[] = '<input type="text" class="inputbox" id="cc_number_' . $paymentId .
                        '" name="cc_number_' . $paymentId . '" value="' . $this->_cc_number .
                        '"    autocomplete="off"   onchange="javascript:checkAuthorizeNet(' . $paymentId . ', this);"  />';

                    $html[] = ' <div id="cc_cardnumber_errormsg_' . $paymentId . '"></div>';


                    $html[] = '</td>';

                $html[] = '</tr>';

                $html[] = '<tr valign="top">';

                        $html[] = '<td nowrap width="10%" align="right">';

                        $html[] = '<label for="cc_cvv">';
                        $html[] =  vmText::_('VMPAYMENT_CHECKOUTAPIPAYMENT_CVV2');
                        $html[] = '</label>';

                        $html[] = '</td>';

                        $html[] = '<td>';
                        $html[] = '<input type="text" class="inputbox" id="cc_cvv_' . $paymentId . '" name="cc_cvv_'
                            . $paymentId. '" maxlength="4" size="5" value="' . $this->_cc_cvv . '" autocomplete="off" />';
                        $html[] = '<span class="hasTip" title="' . vmText::_('VMPAYMENT_CHECKOUTAPIPAYMENT_WHATISCVV') . '::'
                            . vmText::sprintf("VMPAYMENT_CHECKOUTAPIPAYMENT_WHATISCVV_TOOLTIP", $cvv_images) . ' ">' .
                            vmText::_('VMPAYMENT_CHECKOUTAPIPAYMENT_WHATISCVV') . '
			</span>';
                        $html[] = '</td>';

                $html[] = '</tr>';

                $html[] = '<tr valign="top">';

                    $html[] = '<td nowrap width="10%" align="right">';

                        $html[] = '<label for="creditcardtype">';
                        $html[] =  vmText::_('VMPAYMENT_CHECKOUTAPIPAYMENT_EXDATE');
                        $html[] = '</label>';
                        $html[] = '</td>';

                    $html[] = '<td>';
                    $html[] = shopfunctions::listMonths('cc_expire_month_' . $paymentId, $this->_cc_expire_month);
                    $html[] = '/';
                    $html[] = <<<EOD
                    <script type="text/javascript">
                        //<![CDATA[
                          function changeDate(id, el)
                           {
                             var month = document.getElementById('cc_expire_month_'+id); if(!CreditCardisExpiryDate(month.value,el.value, id))
                             {el.value='';
                             month.value='';}
                           }
                        //]]>
                    </script>
EOD;

                    $html[] = shopfunctions::listYears('cc_expire_year_' . $paymentId, $this->_cc_expire_year, NULL, (date('Y')+20),
                        ' onchange="javascript:changeDate(" . $paymentId . ", this);" ');


                    $html[] = '<div id="cc_expiredate_errormsg_' . $paymentId . '"></div>';

                    $html[] = '</td>';

                $html[] = '</tr>';


            $html[] = '</table>';
            $html[] = '</span>';

        $htmlIn[] = array(join("\n",$html));


        return $toReturn;
    }
    protected function _getSessionData()
    {

    }
    public function process()
    {
        global  $osC_ShoppingCart, $osC_CreditCard;
        $this->_verifyData();
        $config = parent::process();
        $config['postedParam']['card']['phoneNumber'] =  $osC_ShoppingCart->getBillingAddress('telephone_number');
        $config['postedParam']['card']['name'] = $osC_CreditCard->getOwner();
        $config['postedParam']['card']['number'] = $osC_CreditCard->getNumber();
        $config['postedParam']['card']['expiryMonth'] = (int)$osC_CreditCard->getExpiryMonth();
        $config['postedParam']['card']['expiryYear'] = (int)$osC_CreditCard->getExpiryYear();
        $config['postedParam']['card']['cvv'] = $osC_CreditCard->getCVC();

        $this->_placeorder($config);
    }

    protected function _sessionSave(VirtueMartCart $cart)
    {
        return $this->getInstance()->_sessionSave($cart);
    }


    /**
     * drawing form utilities
     */

    static function getCreditCards()
    {
        return array(
            'Visa',
            'Mastercard',
            'AmericanExpress',
            'Discover',
            'DinersClub',
            'JCB',
        );

    }

    /**
     * Creates a Drop Down list of available Creditcards
     *
     * @author Valerie Isaksen
     */
    function _renderCreditCardList($creditCards, $selected_cc_type, $paymentmethod_id, $multiple = FALSE, $attrs = '')
    {

        $idA = $id = 'cc_type_' . $paymentmethod_id;
        //$options[] = JHTML::_('select.option', '', vmText::_('VMPAYMENT_AUTHORIZENET_SELECT_CC_TYPE'), 'creditcard_type', $name);
        if (!is_array($creditCards)) {
            $creditCards = (array)$creditCards;
        }
        foreach ($creditCards as $creditCard) {
            $options[] = JHTML::_('select.option', $creditCard, vmText::_('VMPAYMENT_CHECKOUTAPIPAYMENT_' . strtoupper($creditCard)));
        }
        if ($multiple) {
            $attrs = 'multiple="multiple"';
            $idA .= '[]';
        }
        return JHTML::_('select.genericlist', $options, $idA, $attrs, 'value', 'text', $selected_cc_type);
    }

    public function _displayCVVImages($method,$obj) {

        $cvv_images = $method->cvv_images;
        $img = '';

        if ($cvv_images) {
            $img = $obj->displayLogos($cvv_images);
            $img = str_replace('"', "'", $img);
        }
        return $img;
    }

    public function plgVmOnSelectCheckPayment(VirtueMartCart $cart, &$msg)
    {
        CheckoutApi_Utility_Utilities::dump($cart); die();
        if (!$this->selectedThisByMethodId($cart->virtuemart_paymentmethod_id)) {
            return null; // Another method was selected, do nothing
        }

        if (!($this->_currentMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
            return false;
        }
    }
}