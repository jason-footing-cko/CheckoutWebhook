<?php 
final class CheckoutApi_Lib_Validator extends CheckoutApi_lib_Object
{

	public static function isEmpty($var) 
	{	
		$toReturn = false;

		if(is_array($var) && empty($var)) {
			$toReturn = true;
		}

		if(is_string($var) && ($var =='' || is_null($var))) {
			$toReturn = true;
		}

		if(is_integer($var) && ($var =='' || is_null($var)) ) {
			$toReturn = true;
		}

		if(is_float($var) && ($var =='' || is_null($var)) ) {
			$toReturn = true;
		}

		return $toReturn;
	} 	


	public static function isInteger($int) 
	{
		return is_integer($int);
	}

	public static function isString($string)
	{
		return is_string($string);
	}

	public static function isFloat($string)
	{
		return is_float($string);
	}

	public static function isValidEmail($email) 
	{
		$emailReg = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/";
		return preg_match ($emailReg,$email);
	}

	public static function isLength($var, $length)
	{
		

		if(is_array($var)  ) {
			return sizeof($var) == $length;

		} else {
			return strlen($var) == $length;

		}

	}

	public static function isValidCvv2Len($cvv2)
	{
		$pattern = '/^[0-9]{3,4}$/';
		return preg_match($pattern, $cvv2);
	}
}