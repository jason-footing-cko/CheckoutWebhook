<?php
class model_methods_creditcard extends model_methods_Abstract
{

    public function before_process()
    {
        global  $HTTP_POST_VARS,$order;

        $config = parent::before_process();

        $config['postedParam']['email'] = $HTTP_POST_VARS['cko_cc_email'];
        $config['postedParam']['cardToken'] = $HTTP_POST_VARS['cko_cc_token'];
        $this->_placeorder($config);
    }

    public function  before_capture($params)
    {
        print_r($params);die();
    }

    public function getFooterHtml($param)
    {

        $Api = CheckoutApi_Api::getApi(array('mode'=>$param['modetype']));
        $GATEWAY = getGatewayVariables('checkoutapipayment');

        $config['debug'] = 'false';
        $config['publicKey']  = $GATEWAY['publickey'] ;
        $config['email'] =   $param['clientsdetails']['email'];
        $config['name'] = $param['clientsdetails']['firstname'].' '. $param['clientsdetails']['lastname'];
        $config['amount'] =   $param['rawtotal']*100;
        $config['currency'] =   $param['currency']['code'];
        $config['widgetSelector'] =  '.widget-container';
        $config['cardTokenReceivedEvent'] = "
                        document.getElementById('cko-cc-token').value = event.data.cardToken;
                        document.getElementById('cko-cc-email').value = event.data.email;

                        //$('.ordernow').trigger('click');
                        ";
        $config['widgetRenderedEvent'] ="if ($('.cko-pay-now')) {
                                                $('.cko-pay-now').hide();
                                            }";
        $config['readyEvent'] = '';
        $html = "  <script type='text/javascript' >
            window.CKOConfig = {
                publicKey: '".$GATEWAY['publickey']."',
                debugMode: true,
                ready: function() {
                var submit = document.getElementById('mainfrm').onsubmit;
                    CKOAPI.monitorForm('#mainfrm');
                    $('[name=ccnumber]').attr('data-checkout','email-address');
                    $('#expiry-month').val($('#ccexpirymonth').val());

                    $('#ccexpirymonth').change(function(){
                        $('#expiry-month').val($(this).val());
                    });

                    $('#expiry-year').val( $('[name=ccexpiryyear]').val());

                    $('[name=ccexpiryyear]').change(function(){
                        $('#expiry-year').val($(this).val());
                    });

                    $('[name=cccvv]').attr('data-checkout','cvv');
                    $('[name=ccnumber]').attr('data-checkout','card-number');
                    console.log(submit+'');
                },
                formMonitored: function(event) {

                },
                formSubmitted: function(event) {
                    $('#mainfrm').trigger('submit');

                }
            };
        </script>
        <script  src='https://www.checkout.com/cdn/js/CKOAPI.js'></script>";
        $html.='<div style="display:block" class="widget-container"><input data-checkout="email-address" type="hidden" placeholder="Enter your e-mail address" class="input-control" value="'. $config['email'] .'"/>
                <input data-checkout="card-name" type="hidden" placeholder="Enter the name on your card" autocomplete="off" class="input-control" value="'. $config['name'].'" />
                <input data-checkout="expiry-month" id="expiry-month" type="hidden" placeholder="MM" autocomplete="off" class="input-control center-align" maxlength="2" value=""/>
                <input data-checkout="expiry-year" id="expiry-year" type="hidden" placeholder="YY" autocomplete="off" class="input-control center-align" maxlength="2" value=""/>

</div><script>   $(".widget-container").insertBefore($("#ccinputform"));</script>';
        return $html;
    }

    public function getHeadHtml($param)
    {
        return '';
    }
}