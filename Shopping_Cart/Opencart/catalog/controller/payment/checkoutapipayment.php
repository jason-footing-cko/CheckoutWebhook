<?php
include ('includes/autoload.php');
class ControllerPaymentcheckoutapipayment extends Controller_Abstract
{
    protected function index()
    {
        parent::index();
        $this->document->addScript('http://ckofe.com/js/Checkout.js');

        $this->render();
    }

}
