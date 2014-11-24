<?php 
 include 'autoload.php';
/** @var  $Api Http_Client_ClientGW3 */
 $Api = CheckoutApi_Api::getApi();
 
/**
 * ###### Requesting a  card tokem #####
 **/
 $secretKey = 'sk_test_CC937715-4F68-4306-BCBE-640B249A4D50';
 $publicKey = 'pk_test_1ADBEB2D-2BEA-4F82-8ABC-EDE3A1201C8D';
 // configuration for token
 $cardTokenConfig = array();
 $cardTokenConfig['authorization'] = "$publicKey" ;

 $cardTokenConfig['postedParam'] = array ( 
                                           'email' =>'dhiraj@checkout.com',
                                           'card' => array(
                                              'phoneNumber'=>'0123465789',
                                              'name'=>'test name',
                                              'number' => '4543474002249996',
                                              'expiryMonth' => 06,
                                              'expiryYear' => 2017,
                                              'cvv' => 956,
                                            )
                                          );

$respondCardToken = $Api->getCardToken( $cardTokenConfig );


$cardToken = null;

echo '<pre>';
echo " ###### Requesting a  card token #####\n";
if($respondCardToken->isValid()) {
  $cardToken = $respondCardToken->getId();
  echo "\nCard token ::" . $cardToken ;

 } else{
  
  echo "\n\nAn error has occurred while creating the card token";
  echo $respondCardToken->printError();

 }

die();
 echo "\n\n###### Requesting a session token #####";

   $sessionConfig = array();
   $sessionConfig['authorization'] = $secretKey ;
   $sessionConfig['postedParam'] = array( "amount"=>100, "currency"=>"GBP");
   


$sessionTokenObj = $Api->getSessionToken($sessionConfig);

$sessionToken = null;
if($sessionTokenObj->isValid()) {
  $sessionToken = $sessionTokenObj->getId(); 

} else {
  echo "\n\nAn error has occurred while creating the session token";
  echo $respondCardToken->printError();
}

echo "\n\nSession token:: $sessionToken";

echo "\n\n###### Creating a charge #####";

$config = array();
$config['authorization'] = $secretKey ;
$config['postedParam'] = array ( 'email'=>'dhiraj@checkout.com',
                                 'amount'=>150,
                                 'currency'=>'usd',
                                 'description'=>'desc',
                                 'caputure'=>false,

                                 'card' => array(

                                               'phoneNumber'=>'0123465789',
                                               'name'=>'test name',
                                               'number' => '4543474002249996',
                                               'expiryMonth' => 06,
                                               'expiryYear' => 2017,
                                               'cvv' => 956,
                                              // "id"=> "card_471ff8b1-252f-4008-82f4-1ca85be3b83c"
                                               )
                               );

$charge = $Api->createCharge($config);

if($charge->isValid()) {

  $chargeId = $charge->getId();
  $cardObjId = $charge->getCard()->getId();
  echo "\n\nCharge id :: $chargeId";
  echo "\n\nCharge card object id :: $cardObjId";

} else {

 echo "\n\nAn error has occurred while creating a charge";
 echo $charge->printError();

}

echo "\n\n###### Refund a charge #####";
$config = array();
$config['authorization'] = $secretKey ;
$config['chargeId'] = $chargeId ;
$config['postedParam'] = array ( 
                                 'amount'=>150
                               );

$refundCharge = $Api->refundCharge($config);

if($refundCharge->isValid()) {

  $chargeId = $refundCharge->getId();
  $refunded = $refundCharge->getRefunded();
  echo "\n\nCharge id :: $chargeId";
  echo "\nRefunded  :: $refunded";

} else {

 echo "\n\nAn error has occurred while refunding a charge";
 echo $refundCharge->printError();

}

echo "\n\n###### caputure a charge #####";
$config = array();
$config['authorization'] = $secretKey ;
$config['chargeId'] = $chargeId ;
$config['postedParam'] = array ( 
                                 'amount'=>150
                               );

$captureCharge = $Api->captureCharge($config);

if($captureCharge->isValid()) {

  $chargeId = $captureCharge->getId();
  $captured = $captureCharge->getCaptured();
  echo "\n\nCharge id :: $chargeId";
  echo "\nCaptured  :: $refunded";

} else {

 echo "\n\nAn error has occurred while caputiring a charge";
 echo $captureCharge->printError();

}


