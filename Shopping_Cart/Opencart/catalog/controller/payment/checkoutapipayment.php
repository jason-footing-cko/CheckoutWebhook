<?php
include ('checkoutApi/autoload.php');
class ControllerPaymentcheckoutapipayment extends Controller_Abstract
{
    protected function index()
    {
        //parent::index();
        $this->render();
    }

    public function send()
    {
        $this->load->model('checkout/order');

        print_r ('test');
        die();
    }

}
