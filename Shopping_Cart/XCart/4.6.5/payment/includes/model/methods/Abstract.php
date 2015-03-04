<?php

abstract class model_methods_Abstract
{

    public function handleRequest()
    {
        global $module_params, $userinfo, $sql_tbl, $cart, $secure_oid, $XCARTSESSID;

        $orderid = $secure_oid[0];

        db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessid,trstat)
                VALUES ('" . addslashes($orderid) . "','" . $XCARTSESSID . "','GO|" . implode('|', $secure_oid) . "')");

        $config = array();
        $amountCents = (int) (100 * $cart['total_cost']);
        $config['authorization'] = $module_params['param02'];
        $config['mode'] = $module_params['param01'];

        $config['postedParam'] = array(
            'email'    => $userinfo['email'],
            'value'    => $amountCents,
            'currency' => $module_params['param09'],
            'card'     => array(
                            'billingDetails' => array(
                                'addressLine1'    =>  $userinfo['b_address'],
                                'addressPostcode' =>  $userinfo['b_address_2'],
                                'addressCountry'  =>  $userinfo['b_country'],
                                'addressCity'     =>  $userinfo['b_city'],
                                'addressPhone'    =>  $userinfo['b_phone']
                                ),
                            )
            );

        if ($module_params['param06'] == 'Authorize and Capture') {
            $config = array_merge($this->_captureConfig(), $config);
            
        } else {
            
            $config = array_merge($this->_authorizeConfig(), $config);
            
        }

        return $config;
    }

    public function handleResponse($respondCharge)
    {
        global $xcart_dir, $secure_oid, $sql_tbl, $xcart_catalogs, $cart;

       $skey = $secure_oid[0];

        $xcart_catalogs['customer'];
        if ($respondCharge->isValid()) {
            if (preg_match('/^1[0-9]+$/', $respondCharge->getResponseCode())) {

                // update charge metadata
                $Api = CheckoutApi_Api::getApi(array('mode' => $module_params['param01']));
                $chargeUpdated = $Api->updateMetadata($respondCharge, array('trackId' => $orderId));

                $bill_output['code'] = 1;
                $bill_output['billmes'] = 'Transaction approved. Charge ID : ' . $respondCharge->getId();
                
            } else { 
                $bill_output['code'] = 2;
                $bill_output['billmes'] = 'An error has occured. Please verify your credit card details and try again.  ' . $respondCharge->getId();
            }
        } else {
            $bill_output['code'] = 4;
            $bill_output['billmes'] = 'An error has occured. Please verify your credit card details and try again.';

        }
        require($xcart_dir . '/payment/payment_ccend.php');
    }

    protected function _placeorder($config)
    {
        //building charge
        $respondCharge = $this->_createCharge($config);
        //$this->_currentCharge = $respondCharge;
        return $this->handleResponse($respondCharge);
    }

    public function _createCharge($config)
    {
        global $module_params;

        $Api = CheckoutApi_Api::getApi(array('mode' => $module_params['param01']));

        return $Api->createCharge($config);
    }

    public function _captureConfig()
    {
        global $module_params;
        $to_return['postedParam'] = array(
            'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_CAPTURE,
            'autoCapTime' => $module_params['param07']
        );
        return $to_return;
    }

    public function _authorizeConfig()
    {
        $to_return['postedParam'] = array(
            'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_AUTH,
            'autoCapTime' => 0
        );
        return $to_return;
    }

}
