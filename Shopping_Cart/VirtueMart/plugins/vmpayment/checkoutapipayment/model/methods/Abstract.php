<?php
defined('_JEXEC') or die('Restricted access');

if (!class_exists('Creditcard')) {
    require_once(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'creditcard.php');
}

if (!class_exists('vmPSPlugin')) {
    require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
}

abstract class model_methods_Abstract
{

     public function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn, $obj)
     {
         return $obj->displayListFE ($cart, $selected, $htmlIn);
     }


    public function process()
    {
        global $osC_Customer, $osC_Currencies, $osC_ShoppingCart;
        $config = array();


            $amountCents = (int)$osC_Currencies->formatRaw($osC_ShoppingCart->getTotal(),$osC_Currencies->getCode())*100;
            $config['authorization'] = MODULE_PAYMENT_CHECKOUTAPIPAYMENT_SECRET_KEY;
            $config['mode'] = MODULE_PAYMENT_CHECKOUTAPIPAYMENT_GATEWAY_SERVER;
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
                'email'             => $osC_Customer->getEmailAddress() ,
                'value'             => $amountCents,
                'shippingDetails'   => $shippingAddressConfig,
                'currency'          => $osC_Currencies->getCode() ,
                'products'         =>    $products,
                'metadata'      =>    array("trackId" => $this->_order_id ),
                'card'   => array(
                            'billingDetails' => array (
                                                'addressLine1'       =>  $osC_ShoppingCart->getBillingAddress('street_address'),
                                                'addressPostcode'    =>  $osC_ShoppingCart->getBillingAddress('postcode'),
                                                'addressCountry'     =>  $osC_ShoppingCart->getBillingAddress('country_title'),
                                                'addressCity'        =>  $osC_ShoppingCart->getBillingAddress('city'),
                                                'addressPhone'       =>  $osC_ShoppingCart->getBillingAddress('telephone_number')

                                             )
                             )

            );

            if (MODULE_PAYMENT_CHECKOUTAPIPAYMENT_TRANSACTION_METHOD == 'Authorize and Capture') {
                $config = array_merge( $this->_captureConfig(),$config);
            } else {
                $config = array_merge( $this->_authorizeConfig(),$config);
            }

        return $config;
    }

    protected function _placeorder($config)
    {
        global $osC_Database, $osC_Customer, $osC_Currencies, $osC_ShoppingCart, $osC_Language, $messageStack, $osC_CreditCard;
        $order_id = osC_Order::insert();
        $error =   $error = $osC_Language->get('payment_checkoutapipayment_error_general');;
        $respondCharge = $this->_createCharge($config);
        $this->_currentCharge = $respondCharge;

        if( $respondCharge->isValid()) {
            if (preg_match('/^1[0-9]+$/', $respondCharge->getResponseCode())) {
                osC_Order::process($this->_order_id, MODULE_PAYMENT_CHECKOUTAPIPAYMENT_PROCESSING_ORDER_STATUS_ID);

                $Qtransaction = $osC_Database->query('insert into '.TABLE_ORDERS_TRANSACTIONS_HISTORY.'
                                  (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added)
                                  values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');
             //   $Qtransaction->bindTable(':table_orders_transactions_history', TABLE_ORDERS_TRANSACTIONS_HISTORY);
                $Qtransaction->bindInt(':orders_id', $order_id);
                $Qtransaction->bindInt(':transaction_code', $respondCharge->getRespondCode());
                $Qtransaction->bindValue(':transaction_return_value', $respondCharge->getId());
                $Qtransaction->bindInt(':transaction_return_status', 1);
                $Qtransaction->execute();


                if (osc_not_null(MODULE_PAYMENT_CHECKOUTAPIPAYMENT_PROCESSING_ORDER_STATUS_ID)) {
                    osC_Order::process($_POST['invoice'], MODULE_PAYMENT_CHECKOUTAPIPAYMENT_PROCESSING_ORDER_STATUS_ID, 'Checkout.com Processing Transaction');
                }
            }else {
                osC_Order::remove($this->_order_id);

                $messageStack->add_session('checkout_payment', $error, 'error');

                osc_redirect(osc_href_link(FILENAME_CHECKOUT, 'payment_error='.$respondCharge->getRespondCode(), 'SSL'));


            }

        } else  {

            osC_Order::remove($this->_order_id);

            $messageStack->add_session('checkout_payment', $error, 'error');

            osc_redirect(osc_href_link(FILENAME_CHECKOUT,'payment_error=' . $respondCharge->getErrorCode(), 'SSL'));


        }

    }
    private function _createCharge($config)
    {
        $Api = CheckoutApi_Api::getApi(array('mode'=> MODULE_PAYMENT_CHECKOUTAPIPAYMENT_GATEWAY_SERVER));

        return $Api->createCharge($config);
    }

    private function _captureConfig()
    {
        $to_return['postedParam'] = array (
            'autoCapture' =>( MODULE_PAYMENT_CHECKOUTAPIPAYMENT_TRANSACTION_METHOD =='Authorize and Capture'),
            'autoCapTime' => MODULE_PAYMENT_CHECKOUAPIPAYMENT_AUTOCAPTIME
        );

        return $to_return;
    }

    private function _authorizeConfig()
    {
        $to_return['postedParam'] = array(
            'autoCapture' => ( MODULE_PAYMENT_CHECKOUTAPIPAYMENT_TRANSACTION_METHOD =='Authorize'),
            'autoCapTime' => 0
        );
        return $to_return;
    }

    protected function _getSessionData()
    {
        $toReturn = null;
        if (!class_exists('vmCrypt')) {
            require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'vmcrypt.php');
        }
        $session = JFactory::getSession();
        $_session = $session->get('checkoutapipayment', 0, 'vm');
        if (!empty($_session)) {
            $toReturn = (object)json_decode($_session,true);
        }
        return $toReturn;
    }

    protected function _sessionSave(VirtueMartCart $cart)
    {
        return $this->getInstance()->_sessionSave($cart);
    }

}