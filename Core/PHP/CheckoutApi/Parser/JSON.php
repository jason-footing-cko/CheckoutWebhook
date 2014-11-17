<?php
class CheckoutApi_Parser_JSON extends CheckoutApi_Parser_Parser 
{
	protected $_headers = array ('Content-Type: application/json;charset=UTF-8','Accept: application/json');

	public function parseToObj($parser)
	{
		$to_return = null;

		if($parser && is_string ($parser)) {
			$encoding = mb_detect_encoding($parser);
			
			if($encoding =="ASCII") {
				$parser = iconv('ASCII', 'UTF-8', $parser);
			}else {
				$parser =  mb_convert_encoding($parser, "UTF-8", $encoding);
			}
			
			$jsonObj = json_decode($parser,true);
			$jsonObj['rawOutput'] = $parser;
			/** @var CheckoutApi_Lib_RespondObj $respondObj */
			$respondObj = CheckoutApi_Lib_Factory::getInstance('CheckoutApi_Lib_RespondObj');
			$respondObj->setConfig($jsonObj);
			$to_return = $respondObj;
		}

		return $to_return;
	}


	public function preparePosted($postedparam)
	{
		return json_encode($postedparam);
	}
}