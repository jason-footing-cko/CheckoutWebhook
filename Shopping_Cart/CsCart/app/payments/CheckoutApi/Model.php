<?php
 class Model
{

     private $_instance;

     public function getInstance($setting = array())
     {
        if(!$this->_instance) {

            switch($setting['server_type']) {
                case 'yes':
                    $this->_instance = CheckoutApi_Lib_Factory::getInstance('model_methods_creditcardpci');
                break;

                default :
                    $this->_instance =  CheckoutApi_Lib_Factory::getInstance('model_methods_creditcard');
                    break;
            }
        }
        return $this->_instance;
     }

     public function before_process()
     {

         $this->getInstance()->before_process();
     }

     public function after_process()
     {
         $this->getInstance()->after_process();
     }

 }