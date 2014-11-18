<?php 
/**
* @package api
* This class is used as connectors for API_CheckoutApi_Client, performing the
	* tasks of connecting, reading and closing connection to the server.
**/
interface CheckoutApi_Client_Adapter_Interface 
{
	/**
	* Read respond on the server
	* 
	* @return object
	**/

	public function request();
    
    /**
    * Close all open connections and release all set variables
    **/

	public function close();

    /**
    * Open a connection to server/URI
    * @return resource
    **/

	public function connect();

	/**
    * Set parameter $_config value
    *
    * @var array
    *
    * @return mixed
    * 
    **/

	public function setConfig($array = array());

	/**
    * Return parameter value
    *
    * @var $key string
    *
    * @return  mixed
    **/

	public function getConfig($key = null);
}