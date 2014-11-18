<?php 
class CheckoutApi_Client_ClientGW3 extends CheckoutApi_Client_Client
{
	/**
	 *@var string to store uri for charge url
	 **/

	protected $_uriCharge = null;

	/**
	 *@var string to store uri for token url
	 **/

    protected $_uriToken = null;

	/**
	 *@var string to store uri for customer url
	 **/

    protected $_uriCustomer = null;

	/**
	 *@var string to store uri for customer url
	 **/

    protected $_uriProvider = null;

	private $_mode = 'dev';


	public function __construct(array $config = array())
	{

		parent::__construct($config);

		if($mode = $this->getMode()) {
			$this->setMode($mode);
		}

		$this->setUriCharge();
        $this->setUriToken();
        $this->setUriCustomer();
        $this->setUriProvider();
	}

    /**
     * Create Card Token
     * @param array $param
     * @return CheckoutApi_Lib_RespondObj object
     * @throws Exception
     */

    public function getCardToken(array $param)
    {
        $hasError = false;
        $param['postedParam']['type'] = CheckoutApi_Client_Constant::TOKEN_CARD_TYPE;
        $postedParam = $param['postedParam'];
        $this->flushState();
        $isEmailValid = CheckoutApi_Client_Validation_GW3::isEmailValid($postedParam);
        $isCardValid = CheckoutApi_Client_Validation_GW3::isCardValid($postedParam);
       
        $uri = $this->getUriToken();

        if(!$isEmailValid) {
            $hasError =  true;
            $this->throwException('Please provide a valid Email address', array('pram'=>$param));
        }

        if(!$isCardValid) {
            $hasError =  true;
            $this->throwException('Please provide a valid card object', array('pram'=>$param));
        }

        return $this->request( $uri ,$param,!$hasError);
    }

    /**
     * Create session token
     * @param array $param
     * @return mixed
     * @throws Exception
     */

    public  function  getSessionToken(array $param)
    {
        $hasError = false;
        $param['postedParam']['type'] = CheckoutApi_Client_Constant::TOKEN_SESSION_TYPE;
        $postedParam = $param['postedParam'];
        $this->flushState();
        $isAmountValid = CheckoutApi_Client_Validation_GW3::isAmountValid($postedParam);
        $isCurrencyValid = CheckoutApi_Client_Validation_GW3::isValidCurrency($postedParam);
        $uri = $this->getUriToken();

        if(!$isAmountValid) {
            $hasError =  true;
            $this->throwException('Please provide a valid amount (in cents)', array('pram'=>$param));
        }

        if(!$isCurrencyValid) {
            $hasError =  true;
            $this->throwException('Please provide a valid currency code (ISO currency code)', array('pram'=>$param));
        }

        return $this->request( $uri ,$param,!$hasError);
    }

