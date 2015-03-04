<?php

class model_methods_creditcard extends model_methods_Abstract
{

    public function handleRequest()
    {

        $config = parent::handleRequest();
        $this->_placeorder($config);
    }

    public function handleResponse($respondCharge)
    {
        parent::handleResponse($respondCharge);
    }
    
    public function _createCharge($config)
    {
        global $module_params;
        
        $config = array();

        $config['authorization'] = $module_params['param02'];
        $config['timeout'] = $module_params['param08'];
        $config['paymentToken'] = $_POST['cko-cc-paymenToken'];
        $Api = CheckoutApi_Api::getApi(array('mode' => $module_params['param01']));

        return $Api->verifyChargePaymentToken($config);

    }

}
