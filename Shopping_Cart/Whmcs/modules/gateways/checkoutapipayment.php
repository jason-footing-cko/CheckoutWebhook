<?php
include 'includes/autoload.php';

function checkoutapipayment_config() {
    $configarray = array(
     "FriendlyName" => array("Type" => "System", "Value"=>"Checkout.com - Gateway 3.0 Payment"),
     "ispci" => array("FriendlyName" => "Is your site pci?", "Type" => "yesno", "Description" => "Tick this to set pci", ),
     "publickey" => array("FriendlyName" => "Publishable API Key", "Type" => "text", "Description" => "The Checkout.com account publishable API key to use.", ),
     "secretkey" => array("FriendlyName" => "Secret API Key", "Type" => "text", "Description" => "The Checkout.com account secret API key to use .", ),
     "modetype" => array("FriendlyName" => "Transaction Server", "Type" => "dropdown", "Options" => "Test,Prerpod,live", ),
     "transmethod" => array("FriendlyName" => "Transaction Method", "Type" => "dropdown", "Options" => "Authorize,Capture", ),
     "capturetime" => array("FriendlyName" => "Set auto capture time.", "Type" => "Text", "Size" => "15", "Value"=>0,"Description" => "When transaction is set to authorize and caputure , the gateway will use this time to caputure the transaction.", ),
     "timeout" => array("FriendlyName" => "Set Gateway timeout..", "Type" => "text", "size" => "15", "Value"=>60,"Description" => "Set how long request timeout on server.", ),
     "localpayment" => array("FriendlyName" => "Enable localpayment Mode", "Type" => "yesno", "Description" => "Tick this enable localpayment", ),
    );
	return $configarray;
}
function getPaymentInstance($param)
{

    $GATEWAY = getGatewayVariables('checkoutapipayment');
    if($GATEWAY['ispci']){
        $instance = new model_methods_creditcardpci();
    }else {
        $instance = new model_methods_creditcard();
    }

    return $instance;
}

function checkoutapipayment_capture($params) {

   $instance = getPaymentInstance($params);


	# Perform Transaction Here & Generate $results Array, eg:
	return  $instance->before_capture($params);



}

function checkoutapipayment_refund($params) {



//	# Return Results
//	if ($results["status"]=="success") {
//		return array("status"=>"success","transid"=>$results["transid"],"rawdata"=>$results);
//	} elseif ($gatewayresult=="declined") {
//        return array("status"=>"declined","rawdata"=>$results);
//    } else {
//		return array("status"=>"error","rawdata"=>$results);
//	}

}


function addFooterHtml($params)
{

    $instance = getPaymentInstance($params);
    $toRetrun = $instance->getFooterHtml($params);

    return $toRetrun;
}

function addHeadHtml($params)
{

    $instance = getPaymentInstance($params);
    $toRetrun = $instance->getHeadHtml($params);

    return $toRetrun;
}

function ShoppingCartValidateCheckout($params)
{
    $instance = getPaymentInstance($params);
    $toRetrun = $instance->getShoppingCartValidateCheckout($params);

    return $toRetrun;
}
add_hook("ClientAreaFooterOutput",1,"addFooterHtml","");
add_hook("ClientAreaHeadOutput",1,"addHeadHtml","");
add_hook("ShoppingCartValidateCheckout",1,"addHeadHtml","");
?>