    /**
     * Create Charge
     * @param array $param
     * @return mixed
     * @throws Exception
     */
    public function createCharge(array $param)
    {
        $hasError = false;
        $param['postedParam']['type'] = CheckoutApi_Client_Constant::CHARGE_TYPE;
        $postedParam = $param['postedParam'];
        $this->flushState();
        $isAmountValid = CheckoutApi_Client_Validation_GW3::isAmountValid($postedParam);
        $isCurrencyValid = CheckoutApi_Client_Validation_GW3::isValidCurrency($postedParam);
        $isEmailValid = CheckoutApi_Client_Validation_GW3::isEmailValid($postedParam);
        $isCustomerIdValid = CheckoutApi_Client_Validation_GW3::isCustomerIdValid($postedParam);
        $isCardValid = CheckoutApi_Client_Validation_GW3::isCardValid($postedParam);
        $isCardIdValid = CheckoutApi_Client_Validation_GW3::isCardIdValid($postedParam);
        $isCardTokenValid = CheckoutApi_Client_Validation_GW3::isCardToken($postedParam);
        $uri = $this->getUriCharge();

        if(!$isEmailValid && !$isCustomerIdValid) {
            $hasError =  true;
            $this->throwException('Please provide a valid Email address or Customer id', array('param'=>$postedParam));
        }

        if($isCardTokenValid) {
            if(isset($postedParam['card'])) {
                $this->throwException('unset card object', array('param'=>$postedParam),false);
                unset($param['postedParam']['card']);
            }

        }elseif($isCardValid) {

            if(isset($postedParam['token'])){
                $this->throwException('unset invalid token object', array('param'=>$postedParam),false);
                unset($param['postedParam']['token']);
            }

        }elseif($isCardIdValid) {

            if(isset($postedParam['token'])){
                $this->throwException('unset invalid token object', array('param'=>$postedParam),false);
                unset($param['postedParam']['token']);
            }

            if(isset($postedParam['card'])){
                $this->throwException('unset invalid token object', array('param'=>$postedParam),false);

                if(isset($param['postedParam']['card']['name'])) {
                    unset($param['postedParam']['card']['name']);
                }

                if(isset($param['postedParam']['card']['number'])) {
                    unset($param['postedParam']['card']['number']);
                }

                if(isset($param['postedParam']['card']['expiryMonth'])) {
                    unset($param['postedParam']['card']['expiryMonth']);
                }

                if(isset($param['postedParam']['card']['expiryYear'])) {
                    unset($param['postedParam']['card']['expiryYear']);
                }


            }else {
                $hasError =  true;
                $this->throwException('Please provide  either a valid card token or a card object or a card id', array('pram'=>$param));
            }
        }

        if(!$isAmountValid) {
            $hasError =  true;
            $this->throwException('Please provide a valid amount (in cents)', array('pram'=>$param));
        }

        if(!$isCurrencyValid) {
            $hasError =  true;
            $this->throwException('Please provide a valid currency code (ISO currency code)', array('pram'=>$param));
        }

        return $this->request( $uri ,$param,!$hasError);
    }

    /**
     * Refund  Charge
     * @param $param
     * @return mixed
     * @throws Exception
     */

    public function  refundCharge($param)
    {
        $hasError = false;
        $param['postedParam']['type'] = CheckoutApi_Client_Constant::CHARGE_TYPE;
        $postedParam = $param['postedParam'];
        $this->flushState();
        $isAmountValid = CheckoutApi_Client_Validation_GW3::isAmountValid($postedParam);
        $isChargeIdValid = CheckoutApi_Client_Validation_GW3::isChargeIdValid($param);
        $uri = $this->getUriCharge();

        if(!$isChargeIdValid) {
            $hasError = true;
            $this->throwException('Please provide a valid charge id',array('param'=>$param));

        } else {

            $uri = "$uri/{$param['chargeId']}/refund";
        }
         if(!$isAmountValid) {
             $this->throwException('Please provide a amount (in cents)',array('param'=>$param),false);
         }

        return $this->request( $uri ,$param,!$hasError);
    }

    /**
     * Capture   Charge
     * @param $param
     * @return mixed
     * @throws Exception
     */

    public function  captureCharge($param)
    {
        $hasError = false;
        $param['postedParam']['type'] = CheckoutApi_Client_Constant::CHARGE_TYPE;
        $postedParam = $param['postedParam'];
        $this->flushState();
        $isAmountValid = CheckoutApi_Client_Validation_GW3::isAmountValid($postedParam);
        $isChargeIdValid = CheckoutApi_Client_Validation_GW3::isChargeIdValid($param);
        $uri = $this->getUriCharge();

        if(!$isChargeIdValid) {
            $hasError = true;
            $this->throwException('Please provide a valid charge id',array('param'=>$param));

        } else {

            $uri = "$uri/{$param['chargeId']}/capture";
        }
        if(!$isAmountValid) {
            $this->throwException('Please provide a amount (in cents)',array('param'=>$param),false);
        }

        return $this->request( $uri ,$param,!$hasError);
    }

    /**
     * Capture   Charge
     * @param $param
     * @return mixed
     * @throws Exception
     */

