<?php
class Controller_Methods_creditcard extends Controller implements Controller_Interface
{

    public function getData()
    {

        $this->language->load('payment/checkoutapipayment');


        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $toReturn = array(
            'text_card_details' => $this->language->get('text_card_details'),
            'text_wait'         => $this->language->get('text_wait'),
            'entry_public_key'  => $this->config->get('public_key'),
            'order_email'       => $order_info['email'],
            'order_currency'    => $this->currency->getCode(),
            'amount'            => (int) $order_info['total'] * 100,
        );

        foreach ($toReturn as $key=>$val) {

            $this->data[$key] = $val;
        }

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/checkoutapi/creditcard.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/checkoutapi/creditcard.tpl';
        } else {
            $this->template = 'default/template/payment/checkoutapi/creditcard.tpl';
        }

        $toReturn['tpl'] =   $this->render();
        return $toReturn;
    }
    public function createCharge($config,$order_info)
    {

        $config['postedParam']['token']  = $this->request->post['cko_cc_token'];
        $config['postedParam']['email']  = $this->request->post['cko_cc_email'];
        return $config;
    }
}