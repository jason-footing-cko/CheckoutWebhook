<?php 

class CheckoutApi_Lib_Object  {

    /** 
	* Parameters array
	*
	* @var array
    **/

    protected $_config = array();


    /**
    * Return parameter value
    *
    * @var $key string
    *
    * @return  mixed
    **/

    public function getConfig($key = null) 
    {	
    	if($key!=null && isset($this->_config[$key])) { 
    		
    		return $this->_config[$key];
    	
    	} elseif($key == null) {
    		
            return $this->_config;
    	}

        return null;
    }

   /**
    * Set parameter $_config value
    *
    * @var array
    *
    * @return mixed
    * 
    **/

    public function setConfig($config = array()) 
    { 

    	if(is_array($config) ) {

    		if(!empty($config)) {
    			foreach($config as $key=>$value) {

    				$this->_config[$key] = $value;
    			}
    		}

     	} else {
    		
    		throw new Exception( "Invalid parameter");
    	}

    }

    public function resetConfig()
    {
        $this->_config = array();
        return $this;
    }

    /**
     * setting and logging error message
     * @param $errorMsg
     * @param array $trace
     * @param bool $error
     * @return mixed
     * @throws Exception
     */

    public function exception($errorMsg,  array $trace, $error = true )
    {
        $classException = "CheckoutApi_Lib_ExceptionState";

        if (class_exists($classException)) {

            /** @var CheckoutApi_Lib_ExceptionState $class */
            $class = CheckoutApi_Lib_Factory::getSingletonInstance($classException);
              
        } else {
            
            throw new Exception("Not a valid class ::  CheckoutApi_Lib_ExceptionState");
            
        } 

        $class->setLog($errorMsg,$trace,$error);

        return $class;
        
    }

    public function flushState()
    {
        $classException = "CheckoutApi_Lib_ExceptionState";

        if (class_exists($classException)) {
            /** @var CheckoutApi_Lib_ExceptionState $class */
            $class = CheckoutApi_Lib_Factory::getSingletonInstance($classException);
              
        } else {
            
            throw new Exception("Not a valid class ::  CheckoutApi_Lib_ExceptionState");
            
        } 
        $class->flushState();

    }

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
}