    public function  updateCharge($param)
    {
        $hasError = false;
        $param['postedParam']['type'] = CheckoutApi_Client_Constant::CHARGE_TYPE;
        $param['method'] = CheckoutApi_Client_Adapter_Constant::API_PUT;

        $this->flushState();

        $isChargeIdValid = CheckoutApi_Client_Validation_GW3::isChargeIdValid($param);
        $uri = $this->getUriCharge();

        if(!$isChargeIdValid) {
            $hasError = true;
            $this->throwException('Please provide a valid charge id',array('param'=>$param));

        } else {

            $uri = "$uri/{$param['chargeId']}";
        }

        return $this->request( $uri ,$param,!$hasError);
    }

    public function createLocalPaymentCharge($param)
    {
        $hasError = false;
        $param['postedParam']['type'] = CheckoutApi_Client_Constant::LOCALPAYMENT_CHARGE_TYPE;
        $postedParam = $param['postedParam'];
        $this->flushState();
        $uri = $this->getUriCharge();
        $isValidEmail = CheckoutApi_Client_Validation_GW3::isEmailValid($postedParam);
        $isValidSessionToken = CheckoutApi_Client_Validation_GW3::isSessionToken($postedParam);
        $isValidLocalPaymentHash = CheckoutApi_Client_Validation_GW3::isLocalPyamentHashValid($postedParam);
        $param['method'] = CheckoutApi_Client_Adapter_Constant::API_POST;
        if(!$isValidEmail){
            $hasError = true;
            $this->throwException('Please provide a valid email address',array('postedParam'=>$postedParam));
        }

        if(!$isValidSessionToken){
            $hasError = true;
            $this->throwException('Please provide a valid session token',array('postedParam'=>$postedParam));
        }

        if(!$isValidLocalPaymentHash){
            $hasError = true;
            $this->throwException('Please provide a local payment hash',array('postedParam'=>$postedParam));
        }

        if(!isset($param['postedParam']['localPayment']['userData']) ) {
            $param['postedParam']['localPayment']['userData'] = '{}';
        }
        return $this->request( $uri ,$param,!$hasError);
    }
    /**
     * Create a customer
     * @param $param
     * @return mixed
     * @throws Exception
     */

    public  function createCustomer($param)
    {
        $hasError = false;
        $param['method'] = CheckoutApi_Client_Adapter_Constant::API_POST;
        $postedParam = $param['postedParam'];
        $this->flushState();
        $uri = $this->getUriCustomer();
        $isValidEmail = CheckoutApi_Client_Validation_GW3::isEmailValid($postedParam);
        $isCardValid = CheckoutApi_Client_Validation_GW3::isCardValid($postedParam);
        $isTokenValid = CheckoutApi_Client_Validation_GW3::isCardToken($postedParam);

        if(!$isValidEmail) {
            $hasError = true;
            $this->throwException('Please provide a valid Email Address',array('param'=>$param));
        }

        if($isTokenValid) {
            if(isset($postedParam['card'])) {
                $this->throwException('unsetting card object',array('param'=>$param),false);
                unset($param['postedParam']['card']);
            }
        }elseif($isCardValid) {
            if(isset($postedParam['token'])){
                $this->throwException('unsetting token ',array('param'=>$param),false);
                unset($param['postedParam']['token']);
            }
        }else {
            $hasError = true;
            $this->throwException('Please provide a valid card detail or card token',array('param'=>$param));
        }

        return $this->request( $uri ,$param,!$hasError);
    }


    /**
     * Update Customer
     * @param $param
     * @return mixed
     * @throws Exception
     */
    public  function getCustomer($param)
    {
        $hasError = false;

        $param['method'] = CheckoutApi_Client_Adapter_Constant::API_GET;
        $this->flushState();
        $uri = $this->getUriCustomer();
        $isCustomerIdValid = CheckoutApi_Client_Validation_GW3::isCustomerIdValid($param);

        if(!$isCustomerIdValid) {
            $hasError = true;
            $this->throwException('Please provide a valid customer id',array('param'=>$param));
        }else {

            $uri = "$uri/{$param['customerId']}";
        }

        return $this->request( $uri ,$param,!$hasError);
    }

