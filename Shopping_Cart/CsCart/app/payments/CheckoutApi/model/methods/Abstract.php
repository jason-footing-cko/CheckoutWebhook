<?php
abstract class model_methods_Abstract
{

    private $_order_status = array(
        "processed" => "P",
        "pending" => "0",
        "cancel" => "I",
        "completed" => "C",
        "Decline" => "D",
        "Fail" => "F" // Transaction is held for review... I think open order status is good for such situation
    );

    public function processRequest($order,$post,$setting = array())
    {

        $config = array();

        $amountCents = (int)$order['total']*100;
        $config['authorization'] = $setting['secret_key'];
        $config['mode'] =  $setting['mode_type'];
        $products = array();

        if ($order['products']) {
            foreach($order['products'] as $product) {

                $products[] = array (
                    'name'       =>    $product['product'],
                    'sku'        =>    $product['product_code'],
                    'price'      =>    $product['price'],
                    'quantity'   =>     $product['amount'],

                );
            }
        }

        $shippingAddressConfig =  array (
            'addressLine1'       =>  $order['b_address'],
            'addressLine2'       =>  $order['b_address_2'],
            'postcode'    =>  $order['s_zipcode'],
            'country'     =>  $order['s_country'],
            'city'        =>  $order['s_city'],
            'phone'       =>  $order['s_phone']
        );

        $config['postedParam'] = array (
            'email'           => $order['email'] ,
            'value'           => $amountCents,
            'currency'        => $order['secondary_currency'] ,
            'products'        => $products,
            'shippingDetails' => $shippingAddressConfig,
            'metadata'        => array("trackId" => $order['order_id'] ),
            'metadata'        => array("trackId" => $order['order_id'] ),
            'card' => array(
                        'billingDetails' => array (
                                            'addressLine1'  =>  $order['b_address'],
                                            'addressLine2'  =>  $order['b_address_2'],
                                            'postcode'      =>  $order['b_zipcode'],
                                            'country'       =>  $order['b_country'],
                                            'city'          =>  $order['b_city'],
                                            'phone'         =>  $order['b_phone'],

                                         )
                         )

        );

        if ($setting['transaction_type'] == 'authorize_capture') {
            $config = array_merge( $this->_captureConfig($setting),$config);
        } else {
            $config = array_merge( $this->_authorizeConfig($setting),$config);
        }

        return $config;
    }
    protected function _placeorder($config,$order,$setting)
    {
        //building charge
        $respondCharge = $this->_createCharge($config,$setting);
        $pp_response = array();

        if( $respondCharge->isValid()) {
            if (preg_match('/^1[0-9]+$/', $respondCharge->getResponseCode())) {

                $pp_response['order_status'] = $this->getStatus($setting['order_complete']);

            }else {
                $pp_response['order_status'] = $this->getStatus($setting['Decline']);
            }
            $pp_response['transaction_id'] = $respondCharge->getId();
            $pp_response['reason_text'] = $respondCharge->getResponseMessage();

        } else  {

            $pp_response['order_status'] = $this->getStatus($setting['Fail']);
            $pp_response['reason_text'] = $respondCharge->getExceptionState()->getErrorMessage();
         }

        return $pp_response;
    }
    protected function _createCharge($config,$setting)
    {
        $Api = CheckoutApi_Api::getApi(array('mode'=> $setting['mode_type']));
        return $Api->createCharge($config);
    }
    protected function _captureConfig($setting)
    {
        $to_return['postedParam'] = array (
            'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_CAPTURE,
            'autoCapTime' => $setting['autocaptime']
        );

        return $to_return;
    }

    protected function _authorizeConfig($setting)
    {
        $to_return['postedParam'] = array(
            'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_AUTH,
            'autoCapTime' => 0
        );
        return $to_return;
    }

    public function getStatus($state)
    {

        return $this->_order_status[$state];
    }
    public function getExtraHtml($setting){}
}