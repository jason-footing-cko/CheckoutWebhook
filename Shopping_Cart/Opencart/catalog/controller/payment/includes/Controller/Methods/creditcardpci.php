<?php
class Controller_Methods_creditcardpci extends Controller implements Controller_Interface
{

    public function getData()
    {

        $this->language->load('payment/checkoutapipayment');


        $toReturn = array(
                        'text_card_details'    => $this->language->get('text_card_details'),
                        'entry_cc_owner'       => $this->language->get('entry_cc_owner'),
                        'entry_cc_number'      => $this->language->get('entry_cc_number'),
                        'entry_cc_expire_date' => $this->language->get('entry_cc_expire_date'),
                        'entry_cc_cvv2'        => $this->language->get('entry_cc_cvv2'),
                        'text_wait'            => $this->language->get('text_wait'),
                        'button_confirm'       => $this->language->get('button_confirm'),
                     );


        $months = array();
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = array(
                'text' => strftime('%B', mktime(0, 0, 0, $i, 1, 2000)),
                'value' => sprintf('%02d', $i)
            );
        }

        $toReturn['months'] = $months;

        $today = getdate();

        $yearExpire = array();
        for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
            $yearExpire[] = array(
                'text' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)),
                'value' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i))
            );
        }

        $toReturn['year_expire'] = $yearExpire;


        foreach ($toReturn as $key=>$val) {


            $this->data[$key] = $val;

        }

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/checkoutapi/creditcardpci.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/checkoutapi/creditcardpci.tpl';
        } else {
            $this->template = 'default/template/payment/checkoutapi/creditcardpci.tpl';
        }

        $toReturn['tpl'] =   $this->render();
        return $toReturn;
    }

    public function createCharge($config,$order_info)
    {

        $config['postedParam']['card']  = array(

            'phoneNumber'   =>   $order_info['telephone'] ,
            'name'          =>   str_replace(' ', '', $this->request->post['cc_owner']),
            'number'        =>   str_replace(' ', '', $this->request->post['cc_number']),
            'expiryMonth'   =>   str_replace(' ', '', $this->request->post['cc_expire_date_month']),
            'expiryYear'    =>   str_replace(' ', '', $this->request->post['cc_expire_date_year']),
            'cvv'           =>   str_replace(' ', '', $this->request->post['cc_cvv2']),

        );

        return $config;
    }




}