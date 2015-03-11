<?php
class model_methods_creditcardpci extends model_methods_Abstract
{

    public function processRequest($order,$post,$setting = array())
    {

        $config = parent::processRequest($order,$post,$setting);
        $config['postedParam']['card']['phoneNumber'] = $order['s_phone'];
        $config['postedParam']['card']['name'] = $post['payment_info']['cardholder_name'];
        $config['postedParam']['card']['number'] = preg_replace('/\s+/','',$post['payment_info']['card_number']);
        $config['postedParam']['card']['expiryMonth'] = (int)$post['payment_info']['expiry_month'];
        $config['postedParam']['card']['expiryYear'] = (int)$post['payment_info']['expiry_year'];
        $config['postedParam']['card']['cvv'] = $post['payment_info']['cvv2'];
        return $this->_placeorder($config,$order,$setting);
    }


}