echo "\n\n###### update a charge #####";
$config = array();
$config['authorization'] = $secretKey ;
$config['chargeId'] = $chargeId ;
$config['postedParam'] = array ( 
                                 'description'=> 'dhiraj is doing some test'
                               );

$updateCharge = $Api->updateCharge($config);

if($updateCharge->isValid()) {

  $chargeId = $updateCharge->getId();
  $captured = $updateCharge->getCaptured();
  $refunded = $updateCharge->getRefunded();
  $descriptionReturn = $updateCharge->getDescription();

  echo "\n\nCharge id :: $chargeId";
  echo "\nCaptured  :: $refunded";
  echo "\nRefund  :: $refunded";
  echo "\nDescription  :: $descriptionReturn";

} else {

 echo "\n\nAn error has occurred while caputiring a charge";
 echo $updateCharge->printError();

}


echo "\n\n###### Creating a customer #####";

$customerConfig = array();
$customerConfig['authorization'] = $secretKey ;

$customerConfig['postedParam'] = array (
                                'email'=> ('dhiraj@checkout.com'),
                                 'name'=>'test customer',
                                 'description'=>'desc',
                                 'card' => array(
                                               'name'=>'test name',
                                               'number' => '4543474002249996',
                                               'expiryMonth' => 06,
                                               'expiryYear' => 2017,
                                               'cvv' => 956,
                                              // "id"=> "card_471ff8b1-252f-4008-82f4-1ca85be3b83c"
                                               )
                               );

$customer = $Api->createCustomer($customerConfig);
$customerId = '';
if($customer->isValid()) {

  $customerId = $customer->getId();

  echo "\n\nCustomer id :: $customerId";
  //echo "\n\nCustomer card object id :: $cardObjId";

} else {

 echo "\n\nAn error has occurred while creating a customer";
 echo $customer->printError();

}

echo "\n\n###### Get  customer $customerId #####";
$getCustomerConfig = array();
$getCustomerConfig['authorization'] = $secretKey ;
$getCustomerConfig['customerId'] = $customerId ;


$getCustomer = $Api->getCustomer($getCustomerConfig);

if($getCustomer->isValid()) {

   $customerId = $getCustomer->getId();
 //  $customerName = $getCustomer->getName();
   echo "\n\nCustomer id :: $customerId";
  // echo "\n\nCustomer name :: $customerName";
   //echo "\n\nCustomer card object id :: $cardObjId";

} else {

   echo "\n\nAn error has occurred while retrieving a customer";
   echo $getCustomer->printError();

}


echo "\n\n###### Update  customer $customerId #####";
$getCustomerConfig = array();
$customerUpdateConfig['authorization'] = $secretKey ;
$customerUpdateConfig['customerId'] = $customerId ;
$customerUpdateConfig['postedParam'] = array (
   'email'=> (rand().'dhiraj@checkout.com'),
   'name'=>rand().'new customer',
   'description'=>'desc',
   'card' => array(
       'name'=>'test name',
       'number' => '4543474002249996',
       'expiryMonth' => 06,
       'expiryYear' => 2017,
       'cvv' => 956,
       // "id"=> "card_471ff8b1-252f-4008-82f4-1ca85be3b83c"
   )
);

$customerUpdate = $Api->updateCustomer($customerUpdateConfig);

if($customerUpdate->isValid()) {

   $customerId = $customerUpdate->getId();
   $customerCreated = $customerUpdate->getCreated();
   $customerName = $customerUpdate->getName();
   echo "\n\nCustomer id :: $customerId";
   echo "\n\nCustomer updated on :: $customerCreated";
   echo "\n\nCustomer name :: $customerName";
   //echo "\n\nCustomer card object id :: $cardObjId";

} else {

   echo "\n\nAn error has occurred while updating a customer";
   echo $customerUpdate->printError();

}


