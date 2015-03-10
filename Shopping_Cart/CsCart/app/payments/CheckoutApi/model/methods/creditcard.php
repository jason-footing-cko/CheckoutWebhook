<?php
class model_methods_creditcard extends model_methods_Abstract
{

    public function getExtraHtml($order,$setting)
    {


        $amountCents = $order['total']*100 ;
        $Api = CheckoutApi_Api::getApi(array('mode'=>$setting['mode_type']));
        $config = array();
        $config['debug'] = false;
        $config['renderMode'] = 0;
        $config['publicKey'] = $setting['public_key'] ;
        $config['email'] =  $order['user_data']['email'];
        $config['name'] = $order['user_data']['firstname'] . ' ' . $order['user_data']['lastname'];
        $config['amount'] = $amountCents;
        $config['currency'] =  $_SESSION['settings']['secondary_currencyC']['value'];
        $config['widgetSelector'] =  '.widget-container';
        $config['cardTokenReceivedEvent'] = "
                       document.getElementById('cko-cc-token').value = event.data.cardToken;
                        document.getElementById('cko-cc-email').value = event.data.email;

  jQuery('#cko-cc-token').parents('.payments-form.cm-processed-form').find('[name=\"dispatch\[checkout.place_order\]\"]').trigger('click');
                        ";
        $config['widgetRenderedEvent'] =" $('.cko-pay-now').hide(); $('body').append($('.widget-container link'))";
        $config['readyEvent'] = '

        ';


        $jsConfig = $Api->getJsConfig($config);


        $content =  <<<EOD


    <script type="text/javascript">

{$jsConfig};
$(document).ajaxComplete(function(event,request, settings){
    if(settings.url.indexOf('step_four')){
        if( typeof CheckoutIntegration != 'undefined'){

            CheckoutIntegration.render(window.CKOConfig)


         }
    }
 if( !document.getElementById("cko-widget")) {
            if(CheckoutIntegration){
                CheckoutIntegration.render(window.CKOConfig);  console.log(2);
            }else {
                if(Checkout.hasOwnProperty("render")) {
                 Checkout.render(window.CKOConfig);
                 console.log(1);
                }
            }
        }
});


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


}