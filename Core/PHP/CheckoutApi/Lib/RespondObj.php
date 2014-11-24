<?php

/**
 *  CheckoutApi_Lib_RespondObj
 * This class is responsible of mapping anytime of respond into an object with attribute and magic getters
 * @package     CheckoutApi
 * @category     Api
 * @author       Dhiraj Gangoosirdar <dhiraj.gangoosirdar@checkout.com>
 * @copyright 2014 Integration team (http://www.checkout.com)
 */
class CheckoutApi_Lib_RespondObj 
{
    /** @var array $_config configuration value */

    protected $_config = array();

    /**
     * A method that caputer all getter or setters and use them to either set or get value from attribute config
     * @param $method
     * @param $args
     * @throws Exception
     * CheckoutApi_ a php magical method
     * @example http://php.net/manual/en/language.oop5.overloading.php#object.call
     */

	public function __call($method, $args)
    { 
        switch (substr($method, 0, 3)) {
            case 'get' :
                
                $key = substr($method,3);
                $key = lcfirst($key);
                $data = $this->getConfig($key, isset($args[0]) ? $args[0] : null);
                
                return $data;
            break;
            case 'has';
                $key = substr($method,3);
                $key = lcfirst($key);
                $data = $this->getConfig($key);

            return $data?true:false;

        }

       throw new Exception("Respond does not support this method " .$method."(".print_r($args,1).")");
    }

    /**
     * This method return value from the attribute config
     * @param null $key attribute you want to retrive
     * @return array|CheckoutApi_Lib_RespondObj|null
     * @throws Exception
     */
   private function getConfig($key = null) 
    {	
    	if($key!=null && isset($this->_config[$key])) { 
    		
    		$value = $this->_config[$key];
    		
    		if(is_array($value)) {
                /** @var CheckoutApi_Lib_RespondObj $to_return */
    			$to_return = CheckoutApi_Lib_Factory::getInstance('CheckoutApi_Lib_RespondObj');
    			$to_return->setConfig( $value);
    			return $to_return;
    		}
    		return $value;
    	}
        if($key == null) {
            return $this->_config;
        }
        return null;
    }

    /**
     * This method set the config value for an object
     * @param array $config configuration to be set
     * @throws Exception
     */

    public function setConfig($config = array()) 
    { 

    	if(is_array($config) ) {

    		if(!empty($config)) {
    			foreach($config as $key=>$value) {
    				
    				if(!isset($this->_config[$key])){
    					$this->_config[$key] = $value;
    				}
    			}
    		}
    		
     	} else {
    		
    		throw new Exception( "Invalid parameter"."(".print_r($config,1).")");
    	}

    }

    /**
     * check if respond obj is valid
     * @return boolean
     * @throws Exception
     */

    public function isValid()
    {
         /** @var CheckoutApi_Lib_ExceptionState $exceptionState */
         $exceptionState = CheckoutApi_Lib_Factory::getSingletonInstance('CheckoutApi_Lib_ExceptionState');

         return $exceptionState->isValid();
    }

    /**
     * Print all error log by the CheckoutApi_Lib_ExceptionState object for the current request
     * @throws Exception
     * CheckoutApi_ print the error
     */

    public function printError()
    {
         /** @var CheckoutApi_Lib_ExceptionState $exceptionState */

          $exceptionState = CheckoutApi_Lib_Factory::getSingletonInstance('CheckoutApi_Lib_ExceptionState');
          $exceptionState->debug();
          $exceptionState->flushState();
    }

    /**
     * Return an instance of CheckoutApi_Lib_ExceptionState
     * @return CheckoutApi_Lib_ExceptionState|null
     * @throws Exception
     *
     */
    public function getExceptionState()
    {
        $classException = "CheckoutApi_Lib_ExceptionState";
        $class = null;
        if (class_exists($classException)) {
            /** @var CheckoutApi_Lib_ExceptionState $class */
            $class = CheckoutApi_Lib_Factory::getSingletonInstance($classException);

        }

        return $class;
    }

    /**
     * Return all configuration value for an object
     * @return config value
     */

    public function toArray()
    {
        
        return $this->getConfig();
    }
    
}