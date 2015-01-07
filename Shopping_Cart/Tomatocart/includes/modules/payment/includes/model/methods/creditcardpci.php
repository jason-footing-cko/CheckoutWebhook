<?php
class model_methods_creditcardpci extends model_methods_Abstract
{

    public function confirmation($obj)
    {

        global $osC_Language, $osC_CreditCard;

        $confirmation = array(
            'title' => $obj->_method_title,
            'fields' => array(
                array(
                    'title' => $osC_Language->get('payment_checkoutapipayment_credit_card_owner'),
                    'field' => $osC_CreditCard->getOwner()
                ),
                array(
                      'title' => $osC_Language->get('payment_checkoutapipayment_credit_card_number'),
                      'field' => $osC_CreditCard->getSafeNumber()
                ),

                array(
                    'title' => $osC_Language->get('payment_checkoutapipayment_credit_card_expiry_date'),
                    'field' => $osC_CreditCard->getExpiryMonth() . ' / ' . $osC_CreditCard->getExpiryYear()
                )

            )
        );


        return $confirmation;
    }

    public function selection($obj)
    {
        global $osC_Database, $osC_Language, $osC_ShoppingCart;

        for ($i=1; $i<13; $i++) {
            $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B',mktime(0,0,0,$i,1)));
        }

        $year = date('Y');
        for ($i=$year; $i < $year+10; $i++) {
            $expires_year[] = array('id' => $i, 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
        }

        $selection = array('id' => $obj->_code,
            'module' => $obj->_method_title,
            'fields' => array(
                array(
                    'title' => $osC_Language->get('payment_checkoutapipayment_credit_card_owner'),
                    'field' => osc_draw_input_field('checkoutapipayment_cc_owner', $osC_ShoppingCart->getBillingAddress('firstname')
                        . ' ' . $osC_ShoppingCart->getBillingAddress('lastname'))
                ),
                array(
                    'title' => $osC_Language->get('payment_checkoutapipayment_credit_card_number'),
                    'field' => osc_draw_input_field('checkoutapipayment_cc_number')
                ),

                array(
                    'title' => $osC_Language->get('payment_checkoutapipayment_credit_card_expiry_date'),
                    'field' => osc_draw_pull_down_menu('checkoutapipayement_cc_expires_month', $expires_month) . '&nbsp;' .
                        osc_draw_pull_down_menu('checkoutapipayement_cc_expires_year', $expires_year)
                ),
                array(
                    'title' => $osC_Language->get('payment_checkoutapipayment_credit_card_cvc'),
                    'field' => osc_draw_input_field('checkoutapipayement_cc_cvc', null, 'size="5" maxlength="4"'))
            )
        );

        return $selection;
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
        $config['postedParam']['card']['phoneNumber'] =  $osC_ShoppingCart->getBillingAddress('telephone_number');
        $config['postedParam']['card']['name'] = $osC_CreditCard->getOwner();
        $config['postedParam']['card']['number'] = $osC_CreditCard->getNumber();
        $config['postedParam']['card']['expiryMonth'] = (int)$osC_CreditCard->getExpiryMonth();
        $config['postedParam']['card']['expiryYear'] = (int)$osC_CreditCard->getExpiryYear();
        $config['postedParam']['card']['cvv'] = $osC_CreditCard->getCVC();

        $this->_placeorder($config);
    }

    public function  process_button()
    {

    }
   private  function _verifyData() {
        global $osC_Language, $messageStack, $osC_CreditCard;

        $osC_CreditCard = new osC_CreditCard($_POST['checkoutapipayment_cc_number'], $_POST['checkoutapipayement_cc_expires_month'], $_POST['checkoutapipayement_cc_expires_year']);
        $osC_CreditCard->setOwner($_POST['checkoutapipayment_cc_owner']);

        $osC_CreditCard->setCVC($_POST['checkoutapipayment_cc_cvc']);


        if (($result = $this->isValid($osC_CreditCard)) !== true) {


            switch ($result) {
                case -2:
                    $error = $osC_Language->get('payment_checkoutapipayment_cc_error_invalid_expiry_date');
                    break;

                case -3:
                    $error = $osC_Language->get('payment_checkoutapipayment_cc_error_expired');
                    break;

                case -5:
                    $error = $osC_Language->get('payment_checkoutapipayment_credit_card_not_accepted');
                    break;

                default:
                    $error = $osC_Language->get('payment_checkoutapipayment_error_general');
                    break;
            }

            $messageStack->add_session('checkout_payment', $error, 'error');

     }
    }

  public  function isValid($osC_CreditCard) {


        if ($osC_CreditCard->hasValidExpiryDate() === false) {
            return -2;
        }

        if ($osC_CreditCard->hasExpired() === true) {
            return -3;
        }

        if ($osC_CreditCard->hasOwner() && ($osC_CreditCard->hasValidOwner() === false)) {
            return -4;
        }

        return true;
    }
}