    /**
     * Update Customer
     * @param $param
     * @return mixed
     * @throws Exception
     */
    public  function updateCustomer($param)
    {
        $hasError = false;

        $param['method'] = CheckoutApi_Client_Adapter_Constant::API_PUT;
        $this->flushState();
        $uri = $this->getUriCustomer();
        $isCustomerIdValid = CheckoutApi_Client_Validation_GW3::isCustomerIdValid($param);

        if(!$isCustomerIdValid) {
            $hasError = true;
            $this->throwException('Please provide a valid customer id',array('param'=>$param));
        }else {

            $uri = "$uri/{$param['customerId']}";
        }

        return $this->request( $uri ,$param,!$hasError);
    }

    /**
     * Getting a list of customer
     * @param $param
     * @return mixed
     * @throws Exception
     */

    public function getListCustomer($param)
    {
        $hasError = false;

        $param['method'] = CheckoutApi_Client_Adapter_Constant::API_GET;
        $this->flushState();
        $uri = $this->getUriCustomer();
        $delimiter = '?';
        $createdAt = 'created=';

        if(isset($param['created_on'])) {
            $uri="{$uri}{$delimiter}{$createdAt}{$param['created_on']}|";
            $delimiter = '&';

        }else {
            if (isset($param['from_date'])) {
                $fromDate = time($param['from_date']);
                $uri = "{$uri}{$delimiter}{$createdAt}{$fromDate}";
                $delimiter = '&';
                $createdAt = '|';
            }

            if (isset($param['to_date'])) {
                $toDate = time($param['to_date']);
                $uri = "{$uri}{$createdAt}{$toDate}";
                $delimiter = '&';

            }
        }

        if(isset($param['count'])){

            $uri =  "{$uri}{$delimiter}count={$param['count']}";
            $delimiter = '&';
        }

        if(isset($param['offset'])){
            $uri =  "{$uri}{$delimiter}offset={$param['offset']}";
        }

        return $this->request( $uri ,$param,!$hasError);
    }

    /**
     * Delete a customer
     * @param $param
     * @return mixed
     * @throws Exception
     */

    public function deleteCustomer($param)
    {
        $param['method'] = CheckoutApi_Client_Adapter_Constant::API_DELETE;
        $this->flushState();
        $uri = $this->getUriCustomer();
        $hasError = false;
        $isCustomerIdValid = CheckoutApi_Client_Validation_GW3::isCustomerIdValid($param);
        if(!$isCustomerIdValid) {
            $hasError = true;
            $this->throwException('Please provide a valid customer id',array('param'=>$param));
        }else {

            $uri = "$uri/{$param['customerId']}";
        }

        return $this->request( $uri ,$param,!$hasError);
    }

    /**
     * Creating a card link to a customer
     * @param $param
     * @return mixed
     * @throws Exception
     */

    public function createCard($param)
    {

        $this->flushState();
        $uri = $this->getUriCustomer();
        $hasError = false;
        $postedParam = $param['postedParam'];
        $isCustomerIdValid = CheckoutApi_Client_Validation_GW3::isCustomerIdValid($param);
        $isCardValid = CheckoutApi_Client_Validation_GW3::isCardValid($postedParam);

        if(!$isCustomerIdValid) {
            $hasError = true;
            $this->throwException('Please provide a valid customer id',array('param'=>$param));
        }else {

            $uri = "$uri/{$param['customerId']}/cards";
        }

        if(!$isCardValid) {
            $hasError = true;
            $this->throwException('Please provide a valid card object',array('param'=>$param));
        }
        return $this->request( $uri ,$param,!$hasError);
    }

    /**
     * Update a card
     * @param $param
     * @return mixed
     * @throws Exception
     */

