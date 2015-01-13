<?php
defined('_JEXEC') or die('Restricted access');

if (!class_exists('Creditcard')) {
    require_once(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'creditcard.php');
}

if (!class_exists('vmPSPlugin')) {
    require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
}

 class Model extends vmPSPlugin
{

     public  $_currentMethod;

     function __construct (& $subject, $config) {
         parent::__construct ($subject, $config);
         $varsToPush = $this->getVarsToPush ();
         $this->setConfigParameterable ($this->_configTableFieldName, $varsToPush);
         $this->tableFields = array_keys ($this->getTableSQLFields ());
         $this->_tablepkey = 'id';
         $this->_tableId = 'id';

     }

     public function getCurrentMethod()
     {
         if(is_null($this->_current)){
             $this->_currentMethod = $this->methods[0];
         }
         return $this->_currentMethod;
     }

     public function getInstance()
     {

         if(!$this->_instance) {

             switch($this->getCurrentMethod()->mode_type) {
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

     function getTableSQLFields ()
     {
         $SQLfields = array(
             'id' => 'int(1) UNSIGNED NOT NULL AUTO_INCREMENT',
             'virtuemart_order_id' => 'int(1) UNSIGNED',
             'transaction_id' => 'char(64)',
             'gateway_id' => 'varchar(255)',

             'product' => 'varchar(255)',
             'quantity' => 'varchar(255)',
             'total' => 'decimal(15,5) NOT NULL DEFAULT \'0.00000\'',
             'action' => 'char(255)',
             'comments' => 'varchar(255)',
             'referer' => 'varchar(255)',
             'tax_id' => 'smallint(1)',
             'rawOutput' => 'text'
         );
         return $SQLfields;
     }

     // Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
     // The plugin must check first if it is the correct type
     function plgVmOnCheckAutomaticSelectedPayment(VirtueMartCart $cart, array $cart_prices = array(), &$paymentCounter)
     {
         return $this->onCheckAutomaticSelected($cart, $cart_prices, $paymentCounter);
     }

     // This method is fired when showing the order details in the frontend.
     // It displays the method-specific data.
     public function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name)
     {
         $this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
     }

     // This method is fired when showing when priting an Order
     // It displays the the payment method-specific data.
     function plgVmonShowOrderPrintPayment($order_number, $method_id)
     {
         return $this->onShowOrderPrint($order_number, $method_id);
     }

     function plgVmDeclarePluginParamsPaymentVM3( &$data)
     {
         return $this->declarePluginParams('payment', $data);
     }

     function plgVmSetOnTablePluginParamsPayment($name, $id, &$table)
     {
         return $this->setOnTablePluginParams($name, $id, $table);
     }


     public function getVmPluginCreateTableSQL()
     {
         return $this->createTableSQL('CheckoutApi Table');
     }


     public function plgVmDisplayListFEPayment (VirtueMartCart $cart, $selected = 0, &$htmlIn)
     {

         if(parent::displayListFE($cart,$selected,$htmlIn)) {
              $this->getInstance()->plgVmDisplayListFEPayment($cart, $selected, $htmlIn, $this);
             return true;
         }else {
             return false;
         }

     }

     protected function checkConditions ($cart, $method, $cart_prices)
     {

         $this->convert_condition_amount($method);
         $amount = $this->getCartAmount($cart_prices);
         $address = (($cart->ST == 0) ? $cart->BT : $cart->ST);


         //vmdebug('standard checkConditions',  $amount, $cart_prices['salesPrice'],  $cart_prices['salesPriceCoupon']);
         $amount_cond = ($amount >= $method->min_amount AND $amount <= $method->max_amount
             OR
             ($method->min_amount <= $amount AND ($method->max_amount == 0)));
         if (!$amount_cond) {
             return FALSE;
         }
         $countries = array();
         if (!empty($method->countries)) {
             if (!is_array ($method->countries)) {
                 $countries[0] = $method->countries;
             } else {
                 $countries = $method->countries;
             }
         }

         // probably did not gave his BT:ST address
         if (!is_array ($address)) {
             $address = array();
             $address['virtuemart_country_id'] = 0;
         }

         if (!isset($address['virtuemart_country_id'])) {
             $address['virtuemart_country_id'] = 0;
         }
         if (count ($countries) == 0 || in_array ($address['virtuemart_country_id'], $countries) ) {
             return TRUE;
         }

         return FALSE;
     }

     public function getPsType()
     {
         return $this->_psType;
     }

     public function getRenderPluginName($currentMethod)
     {
         return $this->renderPluginName($currentMethod);
     }

     public function pluginHtml($currentMethod,$selected,$methodSalesPrice)
     {
         return $this->getPluginHtml($currentMethod,$selected,$methodSalesPrice);
     }



 }