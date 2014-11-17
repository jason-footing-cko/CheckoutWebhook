<?php 
/**
 * A small utility class to wrap some of php function
 * 
 * @package api
 **/

final class CheckoutApi_Utility_Utilities 
{
	public static function checkExtension( $extension)
	{
		
		return extension_loaded($extension); 
	}

	public static function dump($toPrint)
	{
		echo '<pre>';
			var_dump($toPrint);
		echo '</pre>';
	}
}