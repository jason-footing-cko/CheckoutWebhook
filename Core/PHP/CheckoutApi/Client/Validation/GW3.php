<?php
final class CheckoutApi_Client_Validation_GW3 extends CheckoutApi_Lib_Object
{
	public static function isEmailValid($postedParam) 
	{
		$isEmailEmpty = true;	
		$isValidEmail  = false;

		if(isset($postedParam['email'])) {

			$isEmailEmpty = CheckoutApi_Lib_Validator::isEmpty($postedParam['email']);

		}

		if(!$isEmailEmpty) {

			$isValidEmail =  CheckoutApi_Lib_Validator::isValidEmail($postedParam['email']);

		}

		return !$isEmailEmpty && $isValidEmail;

	}


	public static function isCustomerIdValid($postedParam)
	{
		$isCustomerIdEmpty = true;
		$isValidCustomerId = false;

		if(isset($postedParam['customerId'])) {
			$isCustomerIdEmpty = CheckoutApi_Lib_Validator::isEmpty($postedParam['customerId']);
        }

        if(!$isCustomerIdEmpty) {

			$isValidCustomerId = CheckoutApi_Lib_Validator::isString($postedParam['customerId']);
		}

		return !$isCustomerIdEmpty && $isValidCustomerId;
	}


	public static function isAmountValid($postedParam)
	{
		$isValid = false;

		if(isset($postedParam['amount'])) {

			$amount = $postedParam['amount'];

			$isAmountEmpty = CheckoutApi_Lib_Validator::isEmpty($amount);

			$isIntegerAmount = CheckoutApi_Lib_Validator::isInteger($amount);
			
			if(!$isAmountEmpty && $isIntegerAmount && $amount > -1 ) {
				$isValid = true;
			//	$this->logError(true, "Amount should be in cents.", array('amount'=>$postedParam['amount']), false);
			} 
	
		} 

		return $isValid;
	}

	public static function isValidCurrency($postedParam) 
	{
		$isValid = false;

		if(isset($postedParam['currency'])) {

			$currency = $postedParam['currency'];
			$currencyEmpty = CheckoutApi_Lib_Validator::isEmpty($currency);

			if(!$currencyEmpty){
				$isCurrencyLen = CheckoutApi_Lib_Validator::isLength($currency, 3);

				if($isCurrencyLen) {
					$isValid = true;
				}
			}
		}

		return $isValid;
	}

	public static function isNameValid($postedParam)
	{
		$isValid = false;

		if(isset($postedParam['name'])) {

			$isNameEmpty = CheckoutApi_Lib_Validator::isEmpty($postedParam['name']);
			if(!$isNameEmpty) {

				$isValid = true;
			}
			
		} 

		return $isValid ;
	}

	public static function isCardNumberValid($param)
	{
		$isValid = false;

		if(isset($param['number'])) {

			$errorIsEmpty = CheckoutApi_Lib_Validator::isEmpty($param['number']) ;
			
			if(!$errorIsEmpty) {
				//$this->logError(true, "Card number can not be empty.", array('card'=>$param),false);
				$isValid = true;
			}

		} 

		return $isValid;
	}


	public static function isMonthValid($card)
	{
		$isValid = false;

		if(isset($card['expiryMonth'])) {

			$isExpiryMonthEmpty = CheckoutApi_Lib_Validator::isEmpty($card['expiryMonth'],false);
			
			if(!$isExpiryMonthEmpty && CheckoutApi_Lib_Validator::isInteger($card['expiryMonth']) && ($card['expiryMonth']  > 0 && $card['expiryMonth'] < 13)) {
				$isValid = true;
			} 
		} 

		return $isValid;
	}

	public static function isValidYear($card)
	{
		$isValid = false;

		if(isset($card['expiryYear'])) {

			$isExpiryYear = CheckoutApi_Lib_Validator::isEmpty($card['expiryYear']);
			
			if( !$isExpiryYear && CheckoutApi_Lib_Validator::isInteger($card['expiryYear']) &&
				( CheckoutApi_Lib_Validator::isLength($card['expiryYear'], 2) ||  CheckoutApi_Lib_Validator::isLength($card['expiryYear'], 4) ) ) {
			
				$isValid = true;
			
			} 
		}

		return $isValid;
	}