    public function updateCard($param)
    {
        $this->flushState();
        $uri = $this->getUriCustomer();
        $hasError = false;

      //  $param['method'] = CheckoutApi_Client_Adapter_Constant::API_PUT;
        $isCustomerIdValid = CheckoutApi_Client_Validation_GW3::isCustomerIdValid($param);
        $isCardIdValid = CheckoutApi_Client_Validation_GW3::isGetCardIdValid($param);

        if(!$isCustomerIdValid) {
            $hasError = true;
            $this->throwException('Please provide a valid customer id',array('param'=>$param));

        }elseif(!$isCardIdValid){
            $hasError = true;
            $this->throwException('Please provide a valid card id',array('param'=>$param));
        } else {

            $uri = "$uri/{$param['customerId']}/cards/{$param['cardId']}";
        }

        return $this->request( $uri ,$param,!$hasError);
    }

    /**
     * Get a card
     * @param $param
     * @return mixed
     * @throws Exception
     */

    public function getCard($param)
    {
        $this->flushState();
        $uri = $this->getUriCustomer();
        $hasError = false;
        $param['method'] = CheckoutApi_Client_Adapter_Constant::API_GET;
        $isCustomerIdValid = CheckoutApi_Client_Validation_GW3::isCustomerIdValid($param);
        $isCardIdValid = CheckoutApi_Client_Validation_GW3::isGetCardIdValid($param);

        if(!$isCustomerIdValid) {
            $hasError = true;
            $this->throwException('Please provide a valid customer id',array('param'=>$param));

        }elseif(!$isCardIdValid){
            $hasError = true;
            $this->throwException('Please provide a valid card id',array('param'=>$param));
        } else {

            $uri = "$uri/{$param['customerId']}/cards/{$param['cardId']}";
        }

        return $this->request( $uri ,$param,!$hasError);
    }

    /**
     * Get Card List
     * @param $param
     * @return mixed
     * @throws Exception
     */

    public function getCardList($param)
    {
        $this->flushState();
        $uri = $this->getUriCustomer();
        $hasError = false;
        $param['method'] = CheckoutApi_Client_Adapter_Constant::API_GET;
        $isCustomerIdValid = CheckoutApi_Client_Validation_GW3::isCustomerIdValid($param);

        if(!$isCustomerIdValid) {
            $hasError = true;
            $this->throwException('Please provide a valid customer id',array('param'=>$param));

        } else {

            $uri = "$uri/{$param['customerId']}/cards";
        }

        return $this->request( $uri ,$param,!$hasError);
    }

    /**
     * Get Card List
     * @param $param
     * @return mixed
     * @throws Exception
     */

    public function deleteCard($param)
    {
        $this->flushState();
        $uri = $this->getUriCustomer();
        $hasError = false;
        $param['method'] = CheckoutApi_Client_Adapter_Constant::API_DELETE;
        $isCustomerIdValid = CheckoutApi_Client_Validation_GW3::isCustomerIdValid($param);
        $isCardIdValid = CheckoutApi_Client_Validation_GW3::isGetCardIdValid($param);

        if(!$isCustomerIdValid) {
            $hasError = true;
            $this->throwException('Please provide a valid customer id',array('param'=>$param));

        }elseif(!$isCardIdValid){
            $hasError = true;
            $this->throwException('Please provide a valid card id',array('param'=>$param));
        } else {

            $uri = "$uri/{$param['customerId']}/cards/{$param['cardId']}";
        }

        return $this->request( $uri ,$param,!$hasError);
    }

    /**
     * Get LocalPayment Provider list
     * @param $param
     * @return mixed
     * @throws Exception
     */
    public function  getLocalPaymentList($param)
    {
        $this->flushState();
        $uri = $this->getUriProvider();
        $hasError = false;
        $isTokenValid = CheckoutApi_Client_Validation_GW3::isSessionToken($param);
        $param['method'] = CheckoutApi_Client_Adapter_Constant::API_GET;
        $delimiter = '/localpayments?';

        if(!$isTokenValid) {
            $hasError = true;
            $this->throwException('Please provide a valid session token',array('param'=>$param));
        }else {

            $uri = "{$uri}{$delimiter}token={$param['token']}";
            $delimiter ='&';

            if(isset($param['countryCode'])){
                $uri = "{$uri}{$delimiter}countryCode={$param['countryCode']}";
                $delimiter ='&';
            }

            if(isset($param['ip'])){
                $uri = "{$uri}{$delimiter}ip={$param['ip']}";
                $delimiter ='&';
            }

            if(isset($param['limit'])){
                $uri = "{$uri}{$delimiter}limit={$param['limit']}";
                $delimiter ='&';
            }

            if(isset($param['region'])){
                $uri = "{$uri}{$delimiter}region={$param['region']}";
                $delimiter ='&';
            }

            if(isset($param['name'])){
                $uri = "{$uri}{$delimiter}name={$param['name']}";

            }
        }

        return $this->request( $uri ,$param,!$hasError);
    }

