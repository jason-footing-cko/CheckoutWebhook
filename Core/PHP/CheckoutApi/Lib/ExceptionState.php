<?php 
 class CheckoutApi_Lib_ExceptionState extends CheckoutApi_Lib_Object
{
	private $_errorState = false;
	private $_trace = array();
	private $_message = array();
	private $_critical = array();
	private $_debug = false;

	public function __construct()
	{
        if(isset($_SERVER['API_CHECKOUT_DEBUG'])) {
          $this->_debug = $_SERVER['API_CHECKOUT_DEBUG'] == "true" ? true : false;
        }
        $this->_debug  = true;
	}

	private function setErrorState($state)
	{
		if(!$this->_errorState){
			$this->_errorState = $state;
		}
		
	}

	private function getErrorState()
	{
		return $this->_errorState;
	}

	public function hasError()
	{
		return $this->getErrorState();
	}
	public function isValid()
	{
		return !$this->getErrorState();
	}

	public function setTrace($trace)
	{
		$this->_trace[] = $trace;
	}
	public function getTrace()
	{
		return $this->_trace;
	}

	public function setMessage($message)
	{
		return $this->_message[] = $message;
	}

	public function getMessage()
	{
		return $this->_message;
	}

     public function getErrorMessage()
     {
         $message = $this->getMessage();
         $critical = $this->getCritical();
         $msgError = "";
         for($i= 0, $count = sizeOf($message); $i<$count;$i++ ) {

             if ($critical[$i]) {
                 $msgError .= "{$message[$i]}\n";
             }
         }

         return $msgError;
     }

	public function setCritical($critical)
	{
		return $this->_critical[] = $critical;
	}

	public function getCritical()
	{
		return $this->_critical;
	}

	/**
	 * set error state of object. we can have an error but still proceed 
	 * 
	 * @var string $error
	 * @var array $trace
	 * @var boolean $state
	 * 
	 * 
	 **/

	public function setLog($error,$trace, $state = true)
	{

		$this->setErrorState($state);
		$this->setTrace($trace);
		$this->setMessage($error);
		$this->setCritical($state);
	}

	public function debug()
	{
	
		if($this->_debug && $this->hasError() ){
			$message = $this->getMessage();
			$trace = $this->getTrace();
			$critical = $this->getCritical();

			for($i= 0, $count = sizeOf($message); $i<$count;$i++ ) {

				if($critical[$i]){
					echo '<strong style="color:red">';
				} else  {
					continue;
				}

				CheckoutApi_Utility_Utilities::dump($message[$i] .'==> { ');
				
				foreach($trace[$i] as $errorIndex => $errors) {
					echo "<pre>";
						echo  $errorIndex ."=>  "; var_dump($errors);
						
					echo "</pre>";
				}
				
				if($critical[$i])	{
					echo '</strong>';
				}
				
				CheckoutApi_Utility_Utilities::dump('} ');
				
			}
			
		}
		
	}

	public function flushState()
	{
		$this_errorState = false;
		$this_trace = array();
		$this_message = array();
		$this_critical = array();
	}


}