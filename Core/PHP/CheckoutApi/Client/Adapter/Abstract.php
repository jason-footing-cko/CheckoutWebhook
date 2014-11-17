<?php 
/**
* An abstract class for CheckoutApi_Client adapters
*
*@package api
**/

abstract class CheckoutApi_Client_Adapter_Abstract extends CheckoutApi_Lib_Object 
{
	/**
	* server identifier
	*
	* @var string
	**/

	protected $_uri = null;

	/**
	* The server session handler
	*
	* @var resource|null
	**/
	
	protected $_resource = null;
    
    /**
    * Respond return by the server
    *
    * @var mixed
    */
   
    protected $_respond = null;


    /**
     *
     * Constructor for Adapters
     * @param array $arguments
     * @throws Exception
     */

    public function __construct ( array $arguments = array() ) 
    { 
        if(isset($arguments['uri']) && $uri = $arguments['uri'] ) {
            $this->setUri($uri);
        }
    	
        if(isset($arguments['config']) && $config = $arguments['config'] ) {

    	   $this->setConfig($arguments['config']);
        }
   
    }


     /**
     * Set/Get attribute wrapper
     *
     * @param   string $method
     * @param   array $args
      *
      * @return mixed
     */

    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get' :
                
                $key = substr($method,3);
                $key = lcfirst($key);
                $data = $this->getConfig($key, isset($args[0]) ? $args[0] : null);
                
                return $data;

            case 'set' :
                
                $key =substr($method,3);
                $key = lcfirst($key);
                $result = $this->setConfig($key, isset($args[0]) ? $args[0] : null);
      
                return $result;

           
        }

       //throw new Exception("Invalid method ".get_class($this)."::".$method."(".print_r($args,1).")");

       $this->exception("Invalid method ".get_class($this)."::".$method."(".print_r($args,1).")",
                         debug_backtrace());

        return null;
    }

    /**
    * setter for $_uri
    * 
    * @var string
    **/

    public function setUri($uri)
    { 

    	$this->_uri = $uri;
    }

    /**
     * Getter for $_uri
     * 
     * @return string
     **/

    public function getUri()
    {
    	return $this->_uri;
    }

    /**
     * Setter for $_resource
     * 
     * @var resource
     **/

    public function setResource($resource) 
    {
    	$this->_resource = $resource;
    }


    /**
     * Getter for $_resource
     * 
     * @return resource
     **/

    public function getResource()
    {
    	return $this->_resource;
    }

    /**
     * Setter for respond
     * 
     * @var mixed
     * 
     **/

    public function setRespond($respond)
    {
    	$this->_respond = $respond;
    }

    /**
     * Getter for respond
     * 
     * @return mixed
     * 
     **/
     
    public function getRespond()
    {
    	return $this->_respond;
    }

    public function connect() 
    {
        return $this;
    }

    public function close()
    {
        $this->setResource(null);
        $this->setRespond(null);

    }


    /**
     * @return  CheckoutApi_Lib_RespondObj
     */
    abstract function request();


}