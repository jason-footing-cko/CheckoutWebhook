<?php
abstract class Controller_Abstract extends Controller
{

    public $_methodInstance;

    public function __construct($registry)
    {

        parent::__construct($registry);
        $this->language->load('payment/checkoutapipayment');
        $methodType = $this->config->get('pci_enable');

        switch ($methodType)
        {
            case 'yes':
                $this->setMethodInstance(new Controller_Methods_creditcardpci($registry));
                break;

            default:
                $this->setMethodInstance(new Controller_Methods_creditcard($registry));
                break;
        }

    }

    protected function index()
    {

        $this->language->load('payment/checkoutapipayment');
        $data = $this->getMethodInstance()->getData();
        foreach ($data as $key=>$val) {

            $this->data[$key] = $val;
        }

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/checkoutapi/checkoutapipayment.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/checkoutapi/checkoutapipayment.tpl';
        } else {
            $this->template = 'default/template/payment/checkoutapi/checkoutapipayment.tpl';
        }

    }

    public function setMethodInstance($methodInstance)
    {
        $this->_methodInstance = $methodInstance;
    }

    public function getMethodInstance()
    {
        return $this->_methodInstance;
    }

    public function send()
    {
        $this->_placeorder();
    }

    public function _placeorder()
    {

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        //building charge
        $respondCharge = $this->_createCharge();

        if( $respondCharge->isValid()) {

            if (preg_match('/^1[0-9]+$/', $respondCharge->getResponseCode())) {
                $Message = 'Your transaction has been successfully authorized with transaction id : '.$respondCharge->getId();

                if(!isset($this->session->data['fail_transaction']) || $this->session->data['fail_transaction'] == false) {
                    $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('checkout_successful_order'), $Message, true);
                }

                if(isset($this->session->data['fail_transaction']) && $this->session->data['fail_transaction']) {
                    $this->model_checkout_order->update($this->session->data['order_id'], $this->config->get('checkout_successful_order'), $Message, true);
                    $this->session->data['fail_transaction'] = false;
                }

                $json['success'] = $this->url->link('checkout/success', '', 'SSL');

            } else {
                $Payment_Error = 'Transaction failed : '.$respondCharge->getErrorMessage(). ' with response code : '.$respondCharge->getResponseCode();

                if(!isset($this->session->data['fail_transaction']) || $this->session->data['fail_transaction'] == false) {
                    $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('checkout_failed_order'), $Payment_Error, true);
                }
                if(isset($this->session->data['fail_transaction']) && $this->session->data['fail_transaction']) {
                    $this->model_checkout_order->update($this->session->data['order_id'], $this->config->get('checkout_failed_order'), $Payment_Error, true);
                }
                $json['error'] = 'We are sorry, but you transaction could not be processed. Please verify your card information and try again.'  ;
                $this->session->data['fail_transaction'] = true;
            }

        } else  {

            $json['error'] = $respondCharge->getExceptionState()->getErrorMessage()  ;
        }
        $this->response->setOutput(json_encode($json));
    }
    private function _createCharge()
    {
        $config = array();
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $scretKey = $this->config->get('secret_key');
        $orderId = $this->session->data['order_id'];
        $amountCents = (int) $order_info['total'] * 100;
        $config['authorization'] = $scretKey  ;
        $config['mode'] = $this->config->get('test_mode');
        $config['timeout'] =  $this->config->get('gateway_timeout');

        if($this->config->get('payment_action') =='authorize_capture') {
            $config = array_merge($config, $this->_captureConfig());

        }else {

            $config = array_merge($config,$this->_authorizeConfig());
        }

        $config['postedParam'] = array_merge($config['postedParam'],array (
            'email'=>$this->customer->getEmail(),
            'amount'=>$amountCents,
            'currency'=> $this->currency->getCode(),
            'description'=>"Order number::$orderId",
        ));

        return $this->_getCharge($this->getMethodInstance()->createCharge($config,$order_info));
    }

    private function _captureConfig()
    {
        $to_return['postedParam'] = array (
            'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_CAPTURE,
            'autoCapTime' => $this->config->get('autocapture_delay')
        );

        return $to_return;
    }

    private function _authorizeConfig()
    {
        $to_return['postedParam'] = array (
            'autoCapture' => CheckoutApi_Client_Constant::AUTOCAPUTURE_AUTH,
            'autoCapTime' => 0
        );

        return $to_return;
    }

    protected function _getCharge($config)
    {
        $Api = CheckoutApi_Api::getApi(array('mode'=> $this->config->get('test_mode')));

        return $Api->createCharge($config);
    }
}