<?php 
final class CheckoutApi_Api 
{
	private static $_apiClass = 'CheckoutApi_Client_ClientGW3';
    //private static $_stateException = null;

    public static function getApi(array $arguments = array(),$_apiClass = null)
    {
    	if($_apiClass) {
    		self::setApiClass($_apiClass);
    	}
    	return CheckoutApi_Lib_Factory::getSingletonInstance(self::getApiClass(),$arguments);
    }

    public static function setApiClass($apiClass)
    {
    	self::$_apiClass = $apiClass;
    }

    public static function getApiClass()
    {
    	return self::$_apiClass;
    }
}