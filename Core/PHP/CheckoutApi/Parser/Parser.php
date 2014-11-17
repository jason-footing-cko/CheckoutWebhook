<?php 

abstract class CheckoutApi_Parser_Parser extends CheckoutApi_Lib_Object 
{
	protected $_headers = array();

	protected $_respondObj = null;


	abstract public function parseToObj($parser);

	public function setRespondObj($obj)
	{
		$this->_respondObj = $obj;
	}

	public function getRespondObj()
	{
		return $this->_respondObj ;
	}

	public function getHeaders()
	{
		return $this->_headers;
	}

	abstract public function preparePosted($postedParam);
} 