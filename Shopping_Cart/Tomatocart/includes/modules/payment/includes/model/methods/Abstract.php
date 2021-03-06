<?php
abstract class model_methods_Abstract  {

    private $_currentCharge;

    public function getEnabled()
    {
        return   defined('MODULE_PAYMENT_CHECKOUTAPIPAYMENT_STATUS') &&
        (MODULE_PAYMENT_CHECKOUTAPIPAYMENT_STATUS == 'True') ? true : false;
    }

    public function selection($obj)
    {
        return array();
    }
    abstract public function pre_confirmation_check();

    abstract public function confirmation($obj);

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
            'trackId'           => $this->_order_id,
            'shippingDetails'   => $shippingAddressConfig,
            'currency'          => $osC_Currencies->getCode() ,
            'products'          =>    $products,
            'metadata'          =>    array("trackId" => $this->_order_id ),
            'card'              => array(
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
            $config = array_merge_recursive( $this->_captureConfig(),$config);
        } else {
            $config = array_merge_recursive( $this->_authorizeConfig(),$config);
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

            $Api = CheckoutApi_Api::getApi( array( 'mode'          => MODULE_PAYMENT_CHECKOUTAPIPAYMENT_TRANSACTION_SERVER,
                                                   'authorization' => MODULE_PAYMENT_CHECKOUTAPIPAYMENT_SECRET_KEY
                                                 )
                                          );

            $chargeUpdated = $Api->updateMetadata($this->_currentCharge,array('trackId' => $order_id));

            if (preg_match('/^1[0-9]+$/', $respondCharge->getResponseCode())) {
                osC_Order::process($this->_order_id, MODULE_PAYMENT_CHECKOUTAPIPAYMENT_PROCESSING_ORDER_STATUS_ID);

                $Qtransaction = $osC_Database->query('insert into '.TABLE_ORDERS_TRANSACTIONS_HISTORY.'
                                  (orders_id, transaction_code, transaction_return_value, transaction_return_status, date_added)
                                  values (:orders_id, :transaction_code, :transaction_return_value, :transaction_return_status, now())');

                $Qtransaction->bindInt(':orders_id', $order_id);
                $Qtransaction->bindInt(':transaction_code', $respondCharge->getRespondCode());
                $Qtransaction->bindValue(':transaction_return_value', $respondCharge->getId());
                $Qtransaction->bindInt(':transaction_return_status', 1);
                $Qtransaction->execute();

                if (osc_not_null(MODULE_PAYMENT_CHECKOUTAPIPAYMENT_PROCESSING_ORDER_STATUS_ID)) {
                    osC_Order::process($_POST['invoice'], MODULE_PAYMENT_CHECKOUTAPIPAYMENT_PROCESSING_ORDER_STATUS_ID, 'Checkout.com Processing Transaction');
                }

            } else {
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
    protected function _createCharge($config)
    {
        $Api = CheckoutApi_Api::getApi(array('mode'=> MODULE_PAYMENT_CHECKOUTAPIPAYMENT_GATEWAY_SERVER));

        return $Api->createCharge($config);
    }

    protected function _captureConfig()
    {
        $to_return['postedParam'] = array (
            'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_CAPTURE,
            'autoCapTime' => MODULE_PAYMENT_CHECKOUAPIPAYMENT_AUTOCAPTIME
        );

        return $to_return;
    }

    protected function _authorizeConfig()
    {
        $to_return['postedParam'] = array(
            'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_AUTH,
            'autoCapTime' => 0
        );
        return $to_return;
    }
    public function getJavascriptBlock($obj)
    {
        return '';
    }


}