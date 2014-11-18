<?php
class Controller_Abstract extends Controller
{

    public $_methodInstance;

//    private function __construct()
//    {
//
//        $this->language->load('payment/checkoutapipayment');
//        //$methodType = $this->config->get('pci_enable');
//        die('test');
//
//        switch ($methodType)
//        {
//            case 'yes':
//                $this->setMethodInstance(new Controller_Method_creditcardpci());
//                break;
//
//            default:
//                $this->setMethodInstance(new Controller_Method_creditcard());
//                break;
//        }
//    }

    protected function index()
    {
        $this->language->load('payment/checkoutapipayment');
        $methodType = $this->config->get('pci_enable');


        switch ($methodType)
        {
            case 'yes':
                $this->setMethodInstance(new Controller_Method_creditcardpci());
                break;

            default:
                $this->setMethodInstance(new Controller_Method_creditcard());
                break;
        }

        $data = $this->getMethodInstance();

        die($data);

        foreach ($data as $key=>$val) {


            $this->data[$key] = $val;

        }

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/checkoutapipayment.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/checkoutapipayment.tpl';
        } else {
            $this->template = 'default/template/payment/checkoutapipayment.tpl';
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
}