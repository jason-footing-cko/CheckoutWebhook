<?php
 class Model extends osC_Payment
{
     public $_title;
     public $_code ='checkoutapipayment';
     protected $_status = false;
     protected $_sort_order;
     protected $_order_status;
     protected $_instance;

     public function __construct()
     {
         global  $osC_Language,$osC_Template;
         $this->_title = $osC_Language->get('payment_checkoutapipayment_cc_title');
         $this->_method_title = $osC_Language->get('payment_checkoutapipayment_method_title');
         $this->_status = (MODULE_PAYMENT_CHECKOUTAPIPAYMENT_STATUS == '1') ? true : false;
         $this->_sort_order = MODULE_PAYMENT_CHECKOUTAPIPAYMENT_SORT_ORDER;
         $this->getInstance();
         $this->_init();

     }

     private function _init()
     {
         global $osC_Database,$osC_ShoppingCart;
         if ($this->_status === true) {
             if ((int)MODULE_PAYMENT_CHECKOUTAPIPAYMENT_ORDER_STATUS_ID > 0) {
                 $this->order_status = MODULE_PAYMENT_CHECKOUTAPIPAYMENT_PROCESSING_ORDER_STATUS_ID;
             }

             if ((int)MODULE_PAYMENT_CHECKOUTAPIPAYMENT_ZONE > 0 && $osC_ShoppingCart) {
                 $check_flag = false;

                 $Qcheck = $osC_Database->query('select zone_id from :table_zones_to_geo_zones where geo_zone_id = :geo_zone_id and zone_country_id = :zone_country_id order by zone_id');
                 $Qcheck->bindTable(':table_zones_to_geo_zones', TABLE_ZONES_TO_GEO_ZONES);
                 $Qcheck->bindInt(':geo_zone_id', MODULE_PAYMENT_CHECKOUTAPIPAYMENT_ZONE);
                 $Qcheck->bindInt(':zone_country_id', $osC_ShoppingCart->getBillingAddress('country_id'));
                 $Qcheck->execute();

                 while ($Qcheck->next()) {
                     if ($Qcheck->valueInt('zone_id') < 1) {
                         $check_flag = true;
                         break;
                     } elseif ($Qcheck->valueInt('zone_id') == $osC_ShoppingCart->getBillingAddress('zone_id')) {
                         $check_flag = true;
                         break;
                     }
                 }

                 if ($check_flag === false) {
                     $this->_status = false;
                 }
             }
         }
     }
     public function getInstance()
     {
        if(!$this->_instance) {

            switch(MODULE_PAYMENT_CHECKOUAPIPAYMENT_TYPE) {
                case '1':
                    $this->_instance = CheckoutApi_Lib_Factory::getInstance('model_methods_creditcardpci');
                break;
                default :
                    $this->_instance =  CheckoutApi_Lib_Factory::getInstance('model_methods_creditcard');

                    break;
            }
        }

         return $this->_instance;

     }


     public function selection()
     {
         return array_merge( array('id'     => $this->code,
                                   'module' => $this->_method_title),$this->getInstance()->selection($this)
                           );
     }
     public function pre_confirmation_check()
     {
         $this->getInstance()->pre_confirmation_check();
     }

     public function confirmation()
     {
         return  $this->getInstance()->confirmation($this);
     }


     public function process()
     {

         $this->getInstance()->process();
     }
     public function getJavascriptBlock()
     {
         return $this->getInstance()->getJavascriptBlock($this);
     }



 }