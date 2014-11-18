<?php 
class CheckoutApi_Lib_RespondObj 
{

    protected $_config = array();
    
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
     * @param array $config
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
     * @return mixed
     * @throws Exception
     */

    public function isValid()
    {
         /** @var CheckoutApi_Lib_ExceptionState $exceptionState */
         $exceptionState = CheckoutApi_Lib_Factory::getSingletonInstance('CheckoutApi_Lib_ExceptionState');

         return $exceptionState->isValid();
    }

    public function printError()
    {
         /** @var CheckoutApi_Lib_ExceptionState $exceptionState */

          $exceptionState = CheckoutApi_Lib_Factory::getSingletonInstance('CheckoutApi_Lib_ExceptionState');
          $exceptionState->debug();
          $exceptionState->flushState();
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

    public function toArray()
    {
        
        return $this->getConfig();
    }
    
}