    /**
     * Get LocalPayment Provider
     * @param $param
     * @return mixed
     * @throws Exception
     */
    public  function getLocalPaymentProvider($param)
    {
        $this->flushState();
        $uri = $this->getUriProvider();
        $hasError = false;
        $isTokenValid = CheckoutApi_Client_Validation_GW3::isSessionToken($param);
        $isValidProvider = CheckoutApi_Client_Validation_GW3::isProvider($param);
        $param['method'] = CheckoutApi_Client_Adapter_Constant::API_GET;
        $delimiter = '/localpayments/';

        if(!$isTokenValid) {
            $hasError = true;
            $this->throwException('Please provide a valid session token',array('param'=>$param));
        }

        if(!$isValidProvider)
        {
            $hasError = true;
            $this->throwException('Please provide a valid provider id',array('param'=>$param));
        }

        if(!$hasError){
            $uri = "{$uri}{$delimiter}{$param['providerId']}?token={$param['token']}";
        }

        return $this->request( $uri ,$param,!$hasError);
    }

    /**
     * Get Card Provider list
     * @param $param
     * @return mixed
     * @throws Exception
     */

    public function getCardProvidersList($param)
    {
        $this->flushState();
        $uri = $this->getUriProvider().'/cards';
        $hasError = false;
        return $this->request( $uri ,$param,!$hasError);
    }

    public function getCardProvider($param)
    {
        $this->flushState();
        $isValidProvider = CheckoutApi_Client_Validation_GW3::isProvider($param);
        $uri = $this->getUriProvider().'/cards';
        $hasError = false;
        if(!$isValidProvider)
        {
            $hasError = true;
            $this->throwException('Please provide a valid provider id',array('param'=>$param));
        }

        if(!$hasError){
            $uri = "{$uri}/{$param['providerId']}";
        }

        return $this->request( $uri ,$param,!$hasError);
    }
    /**
     * @param $uri
     * @param array $param
     * @param $state
     * @return mixed
     * @throws Exception
     */

    private function request($uri,array $param, $state)
    {

        /** @var CheckoutApi_Lib_RespondObj $respond */
        $respond = CheckoutApi_Lib_Factory::getSingletonInstance('CheckoutApi_Lib_RespondObj');
        $this->setConfig($param);

        if(!isset($param['postedParam'])) {

            $param['postedParam'] = array();
        }

        $param['postedParam'] = $this->getParser()->preparePosted($param['postedParam']);

        $respondArray = null;

        if($state){
            $headers = $this->initHeader();
            $param['headers'] = $headers;

            /** @var CheckoutApi_Client_Adapter_Abstract $adapter */
            $adapter =  $this->getAdapter($this->getProcessType(),array('uri'=>$uri,'config'=>$param));

            if($adapter){
                $adapter->connect();
                $respondString = $adapter->request()->getRespond();
                $respond = $this->getParser()->parseToObj($respondString);


                if($respond->hasErrorCode() && $respond->hasErrors()) {

                    /** @var CheckoutApi_Lib_ExceptionState  $exceptionStateObj */
                    $exceptionStateObj = $respond->getExceptionState();
                    $errors = $respond->getErrors()->toArray();
                    $exceptionStateObj->flushState();

                    foreach( $errors as $error) {
                        $this->throwException($error, $respond->getErrors()->toArray());
                    }
                }elseif($respond->hasErrorCode()) {
                    /** @var CheckoutApi_Lib_ExceptionState  $exceptionStateObj */
                    $exceptionStateObj = $respond->getExceptionState();

                    $this->throwException($respond->getMessage(),$respond->toArray() );
                }

                $adapter->close();
            }

        } //else {
//
//            $exceptionStateObj = $respond->getExceptionState();
//            $messages = $exceptionStateObj ->getMessage();
//            $critical = $exceptionStateObj ->getCritical();
//
//            if(is_array($messages) && sizeof($messages)>0) {
//                foreach($messages as $index => $message ){
//                    if(isset($critical[$index]) && $critical[$index]){
//                        $respondArray['errorCode'] = 'validationFail';
//                        $respondArray['message'] = $messages[$index];
//                        break;
//                    }
//                }
//                $respond->setConfig($respondArray);
//            }
//        }

        return $respond;
    }