echo "\n\n###### Get  customer List #####";
$getCustomerListConfig = array();
$getCustomerListConfig['authorization'] = $secretKey ;
$getCustomerListConfig['count'] = 100 ;
$getCustomerListConfig['from_date'] = '09/30/2014' ;
$getCustomerListConfig['to_date'] = '10/02/2014' ;
$customerList = $Api->getListCustomer($getCustomerListConfig);
if($customerList->isValid()) {

   $count = $customerList->getCount();
   $dataList = $customerList->getData();
 
   echo "\nCount of customer created :: $count";
 //  echo "\n\nCustomer updated on :: $customerCreated";
   //echo "\n\nCustomer card object id :: $cardObjId";

} else {

   echo "\n\nAn error has occurred while updating a customer";
   echo $customerList->printError();

}




echo "\n\n###### Creating a card id with customer id $customerId #####";
$cardConfig = array();
$cardConfig['authorization'] = $secretKey ;
$cardConfig['customerId'] = $customerId ;
$cardConfig['postedParam'] = array (
   'customerID'=> $customerId,
   'card' => array(
       'name'=>'test name',
       'number' => '4543474002249996',
       'expiryMonth' => 06,
       'expiryYear' => 2017,
       'cvv' => 956,

   )
);

$cardObj = $Api->createCard($cardConfig);

if($cardObj->isValid()){
 $data = $cardObj->getData();
 $cardId = $data->get0()->getId();
 $last4 = $data->get0()->getLast4();
 echo "\ncard id $cardId";
 echo "\nlast 4 digit $last4";

}else {
   echo 'error while creating a card';
   echo $cardObj->printError();
}


echo "\n\n###### Update  card id: $cardId  for customer id $customerId #####";
$updateCardConfig = array();
$updateCardConfig['authorization'] = $secretKey ;
$updateCardConfig['customerId'] = $customerId ;
$updateCardConfig['cardId'] = $cardId ;
$updateCardConfig['postedParam'] = array (
   'card' => array(
       'name'=>'New name',
       'number' => '4543474002249996',
       'expiryMonth' => 08,
       'expiryYear' => 2017,
       'cvv' => 956,

   )
);

$updateCardObj = $Api->updateCard($updateCardConfig);

if($updateCardObj->isValid()){


   $cardId = $updateCardObj->getId();
   $name = $updateCardObj->getName();
   $expMonth = $updateCardObj->getExpiryMonth();
   echo "\ncard id $cardId";
   echo "\ncustomer Name $name";
   echo "\nexpiry month $expMonth";

}else {
   echo 'error while creating a card';
   echo $updateCardObj->printError();
}

echo "\n\n###### Get  card id: $cardId  for customer id $customerId #####";
$getCardConfig = array();
$getCardConfig['authorization'] = $secretKey ;
$getCardConfig['customerId'] = $customerId ;
$getCardConfig['cardId'] = $cardId ;


$getCardObj = $Api->getCard($getCardConfig);

if($getCardObj->isValid()){

   $cardId =$getCardObj->getId();
   $name = $getCardObj->getName();
   $expMonth = $getCardObj->getExpiryMonth();
   echo "\ncard id $cardId";
   echo "\ncustomer Name $name";
   echo "\nexpiry month $expMonth";

}else {
   echo 'error while creating a card';
   echo $updateCardObj->printError();
}
echo "\n\n###### Get  list of for customer id $customerId #####";
$getCardListConfig = array();
$getCardListConfig['authorization'] = $secretKey ;
$getCardListConfig['customerId'] = $customerId ;

$getCardListObj = $Api->getCardList($getCardConfig);

if($getCardListObj->isValid()){

   $cardCount = $getCardListObj->getCount();

   echo "\ncard count $cardCount";

}else {
   echo 'error while creating a card';
   echo $updateCardObj->printError();
}

echo "\n\n###### Delete  card id: $cardId  for customer id $customerId #####";
$deleteCardConfig = array();
$deleteCardConfig['authorization'] = $secretKey ;
$deleteCardConfig['customerId'] = $customerId ;
$deleteCardConfig['cardId'] = $cardId ;


$deleteCard = $Api->deleteCard($deleteCardConfig);

if($deleteCard->isValid()){

   $deleted =$deleteCard->getDeleted()?'true':'false';
   echo "\ndeleted $deleted";

}else {
   echo 'error while creating a card';
   echo $deleteCard->printError();
}


