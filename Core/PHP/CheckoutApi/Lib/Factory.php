<?php 
/**
 * Class to create factory object
 */

final class CheckoutApi_Lib_Factory extends CheckoutApi_Lib_Object
{

	/**
	* Registry collection
	*
	* @var array
	*/

	static private $_registry = array();

	public static function getInstance($className) 
	{
		return new $className;
	}
	
	/**
    * The singleton method
    *
    **/ 
       
    public static function getSingletonInstance($className, array $arguments = array())
    {	
    	
    	$registerKey = $className;
    
        if (!isset(self::$_registry[$registerKey])) {

        	if(class_exists($className)) {
            	self::$_registry[$registerKey] = new $className($arguments);
            	
        	}else {
 
        		throw new Exception ('Invalid class name:: ' .$className."(".print_r($arguments,1).')');
        	}
        }


        return  self::$_registry[$registerKey];
    }
	

}