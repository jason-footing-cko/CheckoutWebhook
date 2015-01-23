<?php
class model_methods_creditcard extends model_methods_Abstract
{
    public function handleRequest()
    {
        global $userinfo;
        $config = parent::handleRequest();
        $config['postedParam']['email'] = $_POST['cko_cc_email'];
        $config['postedParam']['cardToken'] = $_POST['cko_cc_token'];

        $this->_placeorder($config);
    }

    public function handleResponse($respondCharge)
    {
        parent::handleResponse($respondCharge);
    }

}