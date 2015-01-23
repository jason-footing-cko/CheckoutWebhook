<?php
abstract class model_methods_Abstract {


    public function handleRequest()
    {
        global $module_params, $userinfo, $sql_tbl, $cart,$secure_oid, $XCARTSESSID;

        $orderid = $secure_oid[0];

        db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessid,trstat)
                VALUES ('".addslashes($orderid)."','".$XCARTSESSID."','GO|".implode('|',$secure_oid)."')");

        $config = array();
        $amountCents =(int)(100*$cart['total_cost']);
        $config['authorization'] = $module_params['param02'];
        $config['mode'] = $module_params['param01'];

        $config['postedParam'] = array (
            'email'=> $userinfo['email'] ,
            'value'=>$amountCents,
            'currency'=> $module_params['param09'] ,
            'card' => array(
                        'billingDetails' => array (
                                            'addressLine1'       =>  $userinfo['s_address'],
                                            'addressPostcode'    =>  $userinfo['s_zipcode'],
                                            'addressCountry'     =>  $userinfo['b_country'],
                                            'addressCity'        =>  $userinfo['b_city'],
                                            'addressPhone'       =>  $userinfo['b_phone']
                                         )
                         )
        );

        if ($module_params['param06'] == 'Authorize and Capture') {
            $config = array_merge( $this->_captureConfig(),$config);
        } else {
            $config = array_merge( $this->_authorizeConfig(),$config);
        }

        return $config;
    }

    public function handleResponse($respondCharge)
    {
        global $xcart_dir, $secure_oid, $sql_tbl,$xcart_catalogs,$cart;

        $skey = $secure_oid[0];
        $xcart_catalogs['customer'];
            if ($respondCharge->isValid()) {
                if (preg_match('/^1[0-9]+$/', $respondCharge->getResponseCode())) {

                    $bill_output['code'] = 1;
                    $bill_output['billmes'] = 'Transaction approved. Charge ID : ' . $respondCharge->getId();
                    require($xcart_dir . '/payment/payment_ccend.php');

                } else { echo '1';
                    $bill_output['code'] = 2;
                    $bill_output['billmes'] = 'An error has occured. Please verify your credit card details and try again.  ' .
                                                $respondCharge->getId();
                    require($xcart_dir . '/payment/payment_ccend.php');
                }
            } else {

                $bill_output['code'] = 4;
                $bill_output['billmes'] = 'An error has occured. Please verify your credit card details and try again.';
                require($xcart_dir . '/payment/payment_ccend.php');
            }

    }

    protected function _placeorder($config)
    {
        //building charge
        $respondCharge = $this->_createCharge($config);

        $this->_currentCharge = $respondCharge;

        return $this->handleResponse($respondCharge);

    }

    private function _createCharge($config)
    {
        global $module_params;

        $Api = CheckoutApi_Api::getApi(array('mode'=> $module_params['param01']));

        return $Api->createCharge($config);
    }

    private function _captureConfig()
    {
        global $module_params;
        $to_return['postedParam'] = array (
            'autoCapture' =>( $module_params['param06'] =='Authorize and Capture'),
            'autoCapTime' => $module_params['param07']
        );

        return $to_return;
    }

    private function _authorizeConfig()
    {
        global $module_params;
        $to_return['postedParam'] = array(
            'autoCapture' => ( $module_params['param06'] =='Authorize'),
            'autoCapTime' => $module_params['param07']
        );
        return $to_return;
    }

}