	public static function isValidCvv2($card) 
	{
		$isValid = false;

		if(isset($card['cvv'])) {

			$isCvv2Empty = CheckoutApi_Lib_Validator::isEmpty($card['cvv']);
			
			if(!$isCvv2Empty && CheckoutApi_Lib_Validator::isValidCvv2Len($card['cvv'])) {
			
				$isValid = true;
			
			} 

		} 

		return $isValid;
	}

	public static function  isCardValid($param) 
	{
		$isValid = true;
        if(isset($param['card'])) {
            $card = $param['card'];

            $isNameValid = CheckoutApi_Client_Validation_GW3::isNameValid($card);

            if (!$isNameValid) {

                $isValid = false;
            }

            $isCardNumberValid = CheckoutApi_Client_Validation_GW3::isCardNumberValid($card);

            if (!$isCardNumberValid) {

                $isValid = false;
            }

            $isValidMonth = CheckoutApi_Client_Validation_GW3::isMonthValid($card);

            if (!$isValidMonth) {
                $isValid = false;
            }

            $isValidYear = CheckoutApi_Client_Validation_GW3::isValidYear($card);

            if (!$isValidYear) {
                $isValid = false;
            }

            $isValidCvv2 = CheckoutApi_Client_Validation_GW3::isValidCvv2($card);

            if (!$isValidCvv2) {
                $isValid = false;
            }

            return $isValid;
        }
        return true;

	}

	public static function isCardIdValid($param)
	{
		$isValid = false;
        if(isset($param['card'])) {
		$card = $param['card'];

		if(isset($card['id'])) {

			$isCardIdEmpty = CheckoutApi_Lib_Validator::isEmpty($card['id']);

			if(!$isCardIdEmpty && CheckoutApi_Lib_Validator::isString($card['id']) )
			{
				$isValid = true;
			}
		}

		return $isValid;
        }
        return true;

	}

    public static function isGetCardIdValid($param)
    {
        $isValid = false;
        $card = $param['cardId'];

        if(isset($param['cardId'])) {
            $isValid = self::isCardIdValid(array('card'=>$param['cardId']));
        }

        return $isValid;

    }

	public static function isPhoneNoValid($postedParam)
	{
		$isValid = false;

		if(isset($postedParam['phoneNumber'])) {

			$isPhoneEmpty = CheckoutApi_Lib_Validator::isEmpty($postedParam['phoneNumber']);

			if(!$isPhoneEmpty &&  CheckoutApi_Lib_Validator::isString($postedParam['phoneNumber']) ){
				$isValid = true;
			}
		}

		return $isValid;

	}

	public static function isCardToken($param)
	{
		$isValid = false;

		if(isset($param['token'])){
			$isTokenEmpty = CheckoutApi_Lib_Validator::isEmpty($param['token']);

			if(!$isTokenEmpty) {
				$isValid = true;
			}
		}

		return $isValid;
	}

	public static function isSessionToken($param)
	{
		$isValid = false;

		if(isset($param['token'])){
			$isTokenEmpty = CheckoutApi_Lib_Validator::isEmpty($param['token']);

			if(!$isTokenEmpty) {
				$isValid = true;
			}
		}

		return $isValid;
	}


	public static function isLocalPyamentHashValid($postedParam)
	{
		$isValid = false;

		if(isset($postedParam['localPayment']) && !(CheckoutApi_Lib_Validator::isEmpty($postedParam['localPayment']))) {
			if(isset($postedParam['localPayment']['lppId']) && !(CheckoutApi_Lib_Validator::isEmpty($postedParam['localPayment']['lppId']))) {
				$isValid = true;
			}
		}

		return $isValid;
	}

    public static function isChargeIdValid($param)
    {
        $isValid = false;

        if(isset($param['chargeId']) && !(CheckoutApi_Lib_Validator::isEmpty($param['chargeId']))) {
                $isValid = true;
        }
        return $isValid;
    }

    public static function isProvider($param)
    {
        $isValid = false;

        if(isset($param['providerId']) && !(CheckoutApi_Lib_Validator::isEmpty($param['providerId']))) {
            $isValid = true;
        }
        return $isValid;
    }
}