echo "\n\n###### Delete  customer $customerId #####";
$deleteCustomerConfig = array();
$deleteCustomerConfig['authorization'] = $secretKey ;
$deleteCustomerConfig['customerId'] = $customerId ;

$deleteCustomer = $Api->deleteCustomer($deleteCustomerConfig);
if($deleteCustomer->isValid()) {

   $customerId = $deleteCustomer->getId();
   $deleted = $deleteCustomer->getDeleted();
   echo "\n\nCustomer id :: $customerId";
   echo "\n\nCustomer deleted :: $deleted";
   //echo "\n\nCustomer card object id :: $cardObjId";

} else {

   echo "\n\nAn error has occurred while updating a customer";
   echo $deleteCustomer->printError();

}

 echo "\n\n Getting a list of local payment base on session token $sessionToken";

$localPaymentListConfig = array();
$localPaymentListConfig['authorization'] = $publicKey ;
$localPaymentListConfig['token'] = $sessionToken ;

$localPaymentListObj = $Api->getLocalPaymentList($localPaymentListConfig);
if($localPaymentListObj->isValid()) {
 $providerId = $localPaymentListObj->getData()->get0()->getId();
 $localPaymentName =    $localPaymentListObj->getData()->get0()->getName();
 echo "\nProvider Id : $providerId";
 echo "\nProvider name : $localPaymentName";

}else {
    echo 'error while get list of local payment';
    echo $localPaymentListObj->printError();
}

echo "\n\n Getting a  local payment provider (provider id:$providerId) base on session token $sessionToken";

$localPaymentConfig = array();
$localPaymentConfig['authorization'] = $publicKey ;
$localPaymentConfig['token'] = $sessionToken ;
$localPaymentConfig['providerId'] = $providerId ;

$localPaymentObj = $Api->getLocalPaymentProvider($localPaymentConfig);

if($localPaymentObj->isValid()) {
    $providerId = $localPaymentListObj->getData()->get0()->getId();
    $localPaymentName =    $localPaymentListObj->getData()->get0()->getName();
    echo "\nProvider Id : $providerId";
    echo "\nProvider name : $localPaymentName";

}else {
    echo 'error while get  of local payment';
    echo $localPaymentListObj->printError();
}


echo "\n\n Getting a  card object list";

$cardProvidersListConfig = array();
$cardProvidersListConfig['authorization'] = $publicKey ;

$cardProviderListObj = $Api->getCardProvidersList($cardProvidersListConfig);
if($cardProviderListObj->isValid()) {
    $providerId = $cardProviderListObj->getData()->get0()->getId();
    $name =    $cardProviderListObj->getData()->get0()->getName();
    echo "\nProvider Id : $providerId";
    echo "\nCard name : $name";

}else {
    echo 'error while get  card object by provider';
    echo $localPaymentListObj->printError();
}


echo "\n\n Getting a  card object from  provider id:$providerId";

$cardProvidersConfig = array();
$cardProvidersConfig['authorization'] = $publicKey ;
$cardProvidersConfig['providerId'] = $providerId ;

$cardProvidersObj = $Api->getCardProvider($cardProvidersConfig);
if($cardProvidersObj->isValid()) {
    $providerId = $cardProvidersObj->getId();
    $name =    $cardProvidersObj->getName();
    echo "\nProvider Id : $providerId";
    echo "\nCard name : $name";

}else {
    echo 'error while get  card object by provider';
    echo $localPaymentListObj->printError();
}


echo "\n\n Creating a local payment charge";

$chargeLocalPaymentConfig = array();
$chargeLocalPaymentConfig['authorization'] = $publicKey ;
$chargeLocalPaymentConfig['postedParam'] = array(
                                                'email'=>'dhiraj.checkout@checkout.com',
                                                'token'=>$sessionToken,
                                                 'localPayment'=> array(
                                                     'lppId'=> $localPaymentObj->getId()
                                                 )
                                                ) ;


$chargeLocalPaymentObj = $Api->createLocalPaymentCharge($chargeLocalPaymentConfig);

if($chargeLocalPaymentObj->isValid()) {
    $chargeId = $chargeLocalPaymentObj->getId();
    echo "\Charge id : $chargeId";

}else {
    echo 'error while creating a local payment charge';
    echo $localPaymentListObj->printError();
}

echo '</pre>';

