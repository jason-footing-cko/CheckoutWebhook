<?php
class Controller_Methods_creditcard extends Controller implements Controller_Interface
{

    public function getData()
    {

        $this->language->load('payment/checkoutapipayment');
        //$this->document->addScript('https://www.checkout.com/cdn/js/Checkout.js');

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $jsConfig = $this->renderJsConfig();
        $toReturn = array(
            'text_card_details' => $this->language->get('text_card_details'),
            'text_wait'         => $this->language->get('text_wait'),
            'entry_public_key'  => $this->config->get('public_key'),
            'order_email'       => $order_info['email'],
            'order_currency'    => $this->currency->getCode(),
            'amount'            => (int) $order_info['total'] * 100,
            'jsconfig'            => $jsConfig,
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

        $config['postedParam']['cardToken']  = $this->request->post['cko_cc_token'];
        $config['postedParam']['email']  = $this->request->post['cko_cc_email'];
        return $config;
    }


    public function renderJsConfig()
    {
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $Api = CheckoutApi_Api::getApi(array('mode'=> $this->config->get('test_mode')));

        $config = array();
        $config['debug'] = false;
        $config['publicKey'] = $this->config->get('public_key') ;
        $config['email'] =  $order_info['email'];
        $config['name'] = $order_info['firstname']. ' '.$order_info['lastname'];
        $config['amount'] =  (int) $order_info['total'] * 100;
        $config['currency'] =  $this->currency->getCode();
        $config['widgetSelector'] =  '.widget-container';
        $config['cardTokenReceivedEvent'] = "
                        document.getElementById('cko-cc-token').value = event.data.cardToken;
                        document.getElementById('cko-cc-email').value = event.data.email;

                           $.ajax({
                        url: 'index.php?route=payment/checkoutapipayment/send',
                        type: 'post',
                        data: $('#payment :input'),
                        dataType: 'json',
                        beforeSend: function () {
                            $('#button-confirm').attr('disabled', true);
                            $('#payment').before('<div class=\"attention\"><img src=\"catalog/view/theme/default/image/loading.gif\" alt=\"\" />".$this->language->get('text_wait')."</div>');
                        },
                        complete: function () {
                            $('#button-confirm').attr('disabled', false);
                            $('.attention').remove();
                        },
                        success: function (json) {

                            if (json['error']) {
                                alert(json['error']);
                            }

                            if (json['success']) {
                                 location = json['success'];
                            }
                        }
                    });

                        ";
        $config['readyEvent'] = '';
        $jsConfig = $Api->getJsConfig($config);

        return $jsConfig;
    }
}