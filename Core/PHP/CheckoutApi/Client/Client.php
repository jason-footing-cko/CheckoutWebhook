<?php 

/**
* An abstract class for CheckoutApi_Client gateway api 
*
*@package api
**/

 abstract class CheckoutApi_Client_Client  extends CheckoutApi_Lib_Object
 {
 	/**
 	 * Uri to where request should be made 
 	 *
 	 **/
 	protected $_uri = null;

     /**
 	* Hold headers that should be pass to api 
 	* 
 	* @var array
 	**/

 	protected $_headers = array ();

    /**
     * Type of adapter to be called
     * 
     **/
    protected $_processType = "curl";

    /**
     * Type of respond expecting from the server
     * 
     **/
    protected $_respondType = CheckoutApi_Parser_Constant::API_RESPOND_TYPE_JSON;

    /**
     * Hold an instance of CheckoutApi_Parser_Parser 
     * 
     **/

    protected $_parserObj = null;

    public function __construct(array $config = array())
    {

        parent::setConfig($config);
        $this->initParser($this->getRespondType());
    }

 	/**
     * Set/Get attribute wrapper
     *
     * @param   string $method
     * @param   array $args
     * @return  mixed
     */

    public function __call($method, $args)
    { 
        switch (substr($method, 0, 3)) {
            case 'get' :
                
                $key = substr($method,3);
                $key = lcfirst($key);
                $data = $this->getConfig($key, isset($args[0]) ? $args[0] : null);
                
                return $data;
           
        }

       $this->exception("Api does not support this method " .$method."(".print_r($args,1).")", debug_backtrace());
        return null;
    }

    public function getAdapter($adapterName,  $arguments = array())
    {
        $stdName = ucfirst($adapterName);

        $classAdapterName = CheckoutApi_Client_Constant::ADAPTER_CLASS_GROUP.$stdName;
        
        $class = null;

        if (class_exists($classAdapterName)) {
            /** @var CheckoutApi_Client_Adapter_Abstract  $class */
            $class = CheckoutApi_Lib_Factory::getSingletonInstance($classAdapterName,$arguments);
            if(isset($arguments['uri'])) {
                $class->setUri($arguments['uri']);
            }

            if(isset($arguments['config'])) {
                $class->setConfig($arguments['config']);
            }

        } else {
          
            $this->exception("Not a valid Adapter", debug_backtrace());
        } 

        return $class;
        
    }

    public function getParser()
    {
        return $this->_parserObj;
    }
    public function setParser($parser)
    {
        $this->_parserObj = $parser;
        //$this->setHeaders($parser->getHeaders());
    }

    public function setHeaders($headers) 
    {

       if(!$this->_parserObj) {
           $this->initParser($this->getRespondType());

        }

        /** @var array  _headers */
        $this->_headers = $this->getParser()->getHeaders();
        $this->_headers = array_merge($this->_headers,$headers);
    }

    public function getHeaders() 
    {
        return $this->_headers ;
    }

    public function setProcessType($processType) 
    {
        $this->_processType = $processType;
    }

    public function getProcessType() 
    {
        return $this->_processType ;
    }

    public function getRespondType()
    {
        $_respondType = $this->_respondType;
        if($respondType = $this->getConfig('respondType')) {
            $_respondType  =  $respondType;
        }

        return $_respondType;
    }

    public function initParser()
    {
        $parserType = CheckoutApi_Client_Constant::PARSER_CLASS_GROUP.$this->getRespondType(); 

        $parserObj =  CheckoutApi_Lib_Factory::getSingletonInstance($parserType) ;
        $this->setParser($parserObj);       
    }

    public function setUri($uri)
    {
        $this->_uri = $uri;
    }

     public function getUri()
    {
        return $this->_uri;
    }

 }
