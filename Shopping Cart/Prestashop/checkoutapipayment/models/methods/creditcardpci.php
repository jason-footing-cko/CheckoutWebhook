<?php
/**
 * Created by PhpStorm.
 * User: dhiraj.gangoosirdar
 * Date: 10/30/2014
 * Time: 12:51 PM
 */

class models_methods_creditcardpci extends models_methods_Abstract
{
    protected  $_code = 'creditcardpci';

    public function __construct()
    {
        $this->name = 'creditcardpic';
        $this->author = 'Checkout.com';
        $this->description = $this->l('Receive payment with gateway 3.0');
        parent::__construct();
    }

    public  function _initCode()
    {

    }

    public function hookPayment($param)
    {

        $hasError = false;
        $ccType = Tools::getValue('cc_type');
        $cc_owner = Tools::getValue('cc_owner');
        $cc_number = Tools::getValue('cc_number');
        $cc_exp_month = Tools::getValue('cc_exp_month');
        $cc_exp_year = Tools::getValue('cc_exp_year');
        $cc_cid = Tools::getValue('cc_cid');

        $cards = helper_Card::getCardType($this);
        return  array(
            'hasError' 			=>	 $hasError,
            'cards' 			=>	 $cards,
            'ccType' 			=>	 $ccType,
            'cc_owner' 			=>	 $cc_owner,
            'cc_exp_month' 		=>	 $cc_exp_month,
            'cc_exp_year' 		=>	 $cc_exp_year,
            'months' 			=>	 helper_Card::getExMonth(),
            'years' 			=>	 helper_Card::getExYear(),
            'methodType' 		=>	 $this->getCode()
           );


    }
    public  function createCharge($config = array(),$cart)
    {
        $invoiceAddress = new Address((int)$cart->id_address_invoice);

        $config['postedParam']['card']  = array(

            'phoneNumber'   =>   $invoiceAddress->phone ,
            'name'          =>   Tools::getValue('cc_owner'),
            'number'        =>   Tools::getValue('cc_number'),
            'expiryMonth'   =>   Tools::getValue('cc_exp_month'),
            'expiryYear'    =>  Tools::getValue('cc_exp_year'),
            'cvv'           =>  Tools::getValue('cc_cid'),

        );

       return parent::_createCharge($config);
    }
}