     private  function initHeader()
     {
         $headers = array('Authorization: '. $this->getAuthorization());
         $this->setHeaders($headers);
         return $this->getHeaders();
     }

    /**
     * Setting which mode we are running live, preprod or dev
     * @param string $mode
     * @throws Exception
     */

	public function setMode( $mode)
	{

		$this->_mode = $mode;
		$this->setConfig(array('mode'=>$mode));
	}

    /**
     * return the mode . can be either dev or preprod or live
     * @return string
     */

	public function getMode()
	{
        if($this->_config['mode']) {
            $this->_mode =$this->_config['mode'];
        }
        
		return $this->_mode;
	}

    /**
     * @param  string  $uri
     */
	public function setUriCharge( $uri = '')
	{
		$toSetUri = $uri;
		if(!$uri) {
			$toSetUri = $this->getUriPrefix().'charges';
		}

		$this->_uriCharge = $toSetUri;
	}

    /**
     * return $_uriCharge value
     * @return string
     */
	public function getUriCharge()
	{
		return $this->_uriCharge ;
	}

    /**
     * set uri token
     * @param null|string $uri
     */

	public function setUriToken($uri = null)
	{
		$toSetUri = $uri;
		if(!$uri) {
			$toSetUri = $this->getUriPrefix().'tokens';
		}

		$this->_uriToken = $toSetUri;
	}

    /**
     * return uri token
     * @return string
     */

	public function getUriToken()
	{
	    return $this->_uriToken ;
	}

    /**
     * set customer uri
     * @param null|string $uri
     */

	public function setUriCustomer( $uri = null)
	{
		$toSetUri = $uri;
		if(!$uri) {
			$toSetUri = $this->getUriPrefix().'customers';
		}

		$this->_uriCustomer = $toSetUri;
	}

    /**
     * return customer uri
     * @return string
     */
	public function getUriCustomer()
	{
	    return $this->_uriCustomer ;
	}

    /**
     * set provider uri
     * @param null|string $uri
     */

	public function setUriProvider( $uri = null)
	{
		$toSetUri = $uri;
		if(!$uri) {
			$toSetUri = $this->getUriPrefix().'providers';
		}

		$this->_uriProvider = $toSetUri;
	}

    /**
     * return provider uri
     * @return string
     */

	public function getUriProvider()
	{
        return $this->_uriProvider ;
	}

    /**
     * return which uri prefix to be used base on mode type
     * @return string
     */

	private function getUriPrefix()
	{
		$mode = $this->getMode();
		switch ($mode) {
			case 'live':
				$prefix = CheckoutApi_Client_Constant::APIGW3_URI_PREFIX_LIVE;
				break;
			case 'preprod':
				$prefix = CheckoutApi_Client_Constant::APIGW3_URI_PREFIX_PREPOD;
				break;
			default:
				$prefix = CheckoutApi_Client_Constant::APIGW3_URI_PREFIX_DEV;
				break;
		}
		return $prefix;
	}

    /**
     * setting exception state log
     * @param $message
     * @param array $stackTrace
     * @param bool $error
     */

    private function throwException($message,array $stackTrace , $error = true )
    {
        $this->exception($message,$stackTrace,$error);
    }
}