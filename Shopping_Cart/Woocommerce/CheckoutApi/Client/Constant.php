<?php 
final class CheckoutApi_Client_Constant 
{
	const APIGW3_URI_PREFIX_PREPOD = 'http://preprod.checkout.com/api.gw3/';
	const APIGW3_URI_PREFIX_DEV= 'http://dev.checkout.com/api.gw3/';
	const APIGW3_URI_PREFIX_LIVE = 'https://api2.checkout.com/v1/';
	const ADAPTER_CLASS_GROUP = 'CheckoutApi_Client_Adapter_';
	const PARSER_CLASS_GROUP = 'CheckoutApi_Parser_';
	const CHARGE_TYPE = 'card';
	const LOCALPAYMENT_CHARGE_TYPE = 'localPayment';
	const TOKEN_CARD_TYPE = 'cardToken';
	const TOKEN_SESSION_TYPE = 'sessionToken';
	const AUTOCAPUTURE_CAPTURE = 'y';
	const AUTOCAPUTURE_AUTH = 'n';


}