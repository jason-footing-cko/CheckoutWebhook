<?php
class model_methods_creditcard extends model_methods_Abstract
{

    public function getExtraHtml($order,$setting)
    {


        $amountCents = $order['total']*100 ;
        $paymentToken = $this->getPaymentToken($order,$setting);

        $content =  <<<EOD


    <script type="text/javascript">

 window.CKOConfig = {
        debugMode: false,
        renderMode: 2,
        namespace: 'CheckoutIntegration',
        publicKey: "{$setting['public_key']}",
        paymentToken: "{$paymentToken}",
        value: "{$amountCents}",
        currency: "{$_SESSION['settings']['secondary_currencyC']['value']}",
        customerEmail: "{$order['user_data']['email']}",
        customerName: "{$order['user_data']['firstname']} {$order['user_data']['lastname']}",
        paymentMode: 'card',
        widgetContainerSelector: '.widget-container',
        paymentTokenExpired: function(){
            alert('Your payment session has expired. We will reload you page thank you');
            window.location.reload();
        },
        widgetRenderedEvent: function(event){
            $('.cko-pay-now').hide(); $('body').append($('.widget-container link'));

        },
        cardCharged: function(event){

                document.getElementById('cko-cc-paymenToken').value = event.data.paymentToken;
                jQuery('#cko-cc-paymenToken').parents('.payments-form.cm-processed-form').find('[name=\"dispatch\[checkout.place_order\]\"]').trigger('click');



        },
        ready: function() {
            seeder = 0;
            $('body').append($('.widget-container link'));
        }
    };


</script>
EOD;

        return $content;

    }


    public function processRequest($order,$post, $setting= array() )
    {

        $config = parent::processRequest($order,$post,$setting);

        $config['postedParam']['email'] = $post['cko_cc_email'];
        $config['postedParam']['cardToken'] = $post['cko_cc_token'];
        return $this->_placeorder($config,$order,$setting);
    }

    protected function _createCharge($config,$setting)
    {
        /** @var CheckoutApi_Client_ClientGW3  $Api */
        $Api = CheckoutApi_Api::getApi(array('mode'=>$setting['mode_type']));

        $config['paymentToken'] = $_POST['cko_cc_paymenToken'];
        $config['authorization'] = $setting['secret_key'];
        $config['mode'] =  $setting['mode_type'];

        return $Api->verifyChargePaymentToken($config);

    }
    protected function getPaymentToken($order,$setting )
    {

        $Api = CheckoutApi_Api::getApi(array('mode'=>$setting['mode_type']));


        $config['authorization'] = $setting['secret_key'];
        $config['mode'] =  $setting['mode_type'];


        if ($order['products']) {
            foreach($order['products'] as $product) {

                $products[] = array (
                    'name'       =>    $product['product'],
                    'sku'        =>    $product['product_code'],
                    'price'      =>    $product['price'],
                    'quantity'   =>     $product['amount'],

                );
            }
        }

        $amountCents = $order['total']*100 ;

        $shippingAddressConfig =  array (
            'addressLine1' =>  $order['user_data']['s_address'],
            'addressLine2' =>  $order['user_data']['s_address_2'],
            'postcode'     =>  $order['user_data']['s_zipcode'],
            'country'      =>  $order['user_data']['s_country'],
            'city'         =>  $order['user_data']['s_city'],
            'phone'        =>  $order['user_data']['s_phone']
        );


        $config['postedParam'] = array (
            'email'           => $order['user_data']['email'] ,
            'value'           => $amountCents,
            'currency'        => $_SESSION['settings']['secondary_currencyC']['value'],
            'products'        => $products,
            'shippingDetails' => $shippingAddressConfig,

            'billingDetails'  => array (
                'addressLine1'   =>  $order['user_data']['b_address'],
                'addressLine2'  =>  $order['user_data']['b_address_2'],
                'postcode'      =>  $order['user_data']['b_zipcode'],
                'country'       =>  $order['user_data']['b_country'],
                'city'          =>  $order['user_data']['b_city'],
                'phone'         =>  $order['user_data']['b_phone'],


            )


        );

        if ($setting['transaction_type'] == 'authorize_capture') {
            $config = array_merge_recursive( $this->_captureConfig($setting),$config);
        } else {
            $config = array_merge_recursive( $this->_authorizeConfig($setting),$config);
        }

        $paymentTokenCharge = $Api->getPaymentToken($config);

        $paymentToken    =   '';

        if($paymentTokenCharge->isValid()){
            $paymentToken = $paymentTokenCharge->getId();
        }

        if(!$paymentToken) {
            throw new Exception(
                $paymentTokenCharge->getExceptionState()->getErrorMessage().
                ' ( '.$paymentTokenCharge->getEventId().')'
            );
        }

        return $paymentToken;

    }

}